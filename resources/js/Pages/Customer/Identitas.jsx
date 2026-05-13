import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { User, Phone, Check, MapPin } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

export default function Identitas({ table }) {
    const [name,        setName]        = useState('');
    const [phone,       setPhone]       = useState('');
    const [isMahasiswa, setIsMahasiswa] = useState(false);
    const [phoneError,  setPhoneError]  = useState('');
    const [logoError,   setLogoError]   = useState(false);

    useEffect(() => {
        if (!table) return;
        try {
            const saved = sessionStorage.getItem('w9_customer');
            if (saved) {
                const data = JSON.parse(saved);
                if (data.name && data.tableId === table.id) {
                    router.visit(`/customer/menu?table=${table.id}`);
                }
            }
        } catch (_) {}
    }, []);

    function saveIdentity(nameVal, phoneVal, isMhs) {
        sessionStorage.setItem('w9_customer', JSON.stringify({
            name:        nameVal,
            phone:       phoneVal,
            isMahasiswa: isMhs,
            tableId:     table?.id ?? null,
            tableNumber: table?.table_number ?? null,
        }));
    }

    function navigateToMenu() {
        router.visit(table ? `/customer/menu?table=${table.id}` : '/customer/menu');
    }

    function handleMasuk() {
        let valid = true;

        const phoneClean = phone.replace(/\D/g, '');
        if (phoneClean && (phoneClean.length < 10 || phoneClean.length > 15)) {
            setPhoneError('Nomor telepon tidak valid (min 10 digit)');
            valid = false;
        } else {
            setPhoneError('');
        }

        if (!valid) return;

        saveIdentity(name.trim(), phoneClean, isMahasiswa);
        navigateToMenu();
    }

    function handleLewati() {
        saveIdentity('', '', false);
        navigateToMenu();
    }

    if (!table) {
        return (
            <CustomerLayout activeTab="menu" showBottomNav={false}>
                <div className="min-h-screen bg-muted flex flex-col items-center justify-center px-7 py-10 text-center">
                    <div className="w-[72px] h-[72px] rounded-[18px] overflow-hidden mb-5 shadow-[0_8px_24px_rgba(0,0,0,0.18)]">
                        {logoError ? (
                            <div className="w-full h-full bg-primary/20 flex items-center justify-center">
                                <span className="text-white text-2xl italic font-bold">w9</span>
                            </div>
                        ) : (
                            <img
                                src="/images/logo.jpg"
                                alt="W9 Cafe"
                                className="w-full h-full object-cover"
                                onError={() => setLogoError(true)}
                            />
                        )}
                    </div>
                    <h1 className="text-[22px] font-extrabold text-foreground mb-[10px]">
                        Pindai QR Meja
                    </h1>
                    <p className="text-sm text-muted-foreground leading-relaxed mb-1.5">
                        Silakan pindai kode QR yang ada di meja Anda untuk mulai memesan.
                    </p>
                    <p className="text-xs text-muted-foreground/50">
                        Hubungi kasir jika membutuhkan bantuan.
                    </p>
                </div>
            </CustomerLayout>
        );
    }

    return (
        <CustomerLayout activeTab="menu" showBottomNav={false}>
            <div className="min-h-screen bg-muted flex flex-col">

                <div className="h-[240px] relative flex flex-col items-center justify-center overflow-hidden shrink-0 bg-gradient-to-b from-[oklch(0.42_0.09_35)] to-[oklch(0.28_0.05_35)]">
                    <div className="absolute w-[180px] h-[180px] rounded-full -top-10 -left-[60px] bg-white/[0.03]" />
                    <div className="absolute w-[100px] h-[100px] rounded-full top-5 -right-[10px] bg-white/[0.02]" />

                    <div className={cn(
                        'w-[110px] h-[110px] rounded-[22px] overflow-hidden z-[1] flex items-center justify-center shadow-[0_8px_30px_rgba(0,0,0,0.40)]',
                        logoError ? 'bg-primary/80' : 'bg-transparent'
                    )}>
                        {logoError ? (
                            <span className="text-white text-[32px] italic font-bold">w9</span>
                        ) : (
                            <img
                                src="/images/logo.jpg"
                                alt="W9 Cafe"
                                className="w-full h-full object-cover"
                                onError={() => setLogoError(true)}
                            />
                        )}
                    </div>

                    <div className="absolute bottom-[-1px] left-0 right-0 h-9 bg-muted rounded-[50%_50%_0_0/100%_100%_0_0]" />
                </div>

                <div className="px-5 pb-10 pt-6 flex-1">

                    <div className="text-center mb-6">
                        <h1 className="text-[26px] font-extrabold text-foreground mb-2 tracking-tight">
                            Selamat Datang!
                        </h1>
                        <div className="flex items-center justify-center gap-1">
                            <MapPin size={13} className="text-primary/60" />
                            <span className="text-[13px] text-muted-foreground">
                                Meja No. <strong className="text-foreground">{table.table_number}</strong>
                            </span>
                        </div>
                    </div>

                    <Card>
                        <CardContent className="flex flex-col gap-4 pt-4">

                            <div>
                                <label className="text-[13px] font-bold text-foreground block mb-2">
                                    Nama <span className="text-muted-foreground font-normal">(opsional)</span>
                                </label>
                                <div className="relative">
                                    <User size={18} className="text-muted-foreground/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                                    <Input
                                        value={name}
                                        onChange={e => setName(e.target.value)}
                                        onKeyDown={e => e.key === 'Enter' && handleMasuk()}
                                        placeholder="Masukkan nama Anda"
                                        maxLength={100}
                                        className="pl-11 h-[50px] rounded-xl text-sm"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="text-[13px] font-bold text-foreground block mb-2">
                                    Nomor Telepon <span className="text-muted-foreground font-normal">(opsional)</span>
                                </label>
                                <div className="relative">
                                    <Phone size={18} className="text-muted-foreground/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                                    <Input
                                        type="tel"
                                        value={phone}
                                        onChange={e => setPhone(e.target.value.replace(/[^0-9]/g, ''))}
                                        onKeyDown={e => e.key === 'Enter' && handleMasuk()}
                                        placeholder="Masukkan nomor telepon"
                                        maxLength={15}
                                        className={cn(
                                            'pl-11 h-[50px] rounded-xl text-sm',
                                            phoneError && 'border-destructive'
                                        )}
                                    />
                                </div>
                                {phoneError && <p className="text-destructive text-xs mt-1">{phoneError}</p>}
                            </div>

                            <div
                                onClick={() => setIsMahasiswa(p => !p)}
                                className={cn(
                                    'rounded-xl px-3.5 py-3 flex items-center gap-3 cursor-pointer transition-all duration-150',
                                    isMahasiswa
                                        ? 'bg-secondary border border-primary'
                                        : 'bg-card border border-transparent',
                                )}
                            >
                                <div className={cn(
                                    'w-[22px] h-[22px] rounded-[6px] shrink-0 flex items-center justify-center transition-all duration-150',
                                    isMahasiswa
                                        ? 'bg-primary border-2 border-primary'
                                        : 'border-2 border-muted-foreground/30',
                                )}>
                                    {isMahasiswa && <Check size={13} className="text-primary-foreground" strokeWidth={2.5} />}
                                </div>
                                <div>
                                    <div className="text-[13px] font-semibold text-foreground">
                                        Saya adalah mahasiswa STIE Totalwin Semarang
                                    </div>
                                    <div className="text-[11px] text-muted-foreground/70 mt-0.5">
                                        Opsional
                                    </div>
                                </div>
                            </div>

                            <Button
                                onClick={handleMasuk}
                                className="w-full h-[52px] rounded-full text-[15px] font-bold mt-1"
                            >
                                Masuk
                            </Button>

                            <Button
                                onClick={handleLewati}
                                variant="outline"
                                className="w-full h-[52px] rounded-full text-[15px] font-bold"
                            >
                                Lewati
                            </Button>

                        </CardContent>
                    </Card>
                </div>
            </div>
        </CustomerLayout>
    );
}
