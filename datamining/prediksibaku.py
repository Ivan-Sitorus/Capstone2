"""
prediksibaku.py — Prediksi Penggunaan Bahan Baku — Prophet
W9 Cafe POS | Capstone STIE Totalwin

Mengikuti notebook: 1 Model Bahan Baku Prophet preprocessing_prediction.ipynb
  - Preprocessing: parse Tanggal, drop NaN/duplikat, agregasi harian,
    reindex tanggal, IQR Capping per bahan baku
  - Split 75:25
  - Prophet: yearly=False, weekly=True, additive,
    changepoint_prior_scale=0.07, seasonality_prior_scale=8
  - Regressor: is_weekend (1 = Sabtu/Minggu)
  - Evaluasi: MAE, RMSE, MAPE, SMAPE pada data test (25%)
  - Prediksi 2 hari ke depan per bahan baku
  - Visualisasi: forecast_all, feature_importance, evaluation 2×2,
    all_items grid, per_ingredient individual

Input DataFrame kolom: Tanggal, Bahan_Baku, Unit, Jumlah_Digunakan
Output: dict JSON sesuai kontrak PrediksiBahanBaku.php + prediksi-bahan-baku.blade.php
"""

import warnings, math
import numpy as np
import pandas as pd
from prophet import Prophet
from sklearn.metrics import mean_absolute_error, mean_squared_error

try:
    from .prediction import PROPHET_COMMON_PARAMS, _fill_missing_dates_global, _cap_iqr
except ImportError:
    from prediction import PROPHET_COMMON_PARAMS, _fill_missing_dates_global, _cap_iqr

warnings.filterwarnings("ignore")

HARI_ID = {
    "Monday":    "Senin",
    "Tuesday":   "Selasa",
    "Wednesday": "Rabu",
    "Thursday":  "Kamis",
    "Friday":    "Jumat",
    "Saturday":  "Sabtu",
    "Sunday":    "Minggu",
}

# ─────────────────────────────────────────────────────────────────────────────
# Helpers
# ─────────────────────────────────────────────────────────────────────────────

def _smape(y_true, y_pred) -> float:
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    denom = (np.abs(y_true) + np.abs(y_pred)) / 2
    mask  = denom != 0
    return float(np.mean(np.abs(y_true[mask] - y_pred[mask]) / denom[mask]) * 100) if mask.sum() else 0.0


def _mape(y_true, y_pred) -> float:
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    mask = y_true != 0
    return float(np.mean(np.abs((y_true[mask] - y_pred[mask]) / y_true[mask])) * 100) if mask.sum() else 0.0


# ─────────────────────────────────────────────────────────────────────────────
# TAHAP 1 — PREPROCESSING  (sel 2–19 notebook)
# ─────────────────────────────────────────────────────────────────────────────

def _preprocess(df: pd.DataFrame):
    logs = []

    # Parse tanggal
    df = df.copy()
    df["Tanggal"] = pd.to_datetime(df["Tanggal"])

    # Simpan peta satuan per bahan baku
    unit_map: dict = df.groupby("Bahan_Baku")["Unit"].first().to_dict()

    # Agregasi harian per bahan baku
    df = df.sort_values("Tanggal")
    df_agg = df.groupby(["Tanggal", "Bahan_Baku"], as_index=False)["Jumlah_Digunakan"].sum()
    df_agg["Day_Type"] = np.where(
        df_agg["Tanggal"].dt.dayofweek >= 5, "Weekend", "Weekday"
    )
    logs.append({
        "tahap":  "Agregasi Harian",
        "detail": (
            f"Diagregasi per Tanggal×Bahan_Baku. "
            f"Baris: {len(df_agg)}, bahan baku unik: {df_agg['Bahan_Baku'].nunique()}."
        ),
    })

    # Reindex — lengkapi tanggal yang hilang dengan Jumlah=0
    min_date = df_agg["Tanggal"].min()
    max_date = df_agg["Tanggal"].max()

    # C2: date-fill vektorisasi (rentang global) menggantikan loop per bahan.
    df_full = _fill_missing_dates_global(df_agg, "Bahan_Baku", "Jumlah_Digunakan")
    df_full["Day_Type"] = np.where(
        df_full["Tanggal"].dt.dayofweek >= 5, "Weekend", "Weekday"
    )
    logs.append({
        "tahap":  "Lengkapi Tanggal Kosong",
        "detail": f"Tanggal hilang diisi Jumlah=0. Total baris: {len(df_full)}.",
    })

    # IQR Capping per bahan baku
    # C2: groupby.quantile + np.where menggantikan loop per bahan.
    df_capped, n_outlier = _cap_iqr(df_full, "Bahan_Baku", "Jumlah_Digunakan", round_bounds=False)
    logs.append({
        "tahap":  "Outlier IQR Capping",
        "detail": f"Total nilai outlier di-cap: {n_outlier} baris.",
    })

    return df_capped, unit_map, min_date, max_date, logs


# ─────────────────────────────────────────────────────────────────────────────
# TAHAP 2 — PROPHET MODEL + PREDIKSI  (sel 20–33 notebook)
# ─────────────────────────────────────────────────────────────────────────────

