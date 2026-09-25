"""Parity + micro-benchmark: optimized vs original pure functions (NO database).

Feed identical synthetic DataFrames to the original implementations and the
optimized functions, then assert numerical equality of the produced values.
Run:  python datamining/test_optimization_parity.py
"""

import math
import time

import numpy as np
import pandas as pd
from mlxtend.frequent_patterns import fpgrowth, association_rules as mlxtend_rules
from mlxtend.preprocessing import TransactionEncoder

import association
import prediction
import prediksibaku
import bahanbaku  # noqa: F401  (ensures imports resolve)


# ─────────────────────────────────────────────────────────────────────────────
# Synthetic data
# ─────────────────────────────────────────────────────────────────────────────

def make_order_df(seed=0, n_items=6, n_days=30, orders_per_day=6):
    rng = np.random.default_rng(seed)
    dates = pd.date_range("2026-01-01", periods=n_days, freq="D")
    items = [f"Menu {i:02d}" for i in range(n_items)]
    rows = []
    for d in dates:
        for o in range(orders_per_day):
            oid = f"ORD-{d:%Y%m%d}-{o:03d}"
            k = int(rng.integers(1, 4))
            for it in rng.choice(items, size=k, replace=False).tolist():
                rows.append({"Tanggal": d, "Order_id": oid, "Nama Item": it})
    return pd.DataFrame(rows)


def make_sales_df(seed=1, n_items=5, n_days=45):
    rng = np.random.default_rng(seed)
    dates = pd.date_range("2026-01-01", periods=n_days, freq="D")
    rows = []
    for k in range(n_items):
        item = f"Menu {k:02d}"
        for d in dates[k % 5:]:
            if rng.random() < 0.25:
                continue
            rows.append({"Tanggal": d, "Nama Item": item, "Jumlah": float(rng.integers(0, 8))})
            if rng.random() < 0.2:
                rows.append({"Tanggal": d, "Nama Item": item, "Jumlah": float(rng.integers(0, 4))})
            if rng.random() < 0.05:
                rows.append({"Tanggal": d, "Nama Item": item, "Jumlah": 200.0})
    df = pd.DataFrame(rows)
    df["Keuntungan"] = 0.0
    return df


def make_ingredient_df(seed=2, n_items=4, n_days=40):
    rng = np.random.default_rng(seed)
    dates = pd.date_range("2026-01-01", periods=n_days, freq="D")
    rows = []
    for k in range(n_items):
        bahan = f"Bahan {k}"
        for d in dates[k:]:
            if rng.random() < 0.3:
                continue
            rows.append({"Tanggal": d, "Bahan_Baku": bahan, "Unit": "gram",
                         "Jumlah_Digunakan": float(rng.integers(0, 20))})
            if rng.random() < 0.05:
                rows.append({"Tanggal": d, "Bahan_Baku": bahan, "Unit": "gram",
                             "Jumlah_Digunakan": 500.0})
    return pd.DataFrame(rows)


# ─────────────────────────────────────────────────────────────────────────────
# Original (reference) implementations
# ─────────────────────────────────────────────────────────────────────────────

def old_preprocess_prediction(df):
    logs = []
    df = df.copy()
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])

    df_sorted = df.sort_values("Tanggal", ascending=True)
    df_total = df_sorted.groupby(["Tanggal", "Nama Item"], as_index=False)["Jumlah"].sum()
    df_total["Day_Type"] = df_total["Tanggal"].dt.dayofweek.apply(
        lambda x: "Weekend" if x >= 5 else "Weekday")

    df_full_all = []
    for item in df_total["Nama Item"].unique():
        df_item = df_total[df_total["Nama Item"] == item].copy()
        all_dates = pd.date_range(df_item["Tanggal"].min(), df_item["Tanggal"].max(), freq="D")
        df_full = pd.DataFrame({"Tanggal": all_dates})
        df_full = df_full.merge(df_item, on="Tanggal", how="left")
        df_full["Jumlah"] = df_full["Jumlah"].fillna(0)
        df_full["Nama Item"] = item
        df_full = df_full[["Tanggal", "Nama Item", "Jumlah"]]
        df_full_all.append(df_full)

    df_final = pd.concat(df_full_all, ignore_index=True)
    df_final = df_final.sort_values(["Nama Item", "Tanggal"]).reset_index(drop=True)
    df_final["Day_Type"] = df_final["Tanggal"].dt.dayofweek.apply(
        lambda x: "Weekend" if x >= 5 else "Weekday")

    df_result_list = []
    outlier_total = 0
    for item in df_final["Nama Item"].unique():
        df_item = df_final[df_final["Nama Item"] == item].copy()
        Q1 = df_item["Jumlah"].quantile(0.25)
        Q3 = df_item["Jumlah"].quantile(0.75)
        IQR = Q3 - Q1
        if IQR > 0:
            lower = math.floor(Q1 - 1.5 * IQR)
            upper = math.ceil(Q3 + 1.5 * IQR)
            outlier_total += int(((df_item["Jumlah"] < lower) | (df_item["Jumlah"] > upper)).sum())
            df_item["Jumlah"] = np.where(
                df_item["Jumlah"] > upper, upper,
                np.where(df_item["Jumlah"] < lower, lower, df_item["Jumlah"]))
        df_result_list.append(df_item)

    df_capped = pd.concat(df_result_list, ignore_index=True)
    df_capped["Jumlah"] = df_capped["Jumlah"].round().astype(int)
    return df_capped, logs


