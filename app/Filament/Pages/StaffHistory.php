<?php

namespace App\Filament\Pages;

use App\Models\CashierSession;
use App\Models\KitchenSession;
use App\Services\StaffSessionService;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengguna';

    protected static ?string $navigationLabel = 'Riwayat Login Staff';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Riwayat Login Staff';

    public int $activeTab = 1;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('StaffTabs')
                ->livewireProperty('activeTab')
                ->tabs([
                    Tab::make('Kasir')
                        ->icon('heroicon-o-calculator')
                        ->schema([
                            EmbeddedTable::make(),
                        ]),
                    Tab::make('Dapur')
                        ->icon('heroicon-o-fire')
                        ->schema([
                            EmbeddedTable::make(),
                        ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama'),
                TextColumn::make('user.email')
                    ->label('Email'),
                TextColumn::make('started_at')
                    ->label('Waktu Masuk')
                    ->dateTime('d M Y, H:i:s'),
                TextColumn::make('ended_at')
                    ->label('Waktu Keluar')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d M Y, H:i:s') : '—'),
                TextColumn::make('order_count')
                    ->label('Jumlah Pesanan')
                    ->getStateUsing(fn ($record) => app(StaffSessionService::class)->getOrderCount($record)),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.pages.staff-session-detail', [
                        'type' => $record instanceof CashierSession ? 'cashier' : 'kitchen',
                        'session' => $record->id,
                    ])),
            ])
            ->defaultSort('started_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        return match ($this->activeTab) {
            1 => CashierSession::with('user')->orderByDesc('started_at'),
            2 => KitchenSession::with('user')->orderByDesc('started_at'),
            default => CashierSession::with('user')->orderByDesc('started_at'),
        };
    }

    public function updatedActiveTab(): void
    {
        $this->resetTable();
    }
}
