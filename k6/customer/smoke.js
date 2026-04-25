/**
 * smoke.js -- Smoke Test Modul Pelanggan
 *
 * Tujuan   : Memastikan semua endpoint pelanggan dapat diakses tanpa error
 *            dengan beban minimal (1 VU, 1 menit).
 * Jalankan : k6 run k6/customer/smoke.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import {
    BASE_URL,
    randomTable,
    randomMenu,
    randomPhone,
    jsonHeaders,
    webHeaders,
    buildOrderPayload,
} from './helpers.js';

export var options = {
    vus:      1,
    duration: '1m',
    thresholds: {
        http_req_failed:   ['rate<0.01'],
        http_req_duration: ['p(95)<3000'],
    },
};

export default function () {
    var tableId = randomTable();
    var menuId  = randomMenu();
    var phone   = randomPhone();

    // 1. Halaman identitas
    var resIdentitas = http.get(
        BASE_URL + '/order?table=' + tableId,
        { headers: webHeaders(), redirects: 5 }
    );
    check(resIdentitas, {
        '[Smoke] Identitas -- status 200':    function (r) { return r.status === 200; },
        '[Smoke] Identitas -- bukan 500':     function (r) { return r.status !== 500; },
        '[Smoke] Identitas -- response < 3s': function (r) { return r.timings.duration < 3000; },
    });
    sleep(0.5);

    // 2. Halaman menu
    var resMenu = http.get(
        BASE_URL + '/customer/menu?table=' + tableId,
        { headers: webHeaders(), redirects: 5 }
    );
    check(resMenu, {
        '[Smoke] Menu -- status 200':    function (r) { return r.status === 200; },
        '[Smoke] Menu -- bukan 500':     function (r) { return r.status !== 500; },
        '[Smoke] Menu -- response < 3s': function (r) { return r.timings.duration < 3000; },
    });
    sleep(0.5);

    // 3. Buat pesanan via API
    var resOrder = http.post(
        BASE_URL + '/api/order',
        buildOrderPayload(tableId, [menuId]),
        { headers: jsonHeaders(), redirects: 5 }
    );
    var orderOk = check(resOrder, {
        '[Smoke] Buat Pesanan -- status 201':     function (r) { return r.status === 201; },
        '[Smoke] Buat Pesanan -- ada order_code': function (r) {
            try { return !!JSON.parse(r.body).order_code; } catch (e) { return false; }
        },
        '[Smoke] Buat Pesanan -- response < 5s':  function (r) { return r.timings.duration < 5000; },
    });
    sleep(0.5);

    // 4. Status pesanan
    if (orderOk) {
        var orderCode = JSON.parse(resOrder.body).order_code;
        var resStatus = http.get(
            BASE_URL + '/customer/order/' + orderCode + '/status',
            { headers: webHeaders(), redirects: 5 }
        );
        check(resStatus, {
            '[Smoke] Status Pesanan -- status 200':    function (r) { return r.status === 200; },
            '[Smoke] Status Pesanan -- response < 3s': function (r) { return r.timings.duration < 3000; },
        });
        sleep(0.5);
    }

    // 5. Riwayat pesanan
    var resRiwayat = http.get(
        BASE_URL + '/customer/riwayat?phone=' + phone,
        { headers: webHeaders(), redirects: 5 }
    );
    check(resRiwayat, {
        '[Smoke] Riwayat -- status 200':    function (r) { return r.status === 200; },
        '[Smoke] Riwayat -- bukan 500':     function (r) { return r.status !== 500; },
        '[Smoke] Riwayat -- response < 3s': function (r) { return r.timings.duration < 3000; },
    });

    sleep(1);
}
