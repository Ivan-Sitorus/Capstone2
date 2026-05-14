import { useState, useEffect, useMemo, useCallback } from 'react';
import { router, Head, Link } from '@inertiajs/react';
import { Search, ShoppingBag } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import CustomerLayout from '@/Layouts/CustomerLayout';
import useCart from '@/Hooks/useCart';
import { formatRupiah } from '@/helpers';
import SharedMenuItem from '@/Components/Shared/SharedMenuItem';
import { cn } from '@/lib/utils';

/* ── Main page ─────────────────────────────────────────────────────── */
export default function CustomerMenu({ categories, table }) {
    const [activeCategory, setActiveCategory] = useState('all');
    const [search,         setSearch]         = useState('');
    const [customer,       setCustomer]       = useState(null);
    const [ready,          setReady]          = useState(false);
    const [logoError,      setLogoError]      = useState(false);

    const { items, addItem, removeItem, updateQty, setTable, total, count } = useCart();

    /* Save table info to session (no identity guard) */
    useEffect(() => {
        const tid = table?.id ?? null;
        const tn = table?.table_number ?? null;
        sessionStorage.setItem('w9_customer', JSON.stringify({ tableId: tid, tableNumber: tn }));
        setTable(tid);
        setReady(true);
    }, [table?.id]);

    /* Cart lookup map */
    const cartMap = useMemo(() => {
        const m = {};
        items.forEach(i => { m[i.menuId] = i; });
        return m;
    }, [items]);

    /* Flatten menus */
    const allMenus = useMemo(
        () => categories.flatMap(c => c.menus.map(m => ({ ...m, categoryName: c.name }))),
        [categories]
    );

    /* Filtered menus */
    const filtered = useMemo(() => {
        let list = activeCategory === 'all'
            ? allMenus
            : allMenus.filter(m => m.categoryName === activeCategory);
        if (search.trim()) {
            const q = search.toLowerCase();
            list = list.filter(m => m.name.toLowerCase().includes(q));
        }
        return list;
    }, [allMenus, activeCategory, search]);

    /* Group by category */
    const grouped = useMemo(() => {
        if (activeCategory !== 'all') {
            return [{ label: activeCategory, menus: filtered }];
        }
        const map = {};
        filtered.forEach(m => {
            if (!map[m.categoryName]) map[m.categoryName] = [];
            map[m.categoryName].push(m);
        });
        return Object.entries(map).map(([label, menus]) => ({ label, menus }));
    }, [filtered, activeCategory]);

    const handleVisitCart = useCallback(() => router.visit('/customer/cart'), []);

    const renderMenuGrid = useCallback((menuList) => (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            {menuList.map(menu => {
                const cartItem = cartMap[menu.id];
                const inCart = cartItem && cartItem.quantity > 0;
                return (
                    <SharedMenuItem
                        key={menu.id}
                        menu={menu}
                        variant="customer"
                        inCart={inCart}
                        quantity={cartItem?.quantity || 0}
                        onAdd={() => addItem(menu)}
                        onIncrement={() => addItem(menu)}
                        onDecrement={cartItem ? () => {
                            const newQty = cartItem.quantity - 1;
                            if (newQty <= 0) removeItem(menu.id);
                            else updateQty(menu.id, newQty);
                        } : undefined}
                    />
                );
            })}
        </div>
    ), [cartMap, addItem, removeItem, updateQty]);

    if (!ready) return null;

    return (
        <CustomerLayout activeTab="menu">
            <Head>
                <title>Menu — W9 Cafe</title>
                <meta name="description" content="Pesan makanan dan minuman favorit Anda di W9 Cafe STIE Totalwin." />
            </Head>

            <div className="bg-card px-5 pt-5 pb-4">
                <div className="flex items-center gap-3.5 mb-3.5">
                    <div className="w-[52px] h-[52px] rounded-[14px] overflow-hidden shrink-0 flex items-center justify-center bg-cyan-900 shadow-[0_4px_14px_rgba(0,0,0,0.18)]">
                        {logoError ? (
                            <span className="text-white text-lg italic font-bold">w9</span>
                        ) : (
                            <img
                                src="/images/logo.jpg"
                                alt="W9 Cafe"
                                fetchPriority="high"
                                loading="eager"
                                width="52"
                                height="52"
                                className="w-full h-full object-cover"
                                onError={() => setLogoError(true)}
                            />
                        )}
                    </div>

                    <div>
                        <div className="text-xs text-muted-foreground/70 mb-0.5">
                            Selamat datang,
                        </div>
                        <div className="text-xl font-extrabold text-foreground tracking-tight leading-tight">
                            {customer?.name ?? 'Pelanggan'}
                        </div>
                        <div className="text-[11px] text-muted-foreground/70 mt-0.5">
                            Meja {table?.table_number ?? customer?.tableNumber ?? '-'}
                        </div>
                    </div>
                </div>

                <div className="relative">
                    <Search
                        size={17}
                        className="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground/40 pointer-events-none"
                    />
                    <Input
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        placeholder="Cari menu favoritmu..."
                        className="w-full h-[46px] pl-11 rounded-full bg-muted border-none text-sm text-foreground"
                    />
                </div>
            </div>

            <div className="bg-card border-b border-border">
                <div className="flex gap-2 overflow-x-auto scrollbar-none px-5 pt-3 pb-3.5">
                    {[{ id: 'all', name: 'Semua' }, ...categories].map(c => {
                        const active = activeCategory === (c.id === 'all' ? 'all' : c.name);
                        return (
                            <Button
                                key={c.id}
                                variant={active ? 'default' : 'outline'}
                                className={cn(
                                    'shrink-0 rounded-full px-[18px] py-2 text-[13px] whitespace-nowrap cursor-pointer transition-colors duration-150',
                                    active && 'shadow-[0_3px_10px_rgba(232,118,58,0.28)] font-bold',
                                )}
                                onClick={() => setActiveCategory(c.id === 'all' ? 'all' : c.name)}
                            >
                                {c.name}
                            </Button>
                        );
                    })}
                </div>
            </div>

            <div className={cn('px-5 pt-4 bg-muted min-h-[calc(100vh-180px)]', count > 0 ? 'pb-[90px]' : 'pb-8')}>
                {filtered.length === 0 ? (
                    <div className="text-center text-muted-foreground/60 py-14 text-sm">
                        Tidak ada menu ditemukan
                    </div>
                ) : activeCategory === 'all' ? (
                    renderMenuGrid(filtered)
                ) : (
                    grouped.map(group => (
                        <div key={group.label} className="mb-5">
                            <div className="flex justify-between items-center mb-3">
                                <span className="text-base font-bold text-foreground">
                                    {group.label}
                                </span>
                                <span className="text-xs text-muted-foreground/70">
                                    {group.menus.length} menu
                                </span>
                            </div>
                            {renderMenuGrid(group.menus)}
                        </div>
                    ))
                )}
            </div>

            {count > 0 && (
                <div className="fixed bottom-16 left-1/2 -translate-x-1/2 w-full max-w-[430px] bg-stone-900 px-4 py-3 flex items-center justify-between z-50 shadow-[0_-4px_24px_rgba(0,0,0,0.22)]">
                    <div className="flex items-center gap-3">
                        <div className="relative shrink-0">
                            <ShoppingBag size={22} className="text-white" />
                            <span className="absolute -top-[7px] -right-[7px] bg-primary text-primary-foreground rounded-full w-[18px] h-[18px] text-[10px] font-bold flex items-center justify-center">
                                {count}
                            </span>
                        </div>
                        <div>
                            <div className="text-[11px] text-white/60">
                                {count} item di keranjang
                            </div>
                            <div className="text-sm font-bold text-white tracking-tight">
                                {formatRupiah(total)}
                            </div>
                        </div>
                    </div>

                    <Link
                        href="/customer/cart"
                        className="bg-primary text-primary-foreground rounded-xl px-[18px] py-[10px] text-[13px] font-bold whitespace-nowrap shadow-[0_4px_14px_rgba(232,118,58,0.35)] tracking-tight inline-flex items-center gap-1"
                    >
                        Lihat Keranjang
                        <span className="inline-block">→</span>
                    </Link>
                </div>
            )}
        </CustomerLayout>
    );
}
