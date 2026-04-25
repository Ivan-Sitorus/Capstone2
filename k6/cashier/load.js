/**
 * load.js — Load Test Modul Kasir
 *
 * Tujuan   : Menguji performa sistem pada kondisi normal operasional kafe
 *            (simulasi kasir menggunakan sistem secara bersamaan).
 * Skenario : Ramp up 0->5 VU dalam 30 detik, tahan 3 menit, ramp down 30 detik.
 * Catatan  : Maksimal kasir aktif sehari-hari tidak lebih dari 5 orang.
 * Jalankan : k6 run k6/cashier/load.js
 */

import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Trend, Counter, Rate } from 'k6/metrics';
import { ensureLoggedIn, h, BASE_URL } from './auth.js';

const dashboardTrend    = new Trend('dashboard_duration',     true);
const pesananBaruTrend  = new Trend('pesanan_baru_duration',  true);
const pesananAktifTrend = new Trend('pesanan_aktif_duration', true);
const riwayatTrend      = new Trend('riwayat_duration',       true);
const orderDetailTrend  = new Trend('order_detail_duration',  true);
const errorCounter      = new Counter('request_errors');
const successRate       = new Rate('request_success_rate');

export const options = {
    stages: [
        { duration: '30s', target: 5 },
        { duration: '3m',  target: 5 },
        { duration: '30s', target: 0 },
    ],
    thresholds: {
        http_req_failed:          ['rate<0.05'],
        http_req_duration:        ['p(95)<7000'],
        'dashboard_duration':     ['p(95)<7000'],
        'pesanan_baru_duration':  ['p(95)<7000'],
        'pesanan_aktif_duration': ['p(95)<7000'],
        'riwayat_duration':       ['p(95)<7000'],
        'order_detail_duration':  ['p(95)<7000'],
        'request_success_rate':   ['rate>0.90'],
    },
};

export default function () {
    ensureLoggedIn();

    group('Dashboard', () => {
        const res = http.get(`${BASE_URL}/cashier/dashboard`, { headers: h(), redirects: 5 });
        const ok = check(res, {
            'Dashboard: status 200':    (r) => r.status === 200,
            'Dashboard: tidak 500':     (r) => r.status !== 500,
            'Dashboard: response < 7s': (r) => r.timings.duration < 7000,
        });
        dashboardTrend.add(res.timings.duration);
        successRate.add(ok);
        if (!ok) errorCounter.add(1);
    });
    sleep(1);

    group('Pesanan Baru', () => {
        const res = http.get(`${BASE_URL}/cashier/pesanan-baru`, { headers: h(), redirects: 5 });
        const ok = check(res, {
            'Pesanan Baru: status 200':    (r) => r.status === 200,
            'Pesanan Baru: tidak 500':     (r) => r.status !== 500,
            'Pesanan Baru: response < 7s': (r) => r.timings.duration < 7000,
        });
        pesananBaruTrend.add(res.timings.duration);
        successRate.add(ok);
        if (!ok) errorCounter.add(1);
    });
    sleep(1);

    group('Pesanan Aktif', () => {
        const res = http.get(`${BASE_URL}/cashier/pesanan-aktif`, { headers: h(), redirects: 5 });
        const ok = check(res, {
            'Pesanan Aktif: status 200':    (r) => r.status === 200,
            'Pesanan Aktif: tidak 500':     (r) => r.status !== 500,
            'Pesanan Aktif: response < 7s': (r) => r.timings.duration < 7000,
        });
        pesananAktifTrend.add(res.timings.duration);
        successRate.add(ok);
        if (!ok) errorCounter.add(1);
    });
    sleep(1);

    group('Riwayat Pesanan', () => {
        const res = http.get(`${BASE_URL}/cashier/riwayat`, { headers: h(), redirects: 5 });
        const ok = check(res, {
            'Riwayat: status 200':    (r) => r.status === 200,
            'Riwayat: tidak 500':     (r) => r.status !== 500,
            'Riwayat: response < 7s': (r) => r.timings.duration < 7000,
        });
        riwayatTrend.add(res.timings.duration);
        successRate.add(ok);
        if (!ok) errorCounter.add(1);
    });
    sleep(1);

    group('Detail Pesanan', () => {
        const res = http.get(`${BASE_URL}/cashier/order/7396`, { headers: h(), redirects: 5 });
        const ok = check(res, {
            'Detail Pesanan: status 200':    (r) => r.status === 200,
            'Detail Pesanan: tidak 500':     (r) => r.status !== 500,
            'Detail Pesanan: response < 7s': (r) => r.timings.duration < 7000,
        });
        orderDetailTrend.add(res.timings.duration);
        successRate.add(ok);
        if (!ok) errorCounter.add(1);
    });

    sleep(2);
}
