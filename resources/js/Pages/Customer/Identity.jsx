import { useState, useEffect } from 'react';
import { router, Head } from '@inertiajs/react';
import { User, Phone, Check, MapPin } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { STONE_50, WHITE, STONE_300, STONE_700, STONE_800, STONE_900, STONE_500, STONE_400, NAVY, NAVY_DEEP, DANGER, FONT_SANS } from '@/theme';

export default function Identitas({ table }) {
    const [name,        setName]        = useState('');
    const [phone,       setPhone]       = useState('');
    const [isStudent, setIsStudent] = useState(false);
    const [nameError,   setNameError]   = useState('');
    const [phoneError,  setPhoneError]  = useState('');

    /* Jika sudah pernah isi identitas untuk meja ini → skip ke menu */
    useEffect(() => {
        if (!table) return;
        try {
            const saved = sessionStorage.getItem('minepos_customer');
            if (saved) {
                const data = JSON.parse(saved);
                if (data.name && data.tableId === table.id) {
                    router.visit(route('customer.menu', { table: table.id }));
                }
            }
        } catch (_) {}
    }, []);

    function handleSubmit() {
        let valid = true;

        if (!name.trim() || name.trim().length < 2) {
            setNameError('Nama minimal 2 karakter');
            valid = false;
        } else {
            setNameError('');
        }

        const phoneClean = phone.replace(/\D/g, '');
        if (phoneClean && (phoneClean.length < 10 || phoneClean.length > 15)) {
            setPhoneError('Nomor telepon tidak valid (min 10 digit)');
            valid = false;
        } else {
            setPhoneError('');
        }

        if (!valid) return;

        sessionStorage.setItem('minepos_customer', JSON.stringify({
            name:        name.trim(),
            phone:       phoneClean,
            isMahasiswa: isStudent,
            tableId:     table?.id ?? null,
            tableNumber: table?.table_number ?? null,
        }));

        router.visit(table ? route('customer.menu', { table: table.id }) : route('customer.menu'));
    }

    /* ── No table state ── */
    if (!table) {
        return (
            <CustomerLayout activeTab="menu" showBottomNav={false}>
                <Head>
                    <title>minePOS</title>
                    <link rel="preconnect" href="https://fonts.googleapis.com" />
                    <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
                    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
                </Head>

                <div style={{
                    position: 'relative', zIndex: 1,
                    minHeight: '100dvh', display: 'flex', flexDirection: 'column',
                    alignItems: 'center', justifyContent: 'center',
                    padding: '40px 28px', fontFamily: FONT_SANS, textAlign: 'center',
                }}>
                    <div style={{
                        width: 96, height: 96, borderRadius: 20,
                        overflow: 'hidden', background: NAVY_DEEP,
                        boxShadow: '0 8px 24px rgba(0,0,0,0.25)', marginBottom: 24,
                    }}>
                        <img src="/images/logo.jpg" alt="minePOS"
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                            onError={e => { e.target.style.display = 'none'; }} />
                    </div>
                    <h1 style={{ fontSize: 22, fontWeight: 700, color: STONE_900, margin: '0 0 10px', fontFamily: FONT_SANS }}>
                        Scan QR Meja
                    </h1>
                    <p style={{ fontSize: 14, color: STONE_500, lineHeight: 1.6, margin: '0 0 6px', fontFamily: FONT_SANS }}>
                        Silakan scan QR code yang ada di meja Anda untuk mulai memesan.
                    </p>
                    <p style={{ fontSize: 12, color: STONE_400, fontFamily: FONT_SANS }}>
                        Hubungi kasir jika membutuhkan bantuan.
                    </p>
                </div>
            </CustomerLayout>
        );
    }

    /* ── Main form ── */
    return (
        <CustomerLayout activeTab="menu" showBottomNav={false}>
            <Head>
                <title>Selamat Datang - minePOS</title>
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
                <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
                <style>{`
                    html, body { background: ${STONE_50}; }
                    .mineposid-input { outline: none; transition: border-color 0.15s; }
                    .mineposid-input:focus { border-color: ${STONE_700} !important; }
                    .mineposid-btn:active { background: ${STONE_800} !important; }
                `}</style>
            </Head>

            {/* Page */}
            <div style={{
                position: 'relative', zIndex: 1,
                minHeight: '100dvh', display: 'flex', flexDirection: 'column',
                maxWidth: 430, margin: '0 auto',
            }}>

                {/* ── Branding header ── */}
                <header style={{
                    background: NAVY,
                    paddingTop: 48, paddingBottom: 32,
                    borderRadius: '0 0 36px 36px',
                    display: 'flex', flexDirection: 'column', alignItems: 'center',
                    flexShrink: 0,
                }}>
                    <div style={{
                        width: 96, height: 96, borderRadius: 20,
                        overflow: 'hidden', background: NAVY_DEEP,
                        boxShadow: '0 8px 30px rgba(0,0,0,0.35)',
                        border: '1px solid rgba(255,255,255,0.10)',
                    }}>
                        <img src="/images/logo.jpg" alt="minePOS"
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                            onError={e => { e.target.style.display = 'none'; }} />
                    </div>

                    {/* Divider dekoratif bawah header */}
                    <div style={{
                        display: 'flex', alignItems: 'center', gap: 6,
                        marginTop: 24,
                    }}>
                        <div style={{ width: 32, height: 1, background: 'rgba(255,255,255,0.20)' }} />
                        <div style={{ width: 5, height: 5, borderRadius: '50%', background: 'rgba(255,255,255,0.40)' }} />
                        <div style={{ width: 24, height: 2, borderRadius: 1, background: 'rgba(255,255,255,0.70)' }} />
                        <div style={{ width: 5, height: 5, borderRadius: '50%', background: 'rgba(255,255,255,0.40)' }} />
                        <div style={{ width: 32, height: 1, background: 'rgba(255,255,255,0.20)' }} />
                    </div>
                </header>

                {/* ── Main content ── */}
                <main style={{
                    flex: 1,
                    background: 'transparent',
                    padding: '32px 24px 48px',
                    overflowY: 'auto',
                }}>

                    {/* Welcome + table pill */}
                    <div style={{ textAlign: 'center', marginBottom: 32 }}>
                        <h1 style={{
                            fontSize: 32, fontWeight: 700, color: STONE_700,
                            textTransform: 'uppercase', letterSpacing: '-0.02em',
                            fontFamily: FONT_SANS, margin: '0 0 12px',
                        }}>
                            Selamat Datang!
                        </h1>
                        <div style={{
                            display: 'inline-flex', alignItems: 'center', gap: 6,
                            background: STONE_700, color: WHITE,
                            borderRadius: 999, padding: '6px 16px',
                            boxShadow: '0 2px 8px rgba(68,64,60,0.25)',
                        }}>
                            <MapPin size={12} color={WHITE} strokeWidth={2.5} />
                            <span style={{
                                fontSize: 11, fontWeight: 700,
                                letterSpacing: '0.10em', textTransform: 'uppercase',
                                fontFamily: FONT_SANS,
                            }}>
                                Meja No. {table.table_number}
                            </span>
                        </div>
                    </div>

                    {/* Form card */}
                    <section style={{
                        background: WHITE,
                        borderRadius: 12, padding: 24,
                        border: `1px solid ${STONE_300}`,
                        boxShadow: '0 4px 6px rgba(0,0,0,0.05), 0 2px 4px rgba(0,0,0,0.03)',
                        position: 'relative', overflow: 'hidden',
                        display: 'flex', flexDirection: 'column', gap: 20,
                    }}>
                        {/* Decorative corners */}
                        <div style={{
                            position: 'absolute', top: 0, right: 0,
                            width: 64, height: 64,
                            borderTop: `2px solid rgba(226,222,216,0.40)`,
                            borderRight: `2px solid rgba(226,222,216,0.40)`,
                            borderRadius: '0 12px 0 0',
                        }}/>
                        <div style={{
                            position: 'absolute', bottom: 0, left: 0,
                            width: 64, height: 64,
                            borderBottom: `2px solid rgba(226,222,216,0.40)`,
                            borderLeft: `2px solid rgba(226,222,216,0.40)`,
                            borderRadius: '0 0 0 12px',
                        }}/>

                        {/* ── Nama ── */}
                        <div>
                            <label style={{
                                display: 'block', fontSize: 13, fontWeight: 700,
                                color: STONE_900, marginBottom: 8, fontFamily: FONT_SANS,
                            }}>
                                Nama
                            </label>
                            <div style={{ position: 'relative' }}>
                                <User size={18} color={STONE_400} style={{
                                    position: 'absolute', left: 12, top: '50%',
                                    transform: 'translateY(-50%)', pointerEvents: 'none',
                                }} />
                                <input
                                    type="text"
                                    value={name}
                                    onChange={e => setName(e.target.value)}
                                    onKeyDown={e => e.key === 'Enter' && handleSubmit()}
                                    placeholder="Masukkan nama lengkap"
                                    maxLength={100}
                                    className="mineposid-input"
                                    style={{
                                        width: '100%', height: 48, boxSizing: 'border-box',
                                        border: `1px solid ${nameError ? DANGER : STONE_300}`,
                                        borderRadius: 8, paddingLeft: 40, paddingRight: 12,
                                        fontSize: 13, color: STONE_900,
                                        background: STONE_50, fontFamily: FONT_SANS,
                                    }}
                                />
                            </div>
                            {nameError && (
                                <p style={{ color: DANGER, fontSize: 12, margin: '5px 0 0', fontFamily: FONT_SANS }}>
                                    {nameError}
                                </p>
                            )}
                        </div>

                        {/* ── Nomor Telepon ── */}
                        <div>
                            <label style={{
                                display: 'block', fontSize: 13, fontWeight: 700,
                                color: STONE_900, marginBottom: 8, fontFamily: FONT_SANS,
                            }}>
                                Nomor Telepon (opsional)
                            </label>
                            <div style={{ position: 'relative' }}>
                                <Phone size={18} color={STONE_400} style={{
                                    position: 'absolute', left: 12, top: '50%',
                                    transform: 'translateY(-50%)', pointerEvents: 'none',
                                }} />
                                <input
                                    type="tel"
                                    value={phone}
                                    onChange={e => setPhone(e.target.value.replace(/[^0-9]/g, ''))}
                                    onKeyDown={e => e.key === 'Enter' && handleSubmit()}
                                    placeholder="0812 3456 7890"
                                    maxLength={15}
                                    className="mineposid-input"
                                    style={{
                                        width: '100%', height: 48, boxSizing: 'border-box',
                                        border: `1px solid ${phoneError ? DANGER : STONE_300}`,
                                        borderRadius: 8, paddingLeft: 40, paddingRight: 12,
                                        fontSize: 13, color: STONE_900,
                                        background: STONE_50, fontFamily: FONT_SANS,
                                    }}
                                />
                            </div>
                            {phoneError && (
                                <p style={{ color: DANGER, fontSize: 12, margin: '5px 0 0', fontFamily: FONT_SANS }}>
                                    {phoneError}
                                </p>
                            )}
                            <p style={{ color: STONE_400, fontSize: 11, margin: '5px 0 0', fontFamily: FONT_SANS }}>
                                Dipakai untuk kirim struk via WhatsApp & menampilkan riwayat pesanan Anda.
                            </p>
                        </div>

                        {/* ── Mahasiswa checkbox ── */}
                        <div
                            onClick={() => setIsStudent(p => !p)}
                            style={{ display: 'flex', alignItems: 'flex-start', gap: 12, cursor: 'pointer' }}
                        >
                            <div style={{
                                width: 20, height: 20, borderRadius: 4, flexShrink: 0, marginTop: 1,
                                border: `1.5px solid ${isStudent ? STONE_700 : STONE_300}`,
                                background: isStudent ? STONE_700 : WHITE,
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                transition: 'all 0.15s',
                            }}>
                                {isStudent && <Check size={12} color={WHITE} strokeWidth={2.5} />}
                            </div>
                            <div>
                                <p style={{ fontSize: 13, fontWeight: 600, color: STONE_900, margin: 0, fontFamily: FONT_SANS }}>
                                    Saya adalah mahasiswa STIE Totalwin Semarang
                                </p>
                                <p style={{ fontSize: 11, color: STONE_400, margin: '2px 0 0', fontFamily: FONT_SANS }}>
                                    Opsional
                                </p>
                            </div>
                        </div>

                        {/* ── CTA Button ── */}
                        <div style={{ paddingTop: 4 }}>
                            <button
                                onClick={handleSubmit}
                                className="mineposid-btn"
                                style={{
                                    width: '100%', height: 52,
                                    background: STONE_700, color: WHITE,
                                    border: 'none', borderRadius: 8,
                                    fontSize: 15, fontWeight: 700, cursor: 'pointer',
                                    fontFamily: FONT_SANS, transition: 'background 0.15s',
                                    boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
                                }}
                            >
                                Masuk
                            </button>
                        </div>
                    </section>
                </main>

            </div>
        </CustomerLayout>
    );
}
