import { useState, useEffect, useRef, useCallback } from 'react';
import { router, Head } from '@inertiajs/react';
import axios from 'axios';
import KitchenLayout from '@/Layouts/KitchenLayout';
import SharedOrderCard from '@/Components/Shared/SharedOrderCard';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Maximize, Minimize, Volume2, VolumeX, Bell } from 'lucide-react';
import { useKitchenSound } from '@/Hooks/useKitchenSound';

const COLUMNS = [
    { key: 'pending',  label: 'Menunggu',  bumpLabel: 'Ambil',   nextStatus: 'diproses', borderClass: 'border-b-2 border-b-[#3B6FD4]', badgeClass: 'bg-[#3B6FD4] hover:bg-[#3B6FD4] text-white' },
    { key: 'diproses', label: 'Diproses',   bumpLabel: 'Selesai', nextStatus: 'selesai',  borderClass: 'border-b-2 border-b-[#EAB308]', badgeClass: 'bg-[#EAB308] hover:bg-[#EAB308] text-white' },
    { key: 'selesai',  label: 'Siap',       bumpLabel: null,      nextStatus: null,       borderClass: 'border-b-2 border-b-[#28A745]', badgeClass: 'bg-[#28A745] hover:bg-[#28A745] text-white' },
];

const FILTERS = [
    { key: 'all',      label: 'Semua' },
    { key: 'minuman',  label: 'Minuman' },
    { key: 'makanan',  label: 'Makanan' },
];

