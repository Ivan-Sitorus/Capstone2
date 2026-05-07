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

    public function mount(): void
    {
        $this->activeTab = 'generated';
    }

    public function generateReportName(array $data): string
    {
        $labels = ['simple' => 'Simple', 'rigid' => 'Rigid', 'custom' => 'Custom'];
        return ($labels[$data['report_type']] ?? $data['report_type']) . " — {$data['date_start']} to {$data['date_end']}";
    }

    public function loadTemplate($templateId): void
    {
        if (blank($templateId)) return;
        $template = ReportTemplate::forUser(Auth::id())->find($templateId);
        if (! $template) { Notification::make()->title('Template Not Found')->danger()->send(); return; }
        $config = $template->config;
        try {
            $result = match ($config['report_type'] ?? 'simple') {
                'simple' => app(SimpleReportService::class)->generate($config['date_start'] ?? null, $config['date_end'] ?? null),
                'rigid' => app(RigidReportService::class)->generate($config['date_start'] ?? now()->subMonth()->toDateString(), $config['date_end'] ?? now()->toDateString()),
                'custom' => app(CustomReportService::class)->generate(['date_start' => $config['date_start'] ?? now()->subMonth()->toDateString(), 'date_end' => $config['date_end'] ?? now()->toDateString(), 'aggregation' => $config['aggregation'] ?? 'monthly', 'categories' => $config['categories'] ?? []]),
                default => throw new \InvalidArgumentException('Unknown report type'),
            };
            $report = GeneratedReport::create(['user_id' => Auth::id(), 'name' => ($template->name . ' — ' . now()->format('Y-m-d H:i')), 'type' => $config['report_type'] ?? 'simple', 'date_start' => $config['date_start'] ?? now()->subMonth()->toDateString(), 'date_end' => $config['date_end'] ?? now()->toDateString(), 'aggregation' => $config['aggregation'] ?? 'monthly', 'categories' => $config['categories'] ?? [], 'result' => $result]);
            Notification::make()->title('Generated from Template')->success()->send();
            $this->redirect(ViewReport::getUrl(['id' => $report->id]));
        } catch (\Exception $e) {
            Notification::make()->title('Failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function deleteTemplate(int $templateId): void
    {
        $template = ReportTemplate::forUser(Auth::id())->find($templateId);
        if (! $template) { Notification::make()->title('Not Found')->danger()->send(); return; }
        $template->delete();
        Notification::make()->title('Template Deleted')->success()->send();
    }

    public function getTitle(): string { return 'Financial Reports'; }
}
