<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Expense;
use App\Models\ReportTemplate;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class FinancialReport extends Page
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance Details';

    protected static ?string $navigationLabel = 'Financial Reports';

    protected static ?string $title = 'Financial Reports';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.financial-report';

    public ?array $data = [];

    public bool $hasResult = false;

    public ?array $reportResult = null;

    public ?int $selectedTemplateId = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                DatePicker::make('date_start')
                    ->label('Start Date')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->required()
                    ->columnSpan(1),
                DatePicker::make('date_end')
                    ->label('End Date')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->required()
                    ->afterOrEqual('date_start')
                    ->columnSpan(1),
                Select::make('report_type')
                    ->label('Report Type')
                    ->options([
                        'simple'  => 'Simple',
                        'rigid'   => 'Rigid',
                        'custom'  => 'Custom',
                    ])
                    ->default('simple')
                    ->required()
                    ->live()
                    ->columnSpan(1),
                Select::make('aggregation')
                    ->label('Aggregation')
                    ->options([
                        'daily'     => 'Daily',
                        'weekly'    => 'Weekly',
                        'monthly'   => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly'    => 'Yearly',
                    ])
                    ->default('monthly')
                    ->required()
                    ->columnSpan(1),
                Select::make('category_ids')
                    ->label('Categories')
                    ->multiple()
                    ->options(function () {
                        $menuCategories = Category::where('is_active', true)
                            ->pluck('name', 'id')
                            ->toArray();

                        $expenseCategories = Expense::distinct()
                            ->pluck('category', 'category')
                            ->mapWithKeys(fn ($v) => ['expense:'.$v => $v.' (Expense)'])
                            ->toArray();

                        return array_merge($menuCategories, $expenseCategories);
                    })
                    ->visible(fn (callable $get) => $get('report_type') === 'custom')
                    ->columnSpanFull(),
                Actions::make([
                    Action::make('generate')
                        ->label('Generate Report')
                        ->icon('heroicon-o-play')
                        ->color('primary')
                        ->action('generateReport'),
                    Action::make('save_template')
                        ->label('Save as Template')
                        ->icon('heroicon-o-bookmark')
                        ->color('gray')
                        ->form([
                            TextInput::make('template_name')
                                ->label('Template Name')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->action('saveAsTemplate'),
                ])->columnSpanFull(),
                Select::make('template_id')
                    ->label('Load Template')
                    ->options(function () {
                        return ReportTemplate::forUser(Auth::id())
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->placeholder('Select a template...')
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->loadTemplate($state))
                    ->columnSpanFull(),
                Actions::make([
                    Action::make('delete_template')
                        ->label('Delete Template')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn () => ! empty($this->form->getState()['template_id']))
                        ->action(fn () => $this->deleteTemplate($this->form->getState()['template_id'])),
                ])->columnSpanFull(),
            ])
            ->statePath('data')
            ->columns(2);
    }

    public function generateReport(): void
    {
        $this->validate();

        $data = $this->form->getState();

        // Stub: Tasks 17-19 will replace this with actual service calls:
        //   T17 → SimpleReportService
        //   T18 → RigidReportService
        //   T19 → CustomReportService
        $this->reportResult = [
            'type'         => $data['report_type'],
            'date_start'   => $data['date_start'],
            'date_end'     => $data['date_end'],
            'aggregation'  => $data['aggregation'],
            'categories'   => $data['category_ids'] ?? [],
            'summary'      => 'Report generation will be implemented in Tasks 17-19 (SimpleReportService, RigidReportService, CustomReportService).',
            'generated_at' => now()->toDateTimeString(),
        ];

        $this->hasResult = true;

        Notification::make()
            ->title('Report Generated')
            ->body('The financial report has been generated successfully (stub).')
            ->success()
            ->send();
    }

    public function saveAsTemplate(): void
    {
        $data = $this->form->getState();

        // Validate template name is provided
        if (empty($data['template_name'])) {
            Notification::make()
                ->title('Template Name Required')
                ->body('Please enter a name for the template.')
                ->danger()
                ->send();

            return;
        }

        $templateName = trim($data['template_name']);

        // Check for duplicate name for this user
        $existingTemplate = ReportTemplate::forUser(Auth::id())
            ->where('name', $templateName)
            ->first();

        if ($existingTemplate) {
            // Update existing template
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
            // Create new template
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

        // Reset template name field
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

        // Fill form with template config
        $this->form->fill([
            'date_start' => $config['date_start'] ?? null,
            'date_end' => $config['date_end'] ?? null,
            'report_type' => $config['report_type'] ?? 'simple',
            'category_ids' => $config['categories'] ?? [],
            'aggregation' => $config['aggregation'] ?? 'monthly',
        ]);

        Notification::make()
            ->title('Template Loaded')
            ->body("Template \"{$template->name}\" has been loaded.")
            ->success()
            ->send();
    }

    public function deleteTemplate($templateId): void
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

        // Reset template selector
        $this->form->fill(['template_id' => null]);

        Notification::make()
            ->title('Template Deleted')
            ->body("Template \"{$templateName}\" has been deleted.")
            ->success()
            ->send();
    }

    public function getTemplates()
    {
        return ReportTemplate::forUser(Auth::id())->get();
    }

    public function getTitle(): string
    {
        return 'Financial Reports';
    }
}
