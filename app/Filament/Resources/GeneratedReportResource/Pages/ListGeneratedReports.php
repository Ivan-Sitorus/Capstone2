<?php

namespace App\Filament\Resources\GeneratedReportResource\Pages;

use App\Filament\Pages\ViewReport;
use App\Filament\Resources\GeneratedReportResource;
use App\Models\GeneratedReport;
use App\Services\FinancialReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListGeneratedReports extends ListRecords
{
    protected static string $resource = GeneratedReportResource::class;

    protected function generateReportName(array $data): string
    {
        $labels = ['simple' => 'Simple', 'rigid' => 'Rigid', 'custom' => 'Custom'];
        return ($labels[$data['report_type']] ?? $data['report_type'])." - {$data['date_start']} s/d {$data['date_end']}";
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
                    Select::make('report_type')->label('Tipe Laporan')
                        ->options([
                            'simple' => 'Simple (Ringkasan)',
                            'rigid' => 'Rigid (Laba Rugi + Arus Kas)',
                            'custom' => 'Custom (Per Kategori)',
                        ])->required()->default('simple'),
                    DatePicker::make('date_start')->label('Tanggal Awal')->required()->default(now()->subMonth()),
                    DatePicker::make('date_end')->label('Tanggal Akhir')->required()->default(now()),
                    Select::make('aggregation')->label('Agregasi')
                        ->options(['daily' => 'Harian', 'monthly' => 'Bulanan'])
                        ->default('monthly')
                        ->visible(fn ($get): bool => $get('report_type') === 'custom'),
                ])
                ->action(function (array $data): void {
                    $reportData = app(FinancialReportService::class)->generate(
                        $data['report_type'], [
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
                    Notification::make()->title('Laporan Terbuat')->success()->send();
                    $this->redirect(ViewReport::getUrl(['id' => $report->id]));
                }),
        ];
    }
}
