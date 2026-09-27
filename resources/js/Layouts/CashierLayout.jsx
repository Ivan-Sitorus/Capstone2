import { useState, useEffect, createContext, useContext, useMemo } from 'react';
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
    Menu as MenuIcon,
    X as CloseIcon,
} from 'lucide-react';
import { Drawer } from '@base-ui/react/drawer';
import { SLATE_900, SLATE_800, SLATE_200, SLATE_400, SLATE_50, WHITE, BLUE, RED, RED_DARK, RED_50, RED_200, GREEN_50, GREEN_300, GREEN_700 } from '@/theme';
import { useIsMobile } from '@/Hooks/useMediaQuery';
import '../../css/cashier-mobile.css';

const navItems = [
    { label: 'Dashboard',       href: route('kasir.dashboard'),       icon: LayoutDashboard },
    { label: 'Pesanan Baru',    href: route('kasir.new-order'),    icon: ShoppingCart },
    { label: 'Pesanan Aktif',   href: route('kasir.active-orders'),   icon: ClipboardList },
    { label: 'Riwayat Pesanan', href: route('kasir.order-history'), icon: History },
    { label: 'Profil',          href: route('kasir.profile'),          icon: User },
];

const CashierSidebarContext = createContext(null);

export function useCashierSidebar() {
    return useContext(CashierSidebarContext) ?? { collapsed: false, width: 260 };
}

const srOnlyStyle = {
    position: 'absolute',
    width: 1,
    height: 1,
    padding: 0,
    margin: -1,
    overflow: 'hidden',
    clip: 'rect(0, 0, 0, 0)',
    whiteSpace: 'nowrap',
    border: 0,
};

const mobileHamburgerStyle = {
    position: 'fixed',
    top: 10,
    left: 12,
    zIndex: 800,
    width: 44,
    height: 44,
    borderRadius: 10,
    border: `1px solid ${SLATE_200}`,
    background: WHITE,
    color: SLATE_900,
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    cursor: 'pointer',
    boxShadow: '0 2px 10px rgba(15, 23, 42, 0.10)',
    flexShrink: 0,
};

const drawerCloseStyle = {
    marginLeft: 'auto',
    width: 34,
    height: 34,
    borderRadius: 10,
    border: '1px solid rgba(255,255,255,0.14)',
    background: SLATE_800,
    color: SLATE_200,
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    cursor: 'pointer',
    flexShrink: 0,
};

/**
 * Isi sidebar (brand, nav, logout): dipakai ulang oleh <aside> desktop dan
 * drawer off-canvas mobile agar daftar menu tidak pernah terduplikasi.
 */
