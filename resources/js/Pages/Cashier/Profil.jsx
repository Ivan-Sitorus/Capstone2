import { router, Head } from '@inertiajs/react';
import { User, LogOut } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import CashierLayout from '@/Layouts/CashierLayout';
import { formatDate } from '@/helpers';

export default function Profil({ user }) {
    function handleLogout() {
        router.post('/logout');
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
            <div className="flex-1 overflow-y-auto p-8 bg-muted">
            <div className="bg-card rounded-xl p-6 border border-border shadow-sm">

            {/* ── Page header ── */}
            <div className="mb-7">
                <h1 className="text-3xl font-bold text-foreground m-0 mb-1 tracking-tight">
                    Profil Saya
                </h1>
                <p className="text-sm text-muted-foreground m-0">
                    Kelola informasi akun Anda
                </p>
            </div>

            {/* ── 2-column layout ── */}
            <div className="flex flex-col lg:flex-row gap-6 items-start">

                {/* ── LEFT: Avatar card ── */}
                <div className="w-full lg:w-80 shrink-0">
                    <Card className="shadow-md">
                        <CardContent className="flex flex-col items-center gap-5 py-8">
                            {/* Avatar circle */}
                            <div className="w-24 h-24 rounded-full bg-primary flex items-center justify-center shrink-0 shadow-[0_4px_16px_rgba(59,111,212,0.25)]">
                                <User size={44} className="text-primary-foreground" />
                            </div>

                            {/* Name */}
                            <div className="text-center">
                                <div className="text-xl font-bold text-foreground mb-2">
                                    {user.name}
                                </div>

                                {/* Role badge */}
                                <span className="inline-flex items-center justify-center h-7 px-3 rounded-full bg-muted text-xs font-semibold text-primary">
                                    {roleLabel}
                                </span>
                            </div>

                            {/* Tombol Keluar */}
                            <Button
                                onClick={handleLogout}
                                variant="destructive"
                                className="w-full h-11 flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(220,38,38,0.25)] hover:bg-[#B91C1C]"
                            >
                                <LogOut size={16} />
                                Keluar dari Akun
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                {/* ── RIGHT: Info card ── */}
                <div className="flex-1 min-w-0">
                    <Card className="shadow-md overflow-hidden">
                        <CardHeader className="bg-muted border-b border-border">
                            <CardTitle>Informasi Akun</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-5 p-6">
                            {fields.map(field => (
                                <div key={field.label} className="flex flex-col gap-1.5">
                                    <label className="text-sm font-medium block text-muted-foreground">
                                        {field.label}
                                    </label>
                                    <div className="h-11 rounded-lg bg-muted border border-border px-3.5 flex items-center text-sm text-foreground">
                                        {field.value}
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>

            </div>
            </div>
            </div>
        </CashierLayout></>
    );
}
