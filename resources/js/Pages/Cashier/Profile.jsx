import { useState } from 'react';
import { router, Head } from '@inertiajs/react';
import { User, LogOut } from 'lucide-react';
import CashierLayout from '@/Layouts/CashierLayout';
import { formatDate } from '@/helpers';
import { SLATE_50, WHITE, SLATE_200, SLATE_900, SLATE_500, BLUE, BLUE_50, RED_700, RED_DARK, SLATE_100 } from '@/theme';

export default function Profil({ user }) {
    const [logoutHover, setLogoutHover] = useState(false);

    function handleLogout() {
        router.post(route('logout'));
    }

    const roleLabel = {
        cashier: 'Kasir',
        admin:   'Admin',
        customer:'Pelanggan',
    }[user.role] ?? user.role;

    const fields = [
        { label: 'Nama Lengkap',    value: user.name },
        { label: 'Email',           value: user.email },
        { label: 'Peran / Role',    value: roleLabel },
        { label: 'Terdaftar Sejak', value: formatDate(user.created_at) },
    ];

    return (
        <><Head title="Profil Saya | W9 Cafe" /><CashierLayout title="Profil Saya" fullscreen>
            <div style={{ flex: 1, overflowY: 'auto', padding: 32, background: SLATE_50 }}>
            <div style={{ background: WHITE, borderRadius: 12, padding: 24, border: `1px solid ${SLATE_200}`, boxShadow: '0 2px 8px rgba(15,23,42,0.03)' }}>

            {/* ── Page header ── */}
            <div style={{ marginBottom: 28 }}>
                <h1 style={{
                    fontSize: 26, fontWeight: 700, color: SLATE_900,
                    margin: '0 0 4px', letterSpacing: '-0.5px',
                }}>
                    Profil Saya
                </h1>
                <p style={{ fontSize: 14, color: SLATE_500, margin: 0 }}>
                    Kelola informasi akun Anda
                </p>
            </div>

            {/* ── 2-column layout ── */}
            <div style={{ display: 'flex', gap: 24, alignItems: 'flex-start' }}>

                {/* ── LEFT: Avatar card ── */}
                <div style={{
                    width: 320, flexShrink: 0,
                    background: WHITE, borderRadius: 16,
                    border: `1px solid ${SLATE_200}`,
                    boxShadow: '0 4px 14px rgba(15,23,42,0.06)',
                    padding: 32,
                    display: 'flex', flexDirection: 'column',
                    alignItems: 'center', gap: 20,
                }}>
                    {/* Avatar circle */}
                    <div style={{
                        width: 96, height: 96, borderRadius: '50%',
                        background: BLUE,
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        boxShadow: '0 4px 16px rgba(59,111,212,0.25)',
                        flexShrink: 0,
                    }}>
                        <User size={44} color={WHITE} />
                    </div>

                    {/* Name */}
                    <div style={{ textAlign: 'center' }}>
                        <div style={{
                            fontSize: 20, fontWeight: 700, color: SLATE_900, marginBottom: 8,
                        }}>
                            {user.name}
                        </div>

                        {/* Role badge */}
                        <span style={{
                            display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
                            height: 28, padding: '0 12px', borderRadius: 100,
                            background: BLUE_50,
                            fontSize: 12, fontWeight: 600, color: BLUE,
                        }}>
                            {roleLabel}
                        </span>
                    </div>

                    {/* Logout button */}
                    <button
                        onClick={handleLogout}
                        onMouseEnter={() => setLogoutHover(true)}
                        onMouseLeave={() => setLogoutHover(false)}
                        style={{
                            width: '100%', height: 44,
                            background: logoutHover ? RED_700 : RED_DARK,
                            color: WHITE, border: 'none', borderRadius: 8,
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            gap: 8, fontSize: 14, fontWeight: 600, cursor: 'pointer',
                            boxShadow: '0 2px 8px rgba(220,38,38,0.25)',
                            transition: 'background 0.15s',
                        }}
                    >
                        <LogOut size={16} />
                        Keluar dari Akun
                    </button>
                </div>

                {/* ── RIGHT: Info card ── */}
                <div style={{
                    flex: 1, minWidth: 0,
                    background: WHITE, borderRadius: 16,
                    border: `1px solid ${SLATE_200}`,
                    boxShadow: '0 4px 14px rgba(15,23,42,0.06)',
                    overflow: 'hidden',
                }}>
                    {/* Card title */}
                    <div style={{
                        padding: '16px 24px',
                        background: SLATE_100,
                        borderBottom: `1px solid ${SLATE_200}`,
                    }}>
                        <span style={{ fontSize: 16, fontWeight: 600, color: SLATE_900 }}>
                            Informasi Akun
                        </span>
                    </div>

                    {/* Fields */}
                    <div style={{
                        padding: 24,
                        display: 'flex', flexDirection: 'column', gap: 20,
                    }}>
                        {fields.map(field => (
                            <div key={field.label} style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                                <label style={{
                                    fontSize: 13, fontWeight: 500, color: SLATE_500, display: 'block',
                                }}>
                                    {field.label}
                                </label>
                                <div style={{
                                    height: 44, borderRadius: 8,
                                    background: SLATE_100, border: `1px solid ${SLATE_200}`,
                                    padding: '0 14px',
                                    display: 'flex', alignItems: 'center',
                                    fontSize: 14, color: SLATE_900,
                                }}>
                                    {field.value}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

            </div>
            </div>
            </div>
        </CashierLayout></>
    );
}
