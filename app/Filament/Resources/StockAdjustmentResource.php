<?php

namespace App\Filament\Resources;

use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Resources\StockAdjustmentResource\Pages\EditStockAdjustment;
use App\Filament\Resources\StockAdjustmentResource\Pages\ListStockAdjustments;
use App\Filament\Resources\StockAdjustmentResource\Pages\ViewStockAdjustment;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers\MovementsRelationManager;
use App\Models\IngredientBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static bool $shouldRegisterNavigation = true;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Penyesuaian Stok';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'penyesuaian-stok';

    protected static ?string $breadcrumb = 'Penyesuaian Stok';

    protected static ?string $label = 'Penyesuaian Stok';

    protected static ?string $pluralLabel = 'Penyesuaian Stok';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('adjustable_type')
                ->label('Jenis')
                ->options(StockAdjustment::ADJUSTABLE_TYPES)
                ->default(StockAdjustment::ADJUSTABLE_TYPE_INGREDIENT)
                ->required()
                ->native(false)
                ->live(),
            Select::make('ingredient_id')
                ->label('Bahan')
                ->relationship('ingredient', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => $get('adjustable_type') === StockAdjustment::ADJUSTABLE_TYPE_INGREDIENT)
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->unit.')'),
            Select::make('menu_id')
                ->label('Menu')
                ->relationship('menu', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => $get('adjustable_type') === StockAdjustment::ADJUSTABLE_TYPE_MENU),
            Select::make('adjustment_type')
                ->label('Tipe Penyesuaian')
                ->options([
                    StockAdjustment::TYPE_INCREASE => 'Penambahan',
                    StockAdjustment::TYPE_DECREASE => 'Pengurangan',
                ])
                ->required()
                ->native(false)
                ->live(),
            Select::make('category')
                ->label('Kategori')
                ->options(fn (Get $get) => filled($get('adjustment_type'))
                    ? StockAdjustment::getCategoryOptions($get('adjustment_type'))
                    : StockAdjustment::DECREASE_CATEGORIES + StockAdjustment::INCREASE_CATEGORIES)
                ->required()
                ->disabled(fn (Get $get) => ! filled($get('adjustment_type')))
                ->native(false),
            TextInput::make('quantity')
                ->label('Jumlah')
                ->required()
                ->numeric(fn (Get $get) => $get('adjustable_type') === StockAdjustment::ADJUSTABLE_TYPE_MENU)
                ->prefix(fn (Get $get) => $get('adjustment_type') === StockAdjustment::TYPE_DECREASE ? '-' : '+')
                ->suffix(fn (Get $get) => $get('adjustable_type') === StockAdjustment::ADJUSTABLE_TYPE_MENU
                    ? ' porsi'
                    : ($get('ingredient_id')
                        ? ' ' . (\App\Models\Ingredient::find($get('ingredient_id'))?->unit ?? '')
                        : '')
                )
                ->extraAttributes(fn (Get $get) => $get('adjustable_type') === StockAdjustment::ADJUSTABLE_TYPE_MENU
                    ? NumberInputHelper::integer()
                    : NumberInputHelper::decimal()),
            Textarea::make('reason')
                ->label('Catatan')
                ->rows(3)
                ->maxLength(65535),
            Select::make('reported_by')
                ->label('Dilaporkan Oleh')
                ->relationship('reportedBy', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => Auth::id()),
            DateTimePicker::make('adjusted_at')
                ->label('Tanggal Kejadian')
                ->seconds(false)
                ->default(now()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['ingredient', 'menu', 'reportedBy']))
            ->columns([
                TextColumn::make('adjusted_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable(),
                TextColumn::make('adjustment_type')
                    ->label('Tipe Penyesuaian')
                    ->badge()
                    ->color(fn (string $state): string => $state === StockAdjustment::TYPE_INCREASE ? 'primary' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === StockAdjustment::TYPE_INCREASE ? 'Penambahan' : 'Pengurangan'),
                TextColumn::make('adjustable_type')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => StockAdjustment::ADJUSTABLE_TYPES[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Nama')
                    ->state(fn (StockAdjustment $record) =>
                        $record->isMenuAdjustment()
                            ? $record->menu?->name
                            : $record->ingredient?->name
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn ($state) => StockAdjustment::DECREASE_CATEGORIES[$state]
                        ?? StockAdjustment::INCREASE_CATEGORIES[$state]
                        ?? $state)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'cancelled' ? 'danger' : 'success')
                    ->formatStateUsing(fn (?string $state): string => $state === 'cancelled' ? 'Dibatalkan' : 'Aktif')
                    ->sortable(),
                TextColumn::make('cancel_reason')
                    ->label('Alasan Batal')
                    ->formatStateUsing(fn ($state, StockAdjustment $record): string =>
                        $record->status === 'cancelled' ? ($state ?? '-') : '-'
                    ),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, StockAdjustment $record) =>
                        ($record->adjustment_type === StockAdjustment::TYPE_INCREASE ? '+ ' : '- ')
                        . number_format(abs((float) $state), 0, ',', '.')
                        . ' ' . (
                            $record->isMenuAdjustment()
                                ? 'porsi'
                                : ($record->ingredient?->unit ?? '')
                        )
                    )
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Catatan')
                    ->limit(50),
                TextColumn::make('reportedBy.name')
                    ->label('Dilaporkan Oleh')
                    ->default('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('adjustable_type')
                    ->label('Jenis')
                    ->options(StockAdjustment::ADJUSTABLE_TYPES),
                SelectFilter::make('adjustment_type')
                    ->label('Tipe')
                    ->options([
                        StockAdjustment::TYPE_INCREASE => 'Penambahan',
                        StockAdjustment::TYPE_DECREASE => 'Pengurangan',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(StockAdjustment::DECREASE_CATEGORIES + StockAdjustment::INCREASE_CATEGORIES),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail')
                    ->url(fn (StockAdjustment $record) => static::getUrl('view', ['record' => $record])),
                Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->disabled(fn (StockAdjustment $record): bool => $record->status === 'cancelled')
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
                    }),
            ])
            ->toolbarActions([])
            ->defaultSort('adjusted_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockAdjustments::route('/'),
            'view' => ViewStockAdjustment::route('/{record}'),
            'edit' => EditStockAdjustment::route('/{record}/edit'),
        ];
    }
}