def run_prediction_pipeline_bahan_baku(df: pd.DataFrame) -> dict:
    df_capped, unit_map, min_date, max_date, logs = _preprocess(df)

    ingredients = df_capped["Bahan_Baku"].unique().tolist()
    n = len(ingredients)

    groups = {name: group for name, group in df_capped.groupby("Bahan_Baku")}

    predictions_out       = []
    summary_rows          = []
    all_items_store = []

    for bahan in ingredients:
        df_b = groups[bahan].sort_values("Tanggal").reset_index(drop=True)

        # Siapkan DataFrame Prophet (ds, y, is_weekend)
        df_p = df_b[["Tanggal", "Jumlah_Digunakan"]].rename(
            columns={"Tanggal": "ds", "Jumlah_Digunakan": "y"}
        ).copy()
        df_p["is_weekend"] = (df_p["ds"].dt.dayofweek >= 5).astype(int)

        # Split 75 : 25
        n_rows  = len(df_p)
        n_train = math.ceil(n_rows * 0.75)
        train   = df_p.iloc[:n_train].reset_index(drop=True)
        test    = df_p.iloc[n_train:].reset_index(drop=True)

        # ── Prophet — konfigurasi notebook Bahan Baku (C4: konstanta bersama) ──
        model = Prophet(**PROPHET_COMMON_PARAMS)
        model.add_regressor("is_weekend")
        model.fit(train[["ds", "y", "is_weekend"]])

        # Evaluasi pada test set
        test_fc = model.predict(test[["ds", "is_weekend"]])
        y_true  = test["y"].values
        y_pred  = test_fc["yhat"].clip(lower=0).values

        mae_v   = float(mean_absolute_error(y_true, y_pred))
        rmse_v  = float(math.sqrt(mean_squared_error(y_true, y_pred)))
        mape_v  = _mape(y_true, y_pred)
        smape_v = _smape(y_true, y_pred)

        # Prediksi 2 hari ke depan
        last_date    = df_p["ds"].max()
        future_dates = pd.date_range(start=last_date + pd.Timedelta(days=1), periods=2, freq="D")
        future_df    = pd.DataFrame({"ds": future_dates})
        future_df["is_weekend"] = (future_df["ds"].dt.dayofweek >= 5).astype(int)
        future_fc    = model.predict(future_df)

        forecast_days = []
        for _, row in future_fc.iterrows():
            forecast_days.append({
                "tanggal":    str(row["ds"].date()),
                "hari":       HARI_ID.get(row["ds"].strftime("%A"), row["ds"].strftime("%A")),
                "day_type":   "Weekend" if row["ds"].dayofweek >= 5 else "Weekday",
                "prediksi":   round(float(max(0.0, row["yhat"])), 1),
                "batas_bawah": round(float(max(0.0, row["yhat_lower"])), 1),
                "batas_atas":  round(float(max(0.0, row["yhat_upper"])), 1),
            })

        total_fc = sum(d["prediksi"] for d in forecast_days)
        satuan   = unit_map.get(bahan, "")

        predictions_out.append({
            "nama_bahan_baku": bahan,
            "satuan":          satuan,
            "mae":             round(mae_v,   2),
            "rmse":            round(rmse_v,  2),
            "mape":            round(mape_v,  2),
            "smape":           round(smape_v, 2),
            "total_forecast":  round(total_fc, 1),
            "forecast":        forecast_days,
        })

        summary_rows.append({
            "nama_bahan_baku": bahan,
            "satuan":          satuan,
            "total_forecast":  round(total_fc, 1),
            "avg_per_day":     round(total_fc / 2, 1),
            "mae":             round(mae_v,   2),
            "rmse":            round(rmse_v,  2),
            "mape":            round(mape_v,  2),
            "smape":           round(smape_v, 2),
            "model":           "Prophet",
        })

        all_items_store.append({
            "bahan":   bahan,
            "satuan":  satuan,
            "train":   (train["ds"].tolist(), train["y"].tolist()),
            "test_ds": test["ds"].tolist(),
            "test_y":  y_true.tolist(),
            "pred_y":  y_pred.tolist(),
        })

    # Sort summary by total_forecast desc
    summary_rows.sort(key=lambda x: x["total_forecast"], reverse=True)

    means = (
        df_capped
        .groupby(["Bahan_Baku", "Day_Type"])["Jumlah_Digunakan"]
        .mean()
        .unstack("Day_Type")
        .reindex(ingredients)
    )
    wd = means["Weekday"] if "Weekday" in means.columns else pd.Series(np.nan, index=means.index)
    we = means["Weekend"] if "Weekend" in means.columns else pd.Series(np.nan, index=means.index)
    feature_importance = [
        {
            "bahan":   bahan,
            "Weekday": round(float(wd.loc[bahan]), 4),
            "Weekend": round(float(we.loc[bahan]), 4),
        }
        for bahan in ingredients
    ]

    # ─────────────────────────────────────────────────────────────────────
    # Forecast range dates
    # ─────────────────────────────────────────────────────────────────────
    if predictions_out and predictions_out[0]["forecast"]:
        fc_from = predictions_out[0]["forecast"][0]["tanggal"]
        fc_to   = predictions_out[0]["forecast"][-1]["tanggal"]
    else:
        fc_from = fc_to = ""

    return {
        "status":             "success",
        "total_ingredients":  n,
        "forecast_days":      2,
        "date_range":         {"from": str(min_date.date()), "to": str(max_date.date())},
        "forecast_range":     {"from": fc_from, "to": fc_to},
        "predictions":        predictions_out,
        "summary_table":      summary_rows,
        "preprocessing_logs": logs,
        "feature_importance": feature_importance,
        "all_items":          all_items_store,
    }