def old_prepare_menu_data(df_capped, menu_name):
    df_menu = df_capped[df_capped["Nama Item"] == menu_name].copy()
    df_menu = df_menu.sort_values("Tanggal").reset_index(drop=True)
    df_menu = df_menu.rename(columns={"Tanggal": "ds", "Jumlah": "y"})
    df_menu["ds"] = pd.to_datetime(df_menu["ds"])
    df_menu["is_weekend"] = (df_menu["Day_Type"].str.strip().str.lower() == "weekend").astype(int)
    split_idx = int(len(df_menu) * 0.75)
    return df_menu, df_menu.iloc[:split_idx].copy(), df_menu.iloc[split_idx:].copy()


def old_build_feature_importance(item_data, items):
    fi_list = []
    for item in items:
        df_all = item_data[item]["full"]
        weekday_data = df_all[df_all["is_weekend"] == 0]["y"]
        weekend_data = df_all[df_all["is_weekend"] == 1]["y"]
        avg_weekday = float(weekday_data.mean()) if len(weekday_data) > 0 else 0.0
        avg_weekend = float(weekend_data.mean()) if len(weekend_data) > 0 else 0.0
        fi_list.append({"item": item, "Weekday": round(avg_weekday, 4),
                        "Weekend": round(avg_weekend, 4)})
    return fi_list


def old_preprocess_bahan(df):
    df = df.copy()
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])
    unit_map = df.groupby("Bahan_Baku")["Unit"].first().to_dict()

    df = df.sort_values("Tanggal")
    df_agg = df.groupby(["Tanggal", "Bahan_Baku"], as_index=False)["Jumlah_Digunakan"].sum()
    df_agg["Day_Type"] = df_agg["Tanggal"].dt.dayofweek.apply(
        lambda x: "Weekend" if x >= 5 else "Weekday")

    min_date = df_agg["Tanggal"].min()
    max_date = df_agg["Tanggal"].max()
    full_dates = pd.date_range(start=min_date, end=max_date, freq="D")
    parts = []
    for bahan in df_agg["Bahan_Baku"].unique():
        tmp = df_agg[df_agg["Bahan_Baku"] == bahan].set_index("Tanggal")
        tmp = tmp.reindex(full_dates)
        tmp["Bahan_Baku"] = bahan
        tmp["Jumlah_Digunakan"] = tmp["Jumlah_Digunakan"].fillna(0)
        tmp = tmp.reset_index().rename(columns={"index": "Tanggal"})
        parts.append(tmp)
    df_full = pd.concat(parts, ignore_index=True)
    df_full["Day_Type"] = df_full["Tanggal"].dt.dayofweek.apply(
        lambda x: "Weekend" if x >= 5 else "Weekday")

    capped, n_outlier = [], 0
    for bahan in df_full["Bahan_Baku"].unique():
        tmp = df_full[df_full["Bahan_Baku"] == bahan].copy()
        Q1, Q3 = tmp["Jumlah_Digunakan"].quantile(0.25), tmp["Jumlah_Digunakan"].quantile(0.75)
        IQR = Q3 - Q1
        if IQR > 0:
            lo, hi = Q1 - 1.5 * IQR, Q3 + 1.5 * IQR
            n_outlier += int(((tmp["Jumlah_Digunakan"] < lo) | (tmp["Jumlah_Digunakan"] > hi)).sum())
            tmp["Jumlah_Digunakan"] = tmp["Jumlah_Digunakan"].clip(lower=lo, upper=hi)
        capped.append(tmp)
    df_capped = pd.concat(capped, ignore_index=True)
    return df_capped, unit_map, min_date, max_date


