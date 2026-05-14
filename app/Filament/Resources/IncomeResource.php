<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Models\Income;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Pemasukan';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Pemasukan')
                ->schema([
                    TextInput::make('source')
                        ->label('Sumber')
                        ->required()
                        ->maxLength(255),
                    Select::make('category')
                        ->label('Kategori')
                        ->required()
                        ->options([
                            'penjualan' => 'Penjualan',
                            'modal' => 'Modal',
                            'investasi' => 'Investasi',
                            'lainnya' => 'Lainnya',
                        ]),
                    TextInput::make('amount')
                        ->label('Jumlah')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp'),
                    DatePicker::make('date')
                        ->label('Tanggal')
                        ->required()
                        ->native(false),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->nullable()
                        ->rows(3),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source')
                    ->label('Sumber')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'penjualan' => 'success',
                        'modal' => 'info',
                        'investasi' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'penjualan' => 'Penjualan',
                        'modal' => 'Modal',
                        'investasi' => 'Investasi',
                        'lainnya' => 'Lainnya',
                        default => $state,
                    }),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->prefix('Rp')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'penjualan' => 'Penjualan',
                        'modal' => 'Modal',
                        'investasi' => 'Investasi',
                        'lainnya' => 'Lainnya',
                    ]),
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('date', today()))
                    ->toggle(),
                Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('date', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]))
                    ->toggle(),
                Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year))
                    ->toggle(),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }
}
