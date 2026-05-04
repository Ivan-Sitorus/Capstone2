<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
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
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ManageBatches extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = \App\Filament\Resources\IngredientResource::class;

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
                TextColumn::make('id')
                    ->label('Batch ID')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' ' . $unit)
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => !$record->expiry_date ? 'gray' : ($record->expiry_date->isPast() ? 'danger' : ($record->expiry_date->diffInDays(now()) < 7 ? 'warning' : 'success'))),
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
                    ->model(IngredientBatch::class)
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.1)
                            ->extraInputAttributes([
                                'min' => '0',
                                'onkeydown' => "return !(event.key.length===1&&!/[0-9.]/.test(event.key))"
                            ])
                            ->suffix(fn () => ' ' . $this->record->unit),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->nullable()
                            ->native(false),
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
                        ->extraInputAttributes([
                            'min' => '0',
                            'onkeydown' => "return !(event.key.length===1&&!/[0-9]/.test(event.key))"
                        ])
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
                            ->label('Quantity')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.1)
                            ->extraInputAttributes([
                                'min' => '0',
                                'onkeydown' => "return !(event.key.length===1&&!/[0-9.]/.test(event.key))"
                            ])
                            ->suffix(fn () => ' ' . $this->record->unit),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->nullable()
                            ->native(false),
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
                        ->extraInputAttributes([
                            'min' => '0',
                            'onkeydown' => "return !(event.key.length===1&&!/[0-9]/.test(event.key))"
                        ])
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
