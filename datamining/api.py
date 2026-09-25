"""
FastAPI — Data Mining API
W9 Cafe POS | Capstone STIE Totalwin

Endpoints:
  GET  /health            — cek status server
  GET  /preview-data      — preview data dari DB
  POST /clustering        — K-Means Clustering (2-variabel: Jumlah + Keuntungan)
  POST /prediction        — Time Series Prediction per Menu (Prophet)
  POST /association       — Association Rules (Apriori/FP-Growth)
"""

import os, warnings, math, json
from typing import Optional
import numpy as np
import pandas as pd

from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from dotenv import load_dotenv
import psycopg2
from psycopg2.extras import RealDictCursor
from sklearn.preprocessing import MinMaxScaler

try:
    from .prediction    import run_prediction_pipeline
    from .association   import run_association_pipeline
    from .bahanbaku     import run_bahan_baku_pipeline
    from .prediksibaku  import run_prediction_pipeline_bahan_baku
    from .kmeans_utils  import select_kmeans
except ImportError:
    from prediction    import run_prediction_pipeline
    from association   import run_association_pipeline
    from bahanbaku     import run_bahan_baku_pipeline
    from prediksibaku  import run_prediction_pipeline_bahan_baku
    from kmeans_utils  import select_kmeans

warnings.filterwarnings("ignore")
load_dotenv()

app = FastAPI(title="W9 Cafe — Data Mining API", version="2.0.0")
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)


# ── Koneksi DB ─────────────────────────────────────────────────────────────
def get_connection():
    return psycopg2.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", 5432)),
        dbname=os.getenv("DB_NAME", "pos_cafe"),
        user=os.getenv("DB_USER", "postgres"),
        password=os.getenv("DB_PASSWORD", ""),
        options=f"-c TimeZone={os.getenv('DB_TIMEZONE', 'Asia/Jakarta')}",
        cursor_factory=RealDictCursor,
    )


# ── Ambil data pesanan (+ Keuntungan) dengan filter tanggal opsional ───────
def fetch_order_data(date_from: Optional[str] = None,
                     date_to:   Optional[str] = None) -> pd.DataFrame:
    where_clauses = ["o.status = 'completed'"]
    params: list = []

    if date_from and date_from.strip():
        where_clauses.append("o.created_at::date >= %s")
        params.append(date_from.strip())
    if date_to and date_to.strip():
        where_clauses.append("o.created_at::date <= %s")
        params.append(date_to.strip())

    where_sql = " AND ".join(where_clauses)

    sql = f"""
        SELECT
            o.created_at::date                                          AS "Tanggal",
            m.name                                                      AS "Nama Item",
            SUM(oi.quantity)::float                                     AS "Jumlah",
            SUM(oi.subtotal - COALESCE(oi.cost_price, 0) * oi.quantity)::float AS "Keuntungan"
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        JOIN menus  m ON m.id = oi.menu_id
        WHERE {where_sql}
        GROUP BY o.created_at::date, m.name
        ORDER BY o.created_at::date ASC
    """

    conn = get_connection()
    try:
        with conn.cursor() as cur:
            cur.execute(sql, params if params else None)
            rows = cur.fetchall()
    finally:
        conn.close()

    df = pd.DataFrame([dict(r) for r in rows])
    if not df.empty:
        df["Tanggal"] = pd.to_datetime(df["Tanggal"])
    return df


def fetch_association_data(date_from: Optional[str] = None,
                           date_to:   Optional[str] = None) -> pd.DataFrame:
    where_clauses = ["o.status = 'completed'"]
    params: list = []

    if date_from and date_from.strip():
        where_clauses.append("o.created_at::date >= %s")
        params.append(date_from.strip())
    if date_to and date_to.strip():
        where_clauses.append("o.created_at::date <= %s")
        params.append(date_to.strip())

    where_sql = " AND ".join(where_clauses)

    sql = f"""
        SELECT
            o.created_at::date   AS "Tanggal",
            o.order_code         AS "Order_id",
            m.name               AS "Nama Item"
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        JOIN menus  m ON m.id = oi.menu_id
        WHERE {where_sql}
    """

    conn = get_connection()
    try:
        with conn.cursor() as cur:
            cur.execute(sql, params if params else None)
            rows = cur.fetchall()
    finally:
        conn.close()

    df = pd.DataFrame([dict(r) for r in rows])
    if not df.empty:
        df["Tanggal"] = pd.to_datetime(df["Tanggal"])
    return df


