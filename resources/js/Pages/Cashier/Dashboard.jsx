import { useState, useEffect } from 'react';
import { router, Link, Head } from '@inertiajs/react';
import { Calendar, Plus, ClipboardList, Clock } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import CashierLayout from '@/Layouts/CashierLayout';
import StatBar from '@/Components/Cashier/StatBar';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

export default function Dashboard({ totalPenjualan, jumlahTransaksi, pesananAktif, transaksiTerbaru }) {
    const [now, setNow] = useState(new Date());
    useEffect(() => {
        const timer = setInterval(() => setNow(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    return (
        <><Head title="Dasbor | W9 Cafe" /><CashierLayout title="Dasbor" fullscreen>
            <div className="flex-1 overflow-y-auto p-8 bg-muted">
            <Card className="shadow-sm">
            <CardContent className="p-6">

            {/* ── A. Header ── */}
            <div className="flex justify-between items-start mb-7">
                <div>
                    <h1 className="text-3xl font-bold text-foreground m-0 mb-1 tracking-tight">
                        Dasbor
                    </h1>
                    <p className="text-sm text-muted-foreground m-0">
                        Selamat datang, Kasir! Berikut ringkasan hari ini.
                    </p>
                </div>

                {/* Date chip */}
                <div className="flex items-center gap-2 bg-card border border-border rounded-lg px-3.5 py-2 text-sm font-medium text-foreground shrink-0 shadow-[0_2px_8px_rgba(15,23,42,0.04)]">
                    <Calendar size={16} className="text-muted-foreground" />
                    {formatDate(now)}, {formatTime(now)}
                </div>
            </div>

            {/* ── B. Stat Bar ── */}
            <StatBar
                totalPenjualan={totalPenjualan}
                jumlahTransaksi={jumlahTransaksi}
                pesananAktif={pesananAktif}
            />

            {/* ── C. Quick Actions ── */}
            <div className="flex gap-3 mb-7">
                <Button
                    onClick={() => router.visit('/cashier/pesanan-baru')}
                    className="shadow-[0_4px_16px_rgba(59,111,212,0.25)]"
                >
                    <Plus size={16} />
                    Pesanan Baru
                </Button>

                <Button
                    variant="outline"
                    onClick={() => router.visit('/cashier/pesanan-aktif')}
                    className="shadow-sm"
                >
                    <ClipboardList size={16} />
                    Lihat Pesanan
                </Button>

                <Button
                    variant="outline"
                    onClick={() => router.visit('/cashier/riwayat')}
                    className="shadow-sm"
                >
                    <Clock size={16} />
                    Riwayat
                </Button>
            </div>

            {/* ── D. Transaksi Terbaru ── */}
            <div>
                {/* Section header */}
                <div className="flex justify-between items-center mb-4">
                    <h2 className="text-lg font-semibold text-foreground m-0 tracking-tight">
                        Transaksi Terbaru
                    </h2>
                    <Link
                        href="/cashier/riwayat"
                        className="text-sm font-medium text-primary no-underline"
                    >
                        Lihat Semua →
                    </Link>
                </div>

                {/* Table card */}
                <Card className="shadow-md">
                    <div className="overflow-x-auto">
                    <table className="w-full border-collapse">
                        <thead>
                            <tr className="bg-muted">
                                {['ID Pesanan', 'Item', 'Total', 'Pembayaran', 'Status'].map((h, i) => (
                                    <th key={i} className="px-4 py-3 text-xs font-semibold text-left uppercase tracking-wide border-b border-border whitespace-nowrap text-foreground">
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {transaksiTerbaru.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-4 py-8 text-center text-sm text-muted-foreground">
                                        Belum ada transaksi hari ini
                                    </td>
                                </tr>
                            ) : (
                                transaksiTerbaru.map((trx, i) => (
                                    <tr
                                        key={trx.id}
                                        className="border-b border-border hover:bg-muted/50 transition-colors duration-100"
                                    >
                                        <td className="px-4 py-3.5 text-sm font-semibold text-foreground whitespace-nowrap">
                                            #{trx.order_code}
                                        </td>
                                        <td className="px-4 py-3.5 text-sm text-muted-foreground max-w-[400px]">
                                            <span className="block truncate">
                                                {trx.items_summary || '-'}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3.5 text-sm font-semibold text-foreground whitespace-nowrap">
                                            {formatRupiah(trx.total_amount)}
                                        </td>
                                        <td className="px-4 py-3.5 text-sm capitalize text-muted-foreground">
                                            {trx.payment_method || '-'}
                                        </td>
                                        <td className="px-4 py-3.5">
                                            <StatusBadge status={trx.status} />
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                    </div>
                </Card>
            </div>

            </CardContent>
            </Card>
            </div>
        </CashierLayout></>
    );
}
