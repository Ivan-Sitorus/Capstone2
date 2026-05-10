<?php

namespace App\Filament\Pages;

use App\Models\GeneratedReport;
use App\Models\ReportTemplate;
use App\Services\FinancialReportService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class FinancialReport extends Page implements HasTable
{
    use InteractsWithTable;

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
            $reportData = app(FinancialReportService::class)->generate(
                $config['report_type'] ?? 'simple',
                [
                    'date_start' => $config['date_start'] ?? now()->subMonth()->toDateString(),
                    'date_end' => $config['date_end'] ?? now()->toDateString(),
                    'aggregation' => $config['aggregation'] ?? 'monthly',
                    'categories' => $config['categories'] ?? [],
                ]
            );
            $result = $reportData->toArray();
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_report')
                ->label('Generate Report')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modal()
                ->modalHeading('Generate New Report')
                ->modalDescription('Choose report parameters to generate a new financial report.')
                ->form([
                    \Filament\Forms\Components\Select::make('report_type')
                        ->label('Report Type')
                        ->options([
                            'simple' => 'Simple (Ringkasan)',
                            'rigid' => 'Rigid (Laba Rugi + Arus Kas)',
                            'custom' => 'Custom (Per Kategori)',
                        ])
                        ->required()
                        ->default('simple'),
                    \Filament\Forms\Components\DatePicker::make('date_start')
                        ->label('Start Date')
                        ->required()
                        ->default(now()->subMonth()),
                    \Filament\Forms\Components\DatePicker::make('date_end')
                        ->label('End Date')
                        ->required()
                        ->default(now()),
                    \Filament\Forms\Components\Select::make('aggregation')
                        ->label('Aggregation')
                        ->options([
                            'daily' => 'Daily',
                            'monthly' => 'Monthly',
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
                        ->title('Report Generated')
                        ->success()
                        ->send();
                    $this->redirect(ViewReport::getUrl(['id' => $report->id]));
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return match ($this->activeTab) {
            'generated' => $this->generatedReportsTable($table),
            default => $this->savedTemplatesTable($table),
        };
    }

    protected function generatedReportsTable(Table $table): Table
    {
        return $table
            ->query(GeneratedReport::with('user')->latest())
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'simple' => 'gray',
                        'rigid' => 'blue',
                        'custom' => 'orange',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('date_start')
                    ->label('Period')
                    ->formatStateUsing(fn ($record): string => $record->date_start->format('d M') . ' → ' . $record->date_end->format('d M Y')),
                TextColumn::make('aggregation')
                    ->label('Aggregation')
                    ->formatStateUsing(fn (?string $state): string => ucfirst($state ?? '—')),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
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

    protected function savedTemplatesTable(Table $table): Table
    {
        return $table
            ->query(ReportTemplate::where('user_id', Auth::id())->latest())
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'simple' => 'gray',
                        'rigid' => 'blue',
                        'custom' => 'orange',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('generate')
                    ->label('Generate')
                    ->icon('heroicon-o-play')
                    ->action(function (ReportTemplate $record): void {
                        $this->loadTemplate($record->id);
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (ReportTemplate $record): void {
                        $this->deleteTemplate($record->id);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
