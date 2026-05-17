import { router } from '@inertiajs/react';
import { formatRupiah } from '@/helpers';

const REMAINING_MAX = 3;

export default function QrisFeedback({ order }) {
    const {
        qris_status,
        resubmit_count = 0,
        rejection_note,
        order_code,
        total_amount,
    } = order;

    const remaining = Math.max(0, REMAINING_MAX - resubmit_count);

    // ── proof submitted – waiting for kasir review ──
    if (qris_status === 'proof_submitted') {
        return (
            <>
                <div className="w-20 h-20 rounded-full bg-secondary flex items-center justify-center text-4xl mb-5">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" className="animate-pulse text-primary">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2" opacity="0.3" />
                        <path d="M12 6v6l4 2" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                    </svg>
                </div>
                <h1 className="text-[22px] font-bold text-foreground mb-2 text-center">
                    Menunggu Verifikasi
                </h1>
                <p className="text-sm text-muted-foreground text-center leading-relaxed mb-6">
                    Bukti pembayaran QRIS sedang diverifikasi kasir.
                    <br />
                    Mohon tunggu beberapa saat.
                </p>
            </>
        );
    }

    // ── resubmit requested – kasir asked for re-upload ──
    if (qris_status === 'resubmit_requested') {
        return (
            <>
                <div className="w-20 h-20 rounded-full bg-destructive/10 flex items-center justify-center text-4xl mb-5">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#DC3545" strokeWidth="2" strokeLinecap="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                </div>
                <h1 className="text-[22px] font-bold text-destructive mb-2 text-center">
                    Bukti Perlu Diunggah Ulang
                </h1>

                {rejection_note && (
                    <div className="bg-destructive/10 border border-destructive/30 rounded-[12px] p-[14px] w-full mb-4">
                        <div className="text-[13px] text-destructive font-semibold mb-1">Alasan:</div>
                        <div className="text-[13px] text-muted-foreground">{rejection_note}</div>
                    </div>
                )}

                <div className="text-sm font-semibold text-primary mb-5 text-center">
                    Sisa kesempatan: {remaining}x
                </div>

                <p className="text-sm text-muted-foreground text-center mb-6">
                    Silakan upload ulang bukti pembayaran yang valid.
                </p>

                <button
                    onClick={() => router.visit(`/customer/payment/${order_code}/qris`)}
                    className="w-full h-[50px] bg-primary text-primary-foreground border-none rounded-[16px] text-[15px] font-bold cursor-pointer"
                >
                    Unggah Ulang Bukti
                </button>
            </>
        );
    }

    // ── accepted – payment confirmed by kasir ──
    if (qris_status === 'accepted') {
        return (
            <>
                <div className="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center text-4xl mb-5">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#28A745" strokeWidth="2.5" strokeLinecap="round">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="16 8 10 16 7 12" />
                    </svg>
                </div>
                <h1 className="text-[22px] font-bold text-foreground mb-2 text-center">
                    Pembayaran Dikonfirmasi!
                </h1>
                <p className="text-sm text-muted-foreground text-center mb-2">
                    #{order_code} &middot; {formatRupiah(total_amount)}
                </p>
                <p className="text-sm text-muted-foreground text-center mb-6">
                    Pesanan Anda sedang diproses oleh kasir.
                </p>
            </>
        );
    }

    // ── rejected – final rejection (no resubmit) ──
    if (qris_status === 'rejected') {
        return (
            <>
                <div className="w-20 h-20 rounded-full bg-destructive/10 flex items-center justify-center text-4xl mb-5">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#DC3545" strokeWidth="2" strokeLinecap="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                </div>
                <h1 className="text-[22px] font-bold text-destructive mb-2 text-center">
                    Bukti Pembayaran Ditolak
                </h1>

                {rejection_note && (
                    <div className="bg-destructive/10 border border-destructive/30 rounded-[12px] p-[14px] w-full mb-4">
                        <div className="text-[13px] text-destructive font-semibold mb-1">Alasan:</div>
                        <div className="text-[13px] text-muted-foreground">{rejection_note}</div>
                    </div>
                )}

                <p className="text-sm text-muted-foreground text-center mb-6">
                    Silakan hubungi kasir untuk informasi lebih lanjut.
                </p>
            </>
        );
    }

    // ── fallback – no qris_status yet, show generic waiting ──
    return (
        <>
            <div className="w-20 h-20 rounded-full bg-secondary flex items-center justify-center text-4xl mb-5">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" className="animate-pulse text-primary">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="2" opacity="0.3" />
                    <path d="M12 6v6l4 2" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
                </svg>
            </div>
            <h1 className="text-[22px] font-bold text-foreground mb-2 text-center">
                Menunggu Pembayaran
            </h1>
            <p className="text-sm text-muted-foreground text-center leading-relaxed mb-6">
                Silakan lakukan pembayaran QRIS dan upload bukti pembayaran.
            </p>
            <button
                onClick={() => router.visit(`/customer/payment/${order_code}/qris`)}
                className="w-full h-[50px] bg-primary text-primary-foreground border-none rounded-[16px] text-[15px] font-bold cursor-pointer"
            >
                Upload Bukti Pembayaran
            </button>
        </>
    );
}
