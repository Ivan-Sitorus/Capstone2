<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->loadMissing('orderPayments');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recordPayment')
                ->label('Catat Pembayaran')
                ->icon(Heroicon::OutlinedCurrencyDollar)
                ->color('success')
                ->visible(fn (Order $record): bool => $record->payment_method === 'piutang' && $record->status === 'belum_lunas')
                ->form([
                    TextInput::make('amount')
                        ->label('Jumlah Pembayaran')
                        ->required()
                        ->numeric()
                        ->type('text')
                        ->mask(\App\Filament\Forms\Components\NumericInput::mask(0))
                        ->stripCharacters('.')
                        ->maxLength(\App\Filament\Forms\Components\NumericInput::maxLength(6, 0))
                        ->minValue(1)
                        ->maxValue(fn (Order $record): float => max(0, (float) $record->total_amount - (float) $record->orderPayments->sum('amount'))),
                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Tunai',
                            'qris' => 'QRIS',
                            'transfer' => 'Transfer',
                        ])
                        ->default('cash')
                        ->required(),
                    DateTimePicker::make('payment_date')
                        ->label('Tanggal Pembayaran')
                        ->default(now()),
                    Textarea::make('notes')
                        ->label('Catatan'),
                ])
                ->action(function (Order $record, array $data): void {
                    $record->orderPayments()->create([
                        'amount' => $data['amount'],
                        'payment_date' => $data['payment_date'] ?? now(),
                        'payment_method' => $data['payment_method'],
                    ]);

                    $record->recalculatePaymentStatus();

                    $this->redirect(request()->header('Referer') ?? url()->previous());
                }),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return parent::infolist($schema)->components([
            ...$schema->getComponents(),
            Section::make('Riwayat Pembayaran')
                ->visible(fn (Order $record): bool => $record->payment_method === 'piutang')
                ->schema([
                    RepeatableEntry::make('orderPayments')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('payment_date')
                                ->label('Tanggal')
                                ->dateTime('d M Y, H:i:s'),
                            TextEntry::make('amount')
                                ->label('Jumlah')
                                ->formatStateUsing(fn ($state) => 'Rp'.number_format((float) $state, 0, ',', '.')),
                            TextEntry::make('payment_method')
                                ->label('Metode')
                                ->formatStateUsing(fn ($state) => match ($state) {
                                    'cash' => 'Tunai',
                                    'qris' => 'QRIS',
                                    'transfer' => 'Transfer',
                                    default => $state ?? '-',
                                }),
                        ])->columns(3),
                ]),
        ]);
    }
}
