/**
 * auth.js — Helper autentikasi kasir
 *
 * Di K6, cookie jar bersifat per-VU. Setup() berjalan di konteks
 * terpisah sehingga session tidak bisa dibagikan ke VU.
 * Solusi: setiap VU login sendiri di awal iterasi pertama.
 *
 * Cara pakai di default function:
 *   ensureLoggedIn();
 *   const res = http.get(url); // cookie otomatis ikut
 */

import http from 'k6/http';
import { check, fail } from 'k6';

export const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';

// State per-VU (direset setiap VU baru)
let _loggedIn = false;

function urlDecode(str) {
    return decodeURIComponent(str.replace(/\+/g, ' '));
}

/**
 * Login sekali per VU. Aman dipanggil setiap iterasi —
 * hanya benar-benar login saat belum punya sesi aktif.
 */
export function ensureLoggedIn(
    email    = __ENV.KASIR_EMAIL    || 'kasir@w9cafe.com',
    password = __ENV.KASIR_PASSWORD || 'password'
) {
    if (_loggedIn) return;

    // 1. GET /login → simpan XSRF-TOKEN ke cookie jar VU
    const loginPage = http.get(`${BASE_URL}/login`, {
        headers: { 'Accept': 'text/html' },
    });

    if (loginPage.status !== 200) {
        fail(`[Auth] Halaman login gagal: ${loginPage.status}`);
    }

    // 2. Ambil XSRF-TOKEN dari cookie response
    const xsrfCookie = loginPage.cookies['XSRF-TOKEN'];
    if (!xsrfCookie || xsrfCookie.length === 0) {
        fail('[Auth] XSRF-TOKEN cookie tidak ditemukan');
    }
    const xsrfToken = urlDecode(xsrfCookie[0].value);

    // 3. POST login — cookie jar VU otomatis menyimpan session cookie
    const loginRes = http.post(
        `${BASE_URL}/login`,
        JSON.stringify({ email, password }),
        {
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-XSRF-TOKEN': xsrfToken,
                'X-Inertia':    'true',
                'Referer':      `${BASE_URL}/login`,
            },
            redirects: 10,
        }
    );

    const ok = check(loginRes, {
        '[Auth] Login berhasil': (r) =>
            r.status === 200 || r.url.includes('/cashier/'),
    });

    if (!ok) {
        fail(`[Auth] Login gagal — status: ${loginRes.status}`);
    }

    _loggedIn = true;
}

/**
 * Headers standar untuk GET halaman kasir.
 * TANPA X-Inertia — header ini menyebabkan server mengembalikan
 * 409 saat ada redirect auth, sehingga semua check status 200 gagal.
 */
export function h() {
    return {
        'Accept': 'text/html,application/xhtml+xml',
    };
}
