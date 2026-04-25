"""
Association Rule Mining — FP-Growth
W9 Cafe POS | Capstone STIE Totalwin

Pipeline mengikuti notebook: FIX Preprocessing_Association.ipynb
"""

import io, base64, warnings
import numpy as np
import pandas as pd
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt

from mlxtend.frequent_patterns import fpgrowth, association_rules
from mlxtend.preprocessing import TransactionEncoder

warnings.filterwarnings("ignore")


# ── Preprocessing (notebook cells 2–7) ────────────────────────────────────
def preprocess(df: pd.DataFrame):
    logs = []
    df = df.copy()

    # Cell 2: parse Tanggal
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])

    # Cell 4: Hapus baris jika Nama Item NaN atau kosong
    before = len(df)
    df = df[df["Nama Item"].notna() & (df["Nama Item"].str.strip() != "")]
    logs.append({
        "tahap":  "Hapus Nama Item Kosong",
        "detail": f"Dihapus {before - len(df)} baris. Sisa: {len(df)} baris.",
    })

    # Cell 5: Hapus duplikat
    before = len(df)
    df = df.drop_duplicates(subset=["Tanggal", "ID Pesanan", "Nama Item", "Harga", "Jumlah"])
    logs.append({
        "tahap":  "Hapus Duplikat",
        "detail": f"Dihapus {before - len(df)} duplikat. Sisa: {len(df)} baris.",
    })

    # Cell 7: Urutkan berdasarkan tanggal
    df = df.sort_values("Tanggal", ascending=True).reset_index(drop=True)
    logs.append({
        "tahap":  "Pengurutan Data",
        "detail": (
            f"Data diurutkan berdasarkan tanggal. "
            f"Rentang: {df['Tanggal'].min().date()} s/d {df['Tanggal'].max().date()}."
        ),
    })

    return df, logs


