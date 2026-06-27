<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Resources\StockResource;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ManageBatches extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = StockResource::class;

    protected string $view = 'filament.pages.manage-batches';

    public Ingredient $record;

    public function mount(Ingredient $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string|Htmlable
    {
        return "Batch Stok - {$this->record->name}";
    }

    public function table(Table $table): Table
    {
        $unit = $this->record->unit;

        return $table
            ->query(IngredientBatch::where('ingredient_id', $this->record->id))
            ->columns([
                TextColumn::make('batch_code')
                    ->label('Kode Batch')
                    ->default('-'),
                TextColumn::make('id')
                    ->label('ID Batch')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.').' '.$unit)
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Tanggal Kedaluwarsa')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->expiry_date && $record->expiry_date->isPast() ? 'danger' : null),
                TextColumn::make('received_at')
                    ->label('Tanggal Diterima')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cost_per_unit')
                    ->label('Harga/Unit')
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(IngredientBatch::class)
                    ->form([
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->required()
                            ->minValue(0)
                            ->step(0.1)
                            ->type('text')
                            ->stripCharacters('.')
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                            ->extraInputAttributes(NumberInputHelper::decimal())
                            ->suffix(fn () => ' '.$this->record->unit),
                        DatePicker::make('expiry_date')
                            ->label('Tanggal Kedaluwarsa')
                            ->native(false)
                            ->required(fn () => $this->record->batch_mode === Ingredient::BATCH_MODE_FEFO)
                            ->helperText(fn () => $this->record->batch_mode === Ingredient::BATCH_MODE_FEFO
                                ? 'Wajib untuk mode FEFO'
                                : null),
                        DateTimePicker::make('received_at')
                            ->label('Tanggal Diterima')
                            ->required()
                            ->default(now())
                            ->native(false),
                        TextInput::make('cost_per_unit')
                            ->label('Harga per Unit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->type('text')
                            ->stripCharacters('.')
                            ->extraInputAttributes(NumberInputHelper::integer())
                            ->prefix('Rp'),
                    ])
                    ->using(function (array $data): IngredientBatch {
                        return $this->record->batches()->create($data);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->required()
                            ->minValue(0)
                            ->step(0.1)
                            ->type('text')
                            ->stripCharacters('.')
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                            ->extraInputAttributes(NumberInputHelper::decimal())
                            ->suffix(fn () => ' '.$this->record->unit),
                        DatePicker::make('expiry_date')
                            ->label('Tanggal Kedaluwarsa')
                            ->native(false)
                            ->required(fn () => $this->record->batch_mode === Ingredient::BATCH_MODE_FEFO)
                            ->helperText(fn () => $this->record->batch_mode === Ingredient::BATCH_MODE_FEFO
                                ? 'Wajib untuk mode FEFO'
                                : null),
                        DateTimePicker::make('received_at')
                            ->label('Tanggal Diterima')
                            ->required()
                            ->default(now())
                            ->native(false),
                        TextInput::make('cost_per_unit')
                            ->label('Harga per Unit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->type('text')
                            ->stripCharacters('.')
                            ->extraInputAttributes(NumberInputHelper::integer())
                            ->prefix('Rp'),
                    ]),
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
                        }
                    }),
            ])
            ->toolbarActions([])
            ->defaultSort('expiry_date', 'asc');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(static::$resource::getUrl('index')),
        ];
    }
}
