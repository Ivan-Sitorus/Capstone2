import { router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import usePolling from '@/Hooks/usePolling';
import { formatRupiah, formatDate, formatTime } from '@/helpers';
import { GOLD_DARK, CREAM, GREEN_SAGE, GREEN_TINT, BLUE_DEEP, BLUE_TINT, RED_DEEP, RED_TINT_BG, WARM_TEXT_MUTED, WHITE, WARM_BORDER, WARM_TEXT, WARM_BG, WARM_ICON, BRAND_ORANGE } from '@/theme';

const STATUS_CONFIG = {
    pending:    { icon: '⏳', label: 'Menunggu Pembayaran', color: GOLD_DARK, bg: CREAM },
    confirmed:  { icon: '✓',  label: 'Pesanan Dikonfirmasi', color: GREEN_SAGE, bg: GREEN_TINT },
    preparing:  { icon: '👨‍🍳', label: 'Sedang Diproses',    color: BLUE_DEEP, bg: BLUE_TINT },
    ready:      { icon: '🛎', label: 'Siap Diambil',        color: GREEN_SAGE, bg: GREEN_TINT },
    completed:  { icon: '✅', label: 'Selesai',             color: GREEN_SAGE, bg: GREEN_TINT },
    cancelled:  { icon: '✕',  label: 'Dibatalkan',          color: RED_DEEP, bg: RED_TINT_BG },
};

export default function OrderStatus({ order }) {
    const cfg = STATUS_CONFIG[order.status] ?? STATUS_CONFIG.pending;

    usePolling(
        () => router.reload({ only: ['order'] }),
        30_000,
        { enabled: !['completed', 'cancelled'].includes(order.status) },
    );

    return (
        <CustomerLayout activeTab="riwayat">
            <div style={{
                display: 'flex', flexDirection: 'column',
                gap: 16, padding: '24px 24px 24px',
            }}>

                {/* Status card */}
                <div style={{
                    background: cfg.bg, borderRadius: 20,
                    padding: '28px 24px', textAlign: 'center',
                    border: `1px solid ${cfg.color}30`,
                }}>
                    <div style={{ fontSize: 44, marginBottom: 12 }}>{cfg.icon}</div>
                    <div style={{
                        fontSize: 18, fontWeight: 700, color: cfg.color,
                        fontFamily: '"DM Sans", system-ui, sans-serif',
                        marginBottom: 4,
                    }}>
                        {cfg.label}
                    </div>
                    <div style={{ fontSize: 13, color: WARM_TEXT_MUTED, fontFamily: 'Outfit, system-ui, sans-serif' }}>
                        #{order.order_code}
                    </div>
                </div>

                {/* Detail card */}
                <div style={{
                    background: WHITE, borderRadius: 20,
                    border: `1px solid ${WARM_BORDER}`, padding: 20,
                    boxShadow: '0 4px 14px rgba(45,32,22,0.06)',
                    display: 'flex', flexDirection: 'column', gap: 14,
                }}>
                    <span style={{ fontSize: 15, fontWeight: 700, color: WARM_TEXT, fontFamily: '"DM Sans", system-ui, sans-serif' }}>
                        Detail Pesanan
                    </span>

                    {[
                        ['Tanggal', `${formatTime(order.created_at)} - ${formatDate(order.created_at)}`],
                        ['Item',    order.itemsSummary],
                        ['Metode',  order.payment_method ? order.payment_method.toUpperCase() : '-'],
                        ['Total',   formatRupiah(order.total_amount)],
                    ].map(([label, value]) => (
                        <div key={label} style={{
                            display: 'flex', justifyContent: 'space-between',
                            paddingBottom: 12, borderBottom: `1px solid ${WARM_BG}`,
                        }}>
                            <span style={{ fontSize: 13, color: WARM_ICON, fontFamily: 'Outfit, system-ui, sans-serif' }}>
                                {label}
                            </span>
                            <span style={{
                                fontSize: 13, fontWeight: 600, color: WARM_TEXT,
                                fontFamily: 'Outfit, system-ui, sans-serif',
                                maxWidth: '55%', textAlign: 'right',
                            }}>
                                {value}
                            </span>
                        </div>
                    ))}
                </div>

                {/* Back button */}
                <button
                    onClick={() => router.visit(route('customer.menu'))}
                    style={{
                        background: BRAND_ORANGE, color: WHITE,
                        border: 'none', borderRadius: 50,
                        height: 52, width: '100%',
                        fontSize: 15, fontWeight: 600,
                        fontFamily: 'Outfit, system-ui, sans-serif',
                        cursor: 'pointer',
                        boxShadow: '0 2px 8px rgba(232,118,58,0.25)',
                    }}
                >
                    Pesan Lagi
                </button>

            </div>
        </CustomerLayout>
    );
}
