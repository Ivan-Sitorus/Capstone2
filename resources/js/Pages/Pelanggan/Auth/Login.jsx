import { useForm } from '@inertiajs/react';
import { Head } from '@inertiajs/react';
import { User, Lock, GraduationCap, AlertCircle } from 'lucide-react';
import PelangganLayout from '@/Layouts/PelangganLayout';

const F = '"Inter", system-ui, sans-serif';

export default function CustomerLogin() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        nim: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/customer/login');
    }

    return (
        <PelangganLayout activeTab="menu" showBottomNav={false}>
            <Head>
                <title>Masuk — W9 Cafe</title>
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
                <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
                <style>{`
                    html, body { background: #FAFAFA; }
                    .w9l-input { outline: none; transition: border-color 0.15s; }
                    .w9l-input:focus { border-color: #44403C !important; }
                `}</style>
            </Head>

            <div style={{
                minHeight: '100vh',
                background: '#FAFAFA',
                display: 'flex', flexDirection: 'column',
                padding: '40px 24px 24px',
                fontFamily: F,
            }}>
                {/* Logo area */}
                <div style={{ textAlign: 'center', marginBottom: 28 }}>
                    <div style={{
                        width: 80, height: 80, borderRadius: 16,
                        background: '#1A2332',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        margin: '0 auto 12px',
                        boxShadow: '0 4px 16px rgba(26,35,50,0.25)',
                    }}>
                        <span style={{ color: '#FFFFFF', fontSize: 28, fontWeight: 700, fontStyle: 'italic' }}>w9</span>
                    </div>
                    <h1 style={{
                        fontSize: 22, fontWeight: 700, color: '#1A1A2E',
                        margin: 0, fontFamily: F,
                    }}>
                        W9 Cafe
                    </h1>
                    <p style={{ fontSize: 14, color: '#6C757D', margin: '4px 0 0', fontFamily: F }}>
                        Pemesanan Online
                    </p>
                </div>

                {/* Info box mahasiswa */}
                <div style={{
                    background: '#FFF0E8',
                    borderRadius: 12,
                    padding: '14px 16px',
                    marginBottom: 24,
                    display: 'flex', alignItems: 'flex-start', gap: 12,
                }}>
                    <GraduationCap size={20} color="#E8692A" style={{ flexShrink: 0, marginTop: 1 }} />
                    <div>
                        <p style={{
                            fontSize: 14, fontWeight: 600, color: '#E8692A',
                            margin: 0, fontFamily: F,
                        }}>
                            Login sebagai Mahasiswa
                        </p>
                        <p style={{
                            fontSize: 12, color: '#6C757D',
                            margin: '4px 0 0', fontFamily: F,
                        }}>
                            Dapatkan diskon 10% untuk semua menu!
                        </p>
                    </div>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 18 }}>
                    {/* Error box */}
                    {errors.name && (
                        <div style={{
                            background: '#FEF2F2',
                            border: '1px solid #FCA5A5',
                            borderRadius: 12,
                            padding: '12px 14px',
                            display: 'flex', alignItems: 'center', gap: 8,
                            fontSize: 13, color: '#DC2626',
                            fontFamily: F,
                        }}>
                            <AlertCircle size={16} style={{ flexShrink: 0 }} />
                            {errors.name}
                        </div>
                    )}

                    {/* Username (Nama Lengkap) */}
                    <div>
                        <label style={{
                            display: 'block', fontSize: 13, fontWeight: 600,
                            color: '#374151', marginBottom: 6, fontFamily: F,
                        }}>
                            Username (Nama Lengkap)
                        </label>
                        <div style={{ position: 'relative' }}>
                            <User size={18} color="#6C757D" style={{
                                position: 'absolute', left: 14, top: '50%',
                                transform: 'translateY(-50%)', pointerEvents: 'none',
                            }} />
                            <input
                                type="text"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                placeholder="Masukkan nama lengkap..."
                                className="w9l-input"
                                style={{
                                    width: '100%', height: 48, boxSizing: 'border-box',
                                    border: `1px solid ${errors.name ? '#DC3545' : '#E9ECEF'}`,
                                    borderRadius: 12, paddingLeft: 44, paddingRight: 14,
                                    fontSize: 14, color: '#1A1A2E',
                                    background: '#FFFFFF', fontFamily: F,
                                }}
                            />
                        </div>
                    </div>

                    {/* Password (NIM) */}
                    <div>
                        <label style={{
                            display: 'block', fontSize: 13, fontWeight: 600,
                            color: '#374151', marginBottom: 6, fontFamily: F,
                        }}>
                            Password (NIM)
                        </label>
                        <div style={{ position: 'relative' }}>
                            <Lock size={18} color="#6C757D" style={{
                                position: 'absolute', left: 14, top: '50%',
                                transform: 'translateY(-50%)', pointerEvents: 'none',
                            }} />
                            <input
                                type="password"
                                value={data.nim}
                                onChange={e => setData('nim', e.target.value)}
                                placeholder="Masukkan NIM..."
                                className="w9l-input"
                                style={{
                                    width: '100%', height: 48, boxSizing: 'border-box',
                                    border: `1px solid ${errors.nim ? '#DC3545' : '#E9ECEF'}`,
                                    borderRadius: 12, paddingLeft: 44, paddingRight: 14,
                                    fontSize: 14, color: '#1A1A2E',
                                    background: '#FFFFFF', fontFamily: F,
                                }}
                            />
                        </div>
                    </div>

                    {/* Submit */}
                    <button
                        type="submit"
                        disabled={processing}
                        style={{
                            width: '100%', height: 50,
                            background: processing ? '#D6D3D1' : '#E8692A',
                            color: '#FFFFFF', border: 'none', borderRadius: 8,
                            fontSize: 16, fontWeight: 600, cursor: processing ? 'not-allowed' : 'pointer',
                            fontFamily: F, marginTop: 6,
                            boxShadow: processing ? 'none' : '0 4px 12px rgba(232,105,42,0.30)',
                            transition: 'background 0.15s',
                        }}
                    >
                        {processing ? 'Memuat...' : '→ Masuk'}
                    </button>
                </form>

                {/* Cara Login info */}
                <div style={{ marginTop: 24, padding: '16px 0', borderTop: '1px solid #E9ECEF' }}>
                    <p style={{
                        fontSize: 12, color: '#6C757D', fontFamily: F, lineHeight: 1.8, margin: 0,
                    }}>
                        <strong style={{ color: '#374151' }}>Cara Login:</strong><br />
                        • Username: Gunakan nama lengkap Anda<br />
                        • Password: Gunakan NIM Anda<br />
                        <span style={{ color: '#E8692A' }}>
                            • Tunjukkan KTM pada Kasir untuk memverifikasi akun.
                        </span>
                    </p>
                </div>
            </div>
        </PelangganLayout>
    );
}
