# K6 Load Testing — W9 Cafe POS (Modul Kasir)

## Prasyarat

Install K6 di Windows:
```bash
winget install k6
```

Pastikan server Laravel berjalan:
```bash
php artisan serve
```

---

## Struktur File

```
k6/
└── cashier/
    ├── auth.js         ← Helper autentikasi (CSRF + session)
    ├── smoke.js        ← Smoke test: 1 VU, 1 menit
    ├── load.js         ← Load test: 20 VU, 5 menit
    ├── stress.js       ← Stress test: naik hingga 100 VU
    ├── spike.js        ← Spike test: lonjakan mendadak 0→100 VU
    └── order-flow.js   ← Alur pembuatan pesanan: 10 VU, 4 menit
```

---

## Cara Menjalankan

### Smoke Test (jalankan pertama kali)
```bash
k6 run k6/cashier/smoke.js
```

### Load Test (pengujian utama)
```bash
k6 run k6/cashier/load.js
```

### Stress Test
```bash
k6 run k6/cashier/stress.js
```

### Spike Test
```bash
k6 run k6/cashier/spike.js
```

### Alur Pembuatan Pesanan
```bash
k6 run k6/cashier/order-flow.js
```

### Ganti Base URL (jika bukan localhost:8000)
```bash
k6 run -e BASE_URL=http://192.168.1.10:8000 k6/cashier/load.js
```

### Simpan hasil ke file JSON
```bash
k6 run --out json=hasil-load.json k6/cashier/load.js
```

---

## Skenario & Target Beban

| File | VU | Durasi | Tujuan |
|---|---|---|---|
| smoke.js | 1 | 1 menit | Verifikasi semua endpoint bisa diakses |
| load.js | 20 | 5 menit | Simulasi kondisi normal operasional |
| stress.js | 100 | 12 menit | Temukan batas maksimal sistem |
| spike.js | 0→100 | 4 menit | Uji lonjakan beban mendadak |
| order-flow.js | 10 | 4 menit | Uji alur pembuatan pesanan |

---

## Threshold (Batas Keberhasilan)

| Metrik | Smoke | Load | Stress | Spike |
|---|---|---|---|---|
| Error Rate | < 1% | < 5% | < 10% | < 15% |
| Response Time p(95) | < 3 detik | < 3 detik | < 5 detik | < 8 detik |

---

## Metrik yang Dicatat

| Metrik | Keterangan |
|---|---|
| `http_req_duration` | Waktu respons per request |
| `http_req_failed` | Persentase request yang gagal |
| `dashboard_duration` | Waktu muat halaman Dashboard |
| `pesanan_aktif_duration` | Waktu muat halaman Pesanan Aktif |
| `order_create_duration` | Waktu proses pembuatan pesanan |
| `orders_created` | Total pesanan berhasil dibuat |
| `request_success_rate` | Persentase request sukses |
