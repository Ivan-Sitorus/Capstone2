import { useState } from 'react';
import { router, Link } from '@inertiajs/react';
import axios from 'axios';
import { ArrowLeft, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import CashierLayout from '@/Layouts/CashierLayout';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

const paymentLabel = { cash: 'Tunai (Cash)', qris: 'QRIS' };

export default function OrderShow({ order }) {
    const [processing,     setProcessing]     = useState(false);
    const [showRejectModal, setShowRejectModal] = useState(false);
    const [rejectNote,      setRejectNote]      = useState('');

    const isCashPending = order.status === 'pending' && order.payment_method === 'cash';
    const isQrisPending = order.status === 'pending' && order.payment_method === 'qris' && !!order.payment_proof;
    const canAdvance    = order.status === 'diproses';

    async function handleAction(url, body = {}) {
        if (processing) return;
        setProcessing(true);
        try {
            await axios.patch(url, body);
            router.reload();
        } finally {
            setProcessing(false);
        }
    }

    function handleAdvance()      { handleAction(`/cashier/order/${order.id}/status`, { status: 'selesai' }); }
    function handleConfirmCash()  { handleAction(`/cashier/order/${order.id}/confirm-cash`); }
    function handleConfirmQris()  { handleAction(`/cashier/order/${order.id}/confirm-qris`); setShowRejectModal(false); }
    function handleRejectQris()   { handleAction(`/cashier/order/${order.id}/reject-qris`, { note: rejectNote }); setShowRejectModal(false); setRejectNote(''); }

    return (
        <CashierLayout title={`Detail Pesanan ${order.order_code}`} fullscreen>
            <div className="flex-1 overflow-y-auto p-8 bg-muted">
            <Card className="shadow-sm">
            <CardContent className="p-6">

            <div className="flex items-center justify-between mb-7">
                <div className="flex items-center gap-3.5">
                    <Link href="/cashier/pesanan-aktif" className="w-9 h-9 rounded-lg bg-card border border-border shrink-0 flex items-center justify-center no-underline shadow-sm text-foreground">
                        <ArrowLeft size={18} />
                    </Link>
                    <div className="flex flex-col gap-1">
                        <h1 className="text-2xl font-bold m-0 tracking-tight text-foreground">
                            Detail Pesanan {order.order_code}
                        </h1>
                        <p className="text-sm m-0 text-muted-foreground">
                            {formatDate(order.created_at)}, {formatTime(order.created_at)} WIB
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-2.5">
                    {canAdvance && (
                        <Button
                            onClick={handleAdvance}
                            disabled={processing}
                        >
                            {processing ? 'Memproses...' : 'Tandai Selesai'}
                        </Button>
                    )}
                    <StatusBadge status={order.status} />
                </div>
            </div>

            {isCashPending && (
                <div className="flex justify-between items-center px-5 py-4 mb-5 rounded-xl bg-amber-50 border border-amber-300">
                    <div className="flex flex-col gap-0.5">
                        <span className="text-sm font-bold text-amber-700">
                            Pelanggan Akan Bayar Tunai
                        </span>
                        <span className="text-sm text-muted-foreground">
                            Total: <strong className="text-foreground">{formatRupiah(order.total_amount)}</strong> — Konfirmasi setelah uang diterima
                        </span>
                    </div>
                    <Button
                        onClick={handleConfirmCash}
                        disabled={processing}
                        className="shrink-0"
                    >
                        ✓ Konfirmasi Pembayaran
                    </Button>
                </div>
            )}

            {isQrisPending && (
                <div className="flex justify-between items-center px-5 py-4 mb-5 rounded-xl bg-blue-50 border border-blue-300">
                    <div className="flex flex-col gap-0.5">
                        <span className="text-sm font-bold text-blue-700">
                            Bukti Pembayaran QRIS Diterima
                        </span>
                        <span className="text-sm text-muted-foreground">
                            Verifikasi bukti transfer pelanggan sebelum memproses pesanan
                        </span>
                    </div>
                    <div className="flex gap-2 shrink-0">
                        <Button
                            variant="outline"
                            onClick={() => setShowRejectModal(true)}
                            className="text-red-600 border-red-400 hover:bg-red-50"
                        >
                            Tolak
                        </Button>
                        <Button
                            onClick={handleConfirmQris}
                            disabled={processing}
                        >
                            ✓ Konfirmasi
                        </Button>
                    </div>
                </div>
            )}

            <div className="flex flex-col lg:flex-row gap-6 items-start">
                <div className="flex-1 min-w-0">
                    <Card className="shadow-md overflow-hidden">
                        <CardHeader className="bg-muted border-b border-border">
                            <CardTitle>Daftar Item Pesanan</CardTitle>
                        </CardHeader>
                        <div className="flex px-5 py-3 border-b border-border">
                            <div className="flex-1">
                                <span className="text-xs font-semibold text-muted-foreground">Nama Item</span>
                            </div>
                            <div className="w-25 shrink-0">
                                <span className="text-xs font-semibold text-muted-foreground">Harga</span>
                            </div>
                            <div className="w-20 shrink-0">
                                <span className="text-xs font-semibold text-muted-foreground">Jumlah</span>
                            </div>
                            <div className="w-30 shrink-0">
                                <span className="text-xs font-semibold text-muted-foreground">Subtotal</span>
                            </div>
                        </div>
                        {order.items.map(item => (
                            <div key={item.id} className="flex px-5 py-3.5 border-b border-border">
                                <div className="flex-1">
                                    <span className="text-sm font-medium text-foreground">
                                        {item.name}
                                    </span>
                                </div>
                                <div className="w-25 shrink-0">
                                    <span className="text-sm text-muted-foreground">
                                        {formatRupiah(item.unit_price)}
                                    </span>
                                </div>
                                <div className="w-20 shrink-0">
                                    <span className="text-sm font-semibold text-foreground">
                                        {item.quantity}
                                    </span>
                                </div>
                                <div className="w-30 shrink-0">
                                    <span className="text-sm font-semibold text-foreground">
                                        {formatRupiah(item.subtotal)}
                                    </span>
                                </div>
                            </div>
                        ))}
                        <div className="flex justify-between px-5 py-4 bg-muted">
                            <span className="text-base font-bold text-foreground">
                                Total Pembayaran
                            </span>
                            <span className="text-xl font-bold text-primary">
                                {formatRupiah(order.total_amount)}
                            </span>
                        </div>
                    </Card>
                </div>

                <div className="w-full lg:w-90 shrink-0">
                    <Card className="shadow-md overflow-hidden">
                        <CardHeader className="bg-muted border-b border-border">
                            <CardTitle>Informasi Pesanan</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4 p-5">
                            <InfoRow label="ID Pesanan"         value={order.order_code}   bold />
                            <InfoRow label="Nama Pelanggan"     value={order.customer_name} bold />
                            <InfoRow label="No. Telepon"        value={order.customer_phone} />
                            <InfoRow label="Meja"               value={order.table_number ? `No. ${order.table_number}` : '—'} />
                            <InfoRow label="Tanggal"            value={formatDate(order.created_at)} />
                            <InfoRow label="Waktu"              value={`${formatTime(order.created_at)} WIB`} />
                            <InfoRow label="Metode Pembayaran"  value={paymentLabel[order.payment_method] ?? '—'} bold />
                            <InfoRow label="Kasir"              value={order.cashier_name ?? '—'} />
                            <div className="flex justify-between items-center">
                                <span className="text-sm font-medium text-muted-foreground">
                                    Status
                                </span>
                                <StatusBadge status={order.status} />
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            {showRejectModal && (
                <div className="fixed inset-0 flex items-center justify-center z-200 bg-black/40">
                    <div className="bg-card rounded-2xl p-7 w-[420px] shadow-[0_20px_60px_rgba(15,23,42,0.20)]">
                        <h3 className="text-lg font-bold mb-2 text-foreground">
                            Tolak Bukti QRIS
                        </h3>
                        <p className="text-sm mb-4 text-muted-foreground">
                            Isi alasan penolakan (opsional). Pelanggan dapat upload ulang.
                        </p>
                        <textarea
                            value={rejectNote}
                            onChange={e => setRejectNote(e.target.value)}
                            placeholder="Contoh: Nominal tidak sesuai / Bukti tidak jelas"
                            rows={3}
                            className="w-full border border-border rounded-xl p-3 text-sm resize-vertical outline-none bg-muted text-foreground"
                        />
                        <div className="flex gap-3 mt-4">
                            <Button
                                variant="outline"
                                onClick={() => setShowRejectModal(false)}
                                className="flex-1"
                            >
                                Batal
                            </Button>
                            <Button
                                onClick={handleRejectQris}
                                className="flex-1"
                                style={{ background: '#C95D4A' }}
                            >
                                Konfirmasi Tolak
                            </Button>
                        </div>
                    </div>
                </div>
            )}
            </CardContent>
            </Card>
            </div>
        </CashierLayout>
    );
}

function InfoRow({ label, value, bold }) {
    return (
        <div className="flex justify-between items-center">
            <span className="text-sm font-medium text-muted-foreground">
                {label}
            </span>
            <span className="text-sm text-foreground" style={{ fontWeight: bold ? 600 : 400 }}>
                {value}
            </span>
        </div>
    );
}
