<?php

namespace App\Filament\Resources\CashierHistoryResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Concerns\HasOrderStatusBadge;
use App\Filament\Resources\CashierHistoryResource;
use App\Filament\Resources\OrderResource;
use App\Models\CashierHistory;
use App\Models\Order;
use App\Services\CashierHistoryService;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ViewCashierHistory extends Page implements HasTable
{
    use InteractsWithTable;
    use HasOrderStatusBadge;

    protected static string $resource = CashierHistoryResource::class;

    public CashierHistory $record;

    public function mount(CashierHistory $record): void
    {
        $this->record = $record;
    }

    public function content(Schema $schema): Schema
    {
        $session = $this->record;

        return $schema->components([
            Section::make('Informasi Sesi')
                ->schema([
                    TextEntry::make('staff_name')
                        ->label('Nama Kasir')
                        ->state($session->user->name),
                    TextEntry::make('staff_email')
                        ->label('Email')
                        ->state($session->user->email),
                    TextEntry::make('started_at')
                        ->label('Waktu Masuk')
                        ->state($session->started_at->format('d M Y, H:i:s')),
                    TextEntry::make('ended_at')
                        ->label('Waktu Keluar')
                        ->state($session->ended_at?->format('d M Y, H:i:s') ?? 'Masih Aktif'),
                    TextEntry::make('order_count')
                        ->label('Jumlah Pesanan')
                        ->state(app(CashierHistoryService::class)->getOrderCount($session)),
                ])
                ->columns(3),
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        $session = $this->record;
        $endedAt = $session->ended_at ?? now();

        return $table
            ->query(
                Order::with('cafeTable')
                    ->where('cashier_id', $session->user_id)
                    ->whereBetween('created_at', [$session->started_at, $endedAt])
                    ->latest()
            )
            ->columns([
                TextColumn::make('order_code')
                    ->label('Kode Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('cafeTable.table_number')
                    ->label('Meja')
                    ->default('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (OrderStatus $state): string => self::getStatusColor($state->value))
                    ->formatStateUsing(fn (OrderStatus $state): string => self::getStatusLabel($state->value)),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('viewOrder')
                    ->label('Lihat Pesanan')
                    ->icon(Heroicon::OutlinedEye)
                    ->infolist(OrderResource::getInfolistComponents())
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalAutofocus(false),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function getTitle(): string
    {
        return 'Detail Sesi Kasir: '.$this->record->user->name;
    }
}
