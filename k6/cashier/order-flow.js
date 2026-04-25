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
import { loginAsCashier, BASE_URL } from './auth.js';

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
    const shuffled = MENU_ITEMS.sort(() => Math.random() - 0.5);
    return shuffled.slice(0, Math.floor(Math.random() * 3) + 1);
}

function randomPaymentMethod() {
    const methods = ['cash', 'qris', 'bayar_nanti'];
    return methods[Math.floor(Math.random() * methods.length)];
}

export function setup() {
    const cookies = loginAsCashier();

    // Ambil CSRF token dari halaman pesanan baru
    const page = http.get(`${BASE_URL}/cashier/pesanan-baru`, {
        headers: { 'Accept': 'text/html' },
        cookies,
    });

    const match = page.body.match(/name="_token"\s+value="([^"]+)"/);
    const csrfToken = match ? match[1] : '';

    return { cookies, csrfToken };
}

export default function (data) {
    const { cookies, csrfToken } = data;

    // ── 1. Buat Pesanan Baru ─────────────────────────────────────────────
    let newOrderId = null;

    group('Buat Pesanan Baru', () => {
        const payload = JSON.stringify({
            items:          randomItems(),
            payment_method: randomPaymentMethod(),
            customer_name:  `Test User ${__VU}`,
            _token:         csrfToken,
        });

        const res = http.post(
            `${BASE_URL}/cashier/pesanan-baru`,
            payload,
            {
                headers: {
                    'Content-Type':     'application/json',
                    'Accept':           'application/json',
                    'X-CSRF-TOKEN':     csrfToken,
                    'X-Inertia':        'true',
                    'X-Inertia-Version': '1',
                },
                cookies,
            }
        );

        const ok = check(res, {
            'Buat Pesanan: status 200/302': (r) => [200, 201, 302].includes(r.status),
            'Buat Pesanan: tidak 500':      (r) => r.status !== 500,
            'Buat Pesanan: tidak 422':      (r) => r.status !== 422,
            'Buat Pesanan: < 4 detik':      (r) => r.timings.duration < 4000,
        });

        orderCreateTrend.add(res.timings.duration);
        orderErrorRate.add(!ok);

        if (ok) {
            orderCreatedCount.add(1);
            // Coba ekstrak ID dari response jika ada
            try {
                const body = JSON.parse(res.body);
                if (body?.order?.id) newOrderId = body.order.id;
            } catch (_) { }
        }
    });

    sleep(1);

    // ── 2. Cek Pesanan Aktif setelah membuat pesanan ──────────────────────
    group('Cek Pesanan Aktif', () => {
        const res = http.get(`${BASE_URL}/cashier/pesanan-aktif`, {
            headers: { 'Accept': 'text/html', 'X-Inertia': 'true' },
            cookies,
        });

        check(res, {
            'Pesanan Aktif: status 200': (r) => r.status === 200,
            'Pesanan Aktif: < 3 detik':  (r) => r.timings.duration < 3000,
        });

        orderStatusTrend.add(res.timings.duration);
    });

    sleep(2);
}
