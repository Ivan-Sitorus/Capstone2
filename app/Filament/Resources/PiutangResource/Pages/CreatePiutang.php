<?php

namespace App\Filament\Resources\PiutangResource\Pages;

use App\Filament\Resources\PiutangResource;
use App\Models\Order;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePiutang extends CreateRecord
{
    protected static string $resource = PiutangResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['payment_method'] = 'piutang';
        $data['status'] = 'belum_lunas';
        $data['order_code'] = Order::generateCode();
        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;

        if ($this->form->getState()['paid_amount'] > 0) {
            $order->orderPayments()->create([
                'amount' => $this->form->getState()['paid_amount'],
                'payment_date' => now(),
                'payment_method' => 'cash',
            ]);
        }
    }

    public function getFormSchema(): array
    {
        return [
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
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
