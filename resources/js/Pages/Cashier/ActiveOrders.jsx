import { useState, useEffect, useRef } from 'react';
import { router, Head } from '@inertiajs/react';
import axios from 'axios';
import { X, QrCode } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import OrderCard from '@/Components/Cashier/OrderCard';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';
import usePolling from '@/Hooks/usePolling';
import { SLATE_700, SLATE_100, SLATE_300, AMBER_600, AMBER_TINT, AMBER_300, BLUE, BLUE_50, BLUE_300, RED, RED_50, RED_200, SLATE_50, WHITE, SLATE_200, SLATE_900, SLATE_500, SALMON_LIGHT, RED_DARK, RED_MUTED, GREEN_MUTED_LIGHT, GREEN_SAGE } from '@/theme';

export default function ActiveOrders({ orders: initialOrders, counts }) {
    const [activeTab,    setActiveTab]    = useState('all');
    const [qrisOrder,    setQrisOrder]    = useState(null);
    const [rejectNote,   setRejectNote]   = useState('');
    const [cancelTarget, setCancelTarget] = useState(null);
    const [cancelReason, setCancelReason] = useState('');
    const [processing,   setProcessing]   = useState(false);
    const [localOrders,  setLocalOrders]  = useState(initialOrders ?? []);
    const pendingRemoveRef = useRef(new Set());
    const pendingStatusRef = useRef(new Map()); // orderId → optimistic status

    // Sync saat Inertia reload — jaga optimistic state agar polling tidak timpa perubahan in-flight
    useEffect(() => {
        setLocalOrders(
            (initialOrders ?? [])
                .filter(o => !pendingRemoveRef.current.has(o.id))
                .map(o => pendingStatusRef.current.has(o.id)
                    ? { ...o, status: pendingStatusRef.current.get(o.id) }
                    : o
                )
        );
    }, [initialOrders]);

    usePolling(() => router.reload({ only: ['orders', 'counts'] }), 30_000, { immediate: true });

    /* ── Tabs ── */
    const tabs = [
        { key: 'all',         label: `Semua (${counts.all})`,                  color: { text: SLATE_700, bg: SLATE_100, border: SLATE_300 } },
        { key: 'pending',     label: `Pending (${counts.pending})`,             color: { text: AMBER_600, bg: AMBER_TINT, border: AMBER_300 } },
        { key: 'processing',                    label: `Diproses (${counts.processing})`,           color: { text: BLUE, bg: BLUE_50, border: BLUE_300 } },
        { key: 'unpaid', label: `Belum Bayar (${counts.belum_bayar ?? 0})`, color: { text: RED, bg: RED_50, border: RED_200 } },
    ];

    const filteredOrders = (() => {
        switch (activeTab) {
            case 'pending':     return localOrders.filter(o => o.status === 'pending');
            case 'processing':    return localOrders.filter(o => o.status === 'processing');
            case 'unpaid': return localOrders.filter(o => o.is_paid === false);
            default:            return localOrders;
        }
    })();

    /* ── Actions ── */
    async function handleConfirmQris() {
        if (processing || !qrisOrder) return;
        const orderId = qrisOrder.id;
        setProcessing(true);

        // Optimistic: tutup modal + langsung tampilkan sebagai diproses
        pendingStatusRef.current.set(orderId, 'processing');
        setLocalOrders(prev => prev.map(o =>
            o.id === orderId ? { ...o, status: 'processing', payment_proof: null } : o
        ));
        setQrisOrder(null);

        try {
            await axios.patch(route('kasir.order.confirm-qris', { order: orderId }));
            router.reload({
                only: ['orders', 'counts'],
                onFinish: () => pendingStatusRef.current.delete(orderId),
            });
        } catch (_) {
            pendingStatusRef.current.delete(orderId);
            router.reload({ only: ['orders', 'counts'] });
        } finally {
            setProcessing(false);
        }
    }

    async function handleRejectQris() {
        if (processing || !qrisOrder) return;
        setProcessing(true);
        try {
            await axios.patch(route('kasir.order.reject-qris', { order: qrisOrder.id }), { note: rejectNote });
            setQrisOrder(null);
            setRejectNote('');
            router.reload({ only: ['orders', 'counts'] });
        } finally {
            setProcessing(false);
        }
    }

    async function handleMarkDone(orderId, targetStatus) {
        if (processing) return;
        setProcessing(true);

        if (targetStatus === 'completed') {
            pendingRemoveRef.current.add(orderId);
            setLocalOrders(prev => prev.filter(o => o.id !== orderId));
        } else {
            pendingStatusRef.current.set(orderId, targetStatus);
            setLocalOrders(prev => prev.map(o => o.id === orderId ? { ...o, status: targetStatus } : o));
        }

        try {
            await axios.patch(route('kasir.order.update-status', { order: orderId }), { status: targetStatus });
            router.reload({
                only: ['orders', 'counts'],
                onFinish: () => {
                    pendingRemoveRef.current.delete(orderId);
                    pendingStatusRef.current.delete(orderId);
                },
            });
        } catch (_) {
            pendingRemoveRef.current.delete(orderId);
            pendingStatusRef.current.delete(orderId);
            router.reload({ only: ['orders', 'counts'] });
        } finally {
            setProcessing(false);
        }
    }

    async function handleCancelOrder() {
        if (processing || !cancelTarget) return;
        const orderId = cancelTarget.id;
        const reason  = cancelReason.trim();
        setProcessing(true);

        // Optimistic: hapus card dari daftar aktif
        pendingRemoveRef.current.add(orderId);
        setLocalOrders(prev => prev.filter(o => o.id !== orderId));
        setCancelTarget(null);
        setCancelReason('');

        try {
            await axios.patch(route('kasir.order.cancel', { order: orderId }), { reason: reason || null });
            router.reload({
                only: ['orders', 'counts'],
                onFinish: () => pendingRemoveRef.current.delete(orderId),
            });
        } catch (_) {
            pendingRemoveRef.current.delete(orderId);
            router.reload({ only: ['orders', 'counts'] });
        } finally {
            setProcessing(false);
        }
    }

    async function handleConfirmPayment(orderId, paymentMethod) {
        if (processing) return;
        setProcessing(true);
        try {
            await axios.patch(route('kasir.order.confirm-payment', { order: orderId }), { payment_method: paymentMethod });
            router.reload({ only: ['orders', 'counts'] });
        } finally {
            setProcessing(false);
        }
    }

    return (
        <><Head title="Pesanan Aktif | POSMine" /><CashierLayout title="Pesanan Aktif" fullscreen>
            <div style={{ flex: 1, overflowY: 'auto', overflowX: 'hidden', padding: 32, background: SLATE_50, minWidth: 0 }}>
            <div style={{ background: WHITE, borderRadius: 12, padding: 24, border: `1px solid ${SLATE_200}`, boxShadow: '0 2px 8px rgba(15,23,42,0.03)' }}>

            {/* ── Header ── */}
            <div style={{ marginBottom: 20 }}>
                <h1 style={{
                    fontSize: 26, fontWeight: 700, color: SLATE_900,
                    margin: '0 0 4px', letterSpacing: '-0.5px',
                }}>
                    Pesanan Aktif
                </h1>
                <p style={{ fontSize: 14, color: SLATE_500, margin: 0 }}>
                    Kelola semua pesanan yang sedang diproses
                </p>
            </div>

            {/* ── Filter Tabs ── */}
            <div style={{ display: 'flex', gap: 8, marginBottom: 24, flexWrap: 'wrap' }}>
                {tabs.map(tab => {
                    const active = activeTab === tab.key;
                    const color  = tab.color;
                    return (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            style={{
                                height: 36, padding: '0 16px', borderRadius: 100,
                                fontSize: 13, fontWeight: active ? 700 : 500,
                                cursor: 'pointer', transition: 'all 0.15s',
                                background: color.bg,
                                color:      color.text,
                                border:     `1.5px solid ${active ? color.border : 'transparent'}`,
                                opacity:    active ? 1 : 0.65,
                            }}
                        >
                            {tab.label}
                        </button>
                    );
                })}
            </div>

            {/* ── Order Grid ── */}
            {filteredOrders.length === 0 ? (
                <div style={{ textAlign: 'center', color: SLATE_500, paddingTop: 64, fontSize: 14 }}>
                    Tidak ada pesanan aktif
                </div>
            ) : (
                <div style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(3, minmax(0, 1fr))',
                    gap: 18,
                    alignItems: 'start',
                }}>
                    {filteredOrders.map(order => (
                        <OrderCard
                            key={order.id}
                            order={order}
                            onDetail={id => router.visit(route('kasir.order.show', { order: id }))}
                            onOpenQrisModal={o => { setQrisOrder(o); setRejectNote(''); }}
                            onMarkDone={handleMarkDone}
                            onConfirmPayment={handleConfirmPayment}
                            onCancel={o => { setCancelTarget(o); setCancelReason(''); }}
                        />
                    ))}
                </div>
            )}

            {/* ── Modal Konfirmasi Pembatalan ── */}
            {cancelTarget && (
                <div style={{
                    position: 'fixed', inset: 0,
                    background: 'rgba(0,0,0,0.40)',
                    zIndex: 300,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    padding: 16,
                }}>
                    <div style={{
                        background: WHITE, borderRadius: 18,
                        width: '100%', maxWidth: 400,
                        boxShadow: '0 12px 40px rgba(15,23,42,0.18)',
                        overflow: 'hidden',
                    }}>
                        <div style={{ padding: '20px 22px 14px' }}>
                            <h3 style={{
                                margin: '0 0 6px', fontSize: 17, fontWeight: 700,
                                color: SLATE_900, fontFamily: '"DM Sans", system-ui',
                            }}>
                                Batalkan Pesanan?
                            </h3>
                            <p style={{ margin: 0, fontSize: 13, color: SLATE_500, fontFamily: 'Outfit, system-ui', lineHeight: 1.5 }}>
                                Pesanan <strong style={{ color: SLATE_900 }}>#{cancelTarget.order_code}</strong>{cancelTarget.table_number ? ` · Meja ${cancelTarget.table_number}` : ''} akan dibatalkan. Tindakan ini tidak dapat dibatalkan kembali.
                            </p>

                            <label style={{ display: 'block', margin: '16px 0 6px', fontSize: 12, fontWeight: 600, color: SLATE_700, fontFamily: 'Outfit, system-ui' }}>
                                Alasan pembatalan (opsional)
                            </label>
                            <textarea
                                value={cancelReason}
                                onChange={e => setCancelReason(e.target.value)}
                                placeholder="Mis. menu habis, pelanggan batal..."
                                rows={2}
                                maxLength={255}
                                style={{
                                    width: '100%', boxSizing: 'border-box',
                                    border: `1px solid ${SLATE_200}`, borderRadius: 10,
                                    padding: '10px 12px', fontSize: 13, color: SLATE_900,
                                    fontFamily: 'Outfit, system-ui', resize: 'none', outline: 'none',
                                }}
                            />
                        </div>
                        <div style={{ display: 'flex', gap: 10, padding: '0 22px 20px' }}>
                            <button
                                onClick={() => { setCancelTarget(null); setCancelReason(''); }}
                                disabled={processing}
                                style={{
                                    flex: 1, height: 42, background: SLATE_100, color: SLATE_700,
                                    border: 'none', borderRadius: 10, fontSize: 13, fontWeight: 600,
                                    fontFamily: 'Outfit, system-ui', cursor: processing ? 'not-allowed' : 'pointer',
                                }}
                            >
                                Kembali
                            </button>
                            <button
                                onClick={handleCancelOrder}
                                disabled={processing}
                                style={{
                                    flex: 1, height: 42, background: processing ? SALMON_LIGHT : RED_DARK,
                                    color: WHITE, border: 'none', borderRadius: 10,
                                    fontSize: 13, fontWeight: 700, fontFamily: '"DM Sans", system-ui',
                                    cursor: processing ? 'not-allowed' : 'pointer',
                                }}
                            >
                                {processing ? 'Membatalkan...' : 'Ya, Batalkan'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* ── 4c: QRIS Konfirmasi Modal ── */}
            {qrisOrder && (
                <div style={{
                    position: 'fixed', inset: 0,
                    background: 'rgba(0,0,0,0.40)',
                    zIndex: 200,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    padding: '16px',
                }}>
                    <div style={{
                        background: WHITE,
                        borderRadius: 20,
                        width: '100%', maxWidth: 600,
                        maxHeight: 'calc(100vh - 32px)',
                        display: 'flex', flexDirection: 'column',
                        boxShadow: '0 12px 40px rgba(15,23,42,0.125), 0 2px 6px rgba(15,23,42,0.031)',
                        overflow: 'hidden',
                    }}>
                        {/* ── Header ── */}
                        <div style={{
                            display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                            padding: '14px 20px',
                            borderBottom: `1px solid ${SLATE_200}`,
                            flexShrink: 0,
                        }}>
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
                                <span style={{
                                    fontSize: 16, fontWeight: 700, color: SLATE_900,
                                    fontFamily: '"DM Sans", system-ui',
                                }}>
                                    Konfirmasi Pembayaran QRIS
                                </span>
                                <span style={{
                                    fontSize: 12, color: SLATE_500,
                                    fontFamily: 'Outfit, system-ui',
                                }}>
                                    #{qrisOrder.order_code}{qrisOrder.table_number ? ` · Meja ${qrisOrder.table_number}` : ''}
                                </span>
                            </div>
                            <button
                                onClick={() => setQrisOrder(null)}
                                aria-label="Tutup modal"
                                style={{
                                    width: 32, height: 32, borderRadius: 8, flexShrink: 0,
                                    background: SLATE_100, border: 'none', cursor: 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    color: SLATE_500,
                                }}
                            >
                                <X size={16} />
                            </button>
                        </div>

                        {/* ── Body (scrollable) ── */}
                        <div style={{ padding: '14px 20px', display: 'flex', gap: 14, overflowY: 'auto', flex: 1 }}>

                            {/* LEFT: Bukti gambar */}
                            <div style={{ flex: '0 0 200px', display: 'flex', flexDirection: 'column', gap: 8 }}>
                                <span style={{
                                    fontSize: 11, fontWeight: 600, color: SLATE_500,
                                    fontFamily: 'Outfit, system-ui', letterSpacing: '0.3px',
                                }}>
                                    BUKTI TRANSFER
                                </span>
                                <div style={{
                                    background: SLATE_100, borderRadius: 10,
                                    border: `1px solid ${SLATE_200}`, padding: 6,
                                    display: 'flex', flexDirection: 'column', gap: 5,
                                }}>
                                    <img
                                        src={qrisOrder.payment_proof}
                                        alt="Bukti QRIS"
                                        style={{
                                            width: '100%', height: 150,
                                            objectFit: 'contain', borderRadius: 6,
                                            cursor: 'pointer', display: 'block',
                                        }}
                                        onClick={() => window.open(qrisOrder.payment_proof, '_blank')}
                                    />
                                    <span style={{ fontSize: 10, color: SLATE_500, fontFamily: 'Outfit, system-ui', textAlign: 'center' }}>
                                        Klik untuk perbesar
                                    </span>
                                </div>
                            </div>

                            {/* RIGHT: Info + items + textarea */}
                            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 10, minWidth: 0 }}>

                                {/* Payment info */}
                                <div style={{
                                    background: SLATE_50, borderRadius: 10,
                                    border: `1px solid ${SLATE_200}`, padding: '10px 14px',
                                    display: 'flex', flexDirection: 'column', gap: 7,
                                }}>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 12, color: SLATE_500, fontFamily: 'Outfit, system-ui' }}>Metode</span>
                                        <span style={{
                                            display: 'flex', alignItems: 'center', gap: 4,
                                            fontSize: 12, fontWeight: 600, color: SLATE_900,
                                            fontFamily: 'Outfit, system-ui',
                                        }}>
                                            <QrCode size={12} color={BLUE} />
                                            QRIS
                                        </span>
                                    </div>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 12, color: SLATE_500, fontFamily: 'Outfit, system-ui' }}>Waktu Bayar</span>
                                        <span style={{ fontSize: 12, fontWeight: 500, color: SLATE_900, fontFamily: 'Outfit, system-ui' }}>
                                            {formatTime(qrisOrder.created_at)} · {formatDate(qrisOrder.created_at)}
                                        </span>
                                    </div>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 12, color: SLATE_500, fontFamily: 'Outfit, system-ui' }}>Status</span>
                                        <StatusBadge status={qrisOrder.status} />
                                    </div>
                                    <div style={{ height: 1, background: SLATE_200 }} />
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 13, fontWeight: 700, color: SLATE_900, fontFamily: 'Outfit, system-ui' }}>Total</span>
                                        <span style={{
                                            fontSize: 16, fontWeight: 700, color: BLUE,
                                            fontFamily: '"DM Sans", system-ui',
                                        }}>
                                            {formatRupiah(qrisOrder.total_amount)}
                                        </span>
                                    </div>
                                </div>

                                {/* Detail pesanan */}
                                <div style={{
                                    background: SLATE_50, borderRadius: 10,
                                    border: `1px solid ${SLATE_200}`, padding: '10px 14px',
                                    display: 'flex', flexDirection: 'column', gap: 6,
                                }}>
                                    <span style={{
                                        fontSize: 11, fontWeight: 600, color: SLATE_500,
                                        fontFamily: 'Outfit, system-ui', letterSpacing: '0.3px',
                                    }}>
                                        DETAIL PESANAN
                                    </span>
                                    {qrisOrder.items?.map((item, i) => (
                                        <div key={i} style={{
                                            display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                                        }}>
                                            <span style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
                                                <span style={{ fontSize: 12, fontWeight: 600, color: BLUE, fontFamily: 'Outfit, system-ui' }}>
                                                    {item.quantity}x
                                                </span>
                                                <span style={{ fontSize: 12, fontWeight: 500, color: SLATE_900, fontFamily: 'Outfit, system-ui' }}>
                                                    {item.name}
                                                </span>
                                            </span>
                                            <span style={{ fontSize: 12, color: SLATE_500, fontFamily: 'Outfit, system-ui' }}>
                                                {formatRupiah(item.subtotal)}
                                            </span>
                                        </div>
                                    ))}
                                </div>

                            </div>
                        </div>

                        {/* ── Footer ── */}
                        <div style={{
                            padding: '12px 20px 14px',
                            borderTop: `1px solid ${SLATE_200}`,
                            display: 'flex', gap: 10,
                            flexShrink: 0,
                        }}>
                            {/* Tolak */}
                            <button
                                onClick={handleRejectQris}
                                disabled={processing}
                                style={{
                                    flex: 1, height: 40,
                                    background: processing ? SALMON_LIGHT : RED_MUTED,
                                    color: WHITE, border: 'none', borderRadius: 10,
                                    fontSize: 13, fontWeight: 600,
                                    fontFamily: 'Outfit, system-ui',
                                    cursor: processing ? 'not-allowed' : 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                }}
                            >
                                Tolak
                            </button>
                            {/* Konfirmasi Pembayaran */}
                            <button
                                onClick={handleConfirmQris}
                                disabled={processing}
                                style={{
                                    flex: 2, height: 40,
                                    background: processing ? GREEN_MUTED_LIGHT : GREEN_SAGE,
                                    color: WHITE, border: 'none', borderRadius: 10,
                                    fontSize: 13, fontWeight: 700,
                                    fontFamily: '"DM Sans", system-ui',
                                    cursor: processing ? 'not-allowed' : 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    boxShadow: processing ? 'none' : '0 3px 10px rgba(22,163,74,0.145)',
                                }}
                            >
                                Konfirmasi Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            )}
            </div>
            </div>
        </CashierLayout></>
    );
}
