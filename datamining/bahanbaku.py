"""
K-Means Clustering — Bahan Baku
W9 Cafe POS | Capstone STIE Totalwin

Pipeline mengikuti notebook: Bahan Baku FIX KMEANS Preprocessing_Clustering.ipynb
"""

import io, base64, math, warnings
import numpy as np
import pandas as pd
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt
import seaborn as sns

from sklearn.preprocessing import MinMaxScaler
from sklearn.cluster import KMeans
from sklearn.metrics import silhouette_score

warnings.filterwarnings("ignore")

LABELS_LIST = [
    "Sangat Banyak Digunakan",
    "Banyak Digunakan",
    "Cukup Digunakan",
    "Sedikit Digunakan",
    "Paling Sedikit Digunakan",
]


def _to_b64(fig) -> str:
    buf = io.BytesIO()
    fig.savefig(buf, format="png", bbox_inches="tight", dpi=120)
    buf.seek(0)
    enc = base64.b64encode(buf.read()).decode()
    plt.close(fig)
    return enc


def run_bahan_baku_pipeline(df: pd.DataFrame) -> dict:
    """
    Pipeline K-Means clustering bahan baku sesuai notebook cells 3–43.
    Input : DataFrame dengan kolom: Tanggal, Bahan_Baku, Unit, Jumlah_Digunakan
    Output: dict JSON-serializable
    """
    logs = []

    # ── Cell 3: pilih kolom yang dibutuhkan ───────────────────────────
    df_select = df[["Tanggal", "Bahan_Baku", "Jumlah_Digunakan"]].copy()

    # ── Cell 4: parse Tanggal ──────────────────────────────────────────
    df_select["Tanggal"] = pd.to_datetime(df_select["Tanggal"])

    # ── Cell 6: drop NaN ──────────────────────────────────────────────
    before = len(df_select)
    df_select = df_select.dropna(subset=["Tanggal", "Bahan_Baku", "Jumlah_Digunakan"])
    logs.append({
        "tahap":  "Hapus Missing Value",
        "detail": f"Dihapus {before - len(df_select)} baris NaN. Sisa: {len(df_select)} baris.",
    })

    # ── Cell 7: hapus duplikat ─────────────────────────────────────────
    before = len(df_select)
    dup = df_select.duplicated(subset=["Tanggal", "Bahan_Baku", "Jumlah_Digunakan"]).sum()
    if dup > 0:
        df_select = df_select.drop_duplicates(subset=["Tanggal", "Bahan_Baku", "Jumlah_Digunakan"])
    logs.append({
        "tahap":  "Hapus Duplikat",
        "detail": (
            f"Duplikat ditemukan: {dup} baris, sudah dihapus."
            if dup > 0 else "Tidak ada data duplikat."
        ),
    })

    # ── Cell 8: urutkan berdasarkan tanggal ───────────────────────────
    df_sorted = df_select.sort_values("Tanggal", ascending=True)

    date_from = str(df_sorted["Tanggal"].min().date())
    date_to   = str(df_sorted["Tanggal"].max().date())

    # ── Cell 9: agregasi harian per bahan baku ─────────────────────────
    df_total = df_sorted.groupby(["Tanggal", "Bahan_Baku"], as_index=False)["Jumlah_Digunakan"].sum()

    # ── Cell 10: tambahkan Day_Type ────────────────────────────────────
    df_total["Day_Type"] = df_total["Tanggal"].dt.dayofweek.apply(
        lambda x: "Weekend" if x >= 5 else "Weekday"
    )
    logs.append({
        "tahap":  "Agregasi Harian",
        "detail": (
            f"Diagregasi per Tanggal × Bahan Baku. "
            f"Baris: {len(df_total)}, bahan baku unik: {df_total['Bahan_Baku'].nunique()}."
        ),
    })

    # ── Cell 13: lengkapi tanggal yang hilang (0) ──────────────────────
    df_total["Tanggal"] = pd.to_datetime(df_total["Tanggal"])
    df_full_all = []
    for item in df_total["Bahan_Baku"].unique():
        df_item = df_total[df_total["Bahan_Baku"] == item].copy()
        all_dates = pd.date_range(df_item["Tanggal"].min(), df_item["Tanggal"].max(), freq="D")
        df_full = pd.DataFrame({"Tanggal": all_dates})
        df_full = df_full.merge(df_item, on="Tanggal", how="left")
        df_full["Jumlah_Digunakan"] = df_full["Jumlah_Digunakan"].fillna(0)
        df_full["Bahan_Baku"] = item
        df_full = df_full[["Tanggal", "Bahan_Baku", "Jumlah_Digunakan"]]
        df_full_all.append(df_full)

    df_final = pd.concat(df_full_all, ignore_index=True)
    df_final = df_final.sort_values(["Bahan_Baku", "Tanggal"]).reset_index(drop=True)

    # ── Cell 15: Day_Type ulang ────────────────────────────────────────
    df_final["Day_Type"] = df_final["Tanggal"].dt.dayofweek.apply(
        lambda x: "Weekend" if x >= 5 else "Weekday"
    )
    logs.append({
        "tahap":  "Lengkapi Tanggal Kosong",
        "detail": f"Tanggal hilang diisi Jumlah_Digunakan=0. Total baris: {len(df_final)}.",
    })

    # ── Cell 18: IQR Capping per bahan baku ───────────────────────────
    df_result_list = []
    outlier_total = 0
    for item in df_final["Bahan_Baku"].unique():
        df_item = df_final[df_final["Bahan_Baku"] == item].copy()
        Q1 = df_item["Jumlah_Digunakan"].quantile(0.25)
        Q3 = df_item["Jumlah_Digunakan"].quantile(0.75)
        IQR = Q3 - Q1
        lower = math.floor(Q1 - 1.5 * IQR)
        upper = math.ceil(Q3 + 1.5 * IQR)
        n_out = int(((df_item["Jumlah_Digunakan"] < lower) | (df_item["Jumlah_Digunakan"] > upper)).sum())
        outlier_total += n_out
        df_item["Jumlah_Digunakan"] = np.where(
            df_item["Jumlah_Digunakan"] > upper, upper,
            np.where(df_item["Jumlah_Digunakan"] < lower, lower, df_item["Jumlah_Digunakan"])
        )
        df_result_list.append(df_item)

    df_capped = pd.concat(df_result_list, ignore_index=True)
    logs.append({
        "tahap":  "Outlier IQR Capping (per Bahan Baku)",
        "detail": f"Total nilai outlier di-cap: {outlier_total} baris.",
    })

    # ── Cell 20: agregasi total per bahan baku ─────────────────────────
    df_total2 = df_capped.groupby("Bahan_Baku", as_index=False)["Jumlah_Digunakan"].sum()

    # ── Cells 22-24: IQR Capping pada total ───────────────────────────
    Q1 = df_total2["Jumlah_Digunakan"].quantile(0.25)
    Q3 = df_total2["Jumlah_Digunakan"].quantile(0.75)
    IQR = Q3 - Q1
    lower_bound = Q1 - 1.5 * IQR
    upper_bound = Q3 + 1.5 * IQR

    df_capped2 = df_total2.copy()
    df_capped2["Jumlah_Digunakan"] = df_capped2["Jumlah_Digunakan"].clip(
        lower=lower_bound, upper=upper_bound
    )

    n_out_total = int(((df_total2["Jumlah_Digunakan"] < lower_bound) | (df_total2["Jumlah_Digunakan"] > upper_bound)).sum())
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
                "Minimal diperlukan 2 bahan baku. "
                "Jalankan seeder data pemakaian bahan baku terlebih dahulu: "
                "php artisan db:seed --class=IngredientUsageSeeder"
            ),
        }

    silhouette_scores = []
    k_range = range(2, min(10, n_items))
    for k in k_range:
        km     = KMeans(n_clusters=k, random_state=42, n_init=10)
        labels = km.fit_predict(x_train)
        silhouette_scores.append(silhouette_score(x_train, labels))

    if not silhouette_scores:
        # Hanya 2 bahan baku → paksa k=2
        best_k  = 2
        km      = KMeans(n_clusters=best_k, random_state=42, n_init=10)
        labels  = km.fit_predict(x_train)
        best_sil = float(silhouette_score(x_train, labels))
        silhouette_scores = [best_sil]
        k_range = range(2, 3)
    else:
        best_k   = list(k_range)[int(np.argmax(silhouette_scores))]
        best_sil = float(max(silhouette_scores))
    logs.append({
        "tahap":  "Penentuan K Optimal (Silhouette)",
        "detail": f"K terbaik: {best_k} (Silhouette Score: {best_sil:.4f}). Range K: 2–{max(k_range)}.",
    })

    # ── Cell 37: Elbow — hitung inertia ───────────────────────────────
    inertias  = []
    k_range_e = range(1, len(x_train) + 1)
    for k in k_range_e:
        km = KMeans(n_clusters=k, random_state=42, n_init=10).fit(x_train)
        inertias.append(km.inertia_)

    # ── Cells 29-31: K-Means fit ───────────────────────────────────────
    kmean = KMeans(n_clusters=best_k, random_state=42, n_init=10)
    df_capped2["Klaster"] = kmean.fit_predict(x_train)

    # ── Cells 32-34: Label otomatis berdasarkan rata-rata per klaster ──
    cluster_avg = (
        df_capped2.groupby("Klaster")["Jumlah_Digunakan"]
        .mean()
        .sort_values(ascending=False)
    )
    cluster_label = {}
    for i, cluster_id in enumerate(cluster_avg.index):
        cluster_label[cluster_id] = LABELS_LIST[i] if i < len(LABELS_LIST) else f"Cluster {cluster_id}"

    df_capped2["Kategori"] = df_capped2["Klaster"].map(cluster_label)
    logs.append({
        "tahap":  "Labeling Klaster",
        "detail": "Pemetaan: " + ", ".join([f"Klaster {k} → {v}" for k, v in cluster_label.items()]),
    })

    # ── Cell 41: sort by Jumlah_Digunakan ─────────────────────────────
    df_baku = df_capped2.sort_values("Jumlah_Digunakan").reset_index(drop=True)

    # ── Cell 43: df_result ─────────────────────────────────────────────
    df_result = df_baku[["Bahan_Baku", "Jumlah_Digunakan", "Klaster", "Kategori"]].copy()

    # ── Susun output per klaster ───────────────────────────────────────
    clusters_out = []
    for label in LABELS_LIST:
        subset = df_result[df_result["Kategori"] == label].sort_values("Jumlah_Digunakan", ascending=False)
        if subset.empty:
            continue
        clusters_out.append({
            "label":       label,
            "count":       len(subset),
            "total_usage": float(subset["Jumlah_Digunakan"].sum()),
            "avg_usage":   round(float(subset["Jumlah_Digunakan"].mean()), 1),
            "ingredients": [
                {
                    "name":    row["Bahan_Baku"],
                    "unit":    df[df["Bahan_Baku"] == row["Bahan_Baku"]]["Unit"].iloc[0]
                               if "Unit" in df.columns and len(df[df["Bahan_Baku"] == row["Bahan_Baku"]]) > 0
                               else "",
                    "jumlah":  float(row["Jumlah_Digunakan"]),
                    "klaster": int(row["Klaster"]),
                }
                for _, row in subset.iterrows()
            ],
        })

    table_rows = []
    for _, row in df_result.iterrows():
        unit_val = ""
        if "Unit" in df.columns:
            match = df[df["Bahan_Baku"] == row["Bahan_Baku"]]["Unit"]
            if len(match) > 0:
                unit_val = str(match.iloc[0])
        table_rows.append({
            "Nama Bahan Baku":  row["Bahan_Baku"],
            "Satuan":           unit_val,
            "Total Penggunaan": float(row["Jumlah_Digunakan"]),
            "Klaster":          int(row["Klaster"]),
            "Kategori":         row["Kategori"],
        })

    # ── Style global ───────────────────────────────────────────────────
    plt.rcParams.update({
        "font.family":       "DejaVu Sans",
        "axes.spines.top":   False,
        "axes.spines.right": False,
        "axes.titlesize":    13,
        "axes.titleweight":  "bold",
        "axes.titlepad":     14,
        "axes.labelsize":    11,
        "figure.facecolor":  "white",
        "axes.facecolor":    "#fafafa",
        "axes.grid":         True,
        "grid.color":        "#e5e7eb",
        "grid.linewidth":    0.8,
    })

    palette_map = {
        "Sangat Banyak Digunakan": "#16a34a",
        "Banyak Digunakan":        "#2563eb",
        "Cukup Digunakan":         "#d97706",
        "Sedikit Digunakan":       "#ea580c",
        "Paling Sedikit Digunakan":"#9ca3af",
    }
    present_cats    = [c for c in LABELS_LIST if c in df_baku["Kategori"].unique()]
    palette_ordered = [palette_map[c] for c in present_cats]

    # ── Cell 42: Bar Chart (Visualisasi Clustering) ────────────────────
    n_items = len(df_baku)
    fig_w   = max(10, n_items * 0.9)
    fig1, ax1 = plt.subplots(figsize=(fig_w, 6))
    sns.barplot(
        data=df_baku,
        x="Bahan_Baku",
        y="Jumlah_Digunakan",
        hue="Kategori",
        hue_order=present_cats,
        palette=palette_ordered,
        ax=ax1,
        dodge=False,
        width=0.6,
    )
    for bar in ax1.patches:
        h = bar.get_height()
        if h > 0:
            ax1.text(
                bar.get_x() + bar.get_width() / 2,
                h + max(h * 0.01, 500),
                f"{h:,.0f}",
                ha="center", va="bottom",
                fontsize=8, color="#374151",
            )
    ax1.set_title("Visualisasi Clustering Penggunaan Bahan Baku")
    ax1.set_xlabel("Nama Bahan Baku", labelpad=8)
    ax1.set_ylabel("Jumlah Penggunaan", labelpad=8)
    ax1.tick_params(axis="x", rotation=35)
    ax1.set_xticklabels(ax1.get_xticklabels(), ha="right")
    ax1.legend(title="Kategori", loc="upper left", framealpha=0.9, edgecolor="#e5e7eb", fancybox=False)
    ax1.yaxis.grid(True)
    ax1.xaxis.grid(False)
    fig1.tight_layout(pad=2)
    chart_bar = _to_b64(fig1)

    # ── Cell 39: Elbow Curve ───────────────────────────────────────────
    fig2, ax2 = plt.subplots(figsize=(8, 5))
    ax2.plot(list(k_range_e), inertias,
             marker="o", markersize=7, color="#6366f1",
             linewidth=2.5, markerfacecolor="white", markeredgewidth=2)
    ax2.axvline(x=best_k, color="#ef4444", linestyle="--",
                linewidth=1.8, alpha=0.8, label=f"K optimal = {best_k}")
    if best_k <= len(inertias):
        best_inertia = inertias[best_k - 1]
        ax2.annotate(
            f"  K={best_k}\n  Inertia={best_inertia:.4f}",
            xy=(best_k, best_inertia),
            xytext=(best_k + 0.5, best_inertia + (max(inertias) - min(inertias)) * 0.05),
            fontsize=9, color="#ef4444",
            arrowprops=dict(arrowstyle="->", color="#ef4444", lw=1.2),
        )
    ax2.set_xlabel("Jumlah Klaster (K)")
    ax2.set_ylabel("Inertia (Sum of Squared Error)")
    ax2.set_title("Elbow Method — Penentuan Jumlah Klaster Optimal")
    ax2.set_xticks(list(k_range_e))
    ax2.legend(framealpha=0.9, edgecolor="#e5e7eb", fancybox=False)
    fig2.tight_layout(pad=2)
    chart_elbow = _to_b64(fig2)

    # ── Silhouette Score per K ─────────────────────────────────────────
    fig3, ax3 = plt.subplots(figsize=(8, 5))
    ax3.plot(list(k_range), silhouette_scores,
             marker="s", markersize=7, color="#059669",
             linewidth=2.5, markerfacecolor="white", markeredgewidth=2)
    ax3.axvline(x=best_k, color="#ef4444", linestyle="--",
                linewidth=1.8, alpha=0.8, label=f"K optimal = {best_k}")
    ax3.annotate(
        f"  K={best_k}\n  Score={best_sil:.4f}",
        xy=(best_k, best_sil),
        xytext=(best_k + 0.4, best_sil - (max(silhouette_scores) - min(silhouette_scores)) * 0.15),
        fontsize=9, color="#ef4444",
        arrowprops=dict(arrowstyle="->", color="#ef4444", lw=1.2),
    )
    ax3.set_xlabel("Jumlah Klaster (K)")
    ax3.set_ylabel("Silhouette Score")
    ax3.set_title("Silhouette Score per K — Kualitas Pengelompokan")
    ax3.set_xticks(list(k_range))
    ax3.legend(framealpha=0.9, edgecolor="#e5e7eb", fancybox=False)
    fig3.tight_layout(pad=2)
    chart_silhouette = _to_b64(fig3)

    return {
        "status":             "success",
        "best_k":             best_k,
        "silhouette_score":   round(best_sil, 4),
        "total_ingredients":  int(len(df_capped2)),
        "date_range":         {"from": date_from, "to": date_to},
        "clusters":           clusters_out,
        "table_rows":         table_rows,
        "preprocessing_logs": logs,
        "charts": {
            "bar":        chart_bar,
            "elbow":      chart_elbow,
            "silhouette": chart_silhouette,
        },
    }
