import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import BottomNav from '@/Components/Customer/BottomNav';
import { ORANGE_100, ORANGE_200, ORANGE_700 } from '@/theme';

export default function CustomerLayout({ children, activeTab = 'menu', showBottomNav = true }) {
    const { flash } = usePage().props;
    const [info, setInfo] = useState(null);

    /* ── Flash info toast ── */
    useEffect(() => {
        if (flash?.info) {
            setInfo(flash.info);
            const t = setTimeout(() => setInfo(null), 5000);
            return () => clearTimeout(t);
        }
    }, [flash]);

    return (
        <div style={{
            maxWidth: 430,
            margin: '0 auto',
            minHeight: '100vh',
            background: 'transparent',
            position: 'relative',
            paddingBottom: showBottomNav ? 80 : 0,
        }}>
            {children}
            {showBottomNav && <BottomNav activeTab={activeTab} />}

            {/* ── Info toast (verifikasi pending) ── */}
            {info && (
                <div style={{
                    position: 'fixed',
                    bottom: 80,
                    left: '50%',
                    transform: 'translateX(-50%)',
                    width: 'calc(100% - 32px)',
                    maxWidth: 398,
                    background: ORANGE_100,
                    border: `1px solid ${ORANGE_200}`,
                    borderRadius: 14,
                    padding: '12px 16px',
                    fontSize: 13,
                    color: ORANGE_700,
                    fontFamily: 'Outfit, system-ui, sans-serif',
                    boxShadow: '0 4px 16px rgba(45,32,22,0.12)',
                    zIndex: 9999,
                    textAlign: 'center',
                    lineHeight: 1.5,
                }}>
                    ⏳ {info}
                </div>
            )}
        </div>
    );
}
