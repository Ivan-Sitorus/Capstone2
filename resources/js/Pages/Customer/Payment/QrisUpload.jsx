import { useState } from 'react';
import { router, Head } from '@inertiajs/react';
import axios from 'axios';
import { ChevronLeft, Camera, Send, CheckCircle } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { formatRupiah } from '@/helpers';
import useCart from '@/Hooks/useCart';
import { STONE_50, WHITE, STONE_100, STONE_TINT, STONE_700, STONE_900, STONE_500, STONE_400, RED_50, RED_TINT, RED_DARK, RED_400, GREEN_400, SHADOW_CARD, FONT_SANS } from '@/theme';

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
            await axios.post(route('customer.payment.qris-proof', { order: order.id }), formData, {
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
                <title>Pembayaran QRIS - minePOS</title>
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
                <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
                <style>{`
                    html, body { background: ${STONE_50}; }
                    .mineposq-upload { transition: background 0.15s; }
                    .mineposq-upload:hover { background: rgba(239,237,233,0.60) !important; }
                    .mineposq-btn { transition: background 0.15s, transform 0.1s; }
                    .mineposq-btn:active { transform: scale(0.98); }
                `}</style>
            </Head>

            {/* Fixed full-height container: NO scroll */}
            <div style={{
                position: 'fixed', top: 0, left: '50%', transform: 'translateX(-50%)',
                width: '100%', maxWidth: 430,
                height: '100dvh',
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
                        onClick={() => router.visit(route('customer.payment.choose', { order: order.id }))}
                        style={{
                            width: 38, height: 38, borderRadius: 11,
                            background: 'rgba(255,255,255,0.90)',
                            backdropFilter: 'blur(6px)',
                            border: `1px solid rgba(231,229,228,0.50)`,
                            boxShadow: SHADOW_CARD,
                            cursor: 'pointer',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            flexShrink: 0,
                        }}
                    >
                        <ChevronLeft size={18} color={STONE_900} strokeWidth={2} />
                    </button>
                    <div>
                        <h1 style={{
                            fontSize: 17, fontWeight: 700, color: STONE_900,
                            fontFamily: FONT_SANS, letterSpacing: '-0.02em', margin: 0,
                        }}>
                            Pembayaran QRIS
                        </h1>
                        {order.order_code && (
                            <span style={{ fontSize: 11, fontWeight: 500, color: STONE_500, fontFamily: FONT_SANS }}>
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
                                background: RED_50, border: `1px solid ${RED_TINT}`,
                                borderRadius: 10, padding: '8px 12px', flexShrink: 0,
                            }}>
                                <p style={{ fontSize: 12, fontWeight: 700, color: RED_DARK, margin: '0 0 2px', fontFamily: FONT_SANS }}>
                                    Bukti Ditolak Kasir
                                </p>
                                <p style={{ fontSize: 12, color: STONE_500, margin: 0, fontFamily: FONT_SANS }}>{rejectedMessage}</p>
                            </div>
                        )}

                        {/* ── Main card: flex: 1, semua muat ── */}
                        <div style={{
                            flex: 1,
                            margin: '0 20px',
                            background: 'rgba(255,255,255,0.90)',
                            backdropFilter: 'blur(8px)',
                            borderRadius: 16,
                            border: `1px solid rgba(231,229,228,0.30)`,
                            boxShadow: SHADOW_CARD,
                            padding: '16px 20px',
                            display: 'flex', flexDirection: 'column',
                            overflow: 'hidden',
                            minHeight: 0,
                        }}>
                            {/* QR image: flex: 1, tumbuh proporsional */}
                            <div style={{
                                flex: 1,
                                display: 'flex', justifyContent: 'center', alignItems: 'center',
                                minHeight: 0,
                                marginBottom: 10,
                            }}>
                                <div style={{
                                    background: WHITE,
                                    padding: 6, borderRadius: 14,
                                    border: `1px solid rgba(231,229,228,0.50)`,
                                    boxShadow: '0 2px 8px rgba(0,0,0,0.06)',
                                    overflow: 'hidden',
                                    maxWidth: 200, width: '100%',
                                    maxHeight: '100%',
                                }}>
                                    <img
                                        src={qrisImage} alt="QRIS minePOS"
                                        style={{ width: '100%', height: 'auto', borderRadius: 10, display: 'block' }}
                                        onError={e => { e.target.src = '/images/logo.jpg'; }}
                                    />
                                </div>
                            </div>

                            {/* Merchant + amount (kompak) */}
                            <div style={{ textAlign: 'center', flexShrink: 0, marginBottom: 12 }}>
                                <p style={{ fontSize: 12, fontWeight: 500, color: STONE_500, fontFamily: FONT_SANS, margin: '0 0 4px' }}>
                                    {qrisName || ''}
                                </p>
                                <p style={{ fontSize: 26, fontWeight: 700, color: STONE_900, fontFamily: FONT_SANS, letterSpacing: '-0.03em', margin: 0 }}>
                                    {formatRupiah(totalAmount)}
                                </p>
                                <p style={{ fontSize: 10, color: STONE_400, fontFamily: FONT_SANS, textTransform: 'uppercase', letterSpacing: '0.08em', marginTop: 6 }}>
                                    Scan QR dengan aplikasi dompet digital
                                </p>
                            </div>

                            {/* Divider */}
                            <div style={{ height: 1, background: STONE_100, flexShrink: 0, marginBottom: 12 }} />

                            {/* Upload section (kompak) */}
                            <div style={{ flexShrink: 0 }}>
                                <label style={{ fontSize: 13, fontWeight: 600, color: STONE_900, fontFamily: FONT_SANS, display: 'block', marginBottom: 8 }}>
                                    Upload Bukti Pembayaran <span style={{ color: RED_400 }}>*</span>
                                </label>

                                <label htmlFor="proof-upload" style={{ cursor: 'pointer', display: 'block' }}>
                                    <div className="mineposq-upload" style={{
                                        border: `2px dashed ${file ? STONE_700 : STONE_TINT}`,
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
                                                    background: WHITE,
                                                    boxShadow: '0 2px 6px rgba(0,0,0,0.07)',
                                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                    flexShrink: 0,
                                                }}>
                                                    <Camera size={18} color={STONE_500} strokeWidth={1.5} />
                                                </div>
                                                <div>
                                                    <p style={{ fontSize: 13, fontWeight: 700, color: STONE_900, fontFamily: FONT_SANS, margin: 0 }}>
                                                        Pilih atau Foto Bukti Bayar
                                                    </p>
                                                    <p style={{ fontSize: 11, color: STONE_500, fontFamily: FONT_SANS, margin: 0 }}>
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
                                    <p style={{ color: RED_DARK, fontSize: 12, fontFamily: FONT_SANS, margin: '6px 0 0' }}>
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
                                className="mineposq-btn"
                                style={{
                                    width: '100%', padding: '14px 0',
                                    background: !file || uploading ? STONE_TINT : STONE_700,
                                    color: !file || uploading ? STONE_500 : WHITE,
                                    border: 'none', borderRadius: 12,
                                    fontSize: 15, fontWeight: 700,
                                    cursor: !file || uploading ? 'not-allowed' : 'pointer',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                                    fontFamily: FONT_SANS,
                                    boxShadow: !file || uploading ? 'none' : '0 4px 16px rgba(68,64,60,0.30)',
                                }}
                            >
                                <Send size={17} />
                                {uploading ? 'Mengupload...' : 'Kirim Bukti Pembayaran'}
                            </button>
                        </div>
                    </>

                ) : (
                    /* ── Success state: centered ── */
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
                            <CheckCircle size={36} color={GREEN_400} strokeWidth={1.75} />
                        </div>
                        <div>
                            <h2 style={{ fontSize: 20, fontWeight: 700, color: STONE_900, fontFamily: FONT_SANS, margin: '0 0 8px' }}>
                                Bukti Dikirim!
                            </h2>
                            <p style={{ fontSize: 13, color: STONE_500, fontFamily: FONT_SANS, margin: 0, lineHeight: 1.6 }}>
                                Kasir sedang memverifikasi pembayaran Anda.<br/>Harap tunggu konfirmasi.
                            </p>
                        </div>
                        <div style={{
                            width: '100%', background: 'rgba(255,255,255,0.90)',
                            backdropFilter: 'blur(6px)',
                            borderRadius: 12, border: `1px solid ${STONE_TINT}`,
                            padding: '12px 16px', textAlign: 'left',
                        }}>
                            <span style={{ fontSize: 13, color: STONE_500, lineHeight: 1.5, fontFamily: FONT_SANS }}>
                                Pantau status di tab <strong style={{ color: STONE_700 }}>Riwayat</strong> untuk update dari kasir.
                            </span>
                        </div>
                        <button
                            onClick={() => router.visit(route('customer.history'))}
                            className="mineposq-btn"
                            style={{
                                width: '100%', padding: '14px 0',
                                background: STONE_700, color: WHITE,
                                border: 'none', borderRadius: 12,
                                fontSize: 15, fontWeight: 700, cursor: 'pointer',
                                fontFamily: FONT_SANS, boxShadow: '0 4px 16px rgba(68,64,60,0.30)',
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
