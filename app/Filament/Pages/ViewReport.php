<?php

namespace App\Filament\Pages;

use App\DTO\ReportData;
use App\Models\GeneratedReport;
use App\Models\ReportTemplate;
use App\Renderers\CsvRenderer;
use App\Renderers\DomPdfRenderer;
use App\Renderers\ExcelRenderer;
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

    protected static ?string $title = 'Lihat Laporan';

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
            ->name(static::getRelativeRouteName($panel).'.pdf');

        Route::get('/view-report/{id}/download-excel', function ($id) {
            return app(static::class)->downloadExcel($id);
        })
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName($panel).'.excel');

        Route::get('/view-report/{id}/download-csv', function ($id) {
            return app(static::class)->downloadCsv($id);
        })
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getRelativeRouteName($panel).'.csv');
    }

    public function mount($id): void
    {
        $this->report = GeneratedReport::with('user')->findOrFail($id);
    }

    public function getReportData(): ReportData
    {
        return $this->report->toReportData();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_pdf')
                ->label('Unduh PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->url(fn () => url("/admin/view-report/{$this->report->id}/download-pdf"))
                ->openUrlInNewTab(),

            Action::make('download_excel')
                ->label('Unduh Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn () => url("/admin/view-report/{$this->report->id}/download-excel"))
                ->openUrlInNewTab(),

            Action::make('download_csv')
                ->label('Unduh CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn () => url("/admin/view-report/{$this->report->id}/download-csv"))
                ->openUrlInNewTab(),

            Action::make('save_template')
                ->label('Simpan sebagai Template')
                ->icon('heroicon-o-bookmark')
                ->color('gray')
                ->modal()
                ->modalHeading('Simpan Laporan sebagai Template')
                ->modalDescription('Simpan konfigurasi laporan ini untuk digunakan kembali.')
                ->schema([
                    TextInput::make('template_name')
                        ->label('Nama Template')
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
                            ->title('Template Diperbarui')
                            ->body("Template \"{$templateName}\" telah diperbarui.")
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
                            ->title('Template Disimpan')
                            ->body("Template \"{$templateName}\" telah disimpan.")
                            ->success()
                            ->send();
                    }
                }),
        ];
    }

    public function downloadPdf($id): StreamedResponse
    {
        $report = GeneratedReport::findOrFail($id);

        return DomPdfRenderer::download($report->toReportData(), [
            'filename' => "report-{$report->id}.pdf",
            'orientation' => 'landscape',
        ]);
    }

    public function downloadExcel($id): BinaryFileResponse
    {
        $report = GeneratedReport::findOrFail($id);

        return Excel::download(
            new ExcelRenderer($report->toReportData()),
            "report-{$report->id}.xlsx",
        );
    }

    public function downloadCsv($id): StreamedResponse
    {
        $report = GeneratedReport::findOrFail($id);

        return CsvRenderer::download($report->toReportData(), "report-{$report->id}.csv");
    }

    public function getTitle(): string
    {
        return "Lihat Laporan — {$this->report->name}";
    }

    public function getTypeLabel(): string
    {
        return match ($this->report->type) {
            'simple' => 'Simple (Ringkasan)',
            'rigid' => 'Rigid (Laba Rugi + Arus Kas)',
            'custom' => 'Custom (Per Kategori)',
            default => ucfirst($this->report->type),
        };
    }

    public function getAggregationLabel(): string
    {
        return match ($this->report->aggregation) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'quarterly' => 'Triwulan',
            'yearly' => 'Tahunan',
            default => ucfirst($this->report->aggregation),
        };
    }
}
