import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import ThemeToggle from '@/Components/Common/ThemeToggle';

export default function KitchenLayout({ children, title = 'Dapur' }) {
    const { flash } = usePage().props;
    const [toast, setToast] = useState(null);

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

    return (
        <div data-interface="kitchen" style={{
            minHeight: '100vh',
            background: '#1A2332',
            color: '#FFFFFF',
            fontFamily: '"Inter", system-ui, sans-serif',
        }}>
            <div style={{ display: 'flex', justifyContent: 'flex-end', padding: '8px 16px' }}>
                <ThemeToggle />
            </div>
            {children}

            {/* Toast */}
            {toast && (
                <div style={{
                    position: 'fixed',
                    top: 24,
                    right: 24,
                    zIndex: 9999,
                    background: toast.type === 'success' ? '#F0FDF4' : '#FEF2F2',
                    border: `1px solid ${toast.type === 'success' ? '#86EFAC' : '#FCA5A5'}`,
                    borderRadius: 10,
                    padding: '12px 16px',
                    fontSize: 14,
                    color: toast.type === 'success' ? '#15803D' : '#DC2626',
                    boxShadow: '0 4px 16px rgba(0,0,0,0.10)',
                }}>
                    {toast.message}
                </div>
            )}
        </div>
    );
}
