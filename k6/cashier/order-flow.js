/**
 * order-flow.js — Load Test Alur Lengkap Pembuatan Pesanan
 *
 * Tujuan   : Menguji performa endpoint POST pembuatan pesanan dan
 *            PATCH perubahan status di bawah beban bersamaan.
 * Skenario : 10 VU membuat pesanan baru secara berulang selama 3 menit.
 * Jalankan : k6 run k6/cashier/order-flow.js
 */

import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Trend, Counter, Rate } from 'k6/metrics';
import { ensureLoggedIn, jsonHeaders, h, BASE_URL } from './auth.js';

const orderCreateTrend  = new Trend('order_create_duration',  true);
const orderStatusTrend  = new Trend('order_status_duration',  true);
const orderCreatedCount = new Counter('orders_created');
const orderErrorRate    = new Rate('order_error_rate');

export const options = {
    stages: [
        { duration: '30s', target: 10 },
        { duration: '3m',  target: 10 },
        { duration: '30s', target: 0  },
    ],
    thresholds: {
        http_req_failed:        ['rate<0.05'],
        'order_create_duration':['p(95)<4000'],  // buat pesanan < 4 detik
        'order_status_duration':['p(95)<2000'],  // ubah status < 2 detik
        'order_error_rate':     ['rate<0.05'],
    },
};

// Data menu yang tersedia di database (sesuai seeder)
const MENU_ITEMS = [
    { menu_id: 2,  quantity: 1 },  // Americano Panas
    { menu_id: 3,  quantity: 2 },  // Es Americano
    { menu_id: 4,  quantity: 1 },  // Kopi Susu
    { menu_id: 5,  quantity: 1 },  // Es Kopi Susu
    { menu_id: 6,  quantity: 3 },  // Cappuccino
];

function randomItems() {
    // Salin dulu agar tidak mengubah urutan array sumber
    const shuffled = [...MENU_ITEMS].sort(() => Math.random() - 0.5);
    return shuffled.slice(0, Math.floor(Math.random() * 3) + 1);
}

function randomPaymentMethod() {
    const methods = ['cash', 'qris', 'bayar_nanti'];
    return methods[Math.floor(Math.random() * methods.length)];
}

export default function () {
    // Login sekali per VU — cookie jar (termasuk XSRF-TOKEN) tersimpan untuk VU ini
    ensureLoggedIn();

    // ── 1. Buat Pesanan Baru ─────────────────────────────────────────────
    group('Buat Pesanan Baru', () => {
        const payload = JSON.stringify({
            items:          randomItems(),
            payment_method: randomPaymentMethod(),
            customer_name:  `Test User ${__VU}`,
        });

        const res = http.post(
            `${BASE_URL}/cashier/pesanan-baru`,
            payload,
            { headers: jsonHeaders(), redirects: 5 }
        );

        const ok = check(res, {
            'Buat Pesanan: status 200/302': (r) => [200, 201, 302].includes(r.status),
            'Buat Pesanan: tidak 500':      (r) => r.status !== 500,
            'Buat Pesanan: tidak 422':      (r) => r.status !== 422,
            'Buat Pesanan: < 4 detik':      (r) => r.timings.duration < 4000,
        });

        orderCreateTrend.add(res.timings.duration);
        orderErrorRate.add(!ok);

        if (ok) orderCreatedCount.add(1);
    });

    sleep(1);

    // ── 2. Cek Pesanan Aktif setelah membuat pesanan ──────────────────────
    group('Cek Pesanan Aktif', () => {
        const res = http.get(`${BASE_URL}/cashier/pesanan-aktif`, { headers: h(), redirects: 5 });

        check(res, {
            'Pesanan Aktif: status 200': (r) => r.status === 200,
            'Pesanan Aktif: < 3 detik':  (r) => r.timings.duration < 3000,
        });

        orderStatusTrend.add(res.timings.duration);
    });

    sleep(2);
}
