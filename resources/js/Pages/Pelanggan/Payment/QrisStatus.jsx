import { useEffect, useState, useCallback } from 'react';
import { router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import QrisFeedback from '@/Pages/Pelanggan/Payment/QrisFeedback';

function Toast({ toast, onDismiss }) {
    useEffect(() => {
        if (!toast) return;
        const t = setTimeout(onDismiss, 5000);
        return () => clearTimeout(t);
    }, [toast, onDismiss]);

    if (!toast) return null;

    const isSuccess = toast.type === 'success';

    return (
        <div
            className="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] w-[calc(100%-2rem)] max-w-md rounded-xl border px-4 py-3 flex items-center gap-2.5 text-sm shadow-floating animate-in slide-in-from-top-2"
            style={{
                background: isSuccess ? '#F0FDF4' : '#FEF2F2',
                borderColor: isSuccess ? '#BBF7D0' : '#FECACA',
                color: isSuccess ? '#166534' : '#991B1B',
            }}
        >
            {isSuccess ? (
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" className="shrink-0">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="16 8 10 16 7 12" />
                </svg>
            ) : (
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" className="shrink-0">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
            )}
            <span className="flex-1 font-medium">{toast.message}</span>
            <button
                onClick={onDismiss}
                className="shrink-0 w-5 h-5 rounded-full flex items-center justify-center border-none bg-transparent cursor-pointer opacity-60 hover:opacity-100 transition-opacity"
                type="button"
                aria-label="Tutup"
            >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>
    );
}

const DECISION_TOAST = {
    accepted:            { type: 'success', message: 'Pembayaran QRIS telah dikonfirmasi!' },
    rejected:            { type: 'error',   message: 'Bukti pembayaran ditolak.' },
    resubmit_requested:  { type: 'error',   message: 'Diminta upload ulang bukti pembayaran.' },
};

export default function QrisStatus({ order }) {
    const [showDone, setShowDone] = useState(order.status === 'selesai');
    const [toast, setToast] = useState(null);

    const dismissToast = useCallback(() => setToast(null), []);

    useEffect(() => {
        if (!order?.id) return;

        const channel = window.Echo.channel(`order.${order.id}`);
        channel.listen('.OrderQrisReviewed', (e) => {
            const t = DECISION_TOAST[e.decision];
            if (t) {
                let message = t.message;
                if ((e.decision === 'rejected' || e.decision === 'resubmit_requested') && e.reason) {
                    message += ` ${e.reason}`;
                }
                setToast({ type: t.type, message });
            }
            router.reload({ only: ['order'] });
        });

        return () => {
            window.Echo.leave(`order.${order.id}`);
        };
    }, [order?.id]);

    useEffect(() => {
        setShowDone(order.status === 'selesai');
    }, [order.status]);

    return (
        <CustomerLayout>
            <Toast toast={toast} onDismiss={dismissToast} />

            <div className="px-6 py-6 mx-auto w-full max-w-[430px] min-h-screen bg-background flex flex-col items-center justify-center">

                {showDone ? (
                    <>
                        <div className="w-20 h-20 rounded-full bg-secondary flex items-center justify-center text-4xl mb-5">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#28A745" strokeWidth="2" strokeLinecap="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="16 8 10 16 7 12" />
                            </svg>
                        </div>
                        <h1 className="text-[22px] font-bold text-foreground mb-2 text-center">
                            Pesanan Selesai!
                        </h1>
                        <p className="text-sm text-muted-foreground text-center mb-7">
                            Silakan ambil pesanan Anda di kasir.
                        </p>
                        <button
                            onClick={() => router.visit('/customer/menu')}
                            className="w-full h-[50px] bg-primary text-primary-foreground border-none rounded-[16px] text-[15px] font-bold cursor-pointer"
                        >
                            Pesan Lagi
                        </button>
                    </>
                ) : (
                    <QrisFeedback order={order} />
                )}

            </div>
        </CustomerLayout>
    );
}
