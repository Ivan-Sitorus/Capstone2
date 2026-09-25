"""
Pipeline Prediksi Time Series per Menu — Prophet
W9 Cafe POS | Capstone STIE Totalwin

Mengikuti notebook: 1 Model Menu Prophet preprocessing_prediction.ipynb
- Split 75:25 (cell 28)
- Konfigurasi Prophet tetap: yearly=False, weekly=True, additive,
  changepoint_prior_scale=0.03, seasonality_prior_scale=7 (cell 29)
- Model di-fit hanya pada data train (75%)
- Metrik: MAE, RMSE, MAPE, SMAPE (cell 29)
- Feature Importance: Weekday vs Weekend (cell 30)
- Evaluasi 2×2 bar chart (cell 31)
- Visualisasi per item dengan data training (cell 32)
- Prediksi 2 hari ke depan + nama hari Indonesia (cell 33)
"""

import os, warnings, math
import numpy as np
import pandas as pd
from prophet import Prophet
from sklearn.metrics import mean_absolute_error, mean_squared_error

warnings.filterwarnings("ignore")

HARI_ID = {
    'Monday':    'Senin',
    'Tuesday':   'Selasa',
    'Wednesday': 'Rabu',
    'Thursday':  'Kamis',
    'Friday':    'Jumat',
    'Saturday':  'Sabtu',
    'Sunday':    'Minggu',
}

# C4: konfigurasi Prophet yang sama dipakai prediksi menu & bahan baku.
PROPHET_COMMON_PARAMS = {
    "yearly_seasonality":      False,
    "weekly_seasonality":      True,
    "daily_seasonality":       False,
    "seasonality_mode":        "additive",
    "interval_width":          0.95,
    "changepoint_prior_scale": 0.07,
    "seasonality_prior_scale": 8,
}

PROPHET_UNCERTAINTY_SAMPLES = 100


# ─────────────────────────────────────────────────────────────────────────────
# Helper
# ─────────────────────────────────────────────────────────────────────────────



def smape(y_true, y_pred):
    """Symmetric Mean Absolute Percentage Error (cell 27)"""
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    denominator = (np.abs(y_true) + np.abs(y_pred)) / 2
    mask = denominator != 0
    if mask.sum() == 0:
        return 0.0
    return float(np.mean(np.abs(y_true[mask] - y_pred[mask]) / denominator[mask]) * 100)


def mape(y_true, y_pred):
    """Mean Absolute Percentage Error (cell 27)"""
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    mask = y_true != 0
    if mask.sum() == 0:
        return 0.0
    return float(np.mean(np.abs((y_true[mask] - y_pred[mask]) / y_true[mask])) * 100)


def get_day_type(date) -> str:
    """Weekday / Weekend (cell 27)"""
    return 'Weekend' if pd.Timestamp(date).dayofweek >= 5 else 'Weekday'


# ─────────────────────────────────────────────────────────────────────────────
# TAHAP 1 — PREPROCESSING  (cell 3–21)
# ─────────────────────────────────────────────────────────────────────────────

def _fill_missing_dates(df_agg: pd.DataFrame, item_col: str, value_col: str) -> pd.DataFrame:
    """Lengkapi tanggal yang hilang per item (isi 0) secara vektorisasi."""
    bounds = df_agg.groupby(item_col)["Tanggal"].agg(["min", "max"])
    starts = bounds["min"].to_numpy().astype("datetime64[ns]")
    ends   = bounds["max"].to_numpy().astype("datetime64[ns]")
    counts = (ends - starts).astype("timedelta64[D]").astype(np.int64) + 1
    offsets = np.arange(int(counts.sum())) - np.repeat(np.cumsum(counts) - counts, counts)
    dates = np.repeat(starts, counts) + pd.to_timedelta(offsets, unit="D").to_numpy()
    keys  = np.repeat(bounds.index.to_numpy(), counts)

    df_full = pd.DataFrame({"Tanggal": dates, item_col: keys})
    df_full = df_full.merge(df_agg, on=["Tanggal", item_col], how="left")
    df_full[value_col] = df_full[value_col].fillna(0)
    return df_full.sort_values([item_col, "Tanggal"]).reset_index(drop=True)


def _fill_missing_dates_global(df_agg: pd.DataFrame, item_col: str, value_col: str) -> pd.DataFrame:
    """Lengkapi tanggal hilang memakai rentang global (min..max seluruh item)."""
    full_dates = pd.date_range(df_agg["Tanggal"].min(), df_agg["Tanggal"].max(), freq="D")
    items = df_agg[item_col].unique()
    idx = pd.MultiIndex.from_product([items, full_dates], names=[item_col, "Tanggal"])
    df_full = df_agg.set_index([item_col, "Tanggal"]).reindex(idx).reset_index()
    df_full[value_col] = df_full[value_col].fillna(0)
    return df_full


