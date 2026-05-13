import { useState, useEffect } from 'react';
import { router, Link, Head } from '@inertiajs/react';
import { Calendar, Plus, ClipboardList, Clock } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableHeader,
    TableBody,
    TableHead,
    TableRow,
    TableCell,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import CashierLayout from '@/Layouts/CashierLayout';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

const badgeVariant = {
    pending: 'outline',
    confirmed: 'default',
    preparing: 'secondary',
    ready: 'secondary',
    completed: 'default',
    cancelled: 'destructive',
    dibayar: 'default',
    menunggu: 'outline',
    disetujui: 'default',
    ditolak: 'destructive',
};

const statusLabel = {
    pending: 'Pending',
    confirmed: 'Dikonfirmasi',
    preparing: 'Diproses',
    ready: 'Siap',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    dibayar: 'Dibayar',
    menunggu: 'Menunggu',
    disetujui: 'Disetujui',
    ditolak: 'Ditolak',
};

export default function Dashboard({
    totalPenjualan,
    jumlahTransaksi,
    pesananAktif,
    transaksiTerbaru,
}) {
    const [now, setNow] = useState(new Date());
    useEffect(() => {
        const timer = setInterval(() => setNow(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    return (
        <>
            <Head title="Dasbor | W9 Cafe" />
            <CashierLayout title="Dasbor" fullscreen>
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
                                <div className="flex items-center gap-2 bg-card border border-border rounded-lg px-3.5 py-2 text-sm font-medium text-foreground shrink-0">
                                    <Calendar size={16} className="text-muted-foreground" />
                                    {formatDate(now)}, {formatTime(now)}
                                </div>
                            </div>

                            {/* ── B. Stat Cards ── */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-7">
                                <Card size="sm">
                                    <CardContent className="flex flex-col gap-2">
                                        <div className="text-2xl font-bold text-foreground tracking-tight leading-none">
                                            {formatRupiah(totalPenjualan)}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            Total Penjualan Hari Ini
                                        </div>
                                    </CardContent>
                                </Card>
                                <Card size="sm">
                                    <CardContent className="flex flex-col gap-2">
                                        <div className="text-2xl font-bold text-foreground tracking-tight leading-none">
                                            {jumlahTransaksi}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            Jumlah Transaksi
                                        </div>
                                    </CardContent>
                                </Card>
                                <Card size="sm">
                                    <CardContent className="flex flex-col gap-2">
                                        <div className="text-2xl font-bold text-foreground tracking-tight leading-none">
                                            {pesananAktif}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            Pesanan Aktif
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>

                            {/* ── C. Quick Actions ── */}
                            <div className="flex flex-wrap gap-3 mb-7">
                                <Button
                                    onClick={() => router.visit('/cashier/pesanan-baru')}
                                >
                                    <Plus size={16} />
                                    Pesanan Baru
                                </Button>

                                <Button
                                    variant="outline"
                                    onClick={() => router.visit('/cashier/pesanan-aktif')}
                                >
                                    <ClipboardList size={16} />
                                    Lihat Pesanan
                                </Button>

                                <Button
                                    variant="outline"
                                    onClick={() => router.visit('/cashier/riwayat')}
                                >
                                    <Clock size={16} />
                                    Riwayat
                                </Button>
                            </div>

                            {/* ── D. Transaksi Terbaru ── */}
                            <div>
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

                                <Card className="shadow-sm">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>ID Pesanan</TableHead>
                                                <TableHead>Item</TableHead>
                                                <TableHead>Total</TableHead>
                                                <TableHead>Pembayaran</TableHead>
                                                <TableHead>Status</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {transaksiTerbaru.length === 0 ? (
                                                <TableRow>
                                                    <TableCell
                                                        colSpan={5}
                                                        className="text-center text-sm text-muted-foreground py-8"
                                                    >
                                                        Belum ada transaksi hari ini
                                                    </TableCell>
                                                </TableRow>
                                            ) : (
                                                transaksiTerbaru.map((trx) => (
                                                    <TableRow key={trx.id}>
                                                        <TableCell className="font-semibold">
                                                            #{trx.order_code}
                                                        </TableCell>
                                                        <TableCell className="text-muted-foreground max-w-[400px]">
                                                            <span className="block truncate">
                                                                {trx.items_summary || '-'}
                                                            </span>
                                                        </TableCell>
                                                        <TableCell className="font-semibold">
                                                            {formatRupiah(trx.total_amount)}
                                                        </TableCell>
                                                        <TableCell className="capitalize text-muted-foreground">
                                                            {trx.payment_method || '-'}
                                                        </TableCell>
                                                        <TableCell>
                                                            <Badge
                                                                variant={
                                                                    badgeVariant[trx.status] || 'outline'
                                                                }
                                                            >
                                                                {statusLabel[trx.status] || trx.status}
                                                            </Badge>
                                                        </TableCell>
                                                    </TableRow>
                                                ))
                                            )}
                                        </TableBody>
                                    </Table>
                                </Card>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </CashierLayout>
        </>
    );
}
