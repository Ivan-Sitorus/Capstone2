<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\GeneratedReport;
use App\Models\ReportTemplate;
use App\Services\CustomReportService;
use App\Services\RigidReportService;
use App\Services\SimpleReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;

class FinancialReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance Details';

    protected static ?string $navigationLabel = 'Financial Reports';

    protected static ?string $title = 'Financial Reports';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.financial-report';

    public string $activeTab = 'generated';

    public ?int $deleteTemplateId = null;

    public ?array $data = [];

    public bool $hasResult = false;

    public ?array $reportResult = null;

    public function mount(): void
    {
        $this->activeTab = 'generated';
    }

    public function getGeneratedReports()
    {
        return GeneratedReport::with('user')
            ->latest()
            ->get();
    }

    public function getTemplates()
    {
        return ReportTemplate::forUser(Auth::id())->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_report')
                ->label('Buat Laporan Baru')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modal()
                ->modalWidth('2xl')
                ->modalHeading('Buat Laporan Keuangan Baru')
                ->modalDescription('Pilih rentang tanggal, tipe laporan, dan agregasi.')
                ->schema([
                    DatePicker::make('date_start')
                        ->label('Tanggal Mulai')
                        ->native(false)
                        ->displayFormat('Y-m-d')
                        ->required()
                        ->columnSpan(1),
                    DatePicker::make('date_end')
                        ->label('Tanggal Akhir')
                        ->native(false)
                        ->displayFormat('Y-m-d')
                        ->required()
                        ->afterOrEqual('date_start')
                        ->columnSpan(1),
                    Select::make('report_type')
                        ->label('Tipe Laporan')
                        ->options([
                            'simple' => 'Simple (Ringkasan)',
                            'rigid' => 'Rigid (Laba Rugi + Arus Kas)',
                            'custom' => 'Custom (Per Kategori)',
                        ])
                        ->default('simple')
                        ->required()
                        ->live()
                        ->columnSpan(1),
                    Select::make('aggregation')
                        ->label('Agregasi')
                        ->options([
                            'daily' => 'Harian',
                            'weekly' => 'Mingguan',
                            'monthly' => 'Bulanan',
                            'quarterly' => 'Triwulan',
                            'yearly' => 'Tahunan',
                        ])
                        ->default('monthly')
                        ->required()
                        ->columnSpan(1),
                    Select::make('category_ids')
                        ->label('Kategori')
                        ->multiple()
                        ->options(function () {
                            return Category::where('is_active', true)
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->visible(fn (Get $get) => $get('report_type') === 'custom')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    try {
                        $result = match ($data['report_type']) {
                            'simple' => app(SimpleReportService::class)->generate(
                                $data['date_start'],
                                $data['date_end'],
                            ),
                            'rigid' => app(RigidReportService::class)->generate(
                                $data['date_start'],
                                $data['date_end'],
                            ),
                            'custom' => app(CustomReportService::class)->generate([
                                'date_start' => $data['date_start'],
                                'date_end' => $data['date_end'],
                                'aggregation' => $data['aggregation'],
                                'categories' => $data['category_ids'] ?? [],
                            ]),
                            default => throw new \InvalidArgumentException('Unknown report type'),
                        };

                        $report = GeneratedReport::create([
                            'user_id' => Auth::id(),
                            'name' => $this->generateReportName($data),
                            'type' => $data['report_type'],
                            'date_start' => $data['date_start'],
                            'date_end' => $data['date_end'],
                            'aggregation' => $data['aggregation'],
                            'categories' => $data['category_ids'] ?? [],
                            'result' => $result,
                        ]);

                        Notification::make()
                            ->title('Laporan Berhasil Dibuat')
                            ->body('Laporan keuangan telah berhasil dibuat.')
                            ->success()
                            ->send();

                        return redirect()->to(
                            \App\Filament\Pages\ViewReport::getUrl(['id' => $report->id])
                        );
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal Membuat Laporan')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function generateReportName(array $data): string
    {
        $typeLabels = [
            'simple' => 'Simple',
            'rigid' => 'Rigid',
            'custom' => 'Custom',
        ];
        $type = $typeLabels[$data['report_type']] ?? $data['report_type'];
        return "{$type} Report — {$data['date_start']} to {$data['date_end']}";
    }

    public function generateReport(): void
    {
        $this->validate();

        $data = $this->form->getState();

        $this->reportResult = [
            'type'         => $data['report_type'],
            'date_start'   => $data['date_start'],
            'date_end'     => $data['date_end'],
            'aggregation'  => $data['aggregation'],
            'categories'   => $data['category_ids'] ?? [],
            'summary'      => 'Report generation via the modal "Buat Laporan Baru" action.',
            'generated_at' => now()->toDateTimeString(),
        ];

        $this->hasResult = true;

        Notification::make()
            ->title('Report Generated')
            ->body('The financial report has been generated successfully.')
            ->success()
            ->send();
    }

    public function saveAsTemplate(): void
    {
        $data = $this->form->getState();

        if (empty($data['template_name'])) {
            Notification::make()
                ->title('Template Name Required')
                ->body('Please enter a name for the template.')
                ->danger()
                ->send();
            return;
        }

        $templateName = trim($data['template_name']);

        $existingTemplate = ReportTemplate::forUser(Auth::id())
            ->where('name', $templateName)
            ->first();

        if ($existingTemplate) {
            $existingTemplate->update([
                'config' => [
                    'date_start' => $data['date_start'],
                    'date_end' => $data['date_end'],
                    'report_type' => $data['report_type'],
                    'categories' => $data['category_ids'] ?? [],
                    'aggregation' => $data['aggregation'],
                ],
                'type' => $data['report_type'],
            ]);

            Notification::make()
                ->title('Template Updated')
                ->body("Template \"{$templateName}\" has been updated successfully.")
                ->success()
                ->send();
        } else {
            ReportTemplate::create([
                'name' => $templateName,
                'user_id' => Auth::id(),
                'config' => [
                    'date_start' => $data['date_start'],
                    'date_end' => $data['date_end'],
                    'report_type' => $data['report_type'],
                    'categories' => $data['category_ids'] ?? [],
                    'aggregation' => $data['aggregation'],
                ],
                'type' => $data['report_type'],
            ]);

            Notification::make()
                ->title('Template Saved')
                ->body("Template \"{$templateName}\" has been saved successfully.")
                ->success()
                ->send();
        }

        $this->form->fill(['template_name' => '']);
    }

    public function loadTemplate($templateId): void
    {
        if (blank($templateId)) {
            return;
        }

        $template = ReportTemplate::forUser(Auth::id())->find($templateId);

        if (! $template) {
            Notification::make()
                ->title('Template Not Found')
                ->body('You do not have access to this template.')
                ->danger()
                ->send();
            return;
        }

        $config = $template->config;

        try {
            $result = match ($config['report_type'] ?? 'simple') {
                'simple' => app(SimpleReportService::class)->generate(
                    $config['date_start'] ?? null,
                    $config['date_end'] ?? null,
                ),
                'rigid' => app(RigidReportService::class)->generate(
                    $config['date_start'] ?? now()->subMonth()->toDateString(),
                    $config['date_end'] ?? now()->toDateString(),
                ),
                'custom' => app(CustomReportService::class)->generate([
                    'date_start' => $config['date_start'] ?? now()->subMonth()->toDateString(),
                    'date_end' => $config['date_end'] ?? now()->toDateString(),
                    'aggregation' => $config['aggregation'] ?? 'monthly',
                    'categories' => $config['categories'] ?? [],
                ]),
                default => throw new \InvalidArgumentException('Unknown report type'),
            };

            $report = GeneratedReport::create([
                'user_id' => Auth::id(),
                'name' => $template->name . ' — ' . now()->format('Y-m-d H:i'),
                'type' => $config['report_type'] ?? 'simple',
                'date_start' => $config['date_start'] ?? now()->subMonth()->toDateString(),
                'date_end' => $config['date_end'] ?? now()->toDateString(),
                'aggregation' => $config['aggregation'] ?? 'monthly',
                'categories' => $config['categories'] ?? [],
                'result' => $result,
            ]);

            Notification::make()
                ->title('Report Generated from Template')
                ->body("Report created from template \"{$template->name}\".")
                ->success()
                ->send();

            $this->redirect(
                \App\Filament\Pages\ViewReport::getUrl(['id' => $report->id])
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Generate Report')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteTemplate(int $templateId): void
    {
        $template = ReportTemplate::forUser(Auth::id())->find($templateId);

        if (! $template) {
            Notification::make()
                ->title('Template Not Found')
                ->body('You do not have access to this template.')
                ->danger()
                ->send();
            return;
        }

        $templateName = $template->name;
        $template->delete();

        Notification::make()
            ->title('Template Deleted')
            ->body("Template \"{$templateName}\" has been deleted.")
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Financial Reports';
    }
}
