import { formatRupiah } from '@/helpers';
import { WHITE, SLATE_200, SLATE_500, SLATE_900 } from '@/theme';

const metrics = (totalSales, transactionCount, activeOrders) => [
    { value: formatRupiah(totalSales), label: 'Total Penjualan Hari Ini' },
    { value: transactionCount,         label: 'Jumlah Transaksi' },
    { value: activeOrders,             label: 'Pesanan Aktif' },
];

export default function StatBar({ totalSales, transactionCount, activeOrders }) {
    return (
        <div style={{ display: 'flex', gap: 20, marginBottom: 24 }}>
            {metrics(totalSales, transactionCount, activeOrders).map((m, i) => (
                <div key={i} style={{
                    flex: 1,
                    background: WHITE,
                    border: `1px solid ${SLATE_200}`,
                    borderRadius: 16,
                    padding: 20,
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 8,
                    boxShadow: '0 4px 14px rgba(15,23,42,0.06)',
                }}>
                    <div style={{
                        fontSize: 28,
                        fontWeight: 700,
                        color: SLATE_900,
                        letterSpacing: '-1px',
                        lineHeight: 1,
                    }}>
                        {m.value}
                    </div>
                    <div style={{ fontSize: 13, fontWeight: 500, color: SLATE_500 }}>
                        {m.label}
                    </div>
                </div>
            ))}
        </div>
    );
}
