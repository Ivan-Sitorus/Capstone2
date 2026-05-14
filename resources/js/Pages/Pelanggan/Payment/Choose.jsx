import { useState } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import useCart from '@/Hooks/useCart';
import { ChevronLeft, Banknote, QrCode, MapPin, Wallet } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { formatRupiah } from '@/helpers';
import { cn } from '@/lib/utils';

export default function PaymentChoose({ order, items, table_number }) {
    const [selected,   setSelected]   = useState(null);
    const [loading,    setLoading]    = useState(false);
    const [error,      setError]      = useState('');
    const [showCashModal, setShowCashModal] = useState(false);
    const { clearCart } = useCart();

    async function handleLanjut() {
        if (!selected || loading) return;
        setLoading(true);
        setError('');
        try {
            if (selected === 'cash') {
                await axios.post(`/api/order/${order.id}/pay/cash`);
                clearCart();
                setShowCashModal(true);
            } else {
                await axios.post(`/api/order/${order.id}/pay/qris`);
                router.visit(`/customer/payment/${order.order_code}/qris`);
            }
        } catch (err) {
            setError(err.response?.data?.message ?? 'Terjadi kesalahan. Coba lagi.');
        } finally {
            setLoading(false);
        }
    }

    function handleMengerti() {
        let tableId = null;
        try {
            const saved = sessionStorage.getItem('w9_customer');
            if (saved) tableId = JSON.parse(saved)?.tableId;
        } catch (_) {}
        router.visit(tableId ? `/customer/menu?table=${tableId}` : '/customer/menu');
    }

    const METHODS = [
        {
            key:   'cash',
            title: 'Bayar ke Kasir (Cash)',
            desc:  'Bayar langsung di kasir setelah pesanan dikonfirmasi',
            Icon:  Banknote,
            iconBg: 'bg-secondary',
            iconColor: 'text-primary',
        },
        {
            key:   'qris',
            title: 'QRIS',
            desc:  'Pindai kode QR & unggah bukti pembayaran',
            Icon:  QrCode,
            iconBg: 'bg-muted',
            iconColor: 'text-muted-foreground/60',
        },
    ];

    return (
        <CustomerLayout activeTab="cart">
            <div className="bg-card border-b border-border px-6 pb-4 pt-[22px] flex flex-col gap-1">
                <div className="flex items-center gap-[14px]">
                    <button
                        onClick={() => router.visit('/customer/cart')}
                        className="w-9 h-9 rounded-[12px] bg-muted border-none cursor-pointer flex items-center justify-center shrink-0"
                    >
                        <ChevronLeft size={20} className="text-foreground" />
                    </button>
                    <div>
                        <div className="text-xl font-bold text-foreground">
                            Pilih Cara Bayar
                        </div>
                        <div className="text-xs text-muted-foreground">
                            Pesanan #{order.order_code}
                        </div>
                    </div>
                </div>
                {table_number && (
                    <div className="flex items-center gap-1.5 pl-[50px]">
                        <MapPin size={12} className="text-muted-foreground/40" />
                        <span className="text-xs font-medium text-muted-foreground/50">
                            Meja {table_number}
                        </span>
                    </div>
                )}
            </div>

            <div className="px-6 pb-6 flex flex-col gap-[18px]">

                <div className="text-[13px] font-semibold text-muted-foreground/60 tracking-[0.5px] pt-[18px]">
                    RINGKASAN PESANAN
                </div>

                <div className="bg-card rounded-[20px] border border-border overflow-hidden shadow-[0_3px_12px_rgba(45,32,22,0.05)]">
                    {items.map((item, idx) => (
                        <div
                            key={idx}
                            className="flex justify-between items-center px-[18px] py-[14px]"
                            style={{ borderBottom: idx < items.length - 1 ? '1px solid var(--border)' : 'none' }}
                        >
                            <div className="flex items-center gap-[10px]">
                                <div className="w-[26px] h-[26px] rounded-lg bg-secondary flex items-center justify-center shrink-0">
                                    <span className="text-[11px] font-bold text-primary">
                                        {item.qty}x
                                    </span>
                                </div>
                                <span className="text-sm font-medium text-foreground">
                                    {item.name}
                                </span>
                            </div>
                            <span className="text-sm font-semibold text-foreground">
                                {formatRupiah(item.subtotal)}
                            </span>
                        </div>
                    ))}
                    <div className="h-px bg-border" />
                    <div className="flex justify-between items-center px-[18px] pb-4 pt-[10px]">
                        <span className="text-base font-bold text-foreground">
                            Total
                        </span>
                        <span className="text-lg font-bold text-primary">
                            {formatRupiah(order.total_amount)}
                        </span>
                    </div>
                </div>

                <div className="text-[13px] font-semibold text-muted-foreground/60 tracking-[0.5px]">
                    METODE PEMBAYARAN
                </div>

                <div className="flex flex-col gap-[10px]">
                    {METHODS.map(({ key, title, desc, Icon, iconBg, iconColor }) => {
                        const active = selected === key;
                        return (
                            <div
                                key={key}
                                onClick={() => setSelected(key)}
                                className={cn(
                                    'rounded-[16px] px-4 py-4 flex items-center gap-[14px] cursor-pointer transition-all duration-150',
                                    active ? 'bg-secondary border-2 border-primary shadow-[0_2px_10px_rgba(232,118,58,0.12)]' : 'bg-card border border-border',
                                )}
                            >
                                <div className={cn('w-[42px] h-[42px] rounded-[12px] flex items-center justify-center shrink-0', active ? 'bg-secondary' : iconBg)}>
                                    {key === 'qris'
                                        ? <img src="/images/logo-qris.png" alt="QRIS" className="w-7 h-7 object-contain" />
                                        : <Icon size={22} className={active ? 'text-primary' : iconColor} />
                                    }
                                </div>
                                <div className="flex-1">
                                    <div className="text-[15px] font-semibold text-foreground">
                                        {title}
                                    </div>
                                    <div className="text-xs text-muted-foreground mt-0.5">
                                        {desc}
                                    </div>
                                </div>
                                <div className={cn(
                                    'w-[22px] h-[22px] rounded-full shrink-0 flex items-center justify-center',
                                    active ? 'border-2 border-primary' : 'border-[1.5px] border-muted-foreground/30',
                                )}>
                                    {active && (
                                        <div className="w-3 h-3 rounded-full bg-primary" />
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>

                <div className="flex-1" />

                {error && (
                    <div className="bg-destructive/10 border border-destructive/30 rounded-[10px] px-[14px] py-[10px] text-[13px] text-destructive">
                        {error}
                    </div>
                )}

                <button
                    onClick={handleLanjut}
                    disabled={!selected || loading}
                    className={cn(
                        'w-full h-[54px] border-none rounded-[18px] text-base font-bold flex items-center justify-center gap-2 transition-all duration-150',
                        !selected ? 'bg-muted text-muted-foreground/50 cursor-default' : 'bg-primary text-primary-foreground cursor-pointer shadow-[0_4px_16px_rgba(232,118,58,0.30)]',
                    )}
                >
                    {loading ? 'Memproses...' : 'Konfirmasi Pembayaran'}
                </button>
            </div>

            {showCashModal && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center px-6"
                     style={{ background: 'rgba(0,0,0,0.50)' }}>
                    <div className="bg-card rounded-[24px] w-full max-w-[300px] px-6 pb-6 pt-7 flex flex-col items-center gap-4 shadow-[0_8px_30px_rgba(45,32,22,0.20)]">
                        <div className="w-[72px] h-[72px] rounded-full bg-secondary flex items-center justify-center">
                            <Wallet size={36} className="text-primary" />
                        </div>

                        <span className="text-lg font-bold text-foreground text-center">
                            Bayar di Kasir
                        </span>

                        <span className="text-[13px] text-muted-foreground leading-relaxed text-center">
                            Silakan tunjukkan pesanan ini ke kasir dan lakukan pembayaran tunai.
                        </span>

                        <div className="w-full bg-secondary rounded-[14px] px-4 py-3 flex flex-col items-center gap-0.5">
                            <span className="text-[11px] font-medium text-muted-foreground/50">
                                No. Pesanan
                            </span>
                            <span className="text-[15px] font-bold text-foreground tracking-[0.3px] mb-1.5">
                                #{order.order_code}
                            </span>
                            <span className="text-[11px] font-medium text-muted-foreground/50">
                                Total Pembayaran
                            </span>
                            <span className="text-[22px] font-bold text-primary tracking-tight">
                                {formatRupiah(order.total_amount)}
                            </span>
                        </div>

                        <div className="w-full bg-muted rounded-[12px] px-[14px] py-[10px] flex items-start gap-2">
                            <span className="text-xs text-muted-foreground leading-relaxed">
                                Pantau status pesananmu di tab{' '}
                                <strong className="text-primary">Riwayat</strong>
                                {' '}untuk melihat update dari kasir.
                            </span>
                        </div>

                        <button
                            onClick={() => router.visit(`/receipt/${order.order_code}`)}
                            className="w-full h-[46px] bg-transparent border border-muted-foreground/30 rounded-[14px] text-[14px] font-semibold cursor-pointer text-muted-foreground"
                        >
                            Lihat Struk Digital
                        </button>
                        <button
                            onClick={handleMengerti}
                            className="w-full h-[46px] bg-primary text-primary-foreground border-none rounded-[14px] text-[15px] font-bold cursor-pointer shadow-[0_3px_10px_rgba(232,118,58,0.25)]"
                        >
                            Mengerti
                        </button>
                    </div>
                </div>
            )}
        </CustomerLayout>
    );
}
