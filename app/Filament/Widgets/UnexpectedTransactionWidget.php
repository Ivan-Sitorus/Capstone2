<?php

namespace App\Filament\Widgets;

use App\Models\UnexpectedTransaction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UnexpectedTransactionWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public static function isLazy(): bool { return false; }

    private function formSchema(): array
    {
        return [
            Select::make('jenis')
                ->label('Jenis')
                ->options(['pemasukan' => 'Pemasukan', 'pengeluaran' => 'Pengeluaran'])
                ->required(),
            TextInput::make('nominal')
                ->label('Nominal (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required()
                ->minValue(0),
            Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->rows(3)
                ->nullable(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(UnexpectedTransaction::query()->latest())
            ->heading('Pemasukan & Pengeluaran Tak Terduga')
            ->description('Catat transaksi pemasukan atau pengeluaran di luar operasional rutin')
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex()
                    ->width(50),
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pemasukan'   => 'success',
                        'pengeluaran' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->formatStateUsing(fn (float $state): string =>
                        'Rp ' . number_format($state, 0, ',', '.') . ',-'
                    )
                    ->color(fn (UnexpectedTransaction $record): string =>
                        $record->jenis === 'pemasukan' ? 'success' : 'danger'
                    ),
                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(80)
                    ->placeholder('—')
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->dateTime('d M Y, H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Transaksi')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Tambah Transaksi Tak Terduga')
                    ->modalWidth('lg')
                    ->form($this->formSchema())
                    ->model(UnexpectedTransaction::class),
            ])
            ->actions([
                EditAction::make()
                    ->label('Edit')
                    ->modalHeading('Edit Transaksi')
                    ->modalWidth('lg')
                    ->form($this->formSchema()),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateHeading('Belum ada transaksi')
            ->emptyStateDescription('Klik "Tambah Transaksi" untuk mencatat pemasukan atau pengeluaran tak terduga.')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
