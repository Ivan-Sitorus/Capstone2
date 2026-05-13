import { Card, CardContent } from '@/components/ui/card';
import { formatRupiah } from '@/helpers';

const metrics = (totalPenjualan, jumlahTransaksi, pesananAktif) => [
    { value: formatRupiah(totalPenjualan), label: 'Total Penjualan Hari Ini' },
    { value: jumlahTransaksi,             label: 'Jumlah Transaksi' },
    { value: pesananAktif,                label: 'Pesanan Aktif' },
];

export default function StatBar({ totalPenjualan, jumlahTransaksi, pesananAktif }) {
    return (
        <div className="flex gap-5 mb-6">
            {metrics(totalPenjualan, jumlahTransaksi, pesananAktif).map((m) => (
                <Card key={m.label} size="sm" className="flex-1 shadow-sm">
                    <CardContent className="flex flex-col gap-2">
                        <div className="text-3xl font-bold text-foreground tracking-tight leading-none">
                            {m.value}
                        </div>
                        <div className="text-xs font-medium text-muted-foreground">
                            {m.label}
                        </div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}
