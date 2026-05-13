import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { ShoppingBag } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import useCart from '@/Hooks/useCart';
import { formatRupiah } from '@/helpers';
import SharedCartItem from '@/Components/Shared/SharedCartItem';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

export default function CustomerCart() {
    const { items, tableId, removeItem, updateQty, total, count } = useCart();
    const [loading, setLoading] = useState(false);
    const [errorMsg, setErrorMsg] = useState('');
    const [isMahasiswa, setIsMahasiswa] = useState(false);

    const isEmpty = items.length === 0;

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
                customer_name: customer.name,
                customer_phone: customer.phone,
                table_id: customer.tableId,
                is_mahasiswa: isMahasiswa,
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
            <div className="bg-card px-5 border-b border-border">
                <div className="h-[54px] flex items-center justify-center">
                    <span className="text-lg font-extrabold text-foreground tracking-tight">
                        Keranjang
                    </span>
                </div>
                {!isEmpty && (
                    <div className="flex justify-center pb-2.5">
                        <span className="text-xs text-muted-foreground">
                            {count} item
                        </span>
                    </div>
                )}
            </div>

            {isEmpty ? (
                <div className="flex flex-col items-center justify-center gap-5 px-6 h-[calc(100vh-180px)]">
                    <div className="w-20 h-20 rounded-[20px] bg-muted flex items-center justify-center">
                        <ShoppingBag size={36} className="text-muted-foreground/40" />
                    </div>
                    <div className="flex flex-col items-center gap-1.5 text-center">
                        <p className="text-base font-bold text-foreground">
                            Keranjang Kosong
                        </p>
                        <p className="text-sm text-muted-foreground">
                            Tambahkan menu favoritmu dari halaman menu
                        </p>
                    </div>
                    <Button
                        onClick={() => router.visit(`/customer/menu?table=${tableId ?? ''}`)}
                        className="h-[46px] px-7 !rounded-full text-sm font-semibold"
                    >
                        Kembali ke Menu
                    </Button>
                </div>
            ) : (
                <div className="flex flex-col bg-muted h-[calc(100vh-180px)]">
                    <div className="flex-1 overflow-y-auto bg-card px-4 pt-3.5">
                        {items.map((item) => (
                            <SharedCartItem
                                key={item.menuId}
                                item={item}
                                variant="customer"
                                onUpdate={(id, qty) => {
                                    if (qty <= 0) removeItem(id);
                                    else updateQty(id, qty);
                                }}
                                onRemove={(id) => removeItem(id)}
                            />
                        ))}
                    </div>

                    <div className="shrink-0 px-4 pb-4 pt-3 bg-muted">
                        <Card className="rounded-2xl !ring-0 border border-border">
                            <CardContent className="px-[18px] py-4">
                                <div className="text-[11px] font-bold text-muted-foreground tracking-[0.8px] mb-3.5 uppercase">
                                    Ringkasan Pesanan
                                </div>

                                <div className="flex justify-between mb-2.5">
                                    <span className="text-sm text-muted-foreground">Subtotal</span>
                                    <span className="text-sm text-foreground font-medium">{formatRupiah(total)}</span>
                                </div>

                                {isMahasiswa && totalCashback > 0 && (
                                    <div className="flex justify-between mb-2.5">
                                        <span className="text-sm text-green-600">Cashback Mahasiswa</span>
                                        <span className="text-sm text-green-600">- {formatRupiah(totalCashback)}</span>
                                    </div>
                                )}

                                <div className="h-px bg-border mb-3.5" />

                                <div className="flex justify-between items-center">
                                    <span className="text-base font-bold text-foreground">Total</span>
                                    <span className="text-lg font-extrabold text-foreground tracking-tight">
                                        {formatRupiah(grandTotal)}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>

                        {errorMsg && (
                            <div className="bg-destructive/10 border border-destructive/30 rounded-xl px-[14px] py-[10px] text-sm text-destructive mt-3">
                                {errorMsg}
                            </div>
                        )}

                        <Button
                            onClick={handleCheckout}
                            disabled={loading}
                            className="w-full h-[52px] !rounded-full text-[15px] font-bold mt-3"
                        >
                            {loading ? 'Memproses...' : `Bayar Sekarang  ${formatRupiah(grandTotal)}`}
                        </Button>
                    </div>
                </div>
            )}
        </CustomerLayout>
    );
}
