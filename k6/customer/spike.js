/**
 * spike.js -- Spike Test Modul Pelanggan
 *
 * Tujuan   : Menguji ketahanan sistem terhadap lonjakan pengguna tiba-tiba,
 *            seperti ketika banyak pelanggan scan QR dan memesan serentak
 *            di jam makan siang atau event kampus.
 * Skenario : Idle -> lonjakan 80 VU -> turun -> lonjakan kedua.
 * Jalankan : k6 run k6/customer/spike.js
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

var menuTrend   = new Trend('spike_menu_duration',  true);
var orderTrend  = new Trend('spike_order_duration', true);
var errorRate   = new Rate('spike_error_rate');
var orderSuccess = new Rate('spike_order_success');
var totalOrders  = new Counter('spike_total_orders');

export var options = {
    stages: [
        { duration: '30s', target: 5  },
        { duration: '30s', target: 80 },
        { duration: '1m',  target: 80 },
        { duration: '30s', target: 5  },
        { duration: '1m',  target: 5  },
        { duration: '30s', target: 80 },
        { duration: '1m',  target: 80 },
        { duration: '30s', target: 0  },
    ],
    thresholds: {
        http_req_failed:        ['rate<0.15'],
        http_req_duration:      ['p(95)<8000'],
        'spike_menu_duration':  ['p(95)<5000'],
        'spike_order_duration': ['p(95)<10000'],
        'spike_error_rate':     ['rate<0.15'],
        'spike_order_success':  ['rate>0.80'],
    },
};

export default function () {
    var tableId = randomTable();

    // Halaman Menu
    group('Spike -- Halaman Menu', function () {
        var res = http.get(
            BASE_URL + '/customer/menu?table=' + tableId,
            { headers: webHeaders(), redirects: 5 }
        );
        var ok = check(res, {
            'Spike -- Menu: status OK': function (r) { return r.status === 200; },
            'Spike -- Menu: < 5s':      function (r) { return r.timings.duration < 5000; },
        });
        menuTrend.add(res.timings.duration);
        errorRate.add(!ok);
    });
    sleep(0.3);

    // Buat Pesanan
    group('Spike -- Buat Pesanan', function () {
        var res = http.post(
            BASE_URL + '/api/order',
            buildOrderPayload(tableId, randomMenus(2)),
            { headers: jsonHeaders(), redirects: 5 }
        );
        var ok = check(res, {
            'Spike -- Order: status 201':     function (r) { return r.status === 201; },
            'Spike -- Order: ada order_code': function (r) {
                try { return !!JSON.parse(r.body).order_code; } catch (e) { return false; }
            },
            'Spike -- Order: < 10s': function (r) { return r.timings.duration < 10000; },
        });
        orderTrend.add(res.timings.duration);
        orderSuccess.add(ok);
        errorRate.add(!ok);
        if (ok) totalOrders.add(1);
    });

    sleep(1);
}
