import { useState, useRef } from 'react';
import { router, Link, Head } from '@inertiajs/react';
import { Search, Calendar } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import CashierLayout from '@/Layouts/CashierLayout';
import { formatRupiah, formatDate, formatTime } from '@/helpers';

const STATUS_CONFIG = {
  pending:   { variant: 'secondary',   label: 'Pending' },
  diproses:  { variant: 'default',     label: 'Diproses' },
  selesai:   { variant: 'default',     label: 'Selesai' },
  completed: { variant: 'default',     label: 'Selesai' },
  cancelled: { variant: 'destructive', label: 'Dibatalkan' },
  unpaid:    { variant: 'secondary',   label: 'Belum Dibayar' },
  paid:      { variant: 'default',     label: 'Dibayar' },
};

const METHOD_LABELS = { cash: 'Tunai', qris: 'QRIS', bayar_nanti: 'Bayar Nanti' };
const TODAY = new Date().toISOString().split('T')[0];

export default function RiwayatPesanan({ orders, filters }) {
  const rows     = orders.data ?? [];
  const prevUrl  = orders.prev_page_url ?? null;
  const nextUrl  = orders.next_page_url ?? null;
  const currPage = orders.current_page ?? 1;
  const lastPage = orders.last_page ?? 1;

  const [search, setSearch] = useState(filters.search ?? '');
  const [date, setDate]     = useState(filters.date ?? TODAY);
  const [method, setMethod] = useState(filters.method ?? '');
  const searchTimer = useRef(null);

  function apply(overrides = {}) {
    const params = { search, date, method, ...overrides };
    Object.keys(params).forEach(k => { if (params[k] === '') delete params[k]; });
    router.get('/cashier/riwayat', params, { preserveState: true, replace: true });
  }

  function handleSearch(e) {
    const val = e.target.value;
    setSearch(val);
    clearTimeout(searchTimer.current);
    searchTimer.current = setTimeout(() => apply({ search: val }), 400);
  }

  function handleDate(e) {
    const val = e.target.value;
    setDate(val);
    apply({ date: val });
  }

  function handleMethod(val) {
    setMethod(val);
    apply({ method: val });
  }

  return (
    <>
      <Head title="Riwayat Pesanan | W9 Cafe" />
      <CashierLayout title="Riwayat Pesanan" fullscreen>
        <div className="flex-1 overflow-y-auto p-8 bg-muted">
          <Card className="shadow-sm">
            <CardContent className="p-6">
              {/* Title */}
              <div className="mb-7 flex flex-col gap-1">
                <h1 className="text-3xl font-bold text-foreground m-0 tracking-tight">
                  Riwayat Pesanan
                </h1>
                <p className="text-sm text-muted-foreground m-0">
                  Lihat semua transaksi yang telah selesai
                </p>
              </div>

              {/* Filter bar */}
              <div className="flex items-center gap-3 mb-5 flex-wrap">
                <div className="relative min-w-[200px] flex-1">
                  <Search size={18} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                  <Input
                    value={search}
                    onChange={handleSearch}
                    placeholder="Cari transaksi..."
                    className="w-full h-11 pl-11 shadow-sm"
                  />
                </div>

                <div className="relative shrink-0">
                  <Calendar size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                  <Input
                    type="date"
                    value={date}
                    onChange={handleDate}
                    className="h-11 w-40 lg:w-45 pl-10 pr-3.5 shadow-sm"
                  />
                </div>

                <Select value={method} onValueChange={handleMethod}>
                  <SelectTrigger className="h-11 w-40 lg:w-45 shadow-sm">
                    <SelectValue placeholder="Semua Metode" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">Semua Metode</SelectItem>
                    <SelectItem value="cash">Tunai</SelectItem>
                    <SelectItem value="qris">QRIS</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {/* Table */}
              <Card className="shadow-md overflow-hidden">
                <Table>
                  <TableHeader>
                    <TableRow className="bg-muted">
                      <TableHead className="text-xs font-semibold text-foreground">ID Pesanan</TableHead>
                      <TableHead className="text-xs font-semibold text-foreground">Tanggal</TableHead>
                      <TableHead className="text-xs font-semibold text-foreground">Waktu</TableHead>
                      <TableHead className="text-xs font-semibold text-foreground">Total</TableHead>
                      <TableHead className="text-xs font-semibold text-foreground">Pembayaran</TableHead>
                      <TableHead className="text-xs font-semibold text-foreground">Kasir</TableHead>
                      <TableHead className="text-xs font-semibold text-foreground">Status</TableHead>
                      <TableHead className="text-xs font-semibold text-foreground text-right">Aksi</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {rows.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={8} className="text-center py-12 text-sm text-muted-foreground">
                          Tidak ada transaksi ditemukan
                        </TableCell>
                      </TableRow>
                    ) : (
                      rows.map(order => {
                        const statusCfg = STATUS_CONFIG[order.status] ?? { variant: 'outline', label: order.status };
                        return (
                          <TableRow key={order.id}>
                            <TableCell className="font-semibold">{order.order_code}</TableCell>
                            <TableCell className="text-muted-foreground">{formatDate(order.created_at)}</TableCell>
                            <TableCell className="text-muted-foreground">{formatTime(order.created_at)}</TableCell>
                            <TableCell className="font-semibold">{formatRupiah(order.total_amount)}</TableCell>
                            <TableCell className="text-muted-foreground">
                              {METHOD_LABELS[order.payment_method] ?? order.payment_method ?? '—'}
                            </TableCell>
                            <TableCell className="text-muted-foreground">{order.cashier_name ?? '—'}</TableCell>
                            <TableCell>
                              <Badge variant={statusCfg.variant}>{statusCfg.label}</Badge>
                            </TableCell>
                            <TableCell className="text-right">
                              <Button
                                variant="ghost"
                                size="sm"
                                render={<Link href={`/cashier/order/${order.id}`} />}
                              >
                                Detail
                              </Button>
                            </TableCell>
                          </TableRow>
                        );
                      })
                    )}
                  </TableBody>
                </Table>
              </Card>

              {/* Pagination */}
              {lastPage > 1 && (
                <div className="flex items-center justify-between mt-4">
                  <span className="text-sm text-muted-foreground">
                    Halaman {currPage} dari {lastPage}
                  </span>
                  <div className="flex gap-2">
                    {prevUrl && (
                      <Link
                        href={prevUrl}
                        className="inline-flex items-center justify-center rounded-lg text-sm font-medium border border-border text-foreground bg-card px-4 py-1.5 no-underline hover:bg-muted transition-colors"
                      >
                        ← Sebelumnya
                      </Link>
                    )}
                    {nextUrl && (
                      <Link
                        href={nextUrl}
                        className="inline-flex items-center justify-center rounded-lg text-sm font-medium bg-primary text-primary-foreground px-4 py-1.5 no-underline hover:bg-primary/80 transition-colors"
                      >
                        Berikutnya →
                      </Link>
                    )}
                  </div>
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      </CashierLayout>
    </>
  );
}
