import { useEffect } from 'react';
import { router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { formatRupiah } from '@/helpers';

export default function QrisStatus({ order }) {
    const isRejected = order.status === 'pending' && !!order.rejection_note;
    const isDone     = order.status === 'selesai';

    useEffect(() => {
        if (isDone || isRejected) return;
        const id = setInterval(() => router.reload({ only: ['order'] }), 5000);
        return () => clearInterval(id);
    }, [order.status, order.rejection_note]);

    const isWaiting   = order.status === 'pending' && !order.rejection_note;
    const isConfirmed = order.status === 'diproses';

    return (
        <CustomerLayout>
            <div className="px-6 py-6 mx-auto w-full max-w-[430px] min-h-screen bg-background flex flex-col items-center justify-center">

                {isWaiting && (
                    <>
                        <div className="w-20 h-20 rounded-full bg-secondary flex items-center justify-center text-4xl mb-5">⏳</div>
                        <h1 className="text-[22px] font-bold text-foreground mb-2 text-center">
                            Menunggu Verifikasi
                        </h1>
                        <p className="text-sm text-muted-foreground text-center leading-relaxed mb-6">
                            Bukti pembayaran QRIS sedang diverifikasi kasir.<br/>
                            Mohon tunggu beberapa saat.
                        </p>
                        <p className="text-xs text-muted-foreground/50">Halaman ini otomatis update setiap 5 detik</p>
                    </>
                )}

                {isConfirmed && (
                    <>
                        <div className="text-[72px] mb-4">✅</div>
                        <h1 className="text-[22px] font-bold text-foreground mb-2 text-center">
                            Pembayaran Dikonfirmasi!
                        </h1>
                        <p className="text-sm text-muted-foreground text-center mb-6">
                            #{order.order_code} · {formatRupiah(order.total_amount)}
                        </p>
                        <div className="bg-card rounded-[16px] border border-border p-[18px] w-full">
                            {[
                                { label: 'Pembayaran Dikonfirmasi', done: true },
                                { label: 'Pesanan Sedang Diproses', done: true },
                            ].map((s, i) => (
                                <div key={i} className="flex items-center gap-3 mb-3">
                                    <div className={`w-[26px] h-[26px] rounded-full flex items-center justify-center text-[13px] font-bold shrink-0 text-white ${s.done ? 'bg-green-500' : 'bg-stone-100'}`}>
                                        {s.done ? '✓' : ''}
                                    </div>
                                    <span className={`text-[13px] ${s.done ? 'text-green-600 font-semibold' : 'text-gray-400 font-normal'}`}>
                                        {s.label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </>
                )}

                {isDone && (
                    <>
                        <div className="text-[72px] mb-4">🎉</div>
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
                )}

                {isRejected && (
                    <>
                        <div className="text-[72px] mb-4">✗</div>
                        <h1 className="text-[22px] font-bold text-destructive mb-2 text-center">
                            Bukti Pembayaran Ditolak
                        </h1>
                        {order.rejection_note && (
                            <div className="bg-destructive/10 border border-destructive/30 rounded-[12px] p-[14px] w-full mb-5">
                                <div className="text-[13px] text-destructive font-semibold mb-1">
                                    Alasan:
                                </div>
                                <div className="text-[13px] text-muted-foreground">{order.rejection_note}</div>
                            </div>
                        )}
                        <p className="text-sm text-muted-foreground text-center mb-6">
                            Silakan upload ulang bukti pembayaran yang valid.
                        </p>
                        <button
                            onClick={() => router.visit(`/customer/payment/${order.order_code}/qris`)}
                            className="w-full h-[50px] bg-primary text-primary-foreground border-none rounded-[16px] text-[15px] font-bold cursor-pointer"
                        >
                            Unggah Ulang Bukti
                        </button>
                    </>
                )}
            </div>
        </CustomerLayout>
    );
}
