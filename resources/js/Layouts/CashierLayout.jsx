import { useState, useEffect, useCallback } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import {
    LayoutDashboard,
    ShoppingCart,
    ClipboardList,
    History,
    User,
    LogOut,
    CheckCircle,
    XCircle,
    PanelLeftClose,
    PanelLeftOpen,
} from 'lucide-react';
import ThemeToggle from '@/Components/Common/ThemeToggle';
import { cn } from '@/lib/utils';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
    SidebarProvider,
    SidebarRail,
    SidebarTrigger,
} from '@/components/ui/sidebar';

const navItems = [
    { label: 'Dashboard',       href: '/cashier/dashboard',     icon: LayoutDashboard },
    { label: 'Pesanan Baru',    href: '/cashier/pesanan-baru',  icon: ShoppingCart },
    { label: 'Pesanan Aktif',   href: '/cashier/pesanan-aktif', icon: ClipboardList },
    { label: 'Riwayat Pesanan', href: '/cashier/riwayat',       icon: History },
    { label: 'Profil',          href: '/cashier/profil',        icon: User },
];

const SIDEBAR_STORAGE_KEY = 'cashier-sidebar-collapsed';
const SIDEBAR_EXPANDED_W = 260;
const SIDEBAR_COLLAPSED_W = 84;

