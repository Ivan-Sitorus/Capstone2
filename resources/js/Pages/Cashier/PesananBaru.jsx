import { useState, useMemo, useEffect } from 'react';
import { router, Head } from '@inertiajs/react';
import { Search, X, Banknote, Lock, User, CircleCheck, Clock, PanelRightClose, PanelRightOpen, Printer, Percent } from 'lucide-react';
import { QRCodeCanvas } from 'qrcode.react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import CashierLayout from '@/Layouts/CashierLayout';
import MenuGridItem from '@/Components/Cashier/MenuGridItem';
import KeranjangItem from '@/Components/Cashier/KeranjangItem';
import { formatRupiah, formatDate, formatTime } from '@/helpers';
import { cn } from '@/lib/utils';

export default function PesananBaru({ categories, promotions }) {
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
    const [successOrderCode, setSuccessOrderCode] = useState('');
    const [isCartCollapsed, setIsCartCollapsed] = useState(false);
    const [isLeftSidebarCollapsed, setIsLeftSidebarCollapsed] = useState(() => {
        if (typeof window === 'undefined') return false;
        return window.localStorage.getItem('cashier-sidebar-collapsed') === 'true';
    });
    const [viewport, setViewport] = useState(() => {
        if (typeof window === 'undefined') return { width: 1280, height: 720 };
        return { width: window.innerWidth, height: window.innerHeight };
    });

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

    const [isMahasiswa, setIsMahasiswa] = useState(false);

    const totalQty      = cartItems.reduce((s, i) => s + i.quantity, 0);
    const total         = cartItems.reduce((s, i) => s + i.price * i.quantity, 0);
    const totalCashback = isMahasiswa ? cartItems.reduce((s, i) => s + (i.cashback ?? 0) * i.quantity, 0) : 0;
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

    function addToCart(menu) {
        setCartItems(prev => {
            const existing = prev.find(i => i.menuId === menu.id);
            if (existing) return prev.map(i => i.menuId === menu.id ? { ...i, quantity: i.quantity + 1 } : i);
            return [...prev, { menuId: menu.id, name: menu.name, price: Number(menu.price), cashback: Number(menu.cashback ?? 0), quantity: 1 }];
        });
    }
    const increment = (menuId) => setCartItems(prev => prev.map(i => i.menuId === menuId ? { ...i, quantity: i.quantity + 1 } : i));
    const decrement = (menuId) => setCartItems(prev => prev.map(i => i.menuId === menuId ? { ...i, quantity: i.quantity - 1 } : i).filter(i => i.quantity > 0));

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

    function handleChooseProceed() {
        if (!customerName.trim()) {
            setNameError(true);
            return;
        }
        setNameError(false);
        submitOrder(payMethod);
    }

    function submitOrder(method) {
        const orderTotal = grandTotal;
        setProcessing(true);
        router.post(
            '/cashier/pesanan-baru',
            { items: cartItems.map(i => ({ menu_id: i.menuId, quantity: i.quantity })), payment_method: method, customer_name: customerName.trim() || null, is_mahasiswa: isMahasiswa },
            {
                onSuccess: (page) => {
                    setProcessing(false);
                    setShowPayModal(false);
                    setSuccessTotal(orderTotal);
                    const flash = page?.props?.flash || {};
                    setSuccessOrderCode(flash.receipt_order_code || '');
                    setShowSuccess(true);
                },
                onError: () => setProcessing(false),
            }
        );
    }

    function handleSuccessOk() {
        setShowSuccess(false);
        setCartItems([]);
        setCustomerName('');
        setPayMethod('cash');
        router.visit('/cashier/pesanan-aktif');
    }

    /* ─── PAYMENT METHOD CONFIG ─── */
    const methods = [
        {
            key: 'cash',
            label: 'Bayar ke Kasir (Cash)',
            desc: 'Bayar langsung di kasir setelah pesanan dikonfirmasi',
            icon: <Banknote size={22} />,
            iconBg: (active) => active ? 'bg-blue-100' : 'bg-muted',
            iconColor: (active) => active ? 'text-primary' : 'text-muted-foreground',
        },
        {
            key: 'qris',
            label: 'QRIS',
            desc: 'Pindai kode QR & konfirmasi kasir',
            icon: <img src="/images/logo-qris.png" alt="QRIS" className="w-7 h-7 object-contain" />,
            iconBg: (active) => active ? 'bg-blue-100' : 'bg-muted',
            iconColor: () => '',
        },
        {
            key: 'bayar_nanti',
            label: 'Bayar Nanti',
            desc: 'Simpan pesanan, pelanggan bayar nanti',
            icon: <Clock size={22} />,
            iconBg: (active) => active ? 'bg-blue-100' : 'bg-muted',
            iconColor: (active) => active ? 'text-primary' : 'text-muted-foreground',
        },
    ];

    return (
        <><Head title="Pesanan Baru | W9 Cafe" /><CashierLayout title="Pesanan Baru" fullscreen>
            <div className="flex" style={{ flexDirection: isPortrait ? 'column' : 'row', height: '100vh', overflow: 'hidden' }}>

                {/* ══ PANEL TENGAH ══ */}
                <div
                    className="flex flex-col gap-4 overflow-y-auto min-h-0 flex-1 bg-muted"
                    style={{ padding: isPortrait ? 14 : 24, height: isPortrait ? 'auto' : '100vh' }}
                >
                    {/* Search */}
                    <div className="relative">
                        <Search size={18} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground/70 pointer-events-none" />
                        <Input
                            type="text"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder="Cari menu..."
                            className="w-full h-11 pl-11 pr-10 shadow-[0_2px_8px_rgba(15,23,42,0.04)]"
                        />
                        {search && (
                            <button
                                onClick={() => setSearch('')}
                                className="absolute right-3 top-1/2 -translate-y-1/2 bg-none border-none cursor-pointer p-0 flex text-muted-foreground/70"
                                aria-label="Hapus pencarian"
                            >
                                <X size={16} />
                            </button>
                        )}
                    </div>

                    {/* Category chips */}
                    <div className="flex gap-2 overflow-x-auto pb-1 shrink-0">
                        {['Semua', ...categories.map(c => c.name)].map(cat => {
                            const active = activeCategory === cat;
                            return (
                                <button
                                    key={cat}
                                    onClick={() => setActiveCategory(cat)}
                                    className={cn(
                                        'h-9 px-4 rounded-full cursor-pointer text-sm whitespace-nowrap shrink-0 transition-colors duration-150 font-medium',
                                        active
                                            ? 'bg-primary text-primary-foreground font-semibold border-none'
                                            : 'bg-card text-muted-foreground border border-border',
                                    )}
                                >
                                    {cat}
                                </button>
                            );
                        })}
                    </div>

                    {/* Menu grid */}
                    {filteredMenus.length === 0 ? (
                        <div className="text-center pt-12 text-sm text-muted-foreground/70">Tidak ada menu ditemukan</div>
                    ) : (
                        <div className="grid" style={{ gridTemplateColumns: menuGridColumns, gap: isPortrait ? 10 : 16 }}>
                            {filteredMenus.map(menu => <MenuGridItem key={menu.id} menu={menu} onAdd={addToCart} />)}
                        </div>
                    )}
                </div>

                {/* ══ PANEL KANAN — Keranjang ══ */}
                <div
                    className="flex flex-col shrink-0 overflow-y-auto overflow-x-hidden bg-card"
                    style={{
                        width: isPortrait ? '100%' : cartPanelWidth,
                        borderLeft: isPortrait ? 'none' : '1px solid hsl(var(--border))',
                        borderTop: isPortrait ? '1px solid hsl(var(--border))' : 'none',
                        padding: isCartCollapsed ? '14px 12px' : (isPortrait ? '14px 16px 16px' : 24),
                        height: isPortrait ? (isCartCollapsed ? 72 : '44vh') : '100vh',
                        transition: 'width 0.2s ease, height 0.2s ease, padding 0.2s ease',
                    }}
                >
                    <div className="flex items-center gap-2.5" style={{ justifyContent: isCartCollapsed ? 'center' : 'space-between', marginBottom: isCartCollapsed ? 0 : 16 }}>
                        {!isCartCollapsed && <span className="text-base font-bold tracking-tight text-foreground">Keranjang Pesanan</span>}
                        <div className="flex items-center gap-2">
                            <span className="rounded-full flex items-center justify-center text-xs font-bold bg-primary text-primary-foreground w-7 h-7">{totalQty}</span>
                            <button
                                type="button"
                                onClick={() => setIsCartCollapsed(prev => !prev)}
                                title={isCartCollapsed ? 'Expand keranjang' : 'Collapse keranjang'}
                                className="w-8 h-8 rounded-lg inline-flex items-center justify-center cursor-pointer bg-muted text-muted-foreground border border-border"
                            >
                                {isCartCollapsed ? <PanelRightOpen size={16} /> : <PanelRightClose size={16} />}
                            </button>
                        </div>
                    </div>
                    {!isCartCollapsed && (
                        <>
                            <div className="flex-1 overflow-y-auto">
                                {cartItems.length === 0 ? (
                                    <p className="text-center mt-10 text-sm text-muted-foreground/70">Keranjang kosong</p>
                                ) : (
                                    cartItems.map(item => <KeranjangItem key={item.menuId} item={item} onIncrement={increment} onDecrement={decrement} />)
                                )}
                            </div>
                            {/* Active Promotions */}
                            {promotions && promotions.length > 0 && cartItems.length > 0 && (
                                <div className="py-3 border-t border-border">
                                    <div className="flex items-center gap-1.5 mb-2">
                                        <Percent size={14} className="text-primary" />
                                        <span className="text-xs font-semibold uppercase tracking-wide text-primary">
                                            Promo Aktif
                                        </span>
                                        <span className="text-[10px] rounded-full px-1.5 py-px font-semibold bg-primary/10 text-primary">
                                            {promotions.length}
                                        </span>
                                    </div>
                                    {promotions.map(promo => (
                                        <div key={promo.id} className="flex items-start justify-between py-1 gap-2 border-b border-border">
                                            <div className="min-w-0 flex-1">
                                                <span className="text-xs font-semibold text-foreground">
                                                    {promo.name}
                                                </span>
                                                {promo.is_student_only && (
                                                    <span className="text-[10px] ml-1.5 rounded-full px-1.5 py-px bg-[#FFF0E8] text-[#E8763A]">
                                                        Mahasiswa
                                                    </span>
                                                )}
                                                <span className="block text-[11px] truncate text-muted-foreground">
                                                    {promo.type === 'percentage'
                                                        ? `Diskon ${Number(promo.discount_value)}%`
                                                        : promo.type === 'fixed_amount'
                                                            ? `Potongan ${formatRupiah(Number(promo.discount_value))}`
                                                            : promo.description}
                                                    {promo.min_purchase > 0 && ` (min. ${formatRupiah(Number(promo.min_purchase))})`}
                                                </span>
                                            </div>
                                            <span className="text-[11px] font-semibold shrink-0 mt-0.5 text-green-600">
                                                Aktif
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            )}
                            <div className="pt-4 mt-auto border-t border-border">
                        {/* Toggle Mahasiswa */}
                        <div
                            onClick={() => setIsMahasiswa(p => !p)}
                            className="flex items-center gap-2 mb-3 cursor-pointer select-none"
                        >
                            <div
                                className="flex items-center justify-center shrink-0 w-4 h-4 rounded border transition-colors duration-150"
                                style={{
                                    borderColor: isMahasiswa ? 'hsl(var(--primary))' : '#CBD5E1',
                                    background: isMahasiswa ? 'hsl(var(--primary))' : 'white',
                                }}
                            >
                                {isMahasiswa && <span className="text-primary-foreground text-[11px] leading-none">✓</span>}
                            </div>
                            <span className="text-sm text-muted-foreground">Mahasiswa STIE Totalwin Semarang</span>
                        </div>

                        <div className="flex justify-between mb-2">
                            <span className="text-sm text-muted-foreground">Subtotal</span>
                            <span className="text-sm font-medium text-foreground">{formatRupiah(total)}</span>
                        </div>
                        {isMahasiswa && totalCashback > 0 && (
                            <div className="flex justify-between mb-2">
                                <span className="text-sm text-green-600">Cashback Mahasiswa</span>
                                <span className="text-sm font-semibold text-green-600">- {formatRupiah(totalCashback)}</span>
                            </div>
                        )}
                        <div className="flex justify-between py-3 mb-4 border-t border-border">
                            <span className="text-base font-bold text-foreground">Total</span>
                            <span className="text-base font-bold text-foreground">{formatRupiah(grandTotal)}</span>
                        </div>
                        <button
                            onClick={openModal} disabled={cartItems.length === 0}
                            className={cn(
                                'w-full h-13 border-none rounded-xl text-base font-bold flex items-center justify-between px-5 transition-colors duration-150',
                                cartItems.length === 0
                                    ? 'bg-slate-300 text-white cursor-not-allowed'
                                    : 'bg-primary text-white cursor-pointer shadow-[0_4px_16px_rgba(59,111,212,0.30)]',
                            )}
                        >
                            <span>BAYAR</span>
                            <span className="text-lg">{formatRupiah(grandTotal)}</span>
                        </button>
                            </div>
                        </>
                    )}
                </div>
            </div>

            {/* ══ MODAL ══ */}
            {showPayModal && (
                <div
                    className="fixed inset-0 flex items-center justify-center z-1000 p-6 bg-[rgba(15,23,42,0.55)]"
                    onClick={e => { if (e.target === e.currentTarget) closeModal(); }}
                >
                    <div className="w-full bg-card overflow-hidden shadow-[0_24px_64px_rgba(15,23,42,0.18),0_2px_8px_rgba(15,23,42,0.06)]" style={{ borderRadius: 24, maxWidth: 440 }}>

                        {(<>
                            <div className="px-6 py-[22px] pb-4 border-b border-border">
                                <div className="flex items-center gap-3.5">
                                    <button onClick={closeModal} className="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border-none cursor-pointer bg-muted">
                                        <X size={20} className="text-foreground" />
                                    </button>
                                    <div className="flex flex-col gap-0.5">
                                        <span className="text-xl font-bold leading-tight text-foreground">
                                            Metode Pembayaran
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {cartItems.length} item · {formatRupiah(total)}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col gap-[18px] px-6 pb-6 overflow-y-auto" style={{ maxHeight: '68vh' }}>
                                <div className="flex flex-col gap-1.5 pt-2 border-t border-border mt-1">
                                    <label className="text-sm font-medium text-foreground">Nama Pelanggan</label>
                                    <div className="relative">
                                        <User size={18} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground/70 pointer-events-none" />
                                        <Input
                                            type="text"
                                            value={customerName}
                                            onChange={e => { setCustomerName(e.target.value); if (nameError) setNameError(false); }}
                                            placeholder="Masukkan nama pelanggan..."
                                            className={cn(
                                                'w-full h-11 pl-11',
                                                nameError && 'border-destructive bg-destructive/5',
                                            )}
                                        />
                                    </div>
                                    {nameError && (
                                        <p className="m-0 mt-1 text-xs text-destructive">
                                            Nama pelanggan wajib diisi
                                        </p>
                                    )}
                                </div>

                                <p className="m-0 text-sm font-semibold tracking-wide text-muted-foreground">Metode Pembayaran</p>

                                <div className="flex flex-col gap-2.5 -mt-2">
                                    {methods.map(m => {
                                        const active = payMethod === m.key;
                                        return (
                                            <button
                                                key={m.key}
                                                onClick={() => setPayMethod(m.key)}
                                                className={cn(
                                                    'flex items-center gap-3.5 rounded-2xl cursor-pointer text-left p-4 transition-all duration-150',
                                                    active
                                                        ? 'border-2 border-primary bg-primary/5 shadow-[0_2px_10px_rgba(59,111,212,0.10)]'
                                                        : 'border border-border bg-card',
                                                )}
                                            >
                                                <div className={cn(
                                                    'w-10.5 h-10.5 rounded-xl flex items-center justify-center shrink-0',
                                                    m.iconBg(active),
                                                    m.iconColor(active),
                                                )}>
                                                    {m.icon}
                                                </div>
                                                <div className="flex-1 flex flex-col gap-0.5">
                                                    <span className="text-base font-semibold text-foreground">
                                                        {m.label}
                                                    </span>
                                                    <span className="text-xs text-muted-foreground">
                                                        {m.desc}
                                                    </span>
                                                </div>
                                                <div className={cn(
                                                    'w-5.5 h-5.5 rounded-full flex items-center justify-center shrink-0',
                                                    active ? 'border-2 border-primary' : 'border border-muted-foreground/40',
                                                )}>
                                                    {active && <div className="w-3 h-3 rounded-full bg-primary" />}
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>

                                <div className="flex justify-center items-center gap-1 pb-1">
                                    <Lock size={11} className="text-muted-foreground/70" />
                                </div>
                            </div>

                            <div className="px-6 pb-6">
                                <Button
                                    onClick={handleChooseProceed}
                                    disabled={processing}
                                    className="w-full h-13.5 text-base font-bold shadow-[0_4px_16px_rgba(59,111,212,0.28)]"
                                >
                                    {processing ? 'Memproses...' : 'Konfirmasi Pembayaran'}
                                </Button>
                            </div>
                        </>
                        )}

                    </div>
                </div>
            )}
            {/* ══ RECEIPT MODAL ══ */}
            {showSuccess && (
                <div className="fixed inset-0 z-2000 flex items-start justify-center py-8 px-4 overflow-y-auto bg-[rgba(15,23,42,0.55)]"
                     onClick={e => { if (e.target === e.currentTarget) handleSuccessOk(); }}
                >
                    <div className="w-full max-w-[400px] bg-card rounded-2xl shadow-xl overflow-hidden">
                        <div className="text-center pt-7 pb-4 px-6 border-b-2 border-dashed border-border">
                            <div className="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-3">
                                <CircleCheck size={28} className="text-green-500" strokeWidth={2} />
                            </div>
                            <h2 className="text-lg font-bold m-0 text-foreground">Pembayaran Berhasil</h2>
                            <p className="text-sm m-0 mt-1 text-muted-foreground">Struk digital tersedia</p>
                        </div>

                        <div className="px-6 py-4">
                            <div className="flex justify-between items-center py-2 border-b border-border">
                                <span className="text-sm text-muted-foreground">No. Pesanan</span>
                                <span className="text-sm font-bold text-foreground">#{successOrderCode}</span>
                            </div>

                            <div className="py-3 border-b border-border">
                                <p className="text-xs font-semibold mb-2 uppercase tracking-wider text-muted-foreground/70">
                                    Item ({cartItems.length})
                                </p>
                                {cartItems.map((item, idx) => (
                                    <div key={item.menuId} className="flex justify-between items-center py-1.5 text-sm">
                                        <span className="text-foreground/80">
                                            {item.quantity}x {item.name}
                                        </span>
                                        <span className="font-medium text-foreground">
                                            {formatRupiah(item.price * item.quantity)}
                                        </span>
                                    </div>
                                ))}
                            </div>

                            <div className="flex justify-between items-center py-3 border-b border-border">
                                <span className="text-base font-bold text-foreground">Total</span>
                                <span className="text-lg font-bold text-primary">{formatRupiah(successTotal)}</span>
                            </div>

                            <div className="flex flex-col items-center py-4">
                                {successOrderCode && (
                                    <div className="bg-card p-2 rounded-xl shadow-sm border border-border">
                                        <QRCodeCanvas
                                            value={window.location.origin + '/receipt/' + successOrderCode}
                                            size={120}
                                            level="M"
                                            fgColor="hsl(var(--foreground))"
                                            style={{ display: 'block' }}
                                        />
                                    </div>
                                )}
                                <p className="text-xs mt-2 m-0 text-center text-muted-foreground/70">
                                    Pindai QR untuk lihat struk
                                </p>
                            </div>

                            <div className="text-xs text-center text-muted-foreground/70">
                                {formatDate(new Date().toISOString())} · {formatTime(new Date().toISOString())}
                            </div>
                        </div>

                        <div className="px-6 pb-6 flex flex-col gap-2">
                            <Button
                                variant="outline"
                                onClick={() => {
                                    const url = window.location.origin + '/receipt/' + successOrderCode;
                                    window.open(url, '_blank');
                                }}
                                className="w-full h-11 flex items-center justify-center gap-2"
                            >
                                <Printer size={16} />
                                Cetak / Lihat Struk
                            </Button>
                            <Button
                                onClick={handleSuccessOk}
                                className="w-full h-11"
                            >
                                Lanjut ke Pesanan Aktif
                            </Button>
                        </div>
                    </div>
                </div>
            )}
        </CashierLayout></>
    );
}
