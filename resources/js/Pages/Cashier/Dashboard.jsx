import { useState, useEffect } from 'react';
import { router, Link, Head } from '@inertiajs/react';
import { Calendar, Plus, ClipboardList, Clock } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import StatBar from '@/Components/Cashier/StatBar';
import StatusBadge from '@/Components/Common/StatusBadge';
import { formatRupiah, formatDate, formatTime } from '@/helpers';
import usePolling from '@/Hooks/usePolling';
import { SLATE_50, WHITE, SLATE_200, SLATE_900, SLATE_500, BLUE, SLATE_700, SLATE_400, SLATE_100 } from '@/theme';

export default function Dashboard({ totalSales, transactionCount, activeOrders, recentTransactions }) {
    const [now, setNow] = useState(new Date());
    usePolling(() => setNow(new Date()), 1000);

    useEffect(() => {
        router.reload({ only: ['totalSales', 'transactionCount', 'activeOrders', 'cashPending', 'qrisPending', 'recentTransactions'] });
    }, []);

    return (
        <><Head title="Dashboard | W9 Cafe" /><CashierLayout title="Dashboard" fullscreen>
            <div style={{ flex: 1, overflowY: 'auto', padding: 32, background: SLATE_50 }}>
            <div style={{ background: WHITE, borderRadius: 12, padding: 24, border: `1px solid ${SLATE_200}`, boxShadow: '0 2px 8px rgba(15,23,42,0.03)' }}>

            {/* ── A. Header ── */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 28 }}>
                <div>
                    <h1 style={{ fontSize: 26, fontWeight: 700, color: SLATE_900, margin: '0 0 4px', letterSpacing: '-0.5px' }}>
                        Dashboard
                    </h1>
                    <p style={{ fontSize: 14, color: SLATE_500, margin: 0 }}>
                        Selamat datang, Kasir! Berikut ringkasan hari ini.
                    </p>
                </div>

                {/* Date chip */}
                <div style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 8,
                    background: WHITE,
                    border: `1px solid ${SLATE_200}`,
                    borderRadius: 8,
                    padding: '8px 14px',
                    fontSize: 13,
                    fontWeight: 500,
                    color: SLATE_900,
                    boxShadow: '0 2px 8px rgba(15,23,42,0.04)',
                    flexShrink: 0,
                }}>
                    <Calendar size={16} color={SLATE_500} />
                    {formatDate(now)}, {formatTime(now)}
                </div>
            </div>

            {/* ── B. Stat Bar ── */}
            <StatBar
                totalSales={totalSales}
                transactionCount={transactionCount}
                activeOrders={activeOrders}
            />

            {/* ── C. Quick Actions ── */}
            <div style={{ display: 'flex', gap: 12, marginBottom: 28 }}>
                <button
                    onClick={() => router.visit(route('kasir.new-order'))}
                    style={{
                        display: 'flex', alignItems: 'center', gap: 8,
                        height: 44, padding: '0 20px',
                        background: BLUE, color: WHITE,
                        border: 'none', borderRadius: 8,
                        fontSize: 14, fontWeight: 600, cursor: 'pointer',
                        boxShadow: '0 4px 16px rgba(59,111,212,0.25)',
                    }}
                >
                    <Plus size={16} />
                    Pesanan Baru
                </button>

                <button
                    onClick={() => router.visit(route('kasir.active-orders'))}
                    style={{
                        display: 'flex', alignItems: 'center', gap: 8,
                        height: 44, padding: '0 20px',
                        background: WHITE, color: SLATE_900,
                        border: `1px solid ${SLATE_200}`, borderRadius: 8,
                        fontSize: 14, fontWeight: 500, cursor: 'pointer',
                        boxShadow: '0 2px 8px rgba(15,23,42,0.04)',
                    }}
                >
                    <ClipboardList size={16} />
                    Lihat Pesanan
                </button>

                <button
                    onClick={() => router.visit(route('kasir.order-history'))}
                    style={{
                        display: 'flex', alignItems: 'center', gap: 8,
                        height: 44, padding: '0 20px',
                        background: WHITE, color: SLATE_900,
                        border: `1px solid ${SLATE_200}`, borderRadius: 8,
                        fontSize: 14, fontWeight: 500, cursor: 'pointer',
                        boxShadow: '0 2px 8px rgba(15,23,42,0.04)',
                    }}
                >
                    <Clock size={16} />
                    Riwayat
                </button>
            </div>

            {/* ── D. Transaksi Terbaru ── */}
            <div>
                {/* Section header */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                    <h2 style={{ fontSize: 18, fontWeight: 600, color: SLATE_900, margin: 0, letterSpacing: '-0.2px' }}>
                        Transaksi Terbaru
                    </h2>
                    <Link
                        href={route('kasir.order-history')}
                        style={{ fontSize: 13, fontWeight: 500, color: BLUE, textDecoration: 'none' }}
                    >
                        Lihat Semua →
                    </Link>
                </div>

                {/* Table card */}
                <div style={{
                    background: WHITE,
                    border: `1px solid ${SLATE_200}`,
                    borderRadius: 16,
                    overflow: 'hidden',
                    boxShadow: '0 4px 14px rgba(15,23,42,0.06)',
                }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                        <thead>
                            <tr style={{ background: SLATE_100 }}>
                                {['ID Pesanan', 'Item', 'Total', 'Pembayaran', 'Status'].map((h, i) => (
                                    <th key={i} style={{
                                        padding: '12px 16px',
                                        fontSize: 12,
                                        fontWeight: 600,
                                        color: SLATE_700,
                                        textAlign: 'left',
                                        textTransform: 'uppercase',
                                        letterSpacing: '0.4px',
                                        borderBottom: `1px solid ${SLATE_200}`,
                                        whiteSpace: 'nowrap',
                                    }}>
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {recentTransactions.length === 0 ? (
                                <tr>
                                    <td colSpan={5} style={{ padding: '32px 16px', textAlign: 'center', color: SLATE_400, fontSize: 14 }}>
                                        Belum ada transaksi hari ini
                                    </td>
                                </tr>
                            ) : (
                                recentTransactions.map((trx, i) => (
                                    <tr
                                        key={trx.id}
                                        style={{
                                            borderBottom: i < recentTransactions.length - 1 ? `1px solid ${SLATE_200}` : 'none',
                                            transition: 'background 0.12s',
                                        }}
                                        onMouseEnter={e => e.currentTarget.style.background = SLATE_50}
                                        onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                                    >
                                        <td style={{ padding: '14px 16px', fontSize: 13, fontWeight: 600, color: SLATE_900, whiteSpace: 'nowrap' }}>
                                            #{trx.order_code}
                                        </td>
                                        <td style={{ padding: '14px 16px', fontSize: 13, color: SLATE_500, maxWidth: 400 }}>
                                            <span style={{ display: 'block', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                                {trx.itemsSummary || '-'}
                                            </span>
                                        </td>
                                        <td style={{ padding: '14px 16px', fontSize: 13, fontWeight: 600, color: SLATE_900, whiteSpace: 'nowrap' }}>
                                            {formatRupiah(trx.total_amount)}
                                        </td>
                                        <td style={{ padding: '14px 16px', fontSize: 13, color: SLATE_500, textTransform: 'capitalize' }}>
                                            {trx.payment_method || '-'}
                                        </td>
                                        <td style={{ padding: '14px 16px' }}>
                                            <StatusBadge status={trx.status} />
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            </div>
            </div>
        </CashierLayout></>
    );
}
