<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Forms\Components\NumericInput;
use App\Filament\Resources\StockResource;
use App\Models\BatchPayment;
use App\Models\IngredientBatch;
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

class RiwayatBayarBatch extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = StockResource::class;

    protected static ?string $breadcrumb = 'Riwayat Bayar';

    public IngredientBatch $batch;

    public function mount(IngredientBatch $record): void
    {
        $this->batch = $record;
    }

    public function getTitle(): string
    {
        return "Riwayat Bayar: {$this->batch->batch_code}";
    }

    public function content(Schema $schema): Schema
    {
        $totalPaid = (float) $this->batch->batchPayments()->sum('amount');
        $totalCost = (float) ($this->batch->total_cost ?? 0);
        $remaining = $totalCost - $totalPaid;

        return $schema->components([
            Section::make('Ringkasan Pembayaran Supplier')
                ->schema([
                    TextEntry::make('total')
                        ->label('Total Utang')
                        ->state('Rp' . number_format($totalCost, 0, ',', '.')),
                    TextEntry::make('dibayar')
                        ->label('Total Dibayar')
                        ->state('Rp' . number_format($totalPaid, 0, ',', '.'))
                        ->color('success'),
                    TextEntry::make('sisa')
                        ->label('Sisa Utang')
                        ->state('Rp' . number_format(max(0, $remaining), 0, ',', '.'))
                        ->color($remaining > 0 ? 'danger' : 'success'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->state($this->batch->payment_status)
                        ->badge()
                        ->color(fn () => $this->batch->payment_status === 'lunas' ? 'success' : 'warning')
                        ->formatStateUsing(fn () => $this->batch->payment_status === 'lunas' ? 'Lunas' : 'Belum Lunas'),
                ])->columns(4),
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(BatchPayment::where('ingredient_batch_id', $this->batch->id))
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
                        $this->batch->refresh();
                        $this->batch->recalculatePaymentStatus();
                    }),
                DeleteAction::make()
                    ->modalHeading('Hapus Pembayaran')
                    ->modalDescription('Yakin ingin menghapus pembayaran ini? Sisa utang akan dihitung ulang sehingga status utang tetap sesuai.')
                    ->after(function () {
                        $this->batch->refresh();
                        $this->batch->recalculatePaymentStatus();
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('catat_pembayaran')
                ->label('Catat Pembayaran')
                ->icon('heroicon-o-plus')
                ->modalHeading('Catat Pembayaran Supplier')
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
                    $totalPaid = (float) $this->batch->batchPayments()->sum('amount');
                    $totalCost = (float) ($this->batch->total_cost ?? 0);
                    $remaining = $totalCost - $totalPaid;

                    if ((float) $data['amount'] > $remaining) {
                        Notification::make()
                            ->danger()
                            ->title('Pembayaran melebihi sisa utang')
                            ->body("Sisa utang: Rp" . number_format(max(0, $remaining), 0, ',', '.'))
                            ->send();
                        return;
                    }

                    $this->batch->batchPayments()->create([
                        'amount' => $data['amount'],
                        'payment_method' => $data['payment_method'],
                        'payment_date' => $data['payment_date'],
                    ]);

                    $this->batch->recalculatePaymentStatus();

                    Notification::make()
                        ->success()
                        ->title('Pembayaran tercatat')
                        ->send();
                }),
        ];
    }
}