def _cap_iqr(df: pd.DataFrame, item_col: str, value_col: str, round_bounds: bool = True):
    """IQR capping per item via groupby quantile (vektorisasi, bukan per-baris)."""
    q1 = df.groupby(item_col)[value_col].quantile(0.25)
    q3 = df.groupby(item_col)[value_col].quantile(0.75)
    q1 = df[item_col].map(q1)
    q3 = df[item_col].map(q3)

    iqr = q3 - q1
    if round_bounds:
        lower = np.floor(q1 - 1.5 * iqr)
        upper = np.ceil(q3 + 1.5 * iqr)
    else:
        lower = q1 - 1.5 * iqr
        upper = q3 + 1.5 * iqr
    has_iqr  = iqr > 0
    values   = df[value_col]
    capped   = np.where(values > upper, upper, np.where(values < lower, lower, values))
    n_outlier = int((((values < lower) | (values > upper)) & has_iqr).sum())

    out = df.copy()
    out[value_col] = np.where(has_iqr, capped, values)
    return out, n_outlier


def preprocess(df: pd.DataFrame):
    logs = []

    # Cell 3
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])

    # Cell 7–8 — agregasi harian per menu
    # B1: fetch SQL sudah agregasi (Tanggal × Nama Item); groupby hanya
    # dijalankan bila input ternyata masih memiliki duplikat.
    if df.duplicated(subset=["Tanggal", "Nama Item"]).any():
        df_total = df.groupby(
            ["Tanggal", "Nama Item"], as_index=False
        )["Jumlah"].sum()
    else:
        df_total = df[["Tanggal", "Nama Item", "Jumlah"]].copy()

    # Cell 9 — Day_Type (B4: np.where, tanpa .apply lambda)
    df_total["Day_Type"] = np.where(
        df_total["Tanggal"].dt.dayofweek >= 5, "Weekend", "Weekday"
    )
    logs.append({
        "tahap":  "Agregasi Harian & Day_Type",
        "detail": (
            f"Diagregasi per Tanggal×Menu. "
            f"Baris: {len(df_total)}, menu unik: {df_total['Nama Item'].nunique()}."
        ),
    })

    # Cell 12–13 — lengkapi tanggal yang hilang per item (isi Jumlah=0)
    # B2: vektorisasi (repeat/merge) menggantikan loop + merge per item.
    df_final = _fill_missing_dates(df_total, "Nama Item", "Jumlah")
    logs.append({
        "tahap":  "Lengkapi Tanggal Kosong",
        "detail": f"Tanggal hilang diisi Jumlah=0. Total baris: {len(df_final)}.",
    })

    # Cell 15 — tambah ulang Day_Type
    df_final["Day_Type"] = np.where(
        df_final["Tanggal"].dt.dayofweek >= 5, "Weekend", "Weekday"
    )

    # Cell 18 — outlier IQR Capping per item
    # B2: groupby.quantile + np.where menggantikan loop per item.
    df_capped, outlier_total = _cap_iqr(df_final, "Nama Item", "Jumlah")
    logs.append({
        "tahap":  "Outlier IQR Capping",
        "detail": f"Total outlier di-cap: {outlier_total} baris.",
    })

    # Cell 21 — bulatkan ke int
    df_capped["Jumlah"] = df_capped["Jumlah"].round().astype(int)

    logs.append({
        "tahap":  "Data Siap",
        "detail": (
            f"Preprocessing selesai. "
            f"{df_capped['Nama Item'].nunique()} menu, "
            f"periode {df_capped['Tanggal'].min().date()} "
            f"s/d {df_capped['Tanggal'].max().date()}."
        ),
    })
    return df_capped, logs


# ─────────────────────────────────────────────────────────────────────────────
# TAHAP 2 — SPLIT 75:25 PER MENU  (cell 28)
# ─────────────────────────────────────────────────────────────────────────────

def prepare_menu_data(df_item: pd.DataFrame, menu_name: str = None):
    """Rename kolom (ds, y), encode is_weekend, split 75:25.

    B3: menerima slice yang sudah dipisah per menu (hasil satu `groupby`);
    `menu_name` opsional dipertahankan agar pemanggilan lama tetap bekerja.
    """
    if menu_name is not None:
        df_item = df_item[df_item["Nama Item"] == menu_name]

    df_menu = df_item.sort_values("Tanggal").reset_index(drop=True)
    df_menu = df_menu.rename(columns={"Tanggal": "ds", "Jumlah": "y"})
    df_menu["ds"] = pd.to_datetime(df_menu["ds"])
    df_menu["is_weekend"] = (
        df_menu["Day_Type"].str.strip().str.lower() == "weekend"
    ).astype(int)

    split_idx = int(len(df_menu) * 0.75)
    df_train  = df_menu.iloc[:split_idx].copy()
    df_test   = df_menu.iloc[split_idx:].copy()
    return df_menu, df_train, df_test