function SidebarContent({ collapsed, showCollapseControl, onToggleCollapse, pendingOrderCount, onNavigate, headerAction }) {
    const collapseButton = (isCollapsed) => (
        <button
            type="button"
            onClick={onToggleCollapse}
            title={isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}
            aria-label={isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}
            style={{
                marginLeft: isCollapsed ? undefined : 'auto',
                width: isCollapsed ? 40 : 32,
                height: isCollapsed ? 40 : 32,
                borderRadius: isCollapsed ? 10 : 8,
                border: '1px solid rgba(255,255,255,0.14)',
                background: SLATE_800,
                color: SLATE_200,
                display: 'inline-flex',
                alignItems: 'center',
                justifyContent: 'center',
                cursor: 'pointer',
                flexShrink: 0,
                boxShadow: isCollapsed ? '0 2px 10px rgba(0,0,0,0.20)' : 'none',
            }}
        >
            {isCollapsed ? <PanelLeftOpen size={18} /> : <PanelLeftClose size={16} />}
        </button>
    );

    return (
        <>
            {/* Brand / Logo */}
            <div style={{
                padding: '24px 20px',
                borderBottom: '1px solid rgba(255,255,255,0.06)',
                display: 'flex',
                alignItems: 'center',
                gap: 12,
            }}>
                {collapsed ? (
                    showCollapseControl && collapseButton(true)
                ) : (
                    <>
                        <img
                            src="/images/logo.jpg"
                            alt="minePOS"
                            width={40}
                            height={40}
                            style={{
                                width: 40,
                                height: 40,
                                borderRadius: 10,
                                objectFit: 'cover',
                                flexShrink: 0,
                                boxShadow: '0 2px 10px rgba(0,0,0,0.20)',
                            }}
                        />
                        <span style={{ color: 'white', fontWeight: 700, fontSize: 16, whiteSpace: 'nowrap' }}>minePOS</span>
                        {headerAction
                            ? <span style={{ marginLeft: 'auto', display: 'inline-flex' }}>{headerAction}</span>
                            : (showCollapseControl && collapseButton(false))}
                    </>
                )}
            </div>

            {/* Nav */}
            <nav style={{ flex: 1, padding: '20px 20px 0', display: 'flex', flexDirection: 'column', gap: 4, overflowY: 'auto' }}>
                {navItems.map(({ label, href, icon: Icon }) => {
                    const active = new URL(href, window.location.origin).pathname.replace(/\/+$/, '') === window.location.pathname.replace(/\/+$/, '');
                    const showBadge = label === 'Pesanan Aktif' && pendingOrderCount > 0;
                    return (
                        <Link
                            key={href}
                            href={href}
                            prefetch
                            cacheFor="1m"
                            onClick={onNavigate}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: collapsed ? 0 : 12,
                                height: 44,
                                padding: collapsed ? '0 12px' : '0 16px',
                                borderRadius: 8,
                                textDecoration: 'none',
                                fontSize: 14,
                                fontWeight: active ? 600 : 500,
                                color: active ? WHITE : SLATE_400,
                                background: active ? BLUE : 'transparent',
                                transition: 'background 0.15s, color 0.15s',
                                justifyContent: collapsed ? 'center' : 'flex-start',
                            }}
                            onMouseEnter={e => { if (!active) e.currentTarget.style.background = SLATE_800; }}
                            onMouseLeave={e => { if (!active) e.currentTarget.style.background = 'transparent'; }}
                        >
                            <span style={{ position: 'relative', display: 'inline-flex' }}>
                                <Icon size={20} />
                                {collapsed && showBadge && (
                                    <span style={{
                                        background: RED,
                                        color: WHITE,
                                        borderRadius: '50%',
                                        minWidth: 16,
                                        height: 16,
                                        position: 'absolute',
                                        top: -8,
                                        right: -10,
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        fontSize: 9,
                                        fontWeight: 700,
                                        lineHeight: 1,
                                        padding: pendingOrderCount > 9 ? '0 4px' : 0,
                                    }}>
                                        {pendingOrderCount > 99 ? '99+' : pendingOrderCount}
                                    </span>
                                )}
                            </span>
                            {!collapsed && <span style={{ flex: 1 }}>{label}</span>}
                            {showBadge && (
                                !collapsed && (
                                    <span style={{
                                        background: RED,
                                        color: WHITE,
                                        borderRadius: pendingOrderCount > 9 ? 10 : '50%',
                                        minWidth: 20,
                                        height: 20,
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        fontSize: 11,
                                        fontWeight: 700,
                                        lineHeight: 1,
                                        padding: pendingOrderCount > 9 ? '0 5px' : 0,
                                        flexShrink: 0,
                                    }}>
                                        {pendingOrderCount > 99 ? '99+' : pendingOrderCount}
                                    </span>
                                )
                            )}
                        </Link>
                    );
                })}
            </nav>

            {/* Logout */}
            <div style={{ padding: '12px 20px 24px', borderTop: '1px solid rgba(255,255,255,0.06)' }}>
                <button
                    onClick={() => {
                        if (onNavigate) onNavigate();
                        router.post(route('logout'));
                    }}
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: collapsed ? 0 : 12,
                        height: 44,
                        padding: collapsed ? '0 12px' : '0 16px',
                        borderRadius: 8,
                        width: '100%',
                        background: 'transparent',
                        border: 'none',
                        color: RED_DARK,
                        fontSize: 14,
                        fontWeight: 500,
                        cursor: 'pointer',
                        transition: 'background 0.15s',
                        justifyContent: collapsed ? 'center' : 'flex-start',
                    }}
                    onMouseEnter={e => e.currentTarget.style.background = 'rgba(220,38,38,0.08)'}
                    onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                >
                    <LogOut size={20} />
                    {!collapsed && 'Keluar'}
                </button>
            </div>
        </>
    );
}

