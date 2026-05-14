<?php

namespace App\Filament\Clusters\Financial\Pages;

use App\Filament\Clusters\Financial\FinancialCluster;
use App\Filament\Pages\ViewReport;
use App\Models\GeneratedReport;
use App\Services\FinancialReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GeneratedReports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $cluster = FinancialCluster::class;

    protected static ?string $navigationLabel = 'Laporan Hasil';

    protected static ?string $title = 'Laporan Hasil';

    protected static ?string $slug = '';

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(GeneratedReport::with('user')->latest())
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'simple' => 'gray',
                        'rigid' => 'blue',
                        'custom' => 'orange',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('date_start')
                    ->label('Periode')
                    ->formatStateUsing(fn ($record): string => $record->date_start->format('d M').' → '.$record->date_end->format('d M Y')),
                TextColumn::make('aggregation')
                    ->label('Aggregation')
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? '—')),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn (GeneratedReport $record): string => ViewReport::getUrl(['id' => $record->id])),
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (GeneratedReport $record): string => url("/admin/view-report/{$record->id}/download-pdf")),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function generateReportName(array $data): string
    {
        $labels = ['simple' => 'Simple', 'rigid' => 'Rigid', 'custom' => 'Custom'];

        return ($labels[$data['report_type']] ?? $data['report_type'])." — {$data['date_start']} s/d {$data['date_end']}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_report')
                ->label('Generate Laporan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modal()
                ->modalHeading('Generate Laporan Baru')
                ->form([
                    Select::make('report_type')
                        ->label('Tipe Laporan')
                        ->options([
                            'simple' => 'Simple (Ringkasan)',
                            'rigid' => 'Rigid (Laba Rugi + Arus Kas)',
                            'custom' => 'Custom (Per Kategori)',
                        ])
                        ->required()
                        ->default('simple'),
                    DatePicker::make('date_start')
                        ->label('Tanggal Awal')
                        ->required()
                        ->default(now()->subMonth()),
                    DatePicker::make('date_end')
                        ->label('Tanggal Akhir')
                        ->required()
                        ->default(now()),
                    Select::make('aggregation')
                        ->label('Agregasi')
                        ->options([
                            'daily' => 'Harian',
                            'monthly' => 'Bulanan',
                        ])
                        ->default('monthly')
                        ->visible(fn ($get): bool => $get('report_type') === 'custom'),
                ])
                ->action(function (array $data): void {
                    $reportData = app(FinancialReportService::class)->generate(
                        $data['report_type'],
                        [
                            'date_start' => $data['date_start'],
                            'date_end' => $data['date_end'],
                            'aggregation' => $data['aggregation'] ?? 'monthly',
                        ]
                    );
                    $report = GeneratedReport::create([
                        'user_id' => Auth::id(),
                        'name' => $this->generateReportName($data),
                        'type' => $data['report_type'],
                        'date_start' => $data['date_start'],
                        'date_end' => $data['date_end'],
                        'aggregation' => $data['aggregation'] ?? 'monthly',
                        'categories' => [],
                        'result' => $reportData->toArray(),
                    ]);
                    Notification::make()
                        ->title('Laporan Terbuat')
                        ->success()
                        ->send();
                    $this->redirect(ViewReport::getUrl(['id' => $report->id]));
                }),
        ];
    }
}