def fetch_ingredient_data(date_from: Optional[str] = None,
                          date_to:   Optional[str] = None) -> pd.DataFrame:
    where_clauses = ["o.status = 'completed'"]
    params: list = []

    if date_from and date_from.strip():
        where_clauses.append("o.created_at::date >= %s")
        params.append(date_from.strip())
    if date_to and date_to.strip():
        where_clauses.append("o.created_at::date <= %s")
        params.append(date_to.strip())

    where_sql = " AND ".join(where_clauses)

    sql = f"""
        SELECT
            o.created_at::date                          AS "Tanggal",
            i.name                                      AS "Bahan_Baku",
            i.unit                                      AS "Unit",
            SUM(oi.quantity * mi.quantity_used)::float  AS "Jumlah_Digunakan"
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        JOIN menu_ingredients mi ON mi.menu_id = oi.menu_id
        JOIN ingredients i ON i.id = mi.ingredient_id
        WHERE {where_sql}
        GROUP BY o.created_at::date, i.id, i.name, i.unit
        ORDER BY o.created_at::date ASC
    """

    conn = get_connection()
    try:
        with conn.cursor() as cur:
            cur.execute(sql, params if params else None)
            rows = cur.fetchall()
    finally:
        conn.close()

    df = pd.DataFrame([dict(r) for r in rows])
    if not df.empty:
        df["Tanggal"] = pd.to_datetime(df["Tanggal"])
    return df


