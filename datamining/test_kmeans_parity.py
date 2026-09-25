import time

import numpy as np
from sklearn.cluster import KMeans
from sklearn.metrics import silhouette_score

from kmeans_utils import select_kmeans


def old_select_kmeans(x_train, k_max=10):
    k_range = range(2, min(k_max, len(x_train)))

    sil_scores = []
    for k in k_range:
        km = KMeans(n_clusters=k, random_state=42, n_init=10)
        labels = km.fit_predict(x_train)
        sil_scores.append(float(silhouette_score(x_train, labels)))
    best_k = list(k_range)[int(np.argmax(sil_scores))]
    best_sil = float(max(sil_scores))

    inertias = []
    for k in k_range:
        km = KMeans(n_clusters=k, random_state=42, n_init=10)
        km.fit(x_train)
        inertias.append(float(km.inertia_))

    km = KMeans(n_clusters=best_k, random_state=42, n_init=10)
    final_labels = km.fit_predict(x_train)

    return {
        "best_k": best_k,
        "best_sil": best_sil,
        "sil_scores": sil_scores,
        "inertias": inertias,
        "final_labels": final_labels,
        "k_range": k_range,
    }


def make_data(n_samples, n_features, seed):
    rng = np.random.default_rng(seed)
    centers = rng.normal(0, 3, size=(6, n_features))
    idx = rng.integers(0, 6, size=n_samples)
    return centers[idx] + rng.normal(0, 0.7, size=(n_samples, n_features))


def test_parity_old_vs_new():
    for seed in range(1, 6):
        for n_samples in (60, 300, 1200):
            x = make_data(n_samples, 2, seed)
            old = old_select_kmeans(x)
            new = select_kmeans(x)

            assert old["best_k"] == new["best_k"], (seed, n_samples)
            assert np.isclose(old["best_sil"], new["best_sil"], atol=1e-9), (seed, n_samples)
            assert np.allclose(old["sil_scores"], new["sil_scores"], atol=1e-9), (seed, n_samples)
            assert np.allclose(old["inertias"], new["inertias"], atol=1e-6), (seed, n_samples)
            assert np.array_equal(old["final_labels"], new["labels_by_k"][new["best_k"]]), (seed, n_samples)


def test_new_handles_minimum_points():
    x = make_data(3, 2, 7)
    new = select_kmeans(x)
    assert new["best_k"] == 2
    assert len(new["labels_by_k"][2]) == 3


def benchmark(n_samples, n_features=2, repeats=7, seed=99):
    x = make_data(n_samples, n_features, seed)
    select_kmeans(x)
    old_times = []
    new_times = []
    for _ in range(repeats):
        t = time.perf_counter()
        old_select_kmeans(x)
        old_times.append(time.perf_counter() - t)

        t = time.perf_counter()
        select_kmeans(x)
        new_times.append(time.perf_counter() - t)
    return old_times, new_times


def _median(values):
    ordered = sorted(values)
    return ordered[len(ordered) // 2]


def test_timing_new_is_faster():
    for n in (200, 3000):
        old_times, new_times = benchmark(n)
        old_med = _median(old_times)
        new_med = _median(new_times)
        print(
            f"[TIMING] n={n}: old median={old_med * 1000:.1f} ms | "
            f"new median={new_med * 1000:.1f} ms | speedup={old_med / new_med:.2f}x"
        )
        assert new_med < old_med


if __name__ == "__main__":
    test_parity_old_vs_new()
    print("[PARITY] old vs new identik pada 15 kombinasi (seed 1-5 x n=60/300/1200)")

    test_new_handles_minimum_points()
    print("[EDGE] kasus minimum 3 titik: new tidak crash, best_k=2")

    for n in (200, 3000):
        old_times, new_times = benchmark(n)
        old_med = _median(old_times)
        new_med = _median(new_times)
        print(f"[TIMING] n={n}")
        print(f"         old (15 fit) median={old_med * 1000:.1f} ms  runs={[round(t * 1000, 1) for t in old_times]}")
        print(f"         new ( 8 fit) median={new_med * 1000:.1f} ms  runs={[round(t * 1000, 1) for t in new_times]}")
        print(f"         speedup = {old_med / new_med:.2f}x  ({(1 - new_med / old_med) * 100:.1f}% lebih cepat)")
