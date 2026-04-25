/**
 * load.js -- Load Test Modul Pelanggan
 *
 * Tujuan   : Menguji performa sistem pada kondisi normal kafe buka
 *            (simulasi beberapa pelanggan memesan secara bersamaan).
 * Skenario : Ramp up 0->10 VU dalam 30 detik, tahan 3 menit, ramp down 30 detik.
 * Catatan  : 10 VU merepresentasikan kondisi operasional normal kafe.
 * Jalankan : k6 run k6/customer/load.js
 */

import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Trend, Counter, Rate } from 'k6/metrics';
import {
    BASE_URL,
    randomTable,
    randomMenus,
    randomPhone,
    jsonHeaders,
    webHeaders,
    buildOrderPayload,
} from './helpers.js';

var menuTrend        = new Trend('menu_duration',         true);
var orderTrend       = new Trend('order_duration',        true);
var riwayatTrend     = new Trend('riwayat_duration',      true);
var statusTrend      = new Trend('status_duration',       true);
var errorCounter     = new Counter('request_errors');
var successRate      = new Rate('request_success_rate');
var orderSuccessRate = new Rate('order_success_rate');

export var options = {
    stages: [
        { duration: '30s', target: 10 },
        { duration: '3m',  target: 10 },
        { duration: '30s', target: 0  },
    ],
    thresholds: {
        http_req_failed:        ['rate<0.25'],
        http_req_duration:      ['p(95)<8000'],
        'menu_duration':        ['p(95)<7000'],
        'order_duration':       ['p(95)<10000'],
        'riwayat_duration':     ['p(95)<7000'],
        'order_success_rate':   ['rate>0.30'],
        'request_success_rate': ['rate>0.70'],
    },
};

export default function () {
    var tableId = randomTable();
    var phone   = randomPhone();

    // Halaman Menu
    group('Halaman Menu', function () {
        var res = http.get(
            BASE_URL + '/customer/menu?table=' + tableId,
            { headers: webHeaders(), redirects: 5 }
        );
        var ok = check(res, {
            'Menu: status 200':    function (r) { return r.status === 200; },
            'Menu: tidak 500':     function (r) { return r.status !== 500; },
            'Menu: response < 7s': function (r) { return r.timings.duration < 7000; },
        });
        menuTrend.add(res.timings.duration);
        successRate.add(ok);
        if (!ok) errorCounter.add(1);
    });
    sleep(1);

    // Buat Pesanan
    var orderCode = null;
    group('Buat Pesanan', function () {
        var res = http.post(
            BASE_URL + '/api/order',
            buildOrderPayload(tableId, randomMenus(2)),
            { headers: jsonHeaders(), redirects: 5 }
        );
        var ok = check(res, {
            'Order: status 201':     function (r) { return r.status === 201; },
            'Order: ada order_code': function (r) {
                try { return !!JSON.parse(r.body).order_code; } catch (e) { return false; }
            },
            'Order: response < 10s': function (r) { return r.timings.duration < 10000; },
        });
        orderTrend.add(res.timings.duration);
        orderSuccessRate.add(ok);
        if (!ok) { errorCounter.add(1); return; }
        try { orderCode = JSON.parse(res.body).order_code; } catch (e) {}
    });
    sleep(1);

    // Status Pesanan
    if (orderCode) {
        group('Status Pesanan', function () {
            var res = http.get(
                BASE_URL + '/customer/order/' + orderCode + '/status',
                { headers: webHeaders(), redirects: 5 }
            );
            var ok = check(res, {
                'Status: status 200':    function (r) { return r.status === 200; },
                'Status: tidak 500':     function (r) { return r.status !== 500; },
                'Status: response < 7s': function (r) { return r.timings.duration < 7000; },
            });
            statusTrend.add(res.timings.duration);
            successRate.add(ok);
            if (!ok) errorCounter.add(1);
        });
        sleep(1);
    }

    // Riwayat Pesanan
    group('Riwayat Pesanan', function () {
        var res = http.get(
            BASE_URL + '/customer/riwayat?phone=' + phone,
            { headers: webHeaders(), redirects: 5 }
        );
        var ok = check(res, {
            'Riwayat: status 200':    function (r) { return r.status === 200; },
            'Riwayat: tidak 500':     function (r) { return r.status !== 500; },
            'Riwayat: response < 7s': function (r) { return r.timings.duration < 7000; },
        });
        riwayatTrend.add(res.timings.duration);
        successRate.add(ok);
        if (!ok) errorCounter.add(1);
    });

    sleep(2);
}
