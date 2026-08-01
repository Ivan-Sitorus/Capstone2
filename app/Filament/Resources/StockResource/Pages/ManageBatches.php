<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Enums\BatchMode;
use App\Enums\AdjustableType;
use App\Filament\Resources\StockResource;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Filament\Resources\StockAdjustmentResource;
use App\Services\StockReconciliationService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ManageBatches extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = StockResource::class;

    public Ingredient $record;

    public bool $showDepleted = false;

    public function mount(Ingredient $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string|Htmlable
    {
        return "Batch Stok - {$this->record->name}";
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        $unit = $this->record->unit;

        return $table
            ->query(fn () => IngredientBatch::where('ingredient_id', $this->record->id)
                ->with('batchPayments')
                ->when(!$this->showDepleted, fn ($q) => $q->where('quantity', '>', 0)))
            ->columns([
                TextColumn::make('batch_code')
                    ->label('Kode Batch')
                    ->default('-'),
                TextColumn::make('received_at')
                    ->label('Waktu Diterima')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('initial_quantity')
                    ->label('Jumlah')
                    ->state(fn (IngredientBatch $record) => $record->initial_quantity ?? $record->quantity)
                    ->formatStateUsing(fn ($state) => number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.').' '.$unit)
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Sisa')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.').' '.$unit)
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Tanggal Kedaluwarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->expiry_date && $record->expiry_date->isPast() ? 'danger' : null),
                TextColumn::make('cost_per_unit')
                    ->label('Harga/Unit')
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('supplier_name')
                    ->label('Supplier')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('utang')
                    ->label('Sisa Utang')
                    ->state(function (IngredientBatch $record): float {
                        $totalCost = (float) ($record->total_cost ?? 0);
                        $paid = (float) $record->batchPayments->sum('amount');
                        return max(0, $totalCost - $paid);
                    })
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),
                TextColumn::make('payment_status')
                    ->label('Status Utang')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'lunas' ? 'Lunas' : 'Belum Lunas')
                    ->color(fn (?string $state): string => $state === 'lunas' ? 'success' : 'warning'),
                TextColumn::make('allow_expired_usage')
                    ->label('')
                    ->default('')
                    ->state(fn ($record) => $record->allow_expired_usage && $record->expiry_date && $record->expiry_date->isPast() ? '⚠️ Abaikan Kedaluwarsa' : '')
                    ->color('warning'),
            ])
            ->headerActions([
                Action::make('toggle_depleted')
                    ->label(fn () => $this->showDepleted ? 'Sembunyikan Batch Habis' : 'Tampilkan Batch Habis')
                    ->color(fn () => $this->showDepleted ? 'gray' : 'info')
                    ->action(fn () => $this->showDepleted = !$this->showDepleted),
                CreateAction::make()
                    ->model(IngredientBatch::class)
                    ->form($this->batchFormFields(isCreate: true))
                    ->using(function (array $data): IngredientBatch {
                        $totalCost = (float) ($data['total_harga'] ?? 0);
                        $initialQty = (float) ($data['quantity'] ?? 0);
                        $totalDibayar = (float) ($data['total_dibayar'] ?? 0);

                        $data['total_cost'] = $totalCost > 0 ? $totalCost : null;
                        $data['initial_quantity'] = $data['quantity'];
                        $data['cost_per_unit'] = $initialQty > 0 ? $totalCost / $initialQty : 0;
                        unset($data['total_harga'], $data['sudah_lunas'], $data['total_dibayar']);

                        $batch = $this->record->batches()->create($data);

                        // Auto-record purchase stock movement
                        StockMovement::create([
                            'ingredient_id' => $batch->ingredient_id,
                            'ingredient_batch_id' => $batch->id,
                            'movement_type' => 'purchase',
                            'source_type' => 'batch_addition',
                            'quantity_before' => 0,
                            'quantity_change' => $batch->quantity,
                            'quantity_after' => $batch->quantity,
                            'reference' => $batch->batch_code,
                            'recorded_by' => auth()->id(),
                            'notes' => 'Pembelian batch '.($batch->batch_code ?? $batch->id),
                        ]);

                        // Record initial supplier payment if any — observer auto-sets payment_status
                        if ($totalDibayar > 0) {
                            $batch->batchPayments()->create([
                                'amount' => $totalDibayar,
                                'payment_date' => now(),
                                'payment_method' => 'cash',
                            ]);
                        }

                        return $batch;
                    }),
            ])
            ->recordActions([
                Action::make('riwayat_bayar_batch')
                    ->label('Riwayat Bayar')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('info')
                    ->url(fn (IngredientBatch $record) => StockResource::getUrl('riwayat-bayar-batch', ['record' => $record])),
                EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, IngredientBatch $record): array {
                        $data['total_harga'] = (float) ($record->total_cost ?? 0);
                        return $data;
                    })
                    ->form($this->batchFormFields())
                    ->before(function (EditAction $action, IngredientBatch $record) {
                        $data = $action->getData();

                        // Guard: total harga cannot go below already-paid amount
                        $newTotal = (float) str_replace(',', '.', $data['total_harga'] ?? $record->total_cost ?? 0);
                        $totalPaid = (float) $record->batchPayments()->sum('amount');
                        if ($totalPaid > $newTotal) {
                            Notification::make()
                                ->danger()
                                ->title('Total harga tidak boleh kurang dari total dibayar')
                                ->body('Total sudah dibayar: Rp' . number_format($totalPaid, 0, ',', '.') . '. Naikkan total harga atau hapus pembayaran di Riwayat Bayar.')
                                ->send();
                            $action->cancel();
                            return;
                        }

                        $rawQty = $data['quantity'] ?? null;
                        if ($rawQty === null) return;
                        $newQty = (float) str_replace(',', '.', $rawQty);
                        $oldQty = (float) $record->quantity;
                        if (abs($oldQty - $newQty) < 0.001) return;

                        $diff = $newQty - $oldQty;
                        $unit = $record->ingredient?->unit ?? '';
                        $batchCode = $record->batch_code ?? '#'.$record->id;
                        $adjType = $diff > 0 ? StockAdjustment::TYPE_INCREASE : StockAdjustment::TYPE_DECREASE;
                        $note = "Batch {$batchCode}: qty {$oldQty} → {$newQty} {$unit}";

                        StockAdjustment::create([
                            'code' => StockReconciliationService::generateAdjustmentCode(),
                            'adjustable_type' => AdjustableType::Ingredient->value,
                            'ingredient_id' => $record->ingredient_id,
                            'adjustment_type' => $adjType,
                            'category' => StockAdjustment::CAT_CORRECTION,
                            'quantity' => abs($diff),
                            'quantity_before' => $oldQty,
                            'quantity_after' => $newQty,
                            'reason' => $note,
                            'reported_by' => Auth::id(),
                            'adjusted_at' => now(),
                            'status' => StockAdjustment::STATUS_ACTIVE,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Penyesuaian stok otomatis tercatat')
                            ->body($note)
                            ->send();
                    })
                    ->using(function (array $data, $livewire, IngredientBatch $record, $table): void {
                        $totalCost = (float) ($data['total_harga'] ?? 0);
                        $initialQty = (float) ($record->initial_quantity ?: $record->quantity);

                        $data['total_cost'] = $totalCost > 0 ? $totalCost : null;
                        $data['cost_per_unit'] = $initialQty > 0 ? $totalCost / $initialQty : 0;
                        unset($data['total_harga'], $data['sudah_lunas'], $data['total_dibayar']);

                        $record->update($data);
                        $record->recalculatePaymentStatus();
                    }),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, IngredientBatch $record) {
                        if ($record->stockMovements()->exists()) {
                            Notification::make()
                                ->warning()
                                ->title('Batch tidak dapat dihapus')
                                ->body('Batch ini memiliki riwayat pemakaian. Batch telah dinonaktifkan.')
                                ->send();
                            
                            $record->update(['quantity' => 0, 'status' => IngredientBatch::STATUS_INACTIVE]);
                            $action->cancel();
                            return;
                        }

                        if ((float) $record->quantity > 0) {
                            $unit = $record->ingredient?->unit ?? '';
                            $batchCode = $record->batch_code ?? '#'.$record->id;
                            $note = "Hapus batch {$batchCode}: sisa {$record->quantity} {$unit}";

                            StockAdjustment::create([
                                'code' => StockReconciliationService::generateAdjustmentCode(),
                                'adjustable_type' => AdjustableType::Ingredient->value,
                                'ingredient_id' => $record->ingredient_id,
                                'adjustment_type' => StockAdjustment::TYPE_DECREASE,
                                'category' => StockAdjustment::CAT_CORRECTION,
                                'quantity' => (float) $record->quantity,
                                'quantity_before' => (float) $record->quantity,
                                'quantity_after' => 0,
                                'reason' => $note,
                                'reported_by' => Auth::id(),
                                'adjusted_at' => now(),
                                'status' => StockAdjustment::STATUS_ACTIVE,
                            ]);
                        }
                    }),
                Action::make('mark_expired')
                    ->label('Tandai Kedaluwarsa')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (IngredientBatch $record): bool =>
                        $record->expiry_date
                        && $record->expiry_date->isPast()
                        && (float) $record->quantity > 0
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Tandai Batch Kedaluwarsa')
                    ->modalDescription(fn (IngredientBatch $record): string =>
                        "Batch {$record->batch_code} sudah kedaluwarsa sejak "
                        . $record->expiry_date->format('d M Y')
                        . ". Stok sisa {$record->quantity} " . ($record->ingredient?->unit ?? '')
                        . " akan dihapus dan dicatat sebagai waste."
                    )
                    ->modalSubmitActionLabel('Ya, Tandai')
                    ->action(function (IngredientBatch $record) {
                        $unit = $record->ingredient?->unit ?? '';
                        $batchCode = $record->batch_code ?? '#'.$record->id;
                        $qty = (float) $record->quantity;

                        $adjustment = StockAdjustment::create([
                            'code' => StockReconciliationService::generateAdjustmentCode(),
                            'adjustable_type' => AdjustableType::Ingredient->value,
                            'ingredient_id' => $record->ingredient_id,
                            'adjustment_type' => StockAdjustment::TYPE_DECREASE,
                            'category' => StockAdjustment::CAT_EXPIRED,
                            'quantity' => $qty,
                            'quantity_before' => $qty,
                            'quantity_after' => 0,
                            'reason' => "Batch {$batchCode} kedaluwarsa: {$qty} {$unit}",
                            'reported_by' => Auth::id(),
                            'adjusted_at' => now(),
                            'status' => StockAdjustment::STATUS_ACTIVE,
                        ]);

                        StockMovement::create([
                            'ingredient_id' => $record->ingredient_id,
                            'ingredient_batch_id' => $record->id,
                            'stock_adjustment_id' => $adjustment->id,
                            'movement_type' => 'waste',
                            'source_type' => 'stock_adjustment',
                            'source_id' => (string) $adjustment->id,
                            'quantity_before' => $qty,
                            'quantity_change' => -$qty,
                            'quantity_after' => 0,
                            'unit_cost' => $record->cost_per_unit,
                            'notes' => "Batch {$batchCode} kedaluwarsa",
                            'recorded_by' => Auth::id(),
                        ]);

                        $record->update(['quantity' => 0, 'status' => IngredientBatch::STATUS_INACTIVE]);

                        Notification::make()
                            ->success()
                            ->title('Batch ditandai kedaluwarsa')
                            ->body("Stok {$batchCode} telah dihapus dan dicatat di Penyesuaian Stok.")
                            ->send();
                    }),
            ])
            ->toolbarActions([])
            ->defaultSort('expiry_date', 'asc');
    }

    private function batchFormFields(bool $isCreate = false): array
    {
        $fields = [
            TextInput::make('quantity')
                ->label('Jumlah')
                ->required()
                ->minValue(0)
                ->maxValue(999999)
                ->step(fn () => in_array($this->record->unit, ['gram', 'ml']) ? 1 : 0.001)
                ->type('text')
                ->extraInputAttributes(\App\Filament\Forms\Components\QuantityInput::inputAttributes())
                ->dehydrateStateUsing(fn ($state) => \App\Filament\Forms\Components\QuantityInput::normalizeState($state))
                ->suffix(fn () => ' '.$this->record->unit),
            DatePicker::make('expiry_date')
                ->label('Tanggal Kedaluwarsa')
                ->native(false)
                ->required(fn () => $this->record->batch_mode === BatchMode::Fefo->value)
                ->helperText(fn () => $this->record->batch_mode === BatchMode::Fefo->value
                    ? 'Wajib untuk mode FEFO'
                    : null),
            DateTimePicker::make('received_at')
                ->label('Waktu Diterima')
                ->required()
                ->default(now())
                ->native(false),
            TextInput::make('supplier_name')
                ->label('Supplier')
                ->maxLength(255)
                ->placeholder('Nama supplier...'),
            TextInput::make('total_harga')
                ->label('Total Harga')
                ->required()
                ->minValue(0)
                ->maxValue(999999)
                ->numeric()
                ->type('text')
                ->mask('999999')
                ->live()
                ->prefix('Rp'),
        ];

        if ($isCreate) {
            $fields[] = TextInput::make('total_dibayar')
                ->label('Total Dibayar')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->type('text')
                ->mask('999999')
                ->prefix('Rp')
                ->disabled(fn (Get $get): bool => (bool) $get('sudah_lunas'))
                ->dehydrated()
                ->rules([
                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                        $totalHarga = (float) str_replace('.', '', (string) ($get('total_harga') ?? 0));
                        if ((float) $value > $totalHarga) {
                            $fail('Total dibayar tidak boleh melebihi total harga (Rp ' . number_format($totalHarga, 0, ',', '.') . ').');
                        }
                    },
                ]);
            $fields[] = Toggle::make('sudah_lunas')
                ->label('Sudah Lunas')
                ->helperText('Centang jika utang langsung lunas — Total Dibayar otomatis terisi total harga')
                ->live()
                ->default(false)
                ->afterStateUpdated(function (Set $set, Get $get, bool $state): void {
                    if ($state) {
                        $set('total_dibayar', $get('total_harga') ?? 0);
                    } else {
                        $set('total_dibayar', 0);
                    }
                });
        }

        $fields[] = Toggle::make('allow_expired_usage')
            ->label('Bisa dipakai meskipun kedaluwarsa')
            ->helperText('Batch ini tetap bisa dipakai FEFO walau sudah kedaluwarsa')
            ->visible(fn () => $this->record->batch_mode === BatchMode::Fefo->value)
            ->default(false);

        return $fields;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->url(static::$resource::getUrl('index')),
        ];
    }
}
