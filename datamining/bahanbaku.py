"""
K-Means Clustering — Bahan Baku
W9 Cafe POS | Capstone STIE Totalwin

Pipeline mengikuti notebook: Revisi_Bahan_Baku_FIX_KMEANS_Preprocessing_Clustering_(Stok_Bahan_Baku).ipynb
"""

import math, warnings
import numpy as np
import pandas as pd

from sklearn.preprocessing import MinMaxScaler

try:
    from .kmeans_utils import select_kmeans
except ImportError:
    from kmeans_utils import select_kmeans

try:
    from .prediction import _fill_missing_dates, _cap_iqr
except ImportError:
    from prediction import _fill_missing_dates, _cap_iqr

warnings.filterwarnings("ignore")


def run_bahan_baku_pipeline(df: pd.DataFrame) -> dict:
    """
    Pipeline K-Means clustering bahan baku sesuai notebook cells 3–36.
    Input : DataFrame dengan kolom: Tanggal, Bahan_Baku, Unit, Jumlah_Digunakan
    Output: dict JSON-serializable
    """
    logs = []

    # ── Cell 3: pilih kolom yang dibutuhkan ───────────────────────────
    df_select = df[["Tanggal", "Bahan_Baku", "Jumlah_Digunakan"]].copy()

    # C3: peta satuan sekali (menghindari filter df per baris di output).
    unit_map = (
        df.groupby("Bahan_Baku")["Unit"].first().to_dict()
        if "Unit" in df.columns else {}
    )

    # ── Cell 4: parse Tanggal ──────────────────────────────────────────
    df_select["Tanggal"] = pd.to_datetime(df_select["Tanggal"])

    # ── Cell 8: urutkan berdasarkan tanggal ───────────────────────────
    df_sorted = df_select.sort_values("Tanggal", ascending=True)

    date_from = str(df_sorted["Tanggal"].min().date())
    date_to   = str(df_sorted["Tanggal"].max().date())

    # ── Cell 9: agregasi harian per bahan baku ─────────────────────────
    df_total = df_sorted.groupby(["Tanggal", "Bahan_Baku"], as_index=False)["Jumlah_Digunakan"].sum()

    # ── Cell 10: tambahkan Day_Type ────────────────────────────────────
    df_total["Day_Type"] = np.where(
        df_total["Tanggal"].dt.dayofweek >= 5, "Weekend", "Weekday"
    )
    logs.append({
        "tahap":  "Agregasi Harian",
        "detail": (
            f"Diagregasi per Tanggal × Bahan Baku. "
            f"Baris: {len(df_total)}, bahan baku unik: {df_total['Bahan_Baku'].nunique()}."
        ),
    })

    # ── Cell 13: lengkapi tanggal yang hilang (0) ──────────────────────
    # C2: date-fill vektorisasi menggantikan loop per bahan baku.
    df_final = _fill_missing_dates(df_total, "Bahan_Baku", "Jumlah_Digunakan")

    # ── Cell 15: Day_Type ulang setelah lengkapi ───────────────────────
    df_final["Day_Type"] = np.where(
        df_final["Tanggal"].dt.dayofweek >= 5, "Weekend", "Weekday"
    )
    logs.append({
        "tahap":  "Lengkapi Tanggal Kosong",
        "detail": f"Tanggal hilang diisi Jumlah_Digunakan=0. Total baris: {len(df_final)}.",
    })

    # ── Cell 18: IQR Capping per bahan baku ───────────────────────────
    # C2: groupby.quantile + np.where menggantikan loop per bahan baku.
    df_capped, outlier_total = _cap_iqr(df_final, "Bahan_Baku", "Jumlah_Digunakan")
    logs.append({
        "tahap":  "Outlier IQR Capping (per Bahan Baku)",
        "detail": f"Total nilai outlier di-cap: {outlier_total} baris.",
    })

    # ── Cell 20: agregasi total per bahan baku ─────────────────────────
    df_total2 = df_capped.groupby("Bahan_Baku", as_index=False)["Jumlah_Digunakan"].sum()

    # ── Cells 22-24: IQR Capping pada total agregat ────────────────────
    Q1 = df_total2["Jumlah_Digunakan"].quantile(0.25)
    Q3 = df_total2["Jumlah_Digunakan"].quantile(0.75)
    IQR = Q3 - Q1

    df_capped2 = df_total2.copy()
    n_out_total = 0
    if IQR > 0:
        lower_bound = Q1 - 1.5 * IQR
        upper_bound = Q3 + 1.5 * IQR
        df_capped2["Jumlah_Digunakan"] = df_capped2["Jumlah_Digunakan"].clip(
            lower=lower_bound, upper=upper_bound
        )
        n_out_total = int(
            ((df_total2["Jumlah_Digunakan"] < lower_bound) | (df_total2["Jumlah_Digunakan"] > upper_bound)).sum()
        )
    logs.append({
        "tahap":  "Outlier IQR Capping (Total Agregat)",
        "detail": f"Outlier total agregat di-cap: {n_out_total} bahan baku.",
    })

    # ── Cell 25-26: Feature Scaling (MinMaxScaler) ─────────────────────
    x_train = df_capped2["Jumlah_Digunakan"].values.reshape(-1, 1)
    scaler  = MinMaxScaler()
    x_train = scaler.fit_transform(x_train)
    logs.append({
        "tahap":  "Feature Scaling",
        "detail": f"MinMaxScaler diterapkan pada {len(df_capped2)} bahan baku.",
    })

    # ── Cell 28: Silhouette Score — tentukan best_k ────────────────────
    n_items = len(x_train)
    if n_items < 2:
        return {
            "status":  "error",
            "message": (
                f"Data tidak cukup untuk clustering. "
                f"Hanya ditemukan {n_items} bahan baku unik. "
                "Minimal diperlukan 2 bahan baku."
            ),
        }

    sel = select_kmeans(x_train)

    best_k            = sel["best_k"]
    best_sil          = sel["best_sil"]
    silhouette_scores = sel["sil_scores"]
    inertias          = sel["inertias"]
    k_range           = sel["k_range"]
    k_range_e         = k_range
    labels_by_k       = sel["labels_by_k"]

    logs.append({
        "tahap":  "Penentuan K Optimal (Silhouette)",
        "detail": f"K terbaik: {best_k} (Silhouette Score: {best_sil:.4f}). Range K: 2–{max(k_range)}.",
    })

    df_capped2["Klaster"] = labels_by_k[best_k]

    logs.append({
        "tahap":  "K-Means Clustering",
        "detail": f"K-Means dijalankan: K={best_k}, random_state=42, n_init=10.",
    })

    # ── Cell 32: urutkan berdasarkan Jumlah_Digunakan ──────────────────
    df_bbaku = df_capped2.sort_values(by="Jumlah_Digunakan")

    # ── Cell 33: laporan klasterisasi (Klaster asc, Jumlah desc) ───────
    df_laporan = (
        df_capped2
        .sort_values(by=["Klaster", "Jumlah_Digunakan"], ascending=[True, False])
        .reset_index(drop=True)
    )
    df_laporan.index = df_laporan.index + 1

    # ── Cell 34: rata-rata per klaster (sort desc) ─────────────────────
    rata_rata_cluster = (
        df_capped2
        .groupby("Klaster")["Jumlah_Digunakan"]
        .mean()
        .reset_index()
    )
    rata_rata_cluster.columns = ["Klaster", "Rata-rata Jumlah Penggunaan"]
    rata_rata_cluster = rata_rata_cluster.sort_values(
        by="Rata-rata Jumlah Penggunaan", ascending=False
    ).reset_index(drop=True)

    logs.append({
        "tahap":  "Analisis Rata-rata per Klaster",
        "detail": "Rata-rata jumlah penggunaan dihitung per klaster, diurutkan dari tertinggi ke terendah.",
    })


    # ── Build table_rows (Klaster asc, Jumlah desc) ────────────────────
    table_rows = [
        {
            "Nama Bahan Baku":  row["Bahan_Baku"],
            "Satuan":           str(unit_map.get(row["Bahan_Baku"], "")),
            "Total Penggunaan": float(row["Jumlah_Digunakan"]),
            "Klaster":          int(row["Klaster"]),
        }
        for _, row in df_laporan.iterrows()
    ]

    # ── Build rata_rata_table ──────────────────────────────────────────
    rata_rata_table = [
        {
            "Klaster":                      int(row["Klaster"]),
            "Rata-rata Jumlah Penggunaan":  round(float(row["Rata-rata Jumlah Penggunaan"]), 2),
        }
        for _, row in rata_rata_cluster.iterrows()
    ]

    # ── Clusters output per klaster (untuk summary cards) ─────────────
    clusters_out = []
    for klaster_id in sorted(df_capped2["Klaster"].unique()):
        subset = df_capped2[df_capped2["Klaster"] == klaster_id].sort_values(
            "Jumlah_Digunakan", ascending=False
        )
        clusters_out.append({
            "klaster":     int(klaster_id),
            "count":       len(subset),
            "total_usage": float(subset["Jumlah_Digunakan"].sum()),
            "avg_usage":   round(float(subset["Jumlah_Digunakan"].mean()), 1),
            "ingredients": [
                {
                    "name":   r["Bahan_Baku"],
                    "unit":   unit_map.get(r["Bahan_Baku"], ""),
                    "jumlah": float(r["Jumlah_Digunakan"]),
                }
                for _, r in subset.iterrows()
            ],
        })

    return {
        "status":             "success",
        "best_k":             best_k,
        "silhouette_score":   round(best_sil, 4),
        "total_ingredients":  int(len(df_capped2)),
        "date_range":         {"from": date_from, "to": date_to},
        "clusters":           clusters_out,
        "table_rows":         table_rows,
        "rata_rata_table":    rata_rata_table,
        "preprocessing_logs": logs,
        "elbow":              {"k": [int(k) for k in k_range_e], "inertia": [round(float(x), 6) for x in inertias]},
        "silhouette_curve":   {"k": [int(k) for k in k_range], "score": [round(float(x), 6) for x in silhouette_scores]},
    }
