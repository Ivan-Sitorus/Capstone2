import { Coffee } from 'lucide-react';
import { formatRupiah } from '@/helpers';

const F = '"Plus Jakarta Sans", system-ui, sans-serif';

const STATUS_MAP = {
    pending:  { label: 'Menunggu Konfirmasi', bg: '#FEF3C7', color: '#92400E', dot: '#D97706' },
    diproses: { label: 'Sedang Dibuat',  bg: '#DBEAFE', color: '#1E40AF', dot: '#3B82F6' },
    selesai:  { label: 'Selesai',        bg: '#DCFCE7', color: '#14532D', dot: '#16A34A' },
};

function getPaymentLabel(method) {
    if (method === 'cash') return 'Tunai';
    if (method === 'qris') return 'QRIS';
    return '';
}


export default function RiwayatCard({ order, onDetail }) {
    const s = STATUS_MAP[order.status] ?? { label: order.status, bg: '#F5F0EB', color: '#8C7B6B', dot: '#C4B5A5' };

    const payLabel  = getPaymentLabel(order.payment_method);
    const itemCount = order.items?.length ?? 0;
    const subtitle  = [payLabel, itemCount ? `${itemCount} item` : ''].filter(Boolean).join(' · ');

    const hasItems = Array.isArray(order.items) && order.items.length > 0;

    return (
        <div style={{
            background: '#FFFFFF',
            borderRadius: 16,
            overflow: 'hidden',
            boxShadow: '0 1px 6px rgba(0,0,0,0.07)',
        }}>
            {/* Header row */}
            <div style={{ padding: '14px 16px 12px', display: 'flex', alignItems: 'flex-start', gap: 10 }}>
                <div style={{
                    width: 38, height: 38, borderRadius: '50%',
                    background: '#F5F0EB',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    flexShrink: 0, marginTop: 1,
                }}>
                    <Coffee size={17} color="#C4B5A5" />
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{
                        fontSize: 14, fontWeight: 700, color: '#1A1814',
                        fontFamily: F,
                    }}>
                        {order.order_code}
                    </div>
                    {subtitle ? (
                        <div style={{
                            fontSize: 12, color: '#A8998A', marginTop: 2,
                            fontFamily: F,
                        }}>
                            {subtitle}
                        </div>
                    ) : null}
                </div>
                <span style={{
                    display: 'inline-flex', alignItems: 'center', gap: 5,
                    background: s.bg, color: s.color,
                    borderRadius: 999, padding: '4px 10px',
                    fontSize: 11, fontWeight: 700,
                    fontFamily: F,
                    whiteSpace: 'nowrap', flexShrink: 0,
                }}>
                    <span style={{ width: 6, height: 6, borderRadius: '50%', background: s.dot, flexShrink: 0 }} />
                    {s.label}
                </span>
            </div>

            {/* Divider */}
            <div style={{ height: 1, background: '#F5F0EB', margin: '0 16px' }} />

            {/* Items list */}
            <div style={{ padding: '10px 16px 10px', display: 'flex', flexDirection: 'column', gap: 5 }}>
                {hasItems ? (
                    order.items.map((item, i) => (
                        <div key={i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <span style={{ fontSize: 13, color: '#3D3530', fontFamily: 'Outfit, system-ui' }}>
                                {item.quantity}× {item.name}
                            </span>
                            <span style={{ fontSize: 13, color: '#3D3530', fontFamily: 'Outfit, system-ui' }}>
                                {formatRupiah(item.subtotal)}
                            </span>
                        </div>
                    ))
                ) : (
                    <span style={{ fontSize: 13, color: '#6B5E52', fontFamily: 'Outfit, system-ui' }}>
                        {order.items_summary}
                    </span>
                )}
            </div>

            {/* Divider */}
            <div style={{ height: 1, background: '#F5F0EB', margin: '0 16px' }} />

            {/* Footer */}
            <div style={{ padding: '12px 16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div>
                    <div style={{ fontSize: 10, fontWeight: 700, color: '#A8998A', letterSpacing: 0.5, marginBottom: 2 }}>
                        TOTAL
                    </div>
                    <span style={{
                        fontSize: 16, fontWeight: 800, color: '#1A1814',
                        fontFamily: F,
                        letterSpacing: -0.3,
                    }}>
                        {formatRupiah(order.total_amount)}
                    </span>
                </div>
                <button
                    onClick={() => onDetail(order)}
                    style={{
                        background: '#FFFFFF',
                        color: '#3D3530',
                        border: '1.5px solid #D6CFC8',
                        borderRadius: 10,
                        padding: '9px 18px',
                        fontSize: 13, fontWeight: 700,
                        fontFamily: F,
                        cursor: 'pointer',
                        letterSpacing: -0.1,
                    }}
                >
                    Detail
                </button>
            </div>
        </div>
    );
}
