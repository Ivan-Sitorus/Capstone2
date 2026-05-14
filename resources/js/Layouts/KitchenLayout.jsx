import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import FlashToast from '@/Components/Shared/FlashToast';

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
        <div
            data-interface="kitchen"
            className="min-h-screen w-full bg-background text-foreground font-sans text-lg flex flex-col"
        >
        
            <div className="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 p-4 min-h-0">
                {children}
            </div>


            {/* ── Toast ── */}
            <FlashToast toast={toast} onDismiss={() => setToast(null)} />
        </div>
    );
}