def old_fill_per_item(df_agg, item_col, value_col):
    parts = []
    for item in df_agg[item_col].unique():
        df_item = df_agg[df_agg[item_col] == item].copy()
        all_dates = pd.date_range(df_item["Tanggal"].min(), df_item["Tanggal"].max(), freq="D")
        df_full = pd.DataFrame({"Tanggal": all_dates})
        df_full = df_full.merge(df_item, on="Tanggal", how="left")
        df_full[value_col] = df_full[value_col].fillna(0)
        df_full[item_col] = item
        df_full = df_full[["Tanggal", item_col, value_col]]
        parts.append(df_full)
    return pd.concat(parts, ignore_index=True)


def old_cap_per_item(df, item_col, value_col):
    result, n_out = [], 0
    for item in df[item_col].unique():
        tmp = df[df[item_col] == item].copy()
        Q1, Q3 = tmp[value_col].quantile(0.25), tmp[value_col].quantile(0.75)
        IQR = Q3 - Q1
        if IQR > 0:
            lo, hi = math.floor(Q1 - 1.5 * IQR), math.ceil(Q3 + 1.5 * IQR)
            n_out += int(((tmp[value_col] < lo) | (tmp[value_col] > hi)).sum())
            tmp[value_col] = np.where(tmp[value_col] > hi, hi,
                                      np.where(tmp[value_col] < lo, lo, tmp[value_col]))
        result.append(tmp)
    return pd.concat(result, ignore_index=True), n_out


def old_association_result(df):
    df = df.rename(columns={"Order_id": "ID Pesanan"})
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])
    transactions = df.groupby("ID Pesanan")["Nama Item"].apply(list).reset_index()

    te = TransactionEncoder()
    te_array = te.fit(transactions["Nama Item"]).transform(transactions["Nama Item"])
    df_encoded = pd.DataFrame(te_array, columns=te.columns_)
    total_transaksi = df_encoded.shape[0]

    frequent_itemsets = fpgrowth(df_encoded, min_support=association.MIN_SUPPORT,
                                 use_colnames=True, max_len=2)

    freq1_df = frequent_itemsets[frequent_itemsets["itemsets"].apply(lambda x: len(x) == 1)].copy()
    freq1_df["item"] = freq1_df["itemsets"].apply(lambda x: list(x)[0])
    freq1_df["jumlah_kemunculan"] = (freq1_df["support"] * total_transaksi).round().astype(int)
    freq1_list = (freq1_df[["item", "support", "jumlah_kemunculan"]]
                  .sort_values("jumlah_kemunculan", ascending=False).to_dict("records"))
    for r in freq1_list:
        r["support"] = round(r["support"], 6)

    freq2_df = frequent_itemsets[frequent_itemsets["itemsets"].apply(lambda x: len(x) == 2)].copy()
    freq2_df["items"] = freq2_df["itemsets"].apply(lambda x: " + ".join(sorted(list(x))))
    freq2_df["jumlah_kemunculan"] = (freq2_df["support"] * total_transaksi).round().astype(int)
    freq2_list = (freq2_df[["items", "support", "jumlah_kemunculan"]]
                  .sort_values("jumlah_kemunculan", ascending=False).to_dict("records"))
    for r in freq2_list:
        r["support"] = round(r["support"], 6)

    rules_df = mlxtend_rules(frequent_itemsets, metric="confidence",
                             min_threshold=association.MIN_CONFIDENCE)
    rules_2 = rules_df[(rules_df["antecedents"].apply(len) == 1) &
                       (rules_df["consequents"].apply(len) == 1)].copy()
    rules_2["menu_pertama"] = rules_2["antecedents"].apply(lambda x: list(x)[0])
    rules_2["menu_kedua"] = rules_2["consequents"].apply(lambda x: list(x)[0])
    rules_2["jumlah_menu_pertama"] = (rules_2["antecedent support"] * total_transaksi).round().astype(int)
    rules_2["jumlah_menu_kedua"] = (rules_2["consequent support"] * total_transaksi).round().astype(int)
    rules_2["jumlah_bersamaan"] = (rules_2["support"] * total_transaksi).round().astype(int)

    rules_final = rules_2.sort_values("lift", ascending=False).head(8)
    rules_top = []
    for _, row in rules_final.iterrows():
        supp_pct = round(float(row["support"]) * 100, 2)
        conf_pct = round(float(row["confidence"]) * 100, 2)
        lift_val = round(float(row["lift"]), 2)
        rules_top.append({
            "menu_pertama": row["menu_pertama"],
            "menu_kedua": row["menu_kedua"],
            "jumlah_menu_pertama": int(row["jumlah_menu_pertama"]),
            "jumlah_menu_kedua": int(row["jumlah_menu_kedua"]),
            "jumlah_bersamaan": int(row["jumlah_bersamaan"]),
            "support": round(float(row["support"]), 6),
            "confidence": round(float(row["confidence"]), 6),
            "lift": round(float(row["lift"]), 6),
            "interpretasi": (
                f"Ada sekitar {supp_pct}% transaksi pembelian {row['menu_pertama']} dan {row['menu_kedua']} secara bersamaan. "
                f"Dari semua yang membeli {row['menu_pertama']}, ada sekitar {conf_pct}% juga yang membeli {row['menu_kedua']} secara bersamaan. "
                f"Selain itu, kemungkinan membeli {row['menu_kedua']} menjadi {lift_val} kali lebih besar "
                f"jika seseorang membeli {row['menu_pertama']}."
            ),
        })

    return {
        "total_transactions": total_transaksi,
        "rules": rules_top,
        "freq_1_itemsets": freq1_list,
        "freq_2_itemsets": freq2_list[:20],
    }


