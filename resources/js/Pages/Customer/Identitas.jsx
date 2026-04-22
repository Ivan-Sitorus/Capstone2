import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { User, Phone, Coffee, Check, MapPin } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';

const F = '"Plus Jakarta Sans", system-ui, sans-serif';

export default function Identitas({ table }) {
    const [name,        setName]        = useState('');
    const [phone,       setPhone]       = useState('');
    const [isMahasiswa, setIsMahasiswa] = useState(false);
    const [nameError,   setNameError]   = useState('');
    const [phoneError,  setPhoneError]  = useState('');

    useEffect(() => {
        if (!document.getElementById('pjs-font')) {
            const link = document.createElement('link');
            link.id   = 'pjs-font';
            link.rel  = 'stylesheet';
            link.href = 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap';
            document.head.appendChild(link);
        }
    }, []);

    /* Jika sudah pernah isi identitas untuk meja ini → skip ke menu */
    useEffect(() => {
        if (!table) return;
        try {
            const saved = sessionStorage.getItem('w9_customer');
            if (saved) {
                const data = JSON.parse(saved);
                if (data.name && data.phone && data.tableId === table.id) {
                    router.visit(`/customer/menu?table=${table.id}`);
                }
            }
        } catch (_) {}
    }, []);

    function handleLanjut() {
        let valid = true;

        if (!name.trim() || name.trim().length < 2) {
            setNameError('Nama minimal 2 karakter');
            valid = false;
        } else {
            setNameError('');
        }

        const phoneClean = phone.replace(/\D/g, '');
        if (!phoneClean || phoneClean.length < 10 || phoneClean.length > 15) {
            setPhoneError('Nomor telepon tidak valid (min 10 digit)');
            valid = false;
        } else {
            setPhoneError('');
        }

        if (!valid) return;

        sessionStorage.setItem('w9_customer', JSON.stringify({
            name:        name.trim(),
            phone:       phoneClean,
            isMahasiswa: isMahasiswa,
            tableId:     table?.id ?? null,
            tableNumber: table?.table_number ?? null,
        }));

        router.visit(table ? `/customer/menu?table=${table.id}` : '/customer/menu');
    }

    /* Jika tidak ada meja (akses langsung tanpa QR) */
    if (!table) {
        return (
            <CustomerLayout activeTab="menu" showBottomNav={false}>
                <div style={{
                    minHeight: '100vh',
                    background: '#F5F0EB',
                    display: 'flex', flexDirection: 'column',
                    alignItems: 'center', justifyContent: 'center',
                    padding: '40px 28px',
                    fontFamily: F,
                    textAlign: 'center',
                }}>
                    <div style={{
                        width: 72, height: 72, borderRadius: 18,
                        overflow: 'hidden',
                        boxShadow: '0 8px 24px rgba(0,0,0,0.18)',
                        marginBottom: 20,
                    }}>
                        <img src="/images/logo.jpg" alt="W9 Cafe"
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                            onError={e => { e.target.style.display = 'none'; e.target.parentElement.style.background = '#1B3A4B'; }} />
                    </div>
                    <h1 style={{ fontSize: 22, fontWeight: 800, color: '#1A1814', margin: '0 0 10px', fontFamily: F }}>
                        Scan QR Meja
                    </h1>
                    <p style={{ fontSize: 14, color: '#8C7B6B', lineHeight: 1.6, margin: '0 0 6px', fontFamily: F }}>
                        Silakan scan QR code yang ada di meja Anda untuk mulai memesan.
                    </p>
                    <p style={{ fontSize: 12, color: '#B5A898', fontFamily: F }}>
                        Hubungi kasir jika membutuhkan bantuan.
                    </p>
                </div>
            </CustomerLayout>
        );
    }

    return (
        <CustomerLayout activeTab="menu" showBottomNav={false}>
            <div style={{
                minHeight: '100vh',
                background: '#F5F0EB',
                fontFamily: F,
                display: 'flex',
                flexDirection: 'column',
            }}>

                {/* ── Hero Section ── */}
                <div style={{
                    background: 'radial-gradient(ellipse 140% 140% at 50% 30%, #2A4F5F 0%, #1B3A4B 100%)',
                    height: 240,
                    position: 'relative',
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    overflow: 'hidden',
                    flexShrink: 0,
                }}>
                    {/* Deco circles */}
                    <div style={{ position:'absolute', width:180, height:180, borderRadius:'50%', background:'#FFFFFF06', top:-40, left:-60 }}/>
                    <div style={{ position:'absolute', width:100, height:100, borderRadius:'50%', background:'#FFFFFF04', top:20, right:-10 }}/>

                    {/* Logo */}
                    <div style={{
                        width: 110, height: 110,
                        borderRadius: 22,
                        overflow: 'hidden',
                        boxShadow: '0 8px 30px rgba(0,0,0,0.40)',
                        zIndex: 1,
                    }}>
                        <img
                            src="/images/logo.jpg"
                            alt="W9 Cafe"
                            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                            onError={e => {
                                e.target.style.display = 'none';
                                e.target.parentElement.style.background = '#2A4F5F';
                                e.target.parentElement.style.display = 'flex';
                                e.target.parentElement.style.alignItems = 'center';
                                e.target.parentElement.style.justifyContent = 'center';
                                e.target.parentElement.innerHTML = '<span style="color:white;font-size:32px;font-style:italic;font-weight:700">w9</span>';
                            }}
                        />
                    </div>

                    {/* Curved bottom */}
                    <div style={{
                        position: 'absolute', bottom: -1, left: 0, right: 0,
                        height: 36,
                        background: '#F5F0EB',
                        borderRadius: '50% 50% 0 0 / 100% 100% 0 0',
                    }}/>
                </div>

                {/* ── Content ── */}
                <div style={{ padding: '24px 20px 40px', flex: 1 }}>

                    {/* Heading */}
                    <div style={{ textAlign: 'center', marginBottom: 24 }}>
                        <h1 style={{
                            fontSize: 26, fontWeight: 800, color: '#1A1814',
                            margin: '0 0 8px', fontFamily: F, letterSpacing: -0.5,
                        }}>
                            Selamat Datang!
                        </h1>
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 5 }}>
                            <MapPin size={13} color="#C4956A" />
                            <span style={{ fontSize: 13, color: '#8C7B6B', fontFamily: F }}>
                                Meja No. <strong style={{ color: '#1A1814' }}>{table.table_number}</strong>
                            </span>
                        </div>
                    </div>

                    {/* Form card */}
                    <div style={{
                        background: '#FFFFFF',
                        borderRadius: 20,
                        padding: '22px 18px',
                        boxShadow: '0 2px 12px rgba(26,24,20,0.07)',
                        display: 'flex', flexDirection: 'column', gap: 16,
                    }}>

                        {/* Nama */}
                        <div>
                            <label style={{ fontSize: 13, fontWeight: 700, color: '#1A1814', display: 'block', marginBottom: 8, fontFamily: F }}>
                                Nama
                            </label>
                            <div style={{ position: 'relative' }}>
                                <User size={18} color="#C4B5A5" style={{
                                    position: 'absolute', left: 14, top: '50%',
                                    transform: 'translateY(-50%)', pointerEvents: 'none',
                                }}/>
                                <input
                                    type="text"
                                    value={name}
                                    onChange={e => setName(e.target.value)}
                                    onKeyDown={e => e.key === 'Enter' && handleLanjut()}
                                    placeholder=""
                                    maxLength={100}
                                    style={{
                                        width: '100%', height: 50,
                                        border: `1.5px solid ${nameError ? '#DC3545' : '#EDE8E2'}`,
                                        borderRadius: 12,
                                        padding: '0 14px 0 44px',
                                        fontSize: 14, color: '#1A1814',
                                        background: '#FAFAF8', outline: 'none',
                                        boxSizing: 'border-box',
                                        fontFamily: F,
                                    }}
                                />
                            </div>
                            {nameError && <p style={{ color: '#DC3545', fontSize: 12, margin: '5px 0 0', fontFamily: F }}>{nameError}</p>}
                        </div>

                        {/* Telepon */}
                        <div>
                            <label style={{ fontSize: 13, fontWeight: 700, color: '#1A1814', display: 'block', marginBottom: 8, fontFamily: F }}>
                                Nomor Telepon
                            </label>
                            <div style={{ position: 'relative' }}>
                                <Phone size={18} color="#C4B5A5" style={{
                                    position: 'absolute', left: 14, top: '50%',
                                    transform: 'translateY(-50%)', pointerEvents: 'none',
                                }}/>
                                <input
                                    type="tel"
                                    value={phone}
                                    onChange={e => setPhone(e.target.value.replace(/[^0-9]/g, ''))}
                                    onKeyDown={e => e.key === 'Enter' && handleLanjut()}
                                    placeholder=""
                                    maxLength={15}
                                    style={{
                                        width: '100%', height: 50,
                                        border: `1.5px solid ${phoneError ? '#DC3545' : '#EDE8E2'}`,
                                        borderRadius: 12,
                                        padding: '0 14px 0 44px',
                                        fontSize: 14, color: '#1A1814',
                                        background: '#FAFAF8', outline: 'none',
                                        boxSizing: 'border-box',
                                        fontFamily: F,
                                    }}
                                />
                            </div>
                            {phoneError && <p style={{ color: '#DC3545', fontSize: 12, margin: '5px 0 0', fontFamily: F }}>{phoneError}</p>}
                        </div>

                        {/* Mahasiswa checkbox */}
                        <div
                            onClick={() => setIsMahasiswa(p => !p)}
                            style={{
                                background: isMahasiswa ? '#FFF5EF' : '#FFFFFF',
                                borderRadius: 12,
                                border: `1.5px solid ${isMahasiswa ? '#E8763A' : 'transparent'}`,
                                padding: '12px 14px',
                                display: 'flex', alignItems: 'center', gap: 12,
                                cursor: 'pointer',
                                transition: 'all 0.15s',
                            }}
                        >
                            <div style={{
                                width: 22, height: 22, borderRadius: 6, flexShrink: 0,
                                border: `2px solid ${isMahasiswa ? '#E8763A' : '#D6CFC8'}`,
                                background: isMahasiswa ? '#E8763A' : '#FFFFFF',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                transition: 'all 0.15s',
                            }}>
                                {isMahasiswa && <Check size={13} color="#FFFFFF" strokeWidth={2.5} />}
                            </div>
                            <div>
                                <div style={{ fontSize: 13, fontWeight: 600, color: '#1A1814', fontFamily: F }}>
                                    Saya adalah mahasiswa STIE Totalwin Semarang
                                </div>
                                <div style={{ fontSize: 11, color: '#A8998A', marginTop: 2, fontFamily: F }}>
                                    Opsional
                                </div>
                            </div>
                        </div>

                        {/* CTA Button */}
                        <button
                            onClick={handleLanjut}
                            style={{
                                width: '100%', height: 52,
                                background: '#E8763A', color: '#FFFFFF',
                                border: 'none', borderRadius: 50,
                                fontSize: 15, fontWeight: 700, cursor: 'pointer',
                                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
                                marginTop: 4,
                                boxShadow: '0 6px 20px rgba(232,118,58,0.35)',
                                fontFamily: F,
                                letterSpacing: -0.2,
                            }}
                        >
                            Masuk
                        </button>
                    </div>
                </div>
            </div>
        </CustomerLayout>
    );
}
