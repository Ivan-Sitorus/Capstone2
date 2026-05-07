<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ReceivableResource\Pages\ListReceivables;
use App\Filament\Resources\ReceivableResource\Pages\EditReceivable;
use App\Filament\Resources\ReceivableResource\Pages\ViewReceivable;
use App\Filament\Resources\ReceivableResource\Pages;
use App\Models\Receivable;
use App\Models\Order;
use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Helpers\TextInputHelper;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReceivableResource extends Resource
{
    protected static ?string $model = Receivable::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | \UnitEnum | null $navigationGroup = 'Finance Details';

    protected static ?string $navigationLabel = 'Receivables';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('order_id')
                ->label('Order')
                ->relationship('order', 'order_code')
                ->searchable()
                ->nullable()
                ->preload()
                ->afterStateUpdated(function ($state, Forms\Set $set) {
                    if ($state) {
                        $order = Order::find($state);
                        if ($order) {
                            // Auto-fill customer name from order's customer
                            if ($order->customer) {
                                $set('customer_name', $order->customer->name);
                            }
                            // Auto-fill amount from order's total_amount
                            $set('amount', $order->total_amount);
                        }
                    }
                }),
            TextInput::make('customer_name')
                ->label('Customer Name')
                ->required()
                ->maxLength(255)
                ->extraInputAttributes(TextInputHelper::string()),
            TextInput::make('amount')
                ->label('Total Amount')
                ->required()
                ->type('text')
                ->prefix('Rp')
                ->stripCharacters('.')
                ->extraInputAttributes(NumberInputHelper::decimal())
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : ''),
            DatePicker::make('invoice_date')
                ->label('Invoice Date')
                ->required()
                ->default(now())
                ->native(false),
            DatePicker::make('due_date')
                ->label('Due Date')
                ->required()
                ->default(now()->addDays(30))
                ->native(false),
            Select::make('status')
                ->label('Status')
                ->options([
                    Receivable::STATUS_PENDING => 'Pending',
                    Receivable::STATUS_PARTIAL => 'Partial',
                    Receivable::STATUS_PAID => 'Paid',
                    Receivable::STATUS_OVERDUE => 'Overdue',
                ])
                ->required()
                ->default(Receivable::STATUS_PENDING)
                ->native(false),
            TextInput::make('paid_amount')
                ->label('Paid Amount')
                ->required()
                ->type('text')
                ->prefix('Rp')
                ->stripCharacters('.')
                ->extraInputAttributes(NumberInputHelper::decimal())
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : ''),
            Textarea::make('notes')
                ->label('Notes')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Receivable Information')
                ->schema([
                    TextEntry::make('customer_name')
                        ->label('Customer Name'),
                    TextEntry::make('invoice_date')
                        ->label('Invoice Date')
                        ->date('d M Y'),
                    TextEntry::make('amount')
                        ->label('Total Amount')
                        ->money('IDR'),
                    TextEntry::make('paid_amount')
                        ->label('Paid Amount')
                        ->money('IDR'),
                    TextEntry::make('remaining_amount')
                        ->label('Remaining Amount')
                        ->money('IDR')
                        ->color(fn (Receivable $record): string => $record->remaining_amount > 0 ? 'danger' : 'success'),
                    TextEntry::make('due_date')
                        ->label('Due Date')
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
                        ->label('Notes')
                        ->visible(fn ($record) => $record->notes !== null && $record->notes !== ''),
                ])->columns(2),

            Section::make('Order Information')
                ->visible(fn (Receivable $record): bool => $record->order !== null)
                ->schema([
                    TextEntry::make('order.order_code')
                        ->label('Order Code')
                        ->url(fn (Receivable $record): ?string => $record->order 
                            ? route('filament.admin.resources.orders.view', $record->order) 
                            : null)
                        ->openUrlInNewTab(),
                    TextEntry::make('order.status')
                        ->label('Order Status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'selesai' => 'success',
                            'diproses' => 'warning',
                            default => 'gray',
                        }),
                    TextEntry::make('order.total_amount')
                        ->label('Order Total')
                        ->money('IDR'),
                ])->columns(3),

            Section::make('Order Items')
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

            Section::make('No Linked Order')
                ->visible(fn (Receivable $record): bool => $record->order === null)
                ->schema([
                    TextEntry::make('')
                        ->label('')
                        ->content('This receivable is not linked to any order.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_code')
                    ->label('Order')
                    ->searchable()
                    ->url(fn (Receivable $record): ?string => $record->order 
                        ? route('filament.admin.resources.orders.view', $record->order) 
                        : null)
                    ->openUrlInNewTab(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn (Receivable $record): ?string => $record->isOverdue() ? 'danger' : null),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('remaining_amount')
                    ->label('Remaining')
                    ->money('IDR')
                    ->sortable(),
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
                        Receivable::STATUS_PARTIAL => 'Partial',
                        Receivable::STATUS_PAID => 'Paid',
                        Receivable::STATUS_OVERDUE => 'Overdue',
                    ]),
                Filter::make('due_date')
                    ->label('Due Date Range')
                    ->form([
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
            'view' => ViewReceivable::route('/{record}'),
            'edit' => EditReceivable::route('/{record}/edit'),
        ];
    }
}