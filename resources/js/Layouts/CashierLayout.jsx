import { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import {
    ShoppingCart,
    ClipboardList,
    History,
    User,
    LogOut,
} from 'lucide-react';
import FlashToast from '@/Components/Shared/FlashToast';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';
import { formatDate } from '@/helpers';
import {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';

const tabs = [
    { label: 'Pesanan Baru',    href: '/cashier/pesanan-baru', icon: ShoppingCart },
    { label: 'Pesanan Aktif',   href: '/cashier/pesanan-aktif', icon: ClipboardList },
    { label: 'Riwayat Pesanan', href: '/cashier/riwayat',       icon: History },
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

    // ── Active route detection ──
    const currentPath = usePage().url;

    return (
        <div data-interface="cashier" className="min-h-screen flex flex-col bg-muted">
            {/* ── Header Bar ── */}
            <header className="sticky top-0 z-50 bg-card border-b border-border px-4 lg:px-6 h-14 flex items-center gap-2">
                <div className="flex items-center gap-2.5 mr-3 shrink-0">
                    <img
                        src="/images/logo.jpg"
                        alt="W9 Cafe"
                        className="size-8 rounded-[8px] object-cover shrink-0"
                    />
                    <span className="font-bold text-base text-foreground whitespace-nowrap select-none hidden sm:inline">
                        W9 Cafe
                    </span>
                </div>

                {/* Tab Navigation */}
                <nav className="flex items-center gap-1 flex-1 min-w-0">
                    {tabs.map(({ label, href, icon: Icon }) => {
                        const isActive = currentPath === href;
                        const showBadge = label === 'Pesanan Aktif' && pendingCount > 0;
                        return (
                            <Link
                                key={href}
                                href={href}
                                prefetch
                                cacheFor="1m"
                                className={cn(
                                    'relative inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors no-underline whitespace-nowrap',
                                    isActive
                                        ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                                )}
                            >
                                <Icon size={16} className="shrink-0" />
                                <span className="hidden sm:inline">{label}</span>
                                {showBadge && (
                                    <span
                                        className={cn(
                                            'inline-flex items-center justify-center rounded-full bg-destructive text-destructive-foreground text-[10px] font-bold leading-none ml-0.5',
                                            pendingCount > 9 ? 'min-w-4 h-4 px-1' : 'size-4',
                                        )}
                                    >
                                        {pendingCount > 99 ? '99+' : pendingCount}
                                    </span>
                                )}
                            </Link>
                        );
                    })}
                </nav>

                {/* Right side: Theme Toggle + Avatar Dropdown */}
                    <div className="flex items-center gap-1 shrink-0">

                    <DropdownMenu>
                        <DropdownMenuTrigger>
                            <button
                                type="button"
                                className={buttonVariants({ variant: 'outline', size: 'icon' })}
                                aria-label="Menu akun"
                            >
                                <User size={20} />
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" sideOffset={8} className="min-w-[200px] p-0">
                            <div className="px-3.5 py-3">
                                <p className="text-sm font-semibold text-foreground">
                                    {auth?.user?.name ?? 'Kasir'}
                                </p>
                                <p className="text-xs text-muted-foreground mt-0.5">
                                    {auth?.user?.email}
                                </p>
                                <p className="text-xs text-muted-foreground mt-0.5">
                                    Terdaftar sejak {auth?.user?.created_at ? formatDate(auth.user.created_at) : '-'}
                                </p>
                            </div>
                            <div className="h-px bg-border" />
                            <DropdownMenuItem onClick={() => router.post('/logout')} variant="destructive" className="rounded-none px-3.5 py-2.5">
                                <LogOut size={16} />
                                Keluar
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            {/* ── Main Content ── */}
            <main className="flex-1 flex flex-col overflow-hidden min-h-0">
                {children}
            </main>

            {/* ── Toast ── */}
            <FlashToast toast={toast} onDismiss={() => setToast(null)} />
        </div>
    );
}