# ── PIPELINE (mengikuti 2REV_K_Means_REV_Pesanan.ipynb sel per sel) ────────
def run_pipeline(df: pd.DataFrame) -> dict:
    logs = []

    # ── Parsing tanggal ───────────────────────────────────────────────────
    # Data dari sistem POS sudah bersih: tidak ada missing value atau duplikat
    # karena setiap baris order_item dibuat oleh sistem secara otomatis.
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])
    logs.append({
        "tahap":  "Load Data Riwayat Pesanan",
        "detail": (
            f"Berhasil memuat {len(df)} baris item pesanan dari {df['Nama Item'].nunique()} menu unik. "
            f"Rentang data: {df['Tanggal'].min().date()} s/d {df['Tanggal'].max().date()}."
        ),
    })

    # ── Agregasi harian per menu (cell-7) ─────────────────────────────────
    df_sorted = df.sort_values("Tanggal", ascending=True)
    df_daily = df_sorted.groupby(
        ["Tanggal", "Nama Item"], as_index=False
    )[["Jumlah", "Keuntungan"]].sum()
    logs.append({
        "tahap":  "Agregasi Harian per Menu",
        "detail": (
            f"Diagregasi per Tanggal × Nama Item. "
            f"Baris: {len(df_daily)}, menu unik: {df_daily['Nama Item'].nunique()}."
        ),
    })

    # ── Lengkapi tanggal yang hilang per menu → isi 0 (cell-11) ──────────
    df_daily["Tanggal"] = pd.to_datetime(df_daily["Tanggal"])
    min_date = df_daily["Tanggal"].min()
    max_date = df_daily["Tanggal"].max()

    df_full_all = []
    for item in df_daily["Nama Item"].unique():
        df_item = df_daily[df_daily["Nama Item"] == item].copy()
        all_dates = pd.date_range(
            start=df_item["Tanggal"].min(),
            end=df_item["Tanggal"].max(),
            freq="D",
        )
        df_full = pd.DataFrame({"Tanggal": all_dates})
        df_full = df_full.merge(df_item, on="Tanggal", how="left")
        df_full["Jumlah"]     = df_full["Jumlah"].fillna(0)
        df_full["Keuntungan"] = df_full["Keuntungan"].fillna(0)
        df_full["Nama Item"]  = item
        df_full = df_full[["Tanggal", "Nama Item", "Jumlah", "Keuntungan"]]
        df_full_all.append(df_full)

    df_final = pd.concat(df_full_all, ignore_index=True)
    df_final = df_final.sort_values(["Nama Item", "Tanggal"]).reset_index(drop=True)
    logs.append({
        "tahap":  "Lengkapi Tanggal Kosong",
        "detail": f"Tanggal hilang diisi Jumlah=0, Keuntungan=0. Total baris setelah: {len(df_final)}.",
    })

    # ── Outlier IQR Capping per menu — untuk KEDUA variabel (cell-17) ─────
    df_capped_list = []
    outlier_j = 0
    outlier_k = 0
    for item in df_final["Nama Item"].unique():
        df_item = df_final[df_final["Nama Item"] == item].copy()

        # Jumlah — capping hanya jika ada sebaran (IQR > 0). Jika IQR = 0
        # (data harian sangat sparse sehingga Q1 = Q3 = 0), clip akan
        # menghapus seluruh nilai non-nol. Lewati capping agar data terjaga.
        Q1_j, Q3_j = df_item["Jumlah"].quantile(0.25), df_item["Jumlah"].quantile(0.75)
        IQR_j = Q3_j - Q1_j
        if IQR_j > 0:
            lo_j = math.floor(Q1_j - 1.5 * IQR_j)
            hi_j = math.ceil(Q3_j + 1.5 * IQR_j)
            outlier_j += int(((df_item["Jumlah"] < lo_j) | (df_item["Jumlah"] > hi_j)).sum())
            df_item["Jumlah"] = df_item["Jumlah"].clip(lower=lo_j, upper=hi_j)

        # Keuntungan
        Q1_k, Q3_k = df_item["Keuntungan"].quantile(0.25), df_item["Keuntungan"].quantile(0.75)
        IQR_k = Q3_k - Q1_k
        if IQR_k > 0:
            lo_k = math.floor(Q1_k - 1.5 * IQR_k)
            hi_k = math.ceil(Q3_k + 1.5 * IQR_k)
            outlier_k += int(((df_item["Keuntungan"] < lo_k) | (df_item["Keuntungan"] > hi_k)).sum())
            df_item["Keuntungan"] = df_item["Keuntungan"].clip(lower=lo_k, upper=hi_k)

        df_capped_list.append(df_item)

    df_capped = pd.concat(df_capped_list, ignore_index=True)
    logs.append({
        "tahap":  "Outlier IQR Capping (Jumlah & Keuntungan)",
        "detail": f"Outlier di-cap: Jumlah={outlier_j} baris, Keuntungan={outlier_k} baris.",
    })

    # ── Agregasi total per menu — 2 variabel (cell-19) ────────────────────
    df_total = df_capped.groupby("Nama Item", as_index=False).agg(
        Total_Jumlah=("Jumlah", "sum"),
        Total_Keuntungan=("Keuntungan", "sum"),
    )
    logs.append({
        "tahap":  "Agregasi Total per Menu",
        "detail": (
            f"Total {len(df_total)} menu. "
            f"Total_Jumlah: min={df_total['Total_Jumlah'].min():.1f}, "
            f"max={df_total['Total_Jumlah'].max():.1f}. "
            f"Total_Keuntungan: min=Rp{df_total['Total_Keuntungan'].min():,.0f}, "
            f"max=Rp{df_total['Total_Keuntungan'].max():,.0f}."
        ),
    })

    # ── Feature Scaling MinMax pada 2 variabel (cell-22) ──────────────────
    x_train = df_total[["Total_Jumlah", "Total_Keuntungan"]].values
    scaler  = MinMaxScaler()
    x_train = scaler.fit_transform(x_train)
    logs.append({
        "tahap":  "Feature Scaling (MinMaxScaler)",
        "detail": "MinMaxScaler diterapkan pada [Total_Jumlah, Total_Keuntungan] → rentang [0, 1].",
    })

    sel = select_kmeans(x_train)

    best_k      = sel["best_k"]
    best_sil    = sel["best_sil"]
    sil_scores  = sel["sil_scores"]
    inertias    = sel["inertias"]
    k_range     = sel["k_range"]
    labels_by_k = sel["labels_by_k"]

    logs.append({
        "tahap":  "Penentuan K Optimal (Silhouette Score)",
        "detail": (
            f"K terbaik: {best_k} (Silhouette Score: {best_sil:.4f}). "
            f"Rentang K yang diuji: 2–{max(k_range)}."
        ),
    })

    df_total["Klaster"] = labels_by_k[best_k]
    logs.append({
        "tahap":  "K-Means Clustering",
        "detail": f"K-Means dijalankan: K={best_k}, random_state=42, n_init=10.",
    })

    # ── Cluster summary — rata-rata per klaster (cell-26, 28) ─────────────
    cs = df_total.groupby("Klaster", as_index=False).agg(
        rata_jumlah=("Total_Jumlah", "mean"),
        rata_keuntungan=("Total_Keuntungan", "mean"),
    )
    cs.columns = ["Klaster", "Rata-rata Jumlah Penjualan", "Rata-rata Keuntungan"]

    # ── Kategorisasi berdasarkan Total_Jumlah (cell-31) ───────────────────
    def kategori(j: float) -> str:
        if j >= 400:
            return "Sangat Laris"
        elif j >= 350:
            return "Laris"
        elif j >= 200:
            return "Cukup"
        return "Kurang Laris"

    df_total["Kategori"] = df_total["Total_Jumlah"].apply(kategori)
    logs.append({
        "tahap":  "Kategorisasi Penjualan",
        "detail": (
            "Kategori berdasarkan Total_Jumlah: "
            "Sangat Laris (≥400), Laris (350–399), Cukup (200–349), Kurang Laris (<200)."
        ),
    })

    # ── Laporan hasil clustering (cell-27) ────────────────────────────────
    laporan_clustering = (
        df_total[["Nama Item", "Total_Jumlah", "Total_Keuntungan", "Klaster"]]
        .sort_values(["Klaster", "Total_Keuntungan"], ascending=[True, False])
        .reset_index(drop=True)
    )

    # ── Laporan kategorisasi (cell-32) ────────────────────────────────────
    laporan_kategori = (
        df_total[["Nama Item", "Total_Jumlah", "Total_Keuntungan", "Klaster", "Kategori"]]
        .sort_values(["Kategori", "Total_Jumlah"], ascending=[True, False])
        .reset_index(drop=True)
    )

    # ── Susun output ──────────────────────────────────────────────────────
    cluster_summary_out = cs.to_dict(orient="records")
    for r in cluster_summary_out:
        r["Klaster"]                     = int(r["Klaster"])
        r["Rata-rata Jumlah Penjualan"]  = round(float(r["Rata-rata Jumlah Penjualan"]), 2)
        r["Rata-rata Keuntungan"]        = round(float(r["Rata-rata Keuntungan"]), 2)

    table_rows = laporan_clustering.to_dict(orient="records")
    for r in table_rows:
        r["Total_Jumlah"]     = float(r["Total_Jumlah"])
        r["Total_Keuntungan"] = float(r["Total_Keuntungan"])
        r["Klaster"]          = int(r["Klaster"])

    kategorisasi_rows = laporan_kategori.to_dict(orient="records")
    for r in kategorisasi_rows:
        r["Total_Jumlah"]     = float(r["Total_Jumlah"])
        r["Total_Keuntungan"] = float(r["Total_Keuntungan"])
        r["Klaster"]          = int(r["Klaster"])

    return {
        "status":             "success",
        "best_k":             best_k,
        "silhouette_score":   round(best_sil, 4),
        "total_menu":         int(len(df_total)),
        "date_range":         {"from": str(min_date.date()), "to": str(max_date.date())},
        "preprocessing_logs": logs,
        "table_rows":         table_rows,
        "kategorisasi_rows":  kategorisasi_rows,
        "cluster_summary":    cluster_summary_out,
        "elbow":              {"k": [int(k) for k in k_range], "inertia": [round(float(x), 6) for x in inertias]},
        "silhouette_curve":   {"k": [int(k) for k in k_range], "score": [round(float(x), 6) for x in sil_scores]},
    }


