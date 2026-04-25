/**
 * helpers.js -- Shared Utilities Modul Pelanggan
 *
 * Pelanggan TIDAK memerlukan autentikasi server-side.
 * Identitas (nama + nomor HP) hanya disimpan di sessionStorage browser,
 * sehingga setiap VU dapat langsung mengakses endpoint tanpa login.
 *
 * Konfigurasi via environment variable (opsional):
 *   k6 run -e BASE_URL=https://pos-cafe-prototype-main.test
 *           -e TABLE_IDS=1,2,3
 *           -e MENU_IDS=1,2,3,4,5
 *           k6/customer/stress.js
 */

export var BASE_URL = __ENV.BASE_URL || 'https://pos-cafe-prototype-main.test';

var TABLE_IDS = (__ENV.TABLE_IDS || '1,2,3,4,5').split(',').map(Number);
var MENU_IDS  = (__ENV.MENU_IDS  || '1,2,3,4,5').split(',').map(Number);

export function randomFrom(arr) {
    return arr[Math.floor(Math.random() * arr.length)];
}

export function randomTable() {
    return randomFrom(TABLE_IDS);
}

export function randomMenu() {
    return randomFrom(MENU_IDS);
}

export function randomMenus(count) {
    count = count || 2;
    var shuffled = MENU_IDS.slice().sort(function () { return Math.random() - 0.5; });
    return shuffled.slice(0, Math.min(count, MENU_IDS.length));
}

export function randomPhone() {
    var suffix = Math.floor(10000000 + Math.random() * 89999999);
    return '08' + suffix;
}

var SAMPLE_NAMES = [
    'Budi Santoso', 'Siti Rahayu', 'Ahmad Fauzi', 'Dewi Lestari',
    'Eko Prasetyo', 'Rina Wulandari', 'Deni Kurniawan', 'Maya Sari',
    'Andi Wijaya', 'Lina Marlina', 'Hendra Gunawan', 'Yuni Astuti',
];

export function randomName() {
    return SAMPLE_NAMES[Math.floor(Math.random() * SAMPLE_NAMES.length)];
}

export function jsonHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    };
}

export function webHeaders() {
    return {
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    };
}

export function buildOrderPayload(tableId, menuIds, isMahasiswa) {
    isMahasiswa = isMahasiswa || false;
    var items = menuIds.map(function (menuId) {
        return {
            menu_id:  menuId,
            quantity: Math.floor(1 + Math.random() * 2),
        };
    });

    return JSON.stringify({
        customer_name:  randomName(),
        customer_phone: randomPhone(),
        table_id:       tableId,
        is_mahasiswa:   isMahasiswa,
        promotion_ids:  [],
        items:          items,
    });
}