# ── Main Pipeline (notebook cells 8–18) ───────────────────────────────────
def run_association_pipeline(df: pd.DataFrame) -> dict:
    # Rename agar cocok dengan notebook (fetch_order_data menghasilkan "Order_id")
    if "Order_id" in df.columns and "ID Pesanan" not in df.columns:
        df = df.rename(columns={"Order_id": "ID Pesanan"})

    df_clean, logs = preprocess(df)

    date_from = str(df_clean["Tanggal"].min().date())
    date_to   = str(df_clean["Tanggal"].max().date())

    # Cell 8: Kelompokkan pesanan sesuai ID Pesanan
    transactions = df_clean.groupby("ID Pesanan")["Nama Item"].apply(list).reset_index()

    # Hanya gunakan transaksi dengan 2+ item berbeda — single-item orders tidak
    # mengandung informasi asosiasi dan akan mendilusi nilai support.
    transactions = transactions[transactions["Nama Item"].apply(lambda x: len(set(x)) >= 2)].reset_index(drop=True)
    total_transactions = len(transactions)

    logs.append({
        "tahap":  "Pengelompokan Transaksi",
        "detail": f"Total transaksi unik: {total_transactions}. Total item baris: {len(df_clean)}.",
    })

    # Cell 10: Encode ke boolean (TransactionEncoder)
    te       = TransactionEncoder()
    te_array = te.fit(transactions["Nama Item"]).transform(transactions["Nama Item"])
    df_encoded = pd.DataFrame(te_array, columns=te.columns_)

    logs.append({
        "tahap":  "Encoding Transaksi",
        "detail": f"Matrix boolean: {df_encoded.shape[0]} transaksi × {df_encoded.shape[1]} item unik.",
    })

    # Cell 12-13: Frequent 1-Itemsets (min_support=0.01 — ambil semua)
    frequent_all = fpgrowth(df_encoded, min_support=0.01, use_colnames=True)
    frequent_1   = frequent_all[frequent_all["itemsets"].apply(lambda x: len(x) == 1)].copy()
    frequent_1["jumlah_kemunculan"] = (frequent_1["support"] * total_transactions).round().astype(int)
    frequent_1 = frequent_1.sort_values("support", ascending=False)

    logs.append({
        "tahap":  "Frequent 1-Itemsets",
        "detail": f"Ditemukan {len(frequent_1)} item dengan min_support ≥ 1%.",
    })

    # Cell 14-15: Frequent 2-Itemsets (min_support=0.05)
    frequent_05 = fpgrowth(df_encoded, min_support=0.01, use_colnames=True)
    frequent_2  = frequent_05[frequent_05["itemsets"].apply(lambda x: len(x) == 2)].copy()
    frequent_2["jumlah_kemunculan"] = (frequent_2["support"] * total_transactions).round().astype(int)
    frequent_2 = frequent_2.sort_values("support", ascending=False)

    logs.append({
        "tahap":  "Frequent 2-Itemsets",
        "detail": f"Ditemukan {len(frequent_2)} pasangan menu dengan min_support ≥ 5%.",
    })

    # Cell 16: Frequent 3-Itemsets (min_support=0.05)
    frequent_3 = frequent_05[frequent_05["itemsets"].apply(lambda x: len(x) == 3)].copy()
    frequent_3["jumlah_kemunculan"] = (frequent_3["support"] * total_transactions).round().astype(int)
    frequent_3 = frequent_3.sort_values("support", ascending=False)

    # Cell 17-18: Association Rules
    rules = association_rules(frequent_05, metric="confidence", min_threshold=0.01)

    rules_2 = rules[
        (rules["antecedents"].apply(lambda x: len(x)) == 1) &
        (rules["consequents"].apply(lambda x: len(x)) == 1)
    ].copy()

    rules_2["menu_pertama"]          = rules_2["antecedents"].apply(lambda x: list(x)[0])
    rules_2["menu_kedua"]            = rules_2["consequents"].apply(lambda x: list(x)[0])
    rules_2["jumlah_menu_pertama"]   = (rules_2["antecedent support"] * total_transactions).round().astype(int)
    rules_2["jumlah_menu_kedua"]     = (rules_2["consequent support"]  * total_transactions).round().astype(int)
    rules_2["jumlah_bersamaan"]      = (rules_2["support"] * total_transactions).round().astype(int)

    # Sort by lift, ambil TOP 8 (notebook cell 18)
    rules_final = rules_2.sort_values("lift", ascending=False).head(8)

    min_conf = float(rules_final["confidence"].min()) if len(rules_final) > 0 else 0.01
    min_supp = float(rules_final["support"].min())    if len(rules_final) > 0 else 0.05

    logs.append({
        "tahap":  "Association Rules",
        "detail": (
            f"Total rules ditemukan: {len(rules_2)}. "
            f"Diambil TOP 8 berdasarkan lift tertinggi."
        ),
    })

    # Build output rules + interpretasi (notebook cells 18-19)
    rules_out = []
    for _, row in rules_final.iterrows():
        m1       = row["menu_pertama"]
        m2       = row["menu_kedua"]
        supp_pct = round(float(row["support"])     * 100, 2)
        conf_pct = round(float(row["confidence"])  * 100, 2)
        lift_val = round(float(row["lift"]), 2)
        rules_out.append({
            "menu_pertama":        m1,
            "menu_kedua":          m2,
            "jumlah_menu_pertama": int(row["jumlah_menu_pertama"]),
            "jumlah_menu_kedua":   int(row["jumlah_menu_kedua"]),
            "jumlah_bersamaan":    int(row["jumlah_bersamaan"]),
            "support":             round(float(row["support"]),    6),
            "confidence":          round(float(row["confidence"]), 6),
            "lift":                round(float(row["lift"]),       6),
            "interpretasi": (
                f"Ada sekitar {supp_pct}% transaksi pembelian {m1} dan {m2} secara bersamaan. "
                f"Dari semua yang membeli {m1}, ada sekitar {conf_pct}% juga yang membeli {m2} secara bersamaan. "
                f"Selain itu, kemungkinan membeli {m2} menjadi {lift_val} kali lebih besar "
                f"jika seseorang membeli {m1}."
            ),
        })

    freq1_out = [
        {
            "item":              str(list(row["itemsets"])[0]),
            "support":           round(float(row["support"]), 6),
            "jumlah_kemunculan": int(row["jumlah_kemunculan"]),
        }
        for _, row in frequent_1.iterrows()
    ]

    freq2_out = [
        {
            "items":             " + ".join(sorted(str(x) for x in row["itemsets"])),
            "support":           round(float(row["support"]), 6),
            "jumlah_kemunculan": int(row["jumlah_kemunculan"]),
        }
        for _, row in frequent_2.iterrows()
    ]

    freq3_out = [
        {
            "items":             " + ".join(sorted(str(x) for x in row["itemsets"])),
            "support":           round(float(row["support"]), 6),
            "jumlah_kemunculan": int(row["jumlah_kemunculan"]),
        }
        for _, row in frequent_3.iterrows()
    ]

    # ── Charts ─────────────────────────────────────────────────────────────
    plt.rcParams.update({
        "font.family":       "DejaVu Sans",
        "axes.spines.top":   False,
        "axes.spines.right": False,
        "figure.facecolor":  "white",
        "axes.facecolor":    "#fafafa",
        "axes.grid":         True,
        "grid.color":        "#e5e7eb",
        "grid.linewidth":    0.8,
    })

    def _to_b64(fig) -> str:
        buf = io.BytesIO()
        fig.savefig(buf, format="png", bbox_inches="tight", dpi=120)
        buf.seek(0)
        enc = base64.b64encode(buf.read()).decode()
        plt.close(fig)
        return enc

    # Chart 1: Top Rules by Lift (horizontal bar)
    chart_top_rules = None
    if rules_out:
        labels    = [f"{r['menu_pertama']} → {r['menu_kedua']}" for r in rules_out]
        lift_vals = [r["lift"] for r in rules_out]
        colors    = ["#4f46e5" if v >= 1.5 else "#f59e0b" for v in lift_vals]

        fig1, ax1 = plt.subplots(figsize=(10, max(4, len(labels) * 0.6)))
        bars = ax1.barh(labels[::-1], lift_vals[::-1], color=colors[::-1], height=0.6)
        ax1.axvline(x=1.0, color="#9ca3af", linestyle="--", linewidth=1.2, alpha=0.7)
        for bar, val in zip(bars, lift_vals[::-1]):
            ax1.text(val + 0.01, bar.get_y() + bar.get_height() / 2,
                     f"{val:.2f}", va="center", fontsize=9, color="#374151")
        ax1.set_xlabel("Lift Value")
        ax1.set_title("Top 8 Rules — Nilai Lift Tertinggi", fontsize=13, fontweight="bold", pad=12)
        ax1.xaxis.grid(True)
        ax1.yaxis.grid(False)
        fig1.tight_layout(pad=2)
        chart_top_rules = _to_b64(fig1)

    # Chart 2: Support vs Confidence scatter
    chart_sup_conf = None
    if len(rules_2) > 0:
        fig2, ax2 = plt.subplots(figsize=(7, 5))
        sc = ax2.scatter(
            rules_2["support"], rules_2["confidence"],
            c=rules_2["lift"], cmap="YlOrRd",
            s=80, alpha=0.7, edgecolors="#cbd5e1", linewidths=0.5,
        )
        plt.colorbar(sc, ax=ax2, label="Lift")
        ax2.set_xlabel("Support")
        ax2.set_ylabel("Confidence")
        ax2.set_title("Support vs Confidence (warna = Lift)", fontsize=12, fontweight="bold", pad=10)
        fig2.tight_layout(pad=2)
        chart_sup_conf = _to_b64(fig2)

    # Chart 3: Frequent 1-Itemsets horizontal bar
    chart_freq_item = None
    if freq1_out:
        top_items  = freq1_out[:15]
        item_names = [f["item"] for f in top_items]
        item_cnts  = [f["jumlah_kemunculan"] for f in top_items]

        fig3, ax3 = plt.subplots(figsize=(10, max(4, len(item_names) * 0.5)))
        ax3.barh(item_names[::-1], item_cnts[::-1], color="#6366f1", height=0.6)
        for i, cnt in enumerate(item_cnts[::-1]):
            ax3.text(cnt + 0.3, i, str(cnt), va="center", fontsize=9, color="#374151")
        ax3.set_xlabel("Jumlah Kemunculan dalam Transaksi")
        ax3.set_title("Frequent 1-Itemsets — Frekuensi Kemunculan per Menu",
                      fontsize=12, fontweight="bold", pad=10)
        ax3.xaxis.grid(True)
        ax3.yaxis.grid(False)
        fig3.tight_layout(pad=2)
        chart_freq_item = _to_b64(fig3)

    return {
        "status":             "success",
        "total_rules":        len(rules_out),
        "total_transactions": total_transactions,
        "min_support":        0.01,
        "min_confidence":     round(min_conf, 6),
        "date_range":         {"from": date_from, "to": date_to},
        "rules":              rules_out,
        "freq_1_itemsets":    freq1_out,
        "freq_2_itemsets":    freq2_out,
        "freq_3_itemsets":    freq3_out,
        "preprocessing_logs": logs,
        "charts": {
            "top_rules": chart_top_rules,
            "sup_conf":  chart_sup_conf,
            "freq_item": chart_freq_item,
        },
    }
