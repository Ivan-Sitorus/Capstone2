"""
Association Rule Mining — FP-Growth (Dua Arah)
W9 Cafe POS | Capstone STIE Totalwin

Menggunakan mlxtend FP-Growth + association_rules.
Basket transaksi dibangun vektorisasi via `pd.crosstab` (bukan
`groupby(...).apply(list)` + TransactionEncoder).
A→B dan B→A di-generate sebagai rule TERPISAH dengan nilai support/confidence/lift berbeda.
Filter: hanya rule 1-itemset → 1-itemset.
Diurutkan berdasarkan lift tertinggi, diambil TOP 8.
"""

import warnings
import pandas as pd
from mlxtend.frequent_patterns import fpgrowth, association_rules as mlxtend_rules

warnings.filterwarnings("ignore")

MIN_SUPPORT    = 0.002
MIN_CONFIDENCE = 0.03


# ── Preprocessing ──────────────────────────────────────────────────────────────
def preprocess(df: pd.DataFrame):
    logs = []
    df = df.copy()
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])

    logs.append({
        "tahap":  "Load Data Riwayat Pesanan",
        "detail": (
            f"Berhasil memuat {len(df)} baris item pesanan dari "
            f"{df['Nama Item'].nunique()} menu unik. "
            f"Rentang: {df['Tanggal'].min().date()} s/d {df['Tanggal'].max().date()}."
        ),
    })

    return df, logs


