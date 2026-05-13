import { useState, useRef } from 'react';
import { router, Link, Head } from '@inertiajs/react';
import { Search, Calendar, CreditCard, ChevronDown } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import CashierLayout from '@/Layouts/CashierLayout';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

const METHOD_LABELS = { cash: 'Tunai', qris: 'QRIS', bayar_nanti: 'Bayar Nanti' };
const TODAY = new Date().toISOString().split('T')[0];

const COLS = [
    { key: 'id',      label: 'ID Pesanan',  width: 150 },
    { key: 'date',    label: 'Tanggal',     width: 120 },
    { key: 'time',    label: 'Waktu',       width: 80  },
    { key: 'total',   label: 'Total',       width: 140 },
    { key: 'payment', label: 'Pembayaran',  width: 110 },
    { key: 'cashier', label: 'Kasir',       flex: 1    },
    { key: 'status',  label: 'Status',      width: 110 },
    { key: 'action',  label: 'Aksi',        width: 70  },
];

export default function RiwayatPesanan({ orders, filters }) {
    const rows      = orders.data ?? [];
    const prevUrl   = orders.prev_page_url ?? null;
    const nextUrl   = orders.next_page_url ?? null;
    const currPage  = orders.current_page  ?? 1;
    const lastPage  = orders.last_page     ?? 1;

    const [search, setSearch] = useState(filters.search  ?? '');
    const [date,   setDate]   = useState(filters.date    ?? TODAY);
    const [method, setMethod] = useState(filters.method  ?? '');
    const searchTimer = useRef(null);

    function apply(overrides = {}) {
        const params = { search, date, method, ...overrides };
        Object.keys(params).forEach(k => { if (params[k] === '') delete params[k]; });
        router.get('/cashier/riwayat', params, { preserveState: true, replace: true });
    }

    function handleSearch(e) {
        const val = e.target.value;
        setSearch(val);
        clearTimeout(searchTimer.current);
        searchTimer.current = setTimeout(() => apply({ search: val }), 400);
    }

    function handleDate(e) { const val = e.target.value; setDate(val); apply({ date: val }); }
    function handleMethod(e) { const val = e.target.value; setMethod(val); apply({ method: val }); }

    return (
        <><Head title="Riwayat Pesanan | W9 Cafe" /><CashierLayout title="Riwayat Pesanan" fullscreen>
            <div className="flex-1 overflow-y-auto p-8 bg-muted">
            <Card className="shadow-sm">
            <CardContent className="p-6">

            <div className="mb-7 flex flex-col gap-1">
                <h1 className="text-3xl font-bold text-foreground m-0 tracking-tight">
                    Riwayat Pesanan
                </h1>
                <p className="text-sm text-muted-foreground m-0">
                    Lihat semua transaksi yang telah selesai
                </p>
            </div>

            <div className="flex items-center gap-3 mb-5 flex-wrap">
                <div className="relative min-w-[200px] flex-1">
                    <Search size={18} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                    <Input
                        type="text"
                        value={search}
                        onChange={handleSearch}
                        placeholder="Cari transaksi..."
                        className="w-full h-11 pl-11 shadow-sm"
                    />
                </div>

                <div className="relative shrink-0">
                    <Calendar size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                    <Input
                        type="date"
                        value={date}
                        onChange={handleDate}
                        className="h-11 w-40 lg:w-45 pl-10 pr-3.5 shadow-sm"
                    />
                </div>

                <div className="relative shrink-0">
                    <CreditCard size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                    <select
                        value={method}
                        onChange={handleMethod}
                        className="h-11 w-40 lg:w-45 border border-border rounded-xl pl-10 pr-9 text-sm text-foreground bg-card outline-none appearance-none cursor-pointer shadow-sm"
                    >
                        <option value="">Semua Metode</option>
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                    <ChevronDown size={14} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                </div>
            </div>

            <Card className="shadow-md">
                <div className="overflow-x-auto">
                <div className="flex items-center bg-muted px-4 py-3 border-b border-border min-w-[780px]">
                    {COLS.map(col => (
                        <div key={col.key} className="shrink-0" style={{ width: col.width, flex: col.flex, flexShrink: col.flex ? undefined : 0 }}>
                            <span className="text-xs font-semibold text-foreground">
                                {col.label}
                            </span>
                        </div>
                    ))}
                </div>

                {rows.length === 0 ? (
                    <div className="text-center py-12 text-sm text-muted-foreground">
                        Tidak ada data riwayat pesanan
                    </div>
                ) : (
                    <div className="min-w-[780px]">
                    {rows.map(order => (
                        <OrderRow key={order.id} order={order} />
                    ))}
                    </div>
                )}
                </div>
            </Card>

            {lastPage > 1 && (
                <div className="flex items-center justify-between mt-4">
                    <span className="text-sm text-muted-foreground">
                        Halaman {currPage} dari {lastPage}
                    </span>
                    <div className="flex gap-2">
                        {prevUrl && (
                            <Link
                                href={prevUrl}
                                className="px-4 py-1.5 rounded-lg text-sm font-medium border border-border text-foreground bg-card no-underline"
                            >← Sebelumnya</Link>
                        )}
                        {nextUrl && (
                            <Link
                                href={nextUrl}
                                className="px-4 py-1.5 rounded-lg text-sm font-medium bg-primary text-primary-foreground border-none no-underline"
                            >Berikutnya →</Link>
                        )}
                    </div>
                </div>
            )}

            </CardContent>
            </Card>
            </div>
        </CashierLayout></>
    );
}

function OrderRow({ order }) {
    const [hovered, setHovered] = useState(false);
    const methodLabel = METHOD_LABELS[order.payment_method] ?? order.payment_method ?? '—';

    return (
        <div
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
            className="flex items-center px-4 py-3.5 border-b border-border transition-colors duration-100"
            style={{ background: hovered ? 'hsl(var(--muted))' : 'hsl(var(--card))' }}
        >
            <div className="shrink-0" style={{ width: 150 }}>
                <span className="text-sm font-semibold text-foreground">
                    {order.order_code}
                </span>
            </div>
            <div className="shrink-0" style={{ width: 120 }}>
                <span className="text-sm text-muted-foreground">
                    {formatDate(order.created_at)}
                </span>
            </div>
            <div className="shrink-0" style={{ width: 80 }}>
                <span className="text-sm text-muted-foreground">
                    {formatTime(order.created_at)}
                </span>
            </div>
            <div className="shrink-0" style={{ width: 140 }}>
                <span className="text-sm font-semibold text-foreground">
                    {formatRupiah(order.total_amount)}
                </span>
            </div>
            <div className="shrink-0" style={{ width: 110 }}>
                <span className="text-sm text-muted-foreground">{methodLabel}</span>
            </div>
            <div className="flex-1 min-w-0">
                <span className="text-sm truncate block text-muted-foreground">
                    {order.cashier_name ?? '—'}
                </span>
            </div>
            <div className="shrink-0" style={{ width: 110 }}>
                <StatusBadge status={order.status} />
            </div>
            <div className="shrink-0" style={{ width: 70 }}>
                <Link
                    href={`/cashier/order/${order.id}`}
                    className="text-sm font-medium no-underline text-primary"
                >
                    Detail
                </Link>
            </div>
        </div>
    );
}
