"""
FastAPI — Data Mining API
W9 Cafe POS | Capstone STIE Totalwin

Endpoints:
  GET  /health          — cek status server
  GET  /preview-data    — preview data dari DB
  POST /clustering      — K-Means Clustering Penjualan Menu
  POST /prediction      — Time Series Prediction per Menu (Prophet)
"""

import io, os, base64, warnings
import numpy as np
import pandas as pd
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt
import seaborn as sns

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from dotenv import load_dotenv
import psycopg2
from psycopg2.extras import RealDictCursor
from sklearn.preprocessing import MinMaxScaler
from sklearn.cluster import KMeans
from sklearn.metrics import silhouette_score

try:
    from .prediction  import run_prediction_pipeline   # saat dijalankan sebagai package (datamining.api:app)
    from .association import run_association_pipeline
    from .bahanbaku   import run_bahan_baku_pipeline
except ImportError:
    from prediction  import run_prediction_pipeline    # saat dijalankan dari folder datamining/ (api:app)
    from association import run_association_pipeline
    from bahanbaku   import run_bahan_baku_pipeline

warnings.filterwarnings("ignore")
load_dotenv()

app = FastAPI(title="W9 Cafe — Data Mining API", version="1.0.0")
app.add_middleware(CORSMiddleware, allow_origins=["*"], allow_methods=["*"], allow_headers=["*"])


# ── Koneksi DB ─────────────────────────────────────────────────────────────
def get_connection():
    return psycopg2.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", 5432)),
        dbname=os.getenv("DB_NAME", "pos_cafe"),
        user=os.getenv("DB_USER", "postgres"),
        password=os.getenv("DB_PASSWORD", ""),
        cursor_factory=RealDictCursor,
    )


# ── Ambil data dari DB (setara read_excel di notebook) ────────────────────
def fetch_order_data() -> pd.DataFrame:
    sql = """
        SELECT
            o.created_at::date   AS "Tanggal",
            o.order_code         AS "Order_id",
            m.name               AS "Nama Item",
            oi.quantity          AS "Jumlah",
            oi.unit_price::float AS "Harga",
            oi.subtotal::float   AS "Subtotal"
        FROM order_items oi
        JOIN orders o ON o.id  = oi.order_id
        JOIN menus  m ON m.id  = oi.menu_id
        WHERE o.is_paid = true
        ORDER BY o.created_at ASC
    """
    conn = get_connection()
    try:
        with conn.cursor() as cur:
            cur.execute(sql)
            rows = cur.fetchall()
    finally:
        conn.close()

    df = pd.DataFrame([dict(r) for r in rows])
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])
    return df


# ── Helper: fig → base64 PNG ───────────────────────────────────────────────
def fig_to_base64(fig) -> str:
    buf = io.BytesIO()
    fig.savefig(buf, format="png", bbox_inches="tight", dpi=120)
    buf.seek(0)
    b64 = base64.b64encode(buf.read()).decode()
    plt.close(fig)
    return b64


