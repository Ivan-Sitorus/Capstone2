import { useState, useEffect, useCallback } from 'react';
import { router, Head } from '@inertiajs/react';
import axios from 'axios';
import KitchenLayout from '@/Layouts/KitchenLayout';
import KanbanColumn from '@/Components/Kitchen/KanbanColumn';
import OrderKanbanCard from '@/Components/Kitchen/OrderKanbanCard';
import RiwayatTable from '@/Components/Shared/RiwayatTable';

export default function KitchenIndex({ orders: initialOrders, riwayatOrders: initialRiwayat }) {
    const [orders, setOrders] = useState(initialOrders ?? []);
    const [riwayatOrders, setRiwayatOrders] = useState(initialRiwayat ?? []);
    const [activeTab, setActiveTab] = useState('pesanan');
    const [bumpingIds, setBumpingIds] = useState(new Set());

    // ── Polling: reload orders every 5s, pause when tab is hidden ──
    useEffect(() => {
        const reload = () => {
            if (document.visibilityState === 'hidden') return;
            router.reload({
                only: ['orders', 'riwayatOrders'],
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    setOrders(page.props.orders ?? []);
                    setRiwayatOrders(page.props.riwayatOrders ?? []);
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
            router.reload({ only: ['orders', 'riwayatOrders'], preserveState: true, preserveScroll: true });
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

    return (
        <>
            <Head title="Dapur | W9 Cafe" />
            <KitchenLayout activeTab={activeTab} onTabChange={setActiveTab}>
                {activeTab === 'pesanan' ? (
                    <div className="flex flex-1 min-h-0 gap-0 divide-x divide-border">
                        {/* ── Menunggu column ── */}
                        <div className="flex-1 min-w-0 pr-4">
                            <KanbanColumn
                                title="Menunggu"
                                count={pendingOrders.length}
                                colorClass="border-b-2 border-b-primary"
                                badgeClass="bg-primary text-primary-foreground"
                            >
                                {pendingOrders.map(order => (
                                    <OrderKanbanCard
                                        key={order.id}
                                        order={order}
                                        onBump={handleBump}
                                        isBumping={bumpingIds.has(order.id)}
                                    />
                                ))}
                            </KanbanColumn>
                        </div>

                        {/* ── Diproses column ── */}
                        <div className="flex-1 min-w-0 pl-4">
                            <KanbanColumn
                                title="Diproses"
                                count={processingOrders.length}
                                colorClass="border-b-2 border-b-amber-500"
                                badgeClass="bg-amber-500 text-white"
                            >
                                {processingOrders.map(order => (
                                    <OrderKanbanCard
                                        key={order.id}
                                        order={order}
                                        onBump={handleBump}
                                        isBumping={bumpingIds.has(order.id)}
                                    />
                                ))}
                            </KanbanColumn>
                        </div>
                    </div>
                ) : (
                    <RiwayatTable
                        orders={{ data: riwayatOrders, current_page: 1, last_page: 1 }}
                        showFilters={false}
                        showPagination={false}
                        baseRoute="/kitchen"
                    />
                )}
            </KitchenLayout>
        </>
    );
}
