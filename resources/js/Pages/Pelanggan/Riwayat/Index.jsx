import { useEffect, useState, useCallback } from 'react';
import { router } from '@inertiajs/react';
import { Trash2, PackageOpen } from 'lucide-react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import useCustomerOrderHistory from '@/Hooks/useCustomerOrderHistory';
import RiwayatCard from '@/Components/Pelanggan/RiwayatCard';
import { Button } from '@/components/ui/button';

export default function CustomerRiwayat() {
  const { orders, getHistory, clearHistory } = useCustomerOrderHistory();
  const [ready, setReady] = useState(false);

  useEffect(() => {
    getHistory();
    setReady(true);
  }, [getHistory]);

  const handleDetail = useCallback((order) => {
    if (order.receipt_url) {
      router.visit(order.receipt_url);
    } else {
      router.visit(`/customer/order/${order.uuid}/status`);
    }
  }, []);

  const handleClear = useCallback(() => {
    if (window.confirm('Apakah Anda yakin ingin menghapus semua riwayat pesanan di perangkat ini?')) {
      clearHistory();
    }
  }, [clearHistory]);

  const isEmpty = orders.length === 0;

  if (!ready) return null;

  return (
    <CustomerLayout activeTab="riwayat">
      <div className="bg-card px-5 border-b border-border">
        <div className="h-[54px] flex items-center justify-center">
          <span className="text-lg font-extrabold text-foreground tracking-tight">
            Riwayat Pesanan
          </span>
        </div>
      </div>

      {isEmpty ? (
        <div className="flex flex-col items-center justify-center gap-5 px-6 h-[calc(100vh-180px)]">
          <div className="w-20 h-20 rounded-[20px] bg-muted flex items-center justify-center">
            <PackageOpen size={36} className="text-muted-foreground/40" />
          </div>
          <div className="flex flex-col items-center gap-1.5 text-center">
            <p className="text-base font-bold text-foreground">
              Belum ada riwayat pesanan
            </p>
            <p className="text-sm text-muted-foreground">
              Belum ada riwayat pesanan di perangkat ini.
            </p>
          </div>
          <Button
            onClick={() => router.visit('/customer/menu')}
            className="h-[46px] px-7 !rounded-full text-sm font-semibold"
          >
            Mulai Pesan
          </Button>
        </div>
      ) : (
        <div className="flex flex-col min-h-[calc(100vh-180px)]">
          <div className="flex-1 overflow-y-auto px-4 pt-4 pb-4 space-y-3">
            {orders.map((order) => (
              <RiwayatCard
                key={order.uuid}
                order={order}
                onDetail={handleDetail}
              />
            ))}
          </div>

          <div className="shrink-0 px-4 pb-20 pt-2">
            <Button
              variant="outline"
              onClick={handleClear}
              className="w-full h-[46px] !rounded-xl text-sm font-semibold text-destructive border-destructive/30 hover:bg-destructive/5"
            >
              <Trash2 size={16} className="mr-2" />
              Hapus Riwayat
            </Button>
          </div>
        </div>
      )}
    </CustomerLayout>
  );
}