export default function CashierLayout({ children, fullscreen = false }) {
    const { flash, pendingOrderCount: initialCount } = usePage().props;
    const [pendingCount, setPendingCount] = useState(initialCount ?? 0);
    const [toast, setToast] = useState(null);

    // ── Sidebar state: persisted in localStorage, initiates from there ──
    const [sidebarOpen, setSidebarOpen] = useState(() => {
        if (typeof window === 'undefined') return true;
        return window.localStorage.getItem(SIDEBAR_STORAGE_KEY) !== 'true';
    });

    // ── onOpenChange: sync localStorage + dispatch custom event ──
    const handleSidebarChange = useCallback((open) => {
        setSidebarOpen(open);
        if (typeof window === 'undefined') return;
        const collapsed = !open;
        const width = open ? SIDEBAR_EXPANDED_W : SIDEBAR_COLLAPSED_W;
        window.localStorage.setItem(SIDEBAR_STORAGE_KEY, String(collapsed));
        window.dispatchEvent(new CustomEvent('cashier-sidebar-toggle', {
            detail: { collapsed, width },
        }));
    }, []);

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
        <SidebarProvider
            data-interface="cashier"
            open={sidebarOpen}
            onOpenChange={handleSidebarChange}
            style={{
                '--sidebar-width': `${SIDEBAR_EXPANDED_W / 16}rem`,
                '--sidebar-width-icon': `${SIDEBAR_COLLAPSED_W / 16}rem`,
            }}
            className="font-sans"
        >
            <Sidebar collapsible="icon" className="border-r border-sidebar-border">
                {/* ── Brand Header ── */}
                <SidebarHeader className="flex-row items-center gap-3 p-4 border-b border-sidebar-border">
                    {sidebarOpen ? (
                        <>
                            <img
                                src="/images/logo.jpg"
                                alt="W9 Cafe"
                                className="size-10 rounded-[10px] object-cover shrink-0 shadow-lg"
                            />
                            <span className="text-sidebar-foreground font-bold text-base whitespace-nowrap select-none">
                                W9 Cafe
                            </span>
                            <ThemeToggle />
                            <button
                                type="button"
                                onClick={() => handleSidebarChange(false)}
                                title="Collapse sidebar"
                                aria-label="Collapse sidebar"
                                className="ml-auto size-8 rounded-lg border border-sidebar-border bg-sidebar-accent text-sidebar-foreground inline-flex items-center justify-center cursor-pointer shrink-0 hover:brightness-125 transition-[filter]"
                            >
                                <PanelLeftClose size={16} />
                            </button>
                        </>
                    ) : (
                        <div className="flex flex-col items-center gap-2 w-full">
                            <button
                                type="button"
                                onClick={() => handleSidebarChange(true)}
                                title="Expand sidebar"
                                aria-label="Expand sidebar"
                                className="size-10 rounded-[10px] border border-sidebar-border bg-sidebar-accent text-sidebar-foreground inline-flex items-center justify-center cursor-pointer shrink-0 shadow-lg hover:brightness-125 transition-[filter]"
                            >
                                <PanelLeftOpen size={18} />
                            </button>
                        </div>
                    )}
                </SidebarHeader>

                {/* ── Navigation ── */}
                <SidebarContent className="px-3 py-4">
                    <SidebarMenu className="gap-1">
                        {navItems.map(({ label, href, icon: Icon }) => {
                            const active = currentPath === href;
                            const showBadge = label === 'Pesanan Aktif' && pendingCount > 0;
                            return (
                                <SidebarMenuItem key={href}>
                                    <Link
                                        href={href}
                                        prefetch
                                        cacheFor="1m"
                                        className={cn(
                                            'flex items-center gap-3 h-11 rounded-lg text-sm font-medium transition-colors no-underline select-none',
                                            'group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:size-[52px] group-data-[collapsible=icon]:p-0',
                                            sidebarOpen ? 'px-4' : 'px-0',
                                            active
                                                ? 'bg-primary text-primary-foreground'
                                                : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                                        )}
                                    >
                                        {/* Icon + collapsed badge */}
                                        <span className="relative inline-flex shrink-0">
                                            <Icon size={20} />
                                            {!sidebarOpen && showBadge && (
                                                <span
                                                    className={cn(
                                                        'absolute -top-2 -right-2.5 flex items-center justify-center rounded-full bg-destructive text-destructive-foreground text-[9px] font-bold leading-none',
                                                        pendingCount > 9 ? 'min-w-4 h-4 px-1' : 'size-4',
                                                    )}
                                                >
                                                    {pendingCount > 99 ? '99+' : pendingCount}
                                                </span>
                                            )}
                                        </span>
                                        {/* Label */}
                                        {sidebarOpen && (
                                            <span className="flex-1 truncate group-data-[collapsible=icon]:hidden">{label}</span>
                                        )}
                                        {/* Expanded badge */}
                                        {sidebarOpen && showBadge && (
                                            <span
                                                className={cn(
                                                    'shrink-0 flex items-center justify-center rounded-full bg-destructive text-destructive-foreground text-[11px] font-bold leading-none',
                                                    pendingCount > 9 ? 'min-w-5 h-5 px-[5px]' : 'size-5',
                                                )}
                                            >
                                                {pendingCount > 99 ? '99+' : pendingCount}
                                            </span>
                                        )}
                                    </Link>
                                </SidebarMenuItem>
                            );
                        })}
                    </SidebarMenu>
                </SidebarContent>

                {/* ── Logout Footer ── */}
                <SidebarFooter className="p-3 border-t border-sidebar-border">
                    <button
                        type="button"
                        onClick={() => router.post('/logout')}
                        className={cn(
                            'flex items-center gap-3 h-11 w-full rounded-lg text-sm font-medium transition-colors cursor-pointer border-none bg-transparent',
                            'text-destructive hover:bg-destructive/10',
                            'group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:p-0',
                            sidebarOpen ? 'px-4' : 'px-0',
                        )}
                    >
                        <LogOut size={20} className="shrink-0" />
                        {sidebarOpen && (
                            <span className="group-data-[collapsible=icon]:hidden">Keluar</span>
                        )}
                    </button>
                </SidebarFooter>

                <SidebarRail />
            </Sidebar>

            {/* ── Main Content ── */}
            {fullscreen ? (
                <main className="relative flex flex-1 flex-col overflow-hidden h-svh">
                    <SidebarTrigger className="absolute top-3 left-3 z-30 md:hidden" />
                    {children}
                </main>
            ) : (
                <main className="flex flex-1 flex-col bg-muted p-4 sm:p-8 min-h-svh">
                    <div className="flex items-center justify-between mb-4">
                        <SidebarTrigger className="md:hidden" />
                        <div className="flex-1" />
                        <ThemeToggle />
                    </div>
                    <div className="flex-1 bg-card rounded-xl p-6 border shadow-sm">
                        {children}
                    </div>
                </main>
            )}

            {/* ── Toast ── */}
            {toast && (
                <div
                    className={cn(
                        'fixed top-6 right-6 z-[9999] rounded-[10px] px-4 py-3 flex items-center gap-2.5 text-sm shadow-floating min-w-[280px] max-w-[380px]',
                        toast.type === 'success'
                            ? 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-950 dark:border-green-800 dark:text-green-200'
                            : 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-950 dark:border-red-800 dark:text-red-200',
                    )}
                >
                    {toast.type === 'success'
                        ? <CheckCircle size={18} className="shrink-0" />
                        : <XCircle size={18} className="shrink-0" />}
                    {toast.message}
                </div>
            )}
        </SidebarProvider>
    );
}
