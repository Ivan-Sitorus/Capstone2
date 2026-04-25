/**
 * stress.js — Stress Test Modul Kasir
 *
 * Tujuan   : Menemukan batas maksimal sistem dengan terus menaikkan beban
 *            hingga performa mulai menurun atau error meningkat.
 * Skenario : Naik bertahap hingga 100 VU, lihat di mana sistem mulai melambat.
 * Jalankan : k6 run k6/cashier/stress.js
 */

import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Trend, Rate } from 'k6/metrics';
import { ensureLoggedIn, h, BASE_URL } from './auth.js';

const responseTrend = new Trend('stress_response_time', true);
const errorRate     = new Rate('stress_error_rate');

export const options = {
    stages: [
        { duration: '2m', target: 20  },  // warm up
        { duration: '2m', target: 40  },  // beban sedang
        { duration: '2m', target: 60  },  // beban tinggi
        { duration: '2m', target: 80  },  // beban sangat tinggi
        { duration: '2m', target: 100 },  // batas maksimal
        { duration: '2m', target: 0   },  // recovery
    ],
    thresholds: {
        http_req_failed:     ['rate<0.10'],
        http_req_duration:   ['p(95)<5000'],
        'stress_error_rate': ['rate<0.10'],
    },
};

export default function () {
    ensureLoggedIn();

    group('Stress — Pesanan Aktif', () => {
        const res = http.get(`${BASE_URL}/cashier/pesanan-aktif`, { headers: h(), redirects: 5 });
        const ok = check(res, {
            'Stress — Pesanan Aktif: status OK': (r) => r.status === 200,
            'Stress — Pesanan Aktif: < 5s':      (r) => r.timings.duration < 5000,
        });
        responseTrend.add(res.timings.duration);
        errorRate.add(!ok);
    });
    sleep(0.5);

    group('Stress — Pesanan Baru', () => {
        const res = http.get(`${BASE_URL}/cashier/pesanan-baru`, { headers: h(), redirects: 5 });
        const ok = check(res, {
            'Stress — Pesanan Baru: status OK': (r) => r.status === 200,
            'Stress — Pesanan Baru: < 5s':      (r) => r.timings.duration < 5000,
        });
        responseTrend.add(res.timings.duration);
        errorRate.add(!ok);
    });

    sleep(1);
}