export default function KitchenIndex({ orders: initialOrders }) {
    const [orders, setOrders] = useState(initialOrders ?? []);
    const [activeFilter, setActiveFilter] = useState('all');
    const [isMuted, setIsMuted] = useState(() => {
        if (typeof window === 'undefined') return false;
        return localStorage.getItem('kds-muted') === 'true';
    });
    const [isFullscreen, setIsFullscreen] = useState(false);
    const [isPollingPaused, setIsPollingPaused] = useState(false);
    const [bumpingIds, setBumpingIds] = useState(new Set());
    const prevOrderIdsRef = useRef(new Set());
    const { playNewOrderChime } = useKitchenSound();

    const formatClock = useCallback(() => {
        return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }, []);

    const [clockDisplay, setClockDisplay] = useState(formatClock);

    useEffect(() => {
        const id = setInterval(() => setClockDisplay(formatClock()), 1000);
        return () => clearInterval(id);
    }, [formatClock]);

    useEffect(() => {
        const currentIds = new Set(orders.map(o => o.id));
        const prevIds = prevOrderIdsRef.current;

        if (prevIds.size > 0) {
            const newIds = [...currentIds].filter(id => !prevIds.has(id));
            if (newIds.length > 0 && !isMuted) {
                playNewOrderChime();
            }
        }

        prevOrderIdsRef.current = currentIds;
    }, [orders, isMuted, playNewOrderChime]);

    useEffect(() => {
        if (isPollingPaused) return;

        const reload = () => {
            if (document.visibilityState === 'hidden') return;
            router.reload({
                only: ['orders'],
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page) => {
                    const newOrders = page.props.orders ?? [];
                    setOrders(newOrders);
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
    }, [isPollingPaused]);

    const toggleFullscreen = useCallback(() => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().then(() => setIsFullscreen(true)).catch(() => {});
        } else {
            document.exitFullscreen().then(() => setIsFullscreen(false)).catch(() => {});
        }
    }, []);

    useEffect(() => {
        const handler = () => setIsFullscreen(!!document.fullscreenElement);
        document.addEventListener('fullscreenchange', handler);
        return () => document.removeEventListener('fullscreenchange', handler);
    }, []);

    const toggleMute = useCallback(() => {
        setIsMuted(prev => {
            const next = !prev;
            localStorage.setItem('kds-muted', String(next));
            return next;
        });
    }, []);

    const handleBump = useCallback(async (order) => {
        if (bumpingIds.has(order.id)) return;
        if (order.status === 'selesai') return;

        setBumpingIds(prev => new Set(prev).add(order.id));

        try {
            await axios.patch(`/kitchen/order/${order.id}/bump`);
            const nextStatus = order.status === 'pending' ? 'diproses' : 'selesai';
            setOrders(prev => prev.map(o =>
                o.id === order.id ? { ...o, status: nextStatus } : o
            ));
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

    const filteredOrders = orders.filter(o => {
        if (activeFilter === 'all') return true;
        return o.items.some(i => i.category?.toLowerCase() === activeFilter);
    });

    const columnOrders = {
        pending:  filteredOrders.filter(o => o.status === 'pending'),
        diproses: filteredOrders.filter(o => o.status === 'diproses'),
        selesai:  filteredOrders.filter(o => o.status === 'selesai'),
    };

    return (
        <>
            <Head title="Dapur | W9 Cafe" />
            <KitchenLayout>
                {/* ── Header row ── */}
                <div className="col-span-full flex items-center justify-between flex-wrap gap-3">
                    <div className="flex items-center gap-4">
                        <div className="size-10 rounded-xl bg-[#0F1621] border border-[#2A3441] flex items-center justify-center font-bold text-sm text-white">
                            W9
                        </div>
                        <div>
                            <h1 className="text-2xl font-bold text-white tracking-tight leading-none">
                                Dapur
                            </h1>
                            <span className="text-[13px] text-slate-400">
                                Kitchen Display System
                            </span>
                        </div>
                    </div>

                    <div className="flex items-center gap-2.5">
                        <span className="text-[15px] text-slate-400 tabular-nums font-mono">
                            {clockDisplay}
                        </span>

                        <span className="inline-flex items-center gap-1 text-xs font-semibold rounded-full px-2.5 py-1 bg-green-500/10 text-green-500">
                            <Bell className="size-3.5" />
                            {orders.length}
                        </span>

                        <button
                            onClick={toggleMute}
                            title={isMuted ? 'Aktifkan suara' : 'Nonaktifkan suara'}
                            className="size-9 rounded-lg border border-[#2A3441] bg-[#0F1621] flex items-center justify-center cursor-pointer transition-colors hover:bg-[#1A2332]"
                        >
                            {isMuted ? <VolumeX className="size-[18px] text-slate-500" /> : <Volume2 className="size-[18px] text-white" />}
                        </button>

                        <button
                            onClick={toggleFullscreen}
                            title={isFullscreen ? 'Keluar layar penuh' : 'Layar Penuh'}
                            className="size-9 rounded-lg border border-[#2A3441] bg-[#0F1621] flex items-center justify-center cursor-pointer transition-colors hover:bg-[#1A2332]"
                        >
                            {isFullscreen ? <Minimize className="size-[18px] text-white" /> : <Maximize className="size-[18px] text-white" />}
                        </button>
                    </div>
                </div>

                {/* ── Filter tabs ── */}
                <div className="col-span-full">
                    <Tabs value={activeFilter} onValueChange={setActiveFilter}>
                        <TabsList variant="line" className="gap-1 bg-transparent p-0">
                            {FILTERS.map(f => (
                                <TabsTrigger
                                    key={f.key}
                                    value={f.key}
                                    className="rounded-full px-4 py-1.5 text-[13px] font-medium data-active:bg-[#3B6FD4] data-active:text-white text-slate-400 hover:text-white data-active:hover:text-white transition-colors"
                                >
                                    {f.label}
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>
                </div>

                {/* ── Kanban columns ── */}
                {COLUMNS.map(col => {
                    const items = columnOrders[col.key] ?? [];

                    return (
                        <div key={col.key} className="flex flex-col min-h-0 min-w-0">
                            <div className={`flex items-center justify-between mb-3 pb-2.5 ${col.borderClass}`}>
                                <span className="text-base font-bold text-white">
                                    {col.label}
                                </span>
                                <Badge variant="default" className={col.badgeClass}>
                                    {items.length}
                                </Badge>
                            </div>

                            <div className="flex-1 overflow-y-auto flex flex-col gap-3 pb-2">
                                {items.length === 0 ? (
                                    <div className="text-center py-10 px-4 text-[13px] text-slate-600 border border-dashed border-[#2A3441] rounded-xl">
                                        Tidak ada pesanan
                                    </div>
                                ) : (
                                    items.map(order => (
                                        <SharedOrderCard
                                            key={order.id}
                                            order={order}
                                            onBump={handleBump}
                                            showTimer
                                            variant="kitchen"
                                            isBumping={bumpingIds.has(order.id)}
                                        />
                                    ))
                                )}
                            </div>
                        </div>
                    );
                })}
            </KitchenLayout>
        </>
    );
}
