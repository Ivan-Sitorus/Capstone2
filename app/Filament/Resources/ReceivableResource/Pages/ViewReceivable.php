<?php

namespace App\Filament\Resources\ReceivableResource\Pages;

use App\Filament\Resources\ReceivableResource;
use App\Models\Receivable;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;

class ViewReceivable extends ViewRecord
{
    protected static string $resource = ReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('record_payment')
                ->label('Record Payment')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->visible(fn (Receivable $record): bool => $record->remaining_amount > 0)
                ->modalSubmitActionLabel('Save Payment')
                ->form([
                    TextInput::make('payment_amount')
                        ->label('Payment Amount')
                        ->prefix('Rp')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(fn (Receivable $record): float => (float) $record->remaining_amount)
                        ->helperText(fn (Receivable $record): string => 'Maximum: Rp '.number_format($record->remaining_amount, 0, ',', '.'))
                        ->columnSpanFull(),
                ])
                ->action(function (Receivable $record, array $data): void {
                    $paymentAmount = (float) $data['payment_amount'];
                    $newPaidAmount = (float) $record->paid_amount + $paymentAmount;
                    $remaining = (float) $record->amount - $newPaidAmount;

                    if ($remaining <= 0) {
                        $newStatus = Receivable::STATUS_PAID;
                        $newPaidAmount = (float) $record->amount;
                    } elseif ($newPaidAmount > 0) {
                        $newStatus = Receivable::STATUS_PARTIAL;
                    } else {
                        $newStatus = $record->status;
                    }

                    $record->update([
                        'paid_amount' => $newPaidAmount,
                        'status' => $newStatus,
                    ]);

                    Log::info('Payment recorded', [
                        'receivable_id' => $record->id,
                        'payment_amount' => $paymentAmount,
                        'new_paid_amount' => $newPaidAmount,
                        'new_status' => $newStatus,
                    ]);
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
