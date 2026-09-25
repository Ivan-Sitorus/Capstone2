import numpy as np
from sklearn.cluster import KMeans
from sklearn.metrics import silhouette_score


def select_kmeans(x_train, k_max: int = 10) -> dict:
    sil_scores = []
    inertias = []
    labels_by_k = {}
    k_range = range(2, min(k_max, len(x_train)))

    for k in k_range:
        km = KMeans(n_clusters=k, random_state=42, n_init=10)
        labels = km.fit_predict(x_train)
        labels_by_k[k] = labels
        sil_scores.append(float(silhouette_score(x_train, labels)))
        inertias.append(float(km.inertia_))

    if not sil_scores:
        best_k = 2
        km = KMeans(n_clusters=best_k, random_state=42, n_init=10)
        labels = km.fit_predict(x_train)
        labels_by_k[best_k] = labels
        sil_scores = [float(silhouette_score(x_train, labels))]
        inertias = [float(km.inertia_)]
        k_range = range(2, 3)
        best_sil = sil_scores[0]
    else:
        best_k = list(k_range)[int(np.argmax(sil_scores))]
        best_sil = float(max(sil_scores))

    return {
        "best_k": best_k,
        "best_sil": best_sil,
        "sil_scores": sil_scores,
        "inertias": inertias,
        "labels_by_k": labels_by_k,
        "k_range": k_range,
    }
