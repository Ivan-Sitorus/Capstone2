import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { ClipboardList, Coffee, X } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { formatRupiah, formatDate, formatTime } from '@/helpers';
import { cn } from '@/lib/utils';

import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogClose,
} from '@/components/ui/dialog';

const TABS = [
  { key: 'all',      label: 'Semua'   },
  { key: 'pending',  label: 'Menunggu' },
  { key: 'diproses', label: 'Diproses' },
  { key: 'selesai',  label: 'Selesai'  },
];

const METHOD_LABEL = { cash: 'Tunai', qris: 'QRIS' };

const STATUS_LABELS = {
  pending:  'Menunggu',
  diproses: 'Diproses',
  selesai:  'Selesai',
};

function groupByDate(orders, fmtDate) {
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const yday = new Date(today); yday.setDate(yday.getDate() - 1);
  const groups = new Map();
  orders.forEach(o => {
    const d = new Date(o.created_at); d.setHours(0, 0, 0, 0);
    const t = d.getTime();
    const key = t === today.getTime() ? 'HARI INI'
      : t === yday.getTime() ? 'KEMARIN'
      : fmtDate(o.created_at).toUpperCase();
    if (!groups.has(key)) groups.set(key, []);
    groups.get(key).push(o);
  });
  return groups;
}

function OrderCard({ order, onDetail }) {
  const payLabel = METHOD_LABEL[order.payment_method] ?? '';
  const itemCount = order.items?.length ?? 0;
  const subtitle = [payLabel, itemCount ? `${itemCount} item` : ''].filter(Boolean).join(' · ');
  const hasItems = Array.isArray(order.items) && order.items.length > 0;
  const badgeLabel = STATUS_LABELS[order.status] ?? order.status;

  const badgeClass = cn(
    'rounded-full border-0 h-auto px-3 py-1 text-xs font-semibold',
    order.status === 'pending'  && 'bg-amber-50 text-amber-700',
    order.status === 'diproses' && 'bg-primary/15 text-primary',
    order.status === 'selesai'  && 'bg-green-50 text-green-700',
  );

  return (
    <Card className="overflow-hidden rounded-2xl p-0 gap-0">
      <div className="flex items-start gap-2.5 px-4 pt-[14px] pb-3">
        <div className="mt-0.5 flex size-[38px] shrink-0 items-center justify-center rounded-full bg-muted/70">
          <Coffee size={17} className="text-muted-foreground/40" />
        </div>
        <div className="min-w-0 flex-1">
          <div className="text-sm font-bold text-foreground">
            {order.order_code}
          </div>
          {subtitle && (
            <div className="mt-0.5 text-xs text-muted-foreground/60">
              {subtitle}
            </div>
          )}
        </div>
        <Badge className={badgeClass}>{badgeLabel}</Badge>
      </div>

      <div className="mx-4 h-px bg-border" />
      <div className="flex flex-col gap-1.5 px-4 py-[10px]">
        {hasItems
          ? order.items.map((item, i) => (
              <div key={i} className="flex items-center justify-between text-[13px]">
                <span className="text-foreground">{item.quantity}× {item.name}</span>
                <span className="text-foreground">{formatRupiah(item.subtotal)}</span>
              </div>
            ))
          : (
            <span className="text-[13px] text-muted-foreground">
              {order.items_summary}
            </span>
          )}
      </div>

      <div className="mx-4 h-px bg-border" />
      <div className="flex items-center justify-between px-4 py-3">
        <div>
          <div className="mb-0.5 text-[10px] font-bold tracking-[0.5px] text-muted-foreground/60">
            TOTAL
          </div>
          <span className="text-base font-extrabold tracking-tight text-foreground">
            {formatRupiah(order.total_amount)}
          </span>
        </div>
        <Button
          variant="outline"
          size="sm"
          onClick={onDetail}
          className="h-auto rounded-[10px] px-[18px] py-[9px] text-[13px] font-bold"
        >
          Detail
        </Button>
      </div>
    </Card>
  );
}

