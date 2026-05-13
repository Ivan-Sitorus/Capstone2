import { useState } from 'react';
import { router, Head } from '@inertiajs/react';
import { Search, Clock, CheckCircle } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
} from '@/components/ui/tabs';
import CashierLayout from '@/Layouts/CashierLayout';
import { formatDate } from '@/helpers';

const TABS = [
    { value: 'semua',    label: 'Semua' },
    { value: 'menunggu', label: 'Menunggu' },
    { value: 'disetujui',label: 'Disetujui' },
    { value: 'ditolak',  label: 'Ditolak' },
];

const STATUS_CONFIG = {
    disetujui: { className: 'text-green-600 bg-green-50 border-green-200', label: 'Disetujui' },
    menunggu:  { className: 'text-amber-600 bg-amber-50 border-amber-200', label: 'Menunggu' },
    ditolak:   { className: 'text-red-600 bg-red-50 border-red-200',       label: 'Ditolak' },
};

export default function VerifikasiAkun({ users, filters, stats }) {
    const currentTab = filters?.tab || 'semua';
    const [search, setSearch] = useState(filters?.search || '');
    const rows = users?.data ?? [];

    function handleSearch(e) {
        e.preventDefault();
        router.reload({ data: { tab: currentTab, search }, preserveState: true, replace: true });
    }

    function handleTabChange(value) {
        router.visit(`/cashier/verifikasi?tab=${value}`);
    }

    function handleApprove(id) {
        router.post(`/cashier/verifikasi/${id}/approve`);
    }

    function handleReject(id) {
        router.post(`/cashier/verifikasi/${id}/reject`);
    }

    function getStatus(user) {
        return user.is_student_verified ? 'disetujui' : 'menunggu';
    }

    return (
        <>
            <Head title="Verifikasi Akun | W9 Cafe" />
            <CashierLayout title="Verifikasi Akun" fullscreen>
                <div className="flex-1 overflow-y-auto p-8 bg-muted">
                    <Card className="shadow-sm">
                        <CardContent className="p-6">
                            {/* ── Header ── */}
                            <div className="flex items-start justify-between mb-6">
                                <div>
                                    <h1 className="text-3xl font-bold text-foreground m-0 mb-1 tracking-tight">
                                        Verifikasi Akun Mahasiswa
                                    </h1>
                                    <p className="text-sm text-muted-foreground m-0">
                                        Kelola dan verifikasi pendaftaran akun pelanggan mahasiswa
                                    </p>
                                </div>
                                <div className="flex items-center gap-4 shrink-0">
                                    <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-600">
                                        <Clock size={16} />
                                        {stats.menunggu} Menunggu
                                    </span>
                                    <span className="inline-flex items-center gap-1.5 text-sm font-semibold text-green-600">
                                        <CheckCircle size={16} />
                                        {stats.disetujui} Disetujui
                                    </span>
                                </div>
                            </div>

                            {/* ── Search + Tabs ── */}
                            <div className="flex items-center gap-4 mb-6">
                                <form onSubmit={handleSearch} className="relative flex-1 max-w-xs">
                                    <Search
                                        size={16}
                                        className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none"
                                    />
                                    <Input
                                        placeholder="Cari nama atau NIM..."
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        className="w-full h-10 pl-9 shadow-sm"
                                    />
                                </form>

                                <Tabs value={currentTab} onValueChange={handleTabChange}>
                                    <TabsList>
                                        {TABS.map((t) => (
                                            <TabsTrigger key={t.value} value={t.value}>
                                                {t.label}
                                                {t.value !== 'semua' && (
                                                    <span className="ml-1">({stats[t.value] ?? 0})</span>
                                                )}
                                            </TabsTrigger>
                                        ))}
                                    </TabsList>
                                </Tabs>
                            </div>

                            {/* ── Table ── */}
                            <Tabs value={currentTab}>
                                <TabsContent value={currentTab}>
                                    <Card className="shadow-sm overflow-hidden">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="bg-muted">
                                                    <TableHead className="text-xs font-semibold text-foreground w-16">No</TableHead>
                                                    <TableHead className="text-xs font-semibold text-foreground">Nama</TableHead>
                                                    <TableHead className="text-xs font-semibold text-foreground">NIM</TableHead>
                                                    <TableHead className="text-xs font-semibold text-foreground">Tgl Daftar</TableHead>
                                                    <TableHead className="text-xs font-semibold text-foreground">Status</TableHead>
                                                    <TableHead className="text-xs font-semibold text-foreground w-48">Aksi</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {rows.length === 0 ? (
                                                    <TableRow>
                                                        <TableCell
                                                            colSpan={6}
                                                            className="text-center py-12 text-sm text-muted-foreground"
                                                        >
                                                            Tidak ada data
                                                        </TableCell>
                                                    </TableRow>
                                                ) : (
                                                    rows.map((user, idx) => {
                                                        const status = getStatus(user);
                                                        const sc = STATUS_CONFIG[status] ?? STATUS_CONFIG.menunggu;
                                                        const isPending = status === 'menunggu';
                                                        return (
                                                            <TableRow key={user.id}>
                                                                <TableCell className="text-muted-foreground">
                                                                    {users.from + idx}
                                                                </TableCell>
                                                                <TableCell className="font-semibold">
                                                                    {user.name}
                                                                </TableCell>
                                                                <TableCell className="font-mono text-sm">
                                                                    {user.nim || '-'}
                                                                </TableCell>
                                                                <TableCell className="text-muted-foreground">
                                                                    {formatDate(user.created_at)}
                                                                </TableCell>
                                                                <TableCell>
                                                                    <Badge variant="outline" className={sc.className}>
                                                                        {sc.label}
                                                                    </Badge>
                                                                </TableCell>
                                                                <TableCell>
                                                                    {isPending ? (
                                                                        <div className="flex items-center gap-2">
                                                                            <Button
                                                                                variant="ghost"
                                                                                className="text-green-600 hover:text-green-700 hover:bg-green-50 h-8 px-3"
                                                                                onClick={() => handleApprove(user.id)}
                                                                            >
                                                                                Setujui
                                                                            </Button>
                                                                            <Button
                                                                                variant="ghost"
                                                                                className="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 px-3"
                                                                                onClick={() => handleReject(user.id)}
                                                                            >
                                                                                Tolak
                                                                            </Button>
                                                                        </div>
                                                                    ) : (
                                                                        <Button
                                                                            variant="link"
                                                                            className="text-primary h-8 px-0"
                                                                            onClick={() => router.visit(`/cashier/order/${user.id}`)}
                                                                        >
                                                                            Detail
                                                                        </Button>
                                                                    )}
                                                                </TableCell>
                                                            </TableRow>
                                                        );
                                                    })
                                                )}
                                            </TableBody>
                                        </Table>
                                    </Card>
                                </TabsContent>
                            </Tabs>
                        </CardContent>
                    </Card>
                </div>
            </CashierLayout>
        </>
    );
}
