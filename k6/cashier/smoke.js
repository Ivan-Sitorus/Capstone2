/**
 * smoke.js — Smoke Test Modul Kasir
 *
 * Tujuan : Memastikan semua endpoint kasir dapat diakses tanpa error
 *          dengan beban minimal (1 VU, 1 menit).
 * Jalankan: k6 run k6/cashier/smoke.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import { ensureLoggedIn, h, BASE_URL } from './auth.js';

export const options = {
    vus:      1,
    duration: '1m',
    thresholds: {
        http_req_failed:   ['rate<0.01'],
        http_req_duration: ['p(95)<3000'],
    },
};

export default function () {
    // Login sekali per VU — iterasi berikutnya langsung pakai session
    ensureLoggedIn();

    const endpoints = [
        { name: 'Dashboard',      url: `${BASE_URL}/cashier/dashboard` },
        { name: 'Pesanan Baru',   url: `${BASE_URL}/cashier/pesanan-baru` },
        { name: 'Pesanan Aktif',  url: `${BASE_URL}/cashier/pesanan-aktif` },
        { name: 'Riwayat',        url: `${BASE_URL}/cashier/riwayat` },
        { name: 'Profil',         url: `${BASE_URL}/cashier/profil` },
        { name: 'Detail Pesanan', url: `${BASE_URL}/cashier/order/7396` },
    ];

    for (const ep of endpoints) {
        const res = http.get(ep.url, { headers: h(), redirects: 5 });

        check(res, {
            [`[Smoke] ${ep.name} — status 200`]:    (r) => r.status === 200,
            [`[Smoke] ${ep.name} — bukan 500`]:     (r) => r.status !== 500,
            [`[Smoke] ${ep.name} — response < 3s`]: (r) => r.timings.duration < 3000,
        });

        sleep(0.5);
    }

    sleep(1);
}
