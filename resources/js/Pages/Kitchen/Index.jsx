import { useState, useEffect, useCallback } from 'react';
import { router, Head } from '@inertiajs/react';
import axios from 'axios';
import KitchenLayout from '@/Layouts/KitchenLayout';
import KanbanColumn from '@/Components/Kitchen/KanbanColumn';
import OrderKanbanCard from '@/Components/Kitchen/OrderKanbanCard';

export default function KitchenIndex({ orders: initialOrders }) {
    const [orders, setOrders] = useState(initialOrders ?? []);
    const [bumpingIds, setBumpingIds] = useState(new Set());

    // ── Polling: reload orders every 5s, pause when tab is hidden ──
    useEffect(() => {
        const reload = () => {
            if (document.visibilityState === 'hidden') return;
            router.reload({
                only: ['orders'],
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    setOrders(page.props.orders ?? []);
                },
            });
        };

        const id = setInterval(reload, 5000);
        const onVisible = () => {
            if (document.visibilityState === 'visible') reload();
        };
        document.addEventListener('visibilitychange', onVisible);

        return () => {
            clearInterval(id);
            document.removeEventListener('visibilitychange', onVisible);
        };
    }, []);

    // ── Bump: optimistic update with error recovery ──
    const handleBump = useCallback(async (order) => {
        if (bumpingIds.has(order.id)) return;
        if (order.status === 'selesai') return;

        setBumpingIds(prev => new Set(prev).add(order.id));

        try {
            await axios.patch(`/kitchen/order/${order.id}/bump`);
            const nextStatus = order.status === 'pending' ? 'diproses' : 'selesai';
            setOrders(prev =>
                nextStatus === 'selesai'
                    ? prev.filter(o => o.id !== order.id)
                    : prev.map(o => o.id === order.id ? { ...o, status: nextStatus } : o)
            );
        } catch {
            router.reload({ only: ['orders'], preserveState: true, preserveScroll: true });
        } finally {
            setBumpingIds(prev => {
                const next = new Set(prev);
                next.delete(order.id);
                return next;
            });
        }
    }, [bumpingIds]);

    const pendingOrders = orders.filter(o => o.status === 'pending');
    const processingOrders = orders.filter(o => o.status === 'diproses');

    const halfPending = Math.ceil(pendingOrders.length / 2);
    const halfProcessing = Math.ceil(processingOrders.length / 2);

    const pendingCol1 = pendingOrders.slice(0, halfPending);
    const pendingCol2 = pendingOrders.slice(halfPending);
    const processingCol1 = processingOrders.slice(0, halfProcessing);
    const processingCol2 = processingOrders.slice(halfProcessing);

    return (
        <>
            <Head title="Dapur | W9 Cafe" />
            <KitchenLayout>
                <div className="flex flex-1 min-h-0 gap-0 divide-x divide-border">
                    {/* ── Menunggu col 1 ── */}
                    <KanbanColumn
                        title="Menunggu"
                        count={pendingCol1.length}
                        colorClass="border-b-2 border-b-primary"
                        badgeClass="bg-primary text-primary-foreground"
                    >
                        {pendingCol1.map(order => (
                            <OrderKanbanCard
                                key={order.id}
                                order={order}
                                onBump={handleBump}
                                isBumping={bumpingIds.has(order.id)}
                            />
                        ))}
                    </KanbanColumn>

                    {/* ── Menunggu col 2 ── */}
                    <KanbanColumn
                        title="Menunggu"
                        count={pendingCol2.length}
                        colorClass="border-b-2 border-b-primary"
                        badgeClass="bg-primary text-primary-foreground"
                    >
                        {pendingCol2.map(order => (
                            <OrderKanbanCard
                                key={order.id}
                                order={order}
                                onBump={handleBump}
                                isBumping={bumpingIds.has(order.id)}
                            />
                        ))}
                    </KanbanColumn>

                    {/* ── Diproses col 1 ── */}
                    <KanbanColumn
                        title="Diproses"
                        count={processingCol1.length}
                        colorClass="border-b-2 border-b-amber-500"
                        badgeClass="bg-amber-500 text-white"
                    >
                        {processingCol1.map(order => (
                            <OrderKanbanCard
                                key={order.id}
                                order={order}
                                onBump={handleBump}
                                isBumping={bumpingIds.has(order.id)}
                            />
                        ))}
                    </KanbanColumn>

                    {/* ── Diproses col 2 ── */}
                    <KanbanColumn
                        title="Diproses"
                        count={processingCol2.length}
                        colorClass="border-b-2 border-b-amber-500"
                        badgeClass="bg-amber-500 text-white"
                    >
                        {processingCol2.map(order => (
                            <OrderKanbanCard
                                key={order.id}
                                order={order}
                                onBump={handleBump}
                                isBumping={bumpingIds.has(order.id)}
                            />
                        ))}
                    </KanbanColumn>
                </div>
            </KitchenLayout>
        </>
    );
}
