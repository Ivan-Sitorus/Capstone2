<?php

namespace App\Filament\Clusters\Financial\Pages;

use App\Filament\Clusters\Financial\FinancialCluster;
use App\Models\ReportTemplate;
use App\Services\FinancialReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SavedTemplates extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $cluster = FinancialCluster::class;

    protected static ?string $navigationLabel = 'Saved Templates';

    protected static ?string $title = 'Saved Templates';

    protected static ?string $slug = 'templates';

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
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
                        $config = $record->config;
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
                            $report = \App\Models\GeneratedReport::create([
                                'user_id' => Auth::id(),
                                'name' => ($record->name.' — '.now()->format('Y-m-d H:i')),
                                'type' => $config['report_type'] ?? 'simple',
                                'date_start' => $config['date_start'] ?? now()->subMonth()->toDateString(),
                                'date_end' => $config['date_end'] ?? now()->toDateString(),
                                'aggregation' => $config['aggregation'] ?? 'monthly',
                                'categories' => $config['categories'] ?? [],
                                'result' => $reportData->toArray(),
                            ]);
                            Notification::make()
                                ->title('Generated from Template')
                                ->success()
                                ->send();
                            $this->redirect(\App\Filament\Pages\ViewReport::getUrl(['id' => $report->id]));
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (ReportTemplate $record): void {
                        $record->delete();
                        Notification::make()
                            ->title('Template Deleted')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_template')
                ->label('Save as Template')
                ->icon('heroicon-o-bookmark')
                ->color('primary')
                ->modal()
                ->modalHeading('Save Report as Template')
                ->modalDescription('Enter report details to save as a reusable template.')
                ->form([
                    TextInput::make('name')
                        ->label('Template Name')
                        ->required()
                        ->maxLength(255),
                    Select::make('report_type')
                        ->label('Report Type')
                        ->options([
                            'simple' => 'Simple (Ringkasan)',
                            'rigid' => 'Rigid (Laba Rugi + Arus Kas)',
                            'custom' => 'Custom (Per Kategori)',
                        ])
                        ->required()
                        ->default('simple'),
                    DatePicker::make('date_start')
                        ->label('Start Date')
                        ->required()
                        ->default(now()->subMonth()),
                    DatePicker::make('date_end')
                        ->label('End Date')
                        ->required()
                        ->default(now()),
                ])
                ->action(function (array $data): void {
                    \App\Models\ReportTemplate::create([
                        'name' => $data['name'],
                        'user_id' => Auth::id(),
                        'type' => $data['report_type'],
                        'config' => [
                            'report_type' => $data['report_type'],
                            'date_start' => $data['date_start'],
                            'date_end' => $data['date_end'],
                            'aggregation' => 'monthly',
                            'categories' => [],
                        ],
                    ]);
                    Notification::make()
                        ->title('Template Saved')
                        ->success()
                        ->send();
                }),
        ];
    }
}
