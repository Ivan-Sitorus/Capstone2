<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CafeTableResource\Pages\CreateCafeTable;
use App\Filament\Resources\CafeTableResource\Pages\EditCafeTable;
use App\Filament\Resources\CafeTableResource\Pages\ListCafeTables;
use App\Models\CafeTable;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CafeTableResource extends Resource
{
    protected static ?string $model = CafeTable::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-table-cells';

    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Meja Cafe';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('table_number')
                ->label('Nomor Meja')
                ->required()
                ->numeric()
                ->minValue(1)
                ->unique(ignoreRecord: true),
            Toggle::make('is_available')
                ->label('Tersedia')
                ->default(true)
                ->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('table_number')
                    ->label('Nomor Meja')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_available')
                    ->label('Tersedia')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('qr_code_svg')
                    ->label('QR Code')
                    ->html()
                    ->width(120)
                    ->alignCenter()
                    ->formatStateUsing(function (CafeTable $record): string {
                        $dataUri = $record->qr_code_svg_data_uri;

                        return sprintf(
                            '<a href="%s" target="_blank" title="Buka URL">
                                <img src="%s" width="80" height="80" style="border-radius:4px;border:1px solid #e5e7eb;padding:4px;" alt="QR Meja %d" />
                            </a>',
                            e($record->qr_code_url),
                            $dataUri,
                            $record->table_number
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('view_qr')
                    ->label('Lihat QR')
                    ->icon('heroicon-o-qr-code')
                    ->modalHeading(fn (CafeTable $record) => 'QR Code Meja ' . $record->table_number)
                    ->modalWidth('md')
                    ->modalContent(function (CafeTable $record) {
                        $dataUri = $record->qr_code_svg_data_uri;

                        return view('filament.cafe-table-qr-modal', [
                            'record' => $record,
                            'dataUri' => $dataUri,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCafeTables::route('/'),
            'create' => CreateCafeTable::route('/create'),
            'edit' => EditCafeTable::route('/{record}/edit'),
        ];
    }
}
