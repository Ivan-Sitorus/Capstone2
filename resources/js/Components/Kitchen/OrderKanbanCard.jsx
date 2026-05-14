import { useState, useEffect, useRef } from 'react';
import { Loader2 } from 'lucide-react';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import CardBase from '@/Components/Shared/CardBase';

/* ── Timer helpers ─────────────────────────────────────────────── */
function pad(n) {
    return String(n).padStart(2, '0');
}

function getElapsedSeconds(createdAt) {
    return Math.floor((Date.now() - new Date(createdAt).getTime()) / 1000);
}

function getUrgency(seconds) {
    if (seconds >= 600) return 'critical';
    if (seconds >= 300) return 'warning';
    return 'normal';
}

function formatTimer(seconds) {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${pad(m)}:${pad(s)}`;
}

const URGENCY_STYLES = {
    normal:   { cardBg: 'bg-white',          border: 'border-border',          timerClass: 'text-muted-foreground' },
    warning:  { cardBg: 'bg-amber-50',       border: 'border-amber-400',       timerClass: 'text-amber-700'       },
    critical: { cardBg: 'bg-red-50',         border: 'border-red-400',         timerClass: 'text-red-700'         },
};

/* ── Component ────────────────────────────────────────────────── */
export default function OrderKanbanCard({ order, onBump, isBumping }) {
    const [now, setNow] = useState(Date.now());
    const intervalRef = useRef(null);

    useEffect(() => {
        intervalRef.current = setInterval(() => setNow(Date.now()), 1000);
        return () => clearInterval(intervalRef.current);
    }, []);

    const elapsed = getElapsedSeconds(order.created_at);
    const urgency = getUrgency(elapsed);
    const theme   = URGENCY_STYLES[urgency];

    const items   = Array.isArray(order.items) ? order.items : [];
    const canBump = order.status === 'pending' || order.status === 'diproses';
    const bumpLabel = order.status === 'pending' ? 'Ambil' : 'Selesai';

    return (
        <CardBase
            className={cn(
                theme.cardBg,
                theme.border,
                urgency === 'critical' && 'shadow-[0_4px_16px_rgba(239,68,68,0.25)]',
            )}
        >
            <div className="flex flex-col gap-1 items-center p-3">
                <span className="text-[11px] uppercase text-muted-foreground tracking-wide font-semibold">
                    #{order.order_code}
                </span>

                <span className={cn(
                    'font-bold leading-tight text-foreground text-base',
                    theme.timerClass,
                )}>
                    {formatTimer(elapsed)}
                </span>

                <div className="text-sm text-muted-foreground text-left w-full">
                    {items.length > 0 ? (
                        <div className="flex flex-col">
                            {items.map((item, i) => (
                                <div key={item.id || i}>
                                    {item.quantity}x {item.name}
                                </div>
                            ))}
                        </div>
                    ) : (
                        <span>-</span>
                    )}
                </div>

                {canBump && (
                    <button
                        className={cn(
                            buttonVariants({ variant: 'default', size: 'sm' }),
                            'w-full rounded-lg mt-1',
                            isBumping && 'opacity-50 cursor-not-allowed',
                        )}
                        disabled={!canBump || isBumping}
                        onClick={() => !isBumping && onBump?.(order)}
                    >
                        {isBumping ? (
                            <Loader2 className="animate-spin h-4 w-4" />
                        ) : (
                            bumpLabel
                        )}
                    </button>
                )}
            </div>
        </CardBase>
    );
}
