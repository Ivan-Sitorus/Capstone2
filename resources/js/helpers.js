export const formatRupiah = (amount) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

export const formatDate = (date) =>
    new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Jakarta' }).format(new Date(date));

export const formatTime = (date) =>
    new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta' }).format(new Date(date));

export const summarizeItems = (items) =>
    items.map(i => `${i.quantity}x ${i.menu.name}`).join(', ');