# ── PIPELINE (mengikuti notebook sel per sel) ─────────────────────────────
def run_pipeline(df: pd.DataFrame) -> dict:
    logs = []

    # Cell 3-4: parse Tanggal
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])

    # Cell 5: Isi Jumlah kosong dari Subtotal / Harga
    before = int(df["Jumlah"].isna().sum())
    mask = df["Jumlah"].isna() & df["Subtotal"].notna() & (df["Harga"] != 0)
    df.loc[mask, "Jumlah"] = (df.loc[mask, "Subtotal"] / df.loc[mask, "Harga"]).round()
    after = int(df["Jumlah"].isna().sum())
    logs.append({"tahap": "Imputasi Jumlah",
                 "detail": f"Missing sebelum: {before}, sesudah: {after}. Diisi {before-after} baris dari Subtotal/Harga."})

    # Drop baris yang masih kosong
    before = len(df)
    df = df.dropna(subset=["Jumlah", "Nama Item"])
    logs.append({"tahap": "Drop Missing Value",
                 "detail": f"Dihapus {before - len(df)} baris. Sisa: {len(df)} baris."})

    # Cell 7: Hapus duplikat
    before = len(df)
    df = df.drop_duplicates(subset=["Tanggal", "Order_id", "Nama Item", "Jumlah", "Harga"])
    logs.append({"tahap": "Hapus Duplikat",
                 "detail": f"Dihapus {before - len(df)} duplikat. Sisa: {len(df)} baris."})

    # Cell 8: Urutkan tanggal
    df_sorted = df.sort_values("Tanggal", ascending=True)

    # Cell 9: Agregasi harian per menu
    df_total = df_sorted.groupby(["Tanggal", "Nama Item"], as_index=False)["Jumlah"].sum()

    # Cell 10: Day_Type
    df_total["Day_Type"] = df_total["Tanggal"].dt.dayofweek.apply(
        lambda x: "Weekend" if x >= 5 else "Weekday"
    )
    logs.append({"tahap": "Agregasi Harian",
                 "detail": f"Diagregasi per Tanggal×Menu. Baris: {len(df_total)}, menu unik: {df_total['Nama Item'].nunique()}."})

    # Cell 12-13: Lengkapi tanggal yang hilang (reindex per menu → isi 0)
    df_total["Tanggal"] = pd.to_datetime(df_total["Tanggal"])
    min_date = df_total["Tanggal"].min()
    max_date = df_total["Tanggal"].max()
    full_dates = pd.date_range(start=min_date, end=max_date, freq="D")

    df_full_all = []
    for item in df_total["Nama Item"].unique():
        df_item = df_total[df_total["Nama Item"] == item].set_index("Tanggal")
        df_item = df_item.reindex(full_dates)
        df_item["Nama Item"] = item
        df_item["Jumlah"] = df_item["Jumlah"].fillna(0)
        df_item = df_item.reset_index().rename(columns={"index": "Tanggal"})
        df_full_all.append(df_item)

    df_final = pd.concat(df_full_all, ignore_index=True)

    # Cell 15: Day_Type ulang
    df_final["Day_Type"] = df_final["Tanggal"].dt.dayofweek.apply(
        lambda x: "Weekend" if x >= 5 else "Weekday"
    )
    logs.append({"tahap": "Lengkapi Tanggal Kosong",
                 "detail": f"Tanggal hilang diisi Jumlah=0. Total baris: {len(df_final)}."})

    # Cell 18: Outlier IQR Capping per menu
    df_result_list = []
    outlier_total = 0
    for item in df_final["Nama Item"].unique():
        df_item = df_final[df_final["Nama Item"] == item].copy()
        Q1 = df_item["Jumlah"].quantile(0.25)
        Q3 = df_item["Jumlah"].quantile(0.75)
        IQR = Q3 - Q1
        lower = Q1 - 1.5 * IQR
        upper = Q3 + 1.5 * IQR
        outlier_total += int(((df_item["Jumlah"] < lower) | (df_item["Jumlah"] > upper)).sum())
        df_item["Jumlah"] = df_item["Jumlah"].clip(lower=lower, upper=upper)
        df_result_list.append(df_item)

    df_capped = pd.concat(df_result_list, ignore_index=True)
    logs.append({"tahap": "Outlier IQR Capping",
                 "detail": f"Total nilai outlier di-cap: {outlier_total} baris."})

    # Cell 20: Agregasi total per menu (kolom tetap 'Jumlah' sesuai notebook)
    df_total = df_capped.groupby("Nama Item", as_index=False)["Jumlah"].sum()

    # Cell 21-22: Feature Scaling
    x_train = df_total["Jumlah"].values.reshape(-1, 1)
    scaler  = MinMaxScaler()
    x_train = scaler.fit_transform(x_train)

    # Cell 24: Tentukan best_k dengan Silhouette Score
    silhouette_scores = []
    k_range = range(2, min(10, len(x_train)))
    for k in k_range:
        km     = KMeans(n_clusters=k, random_state=42, n_init=10)
        labels = km.fit_predict(x_train)
        silhouette_scores.append(silhouette_score(x_train, labels))

    best_k   = list(k_range)[int(np.argmax(silhouette_scores))]
    best_sil = max(silhouette_scores)
    logs.append({"tahap": "Penentuan K Optimal (Silhouette)",
                 "detail": f"K terbaik: {best_k} (Silhouette Score: {best_sil:.4f}). Range K: 2–{max(k_range)}."})

    # Elbow (untuk grafik saja)
    inertias = []
    for k in k_range:
        km = KMeans(n_clusters=k, random_state=42, n_init=10)
        km.fit(x_train)
        inertias.append(km.inertia_)

    # Cell 25-27: Training + label klaster
    kmean = KMeans(n_clusters=best_k, random_state=42, n_init=10)
    df_total["Klaster"] = kmean.fit_predict(x_train)

    # Cell 28-30: Label otomatis berdasarkan rata-rata per klaster
    cluster_avg = df_total.groupby("Klaster")["Jumlah"].mean().sort_values(ascending=False)
    labels_list = ["Sangat Laris", "Laris", "Cukup Laris", "Kurang Laris", "Tidak Laris"]
    cluster_label = {}
    for i, cluster_id in enumerate(cluster_avg.index):
        cluster_label[cluster_id] = labels_list[i] if i < len(labels_list) else f"Cluster {cluster_id}"

    df_total["Kategori"] = df_total["Klaster"].map(cluster_label)

    logs.append({"tahap": "Labeling Klaster",
                 "detail": "Pemetaan: " + ", ".join([f"Klaster {k} → {v}" for k, v in cluster_label.items()])})

    # Cell 37: df_clustering = df_total.sort_values(by='Jumlah')
    df_clustering = df_total.sort_values(by="Jumlah").reset_index(drop=True)

    # Cell 39: df_result
    df_result = df_clustering[["Nama Item", "Jumlah", "Klaster", "Kategori"]].copy()

    # ── Style global matplotlib ────────────────────────────────────────
    plt.rcParams.update({
        "font.family":   "DejaVu Sans",
        "axes.spines.top":    False,
        "axes.spines.right":  False,
        "axes.titlesize":     14,
        "axes.titleweight":   "bold",
        "axes.titlepad":      16,
        "axes.labelsize":     12,
        "axes.labelpad":      8,
        "xtick.labelsize":    10,
        "ytick.labelsize":    10,
        "legend.fontsize":    10,
        "legend.title_fontsize": 11,
        "figure.facecolor":   "white",
        "axes.facecolor":     "#fafafa",
        "axes.grid":          True,
        "grid.color":         "#e5e7eb",
        "grid.linewidth":     0.8,
    })

    # ── VISUALISASI 1: Bar chart (Cell 38) — diperbesar ───────────────
    kategori_order = ["Tidak Laris", "Kurang Laris", "Cukup Laris", "Laris", "Sangat Laris"]
    palette_map = {
        "Sangat Laris": "#16a34a",
        "Laris":        "#2563eb",
        "Cukup Laris":  "#d97706",
        "Kurang Laris": "#dc2626",
        "Tidak Laris":  "#9ca3af",
    }
    present_cats    = [c for c in kategori_order if c in df_clustering["Kategori"].unique()]
    palette_ordered = [palette_map[c] for c in present_cats]

    n_menu  = len(df_clustering)
    fig_w   = max(14, n_menu * 0.85)
    fig1, ax1 = plt.subplots(figsize=(fig_w, 7))
    sns.barplot(
        data=df_clustering,
        x="Nama Item",
        y="Jumlah",
        hue="Kategori",
        hue_order=present_cats,
        palette=palette_ordered,
        ax=ax1,
        dodge=False,
        width=0.6,
    )
    # Label nilai di atas setiap bar
    for bar in ax1.patches:
        h = bar.get_height()
        if h > 0:
            ax1.text(
                bar.get_x() + bar.get_width() / 2,
                h + 0.8,
                f"{h:.1f}",
                ha="center", va="bottom",
                fontsize=9, color="#374151",
            )
    ax1.set_title("Visualisasi Clustering Penjualan Menu", pad=18)
    ax1.set_xlabel("Nama Menu", labelpad=10)
    ax1.set_ylabel("Jumlah Penjualan (unit)", labelpad=10)
    ax1.tick_params(axis="x", rotation=40)
    ax1.set_xticklabels(ax1.get_xticklabels(), ha="right")
    ax1.legend(title="Kategori", loc="upper left", framealpha=0.9,
               edgecolor="#e5e7eb", fancybox=False)
    ax1.yaxis.grid(True); ax1.xaxis.grid(False)
    fig1.tight_layout(pad=2)
    chart_bar = fig_to_base64(fig1)

    # ── VISUALISASI 2: Elbow curve — diperbesar ────────────────────────
    fig2, ax2 = plt.subplots(figsize=(9, 5))
    ax2.plot(list(k_range), inertias,
             marker="o", markersize=8, color="#6366f1",
             linewidth=2.5, markerfacecolor="white", markeredgewidth=2)
    ax2.axvline(x=best_k, color="#ef4444", linestyle="--",
                linewidth=1.8, alpha=0.8, label=f"K optimal = {best_k}")
    # Annotate best_k point
    best_inertia = inertias[list(k_range).index(best_k)]
    ax2.annotate(f"  K={best_k}\n  Inertia={best_inertia:.4f}",
                 xy=(best_k, best_inertia),
                 xytext=(best_k + 0.4, best_inertia + (max(inertias) - min(inertias)) * 0.08),
                 fontsize=9, color="#ef4444",
                 arrowprops=dict(arrowstyle="->", color="#ef4444", lw=1.2))
    ax2.set_xlabel("Jumlah Klaster (K)")
    ax2.set_ylabel("Inertia (Sum of Squared Error)")
    ax2.set_title("Elbow Method — Penentuan Jumlah Klaster Optimal")
    ax2.set_xticks(list(k_range))
    ax2.legend(framealpha=0.9, edgecolor="#e5e7eb", fancybox=False)
    fig2.tight_layout(pad=2)
    chart_elbow = fig_to_base64(fig2)

    # ── VISUALISASI 3: Silhouette Score per K — diperbesar ─────────────
    fig3, ax3 = plt.subplots(figsize=(9, 5))
    ax3.plot(list(k_range), silhouette_scores,
             marker="s", markersize=8, color="#059669",
             linewidth=2.5, markerfacecolor="white", markeredgewidth=2)
    ax3.axvline(x=best_k, color="#ef4444", linestyle="--",
                linewidth=1.8, alpha=0.8, label=f"K optimal = {best_k}")
    # Annotate best silhouette
    ax3.annotate(f"  K={best_k}\n  Score={best_sil:.4f}",
                 xy=(best_k, best_sil),
                 xytext=(best_k + 0.4, best_sil - (max(silhouette_scores) - min(silhouette_scores)) * 0.15),
                 fontsize=9, color="#ef4444",
                 arrowprops=dict(arrowstyle="->", color="#ef4444", lw=1.2))
    ax3.set_xlabel("Jumlah Klaster (K)")
    ax3.set_ylabel("Silhouette Score")
    ax3.set_title("Silhouette Score per K — Kualitas Pengelompokan")
    ax3.set_xticks(list(k_range))
    ax3.legend(framealpha=0.9, edgecolor="#e5e7eb", fancybox=False)
    fig3.tight_layout(pad=2)
    chart_silhouette = fig_to_base64(fig3)

    # ── Susun output per klaster ───────────────────────────────────────
    clusters_out = []
    for label in ["Sangat Laris", "Laris", "Cukup Laris", "Kurang Laris", "Tidak Laris"]:
        subset = df_result[df_result["Kategori"] == label].sort_values("Jumlah", ascending=False)
        if subset.empty:
            continue
        clusters_out.append({
            "label":      label,
            "count":      len(subset),
            "total_sales": float(subset["Jumlah"].sum()),
            "avg_sales":  round(float(subset["Jumlah"].mean()), 1),
            "menus": [
                {
                    "name":     row["Nama Item"],
                    "jumlah":   float(row["Jumlah"]),
                    "klaster":  int(row["Klaster"]),
                }
                for _, row in subset.iterrows()
            ],
        })

    # df_result sebagai tabel baris (untuk ditampilkan persis seperti notebook)
    table_rows = df_result.to_dict(orient="records")
    for r in table_rows:
        r["Jumlah"]  = float(r["Jumlah"])
        r["Klaster"] = int(r["Klaster"])

    return {
        "status":             "success",
        "best_k":             best_k,
        "silhouette_score":   round(best_sil, 4),
        "total_menu":         int(len(df_total)),
        "date_range":         {"from": str(min_date.date()), "to": str(max_date.date())},
        "preprocessing_logs": logs,
        "table_rows":         table_rows,   # ← persis df_result dari notebook
        "clusters":           clusters_out,
        "charts": {
            "bar":        chart_bar,
            "elbow":      chart_elbow,
            "silhouette": chart_silhouette,
        },
    }


