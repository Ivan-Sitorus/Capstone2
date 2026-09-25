import { useState, useMemo, useEffect } from 'react';
import { router, Head } from '@inertiajs/react';
import { Search, X, Banknote, QrCode, ShieldCheck, Lock, User, CircleCheck, Clock, PanelRightClose, PanelRightOpen, RefreshCw } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import MenuGridItem from '@/Components/Cashier/MenuGridItem';
import CartItem from '@/Components/Cashier/CartItem';
import { formatRupiah, formatTime } from '@/helpers';
import usePolling from '@/Hooks/usePolling';
import { BLUE, BLUE_50, BLUE_200, GRAY_900, GRAY_600, GRAY_BORDER, WHITE, GRAY_BG, SLATE_400, SLATE_LIGHT, GRAY_700, SLATE_DARK, SLATE_300, GREEN_600, SLATE_200, SLATE_900, SLATE_500, RED, RED_50, SLATE_100, BLUE_100, BLUE_SOFT, GREEN_50, GREEN_VIVID } from '@/theme';

export default function NewOrder({ categories }) {
    const [cartItems,      setCartItems]     = useState([]);
    const [activeCategory, setActiveCategory] = useState('Semua');
    const [search,         setSearch]         = useState('');
    const [showPayModal,   setShowPayModal]   = useState(false);
    const [payMethod,      setPayMethod]      = useState('cash');
    const [customerName,   setCustomerName]   = useState('');
    const [processing,     setProcessing]     = useState(false);
    const [nameError,      setNameError]      = useState(false);
    const [showSuccess,    setShowSuccess]    = useState(false);
    const [successTotal,   setSuccessTotal]   = useState(0);
    const [isCartCollapsed, setIsCartCollapsed] = useState(false);
    const [lastUpdated,    setLastUpdated]    = useState(() => new Date());

    const refreshMenu = () => {
        router.reload({ only: ['categories'], onSuccess: () => setLastUpdated(new Date()) });
    };

    usePolling(refreshMenu, 60_000);
    const [isLeftSidebarCollapsed, setIsLeftSidebarCollapsed] = useState(() => {
        if (typeof window === 'undefined') return false;
        return window.localStorage.getItem('cashier-sidebar-collapsed') === 'true';
    });
    const [viewport, setViewport] = useState(() => {
        if (typeof window === 'undefined') return { width: 1280, height: 720 };
        return { width: window.innerWidth, height: window.innerHeight };
    });

    /* ── Derived ── */
    const allMenus = useMemo(
        () => categories.flatMap(c => c.menus.map(m => ({ ...m, category: { name: c.name } }))),
        [categories]
    );

    const filteredMenus = useMemo(() => {
        let menus = activeCategory === 'Semua'
            ? allMenus
            : allMenus.filter(m => m.category.name === activeCategory);
        if (search.trim()) {
            const q = search.toLowerCase();
            menus = menus.filter(m => m.name.toLowerCase().includes(q));
        }
        return menus;
    }, [allMenus, activeCategory, search]);

    const [isStudent, setIsStudent] = useState(false);

    const totalQty      = cartItems.reduce((s, i) => s + i.quantity, 0);
    const total         = cartItems.reduce((s, i) => s + i.price * i.quantity, 0);
    const totalCashback = isStudent ? cartItems.reduce((s, i) => s + (i.cashback ?? 0) * i.quantity, 0) : 0;
    const grandTotal    = total - totalCashback;
    const isPortrait    = viewport.height > viewport.width;

    const cartExpandedWidth = isLeftSidebarCollapsed ? 380 : 340;
    const cartPanelWidth = isCartCollapsed ? 78 : cartExpandedWidth;
    const menuGridColumns = isPortrait
        ? 'repeat(auto-fill, minmax(170px, 1fr))'
        : isCartCollapsed
            ? 'repeat(auto-fill, minmax(185px, 1fr))'
            : 'repeat(auto-fill, minmax(210px, 1fr))';

    useEffect(() => {
        const onResize = () => setViewport({ width: window.innerWidth, height: window.innerHeight });
        window.addEventListener('resize', onResize);
        return () => window.removeEventListener('resize', onResize);
    }, []);

    useEffect(() => {
        if (!isPortrait) return;
        setIsCartCollapsed(true);
    }, [isPortrait]);

    useEffect(() => {
        const onSidebarToggle = (event) => {
            setIsLeftSidebarCollapsed(Boolean(event.detail?.collapsed));
        };

        window.addEventListener('cashier-sidebar-toggle', onSidebarToggle);
        return () => window.removeEventListener('cashier-sidebar-toggle', onSidebarToggle);
    }, []);

    /* ── Cart actions ── */
    function addToCart(menu) {
        setCartItems(prev => {
            const existing = prev.find(i => i.menuId === menu.id);
            if (existing) return prev.map(i => i.menuId === menu.id ? { ...i, quantity: i.quantity + 1 } : i);
            return [...prev, { menuId: menu.id, name: menu.name, price: Number(menu.price), cashback: Number(menu.cashback ?? 0), quantity: 1 }];
        });
    }
    const increment = (menuId) => setCartItems(prev => prev.map(i => i.menuId === menuId ? { ...i, quantity: i.quantity + 1 } : i));
    const decrement = (menuId) => setCartItems(prev => prev.map(i => i.menuId === menuId ? { ...i, quantity: i.quantity - 1 } : i).filter(i => i.quantity > 0));

    /* ── Modal open/close ── */
    function openModal() {
        setPayMethod('cash');
        setShowPayModal(true);
    }
    function closeModal() {
        if (processing) return;
        setShowPayModal(false);
        setCustomerName('');
        setNameError(false);
    }

    /* ── Step 1: proceed from choose ── */
    function handleChooseProceed() {
        if (!customerName.trim()) {
            setNameError(true);
            return;
        }
        setNameError(false);
        submitOrder(payMethod);
    }

    /* ── Submit ── */
    function submitOrder(method) {
        const orderTotal = grandTotal;
        setProcessing(true);
        router.post(
            route('kasir.new-order.store'),
            { items: cartItems.map(i => ({ menu_id: i.menuId, quantity: i.quantity })), payment_method: method, customer_name: customerName.trim() || null, is_mahasiswa: isStudent },
            {
                // Pertahankan state komponen agar popup sukses muncul (tanpa ini
                // komponen remount → setShowSuccess gagal → popup tak muncul)
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    setProcessing(false);
                    setShowPayModal(false);
                    setSuccessTotal(orderTotal);
                    setShowSuccess(true);
                },
                onError: () => setProcessing(false),
            }
        );
    }

    function handleSuccessOk() {
        // Tetap di halaman Pesanan Baru — kosongkan keranjang agar kasir
        // langsung bisa membuat pesanan berikutnya. Kasir berpindah ke
        // Pesanan Aktif secara manual lewat sidebar bila perlu.
        setShowSuccess(false);
        setCartItems([]);
        setCustomerName('');
        setPayMethod('cash');
        setIsStudent(false);
    }

    /* ── Design tokens ── */
    const T = {
        accent:     BLUE,
        accentBg:   BLUE_50,
        accentRing: BLUE_200,
        text:       GRAY_900,
        sub:        GRAY_600,
        border:     GRAY_BORDER,
        elevated:   WHITE,
        surface:    WHITE,
        panelBg:    GRAY_BG,
    };

    return (
        <><Head title="Pesanan Baru | W9 Cafe" /><CashierLayout title="Pesanan Baru" fullscreen>
            <div style={{ display: 'flex', flexDirection: isPortrait ? 'column' : 'row', height: '100vh', overflow: 'hidden' }}>

                {/* ══ PANEL TENGAH ══ */}
                <div
                    style={{
                        flex: 1,
                        padding: isPortrait ? 14 : 24,
                        background: T.panelBg,
                        overflowY: 'auto',
                        display: 'flex',
                        flexDirection: 'column',
                        gap: 16,
                        height: isPortrait ? 'auto' : '100vh',
                        minHeight: 0,
                    }}
                >
                    {/* Search */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        <div style={{ position: 'relative', flex: 1 }}>
                            <Search size={18} style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', color: SLATE_400, pointerEvents: 'none' }} />
                            <input
                                type="text" value={search} onChange={e => setSearch(e.target.value)}
                                placeholder="Cari menu..."
                                style={{ width: '100%', height: 44, border: `1px solid ${T.border}`, borderRadius: 8, padding: '0 40px 0 44px', fontSize: 14, color: T.text, background: T.surface, outline: 'none', boxSizing: 'border-box', boxShadow: '0 2px 8px rgba(15,23,42,0.04)' }}
                            />
                            {search && (
                                <button onClick={() => setSearch('')} style={{ position: 'absolute', right: 12, top: '50%', transform: 'translateY(-50%)', background: 'none', border: 'none', cursor: 'pointer', color: SLATE_400, padding: 0, display: 'flex' }}>
                                    <X size={16} />
                                </button>
                            )}
                        </div>
                        <span style={{ fontSize: 11, color: GRAY_600, whiteSpace: 'nowrap', flexShrink: 0 }}>Diperbarui {formatTime(lastUpdated)}</span>
                        <button onClick={refreshMenu} aria-label="Muat ulang menu" style={{ width: 40, height: 40, border: `1px solid ${T.border}`, borderRadius: 8, background: T.surface, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: BLUE, flexShrink: 0 }}>
                            <RefreshCw size={16} />
                        </button>
                    </div>

                    {/* Category chips */}
                    <div style={{ display: 'flex', gap: 8, overflowX: 'auto', paddingBottom: 4, flexShrink: 0 }}>
                        {['Semua', ...categories.map(c => c.name)].map(cat => {
                            const active = activeCategory === cat;
                            return (
                                <button key={cat} onClick={() => setActiveCategory(cat)} style={{ height: 36, padding: '0 16px', borderRadius: 100, border: active ? 'none' : `1.5px solid ${SLATE_LIGHT}`, cursor: 'pointer', fontSize: 13, fontWeight: active ? 600 : 500, background: active ? T.accent : T.surface, color: active ? WHITE : GRAY_700, whiteSpace: 'nowrap', transition: 'background 0.15s, color 0.15s', flexShrink: 0 }}>
                                    {cat}
                                </button>
                            );
                        })}
                    </div>

                    {/* Menu grid */}
                    {filteredMenus.length === 0 ? (
                        <div style={{ textAlign: 'center', color: SLATE_400, paddingTop: 48, fontSize: 14 }}>Tidak ada menu ditemukan</div>
                    ) : (
                        <div style={{ display: 'grid', gridTemplateColumns: menuGridColumns, gap: isPortrait ? 10 : 16 }}>
                            {filteredMenus.map(menu => <MenuGridItem key={menu.id} menu={menu} onAdd={addToCart} />)}
                        </div>
                    )}
                </div>

                {/* ══ PANEL KANAN — Keranjang ══ */}
                <div
                    style={{
                        width: isPortrait ? '100%' : cartPanelWidth,
                        background: T.surface,
                        borderLeft: isPortrait ? 'none' : `1px solid ${T.border}`,
                        borderTop: isPortrait ? `1px solid ${T.border}` : 'none',
                        padding: isCartCollapsed ? '14px 12px' : (isPortrait ? '14px 16px 16px' : 24),
                        display: 'flex',
                        flexDirection: 'column',
                        flexShrink: 0,
                        height: isPortrait ? (isCartCollapsed ? 72 : '44vh') : '100vh',
                        overflowY: 'auto',
                        overflowX: 'hidden',
                        transition: 'width 0.2s ease, height 0.2s ease, padding 0.2s ease',
                    }}
                >
                    <div style={{ display: 'flex', justifyContent: isCartCollapsed ? 'center' : 'space-between', alignItems: 'center', marginBottom: isCartCollapsed ? 0 : 16, gap: 10 }}>
                        {!isCartCollapsed && <span style={{ fontSize: 16, fontWeight: 700, color: T.text, letterSpacing: '-0.2px' }}>Keranjang Pesanan</span>}
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                            <span style={{ background: T.accent, color: 'white', borderRadius: '50%', width: 28, height: 28, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 12, fontWeight: 700 }}>{totalQty}</span>
                            <button
                                type="button"
                                onClick={() => setIsCartCollapsed(prev => !prev)}
                                title={isCartCollapsed ? 'Expand keranjang' : 'Collapse keranjang'}
                                style={{
                                    width: 32,
                                    height: 32,
                                    borderRadius: 8,
                                    border: `1px solid ${T.border}`,
                                    background: T.panelBg,
                                    color: SLATE_DARK,
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    cursor: 'pointer',
                                }}
                            >
                                {isCartCollapsed ? <PanelRightOpen size={16} /> : <PanelRightClose size={16} />}
                            </button>
                        </div>
                    </div>
                    {!isCartCollapsed && (
                        <>
                            <div style={{ flex: 1, overflowY: 'auto' }}>
                                {cartItems.length === 0 ? (
                                    <p style={{ color: SLATE_400, textAlign: 'center', marginTop: 40, fontSize: 14 }}>Keranjang kosong</p>
                                ) : (
                                    cartItems.map(item => <CartItem key={item.menuId} item={item} onIncrement={increment} onDecrement={decrement} />)
                                )}
                            </div>
                            <div style={{ borderTop: `1px solid ${T.border}`, paddingTop: 16, marginTop: 'auto' }}>
                        {/* Toggle Mahasiswa */}
                        <div
                            onClick={() => setIsStudent(p => !p)}
                            style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 12, cursor: 'pointer', userSelect: 'none' }}
                        >
                            <div style={{
                                width: 16, height: 16, borderRadius: 4, flexShrink: 0,
                                border: `1.5px solid ${isStudent ? T.accent : SLATE_300}`,
                                background: isStudent ? T.accent : 'white',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                            }}>
                                {isStudent && <span style={{ color: 'white', fontSize: 11, lineHeight: 1 }}>✓</span>}
                            </div>
                            <span style={{ fontSize: 13, color: T.sub }}>Mahasiswa STIE Totalwin Semarang</span>
                        </div>

                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                            <span style={{ fontSize: 14, color: T.sub }}>Subtotal</span>
                            <span style={{ fontSize: 14, fontWeight: 500, color: T.text }}>{formatRupiah(total)}</span>
                        </div>
                        {isStudent && totalCashback > 0 && (
                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                                <span style={{ fontSize: 13, color: GREEN_600 }}>Cashback Mahasiswa</span>
                                <span style={{ fontSize: 13, fontWeight: 600, color: GREEN_600 }}>- {formatRupiah(totalCashback)}</span>
                            </div>
                        )}
                        <div style={{ display: 'flex', justifyContent: 'space-between', padding: '12px 0', borderTop: `1px solid ${T.border}`, marginBottom: 16 }}>
                            <span style={{ fontSize: 16, fontWeight: 700, color: T.text }}>Total</span>
                            <span style={{ fontSize: 16, fontWeight: 700, color: T.text }}>{formatRupiah(grandTotal)}</span>
                        </div>
                        <button
                            onClick={openModal} disabled={cartItems.length === 0}
                            style={{ width: '100%', height: 52, background: cartItems.length === 0 ? SLATE_300 : T.accent, color: 'white', border: 'none', borderRadius: 14, fontSize: 16, fontWeight: 700, cursor: cartItems.length === 0 ? 'not-allowed' : 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0 20px', boxShadow: cartItems.length > 0 ? '0 4px 16px rgba(59,111,212,0.30)' : 'none', transition: 'background 0.15s' }}
                        >
                            <span>BAYAR</span>
                            <span style={{ fontSize: 18 }}>{formatRupiah(grandTotal)}</span>
                        </button>
                            </div>
                        </>
                    )}
                </div>
            </div>

            {/* ══ MODAL ══ */}
            {showPayModal && (
                <div
                    style={{ position: 'fixed', inset: 0, background: 'rgba(15,23,42,0.55)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000, padding: 24 }}
                    onClick={e => { if (e.target === e.currentTarget) closeModal(); }}
                >
                    <div style={{ background: T.surface, borderRadius: 24, width: '100%', maxWidth: 440, boxShadow: '0 24px 64px rgba(15,23,42,0.18), 0 2px 8px rgba(15,23,42,0.06)', overflow: 'hidden' }}>

                        {/* ─── Pilih Cara Bayar ─── */}
                        {(<>
                                {/* Header */}
                                <div style={{ padding: '22px 24px 16px', borderBottom: `1px solid ${SLATE_200}` }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                                        <button onClick={closeModal} style={{ width: 36, height: 36, borderRadius: 12, background: SLATE_100, border: 'none', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                            <X size={20} color={SLATE_900} />
                                        </button>
                                        <div style={{ display: 'flex', flexDirection: 'column', gap: 1 }}>
                                            <span style={{ fontSize: 20, fontWeight: 700, color: SLATE_900, fontFamily: '"DM Sans", system-ui', lineHeight: 1.2 }}>Metode Pembayaran</span>
                                            <span style={{ fontSize: 12, color: SLATE_500, fontFamily: 'Outfit, system-ui' }}>{cartItems.length} item · {formatRupiah(total)}</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Body */}
                                <div style={{ padding: '0 24px 24px', display: 'flex', flexDirection: 'column', gap: 18, maxHeight: '68vh', overflowY: 'auto' }}>

                                    {/* Nama Pelanggan */}
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 6, paddingTop: 8, borderTop: `1px solid ${SLATE_200}`, marginTop: 4 }}>
                                        <label style={{ fontSize: 13, fontWeight: 500, color: SLATE_900, fontFamily: 'Outfit, system-ui' }}>Nama Pelanggan</label>
                                        <div style={{ position: 'relative' }}>
                                            <User size={18} style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', color: SLATE_400, pointerEvents: 'none' }} />
                                            <input
                                                type="text"
                                                value={customerName}
                                                onChange={e => { setCustomerName(e.target.value); if (nameError) setNameError(false); }}
                                                placeholder="Masukkan nama pelanggan..."
                                                style={{ width: '100%', height: 44, border: `1px solid ${nameError ? RED : SLATE_200}`, borderRadius: 12, padding: '0 14px 0 42px', fontSize: 14, color: SLATE_900, background: nameError ? RED_50 : WHITE, outline: 'none', boxSizing: 'border-box', fontFamily: 'Outfit, system-ui', transition: 'border-color 0.15s' }}
                                                onFocus={e => e.target.style.borderColor = nameError ? RED : BLUE}
                                                onBlur={e => e.target.style.borderColor = nameError ? RED : SLATE_200}
                                            />
                                        </div>
                                        {nameError && (
                                            <p style={{ margin: '4px 0 0', fontSize: 12, color: RED, fontFamily: 'Outfit, system-ui' }}>
                                                Nama pelanggan wajib diisi
                                            </p>
                                        )}
                                    </div>

                                    {/* Metode Pembayaran */}
                                    <p style={{ margin: 0, fontSize: 13, fontWeight: 600, color: SLATE_500, fontFamily: 'Outfit, system-ui', letterSpacing: 0.5 }}>Metode Pembayaran</p>

                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 10, marginTop: -8 }}>
                                        {/* Cash */}
                                        <button
                                            onClick={() => setPayMethod('cash')}
                                            style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px 16px 16px', borderRadius: 16, cursor: 'pointer', textAlign: 'left', border: payMethod === 'cash' ? `2px solid ${BLUE}` : `1px solid ${SLATE_200}`, background: payMethod === 'cash' ? BLUE_50 : WHITE, boxShadow: payMethod === 'cash' ? '0 2px 10px rgba(59,111,212,0.10)' : 'none', transition: 'all 0.15s' }}
                                        >
                                            <div style={{ width: 42, height: 42, borderRadius: 12, background: payMethod === 'cash' ? BLUE_100 : SLATE_100, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                <Banknote size={22} color={payMethod === 'cash' ? BLUE : SLATE_500} />
                                            </div>
                                            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 2 }}>
                                                <span style={{ fontSize: 15, fontWeight: 600, color: SLATE_900, fontFamily: 'Outfit, system-ui' }}>Bayar ke Kasir (Cash)</span>
                                                <span style={{ fontSize: 12, color: SLATE_500, fontFamily: 'Outfit, system-ui' }}>Bayar langsung di kasir setelah pesanan dikonfirmasi</span>
                                            </div>
                                            <div style={{ width: 22, height: 22, borderRadius: '50%', border: payMethod === 'cash' ? `2px solid ${BLUE}` : `1.5px solid ${SLATE_300}`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                {payMethod === 'cash' && <div style={{ width: 12, height: 12, borderRadius: '50%', background: BLUE }} />}
                                            </div>
                                        </button>

                                        {/* QRIS */}
                                        <button
                                            onClick={() => setPayMethod('qris')}
                                            style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px 16px 16px', borderRadius: 16, cursor: 'pointer', textAlign: 'left', border: payMethod === 'qris' ? `2px solid ${BLUE}` : `1px solid ${SLATE_200}`, background: payMethod === 'qris' ? BLUE_50 : WHITE, boxShadow: payMethod === 'qris' ? '0 2px 10px rgba(59,111,212,0.10)' : 'none', transition: 'all 0.15s' }}
                                        >
                                            <div style={{ width: 42, height: 42, borderRadius: 12, background: payMethod === 'qris' ? BLUE_100 : SLATE_100, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                <img src="/images/logo-qris.png" alt="QRIS" style={{ width: 28, height: 28, objectFit: 'contain' }} />
                                            </div>
                                            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 2 }}>
                                                <span style={{ fontSize: 15, fontWeight: 600, color: SLATE_900, fontFamily: 'Outfit, system-ui' }}>QRIS</span>
                                                <span style={{ fontSize: 12, color: SLATE_400, fontFamily: 'Outfit, system-ui' }}>Scan QR code & konfirmasi kasir</span>
                                            </div>
                                            <div style={{ width: 22, height: 22, borderRadius: '50%', border: payMethod === 'qris' ? `2px solid ${BLUE}` : `1.5px solid ${SLATE_300}`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                {payMethod === 'qris' && <div style={{ width: 12, height: 12, borderRadius: '50%', background: BLUE }} />}
                                            </div>
                                        </button>

                                        {/* Bayar Nanti */}
                                        <button
                                            onClick={() => setPayMethod('bayar_nanti')}
                                            style={{ display: 'flex', alignItems: 'center', gap: 14, padding: '16px 18px 16px 16px', borderRadius: 16, cursor: 'pointer', textAlign: 'left', border: payMethod === 'bayar_nanti' ? `2px solid ${BLUE}` : `1px solid ${SLATE_200}`, background: payMethod === 'bayar_nanti' ? BLUE_50 : WHITE, boxShadow: payMethod === 'bayar_nanti' ? '0 2px 10px rgba(59,111,212,0.10)' : 'none', transition: 'all 0.15s' }}
                                        >
                                            <div style={{ width: 42, height: 42, borderRadius: 12, background: payMethod === 'bayar_nanti' ? BLUE_100 : SLATE_100, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                <Clock size={22} color={payMethod === 'bayar_nanti' ? BLUE : SLATE_500} />
                                            </div>
                                            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 2 }}>
                                                <span style={{ fontSize: 15, fontWeight: 600, color: SLATE_900, fontFamily: 'Outfit, system-ui' }}>Bayar Nanti</span>
                                                <span style={{ fontSize: 12, color: SLATE_400, fontFamily: 'Outfit, system-ui' }}>Simpan pesanan, pelanggan bayar nanti</span>
                                            </div>
                                            <div style={{ width: 22, height: 22, borderRadius: '50%', border: payMethod === 'bayar_nanti' ? `2px solid ${BLUE}` : `1.5px solid ${SLATE_300}`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                                                {payMethod === 'bayar_nanti' && <div style={{ width: 12, height: 12, borderRadius: '50%', background: BLUE }} />}
                                            </div>
                                        </button>
                                    </div>

                                    {/* Security note */}
                                    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: 5, padding: '0 0 4px' }}>
                                        <Lock size={11} color={SLATE_400} />
                                        <span style={{ fontSize: 11, color: SLATE_400, fontFamily: 'Outfit, system-ui' }}></span>
                                    </div>
                                </div>

                                {/* CTA */}
                                <div style={{ padding: '0 24px 24px' }}>
                                    <button
                                        onClick={handleChooseProceed} disabled={processing}
                                        style={{ width: '100%', height: 54, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8, background: processing ? BLUE_SOFT : BLUE, color: WHITE, border: 'none', borderRadius: 14, fontSize: 16, fontWeight: 700, fontFamily: '"DM Sans", system-ui', cursor: processing ? 'not-allowed' : 'pointer', boxShadow: '0 4px 16px rgba(59,111,212,0.28)' }}
                                    >
                                        {processing ? 'Memproses...' : 'Konfirmasi Pembayaran'}
                                    </button>
                                </div>
                            </>
                        )}

                    </div>
                </div>
            )}
            {/* ══ SUCCESS POPUP (3c Pencil — blue kasir theme) ══ */}
            {showSuccess && (
                <div style={{
                    position: 'fixed', inset: 0,
                    background: 'rgba(0,0,0,0.50)',
                    zIndex: 2000,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>
                    <div style={{
                        background: WHITE,
                        borderRadius: 24,
                        width: 320,
                        padding: '28px 24px 24px',
                        display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 16,
                        boxShadow: '0 8px 30px rgba(15,23,42,0.18)',
                    }}>
                        <div style={{
                            width: 72, height: 72, borderRadius: 36,
                            background: GREEN_50,
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                        }}>
                            <CircleCheck size={40} color={GREEN_VIVID} strokeWidth={2} />
                        </div>

                        <span style={{ fontSize: 18, fontWeight: 700, color: SLATE_900, fontFamily: '"DM Sans", system-ui', textAlign: 'center' }}>
                            Pesanan Diterima!
                        </span>

                        <span style={{ fontSize: 13, color: SLATE_500, fontFamily: 'Outfit, system-ui', textAlign: 'center', lineHeight: 1.5, width: '100%' }}>
                            Pesanan berhasil dibuat dan siap diproses. Anda bisa langsung membuat pesanan berikutnya.
                        </span>

                        <div style={{
                            background: BLUE_50, borderRadius: 14,
                            width: '100%', padding: '12px 16px',
                            display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 2,
                        }}>
                            <span style={{ fontSize: 11, fontWeight: 500, color: SLATE_400, fontFamily: 'Outfit, system-ui' }}>
                                Total Pembayaran
                            </span>
                            <span style={{ fontSize: 22, fontWeight: 700, color: BLUE, fontFamily: '"DM Sans", system-ui' }}>
                                {formatRupiah(successTotal)}
                            </span>
                        </div>

                        <button
                            onClick={handleSuccessOk}
                            style={{
                                width: '100%', height: 46,
                                background: BLUE, color: WHITE,
                                border: 'none', borderRadius: 14,
                                fontSize: 15, fontWeight: 700, fontFamily: '"DM Sans", system-ui',
                                cursor: 'pointer',
                                boxShadow: '0 4px 14px rgba(59,111,212,0.30)',
                            }}
                        >
                            OK
                        </button>
                    </div>
                </div>
            )}
        </CashierLayout></>
    );
}