# ─────────────────────────────────────────────────────────────────────────────
# Helpers
# ─────────────────────────────────────────────────────────────────────────────

def _norm(df, cols):
    return df[cols].sort_values(by=cols).reset_index(drop=True)


def _median(values):
    ordered = sorted(values)
    return ordered[len(ordered) // 2]


def _bench(fn, repeat=3):
    fn()
    times = []
    for _ in range(repeat):
        t = time.perf_counter()
        fn()
        times.append(time.perf_counter() - t)
    return _median(times), times


# ─────────────────────────────────────────────────────────────────────────────
# Parity tests
# ─────────────────────────────────────────────────────────────────────────────

def test_prediction_preprocess_parity():
    df = make_sales_df()
    old_df, _ = old_preprocess_prediction(df)
    new_df, _ = prediction.preprocess(df.copy())
    cols = ["Tanggal", "Nama Item", "Jumlah", "Day_Type"]
    pd.testing.assert_frame_equal(_norm(old_df, cols), _norm(new_df, cols),
                                  check_dtype=False, check_like=True)
    assert (new_df["Jumlah"] == 200).sum() == 0, "spike 200 belum di-cap (IQR tidak teruji)"


def test_prepare_menu_data_parity():
    df = make_sales_df()
    capped, _ = prediction.preprocess(df.copy())
    groups = {n: g for n, g in capped.groupby("Nama Item")}
    for item in capped["Nama Item"].unique():
        o_full, o_tr, o_te = old_prepare_menu_data(capped, item)
        n_full, n_tr, n_te = prediction.prepare_menu_data(groups[item])
        pd.testing.assert_frame_equal(o_full, n_full, check_dtype=False, check_like=True)
        pd.testing.assert_frame_equal(o_tr, n_tr, check_dtype=False, check_like=True)
        pd.testing.assert_frame_equal(o_te, n_te, check_dtype=False, check_like=True)


def test_feature_importance_parity():
    df = make_sales_df()
    capped, _ = prediction.preprocess(df.copy())
    items = list(capped["Nama Item"].unique())
    old_item_data = {it: {"full": old_prepare_menu_data(capped, it)[0]} for it in items}
    old_fi = old_build_feature_importance(old_item_data, items)
    new_fi = prediction.build_feature_importance(capped, items)
    assert old_fi == new_fi, (old_fi, new_fi)


def test_prediksibaku_preprocess_parity():
    df = make_ingredient_df()
    old_capped, old_units, old_min, old_max = old_preprocess_bahan(df)
    new_capped, new_units, new_min, new_max, _ = prediksibaku._preprocess(df.copy())
    cols = ["Tanggal", "Bahan_Baku", "Jumlah_Digunakan", "Day_Type"]
    pd.testing.assert_frame_equal(_norm(old_capped, cols), _norm(new_capped, cols),
                                  check_dtype=False, check_like=True)
    assert old_units == new_units
    assert old_min == new_min and old_max == new_max
    assert (new_capped["Jumlah_Digunakan"] == 500.0).sum() == 0, "spike 500 belum di-cap (IQR tidak teruji)"


def test_bahanbaku_fill_cap_parity():
    df = make_ingredient_df()
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])
    agg = (df.sort_values("Tanggal")
             .groupby(["Tanggal", "Bahan_Baku"], as_index=False)["Jumlah_Digunakan"].sum())

    old_fill = old_fill_per_item(agg, "Bahan_Baku", "Jumlah_Digunakan")
    old_cap, old_n = old_cap_per_item(old_fill, "Bahan_Baku", "Jumlah_Digunakan")

    new_fill = prediction._fill_missing_dates(agg, "Bahan_Baku", "Jumlah_Digunakan")
    new_cap, new_n = prediction._cap_iqr(new_fill, "Bahan_Baku", "Jumlah_Digunakan")

    cols = ["Tanggal", "Bahan_Baku", "Jumlah_Digunakan"]
    pd.testing.assert_frame_equal(_norm(old_cap, cols), _norm(new_cap, cols),
                                  check_dtype=False, check_like=True)
    assert old_n == new_n


