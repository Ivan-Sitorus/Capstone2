const rupiahFormatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
});

const numberFormatter = new Intl.NumberFormat('id-ID');

const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Jakarta',
});

const timeFormatter = new Intl.DateTimeFormat('id-ID', {
    hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta',
});

// Intl emits a non-breaking space (U+00A0, U+202F on newer ICU) between "Rp" and
// the digits; the UI contract is a regular space so snapshots/render match "Rp 45.000".
const normalizeSpaces = (formatted) => formatted.replace(/[\u00A0\u202F]/g, ' ');

const toNumeric = (value) => {
    if (value == null || value === '') return null;
    const numeric = Number(value);
    return Number.isFinite(numeric) ? numeric : null;
};

export const formatRupiah = (amount) => normalizeSpaces(rupiahFormatter.format(toNumeric(amount) ?? 0));

export const formatNumber = (value) => normalizeSpaces(numberFormatter.format(toNumeric(value) ?? 0));

const toDate = (date) => {
    if (date == null || date === '') return null;
    const parsed = date instanceof Date ? date : new Date(date);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
};

export const formatDate = (date) => {
    const parsed = toDate(date);
    return parsed ? dateFormatter.format(parsed) : '-';
};

export const formatTime = (date) => {
    const parsed = toDate(date);
    return parsed ? timeFormatter.format(parsed) : '-';
};

export const summarizeItems = (items) => {
    if (!Array.isArray(items) || items.length === 0) return '';
    return items.map((i) => `${i.quantity}x ${i.menu?.name ?? i.name}`).join(', ');
};
