<?php

namespace App\Filament\Resources\PiutangResource\Pages;

use App\Filament\Resources\PiutangResource;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListPiutangs extends ListRecords
{
    protected static string $resource = PiutangResource::class;

    public function getTitle(): string
    {
        return 'Piutang';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Piutang';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Piutang')
                ->icon('heroicon-o-plus')
                ->modalHeading('Buat Piutang Baru')
                ->form([
                    TextInput::make('customer_name')
                        ->label('Nama Pelanggan')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('total_amount')
                        ->label('Jumlah')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp'),
                    TextInput::make('paid_amount')
                        ->label('Sudah Dibayar')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp'),
                ])
                ->action(function (array $data) {
                    $order = Order::create([
                        'customer_name' => $data['customer_name'],
                        'total_amount' => $data['total_amount'],
                        'payment_method' => 'piutang',
                        'status' => 'belum_lunas',
                        'order_code' => Order::generateCode(),
                    ]);

                    if (($data['paid_amount'] ?? 0) > 0) {
                        $order->orderPayments()->create([
                            'amount' => $data['paid_amount'],
                            'payment_date' => now(),
                            'payment_method' => 'cash',
                        ]);
                    }
                }),
        ];
    }
}
