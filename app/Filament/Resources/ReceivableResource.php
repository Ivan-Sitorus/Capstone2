<?php

namespace App\Filament\Resources;

use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Helpers\TextInputHelper;
use App\Filament\Resources\ReceivableResource\Pages\CreateReceivable;
use App\Filament\Resources\ReceivableResource\Pages\EditReceivable;
use App\Filament\Resources\ReceivableResource\Pages\ListReceivables;
use App\Filament\Resources\ReceivableResource\Pages\ViewReceivable;
use App\Models\Menu;
use App\Models\Receivable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReceivableResource extends Resource
{
    protected static ?string $model = Receivable::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Piutang';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Repeater::make('items')
                ->label('Item Pesanan')
                ->schema([
                    Select::make('menu_id')
                        ->label('Menu')
                        ->options(fn () => Menu::where('is_available', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),
                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required(),
                ])
                ->columns(2)
                ->reorderable(false)
                ->addActionLabel('+ Tambah Item')
                ->columnSpanFull()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    if (! is_array($state)) {
                        return;
                    }
                    $menuIds = collect($state)->pluck('menu_id')->filter()->unique();
                    $menus = Menu::whereIn('id', $menuIds)->pluck('price', 'id');
                    $total = collect($state)->sum(fn ($item) => ($menus[$item['menu_id']] ?? 0) * (int) ($item['quantity'] ?? 0));
                    $set('amount', $total);
                }),
            TextInput::make('customer_name')
                ->label('Nama Pelanggan')
                ->required()
                ->maxLength(255)
                ->extraInputAttributes(TextInputHelper::string()),
            TextInput::make('amount')
                ->label('Jumlah Total')
                ->required()
                ->type('text')
                ->prefix('Rp')
                ->disabled()
                ->dehydrated(false)
                ->extraInputAttributes(NumberInputHelper::decimal())
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : ''),
            DatePicker::make('invoice_date')
                ->label('Tanggal Invoice')
                ->required()
                ->default(now())
                ->native(false),
            DatePicker::make('due_date')
                ->label('Jatuh Tempo')
                ->required()
                ->default(now()->addDays(30))
                ->native(false),
            Select::make('status')
                ->label('Status')
                ->options([
                    Receivable::STATUS_PENDING => 'Pending',
                    Receivable::STATUS_PARTIAL => 'Cicilan',
                    Receivable::STATUS_PAID => 'Lunas',
                    Receivable::STATUS_OVERDUE => 'Jatuh Tempo',
                ])
                ->required()
                ->default(Receivable::STATUS_PENDING)
                ->native(false)
                ->live()
                ->disabled(fn ($get) => (int) $get('paid_amount') > 0),
            TextInput::make('paid_amount')
                ->label('Jumlah Dibayar')
                ->required()
                ->type('text')
                ->prefix('Rp')
                ->stripCharacters('.')
                ->extraInputAttributes(NumberInputHelper::decimal())
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : '')
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    if ((int) $state > 0) {
                        $set('status', 'partial');
                    }
                }),
            Textarea::make('notes')
                ->label('Catatan')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Piutang')
                ->schema([
                    TextEntry::make('customer_name')
                        ->label('Nama Pelanggan'),
                    TextEntry::make('invoice_date')
                        ->label('Tanggal Invoice')
                        ->date('d M Y'),
                    TextEntry::make('amount')
                        ->label('Jumlah Total')
                        ->money('IDR'),
                    TextEntry::make('paid_amount')
                        ->label('Jumlah Dibayar')
                        ->money('IDR'),
                    TextEntry::make('remaining_amount')
                        ->label('Sisa')
                        ->money('IDR')
                        ->color(fn (Receivable $record): string => $record->remaining_amount > 0 ? 'danger' : 'success'),
                    TextEntry::make('due_date')
                        ->label('Jatuh Tempo')
                        ->date('d M Y')
                        ->color(fn (Receivable $record): string => $record->isOverdue() ? 'danger' : 'gray'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'paid' => 'success',
                            'partial' => 'warning',
                            'overdue' => 'danger',
                            default => 'gray',
                        }),
                    TextEntry::make('notes')
                        ->label('Catatan')
                        ->visible(fn ($record) => $record->notes !== null && $record->notes !== ''),
                ])->columns(2),

            Section::make('Informasi Pesanan')
                ->visible(fn (Receivable $record): bool => $record->order !== null)
                ->schema([
                    TextEntry::make('order.order_code')
                        ->label('Kode Pesanan')
                        ->url(fn (Receivable $record): ?string => $record->order
                            ? route('filament.admin.resources.orders.view', $record->order)
                            : null)
                        ->openUrlInNewTab(),
                    TextEntry::make('order.status')
                        ->label('Status Pesanan')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'selesai' => 'success',
                            'diproses' => 'warning',
                            default => 'gray',
                        }),
                    TextEntry::make('order.total_amount')
                        ->label('Total Pesanan')
                        ->money('IDR'),
                ])->columns(3),

            Section::make('Item Pesanan')
                ->visible(fn (Receivable $record): bool => $record->order !== null && $record->order->items()->exists())
                ->schema([
                    TextEntry::make('order.items')
                        ->label('')
                        ->getStateUsing(fn ($record) => $record->order ? $record->order->items->map(fn ($item) => [
                            'menu' => $item->menu?->name ?? 'Unknown',
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'subtotal' => $item->subtotal,
                        ])->toArray() : []),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_code')
                    ->label('Pesanan')
                    ->searchable()
                    ->url(fn (Receivable $record): ?string => $record->order
                        ? route('filament.admin.resources.orders.view', $record->order)
                        : null)
                    ->openUrlInNewTab(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->label('Tanggal Invoice')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Receivable $record): ?string => $record->isOverdue() ? 'danger' : null),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->money('IDR'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Receivable::STATUS_PAID => 'success',
                        Receivable::STATUS_PARTIAL => 'warning',
                        Receivable::STATUS_OVERDUE => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Receivable::STATUS_PENDING => 'Pending',
                        Receivable::STATUS_PARTIAL => 'Cicilan',
                        Receivable::STATUS_PAID => 'Lunas',
                        Receivable::STATUS_OVERDUE => 'Jatuh Tempo',
                    ]),
                Filter::make('due_date')
                    ->label('Rentang Jatuh Tempo')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->modal(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceivables::route('/'),
            'create' => CreateReceivable::route('/create'),
            'view' => ViewReceivable::route('/{record}'),
            'edit' => EditReceivable::route('/{record}/edit'),
        ];
    }
}