def test_association_parity():
    df = make_order_df(seed=3, n_items=7, n_days=25, orders_per_day=8)
    old = old_association_result(df.copy())
    new = association.run_association_pipeline(df.copy())
    assert old["total_transactions"] == new["total_transactions"]
    assert old["freq_1_itemsets"] == new["freq_1_itemsets"]
    assert old["freq_2_itemsets"] == new["freq_2_itemsets"]
    assert old["rules"] == new["rules"]


# ─────────────────────────────────────────────────────────────────────────────
# Micro-benchmarks (old vs new)
# ─────────────────────────────────────────────────────────────────────────────

def benchmark():
    orders = make_order_df(seed=9, n_items=30, n_days=180, orders_per_day=250)
    sales = make_sales_df(seed=5, n_items=25, n_days=400)
    ingr = make_ingredient_df(seed=6, n_items=15, n_days=400)
    print(f"[DATA] orders: {len(orders)} item rows / {orders['Order_id'].nunique()} transaksi")

    def old_encode():
        transactions = orders.groupby("Order_id")["Nama Item"].apply(list).reset_index()
        te = TransactionEncoder()
        array = te.fit(transactions["Nama Item"]).transform(transactions["Nama Item"])
        return pd.DataFrame(array, columns=te.columns_)

    def new_encode():
        return orders.groupby(["Order_id", "Nama Item"]).size().unstack(fill_value=0) > 0

    cases = (
        ("assoc basket encode", old_encode, new_encode, 3),
        ("assoc full pipeline", lambda: old_association_result(orders.copy()),
                                lambda: association.run_association_pipeline(orders.copy()), 2),
        ("prediction preprocess", lambda: old_preprocess_prediction(sales.copy()),
                                  lambda: prediction.preprocess(sales.copy()), 3),
        ("bahan-baku preprocess", lambda: old_preprocess_bahan(ingr.copy()),
                                  lambda: prediksibaku._preprocess(ingr.copy()), 3),
    )

    rows = []
    for label, old_fn, new_fn, repeat in cases:
        old_t, old_runs = _bench(old_fn, repeat=repeat)
        new_t, new_runs = _bench(new_fn, repeat=repeat)
        rows.append((label, old_t, new_t))
        print(f"[BENCH] {label:24s} old={old_t * 1000:9.2f} ms  new={new_t * 1000:9.2f} ms  "
              f"speedup={old_t / new_t:5.2f}x")
    return rows


if __name__ == "__main__":
    test_prediction_preprocess_parity()
    print("[PARITY] prediction.preprocess  old == new (B1/B2/B4)")

    test_prepare_menu_data_parity()
    print("[PARITY] prepare_menu_data       old == new (B3)")

    test_feature_importance_parity()
    print("[PARITY] build_feature_importance old == new (B6)")

    test_prediksibaku_preprocess_parity()
    print("[PARITY] prediksibaku._preprocess old == new (C2)")

    test_bahanbaku_fill_cap_parity()
    print("[PARITY] bahan-baku fill + IQR    old == new (C2)")

    test_association_parity()
    print("[PARITY] association pipeline     old == new (A1)")

    print()
    benchmark()
    print("\nALL PARITY CHECKS PASSED")
