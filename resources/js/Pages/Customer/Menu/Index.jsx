import { useState, useEffect, useMemo } from 'react';
import { router } from '@inertiajs/react';
import { Search, ShoppingBag, Coffee } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import useCart from '@/Hooks/useCart';
import { formatRupiah } from '@/helpers';

const F = '"Plus Jakarta Sans", system-ui, sans-serif';

/* ── Inline menu item card (2-col grid, vertical) ───────────────────── */
function MenuItemCard({ menu, cartItem, onAdd, onIncrement, onDecrement }) {
    const cashback = Number(menu.cashback ?? 0);

    return (
        <div style={{
            background: '#FFFFFF',
            borderRadius: 16,
            overflow: 'hidden',
            boxShadow: '0 1px 8px rgba(26,24,20,0.07)',
            display: 'flex',
            flexDirection: 'column',
        }}>
            {/* Image */}
            <div style={{
                width: '100%', aspectRatio: '4/3',
                background: 'linear-gradient(135deg, #C4956A 0%, #A67B55 100%)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                overflow: 'hidden',
            }}>
                {menu.image
                    ? <img src={menu.image} alt={menu.name}
                           style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                    : <Coffee size={36} color="#FFFFFF" strokeWidth={1.5} />
                }
            </div>

            {/* Info */}
            <div style={{ padding: '10px 12px 12px', flex: 1, display: 'flex', flexDirection: 'column' }}>
                <div style={{
                    fontSize: 13, fontWeight: 700, color: '#1A1814',
                    fontFamily: F, lineHeight: 1.3, marginBottom: 4,
                }}>
                    {menu.name}
                </div>
                <div style={{
                    fontSize: 13, fontWeight: 600, color: '#E8763A',
                    fontFamily: F, marginBottom: cashback > 0 ? 4 : 8,
                }}>
                    {formatRupiah(Number(menu.price))}
                </div>
                {cashback > 0 && (
                    <div style={{
                        fontSize: 10, color: '#16A34A', fontFamily: F,
                        display: 'flex', alignItems: 'center', gap: 3,
                        marginBottom: 8,
                    }}>
                        <span style={{ fontSize: 6, lineHeight: 1 }}>●</span>
                        Cashback {formatRupiah(cashback)}
                    </div>
                )}

                {/* Action */}
                <div style={{ marginTop: 'auto' }}>
                    {cartItem ? (
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10 }}>
                            <button onClick={onDecrement} style={{
                                width: 30, height: 30, borderRadius: 8,
                                background: '#F5F0EB', border: 'none', cursor: 'pointer',
                                fontSize: 18, fontWeight: 500, color: '#6B5E52',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                fontFamily: F,
                            }}>−</button>
                            <span style={{
                                fontSize: 15, fontWeight: 700, color: '#1A1814',
                                minWidth: 20, textAlign: 'center', fontFamily: F,
                            }}>
                                {cartItem.quantity}
                            </span>
                            <button onClick={onIncrement} style={{
                                width: 30, height: 30, borderRadius: 8,
                                background: '#E8763A', border: 'none', cursor: 'pointer',
                                fontSize: 18, fontWeight: 600, color: '#FFFFFF',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                boxShadow: '0 3px 10px rgba(232,118,58,0.32)',
                                fontFamily: F,
                            }}>+</button>
                        </div>
                    ) : (
                        <button onClick={onAdd} style={{
                            width: '100%',
                            background: '#E8763A', color: '#FFFFFF',
                            border: 'none', borderRadius: 50,
                            padding: '8px 0',
                            fontSize: 12, fontWeight: 700,
                            fontFamily: F, cursor: 'pointer',
                            boxShadow: '0 3px 10px rgba(232,118,58,0.28)',
                        }}>
                            + Tambah
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}

/* ── Main page ─────────────────────────────────────────────────────── */
export default function CustomerMenu({ categories, table }) {
    const [activeCategory, setActiveCategory] = useState('all');
    const [search,         setSearch]         = useState('');
    const [customer,       setCustomer]       = useState(null);

    const { items, addItem, updateQty, setTable, total, count } = useCart();

    /* Font injection */
    useEffect(() => {
        if (!document.getElementById('pjs-font')) {
            const link = document.createElement('link');
            link.id   = 'pjs-font';
            link.rel  = 'stylesheet';
            link.href = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap';
            document.head.appendChild(link);
        }
    }, []);

    /* Auth guard */
    useEffect(() => {
        try {
            const saved = sessionStorage.getItem('w9_customer');
            if (!saved) {
                const fb = table?.id ?? '';
                router.visit(fb ? `/order?table=${fb}` : '/order');
                return;
            }
            const data = JSON.parse(saved);
            if (!data.name || !data.phone) {
                sessionStorage.removeItem('w9_customer');
                const fb = table?.id ?? '';
                router.visit(fb ? `/order?table=${fb}` : '/order');
                return;
            }
            if (table?.id && data.tableId !== table.id) {
                sessionStorage.removeItem('w9_customer');
                router.visit(`/order?table=${table.id}`);
                return;
            }
            setCustomer(data);
            setTable(table?.id ?? data.tableId ?? null);
        } catch (_) {
            router.visit('/order');
        }
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

    return (
        <CustomerLayout activeTab="menu">

            {/* ── Header ── */}
            <div style={{ background: '#FFFFFF', padding: '20px 20px 16px' }}>
                {/* Logo + Greeting row */}
                <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 14 }}>
                    {/* Logo */}
                    <div style={{
                        width: 52, height: 52, borderRadius: 14, overflow: 'hidden', flexShrink: 0,
                        background: 'radial-gradient(ellipse 140% 140% at 50% 30%, #2A4F5F 0%, #1B3A4B 100%)',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        boxShadow: '0 4px 14px rgba(0,0,0,0.18)',
                    }}>
                        <img
                            src="/images/logo.jpg"
                            alt="W9 Cafe"
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                            onError={e => { e.target.style.display = 'none'; }}
                        />
                    </div>

                    {/* Text */}
                    <div>
                        <div style={{
                            fontSize: 12, color: '#A8998A',
                            fontFamily: F, marginBottom: 1,
                        }}>
                            Selamat datang,
                        </div>
                        <div style={{
                            fontSize: 20, fontWeight: 800, color: '#1A1814',
                            fontFamily: F, letterSpacing: -0.4, lineHeight: 1.2,
                        }}>
                            {customer?.name ?? 'Pelanggan'}
                        </div>
                        <div style={{
                            fontSize: 11, color: '#A8998A', fontFamily: F, marginTop: 1,
                        }}>
                            Meja {table?.table_number ?? customer?.tableNumber ?? '-'}
                        </div>
                    </div>
                </div>

                {/* Search */}
                <div style={{ position: 'relative' }}>
                    <Search size={17} style={{
                        position: 'absolute', left: 16, top: '50%',
                        transform: 'translateY(-50%)',
                        color: '#C4B5A5', pointerEvents: 'none',
                    }} />
                    <input
                        type="text"
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        placeholder="Cari menu favoritmu..."
                        style={{
                            width: '100%', height: 46,
                            background: '#F5F0EB',
                            border: 'none', borderRadius: 50,
                            padding: '0 20px 0 44px',
                            fontSize: 14, color: '#1A1814',
                            fontFamily: F, outline: 'none',
                            boxSizing: 'border-box',
                        }}
                    />
                </div>
            </div>

            {/* ── Category chips ── */}
            <div style={{
                background: '#FFFFFF',
                borderBottom: '1px solid #F5F0EB',
            }}>
                <div style={{
                    display: 'flex', gap: 8,
                    overflowX: 'auto', scrollbarWidth: 'none',
                    padding: '12px 20px 14px',
                }}>
                    {[{ id: 'all', name: 'Semua' }, ...categories].map(c => {
                        const active = activeCategory === (c.id === 'all' ? 'all' : c.name);
                        return (
                            <button
                                key={c.id}
                                onClick={() => setActiveCategory(c.id === 'all' ? 'all' : c.name)}
                                style={{
                                    flexShrink: 0,
                                    background: active ? '#E8763A' : '#F5F0EB',
                                    borderRadius: 50, border: 'none',
                                    padding: '8px 18px',
                                    fontSize: 13,
                                    fontWeight: active ? 700 : 500,
                                    color: active ? '#FFFFFF' : '#8C7B6B',
                                    fontFamily: F, cursor: 'pointer',
                                    whiteSpace: 'nowrap',
                                    transition: 'background 0.15s, color 0.15s',
                                    boxShadow: active ? '0 3px 10px rgba(232,118,58,0.28)' : 'none',
                                }}
                            >
                                {c.name}
                            </button>
                        );
                    })}
                </div>
            </div>

            {/* ── Menu list ── */}
            <div style={{
                padding: '16px 20px',
                paddingBottom: count > 0 ? 90 : 32,
                background: '#F5F0EB',
                minHeight: 'calc(100vh - 180px)',
            }}>
                {filtered.length === 0 ? (
                    <div style={{
                        textAlign: 'center', color: '#B5A898',
                        padding: '56px 0', fontSize: 14, fontFamily: F,
                    }}>
                        Tidak ada menu ditemukan
                    </div>
                ) : activeCategory === 'all' ? (
                    /* Flat list saat "Semua" — jarak seragam */
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                        {filtered.map(menu => (
                            <MenuItemCard
                                key={menu.id}
                                menu={menu}
                                cartItem={cartMap[menu.id]}
                                onAdd={() => addItem(menu)}
                                onIncrement={() => updateQty(menu.id, (cartMap[menu.id]?.quantity ?? 0) + 1)}
                                onDecrement={() => updateQty(menu.id, (cartMap[menu.id]?.quantity ?? 0) - 1)}
                            />
                        ))}
                    </div>
                ) : (
                    /* Grouped dengan section header saat kategori tertentu dipilih */
                    grouped.map(group => (
                        <div key={group.label} style={{ marginBottom: 22 }}>
                            <div style={{
                                display: 'flex', justifyContent: 'space-between',
                                alignItems: 'center', marginBottom: 12,
                            }}>
                                <span style={{
                                    fontSize: 16, fontWeight: 700,
                                    color: '#1A1814', fontFamily: F,
                                }}>
                                    {group.label}
                                </span>
                                <span style={{
                                    fontSize: 12, color: '#A8998A', fontFamily: F,
                                }}>
                                    {group.menus.length} menu
                                </span>
                            </div>
                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                                {group.menus.map(menu => (
                                    <MenuItemCard
                                        key={menu.id}
                                        menu={menu}
                                        cartItem={cartMap[menu.id]}
                                        onAdd={() => addItem(menu)}
                                        onIncrement={() => updateQty(menu.id, (cartMap[menu.id]?.quantity ?? 0) + 1)}
                                        onDecrement={() => updateQty(menu.id, (cartMap[menu.id]?.quantity ?? 0) - 1)}
                                    />
                                ))}
                            </div>
                        </div>
                    ))
                )}
            </div>

            {/* ── Fixed cart bar ── */}
            {count > 0 && (
                <div style={{
                    position: 'fixed', bottom: 0,
                    left: '50%', transform: 'translateX(-50%)',
                    width: '100%', maxWidth: 430,
                    background: '#1A1814',
                    padding: '12px 16px',
                    display: 'flex', alignItems: 'center',
                    justifyContent: 'space-between',
                    zIndex: 100,
                    boxShadow: '0 -4px 24px rgba(0,0,0,0.22)',
                }}>
                    {/* Left */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        <div style={{ position: 'relative', flexShrink: 0 }}>
                            <ShoppingBag size={22} color="#FFFFFF" />
                            <span style={{
                                position: 'absolute', top: -7, right: -7,
                                background: '#E8763A', color: '#FFFFFF',
                                borderRadius: '50%', width: 18, height: 18,
                                fontSize: 10, fontWeight: 700, fontFamily: F,
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                            }}>
                                {count}
                            </span>
                        </div>
                        <div>
                            <div style={{ fontSize: 11, color: '#A8998A', fontFamily: F }}>
                                {count} item di keranjang
                            </div>
                            <div style={{
                                fontSize: 14, fontWeight: 700, color: '#FFFFFF',
                                fontFamily: F, letterSpacing: -0.2,
                            }}>
                                {formatRupiah(total)}
                            </div>
                        </div>
                    </div>

                    {/* Right */}
                    <button
                        onClick={() => router.visit('/customer/cart')}
                        style={{
                            background: '#E8763A', color: '#FFFFFF',
                            border: 'none', borderRadius: 12,
                            padding: '10px 18px',
                            fontSize: 13, fontWeight: 700,
                            fontFamily: F, cursor: 'pointer',
                            whiteSpace: 'nowrap',
                            boxShadow: '0 4px 14px rgba(232,118,58,0.35)',
                            letterSpacing: -0.1,
                        }}
                    >
                        Lihat Keranjang →
                    </button>
                </div>
            )}

        </CustomerLayout>
    );
}
