<?php

namespace App\Filament\Resources\ReceivableResource\Pages;

use App\Filament\Resources\ReceivableResource;
use App\Models\Receivable;
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

class ViewReceivable extends ViewRecord
{
    protected static string $resource = ReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recordPayment')
                ->label('Catat Pembayaran')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->visible(fn (Receivable $record): bool => ! in_array($record->status, [Receivable::STATUS_PAID, Receivable::STATUS_CANCELLED]))
                ->form([
                    TextInput::make('amount')
                        ->label('Jumlah Pembayaran')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn (Receivable $record): float => (float) $record->remaining_amount),
                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Tunai',
                            'qris' => 'QRIS',
                            'transfer' => 'Transfer',
                        ])
                        ->required(),
                    DateTimePicker::make('payment_date')
                        ->label('Tanggal Pembayaran')
                        ->default(now()),
                    Textarea::make('notes')
                        ->label('Catatan'),
                ])
                ->action(function (Receivable $record, array $data): void {
                    $record->recordPayment(
                        (float) $data['amount'],
                        $data['payment_method'],
                        auth()->id(),
                        $data['notes'] ?? null
                    );

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
                ->schema([
                    RepeatableEntry::make('payments')
                        ->schema([
                            TextEntry::make('payment_date')
                                ->label('Tanggal')
                                ->dateTime('d M Y, H:i:s'),
                            TextEntry::make('amount')
                                ->label('Jumlah')
                                ->money('IDR'),
                            TextEntry::make('payment_method')
                                ->label('Metode')
                                ->formatStateUsing(fn ($state) => match ($state) {
                                    'cash' => 'Tunai',
                                    'qris' => 'QRIS',
                                    'transfer' => 'Transfer',
                                    default => $state ?? '-',
                                }),
                            TextEntry::make('notes')
                                ->label('Catatan')
                                ->default('-'),
                            TextEntry::make('recordedBy.name')
                                ->label('Perekam')
                                ->default('-'),
                        ])->columns(5),
                ]),
        ]);
    }
}
