import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { ClipboardList, X } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import RiwayatCard from '@/Components/Customer/RiwayatCard';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

const TABS = [
    { key: 'all',      label: 'Semua'    },
    { key: 'pending',  label: 'Pending'  },
    { key: 'diproses', label: 'Diproses' },
    { key: 'selesai',  label: 'Selesai'  },
];

function groupByDate(orders, fmtDate) {
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const yday  = new Date(today); yday.setDate(yday.getDate() - 1);
    const groups = new Map();
    orders.forEach(o => {
        const d = new Date(o.created_at); d.setHours(0, 0, 0, 0);
        const t = d.getTime();
        const key = t === today.getTime() ? 'HARI INI'
            : t === yday.getTime()  ? 'KEMARIN'
            : fmtDate(o.created_at).toUpperCase();
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(o);
    });
    return groups;
}

const METHOD_LABEL = { cash: 'Tunai', qris: 'QRIS' };
const F = '"Plus Jakarta Sans", system-ui, sans-serif';

export default function CustomerRiwayat({ orders = [] }) {
    const [activeTab,    setActiveTab]    = useState('all');
    const [receiptOrder, setReceiptOrder] = useState(null);

    useEffect(() => {
        if (!document.getElementById('pjs-font')) {
            const link = document.createElement('link');
            link.id   = 'pjs-font';
            link.rel  = 'stylesheet';
            link.href = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap';
            document.head.appendChild(link);
        }
    }, []);

    /* Nama pelanggan dari sessionStorage sebagai fallback */
    const sessionName = (() => {
        try {
            const s = sessionStorage.getItem('w9_customer');
            return s ? JSON.parse(s)?.name ?? null : null;
        } catch (_) { return null; }
    })();

    /* Jika phone ada di sessionStorage tapi belum ada di URL → reload dengan phone */
    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        if (!params.get('phone')) {
            try {
                const saved = sessionStorage.getItem('w9_customer');
                if (saved) {
                    const data = JSON.parse(saved);
                    if (data?.phone) {
                        router.visit(`/customer/riwayat?phone=${encodeURIComponent(data.phone)}`, {
                            preserveState: true,
                            replace: true,
                        });
                    }
                }
            } catch (_) {}
        }
    }, []);

    /* Auto-refresh tiap 8 detik selama ada order aktif */
    useEffect(() => {
        const hasActive = orders.some(o => o.status !== 'selesai');
        if (!hasActive) return;
        const id = setInterval(() => {
            router.reload({ only: ['orders'], preserveState: true });
        }, 8000);
        return () => clearInterval(id);
    }, [orders]);

    function handleDetail(order) {
        setReceiptOrder(order);
    }

    const filteredOrders = orders.filter(o => {
        if (activeTab === 'all') return true;
        return o.status === activeTab;
    });

    const grouped = groupByDate(filteredOrders, formatDate);

    return (
        <CustomerLayout activeTab="riwayat">
            <div style={{
                display: 'flex', flexDirection: 'column',
                minHeight: 'calc(100vh - 92px)',
                background: '#F5F5F0',
            }}>

                {/* Stats bar */}
                <div style={{ padding: '16px 16px 0' }}>
                    <div style={{
                        background: '#EFEFEA', borderRadius: 14,
                        padding: '12px 16px',
                        display: 'inline-block', minWidth: 120,
                    }}>
                        <div style={{ fontSize: 10, fontWeight: 700, color: '#A8998A', letterSpacing: 0.6, marginBottom: 4 }}>
                            TOTAL ORDER
                        </div>
                        <div style={{ fontSize: 22, fontWeight: 800, color: '#1A1814', fontFamily: F, letterSpacing: -0.5 }}>
                            {orders.length}
                        </div>
                    </div>
                </div>

                {/* Tab bar */}
                <div style={{
                    display: 'flex', alignItems: 'center', gap: 4,
                    padding: '14px 16px 10px',
                    overflowX: 'auto',
                }}>
                    {TABS.map(tab => (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            style={{
                                flexShrink: 0,
                                borderRadius: 999, border: 'none', cursor: 'pointer',
                                padding: '7px 18px',
                                fontSize: 13,
                                fontFamily: F,
                                background: activeTab === tab.key ? '#E8763A' : 'transparent',
                                color:      activeTab === tab.key ? '#FFFFFF' : '#A8998A',
                                fontWeight: activeTab === tab.key ? 700 : 500,
                                boxShadow:  activeTab === tab.key ? '0 3px 10px rgba(232,118,58,0.30)' : 'none',
                                transition: 'background 0.15s, color 0.15s, box-shadow 0.15s',
                            }}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                {/* Order list */}
                <div style={{ padding: '0 16px 28px', display: 'flex', flexDirection: 'column', gap: 0, flex: 1 }}>
                    {filteredOrders.length === 0 ? (
                        <div style={{
                            display: 'flex', flexDirection: 'column',
                            alignItems: 'center', justifyContent: 'center',
                            padding: '56px 0', gap: 14,
                        }}>
                            <div style={{
                                width: 72, height: 72, borderRadius: 20,
                                background: '#EFEFEA',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                            }}>
                                <ClipboardList size={30} color="#C4B5A5" />
                            </div>
                            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 4 }}>
                                <span style={{ fontSize: 15, fontWeight: 700, color: '#1A1814', fontFamily: F }}>
                                    Belum ada pesanan
                                </span>
                                <span style={{ fontSize: 12.5, color: '#A8998A', fontFamily: F }}>
                                    Pesanan kamu akan muncul di sini
                                </span>
                            </div>
                        </div>
                    ) : (
                        Array.from(grouped.entries()).map(([dateLabel, groupOrders]) => (
                            <div key={dateLabel}>
                                <div style={{
                                    fontSize: 11, fontWeight: 700, color: '#A8998A',
                                    letterSpacing: 0.8,
                                    fontFamily: F,
                                    padding: '14px 0 8px',
                                }}>
                                    {dateLabel}
                                </div>
                                <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                                    {groupOrders.map(order => (
                                        <RiwayatCard
                                            key={order.id}
                                            order={order}
                                            onDetail={handleDetail}
                                        />
                                    ))}
                                </div>
                            </div>
                        ))
                    )}
                </div>

            </div>

            {/* ── Receipt Modal (Struk) ── */}
            {receiptOrder && (
                <div
                    onClick={() => setReceiptOrder(null)}
                    style={{
                        position: 'fixed', inset: 0,
                        background: 'rgba(26,24,20,0.60)',
                        backdropFilter: 'blur(4px)',
                        zIndex: 300,
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        padding: '24px 20px',
                    }}
                >
                    <div
                        onClick={e => e.stopPropagation()}
                        style={{
                            background: '#FFFFFF',
                            borderRadius: 24,
                            width: '100%', maxWidth: 340,
                            maxHeight: 'calc(100vh - 48px)',
                            display: 'flex', flexDirection: 'column',
                            boxShadow: '0 20px 60px rgba(26,24,20,0.28)',
                            overflow: 'hidden',
                            fontFamily: F,
                        }}
                    >
                        {/* Color bar top */}
                        <div style={{ height: 3, background: 'linear-gradient(90deg, #E8763A, #FB923C)', flexShrink: 0 }} />

                        {/* Close button */}
                        <div style={{ display: 'flex', justifyContent: 'flex-end', padding: '12px 16px 0' }}>
                            <button
                                onClick={() => setReceiptOrder(null)}
                                style={{
                                    width: 32, height: 32, borderRadius: '50%',
                                    background: '#F7F4F0', border: '1px solid #EDE8E2',
                                    cursor: 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                }}
                            >
                                <X size={15} color="#8C7B6B" />
                            </button>
                        </div>

                        {/* Scrollable content */}
                        <div style={{ overflowY: 'auto', flex: 1, padding: '4px 24px 8px' }}>

                            {/* Logo + cafe name */}
                            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8, paddingBottom: 18 }}>
                                <div style={{
                                    width: 54, height: 54, borderRadius: 14,
                                    overflow: 'hidden',
                                    boxShadow: '0 4px 14px rgba(0,0,0,0.15)',
                                    background: '#1B3A4B',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                }}>
                                    <img
                                        src="/images/logo.jpg"
                                        alt="W9"
                                        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                        onError={e => {
                                            e.target.style.display = 'none';
                                            e.target.parentElement.innerHTML = '<span style="color:white;font-size:18px;font-style:italic;font-weight:700">w9</span>';
                                        }}
                                    />
                                </div>
                                <span style={{
                                    fontSize: 17, fontWeight: 800, color: '#1A1814',
                                    fontFamily: F,
                                    letterSpacing: -0.3,
                                }}>
                                    W9 Cafe
                                </span>
                            </div>

                            {/* Divider */}
                            <div style={{ height: 1, background: '#EDE8E2', marginBottom: 16 }} />

                            {/* Info rows */}
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 9, marginBottom: 16 }}>
                                {[
                                    { label: 'No. Pesanan', value: receiptOrder.order_code },
                                    { label: 'Tanggal',     value: `${formatDate(receiptOrder.created_at)}, ${formatTime(receiptOrder.created_at)}` },
                                    { label: 'Pelanggan',   value: receiptOrder.customer_name ?? sessionName ?? '—' },
                                    { label: 'Pembayaran',  value: METHOD_LABEL[receiptOrder.payment_method] ?? '—' },
                                ].map(row => (
                                    <div key={row.label} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', gap: 12 }}>
                                        <span style={{ fontSize: 12.5, color: '#A8998A', flexShrink: 0 }}>
                                            {row.label}
                                        </span>
                                        <span style={{ fontSize: 13, fontWeight: 600, color: '#1A1814', textAlign: 'right' }}>
                                            {row.value}
                                        </span>
                                    </div>
                                ))}
                            </div>

                            {/* Divider */}
                            <div style={{ height: 1, background: '#EDE8E2', marginBottom: 14 }} />

                            {/* Items table header */}
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                                <span style={{ fontSize: 11, fontWeight: 700, color: '#A8998A', flex: 1, letterSpacing: 0.4 }}>ITEM</span>
                                <span style={{ fontSize: 11, fontWeight: 700, color: '#A8998A', width: 32, textAlign: 'center', letterSpacing: 0.4 }}>QTY</span>
                                <span style={{ fontSize: 11, fontWeight: 700, color: '#A8998A', width: 80, textAlign: 'right', letterSpacing: 0.4 }}>HARGA</span>
                            </div>

                            {/* Items */}
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, marginBottom: 14 }}>
                                {(receiptOrder.items ?? []).map((item, i) => (
                                    <div key={i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                        <span style={{ fontSize: 13, color: '#1A1814', flex: 1 }}>{item.name}</span>
                                        <span style={{ fontSize: 13, color: '#A8998A', width: 32, textAlign: 'center' }}>{item.quantity}×</span>
                                        <span style={{ fontSize: 13, color: '#1A1814', width: 80, textAlign: 'right' }}>{formatRupiah(item.subtotal)}</span>
                                    </div>
                                ))}
                            </div>

                            {/* Divider */}
                            <div style={{ height: 1, background: '#EDE8E2', marginBottom: 12 }} />

                            {/* Subtotal */}
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                                <span style={{ fontSize: 13, color: '#A8998A' }}>Subtotal</span>
                                <span style={{ fontSize: 13, color: '#6B5E52' }}>{formatRupiah(receiptOrder.total_amount)}</span>
                            </div>

                            {/* Total */}
                            <div style={{
                                display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                                marginBottom: 20, padding: '10px 12px',
                                background: '#FFF5EF', borderRadius: 10,
                                border: '1px solid #FCDFC9',
                            }}>
                                <span style={{ fontSize: 15, fontWeight: 800, color: '#1A1814', fontFamily: F }}>Total</span>
                                <span style={{ fontSize: 16, fontWeight: 800, color: '#E8763A', fontFamily: F, letterSpacing: -0.3 }}>
                                    {formatRupiah(receiptOrder.total_amount)}
                                </span>
                            </div>

                            {/* Thank you */}
                            <div style={{ textAlign: 'center', marginBottom: 18 }}>
                                <span style={{ fontSize: 13, fontWeight: 600, color: '#A8998A', fontFamily: F }}>
                                    Terima kasih sudah memesan!
                                </span>
                            </div>
                        </div>

                        {/* Footer: Tutup button */}
                        <div style={{ padding: '10px 20px 22px', flexShrink: 0, borderTop: '1px solid #F0EBE4' }}>
                            <button
                                onClick={() => setReceiptOrder(null)}
                                style={{
                                    width: '100%', height: 50,
                                    background: 'linear-gradient(135deg, #E8763A, #F08050)',
                                    color: '#FFFFFF',
                                    border: 'none', borderRadius: 14,
                                    fontSize: 15, fontWeight: 700, cursor: 'pointer',
                                    fontFamily: F,
                                    boxShadow: '0 6px 18px rgba(232,118,58,0.35)',
                                    letterSpacing: -0.2,
                                }}
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </CustomerLayout>
    );
}
