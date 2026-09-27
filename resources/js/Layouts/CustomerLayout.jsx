import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import BottomNav from '@/Components/Customer/BottomNav';
import { ORANGE_100, ORANGE_200, ORANGE_700, STONE_50 } from '@/theme';

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
            minHeight: '100dvh',
            background: 'transparent',
            position: 'relative',
            paddingBottom: showBottomNav ? 80 : 0,
        }}>
            {/* ── Wallpaper bunga (SVG, satu layer untuk semua halaman pelanggan) ── */}
            <div
                aria-hidden="true"
                style={{
                    position: 'fixed',
                    top: 0,
                    bottom: 0,
                    left: 0,
                    right: 0,
                    margin: '0 auto',
                    maxWidth: 430,
                    zIndex: 0,
                    pointerEvents: 'none',
                    backgroundColor: STONE_50,
                    backgroundImage: "url('/images/wallpaper-floral.svg')",
                    backgroundRepeat: 'repeat',
                    backgroundSize: 'clamp(240px, 72vw, 320px) auto',
                    backgroundPosition: 'center top',
                }}
            />

            <div style={{ position: 'relative', zIndex: 1 }}>
                {children}
                {showBottomNav && <BottomNav activeTab={activeTab} />}
            </div>

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