# ─────────────────────────────────────────────────────────────────────────────
# TAHAP 3 — INISIALISASI & FIT MODEL PROPHET  (cell 29 — konfigurasi tetap)
# ─────────────────────────────────────────────────────────────────────────────

def build_prophet_model(df_train: pd.DataFrame) -> Prophet:
    """
    Konfigurasi Prophet tetap sesuai notebook cell 29.
    Model di-fit hanya pada data train (75%).
    uncertainty_samples=100 (lebih cepat dari default 1000, tetap ada CI).
    """
    model = Prophet(
        **PROPHET_COMMON_PARAMS,
        uncertainty_samples=PROPHET_UNCERTAINTY_SAMPLES,
    )
    model.add_regressor('is_weekend')
    model.fit(df_train[["ds", "y", "is_weekend"]])
    return model


# ─────────────────────────────────────────────────────────────────────────────
# TAHAP 4 — EVALUASI MODEL  (cell 29)
# ─────────────────────────────────────────────────────────────────────────────

def evaluate_model(model: Prophet, df_test: pd.DataFrame):
    """Prediksi pada test set, hitung MAE / RMSE / MAPE / SMAPE."""
    future_test   = df_test[["ds", "is_weekend"]].copy()
    forecast_test = model.predict(future_test)

    result = df_test[["ds", "y", "Day_Type"]].merge(
        forecast_test[["ds", "yhat", "yhat_lower", "yhat_upper"]], on="ds"
    )
    result["yhat"]       = result["yhat"].clip(lower=0).round()
    result["yhat_lower"] = result["yhat_lower"].clip(lower=0).round()
    result["yhat_upper"] = result["yhat_upper"].clip(lower=0).round()

    y_true = result["y"].values
    y_pred = result["yhat"].values

    mae_val   = float(mean_absolute_error(y_true, y_pred))
    rmse_val  = float(np.sqrt(mean_squared_error(y_true, y_pred)))
    mape_val  = mape(y_true, y_pred)
    smape_val = smape(y_true, y_pred)

    return result, mae_val, rmse_val, mape_val, smape_val


# ─────────────────────────────────────────────────────────────────────────────
# VISUALISASI 1 — FEATURE IMPORTANCE: Weekday vs Weekend  (cell 30)
# ─────────────────────────────────────────────────────────────────────────────

def build_feature_importance(df_capped: pd.DataFrame, items: list) -> list:
    means = (
        df_capped
        .groupby(["Nama Item", "Day_Type"])["Jumlah"]
        .mean()
        .unstack("Day_Type")
        .reindex(items)
    )
    weekday = means["Weekday"] if "Weekday" in means.columns else pd.Series(np.nan, index=means.index)
    weekend = means["Weekend"] if "Weekend" in means.columns else pd.Series(np.nan, index=means.index)

    fi_list = []
    for item in items:
        wd = weekday.get(item)
        we = weekend.get(item)
        fi_list.append({
            "item":    item,
            "Weekday": round(float(wd), 4) if pd.notna(wd) else 0.0,
            "Weekend": round(float(we), 4) if pd.notna(we) else 0.0,
        })

    return fi_list


# ─────────────────────────────────────────────────────────────────────────────
# MAIN PIPELINE
# ─────────────────────────────────────────────────────────────────────────────

