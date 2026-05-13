import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { ClipboardList, X } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import RiwayatCard from '@/Components/Customer/RiwayatCard';
import { formatRupiah, formatDate, formatTime } from '@/helpers';
import { cn } from '@/lib/utils';

const TABS = [
    { key: 'all',      label: 'Semua'    },
    { key: 'pending',  label: 'Menunggu'  },
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

export default function CustomerRiwayat({ orders = [] }) {
    const [activeTab,    setActiveTab]    = useState('all');
    const [receiptOrder, setReceiptOrder] = useState(null);
    const [logoError,    setLogoError]    = useState(false);

    const sessionName = (() => {
        try {
            const s = sessionStorage.getItem('w9_customer');
            return s ? JSON.parse(s)?.name ?? null : null;
        } catch (_) { return null; }
    })();

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        if (!params.get('phone')) {(() => {
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
            <div className="flex flex-col bg-muted" style={{ minHeight: 'calc(100vh - 92px)' }}>

                <div className="px-4 pt-4">
                    <div className="bg-muted/50 rounded-[14px] px-4 py-3 inline-block min-w-[120px]">
                        <div className="text-[10px] font-bold text-muted-foreground/60 tracking-[0.6px] mb-1">
                            TOTAL PESANAN
                        </div>
                        <div className="text-[22px] font-extrabold text-foreground tracking-tight">
                            {orders.length}
                        </div>
                    </div>
                </div>

                <div className="flex items-center gap-1 px-4 pb-[10px] pt-[14px] overflow-x-auto">
                    {TABS.map(tab => (
                        <button
                            key={tab.key}
                            onClick={() => setActiveTab(tab.key)}
                            className={cn(
                                'shrink-0 rounded-full border-none cursor-pointer px-[18px] py-[7px] text-[13px] min-h-[44px]',
                                'transition-colors duration-150',
                                activeTab === tab.key
                                    ? 'bg-primary text-primary-foreground font-bold shadow-[0_3px_10px_rgba(232,118,58,0.30)]'
                                    : 'bg-transparent text-muted-foreground font-medium',
                            )}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                <div className="px-4 pb-7 flex flex-col gap-0 flex-1">
                    {filteredOrders.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-[56px] gap-[14px]">
                            <div className="w-[72px] h-[72px] rounded-[20px] bg-muted/50 flex items-center justify-center">
                                <ClipboardList size={30} className="text-muted-foreground/40" />
                            </div>
                            <div className="flex flex-col items-center gap-1">
                                <span className="text-[15px] font-bold text-foreground">
                                    Belum ada pesanan
                                </span>
                                <span className="text-[12.5px] text-muted-foreground/70">
                                    Pesanan kamu akan muncul di sini
                                </span>
                            </div>
                        </div>
                    ) : (
                        Array.from(grouped.entries()).map(([dateLabel, groupOrders]) => (
                            <div key={dateLabel}>
                                <div className="text-[11px] font-bold text-muted-foreground/60 tracking-[0.8px] pt-[14px] pb-2">
                                    {dateLabel}
                                </div>
                                <div className="flex flex-col gap-[10px]">
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

            {receiptOrder && (
                <div
                    onClick={() => setReceiptOrder(null)}
                    className="fixed inset-0 z-[300] flex items-center justify-center px-5 py-6"
                    style={{ background: 'rgba(26,24,20,0.60)', backdropFilter: 'blur(4px)' }}
                >
                    <div
                        onClick={e => e.stopPropagation()}
                        className="bg-card rounded-[24px] w-full max-w-[340px] flex flex-col overflow-hidden"
                        style={{ maxHeight: 'calc(100vh - 48px)', boxShadow: '0 20px 60px rgba(26,24,20,0.28)' }}
                    >
                        <div className="h-[3px] shrink-0" style={{ background: 'linear-gradient(90deg, #E8763A, #FB923C)' }} />

                        <div className="flex justify-end px-4 pt-3">
                            <button
                                onClick={() => setReceiptOrder(null)}
                                className="w-8 h-8 rounded-full bg-muted/30 border border-border cursor-pointer flex items-center justify-center"
                            >
                                <X size={15} className="text-muted-foreground/50" />
                            </button>
                        </div>

                        <div className="overflow-y-auto flex-1 px-6 pb-2 pt-1">
                            <div className="flex flex-col items-center gap-2 pb-[18px]">
                                <div className="w-[54px] h-[54px] rounded-[14px] overflow-hidden flex items-center justify-center"
                                     style={{ boxShadow: '0 4px 14px rgba(0,0,0,0.15)', background: logoError ? '#1B3A4B' : 'transparent' }}>
                                    {logoError ? (
                                        <span style={{color:'white', fontSize:'18px', fontStyle:'italic', fontWeight:700}}>w9</span>
                                    ) : (
                                        <img
                                            src="/images/logo.jpg"
                                            alt="W9"
                                            className="w-full h-full object-cover"
                                            onError={() => setLogoError(true)}
                                        />
                                    )}
                                </div>
                                <span className="text-[17px] font-extrabold text-foreground tracking-tight">
                                    W9 Cafe
                                </span>
                            </div>

                            <div className="h-px bg-border mb-4" />

                            <div className="flex flex-col gap-[9px] mb-4">
                                {[
                                    { label: 'No. Pesanan', value: receiptOrder.order_code },
                                    { label: 'Tanggal',     value: `${formatDate(receiptOrder.created_at)}, ${formatTime(receiptOrder.created_at)}` },
                                    { label: 'Pelanggan',   value: receiptOrder.customer_name ?? sessionName ?? '—' },
                                    { label: 'Pembayaran',  value: METHOD_LABEL[receiptOrder.payment_method] ?? '—' },
                                ].map(row => (
                                    <div key={row.label} className="flex justify-between items-baseline gap-3">
                                        <span className="text-[12.5px] text-muted-foreground/70 shrink-0">
                                            {row.label}
                                        </span>
                                        <span className="text-[13px] font-semibold text-foreground text-right">
                                            {row.value}
                                        </span>
                                    </div>
                                ))}
                            </div>

                            <div className="h-px bg-border mb-[14px]" />

                            <div className="flex justify-between items-center mb-2">
                                <span className="text-[11px] font-bold text-muted-foreground/60 flex-1 tracking-[0.4px]">ITEM</span>
                                <span className="text-[11px] font-bold text-muted-foreground/60 w-8 text-center tracking-[0.4px]">JML</span>
                                <span className="text-[11px] font-bold text-muted-foreground/60 w-20 text-right tracking-[0.4px]">HARGA</span>
                            </div>

                            <div className="flex flex-col gap-2 mb-[14px]">
                                {(receiptOrder.items ?? []).map((item, i) => (
                                    <div key={i} className="flex justify-between items-center">
                                        <span className="text-[13px] text-foreground flex-1">{item.name}</span>
                                        <span className="text-[13px] text-muted-foreground/70 w-8 text-center">{item.quantity}×</span>
                                        <span className="text-[13px] text-foreground w-20 text-right">{formatRupiah(item.subtotal)}</span>
                                    </div>
                                ))}
                            </div>

                            <div className="h-px bg-border mb-3" />

                            <div className="flex justify-between items-center mb-2">
                                <span className="text-[13px] text-muted-foreground/70">Subtotal</span>
                                <span className="text-[13px] text-muted-foreground">{formatRupiah(receiptOrder.total_amount)}</span>
                            </div>

                            <div className="flex justify-between items-center mb-5 px-3 py-[10px] bg-secondary rounded-[10px] border border-primary/20">
                                <span className="text-[15px] font-extrabold text-foreground">Total</span>
                                <span className="text-base font-extrabold text-primary tracking-tight">
                                    {formatRupiah(receiptOrder.total_amount)}
                                </span>
                            </div>

                            <div className="text-center mb-[18px]">
                                <span className="text-[13px] font-semibold text-muted-foreground/60">
                                    Terima kasih sudah memesan!
                                </span>
                            </div>
                        </div>

                        <div className="px-5 pb-[22px] pt-[10px] shrink-0 border-t border-border">
                            <button
                                onClick={() => setReceiptOrder(null)}
                                className="w-full h-[50px] text-primary-foreground border-none rounded-[14px] text-[15px] font-bold cursor-pointer bg-primary shadow-[0_6px_18px_rgba(232,118,58,0.35)]"
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
