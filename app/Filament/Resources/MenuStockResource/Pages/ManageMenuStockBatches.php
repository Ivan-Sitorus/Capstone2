<?php

namespace App\Filament\Resources\MenuStockResource\Pages;

use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Resources\MenuStockResource;
use App\Models\MenuStock;
use App\Models\MenuStockBatch;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ManageMenuStockBatches extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = MenuStockResource::class;

    protected string $view = 'filament.pages.manage-menu-stock-batches';

    public MenuStock $record;

    public function mount(MenuStock $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string|Htmlable
    {
        return "Batch Stok - {$this->record->menu->name}";
    }

    public function table(Table $table): Table
    {
        $unit = $this->record->unit;

        return $table
            ->query(MenuStockBatch::where('menu_stock_id', $this->record->id))
            ->columns([
                TextColumn::make('id')
                    ->label('Batch ID')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' '.$unit)
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => ! $record->expiry_date ? 'gray' : ($record->expiry_date->isPast() ? 'danger' : ($record->expiry_date->diffInDays(now()) < 7 ? 'warning' : 'success'))),
                TextColumn::make('received_at')
                    ->label('Received At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cost_per_unit')
                    ->label('Cost/Unit')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(MenuStockBatch::class)
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->minValue(0)
                            ->type('text')
                            ->stripCharacters('.')
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                            ->extraInputAttributes(NumberInputHelper::decimal())
                            ->suffix(fn () => ' '.$this->record->unit),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->native(false)
                            ->required(fn () => $this->record->batch_mode === MenuStock::BATCH_MODE_FEFO)
                            ->helperText(fn () => $this->record->batch_mode === MenuStock::BATCH_MODE_FEFO
                                ? 'Required for FEFO mode'
                                : null),
                        DateTimePicker::make('received_at')
                            ->label('Received At')
                            ->required()
                            ->default(now())
                            ->native(false),
                        TextInput::make('cost_per_unit')
                            ->label('Cost per Unit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->type('text')
                            ->stripCharacters('.')
                            ->extraInputAttributes(NumberInputHelper::integer())
                            ->prefix('Rp'),
                    ])
                    ->using(function (array $data): MenuStockBatch {
                        return $this->record->batches()->create($data);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->minValue(0)
                            ->type('text')
                            ->stripCharacters('.')
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                            ->extraInputAttributes(NumberInputHelper::decimal())
                            ->suffix(fn () => ' '.$this->record->unit),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->native(false)
                            ->required(fn () => $this->record->batch_mode === MenuStock::BATCH_MODE_FEFO)
                            ->helperText(fn () => $this->record->batch_mode === MenuStock::BATCH_MODE_FEFO
                                ? 'Required for FEFO mode'
                                : null),
                        DateTimePicker::make('received_at')
                            ->label('Received At')
                            ->required()
                            ->default(now())
                            ->native(false),
                        TextInput::make('cost_per_unit')
                            ->label('Cost per Unit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->type('text')
                            ->stripCharacters('.')
                            ->extraInputAttributes(NumberInputHelper::integer())
                            ->prefix('Rp'),
                    ]),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
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