export default function CashierLayout({ children, fullscreen = false }) {
    const { flash, pendingOrderCount = 0 } = usePage().props;
    const [toast, setToast] = useState(null);
    const isMobile = useIsMobile();
    const [drawerOpen, setDrawerOpen] = useState(false);
    const [isSidebarCollapsed, setIsSidebarCollapsed] = useState(() => {
        if (typeof window === 'undefined') return false;
        return window.localStorage.getItem('cashier-sidebar-collapsed') === 'true';
    });
    const sidebarWidth = isSidebarCollapsed ? 84 : 260;
    const sidebarContextValue = useMemo(
        () => ({ collapsed: isSidebarCollapsed, width: sidebarWidth }),
        [isSidebarCollapsed, sidebarWidth]
    );

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

    useEffect(() => {
        if (typeof window === 'undefined') return;
        window.localStorage.setItem('cashier-sidebar-collapsed', String(isSidebarCollapsed));
    }, [isSidebarCollapsed]);

    // Drawer hanya untuk layar ponsel: tutup otomatis saat kembali ke desktop.
    useEffect(() => {
        if (!isMobile) setDrawerOpen(false);
    }, [isMobile]);

    useEffect(() => {
        if (!isMobile || !drawerOpen) return undefined;
        const handleKeyDown = (event) => {
            if (event.key === 'Escape') setDrawerOpen(false);
        };
        document.addEventListener('keydown', handleKeyDown, true);
        return () => document.removeEventListener('keydown', handleKeyDown, true);
    }, [isMobile, drawerOpen]);

    return (
        <CashierSidebarContext.Provider value={sidebarContextValue}>
        <div
            style={{
                display: 'flex',
                minHeight: '100dvh',
                fontFamily: "'Inter', system-ui, sans-serif",
                '--cashier-sidebar-width': `${sidebarWidth}px`,
            }}
        >

            {/* ── SIDEBAR (desktop) ── */}
            {!isMobile && (
                <aside style={{
                    width: sidebarWidth,
                    minHeight: '100dvh',
                    background: SLATE_900,
                    display: 'flex',
                    flexDirection: 'column',
                    flexShrink: 0,
                    transition: 'width 0.2s ease',
                }}>
                    <SidebarContent
                        collapsed={isSidebarCollapsed}
                        showCollapseControl
                        onToggleCollapse={() => setIsSidebarCollapsed(prev => !prev)}
                        pendingOrderCount={pendingOrderCount}
                    />
                </aside>
            )}

            {/* ── SIDEBAR (mobile): tombol hamburger + drawer off-canvas ── */}
            {isMobile && (
                <Drawer.Root
                    open={drawerOpen}
                    onOpenChange={(open) => setDrawerOpen(open)}
                    swipeDirection="left"
                    modal
                >
                    <Drawer.Trigger
                        type="button"
                        aria-label="Buka menu navigasi"
                        title="Buka menu navigasi"
                        style={mobileHamburgerStyle}
                    >
                        <MenuIcon size={20} />
                    </Drawer.Trigger>
                    <Drawer.Portal>
                        <Drawer.Backdrop className="cashier-drawer-backdrop" />
                        <Drawer.Viewport className="cashier-drawer-nav-viewport">
                            <Drawer.Popup className="cashier-drawer-nav-popup" aria-modal="true" style={{ background: SLATE_900 }}>
                                <Drawer.Title style={srOnlyStyle}>Navigasi Kasir</Drawer.Title>
                                <Drawer.Content style={{ flex: 1, minHeight: 0, display: 'flex', flexDirection: 'column' }}>
                                    <SidebarContent
                                        collapsed={false}
                                        showCollapseControl={false}
                                        pendingOrderCount={pendingOrderCount}
                                        onNavigate={() => setDrawerOpen(false)}
                                        headerAction={(
                                            <Drawer.Close type="button" aria-label="Tutup menu" style={drawerCloseStyle}>
                                                <CloseIcon size={18} />
                                            </Drawer.Close>
                                        )}
                                    />
                                </Drawer.Content>
                            </Drawer.Popup>
                        </Drawer.Viewport>
                    </Drawer.Portal>
                </Drawer.Root>
            )}

            {/* ── MAIN CONTENT ── */}
            {fullscreen ? (
                <main style={{
                    flex: 1,
                    display: 'flex',
                    flexDirection: 'column',
                    overflow: 'hidden',
                    height: '100dvh',
                    paddingTop: isMobile ? 56 : 0,
                }}>
                    {children}
                </main>
            ) : (
                <main style={{
                    flex: 1,
                    background: SLATE_50,
                    padding: isMobile ? '68px 16px 16px' : 32,
                    minHeight: '100dvh',
                }}>
                    <div style={{
                        background: 'white',
                        borderRadius: 12,
                        padding: isMobile ? 16 : 24,
                        minHeight: isMobile ? 'calc(100dvh - 84px)' : 'calc(100dvh - 64px)',
                        border: `1px solid ${SLATE_200}`,
                        boxShadow: '0 2px 8px rgba(15,23,42,0.03)',
                    }}>
                        {children}
                    </div>
                </main>
            )}

            {/* ── TOAST ── */}
            {toast && (
                <div style={{
                    position: 'fixed',
                    top: 24,
                    right: 24,
                    zIndex: 9999,
                    background: toast.type === 'success' ? GREEN_50 : RED_50,
                    border: `1px solid ${toast.type === 'success' ? GREEN_300 : RED_200}`,
                    borderRadius: 10,
                    padding: '12px 16px',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 10,
                    fontSize: 14,
                    color: toast.type === 'success' ? GREEN_700 : RED_DARK,
                    boxShadow: '0 4px 16px rgba(0,0,0,0.10)',
                    minWidth: 280,
                    maxWidth: 380,
                }}>
                    {toast.type === 'success'
                        ? <CheckCircle size={18} style={{ flexShrink: 0 }} />
                        : <XCircle size={18} style={{ flexShrink: 0 }} />}
                    {toast.message}
                </div>
            )}
        </div>
        </CashierSidebarContext.Provider>
    );
}
