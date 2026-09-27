import { useState, useEffect } from 'react';
import { Calendar } from 'lucide-react';
import { formatDate, formatTime } from '@/helpers';
import { WHITE, SLATE_200, SLATE_900, SLATE_500 } from '@/theme';

/**
 * Jam hidup yang mengisolasi tick 1 detik di dalam komponen ini sendiri,
 * sehingga halaman induk (mis. Dashboard) tidak ikut re-render tiap detik.
 * Tick dijeda saat tab tidak aktif, lalu langsung menyegar ketika tab kembali
 * terlihat (perilaku yang sama dengan usePolling lama).
 */
export default function LiveClock() {
    const [now, setNow] = useState(() => new Date());

    useEffect(() => {
        const tick = () => {
            if (document.visibilityState === 'hidden') return;
            setNow(new Date());
        };

        const id = setInterval(tick, 1000);
        const onVisible = () => {
            if (document.visibilityState === 'visible') tick();
        };

        document.addEventListener('visibilitychange', onVisible);

        return () => {
            clearInterval(id);
            document.removeEventListener('visibilitychange', onVisible);
        };
    }, []);

    return (
        <div style={{
            display: 'flex',
            alignItems: 'center',
            gap: 8,
            background: WHITE,
            border: `1px solid ${SLATE_200}`,
            borderRadius: 8,
            padding: '8px 14px',
            fontSize: 13,
            fontWeight: 500,
            color: SLATE_900,
            boxShadow: '0 2px 8px rgba(15,23,42,0.04)',
            flexShrink: 0,
        }}>
            <Calendar size={16} color={SLATE_500} />
            {formatDate(now)}, {formatTime(now)}
        </div>
    );
}
