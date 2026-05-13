import { Link } from '@inertiajs/react';
import { UtensilsCrossed, ShoppingCart, Clock } from 'lucide-react';
import useCart from '@/Hooks/useCart';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';

function getRiwayatHref() {
    try {
        const saved = sessionStorage.getItem('w9_customer');
        if (saved) {
            const data = JSON.parse(saved);
            if (data?.phone) return `/customer/riwayat?phone=${encodeURIComponent(data.phone)}`;
        }
    } catch (_) {}
    return '/customer/riwayat';
}

export default function BottomNav({ activeTab }) {
    const { count } = useCart();

    const TABS = [
        { key: 'menu',    label: 'Menu',      Icon: UtensilsCrossed, href: '/customer/menu' },
        { key: 'cart',    label: 'Keranjang', Icon: ShoppingCart,   href: '/customer/cart' },
        { key: 'riwayat', label: 'Riwayat',   Icon: Clock,          href: getRiwayatHref() },
    ];

    return (
        <div
            className={cn(
                'fixed bottom-0 left-0 right-0 z-50',
                'pb-[env(safe-area-inset-bottom,0px)]',
                'sm:relative sm:z-30',
                'bg-background/95 backdrop-blur-sm',
                'border-t border-border',
                'sm:border-t-0 sm:border-b',
            )}
        >
            <nav
                className="flex items-center justify-around h-16 sm:h-12"
            >
                {TABS.map(({ key, label, Icon, href }) => {
                    const active = activeTab === key;
                    const showBadge = key === 'cart' && count > 0;

                    return (
                        <Link
                            key={key}
                            href={href}
                            className={cn(
                                buttonVariants({ variant: 'ghost' }),
                                'flex-1 h-full rounded-none',
                                'flex-col gap-0.5',
                                'sm:flex-row sm:gap-2 sm:rounded-lg sm:flex-none sm:px-4',
                                'transition-colors duration-150',
                                active && [
                                    'text-primary sm:bg-accent',
                                    'border-t-2 border-primary sm:border-t-0',
                                    'font-semibold',
                                ],
                                !active && 'text-muted-foreground',
                            )}
                        >
                            <span className="relative inline-flex">
                                <Icon className="size-[22px] sm:size-4" />
                                {showBadge && (
                                    <span className="absolute -top-1 -right-2 flex items-center justify-center min-w-[17px] h-[17px] rounded-full bg-destructive text-destructive-foreground text-[10px] font-bold leading-none ring-2 ring-background">
                                        {count > 9 ? '9+' : count}
                                    </span>
                                )}
                            </span>
                            <span className={cn('text-[11px] font-medium sm:text-sm', active && 'font-semibold')}>
                                {label}
                            </span>
                        </Link>
                    );
                })}
            </nav>
        </div>
    );
}