# ── ENDPOINTS ──────────────────────────────────────────────────────────────
@app.get("/health")
def health():
    return {"status": "ok", "service": "W9 Cafe Data Mining API"}

@app.get("/preview-data")
def preview_data():
    try:
        df = fetch_order_data()
        return {
            "total_rows": len(df),
            "total_menu": df["Nama Item"].nunique(),
            "date_range": {"from": str(df["Tanggal"].min().date()), "to": str(df["Tanggal"].max().date())},
            "sample":     df.head(10).to_dict(orient="records"),
        }
    except Exception as e:
        return {"status": "error", "message": str(e)}

@app.post("/clustering")
def clustering():
    try:
        df = fetch_order_data()
        if df.empty:
            return {"status": "error", "message": "Tidak ada data pesanan yang sudah dibayar."}
        return run_pipeline(df)
    except Exception as e:
        import traceback
        return {"status": "error", "message": str(e), "trace": traceback.format_exc()}


@app.post("/prediction")
def prediction():
    try:
        df = fetch_order_data()
        if df.empty:
            return {"status": "error", "message": "Tidak ada data pesanan yang sudah dibayar."}
        return run_prediction_pipeline(df)
    except Exception as e:
        import traceback
        return {"status": "error", "message": str(e), "trace": traceback.format_exc()}


