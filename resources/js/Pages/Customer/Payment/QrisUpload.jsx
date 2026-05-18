import { useState } from 'react';
import { router, Head } from '@inertiajs/react';
import axios from 'axios';
import { ChevronLeft, Camera, Send, CheckCircle } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { formatRupiah } from '@/helpers';
import useCart from '@/Hooks/useCart';

const F = '"Inter", system-ui, sans-serif';
const C = {
    bg:         '#F7F5F2',
    surface:    '#FFFFFF',
    alt:        '#EFEDE9',
    border:     '#E7E5E4',
    accent:     '#44403C',
    textHead:   '#1C1917',
    textSecond: '#78716C',
    textMuted:  '#A8A29E',
    shadow:     '0 4px 20px -2px rgba(0,0,0,0.05)',
};

export default function QrisUpload({ order, qrisImage, qrisName, totalAmount, rejectedMessage }) {
    const [file,      setFile]      = useState(null);
    const [preview,   setPreview]   = useState(null);
    const [uploading, setUploading] = useState(false);
    const [error,     setError]     = useState(rejectedMessage ?? '');
    const [uploaded,  setUploaded]  = useState(false);
    const { clearCart } = useCart();

    function handleFileChange(e) {
        const f = e.target.files[0];
        if (!f) return;
        if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(f.type)) {
            setError('File harus berupa gambar (JPG atau PNG)');
            return;
        }
        if (f.size > 5 * 1024 * 1024) {
            setError('Ukuran file maksimal 5MB');
            return;
        }
        setError('');
        setFile(f);
        const reader = new FileReader();
        reader.onload = ev => setPreview(ev.target.result);
        reader.readAsDataURL(f);
    }

    async function handleUpload() {
        if (!file || uploading) return;
        setUploading(true);
        setError('');
        const formData = new FormData();
        formData.append('proof', file);
        try {
            await axios.post(`/api/order/${order.id}/qris-proof`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            clearCart();
            setUploaded(true);
        } catch (err) {
            setError(err.response?.data?.message ?? 'Upload gagal. Coba lagi.');
        } finally {
            setUploading(false);
        }
    }

    return (
        <CustomerLayout activeTab="cart">
            <Head>
                <title>Pembayaran QRIS — W9 Cafe</title>
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
                <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
                <style>{`
                    html, body { background: #F7F5F2; }
                    .w9q-upload { transition: background 0.15s; }
                    .w9q-upload:hover { background: rgba(239,237,233,0.60) !important; }
                    .w9q-btn { transition: background 0.15s, transform 0.1s; }
                    .w9q-btn:active { transform: scale(0.98); }
                `}</style>
            </Head>

            {/* Wallpaper */}
            <div style={{
                position: 'fixed', top: 0, left: '50%', transform: 'translateX(-50%)',
                width: '100%', maxWidth: 430, height: '100vh',
                zIndex: 0, pointerEvents: 'none', overflow: 'hidden', background: C.bg,
            }}>
                <img src="/images/wallpaper-menu.jpg" alt=""
                    style={{ width: '100%', height: '100%', objectFit: 'cover', objectPosition: 'center top' }}
                />
            </div>

            {/* Fixed full-height container — NO scroll */}
            <div style={{
                position: 'fixed', top: 0, left: '50%', transform: 'translateX(-50%)',
                width: '100%', maxWidth: 430,
                height: '100vh',
                display: 'flex', flexDirection: 'column',
                zIndex: 1,
                paddingBottom: 64, /* ruang BottomNav */
                boxSizing: 'border-box',
            }}>

                {/* ── Header ── */}
                <header style={{
                    padding: '28px 20px 12px',
                    display: 'flex', alignItems: 'center', gap: 14,
                    flexShrink: 0,
                }}>
                    <button
                        onClick={() => router.visit(`/customer/payment/${order.id}/choose`)}
                        style={{
                            width: 38, height: 38, borderRadius: 11,
                            background: 'rgba(255,255,255,0.90)',
                            backdropFilter: 'blur(6px)',
                            border: `1px solid rgba(231,229,228,0.50)`,
                            boxShadow: C.shadow,
                            cursor: 'pointer',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            flexShrink: 0,
                        }}
                    >
                        <ChevronLeft size={18} color={C.textHead} strokeWidth={2} />
                    </button>
                    <div>
                        <h1 style={{
                            fontSize: 17, fontWeight: 700, color: C.textHead,
                            fontFamily: F, letterSpacing: '-0.02em', margin: 0,
                        }}>
                            Pembayaran QRIS
                        </h1>
                        {order.order_code && (
                            <span style={{ fontSize: 11, fontWeight: 500, color: C.textSecond, fontFamily: F }}>
                                #{order.order_code}
                            </span>
                        )}
                    </div>
                </header>

                {!uploaded ? (
                    <>
                        {/* ── Rejection banner (compact) ── */}
                        {rejectedMessage && (
                            <div style={{
                                margin: '0 20px 8px',
                                background: '#FEF2F2', border: '1px solid #FECACA',
                                borderRadius: 10, padding: '8px 12px', flexShrink: 0,
                            }}>
                                <p style={{ fontSize: 12, fontWeight: 700, color: '#DC2626', margin: '0 0 2px', fontFamily: F }}>
                                    Bukti Ditolak Kasir
                                </p>
                                <p style={{ fontSize: 12, color: C.textSecond, margin: 0, fontFamily: F }}>{rejectedMessage}</p>
                            </div>
                        )}

                        {/* ── Main card — flex: 1, semua muat ── */}
                        <div style={{
                            flex: 1,
                            margin: '0 20px',
                            background: 'rgba(255,255,255,0.90)',
                            backdropFilter: 'blur(8px)',
                            borderRadius: 16,
                            border: `1px solid rgba(231,229,228,0.30)`,
                            boxShadow: C.shadow,
                            padding: '16px 20px',
                            display: 'flex', flexDirection: 'column',
                            overflow: 'hidden',
                            minHeight: 0,
                        }}>
                            {/* QR image — flex: 1, tumbuh proporsional */}
                            <div style={{
                                flex: 1,
                                display: 'flex', justifyContent: 'center', alignItems: 'center',
                                minHeight: 0,
                                marginBottom: 10,
                            }}>
                                <div style={{
                                    background: C.surface,
                                    padding: 6, borderRadius: 14,
                                    border: `1px solid rgba(231,229,228,0.50)`,
                                    boxShadow: '0 2px 8px rgba(0,0,0,0.06)',
                                    overflow: 'hidden',
                                    maxWidth: 200, width: '100%',
                                    maxHeight: '100%',
                                }}>
                                    <img
                                        src={qrisImage} alt="QRIS W9 Cafe"
                                        style={{ width: '100%', height: 'auto', borderRadius: 10, display: 'block' }}
                                        onError={e => { e.target.src = '/images/logo.jpg'; }}
                                    />
                                </div>
                            </div>

                            {/* Merchant + amount (kompak) */}
                            <div style={{ textAlign: 'center', flexShrink: 0, marginBottom: 12 }}>
                                <p style={{ fontSize: 12, fontWeight: 500, color: C.textSecond, fontFamily: F, margin: '0 0 4px' }}>
                                    {qrisName || 'W9 Cafe STIE Totalwin'}
                                </p>
                                <p style={{ fontSize: 26, fontWeight: 700, color: C.textHead, fontFamily: F, letterSpacing: '-0.03em', margin: 0 }}>
                                    {formatRupiah(totalAmount)}
                                </p>
                                <p style={{ fontSize: 10, color: C.textMuted, fontFamily: F, textTransform: 'uppercase', letterSpacing: '0.08em', marginTop: 6 }}>
                                    Scan QR dengan aplikasi dompet digital
                                </p>
                            </div>

                            {/* Divider */}
                            <div style={{ height: 1, background: C.alt, flexShrink: 0, marginBottom: 12 }} />

                            {/* Upload section (kompak) */}
                            <div style={{ flexShrink: 0 }}>
                                <label style={{ fontSize: 13, fontWeight: 600, color: C.textHead, fontFamily: F, display: 'block', marginBottom: 8 }}>
                                    Upload Bukti Pembayaran <span style={{ color: '#F87171' }}>*</span>
                                </label>

                                <label htmlFor="proof-upload" style={{ cursor: 'pointer', display: 'block' }}>
                                    <div className="w9q-upload" style={{
                                        border: `2px dashed ${file ? C.accent : C.border}`,
                                        background: file ? 'rgba(239,237,233,0.40)' : 'rgba(239,237,233,0.30)',
                                        borderRadius: 12,
                                        padding: preview ? 0 : '14px 16px',
                                        display: 'flex', flexDirection: preview ? 'column' : 'row',
                                        alignItems: 'center', justifyContent: 'center',
                                        gap: 10, overflow: 'hidden',
                                    }}>
                                        {preview ? (
                                            <img src={preview} alt="preview"
                                                style={{ width: '100%', maxHeight: 80, objectFit: 'contain', borderRadius: 10, display: 'block' }}
                                            />
                                        ) : (
                                            <>
                                                <div style={{
                                                    width: 36, height: 36, borderRadius: '50%',
                                                    background: C.surface,
                                                    boxShadow: '0 2px 6px rgba(0,0,0,0.07)',
                                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                    flexShrink: 0,
                                                }}>
                                                    <Camera size={18} color={C.textSecond} strokeWidth={1.5} />
                                                </div>
                                                <div>
                                                    <p style={{ fontSize: 13, fontWeight: 700, color: C.textHead, fontFamily: F, margin: 0 }}>
                                                        Pilih atau Foto Bukti Bayar
                                                    </p>
                                                    <p style={{ fontSize: 11, color: C.textSecond, fontFamily: F, margin: 0 }}>
                                                        JPG, PNG, max 5MB
                                                    </p>
                                                </div>
                                            </>
                                        )}
                                    </div>
                                    <input id="proof-upload" type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/webp"
                                        style={{ display: 'none' }}
                                        onChange={handleFileChange}
                                        capture="environment"
                                    />
                                </label>

                                {error && (
                                    <p style={{ color: '#DC2626', fontSize: 12, fontFamily: F, margin: '6px 0 0' }}>
                                        {error}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* ── Submit button ── */}
                        <div style={{ padding: '12px 20px 8px', flexShrink: 0 }}>
                            <button
                                onClick={handleUpload}
                                disabled={!file || uploading}
                                className="w9q-btn"
                                style={{
                                    width: '100%', padding: '14px 0',
                                    background: !file || uploading ? C.border : C.accent,
                                    color: !file || uploading ? C.textSecond : C.surface,
                                    border: 'none', borderRadius: 12,
                                    fontSize: 15, fontWeight: 700,
                                    cursor: !file || uploading ? 'not-allowed' : 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                                    fontFamily: F,
                                    boxShadow: !file || uploading ? 'none' : '0 4px 16px rgba(68,64,60,0.30)',
                                }}
                            >
                                <Send size={17} />
                                {uploading ? 'Mengupload...' : 'Kirim Bukti Pembayaran'}
                            </button>
                        </div>
                    </>

                ) : (
                    /* ── Success state — centered ── */
                    <div style={{
                        flex: 1,
                        display: 'flex', flexDirection: 'column',
                        alignItems: 'center', justifyContent: 'center',
                        gap: 16, padding: '0 24px', textAlign: 'center',
                    }}>
                        <div style={{
                            width: 72, height: 72, borderRadius: 18,
                            background: 'rgba(74,222,128,0.12)',
                            border: '1px solid rgba(74,222,128,0.25)',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                        }}>
                            <CheckCircle size={36} color="#4ADE80" strokeWidth={1.75} />
                        </div>
                        <div>
                            <h2 style={{ fontSize: 20, fontWeight: 700, color: C.textHead, fontFamily: F, margin: '0 0 8px' }}>
                                Bukti Dikirim!
                            </h2>
                            <p style={{ fontSize: 13, color: C.textSecond, fontFamily: F, margin: 0, lineHeight: 1.6 }}>
                                Kasir sedang memverifikasi pembayaran Anda.<br/>Harap tunggu konfirmasi.
                            </p>
                        </div>
                        <div style={{
                            width: '100%', background: 'rgba(255,255,255,0.90)',
                            backdropFilter: 'blur(6px)',
                            borderRadius: 12, border: `1px solid ${C.border}`,
                            padding: '12px 16px', textAlign: 'left',
                        }}>
                            <span style={{ fontSize: 13, color: C.textSecond, lineHeight: 1.5, fontFamily: F }}>
                                Pantau status di tab <strong style={{ color: C.accent }}>Riwayat</strong> untuk update dari kasir.
                            </span>
                        </div>
                        <button
                            onClick={() => router.visit('/customer/riwayat')}
                            className="w9q-btn"
                            style={{
                                width: '100%', padding: '14px 0',
                                background: C.accent, color: C.surface,
                                border: 'none', borderRadius: 12,
                                fontSize: 15, fontWeight: 700, cursor: 'pointer',
                                fontFamily: F, boxShadow: '0 4px 16px rgba(68,64,60,0.30)',
                            }}
                        >
                            Cek Status Pesanan
                        </button>
                    </div>
                )}

            </div>
        </CustomerLayout>
    );
}
