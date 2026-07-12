<?php

namespace App\Filament\Resources\StockAdjustmentResource\Actions;

use App\Models\IngredientBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class CancelAdjustmentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->name('batalkan')
            ->label('Batalkan')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('danger')
            ->hidden(fn (StockAdjustment $record): bool => $record->status === 'cancelled')
            ->modalHeading('Batalkan Penyesuaian Stok')
            ->modalDescription('Stok akan dikembalikan seperti semula. Alasan pembatalan wajib diisi.')
            ->modalSubmitActionLabel('Ya, Batalkan')
            ->requiresConfirmation()
            ->form([
                Textarea::make('cancel_reason')
                    ->label('Alasan Pembatalan')
                    ->required(),
            ])
            ->action(function (StockAdjustment $record, Action $action) {
                $data = $action->getData();
                $reason = $data['cancel_reason'] ?? null;

                foreach ($record->stockMovements as $movement) {
                    $batch = IngredientBatch::find($movement->ingredient_batch_id);
                    if (! $batch) {
                        continue;
                    }

                    $originalChange = (float) $movement->quantity_change;
                    $reversalChange = -$originalChange;
                    $batchBefore = (float) $batch->quantity;
                    $batch->increment('quantity', $reversalChange);
                    $batchAfter = (float) $batch->quantity;

                    StockMovement::create([
                        'ingredient_id' => $movement->ingredient_id,
                        'ingredient_batch_id' => $batch->id,
                        'stock_adjustment_id' => $record->id,
                        'movement_type' => $movement->movement_type,
                        'source_type' => 'stock_adjustment_reversal',
                        'source_id' => (string) $record->id,
                        'quantity_before' => $batchBefore,
                        'quantity_change' => $reversalChange,
                        'quantity_after' => $batchAfter,
                        'unit_cost' => $batch->cost_per_unit,
                        'notes' => 'Pembatalan: ' . $reason,
                        'recorded_by' => Auth::id(),
                    ]);
                }

                $record->update([
                    'status' => 'cancelled',
                    'cancel_reason' => $reason,
                ]);

                Notification::make()
                    ->success()
                    ->title('Penyesuaian stok dibatalkan')
                    ->body('Stok telah dikembalikan seperti semula.')
                    ->send();
            });
    }
}
