import { router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import usePolling from '@/Hooks/usePolling';
import { formatRupiah } from '@/helpers';
import { WARM_WHITE, ORANGE_100, WARM_TEXT, WARM_TEXT_MUTED, WARM_ICON, WHITE, WARM_BORDER, GREEN, SLATE_MUTED, BRAND_ORANGE, RED_DARK, RED_50, RED_TINT, BROWN } from '@/theme';

export default function QrisStatus({ order }) {
    const isRejected = order.status === 'pending' && !!order.rejection_note;
    const isDone     = order.status === 'completed';

    usePolling(
        () => router.reload({ only: ['order'] }),
        15_000,
        { enabled: !isDone && !isRejected },
    );

    const isWaiting   = order.status === 'pending' && !order.rejection_note;
    const isConfirmed = order.status === 'processing';

    return (
        <CustomerLayout>
            <div style={{
                padding: 24, maxWidth: 430, margin: '0 auto',
                fontFamily: "'Outfit', system-ui, sans-serif",
                minHeight: '100vh', background: WARM_WHITE,
                display: 'flex', flexDirection: 'column', alignItems: 'center',
                justifyContent: 'center',
            }}>

                {/* Menunggu konfirmasi */}
                {isWaiting && (
                    <>
                        <div style={{
                            width: 80, height: 80, borderRadius: '50%', background: ORANGE_100,
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            fontSize: 36, marginBottom: 20,
                        }}>⏳</div>
                        <h1 style={{ fontSize: 22, fontWeight: 700, color: WARM_TEXT, marginBottom: 8, textAlign: 'center' }}>
                            Menunggu Verifikasi
                        </h1>
                        <p style={{ fontSize: 14, color: WARM_TEXT_MUTED, textAlign: 'center', lineHeight: 1.6, marginBottom: 24 }}>
                            Bukti pembayaran QRIS sedang diverifikasi kasir.<br/>
                            Mohon tunggu beberapa saat.
                        </p>
                        <p style={{ fontSize: 12, color: WARM_ICON }}>Halaman ini otomatis update setiap 5 detik</p>
                    </>
                )}

                {/* Dikonfirmasi / Diproses / Siap */}
                {isConfirmed && (
                    <>
                        <div style={{ fontSize: 72, marginBottom: 16 }}>✅</div>
                        <h1 style={{ fontSize: 22, fontWeight: 700, color: WARM_TEXT, marginBottom: 8, textAlign: 'center' }}>
                            Pembayaran Dikonfirmasi!
                        </h1>
                        <p style={{ fontSize: 14, color: WARM_TEXT_MUTED, textAlign: 'center', marginBottom: 24 }}>
                            #{order.order_code} · {formatRupiah(order.total_amount)}
                        </p>
                        <div style={{
                            background: WHITE, borderRadius: 16, border: `1px solid ${WARM_BORDER}`,
                            padding: 18, width: '100%',
                        }}>
                            {[
                                { label: 'Pembayaran Dikonfirmasi', done: true },
                                { label: 'Pesanan Sedang Diproses', done: true },
                            ].map((s, i) => (
                                <div key={i} style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12 }}>
                                    <div style={{
                                        width: 26, height: 26, borderRadius: '50%',
                                        background: s.done ? GREEN : WARM_BORDER,
                                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                                        color: WHITE, fontSize: 13, fontWeight: 700, flexShrink: 0,
                                    }}>
                                        {s.done ? '✓' : ''}
                                    </div>
                                    <span style={{ fontSize: 13, color: s.done ? GREEN : SLATE_MUTED, fontWeight: s.done ? 600 : 400 }}>
                                        {s.label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </>
                )}

                {/* Selesai */}
                {isDone && (
                    <>
                        <div style={{ fontSize: 72, marginBottom: 16 }}>🎉</div>
                        <h1 style={{ fontSize: 22, fontWeight: 700, color: WARM_TEXT, marginBottom: 8, textAlign: 'center' }}>
                            Pesanan Selesai!
                        </h1>
                        <p style={{ fontSize: 14, color: WARM_TEXT_MUTED, textAlign: 'center', marginBottom: 28 }}>
                            Silakan ambil pesanan Anda di kasir.
                        </p>
                        <button
                            onClick={() => router.visit(route('customer.menu'))}
                            style={{
                                width: '100%', height: 50, background: BRAND_ORANGE, color: WHITE,
                                border: 'none', borderRadius: 16, fontSize: 15, fontWeight: 700, cursor: 'pointer',
                            }}
                        >
                            Pesan Lagi
                        </button>
                    </>
                )}

                {/* Ditolak */}
                {isRejected && (
                    <>
                        <div style={{ fontSize: 72, marginBottom: 16 }}>✗</div>
                        <h1 style={{ fontSize: 22, fontWeight: 700, color: RED_DARK, marginBottom: 8, textAlign: 'center' }}>
                            Bukti Pembayaran Ditolak
                        </h1>
                        {order.rejection_note && (
                            <div style={{
                                background: RED_50, border: `1px solid ${RED_TINT}`,
                                borderRadius: 12, padding: 14, width: '100%', marginBottom: 20,
                            }}>
                                <div style={{ fontSize: 13, color: RED_DARK, fontWeight: 600, marginBottom: 4 }}>
                                    Alasan:
                                </div>
                                <div style={{ fontSize: 13, color: BROWN }}>{order.rejection_note}</div>
                            </div>
                        )}
                        <p style={{ fontSize: 14, color: WARM_TEXT_MUTED, textAlign: 'center', marginBottom: 24 }}>
                            Silakan upload ulang bukti pembayaran yang valid.
                        </p>
                        <button
                            onClick={() => router.visit(route('customer.payment.qris', { order: order.id }))}
                            style={{
                                width: '100%', height: 50, background: BRAND_ORANGE, color: WHITE,
                                border: 'none', borderRadius: 16, fontSize: 15, fontWeight: 700, cursor: 'pointer',
                            }}
                        >
                            Upload Ulang Bukti
                        </button>
                    </>
                )}
            </div>
        </CustomerLayout>
    );
}
