/**
 * spike.js — Spike Test Modul Kasir
 *
 * Tujuan   : Menguji kemampuan sistem menghadapi lonjakan beban mendadak,
 *            misalnya saat jam makan siang semua kasir login serentak.
 * Skenario : Lonjakan tiba-tiba dari 0 ke 100 VU, lalu turun kembali.
 * Jalankan : k6 run k6/cashier/spike.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';
import { ensureLoggedIn, h, BASE_URL } from './auth.js';

const spikeErrorRate    = new Rate('spike_error_rate');
const spikeResponseTime = new Trend('spike_response_time', true);

export const options = {
    stages: [
        { duration: '30s', target: 5   },  // kondisi normal
        { duration: '10s', target: 100 },  // lonjakan mendadak
        { duration: '2m',  target: 100 },  // tahan beban tinggi
        { duration: '10s', target: 5   },  // turun mendadak
        { duration: '30s', target: 5   },  // recovery
        { duration: '10s', target: 0   },  // selesai
    ],
    thresholds: {
        http_req_failed:    ['rate<0.15'],
        http_req_duration:  ['p(95)<8000'],
        'spike_error_rate': ['rate<0.15'],
    },
};

export default function () {
    ensureLoggedIn();

    const res = http.get(`${BASE_URL}/cashier/pesanan-aktif`, { headers: h(), redirects: 5 });

    const ok = check(res, {
        'Spike — status tidak 500':    (r) => r.status !== 500,
        'Spike — mendapat respons':    (r) => r.status > 0,
        'Spike — dalam batas 8 detik': (r) => r.timings.duration < 8000,
    });

    spikeErrorRate.add(!ok);
    spikeResponseTime.add(res.timings.duration);

    sleep(1);
}
