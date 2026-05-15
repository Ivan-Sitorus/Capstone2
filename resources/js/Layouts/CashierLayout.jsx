import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import {
    ShoppingCart,
    ClipboardList,
    History,
} from 'lucide-react';
import HeaderBar from '@/Components/Shared/HeaderBar';
import FlashToast from '@/Components/Shared/FlashToast';

const tabs = [
    { label: 'Pesanan Baru',    href: route('kasir.pesanan-baru'), icon: ShoppingCart },
    { label: 'Pesanan Aktif',   href: route('kasir.pesanan-aktif'), icon: ClipboardList },
    { label: 'Riwayat Pesanan', href: route('kasir.riwayat-pesanan'),       icon: History },
];

export default function CashierLayout({ children }) {
    const { flash, auth, pendingOrderCount: initialCount } = usePage().props;
    const [pendingCount, setPendingCount] = useState(initialCount ?? 0);
    const [toast, setToast] = useState(null);

    // ── Flash toast ──
    useEffect(() => {
        if (flash?.success) {
            setToast({ type: 'success', message: flash.success });
            const t = setTimeout(() => setToast(null), 3000);
            return () => clearTimeout(t);
        }
        if (flash?.error) {
            setToast({ type: 'error', message: flash.error });
            const t = setTimeout(() => setToast(null), 3000);
            return () => clearTimeout(t);
        }
    }, [flash]);

    // ── WebSocket badge (Reverb / Echo) ──
    useEffect(() => {
        setPendingCount(initialCount ?? 0);
    }, [initialCount]);

    useEffect(() => {
        if (!window.Echo) return;
        const channel = window.Echo.channel('orders');
        channel.listen('.OrderStatusUpdated', (e) => {
            setPendingCount(e.pendingCount);
        });
        return () => {
            window.Echo.leaveChannel('orders');
        };
    }, []);

    return (
        <div data-interface="cashier" className="min-h-screen flex flex-col bg-muted">
            <HeaderBar tabs={tabs} user={auth?.user} pendingCount={pendingCount} />

            {/* ── Main Content ── */}
            <main className="flex-1 flex flex-col overflow-hidden min-h-0">
                {children}
            </main>

            {/* ── Toast ── */}
            <FlashToast toast={toast} onDismiss={() => setToast(null)} />
        </div>
    );
}
