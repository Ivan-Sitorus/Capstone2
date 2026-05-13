import { useForm } from '@inertiajs/react';
import { Mail, Lock, AlertCircle } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import ThemeToggle from '@/Components/Common/ThemeToggle';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/login');
    }

    return (
        <div data-interface="cashier" className="flex min-h-screen">
            <div className="w-1/2 bg-sidebar flex flex-col items-center justify-center gap-3.5">
                <img
                    src="/images/logo.jpg"
                    alt="W9 Cafe"
                    className="w-[120px] h-[120px] rounded-3xl object-cover shadow-[0_4px_20px_rgba(0,0,0,0.30)]"
                />
                <h1 className="text-sidebar-foreground text-[32px] font-bold tracking-tight">
                    W9 Cafe
                </h1>
                <p className="text-sidebar-foreground/50 text-[15px]">
                    Sistem Point of Sale
                </p>
            </div>

            <div className="w-1/2 bg-background flex items-center justify-center px-14 relative">
                <div className="absolute top-4 right-4">
                    <ThemeToggle />
                </div>

                <form onSubmit={handleSubmit} className="w-full max-w-[400px]">
                <Card>
                    <CardHeader>
                        <CardTitle>Masuk ke Akun Anda</CardTitle>
                        <CardDescription>
                            Masukkan email dan kata sandi untuk melanjutkan
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        {errors.email && (
                            <div className="flex items-center gap-2 rounded-xl border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive">
                                <AlertCircle className="size-4 shrink-0" />
                                <span>{errors.email}</span>
                            </div>
                        )}

                        <div className="flex flex-col gap-1.5">
                            <label className="text-sm font-medium text-foreground">Email</label>
                            <div className="relative">
                                <Mail className="pointer-events-none absolute left-3.5 top-1/2 size-[18px] -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="email"
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                    placeholder="kasir@kafenusantara.com"
                                    autoComplete="email"
                                    className="pl-11"
                                />
                            </div>
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <label className="text-sm font-medium text-foreground">Kata Sandi</label>
                            <div className="relative">
                                <Lock className="pointer-events-none absolute left-3.5 top-1/2 size-[18px] -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    type="password"
                                    value={data.password}
                                    onChange={e => setData('password', e.target.value)}
                                    placeholder="Masukkan kata sandi..."
                                    autoComplete="current-password"
                                    className="pl-11"
                                />
                            </div>
                        </div>

                        <Button
                            type="submit"
                            disabled={processing}
                            variant="default"
                            size="lg"
                            className="w-full"
                        >
                            {processing ? 'Memuat...' : 'Masuk'}
                        </Button>
                    </CardContent>
                </Card>
                </form>
            </div>
        </div>
    );
}
