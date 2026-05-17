<?php

namespace App\Filament\Resources;

use App\Filament\Pages\ViewReport;
use App\Filament\Resources\GeneratedReportResource\Pages\ListGeneratedReports;
use App\Models\GeneratedReport;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GeneratedReportResource extends Resource
{
    protected static ?string $model = GeneratedReport::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Laporan Keuangan';
    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->query(GeneratedReport::with('user')->latest())
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->weight('medium'),
                TextColumn::make('type')->label('Tipe')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('date_start')->label('Periode')
                    ->formatStateUsing(fn ($record): string => $record->date_start->format('d M').' -> '.$record->date_end->format('d M Y')),
                TextColumn::make('aggregation')->label('Aggregation')
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? '-')),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y, H:i')->sortable(),
            ])
            ->actions([
                Action::make('view')->label('Lihat')->icon('heroicon-o-eye')
                    ->url(fn (GeneratedReport $record): string => ViewReport::getUrl(['id' => $record->id])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListGeneratedReports::route('/')];
    }
}
