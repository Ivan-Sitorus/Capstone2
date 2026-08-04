<?php

namespace App\Filament\Resources\ReceivableResource\Pages;

use App\Filament\Forms\Components\NumericInput;
use App\Filament\Resources\ReceivableResource;
use App\Models\Order;
use App\Models\OrderPayment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class RiwayatBayar extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ReceivableResource::class;

    protected static ?string $breadcrumb = 'Riwayat Bayar';

    public Order $order;

    public function mount(Order $record): void
    {
        $this->order = $record;
    }

    public function getTitle(): string
    {
        return "Riwayat Bayar: {$this->order->order_code}";
    }

    public function content(Schema $schema): Schema
    {
        $totalPaid = (float) $this->order->orderPayments()->sum('amount');
        $remaining = (float) $this->order->total_amount - $totalPaid;

        return $schema->components([
            Section::make('Ringkasan Pembayaran')
                ->schema([
                    TextEntry::make('total')
                        ->label('Total Pesanan')
                        ->state('Rp' . number_format($this->order->total_amount, 0, ',', '.')),
                    TextEntry::make('dibayar')
                        ->label('Total Dibayar')
                        ->state('Rp' . number_format($totalPaid, 0, ',', '.'))
                        ->color('success'),
                    TextEntry::make('sisa')
                        ->label('Sisa')
                        ->state('Rp' . number_format($remaining, 0, ',', '.'))
                        ->color($remaining > 0 ? 'danger' : 'success'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->state($this->order->status)
                        ->badge()
                        ->color(fn () => $this->order->status === 'selesai' ? 'success' : 'warning')
                        ->formatStateUsing(fn () => $this->order->status === 'selesai' ? 'Lunas' : 'Belum Lunas'),
                ])->columns(4),
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(OrderPayment::where('order_id', $this->order->id))
            ->columns([
                TextColumn::make('payment_date')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris' => 'QRIS',
                        default => $state ?? '-',
                    }),
            ])
            ->defaultSort('payment_date', 'desc')
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Pembayaran')
                    ->form([
                        NumericInput::apply(TextInput::make('amount'), maxDigits: 9)
                            ->label('Jumlah')
                            ->required()
                            ->minValue(1)
                            ->prefix('Rp'),
                        Select::make('payment_method')
                            ->label('Metode')
                            ->options([
                                'cash' => 'Tunai',
                                'transfer' => 'Transfer',
                                'qris' => 'QRIS',
                            ])
                            ->required(),
                        DateTimePicker::make('payment_date')
                            ->label('Tanggal')
                            ->required(),
                    ])
                    ->after(function () {
                        $this->order->refresh();
                        $this->order->recalculatePaymentStatus();
                    }),
                DeleteAction::make()
                    ->modalHeading('Hapus Pembayaran')
                    ->modalDescription('Yakin ingin menghapus pembayaran ini? Sisa pembayaran akan dihitung ulang sehingga status pembayaran tetap sesuai.')
                    ->after(function () {
                        $this->order->refresh();
                        $this->order->recalculatePaymentStatus();
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('catat_pembayaran')
                ->label('Catat Pembayaran')
                ->icon('heroicon-o-plus')
                ->modalHeading('Catat Pembayaran Baru')
                ->form([
                    NumericInput::apply(TextInput::make('amount'), maxDigits: 9)
                        ->label('Jumlah')
                        ->required()
                        ->minValue(1)
                        ->prefix('Rp'),
                    Select::make('payment_method')
                        ->label('Metode')
                        ->options([
                            'cash' => 'Tunai',
                            'transfer' => 'Transfer',
                            'qris' => 'QRIS',
                        ])
                        ->default('cash')
                        ->required(),
                    DateTimePicker::make('payment_date')
                        ->label('Tanggal')
                        ->required()
                        ->default(now()),
                ])
                ->action(function (array $data) {
                    $totalPaid = (float) $this->order->orderPayments()->sum('amount');
                    $remaining = (float) $this->order->total_amount - $totalPaid;

                    if ((float) $data['amount'] > $remaining) {
                        Notification::make()
                            ->danger()
                            ->title('Pembayaran melebihi sisa')
                            ->body("Sisa pembayaran: Rp" . number_format($remaining, 0, ',', '.'))
                            ->send();
                        return;
                    }

                    $this->order->orderPayments()->create([
                        'amount' => $data['amount'],
                        'payment_method' => $data['payment_method'],
                        'payment_date' => $data['payment_date'],
                    ]);

                    $this->order->recalculatePaymentStatus();

                    Notification::make()
                        ->success()
                        ->title('Pembayaran tercatat')
                        ->send();
                }),
        ];
    }
}
