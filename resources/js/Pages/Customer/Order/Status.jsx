import { useEffect } from 'react';
import { router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { formatRupiah, formatDate, formatTime } from '@/helpers';
import { cn } from '@/lib/utils';

const ORDER_FLOW = ['pending', 'confirmed', 'preparing', 'ready', 'completed'];

const STEPS = [
    { key: 'pending',    icon: '⏳', label: 'Menunggu\nPembayaran' },
    { key: 'confirmed',  icon: '✓',  label: 'Dikonfirmasi' },
    { key: 'preparing',  icon: '👨‍🍳', label: 'Diproses' },
    { key: 'ready',      icon: '🛎',  label: 'Siap Diambil' },
    { key: 'completed',  icon: '✅',  label: 'Selesai' },
];

const cancelledCardClass = 'bg-red-50 border border-red-200/30 rounded-[20px] px-6 py-5 text-center';

export default function OrderStatus({ order }) {
    const currentIdx = ORDER_FLOW.indexOf(order.status);
    const isTerminal = ['completed', 'cancelled'].includes(order.status);

    useEffect(() => {
        if (isTerminal) return;
        const id = setInterval(() => {
            router.reload({ only: ['order'] });
        }, 10_000);
        return () => clearInterval(id);
    }, [order.status]);

    const stepState = (i) => {
        if (order.status === 'cancelled') return 'muted';
        if (i < currentIdx) return 'completed';
        if (i === currentIdx) return order.status === 'completed' ? 'completed' : 'active';
        return 'future';
    };

    return (
        <CustomerLayout activeTab="riwayat">
            <div className="flex flex-col gap-4 p-6 pb-24">

                {order.status === 'cancelled' && (
                    <div className={cancelledCardClass}>
                        <div className="text-3xl mb-2">✕</div>
                        <div className="text-base font-semibold text-red-600">
                            Pesanan Dibatalkan
                        </div>
                        <div className="text-xs mt-1 text-muted-foreground">
                            #{order.order_code}
                        </div>
                    </div>
                )}

                <div className="bg-card border border-border rounded-xl p-5">
                    <div className="flex items-start w-full">
                        {STEPS.flatMap((step, i) => {
                            const state = stepState(i);
                            const isCompleted = state === 'completed';
                            const isActive = state === 'active';

                            const elements = [
                                <div key={step.key} className="flex flex-col items-center shrink-0">
                                    <div className={cn(
                                        'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
                                        'transition-colors duration-200',
                                        isCompleted && 'bg-[var(--green-success)] text-white',
                                        isActive && 'bg-primary text-primary-foreground animate-pulse',
                                        !isCompleted && !isActive && 'bg-muted text-muted-foreground',
                                    )}>
                                        {isCompleted ? '✓' : step.icon}
                                    </div>
                                    <span className={cn(
                                        'text-[10px] leading-tight text-center mt-1.5 whitespace-pre-line',
                                        isActive ? 'text-primary font-semibold' : 'text-muted-foreground',
                                    )}>
                                        {step.label}
                                    </span>
                                </div>,
                            ];

                            if (i < STEPS.length - 1) {
                                const connectorGreen = stepState(i) === 'completed';
                                elements.push(
                                    <div
                                        key={`conn-${i}`}
                                        className={cn(
                                            'flex-1 h-[3px] rounded-sm mt-[14px] mx-1',
                                            connectorGreen
                                                ? 'bg-[var(--green-success)]'
                                                : 'bg-[var(--gray-border)]',
                                        )}
                                    />,
                                );
                            }

                            return elements;
                        })}
                    </div>
                </div>

                <div className="bg-card border border-border rounded-xl p-5">
                    <h3 className="text-sm font-semibold text-foreground mb-4">
                        Detail Pesanan
                    </h3>

                    <div className="flex flex-col gap-3">
                        {(order.items ?? []).length > 0 ? (
                            <div className="flex flex-col gap-2">
                                {(order.items ?? []).map((item, i) => (
                                    <div key={i} className="flex justify-between items-center">
                                        <span className="text-sm text-foreground">
                                            {item.quantity}x {item.menu?.name || item.name}
                                        </span>
                                        <span className="text-sm font-medium text-foreground">
                                            {formatRupiah(item.subtotal)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">{order.items_summary}</p>
                        )}

                        <div className="h-px bg-border my-2" />

                        {[
                            ['Tanggal', `${formatDate(order.created_at)}, ${formatTime(order.created_at)}`],
                            ['Metode', order.payment_method?.toUpperCase() ?? '-'],
                        ].map(([label, value]) => (
                            <div key={label} className="flex justify-between items-center">
                                <span className="text-sm text-muted-foreground">{label}</span>
                                <span className="text-sm font-medium text-foreground">{value}</span>
                            </div>
                        ))}

                        <div className="h-px bg-border my-1" />

                        <div className="flex justify-between items-center px-3 py-[10px] bg-secondary rounded-[10px] border border-primary/20">
                            <span className="text-sm font-bold text-foreground">Total</span>
                            <span className="text-base font-extrabold text-primary">
                                {formatRupiah(order.total_amount)}
                            </span>
                        </div>
                    </div>
                </div>

                <button
                    onClick={() => router.visit('/customer/menu')}
                    className="w-full h-[52px] bg-primary text-primary-foreground rounded-full text-sm font-bold cursor-pointer shadow-[0_6px_18px_rgba(232,118,58,0.35)]"
                >
                    Pesan Lagi
                </button>

            </div>
        </CustomerLayout>
    );
}
