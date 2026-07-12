<?php

namespace App\Filament\Resources\StaffSessionResource\Pages;

use App\Filament\Resources\StaffSessionResource;
use App\Models\Order;
use App\Models\StaffSession;
use App\Services\StaffSessionService;
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

class ViewStaffSession extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = StaffSessionResource::class;

    public StaffSession $record;

    public function mount(StaffSession $record): void
    {
        $this->record = $record;
    }

    public function content(Schema $schema): Schema
    {
        $session = $this->record;
        $staff = $session->user;
        $typeLabel = match ($session->type) {
            'cashier' => 'Kasir',
            'kitchen' => 'Dapur',
            default => $session->type,
        };

        return $schema->components([
            Section::make('Informasi Sesi')
                ->schema([
                    TextEntry::make('staff_name')
                        ->label('Nama Staff')
                        ->state($staff->name),
                    TextEntry::make('staff_email')
                        ->label('Email')
                        ->state($staff->email),
                    TextEntry::make('staff_role')
                        ->label('Role')
                        ->state($typeLabel),
                    TextEntry::make('started_at')
                        ->label('Waktu Masuk')
                        ->state($session->started_at->format('d M Y, H:i')),
                    TextEntry::make('ended_at')
                        ->label('Waktu Keluar')
                        ->state($session->ended_at?->format('d M Y, H:i') ?? 'Masih Aktif'),
                    TextEntry::make('order_count')
                        ->label('Jumlah Pesanan')
                        ->state(app(StaffSessionService::class)->getOrderCount($session)),
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
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'diproses' => 'info',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        default => $state,
                    }),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('viewOrder')
                    ->label('Lihat Pesanan')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Order $record) => \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function getTitle(): string
    {
        $session = $this->record;
        $typeLabel = match ($session->type) {
            'cashier' => 'Kasir',
            'kitchen' => 'Dapur',
            default => $session->type,
        };

        return "Detail Sesi {$typeLabel} — {$session->user->name}";
    }
}
