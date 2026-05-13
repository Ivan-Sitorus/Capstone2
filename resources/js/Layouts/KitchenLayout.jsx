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
        <div
            data-interface="kitchen"
            className="min-h-screen w-full bg-background text-foreground font-sans text-lg flex flex-col"
        >
            <div className="flex justify-end shrink-0 px-4 py-3">
                <ThemeToggle />
            </div>

            <div className="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4 min-h-0">
                {children}
            </div>


            {toast && (
                <div
                    className={`fixed top-6 right-6 z-[9999] rounded-xl px-4 py-3 text-sm shadow-[var(--shadow-floating)] border ${
                        toast.type === 'success'
                            ? 'bg-green-50 dark:bg-green-950 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200'
                            : 'bg-red-50 dark:bg-red-950 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200'
                    }`}
                >
                    {toast.message}
                </div>
            )}
        </div>
    );
}
