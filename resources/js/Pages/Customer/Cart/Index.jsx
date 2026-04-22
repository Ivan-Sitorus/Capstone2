import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { ShoppingBag, Coffee } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import useCart from '@/Hooks/useCart';
import { formatRupiah } from '@/helpers';

const F = '"Plus Jakarta Sans", system-ui, sans-serif';

export default function CustomerCart() {
    const { items, tableId, updateQty, total, count } = useCart();
    const [loading,      setLoading]      = useState(false);
    const [errorMsg,     setErrorMsg]     = useState('');
    const [isMahasiswa,  setIsMahasiswa]  = useState(false);

    const isEmpty = items.length === 0;

    useEffect(() => {
        if (!document.getElementById('pjs-font')) {
            const link = document.createElement('link');
            link.id   = 'pjs-font';
            link.rel  = 'stylesheet';
            link.href = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap';
            document.head.appendChild(link);
        }
    }, []);

    useEffect(() => {
        try {
            const saved = sessionStorage.getItem('w9_customer');
            if (saved) {
                const data = JSON.parse(saved);
                setIsMahasiswa(data.isMahasiswa === true);
            }
        } catch (_) {}
    }, []);

    const totalCashback = isMahasiswa
        ? items.reduce((s, i) => s + (i.cashback ?? 0) * i.quantity, 0)
        : 0;

    const grandTotal = total - totalCashback;

    function handleIncrement(menuId) {
        const item = items.find(i => i.menuId === menuId);
        if (item) updateQty(menuId, item.quantity + 1);
    }

    function handleDecrement(menuId) {
        const item = items.find(i => i.menuId === menuId);
        if (item) updateQty(menuId, item.quantity - 1);
    }

    async function handleCheckout() {
        if (isEmpty) return;
        setErrorMsg('');

        let customer = null;
        try {
            customer = JSON.parse(sessionStorage.getItem('w9_customer') || 'null');
        } catch (_) {}

        if (!customer?.name || !customer?.phone) {
            router.visit(`/order?table=${tableId ?? ''}`);
            return;
        }

        setLoading(true);
        try {
            const res = await axios.post('/api/order', {
                customer_name:  customer.name,
                customer_phone: customer.phone,
                table_id:       customer.tableId,
                is_mahasiswa:   isMahasiswa,
                items: items.map(i => ({ menu_id: i.menuId, quantity: i.quantity })),
            });

            const { order_code } = res.data;
            router.visit(`/customer/payment/${order_code}/choose`);
        } catch (err) {
            const msg = err.response?.data?.message
                ?? err.response?.data?.errors
                ?? 'Terjadi kesalahan. Coba lagi.';
            setErrorMsg(typeof msg === 'object' ? Object.values(msg).flat().join(' ') : msg);
        } finally {
            setLoading(false);
        }
    }

    return (
        <CustomerLayout activeTab="cart">
            {/* ── Header ── */}
            <div style={{ background: '#FFFFFF', padding: '0 20px', borderBottom: '1px solid #F0EBE5' }}>
                <div style={{ height: 54, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <span style={{ fontSize: 18, fontWeight: 800, color: '#1A1814', fontFamily: F, letterSpacing: -0.3 }}>
                        Keranjang
                    </span>
                </div>
                {!isEmpty && (
                    <div style={{ display: 'flex', justifyContent: 'center', paddingBottom: 10 }}>
                        <span style={{ fontSize: 12.5, color: '#A8998A', fontFamily: F }}>
                            {count} item
                        </span>
                    </div>
                )}
            </div>

            {/* ── Empty state ── */}
            {isEmpty ? (
                <div style={{
                    display: 'flex', flexDirection: 'column', alignItems: 'center',
                    justifyContent: 'center', padding: '60px 24px', gap: 16,
                    background: '#F5F5F0', minHeight: 'calc(100vh - 180px)',
                }}>
                    <div style={{
                        width: 80, height: 80, borderRadius: 20,
                        background: '#EFEFEA',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}>
                        <ShoppingBag size={36} color="#C4B5A5" />
                    </div>
                    <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6 }}>
                        <p style={{ fontSize: 16, fontWeight: 700, color: '#1A1814', margin: 0, fontFamily: F }}>
                            Keranjang Kosong
                        </p>
                        <p style={{ fontSize: 13, color: '#A8998A', margin: 0, textAlign: 'center', fontFamily: F }}>
                            Tambahkan menu favoritmu dari halaman menu
                        </p>
                    </div>
                    <button
                        onClick={() => router.visit(`/customer/menu?table=${tableId ?? ''}`)}
                        style={{
                            marginTop: 8, height: 46, padding: '0 28px',
                            background: '#E8763A', color: '#FFFFFF',
                            border: 'none', borderRadius: 50,
                            fontSize: 14, fontWeight: 600, cursor: 'pointer',
                            fontFamily: F,
                            boxShadow: '0 4px 14px rgba(232,118,58,0.28)',
                        }}
                    >
                        Kembali ke Menu
                    </button>
                </div>
            ) : (
                <>
                    <div style={{ background: '#F5F5F0', padding: '14px 16px 160px', display: 'flex', flexDirection: 'column', gap: 10 }}>

                        {/* Cart items */}
                        {items.map((item) => {
                            const itemCashback   = isMahasiswa ? (item.cashback ?? 0) : 0;
                            const effectivePrice = item.price - itemCashback;
                            const itemSubtotal   = effectivePrice * item.quantity;
                            return (
                                <div
                                    key={item.menuId}
                                    style={{
                                        background: '#FFFFFF',
                                        borderRadius: 16,
                                        padding: '14px 16px',
                                        boxShadow: '0 1px 6px rgba(0,0,0,0.06)',
                                    }}
                                >
                                    <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
                                        {/* Image circle */}
                                        <div style={{
                                            width: 52, height: 52, borderRadius: '50%',
                                            background: 'linear-gradient(135deg, #C4956A, #A67B55)',
                                            flexShrink: 0,
                                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                                            overflow: 'hidden',
                                        }}>
                                            {item.image
                                                ? <img src={item.image} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                                                : <Coffee size={22} color="rgba(255,255,255,0.8)" />
                                            }
                                        </div>

                                        {/* Text */}
                                        <div style={{ flex: 1, minWidth: 0 }}>
                                            <div style={{ fontSize: 14, fontWeight: 700, color: '#1A1814', fontFamily: F, marginBottom: 3 }}>
                                                {item.name}
                                            </div>
                                            <div style={{ fontSize: 12.5, color: '#A8998A', fontFamily: F }}>
                                                {formatRupiah(effectivePrice)} × {item.quantity}
                                            </div>
                                            <div style={{ fontSize: 16, fontWeight: 800, color: '#1A1814', fontFamily: F, marginTop: 6, letterSpacing: -0.3 }}>
                                                {formatRupiah(itemSubtotal)}
                                            </div>
                                        </div>

                                        {/* Qty controls */}
                                        <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexShrink: 0, marginTop: 2 }}>
                                            <button
                                                onClick={() => handleDecrement(item.menuId)}
                                                style={{
                                                    width: 30, height: 30, borderRadius: 8,
                                                    background: '#FFFFFF', border: '1.5px solid #D6CFC8',
                                                    cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                    fontSize: 18, fontWeight: 500, color: '#6B5E52', fontFamily: F,
                                                }}
                                            >
                                                −
                                            </button>
                                            <span style={{ fontSize: 15, fontWeight: 700, color: '#1A1814', minWidth: 18, textAlign: 'center', fontFamily: F }}>
                                                {item.quantity}
                                            </span>
                                            <button
                                                onClick={() => handleIncrement(item.menuId)}
                                                style={{
                                                    width: 30, height: 30, borderRadius: 8,
                                                    background: '#E8763A', border: 'none',
                                                    cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                    fontSize: 18, fontWeight: 600, color: '#FFFFFF',
                                                    boxShadow: '0 2px 8px rgba(232,118,58,0.30)',
                                                }}
                                            >
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}

                        {/* Order summary */}
                        <div style={{
                            background: '#FFFFFF', borderRadius: 16,
                            padding: '16px 18px',
                            boxShadow: '0 1px 6px rgba(0,0,0,0.06)',
                        }}>
                            <div style={{ fontSize: 11, fontWeight: 700, color: '#A8998A', letterSpacing: 0.8, marginBottom: 14, fontFamily: F }}>
                                RINGKASAN PESANAN
                            </div>

                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10 }}>
                                <span style={{ fontSize: 14, color: '#6B5E52', fontFamily: F }}>Subtotal</span>
                                <span style={{ fontSize: 14, color: '#1A1814', fontFamily: F }}>{formatRupiah(total)}</span>
                            </div>

                            {isMahasiswa && totalCashback > 0 && (
                                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10 }}>
                                    <span style={{ fontSize: 14, color: '#16A34A', fontFamily: F }}>Cashback Mahasiswa</span>
                                    <span style={{ fontSize: 14, color: '#16A34A', fontFamily: F }}>- {formatRupiah(totalCashback)}</span>
                                </div>
                            )}

                            <div style={{ height: 1, background: '#F0EBE4', marginBottom: 14 }} />

                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                <span style={{ fontSize: 16, fontWeight: 700, color: '#1A1814', fontFamily: F }}>Total</span>
                                <span style={{ fontSize: 18, fontWeight: 800, color: '#1A1814', fontFamily: F, letterSpacing: -0.4 }}>
                                    {formatRupiah(grandTotal)}
                                </span>
                            </div>
                        </div>

                        {errorMsg && (
                            <div style={{
                                background: '#FEF2F2', border: '1px solid #FECACA',
                                borderRadius: 12, padding: '10px 14px',
                                fontSize: 13, color: '#DC2626', fontFamily: F,
                            }}>
                                {errorMsg}
                            </div>
                        )}

                        {/* ── Pay button (inline, bawah ringkasan) ── */}
                        <button
                            onClick={handleCheckout}
                            disabled={loading}
                            style={{
                                width: '100%', height: 52,
                                background: loading ? '#C4B5A5' : '#E8763A',
                                color: '#FFFFFF', border: 'none', borderRadius: 14,
                                fontSize: 15, fontWeight: 700,
                                cursor: loading ? 'not-allowed' : 'pointer',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                boxShadow: loading ? 'none' : '0 6px 18px rgba(232,118,58,0.35)',
                                fontFamily: F, letterSpacing: -0.2,
                            }}
                        >
                            {loading ? 'Memproses...' : `Lanjut Pembayaran  ${formatRupiah(grandTotal)}`}
                        </button>
                    </div>
                </>
            )}
        </CustomerLayout>
    );
}