@app.post("/association")
def association():
    try:
        df = fetch_order_data()
        if df.empty:
            return {"status": "error", "message": "Tidak ada data pesanan yang sudah dibayar."}
        return run_association_pipeline(df)
    except Exception as e:
        import traceback
        return {"status": "error", "message": str(e), "trace": traceback.format_exc()}


def fetch_ingredient_data() -> pd.DataFrame:
    sql = """
        SELECT
            diu.usage_date::date  AS "Tanggal",
            diu.ingredient_name   AS "Bahan_Baku",
            diu.unit              AS "Unit",
            diu.jumlah_digunakan::float AS "Jumlah_Digunakan"
        FROM daily_ingredient_usages diu
        ORDER BY diu.usage_date ASC
    """
    conn = get_connection()
    try:
        with conn.cursor() as cur:
            cur.execute(sql)
            rows = cur.fetchall()
    finally:
        conn.close()

    df = pd.DataFrame([dict(r) for r in rows])
    if not df.empty:
        df["Tanggal"] = pd.to_datetime(df["Tanggal"])
    return df


@app.post("/clustering-bahan-baku")
def clustering_bahan_baku():
    try:
        df = fetch_ingredient_data()
        if df.empty:
            return {"status": "error", "message": "Tidak ada data pemakaian bahan baku."}
        return run_bahan_baku_pipeline(df)
    except Exception as e:
        import traceback
        return {"status": "error", "message": str(e), "trace": traceback.format_exc()}