export default function CustomerRiwayat({ orders = [] }) {
  const [activeTab, setActiveTab] = useState('all');
  const [receiptOrder, setReceiptOrder] = useState(null);
  const [logoError, setLogoError] = useState(false);

  const sessionName = (() => {
    try {
      const s = sessionStorage.getItem('w9_customer');
      return s ? JSON.parse(s)?.name ?? null : null;
    } catch (_) { return null; }
  })();

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    if (!params.get('phone')) {
      try {
        const saved = sessionStorage.getItem('w9_customer');
        if (saved) {
          const data = JSON.parse(saved);
          if (data?.phone) {
            router.visit(`/customer/riwayat?phone=${encodeURIComponent(data.phone)}`, {
              preserveState: true,
              replace: true,
            });
          }
        }
      } catch (_) {}
    }
  }, []);

  useEffect(() => {
    const hasActive = orders.some(o => o.status !== 'selesai');
    if (!hasActive) return;
    const id = setInterval(() => {
      router.reload({ only: ['orders'], preserveState: true });
    }, 8000);
    return () => clearInterval(id);
  }, [orders]);

  const filteredOrders = orders.filter(o => {
    if (activeTab === 'all') return true;
    return o.status === activeTab;
  });

  const grouped = groupByDate(filteredOrders, formatDate);

  return (
    <CustomerLayout activeTab="riwayat">
      <div className="flex min-h-[calc(100vh-92px)] flex-col bg-muted">

        <div className="px-4 pt-4">
          <div className="inline-block min-w-[120px] rounded-[14px] bg-muted/50 px-4 py-3">
            <div className="mb-1 text-[10px] font-bold tracking-[0.6px] text-muted-foreground/60">
              TOTAL PESANAN
            </div>
            <div className="text-[22px] font-extrabold tracking-tight text-foreground">
              {orders.length}
            </div>
          </div>
        </div>

        <Tabs value={activeTab} onValueChange={setActiveTab}>
          <TabsList
            className={cn(
              '!h-auto w-full flex-nowrap gap-1 overflow-x-auto rounded-none bg-transparent p-0',
              'px-4 pb-[10px] pt-[14px]',
            )}
          >
            {TABS.map(tab => (
              <TabsTrigger
                key={tab.key}
                value={tab.key}
                className={cn(
                  'min-h-[44px] shrink-0 rounded-full px-[18px] py-[7px] text-[13px]',
                  'bg-transparent font-medium text-muted-foreground',
                  'data-active:bg-primary data-active:text-primary-foreground data-active:font-bold',
                  'data-active:shadow-[0_3px_10px_rgba(232,118,58,0.30)]',
                )}
              >
                {tab.label}
              </TabsTrigger>
            ))}
          </TabsList>
        </Tabs>

        <div className="flex flex-1 flex-col gap-0 px-4 pb-7">
          {filteredOrders.length === 0 ? (
            <div className="flex flex-col items-center justify-center gap-[14px] py-[56px]">
              <div className="flex size-[72px] items-center justify-center rounded-[20px] bg-muted/50">
                <ClipboardList size={30} className="text-muted-foreground/40" />
              </div>
              <div className="flex flex-col items-center gap-1">
                <span className="text-[15px] font-bold text-foreground">
                  Belum ada riwayat pesanan
                </span>
                <span className="text-[12.5px] text-muted-foreground/70">
                  Pesanan kamu akan muncul di sini
                </span>
              </div>
            </div>
          ) : (
            Array.from(grouped.entries()).map(([dateLabel, groupOrders]) => (
              <div key={dateLabel}>
                <div className="pb-2 pt-[14px] text-[11px] font-bold tracking-[0.8px] text-muted-foreground/60">
                  {dateLabel}
                </div>
                <div className="flex flex-col gap-[10px]">
                  {groupOrders.map(order => (
                    <OrderCard
                      key={order.id}
                      order={order}
                      onDetail={() => setReceiptOrder(order)}
                    />
                  ))}
                </div>
              </div>
            ))
          )}
        </div>
      </div>

      <Dialog open={!!receiptOrder} onOpenChange={open => { if (!open) setReceiptOrder(null); }}>
        <DialogContent
          className="flex max-h-[85vh] max-w-[340px] flex-col gap-0 overflow-y-auto rounded-[24px] p-0"
          showCloseButton={false}
        >
          <div className="h-[3px] shrink-0 bg-gradient-to-r from-[#E8763A] to-[#FB923C]" />

          <div className="flex justify-end px-4 pt-3">
            <DialogClose
              render={
                <Button
                  variant="ghost"
                  size="icon-sm"
                  className="size-8 rounded-full border border-border bg-muted/30"
                />
              }
            >
              <X size={15} className="text-muted-foreground/50" />
            </DialogClose>
          </div>

          <div className="flex flex-col items-center gap-2 pb-[18px]">
            <div
              className="flex size-[54px] items-center justify-center overflow-hidden rounded-[14px] bg-card"
              style={{ boxShadow: '0 4px 14px rgba(0,0,0,0.15)' }}
            >
              {logoError ? (
                <span className="text-lg font-bold italic text-white" style={{ color: '#fff' }}>
                  w9
                </span>
              ) : (
                <img
                  src="/images/logo.jpg"
                  alt="W9"
                  className="size-full object-cover"
                  onError={() => setLogoError(true)}
                />
              )}
            </div>
            <span className="text-[17px] font-extrabold tracking-tight text-foreground">
              W9 Cafe
            </span>
          </div>

          <div className="mx-6 h-px bg-border" />

          <div className="flex flex-col gap-[9px] px-6 py-4">
            {[
              { label: 'No. Pesanan', value: receiptOrder?.order_code },
              { label: 'Tanggal',     value: receiptOrder ? `${formatDate(receiptOrder.created_at)}, ${formatTime(receiptOrder.created_at)}` : '' },
              { label: 'Pelanggan',   value: receiptOrder?.customer_name ?? sessionName ?? '—' },
              { label: 'Pembayaran',  value: receiptOrder ? (METHOD_LABEL[receiptOrder.payment_method] ?? '—') : '' },
            ].map(row => (
              <div key={row.label} className="flex items-baseline justify-between gap-3">
                <span className="shrink-0 text-[12.5px] text-muted-foreground/70">
                  {row.label}
                </span>
                <span className="text-right text-[13px] font-semibold text-foreground">
                  {row.value}
                </span>
              </div>
            ))}
          </div>

          <div className="mx-6 h-px bg-border" />

          <div className="flex items-center justify-between px-6 pb-2 pt-[14px]">
            <span className="flex-1 text-[11px] font-bold tracking-[0.4px] text-muted-foreground/60">
              ITEM
            </span>
            <span className="w-8 text-center text-[11px] font-bold tracking-[0.4px] text-muted-foreground/60">
              JML
            </span>
            <span className="w-20 text-right text-[11px] font-bold tracking-[0.4px] text-muted-foreground/60">
              HARGA
            </span>
          </div>

          <div className="flex flex-col gap-2 px-6 pb-[14px]">
            {(receiptOrder?.items ?? []).map((item, i) => (
              <div key={i} className="flex items-center justify-between">
                <span className="flex-1 text-[13px] text-foreground">{item.name}</span>
                <span className="w-8 text-center text-[13px] text-muted-foreground/70">
                  {item.quantity}×
                </span>
                <span className="w-20 text-right text-[13px] text-foreground">
                  {formatRupiah(item.subtotal)}
                </span>
              </div>
            ))}
          </div>

          <div className="mx-6 h-px bg-border" />

          <div className="flex flex-col gap-2 px-6 pt-4">
            <div className="flex items-center justify-between">
              <span className="text-[13px] text-muted-foreground/70">Subtotal</span>
              <span className="text-[13px] text-muted-foreground">
                {receiptOrder ? formatRupiah(receiptOrder.total_amount) : ''}
              </span>
            </div>
            <div className="mb-5 flex items-center justify-between rounded-[10px] border border-primary/20 bg-secondary px-3 py-[10px]">
              <span className="text-[15px] font-extrabold text-foreground">Total</span>
              <span className="text-base font-extrabold tracking-tight text-primary">
                {receiptOrder ? formatRupiah(receiptOrder.total_amount) : ''}
              </span>
            </div>
          </div>

          <div className="pb-[18px] text-center">
            <span className="text-[13px] font-semibold text-muted-foreground/60">
              Terima kasih sudah memesan!
            </span>
          </div>

          <div className="shrink-0 border-t border-border px-5 pb-[22px] pt-[10px]">
            <DialogClose
              render={
                <Button className="h-[50px] w-full rounded-[14px] text-[15px] font-bold">
                  Tutup
                </Button>
              }
            />
          </div>
        </DialogContent>
      </Dialog>
    </CustomerLayout>
  );
}
