<?php

namespace App\Filament\Pages;

use App\Models\GeneratedReport;
use App\Models\ReportTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\PageConfiguration;
use Filament\Panel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViewReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'view-report/{id}';

    protected static ?string $title = 'View Report';

    protected string $view = 'filament.pages.view-report';

    public GeneratedReport $report;

    public static function getRelativeRouteName(Panel $panel): string
    {
        return 'view-report';
    }

    public static function routes(Panel $panel, ?PageConfiguration $configuration = null): void
    {
        Route::get('/view-report/{id}', static::class)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName($panel));

        Route::get('/view-report/{id}/download-pdf', function ($id) {
            return app(static::class)->downloadPdf($id);
        })
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName($panel) . '.pdf');

        Route::get('/view-report/{id}/download-excel', function ($id) {
            return app(static::class)->downloadExcel($id);
        })
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName($panel) . '.excel');
    }

    public function mount($id): void
    {
        $this->report = GeneratedReport::with('user')->findOrFail($id);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_template')
                ->label('Save as Template')
                ->icon('heroicon-o-bookmark')
                ->color('gray')
                ->modal()
                ->modalHeading('Save Report as Template')
                ->modalDescription('Save this report configuration for future use.')
                ->schema([
                    TextInput::make('template_name')
                        ->label('Template Name')
                        ->required()
                        ->maxLength(255)
                        ->default($this->report->name),
                ])
                ->action(function (array $data) {
                    $templateName = trim($data['template_name']);

                    $existingTemplate = ReportTemplate::forUser(Auth::id())
                        ->where('name', $templateName)
                        ->first();

                    if ($existingTemplate) {
                        $existingTemplate->update([
                            'config' => [
                                'date_start' => $this->report->date_start->format('Y-m-d'),
                                'date_end' => $this->report->date_end->format('Y-m-d'),
                                'report_type' => $this->report->type,
                                'categories' => $this->report->categories ?? [],
                                'aggregation' => $this->report->aggregation,
                            ],
                            'type' => $this->report->type,
                        ]);

                        Notification::make()
                            ->title('Template Updated')
                            ->body("Template \"{$templateName}\" has been updated.")
                            ->success()
                            ->send();
                    } else {
                        ReportTemplate::create([
                            'name' => $templateName,
                            'user_id' => Auth::id(),
                            'config' => [
                                'date_start' => $this->report->date_start->format('Y-m-d'),
                                'date_end' => $this->report->date_end->format('Y-m-d'),
                                'report_type' => $this->report->type,
                                'categories' => $this->report->categories ?? [],
                                'aggregation' => $this->report->aggregation,
                            ],
                            'type' => $this->report->type,
                        ]);

                        Notification::make()
                            ->title('Template Saved')
                            ->body("Template \"{$templateName}\" has been saved.")
                            ->success()
                            ->send();
                    }
                }),
        ];
    }

    public function downloadPdf($id): StreamedResponse|BinaryFileResponse
    {
        $report = GeneratedReport::findOrFail($id);
        $summary = $this->buildSummary($report);
        $rows = $this->buildRows($report);

        $pdf = Pdf::loadView('filament.pages.exports.report-pdf', [
            'report' => $report,
            'summary' => $summary,
            'rows' => $rows,
            'typeLabel' => $this->buildTypeLabel($report),
            'aggregationLabel' => $this->buildAggregationLabel($report),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'report-' . $report->id . '.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function downloadExcel($id)
    {
        $report = GeneratedReport::findOrFail($id);
        $summary = $this->buildSummary($report);
        $rows = $this->buildRows($report);

        return Excel::download(
            new \App\Exports\ReportExport($report, $summary, $rows),
            'report-' . $report->id . '.xlsx',
        );
    }

    public function getTitle(): string
    {
        return "View Report — {$this->report->name}";
    }

    public function getReportSummary(): array
    {
        return $this->buildSummary($this->report);
    }

    public function getReportRows(): array
    {
        return $this->buildRows($this->report);
    }

    public function getTypeLabel(): string
    {
        return $this->buildTypeLabel($this->report);
    }

    public function getAggregationLabel(): string
    {
        return $this->buildAggregationLabel($this->report);
    }

    private function buildSummary(GeneratedReport $report): array
    {
        $result = $report->result ?? [];

        if ($report->type === 'simple') {
            return [
                'Total Pendapatan' => 'Rp ' . number_format($result['total_income'] ?? 0, 0, ',', '.'),
                'Total Pengeluaran' => 'Rp ' . number_format($result['total_expense'] ?? 0, 0, ',', '.'),
                'Net' => 'Rp ' . number_format($result['net'] ?? 0, 0, ',', '.'),
                'Piutang Outstanding' => 'Rp ' . number_format($result['receivables_outstanding'] ?? 0, 0, ',', '.'),
            ];
        }

        if ($report->type === 'rigid') {
            $income = $result['income_statement'] ?? [];
            $cash = $result['cash_flow'] ?? [];
            return [
                'Pendapatan' => 'Rp ' . number_format($income['pendapatan'] ?? 0, 0, ',', '.'),
                'HPP' => 'Rp ' . number_format($income['hpp'] ?? 0, 0, ',', '.'),
                'Laba Kotor' => 'Rp ' . number_format($income['laba_kotor'] ?? 0, 0, ',', '.'),
                'Laba Rugi Bersih' => 'Rp ' . number_format($income['laba_rugi_bersih'] ?? 0, 0, ',', '.'),
                'Arus Kas Masuk' => 'Rp ' . number_format($cash['arus_kas_masuk'] ?? 0, 0, ',', '.'),
                'Arus Kas Keluar' => 'Rp ' . number_format($cash['arus_kas_keluar'] ?? 0, 0, ',', '.'),
                'Arus Kas Bersih' => 'Rp ' . number_format($cash['arus_kas_bersih'] ?? 0, 0, ',', '.'),
            ];
        }

        if ($report->type === 'custom') {
            $s = $result['summary'] ?? [];
            return [
                'Total Pendapatan' => 'Rp ' . number_format($s['total_income'] ?? 0, 0, ',', '.'),
                'Total Pengeluaran' => 'Rp ' . number_format($s['total_expense'] ?? 0, 0, ',', '.'),
                'Net' => 'Rp ' . number_format($s['net'] ?? 0, 0, ',', '.'),
            ];
        }

        return [];
    }

    private function buildRows(GeneratedReport $report): array
    {
        $result = $report->result ?? [];

        if ($report->type === 'custom' && isset($result['rows'])) {
            return $result['rows'];
        }

        if ($report->type === 'simple') {
            $rows = [];
            foreach ($result['income_breakdown'] ?? [] as $item) {
                $rows[] = [
                    'date' => $result['date_range']['start'] ?? '-',
                    'category' => $item['source'] ?? '-',
                    'type' => 'Income',
                    'amount' => $item['total'] ?? 0,
                ];
            }
            foreach ($result['expense_breakdown'] ?? [] as $item) {
                $rows[] = [
                    'date' => $result['date_range']['start'] ?? '-',
                    'category' => $item['source'] ?? '-',
                    'type' => 'Expense',
                    'amount' => $item['total'] ?? 0,
                ];
            }
            return $rows;
        }

        return [];
    }

    private function buildTypeLabel(GeneratedReport $report): string
    {
        return match ($report->type) {
            'simple' => 'Simple (Ringkasan)',
            'rigid' => 'Rigid (Laba Rugi + Arus Kas)',
            'custom' => 'Custom (Per Kategori)',
            default => ucfirst($report->type),
        };
    }

    private function buildAggregationLabel(GeneratedReport $report): string
    {
        return match ($report->aggregation) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'quarterly' => 'Triwulan',
            'yearly' => 'Tahunan',
            default => ucfirst($report->aggregation),
        };
    }
}
