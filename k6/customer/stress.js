/**
 * stress.js -- Stress Test Modul Pelanggan
 *
 * Tujuan   : Menemukan batas maksimal sistem dengan terus menaikkan beban
 *            hingga performa mulai menurun atau error meningkat.
 * Skenario : Naik bertahap hingga 100 VU, lihat di mana sistem mulai melambat.
 *
 * Jalankan : k6 run k6/customer/stress.js
 *            k6 run -e TABLE_IDS=1,2,3 -e MENU_IDS=1,2,3,4,5 k6/customer/stress.js
 */

import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Trend, Rate, Counter } from 'k6/metrics';
import {
    BASE_URL,
    randomTable,
    randomMenus,
    randomPhone,
    jsonHeaders,
    webHeaders,
    buildOrderPayload,
} from './helpers.js';

const menuTrend        = new Trend('stress_menu_duration',    true);
const orderTrend       = new Trend('stress_order_duration',   true);
const statusTrend      = new Trend('stress_status_duration',  true);
const riwayatTrend     = new Trend('stress_riwayat_duration', true);
const errorRate        = new Rate('stress_error_rate');
const orderSuccessRate = new Rate('stress_order_success_rate');
const totalOrders      = new Counter('stress_total_orders');

export const options = {
    stages: [
        { duration: '2m', target: 20  },
        { duration: '2m', target: 40  },
        { duration: '2m', target: 60  },
        { duration: '2m', target: 80  },
        { duration: '2m', target: 100 },
        { duration: '2m', target: 0   },
    ],
    thresholds: {
        http_req_failed:             ['rate<0.10'],
        http_req_duration:           ['p(95)<5000'],
        'stress_menu_duration':      ['p(95)<3000'],
        'stress_order_duration':     ['p(95)<8000'],
        'stress_riwayat_duration':   ['p(95)<5000'],
        'stress_error_rate':         ['rate<0.10'],
        'stress_order_success_rate': ['rate>0.85'],
    },
};

export default function () {
    const tableId = randomTable();
    const phone   = randomPhone();

    // 1. Halaman Menu
    group('Stress -- Halaman Menu', function () {
        var res = http.get(
            BASE_URL + '/customer/menu?table=' + tableId,
            { headers: webHeaders(), redirects: 5 }
        );
        var ok = check(res, {
            'Stress -- Menu: status OK': function (r) { return r.status === 200; },
            'Stress -- Menu: < 3s':      function (r) { return r.timings.duration < 3000; },
        });
        menuTrend.add(res.timings.duration);
        errorRate.add(!ok);
    });
    sleep(0.5);

    // 2. Buat Pesanan
    var orderCode = null;
    group('Stress -- Buat Pesanan', function () {
        var res = http.post(
            BASE_URL + '/api/order',
            buildOrderPayload(tableId, randomMenus(2)),
            { headers: jsonHeaders(), redirects: 5 }
        );
        var ok = check(res, {
            'Stress -- Order: status 201':     function (r) { return r.status === 201; },
            'Stress -- Order: ada order_code': function (r) {
                try { return !!JSON.parse(r.body).order_code; } catch (e) { return false; }
            },
            'Stress -- Order: < 8s': function (r) { return r.timings.duration < 8000; },
        });
        orderTrend.add(res.timings.duration);
        orderSuccessRate.add(ok);
        errorRate.add(!ok);
        if (ok) {
            totalOrders.add(1);
            try { orderCode = JSON.parse(res.body).order_code; } catch (e) {}
        }
    });
    sleep(0.5);

    // 3. Status Pesanan
    if (orderCode) {
        group('Stress -- Status Pesanan', function () {
            var res = http.get(
                BASE_URL + '/customer/order/' + orderCode + '/status',
                { headers: webHeaders(), redirects: 5 }
            );
            var ok = check(res, {
                'Stress -- Status: status OK': function (r) { return r.status === 200; },
                'Stress -- Status: < 5s':      function (r) { return r.timings.duration < 5000; },
            });
            statusTrend.add(res.timings.duration);
            errorRate.add(!ok);
        });
        sleep(0.5);
    }

    // 4. Riwayat Pesanan
    group('Stress -- Riwayat', function () {
        var res = http.get(
            BASE_URL + '/customer/riwayat?phone=' + phone,
            { headers: webHeaders(), redirects: 5 }
        );
        var ok = check(res, {
            'Stress -- Riwayat: status OK': function (r) { return r.status === 200; },
            'Stress -- Riwayat: < 5s':      function (r) { return r.timings.duration < 5000; },
        });
        riwayatTrend.add(res.timings.duration);
        errorRate.add(!ok);
    });

    sleep(1);
}