# ── Main Pipeline ──────────────────────────────────────────────────────────────
def run_association_pipeline(df: pd.DataFrame) -> dict:
    if "Order_id" in df.columns and "ID Pesanan" not in df.columns:
        df = df.rename(columns={"Order_id": "ID Pesanan"})

    df_clean, logs = preprocess(df)
    date_from = str(df_clean["Tanggal"].min().date())
    date_to   = str(df_clean["Tanggal"].max().date())

    # ── Kelompokkan item per transaksi (vektorisasi) ───────────────────────
    # A1: groupby.size().unstack() membangun matriks boolean transaksi × menu
    # dalam satu operasi vektorisasi — identik dengan TransactionEncoder,
    # tanpa `groupby(...).apply(list)` per transaksi.
    # Gunakan semua transaksi (termasuk yang hanya 1 item).
    df_encoded = (
        df_clean
        .groupby(["ID Pesanan", "Nama Item"])
        .size()
        .unstack(fill_value=0)
    ) > 0

    total_transaksi = df_encoded.shape[0]

    logs.append({
        "tahap":  "Pengelompokan Transaksi",
        "detail": (
            f"Total transaksi (order): {total_transaksi}. "
            f"Total baris item: {len(df_clean)}."
        ),
    })

    logs.append({
        "tahap":  "Encoding Transaksi (Basket Boolean)",
        "detail": (
            f"Ditemukan {df_encoded.shape[1]} menu unik. "
            f"Matrix transaksi: {df_encoded.shape[0]} transaksi × {df_encoded.shape[1]} menu. "
            f"Urutan item dalam transaksi diabaikan — fokus pada kombinasi (dua arah)."
        ),
    })

    # ── FP-Growth: Frequent Itemsets ───────────────────────────────────────
    frequent_itemsets = fpgrowth(df_encoded, min_support=MIN_SUPPORT, use_colnames=True, max_len=2)

    # Frequent 1-itemsets
    freq1_df = frequent_itemsets[
        frequent_itemsets["itemsets"].apply(lambda x: len(x) == 1)
    ].copy()
    freq1_df["item"]             = freq1_df["itemsets"].apply(lambda x: list(x)[0])
    freq1_df["jumlah_kemunculan"] = (freq1_df["support"] * total_transaksi).round().astype(int)
    freq1_list = (
        freq1_df[["item", "support", "jumlah_kemunculan"]]
        .sort_values("jumlah_kemunculan", ascending=False)
        .to_dict("records")
    )
    for r in freq1_list:
        r["support"] = round(r["support"], 6)

    logs.append({
        "tahap":  "Frequent 1-Itemsets (FP-Growth)",
        "detail": f"Ditemukan {len(freq1_list)} item dengan min_support ≥ {MIN_SUPPORT * 100:g}%.",
    })

    # Frequent 2-itemsets (pasangan tidak berurutan)
    freq2_df = frequent_itemsets[
        frequent_itemsets["itemsets"].apply(lambda x: len(x) == 2)
    ].copy()
    freq2_df["items"]            = freq2_df["itemsets"].apply(lambda x: " + ".join(sorted(list(x))))
    freq2_df["jumlah_kemunculan"] = (freq2_df["support"] * total_transaksi).round().astype(int)
    freq2_list = (
        freq2_df[["items", "support", "jumlah_kemunculan"]]
        .sort_values("jumlah_kemunculan", ascending=False)
        .to_dict("records")
    )
    for r in freq2_list:
        r["support"] = round(r["support"], 6)

    logs.append({
        "tahap":  "Frequent 2-Itemsets (FP-Growth)",
        "detail": (
            f"Ditemukan {len(freq2_list)} pasangan menu yang sering dibeli bersamaan "
            f"dengan min_support ≥ {MIN_SUPPORT * 100:g}%."
        ),
    })

    # ── Association Rules (dua arah) ───────────────────────────────────────
    # mlxtend otomatis generate A→B dan B→A sebagai rule terpisah
    rules_df = mlxtend_rules(
        frequent_itemsets,
        metric="confidence",
        min_threshold=MIN_CONFIDENCE,
    )

    # Filter hanya 1→1 rules
    rules_2_items = rules_df[
        (rules_df["antecedents"].apply(len) == 1) &
        (rules_df["consequents"].apply(len) == 1)
    ].copy()

    rules_2_items["menu_pertama"]        = rules_2_items["antecedents"].apply(lambda x: list(x)[0])
    rules_2_items["menu_kedua"]          = rules_2_items["consequents"].apply(lambda x: list(x)[0])
    rules_2_items["jumlah_menu_pertama"] = (rules_2_items["antecedent support"] * total_transaksi).round().astype(int)
    rules_2_items["jumlah_menu_kedua"]   = (rules_2_items["consequent support"]  * total_transaksi).round().astype(int)
    rules_2_items["jumlah_bersamaan"]    = (rules_2_items["support"] * total_transaksi).round().astype(int)

    # Urutkan berdasarkan lift tertinggi, ambil TOP 8
    rules_final = rules_2_items.sort_values("lift", ascending=False).head(8)

    rules_top = []
    for _, row in rules_final.iterrows():
        menu1    = row["menu_pertama"]
        menu2    = row["menu_kedua"]
        supp_pct = round(float(row["support"])    * 100, 2)
        conf_pct = round(float(row["confidence"]) * 100, 2)
        lift_val = round(float(row["lift"]), 2)

        rules_top.append({
            "menu_pertama":        menu1,
            "menu_kedua":          menu2,
            "jumlah_menu_pertama": int(row["jumlah_menu_pertama"]),
            "jumlah_menu_kedua":   int(row["jumlah_menu_kedua"]),
            "jumlah_bersamaan":    int(row["jumlah_bersamaan"]),
            "support":             round(float(row["support"]),    6),
            "confidence":          round(float(row["confidence"]), 6),
            "lift":                round(float(row["lift"]),       6),
            "interpretasi": (
                f"Ada sekitar {supp_pct}% transaksi pembelian {menu1} dan {menu2} secara bersamaan. "
                f"Dari semua yang membeli {menu1}, ada sekitar {conf_pct}% juga yang membeli {menu2} secara bersamaan. "
                f"Selain itu, kemungkinan membeli {menu2} menjadi {lift_val} kali lebih besar "
                f"jika seseorang membeli {menu1}."
            ),
        })

    total_rules_found = len(rules_2_items)
    logs.append({
        "tahap":  "Association Rules (Dua Arah)",
        "detail": (
            f"Total rules 1→1 ditemukan: {total_rules_found} "
            f"(A→B dan B→A adalah rule TERPISAH dengan nilai berbeda). "
            f"Diambil TOP 8 berdasarkan lift tertinggi."
        ),
    })

    min_conf_val = rules_top[-1]["confidence"] if rules_top else MIN_CONFIDENCE

    return {
        "status":             "success",
        "total_rules":        len(rules_top),
        "total_transactions": total_transaksi,
        "min_support":        MIN_SUPPORT,
        "min_confidence":     round(min_conf_val, 6),
        "date_range":         {"from": date_from, "to": date_to},
        "rules":              rules_top,
        "freq_1_itemsets":    freq1_list,
        "freq_2_itemsets":    freq2_list[:20],
        "preprocessing_logs": logs,
    }