# ── ENDPOINTS ──────────────────────────────────────────────────────────────
@app.get("/health")
def health():
    return {"status": "ok", "service": "W9 Cafe Data Mining API v2"}


# ── Jalankan pipeline berdasarkan tipe (untuk fire-and-forget) ─────────────
def _run_pipeline_for(run_type: str, date_from, date_to) -> dict:
    if run_type == "clustering":
        df = fetch_order_data(date_from, date_to)
        if df.empty:
            raise ValueError("Tidak ada data pesanan selesai pada rentang tanggal yang dipilih.")
        if df["Nama Item"].nunique() < 2:
            raise ValueError("Clustering butuh minimal 2 menu berbeda.")
        return run_pipeline(df)

    if run_type == "prediction":
        df = fetch_order_data(date_from, date_to)
        if df.empty:
            raise ValueError("Tidak ada data pesanan selesai pada rentang tanggal yang dipilih.")
        if df["Tanggal"].nunique() < 3:
            raise ValueError("Data terlalu sedikit: butuh minimal 3 hari transaksi.")
        return run_prediction_pipeline(df)

    if run_type == "association":
        df = fetch_association_data(date_from, date_to)
        if df.empty:
            raise ValueError("Tidak ada data pesanan pada rentang tanggal tersebut.")
        return run_association_pipeline(df)

    if run_type == "clustering-bahan-baku":
        df = fetch_ingredient_data(date_from, date_to)
        if df.empty:
            raise ValueError("Tidak ada data pemakaian bahan baku pada rentang tanggal yang dipilih.")
        return run_bahan_baku_pipeline(df)

    if run_type == "prediction-bahan-baku":
        df = fetch_ingredient_data(date_from, date_to)
        if df.empty:
            raise ValueError("Tidak ada data pemakaian bahan baku pada rentang tanggal yang dipilih.")
        if df["Tanggal"].nunique() < 3:
            raise ValueError("Data terlalu sedikit: butuh minimal 3 hari data.")
        return run_prediction_pipeline_bahan_baku(df)

    raise ValueError(f"Tipe data mining tidak dikenal: {run_type}")


def _update_run(run_id: int, status: str, payload: Optional[dict] = None, error: Optional[str] = None) -> None:
    conn = get_connection()
    try:
        cur = conn.cursor()
        cur.execute(
            """UPDATE datamining_runs
               SET status = %s,
                   payload = %s::json,
                   error = %s,
                   updated_at = NOW()
               WHERE id = %s""",
            (status, json.dumps(payload, default=str) if payload is not None else None, error, run_id),
        )
        conn.commit()
    finally:
        conn.close()


@app.post("/run")
async def run(request: Request):
    try:
        body = await request.json()
    except Exception:
        body = {}

    run_id    = body.get("run_id")
    run_type  = (body.get("type") or "").strip()
    date_from = (body.get("date_from") or "").strip() or None
    date_to   = (body.get("date_to")   or "").strip() or None

    if not run_id or not run_type:
        return {"status": "error", "message": "run_id dan type wajib diisi."}

    try:
        result = _run_pipeline_for(run_type, date_from, date_to)
        _update_run(run_id, "completed", payload=result)
        return {"status": "ok", "run_id": run_id}
    except Exception as e:
        _update_run(run_id, "failed", error=str(e)[:2000])
        return {"status": "error", "run_id": run_id, "message": str(e)}
