<?php

namespace App\Filament\Resources;

use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Resources\CafeTableResource\Pages\ListCafeTables;
use App\Models\CafeTable;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CafeTableResource extends Resource
{
    protected static ?string $model = CafeTable::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

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
                ->maxValue(99)
                ->unique(ignoreRecord: true)
                ->extraAttributes(
                    NumberInputHelper::integer(99)
                ),
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
                EditAction::make()
                    ->modalWidth('md'),
                Action::make('view_qr')
                    ->label('Lihat QR')
                    ->icon('heroicon-o-qr-code')
                    ->modalHeading(fn (CafeTable $record) => 'QR Code Meja '.$record->table_number)
                    ->modalWidth('md')
                    ->infolist(fn (CafeTable $record) => [
                        ImageEntry::make('qr_image')
                            ->hiddenLabel()
                            ->state(fn () => $record->qr_code_svg_data_uri)
                            ->width(200)
                            ->height(200)
                            ->extraImgAttributes([
                                'style' => 'border-radius:8px;border:1px solid #e5e7eb;padding:8px;margin:0 auto;display:block;',
                            ]),
                        TextEntry::make('qr_url')
                            ->hiddenLabel()
                            ->state(fn () => $record->qr_code_url)
                            ->color('primary')
                            ->extraAttributes([
                                'class' => 'underline',
                            ])
                            ->copyable()
                            ->copyMessage('Tersalin!')
                            ->copyMessageDuration(1500)
                            ->alignCenter(),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalFooterActions([
                        Action::make('download_qr_png')
                            ->label('Download PNG')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->action(function (CafeTable $record) {
                                return response()->streamDownload(
                                    fn () => print ($record->generatePngDownload()),
                                    sprintf('qr-meja-%d.png', $record->table_number),
                                    ['Content-Type' => 'image/png'],
                                );
                            }),
                    ]),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading(fn (CafeTable $record) => 'Hapus Meja '.$record->table_number)
                    ->modalWidth('md'),
            ])
            ->toolbarActions([])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->modalHeading('Hapus meja terpilih'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCafeTables::route('/'),
        ];
    }
}
