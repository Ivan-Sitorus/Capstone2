import { useForm } from '@inertiajs/react';
import { Utensils, Mail, Lock, AlertCircle } from 'lucide-react';

export default function KitchenLogin() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/kitchen/login');
    }

    return (
        <div data-interface="kitchen" className="min-h-screen bg-background flex items-center justify-center p-4">
            <div className="w-full max-w-md">
                <form onSubmit={handleSubmit}>
                    <div className="rounded-2xl border border-border bg-card p-8 shadow-[var(--shadow-raised)]">
                        {/* Logo area */}
                        <div className="text-center mb-8">
                            <div className="mx-auto mb-4 flex size-20 items-center justify-center rounded-2xl bg-primary/10">
                                <Utensils className="size-10 text-primary" />
                            </div>
                            <h1 className="text-2xl font-bold text-foreground">Dapur W9 Cafe</h1>
                            <p className="mt-1.5 text-sm text-muted-foreground">
                                Masuk ke Kitchen Display System
                            </p>
                        </div>

                        {/* Error message */}
                        {errors.email && (
                            <div className="mb-5 flex items-center gap-2.5 rounded-xl border border-destructive/40 bg-destructive/10 p-3.5 text-sm text-destructive">
                                <AlertCircle className="size-4 shrink-0" />
                                <span>{errors.email}</span>
                            </div>
                        )}

                        {/* Email */}
                        <div className="mb-4 flex flex-col gap-1.5">
                            <label className="text-sm font-medium text-foreground" htmlFor="email">
                                Email
                            </label>
                            <div className="relative">
                                <Mail className="pointer-events-none absolute left-3.5 top-1/2 size-[18px] -translate-y-1/2 text-muted-foreground" />
                                <input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                    placeholder="kitchen@w9cafe.com"
                                    autoComplete="email"
                                    className="w-full rounded-lg border border-input bg-background py-2.5 pl-11 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-1 focus:ring-offset-background"
                                />
                            </div>
                        </div>

                        {/* Password */}
                        <div className="mb-6 flex flex-col gap-1.5">
                            <label className="text-sm font-medium text-foreground" htmlFor="password">
                                Kata Sandi
                            </label>
                            <div className="relative">
                                <Lock className="pointer-events-none absolute left-3.5 top-1/2 size-[18px] -translate-y-1/2 text-muted-foreground" />
                                <input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={e => setData('password', e.target.value)}
                                    placeholder="Masukkan kata sandi..."
                                    autoComplete="current-password"
                                    className="w-full rounded-lg border border-input bg-background py-2.5 pl-11 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-1 focus:ring-offset-background"
                                />
                            </div>
                        </div>

                        {/* Submit */}
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {processing ? 'Memuat...' : 'Masuk'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