def run_prediction_pipeline(df: pd.DataFrame) -> dict:
    """
    Input  : DataFrame dari fetch_order_data() (api.py)
    Output : dict JSON untuk endpoint /prediction
    """

    # ── Tahap 1: Preprocessing ────────────────────────────────────────────
    df_capped, logs = preprocess(df)

    min_date = df_capped["Tanggal"].min()
    max_date = df_capped["Tanggal"].max()
    items    = list(df_capped["Nama Item"].unique())

    # ── Tahap 2: Split 75:25 per menu (cell 28) ───────────────────────────
    # B3: pisahkan slice per menu sekali via groupby, lalu pakai ulang.
    menu_groups = {name: group for name, group in df_capped.groupby("Nama Item")}
    item_data = {}
    skipped_items = []
    for item in items:
        df_full, df_train, df_test = prepare_menu_data(menu_groups[item])
        if len(df_train) < 2 or len(df_test) < 1:
            skipped_items.append(item)
            continue
        item_data[item] = {"full": df_full, "train": df_train, "test": df_test}

    items = list(item_data.keys())

    if not items:
        raise ValueError(
            "Semua menu tidak memiliki cukup data untuk diprediksi. "
            "Setiap menu butuh minimal 3 hari data transaksi."
        )

    logs.append({
        "tahap":  "Persiapan Data Per Menu",
        "detail": (
            f"Data dibagi train (75%) dan test (25%) untuk {len(items)} menu."
            + (f" Dilewati ({len(skipped_items)} menu data kurang): {', '.join(skipped_items)}." if skipped_items else "")
        ),
    })

    # ── Tahap 3 & 4: Training + Evaluasi (cell 29) ───────────────────────
    models       = {}
    eval_results = {}
    forecasts    = {}

    for item in items:
        model = build_prophet_model(item_data[item]["train"])
        models[item] = model

        result, mae_v, rmse_v, mape_v, smape_v = evaluate_model(
            model, item_data[item]["test"]
        )
        eval_results[item] = {
            "MAE":   round(mae_v,   2),
            "RMSE":  round(rmse_v,  2),
            "MAPE":  round(mape_v,  2),
            "SMAPE": round(smape_v, 2),
        }
        forecasts[item] = result

    logs.append({
        "tahap":  "Training Model Prophet & Evaluasi",
        "detail": (
            f"1 model Prophet per menu, difit pada data train (75%), "
            f"dievaluasi pada test (25%). "
            + " | ".join(
                f"{m}: MAPE {eval_results[m]['MAPE']:.1f}%  SMAPE {eval_results[m]['SMAPE']:.1f}%"
                for m in items
            )
        ),
    })

    # ── Feature importance (data saja; grafik dirender native) ─────────────
    feature_importance = build_feature_importance(df_capped, items)

    # ── Tahap 5: Prediksi 2 hari ke depan (cell 33) ───────────────────────
    future_dates = [max_date + pd.Timedelta(days=i) for i in range(1, 3)]
    future_df    = pd.DataFrame({
        "ds":         future_dates,
        "is_weekend": [1 if d.dayofweek >= 5 else 0 for d in future_dates],
    })

    predictions_out = []
    summary_table   = []

    for item in items:
        forecast_future = models[item].predict(future_df)

        forecast_days_list = []
        for _, row in forecast_future.iterrows():
            day_name  = row["ds"].strftime("%A")
            day_indo  = HARI_ID.get(day_name, day_name)
            day_type  = "Weekend" if row["ds"].dayofweek >= 5 else "Weekday"
            pred      = max(0, round(float(row["yhat"])))
            lower     = max(0, round(float(row["yhat_lower"])))
            upper     = max(0, round(float(row["yhat_upper"])))

            forecast_days_list.append({
                "tanggal":     str(row["ds"].date()),
                "hari":        day_indo,
                "day_type":    day_type,
                "prediksi":    pred,
                "batas_bawah": lower,
                "batas_atas":  upper,
            })

        total_forecast = sum(f["prediksi"] for f in forecast_days_list)
        ev = eval_results[item]

        predictions_out.append({
            "nama_menu":      item,
            "model":          "Prophet",
            "mae":            ev["MAE"],
            "rmse":           ev["RMSE"],
            "mape":           ev["MAPE"],
            "smape":          ev["SMAPE"],
            "total_forecast": float(total_forecast),
            "forecast":       forecast_days_list,
        })
        summary_table.append({
            "nama_menu":      item,
            "total_forecast": float(total_forecast),
            "avg_per_day":    round(total_forecast / 2, 2),
            "mae":            ev["MAE"],
            "rmse":           ev["RMSE"],
            "mape":           ev["MAPE"],
            "smape":          ev["SMAPE"],
            "model":          "Prophet",
        })

    summary_table.sort(key=lambda x: x["total_forecast"], reverse=True)

    forecast_test = []
    for item in items:
        res = forecasts[item]
        forecast_test.append({
            "nama": item,
            "ds": [str(d.date()) for d in res["ds"]],
            "actual": [float(v) for v in res["y"]],
            "predicted": [float(v) for v in res["yhat"]],
        })

    # ── Output akhir ───────────────────────────────────────────────────────
    return {
        "status":             "success",
        "total_menu":         int(len(items)),
        "forecast_days":      2,
        "date_range":         {
            "from": str(min_date.date()),
            "to":   str(max_date.date()),
        },
        "forecast_range":     {
            "from": str(future_dates[0].date()),
            "to":   str(future_dates[1].date()),
        },
        "preprocessing_logs": logs,
        "predictions":        predictions_out,
        "summary_table":      summary_table,
        "feature_importance": feature_importance,
        "forecast_test":      forecast_test,
    }
