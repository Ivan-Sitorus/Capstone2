import { useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { QRCodeCanvas } from 'qrcode.react';
import { formatRupiah, formatDate, formatTime } from '@/helpers';
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

export default function ReceiptShow({ order, cafe }) {
    const receiptUrl = window.location.origin + '/receipt/' + order.order_code;

    useEffect(() => {
        document.title = `Struk #${order.order_code} | ${cafe.name}`;
    }, []);

    const totalQty = order.items.reduce((s, i) => s + i.quantity, 0);

    const paymentLabel =
        order.payment_method === 'bayar_nanti'
            ? 'Bayar Nanti'
            : order.payment_method?.toUpperCase() ?? '-';

    return (
        <>
            <Head title={`Struk #${order.order_code} | ${cafe.name}`} />
            <div className="min-h-screen bg-[#F0F0F0] flex items-start justify-center py-10 px-4">
                <Card
                    id="receipt"
                    size="sm"
                    className="w-full max-w-sm font-inter"
                >
                    <CardContent className="p-0">
                        <div className="text-center pt-8 pb-4 px-6 border-b-2 border-dashed border-[#E9ECEF]">
                            <h1 className="text-xl font-bold text-[#1A1A2E] m-0">
                                {cafe.name}
                            </h1>
                            {cafe.address && (
                                <p className="text-sm text-[#6C757D] m-0 mt-1 leading-relaxed">
                                    {cafe.address}
                                </p>
                            )}
                            {cafe.phone && (
                                <p className="text-sm text-[#6C757D] m-0 mt-0.5">
                                    {cafe.phone}
                                </p>
                            )}
                        </div>

                        <div className="flex justify-between items-start py-4 px-6 border-b border-[#E9ECEF]">
                            <div>
                                <p className="text-xs text-[#6C757D] m-0 font-medium">
                                    No. Pesanan
                                </p>
                                <p className="text-base font-bold text-[#1A1A2E] m-0 mt-0.5">
                                    #{order.order_code}
                                </p>
                            </div>
                            <div className="text-right">
                                <p className="text-xs text-[#6C757D] m-0 font-medium">
                                    Tanggal
                                </p>
                                <p className="text-sm text-[#1A1A2E] m-0 mt-0.5">
                                    {formatDate(order.created_at)},{' '}
                                    {formatTime(order.created_at)}
                                </p>
                            </div>
                        </div>

                        {order.customer_name && (
                            <div className="py-3 px-6 border-b border-[#E9ECEF]">
                                <p className="text-xs text-[#6C757D] m-0 font-medium">
                                    Pelanggan
                                </p>
                                <p className="text-sm font-semibold text-[#1A1A2E] m-0 mt-0.5">
                                    {order.customer_name}
                                </p>
                            </div>
                        )}

                        {order.table_number && (
                            <div className="py-3 px-6 border-b border-[#E9ECEF]">
                                <p className="text-xs text-[#6C757D] m-0 font-medium">
                                    Meja
                                </p>
                                <p className="text-sm font-semibold text-[#1A1A2E] m-0 mt-0.5">
                                    Meja {order.table_number}
                                </p>
                            </div>
                        )}

                        <div className="border-b border-[#E9ECEF]">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="text-xs font-semibold uppercase text-[#6C757D]">
                                            Item
                                        </TableHead>
                                        <TableHead className="text-xs font-semibold uppercase text-[#6C757D] text-center w-[48px]">
                                            Jml
                                        </TableHead>
                                        <TableHead className="text-xs font-semibold uppercase text-[#6C757D] text-right w-[80px]">
                                            Subtotal
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {order.items.map((item, idx) => (
                                        <TableRow key={idx}>
                                            <TableCell className="py-2.5">
                                                <p className="text-sm font-medium text-[#1A1A2E] truncate">
                                                    {item.name}
                                                </p>
                                                <p className="text-xs text-[#6C757D] mt-0.5">
                                                    {formatRupiah(item.unit_price)}
                                                </p>
                                            </TableCell>
                                            <TableCell className="text-sm text-[#1A1A2E] text-center py-2.5">
                                                {item.quantity}
                                            </TableCell>
                                            <TableCell className="text-sm font-semibold text-[#1A1A2E] text-right py-2.5">
                                                {formatRupiah(item.subtotal)}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>

                        <div className="px-6 pt-3 pb-4 border-b-2 border-dashed border-[#E9ECEF]">
                            {order.discount > 0 && (
                                <>
                                    <div className="flex justify-between items-center py-1.5">
                                        <span className="text-sm text-[#6C757D]">
                                            Subtotal ({totalQty} item)
                                        </span>
                                        <span className="text-sm text-[#6C757D]">
                                            {formatRupiah(order.raw_total)}
                                        </span>
                                    </div>

                                    <div className="flex justify-between items-center py-1.5">
                                        <span className="text-sm text-[#28A745]">
                                            Diskon
                                        </span>
                                        <span className="text-sm font-semibold text-[#28A745]">
                                            - {formatRupiah(order.discount)}
                                        </span>
                                    </div>
                                </>
                            )}

                            <div className="flex justify-between items-center py-2 border-t border-[#E9ECEF] mt-1">
                                <span className="text-base font-bold text-[#1A1A2E]">
                                    Total
                                </span>
                                <span className="text-lg font-bold text-[#3B6FD4]">
                                    {formatRupiah(order.total_amount)}
                                </span>
                            </div>

                            <div className="flex justify-between items-center pt-1">
                                <span className="text-xs text-[#6C757D]">
                                    Pembayaran
                                </span>
                                <div className="flex items-center gap-1.5">
                                    <span className="text-xs font-medium text-[#1A1A2E] capitalize">
                                        {paymentLabel}
                                    </span>
                                    <Badge
                                        variant="outline"
                                        className={`text-xs font-semibold px-2 py-0.5 ${
                                            order.is_paid
                                                ? 'bg-green-50 text-green-600'
                                                : 'bg-yellow-50 text-yellow-600'
                                        }`}
                                    >
                                        {order.is_paid ? 'Lunas' : 'Belum Lunas'}
                                    </Badge>
                                </div>
                            </div>

                            {order.cashier_name && (
                                <div className="flex justify-between items-center pt-1">
                                    <span className="text-xs text-[#6C757D]">
                                        Kasir
                                    </span>
                                    <span className="text-xs font-medium text-[#1A1A2E]">
                                        {order.cashier_name}
                                    </span>
                                </div>
                            )}
                        </div>

                        <div className="flex flex-col items-center py-6 px-6 border-b border-[#E9ECEF]">
                            <div className="bg-white p-3 rounded-xl border border-[#E9ECEF]">
                                <QRCodeCanvas
                                    value={receiptUrl}
                                    size={140}
                                    level="M"
                                    fgColor="#1A1A2E"
                                    className="block"
                                />
                            </div>
                            <p className="text-xs text-[#6C757D] mt-3 m-0 text-center">
                                Scan untuk melihat struk digital
                            </p>
                        </div>

                        <div className="text-center py-5 px-6">
                            <p className="text-xs text-[#6C757D] m-0 leading-relaxed">
                                Terima kasih telah berbelanja di {cafe.name}
                            </p>
                            <p className="text-[10px] text-[#ADB5BD] m-0 mt-1">
                                #{order.order_code} ·{' '}
                                {formatDate(order.created_at)}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
