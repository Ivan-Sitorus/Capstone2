<?php

namespace App\Filament\Pages;

use App\Models\DataMiningRun;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

abstract class AnalyticsPage extends Page implements HasTable
{
    use InteractsWithTable;

    public bool $hasResult = false;

    public bool $isRunning = false;

    public ?string $lastRunAt = null;

    public ?string $errorMsg = null;

    abstract protected function getAnalysisType(): string;

    abstract protected function getFastApiEndpoint(): string;

    abstract protected function getFastApiTimeout(): int;

    public function table(Table $table): Table
    {
        return $table
            ->query(DataMiningRun::ofType($this->getAnalysisType())->latest())
            ->columns([
                TextColumn::make('run_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('date_range_start')
                    ->label('Dari')
                    ->date('d M Y'),
                TextColumn::make('date_range_end')
                    ->label('Sampai')
                    ->date('d M Y'),
                TextColumn::make('result_summary')
                    ->label('Ringkasan')
                    ->state(function (DataMiningRun $record): string {
                        $r = $record->result;

                        return match ($record->analysis_type) {
                            'menu_clustering', 'ingredient_clustering' => ($r['best_k'] ?? '?').' klaster, silhouette '.round((float) ($r['silhouette_score'] ?? 0), 3),
                            'menu_prediction', 'ingredient_prediction' => count($r['predictions'] ?? []).' item diprediksi',
                            'association' => ($r['total_rules'] ?? 0).' aturan ditemukan',
                            default => 'Lihat detail',
                        };
                    }),
            ])
            ->defaultSort('run_at', 'desc')
            ->recordAction('view')
            ->recordUrl(fn (DataMiningRun $record): string => $this->getViewUrl($record));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_analysis')
                ->label('Jalankan Baru')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi')
                ->modalDescription('Proses ini akan menjalankan analisis data mining. Data akan diproses oleh FastAPI dan hasilnya disimpan di database. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Jalankan')
                ->action(fn () => $this->executeAnalysis()),
        ];
    }

    public function executeAnalysis(): void
    {
        $this->isRunning = true;
        $this->errorMsg = null;

        try {
            $response = $this->callFastAPI();

            if (($response['status'] ?? '') === 'error') {
                throw new \Exception($response['message'] ?? 'Unknown error');
            }

            $run = $this->storeRun($response);

            $this->hasResult = true;
            $this->lastRunAt = $run->run_at?->format('d M Y, H:i');
            $this->errorMsg = null;

            Notification::make()
                ->title('Analisis selesai!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            $this->errorMsg = $e->getMessage();
            $this->hasResult = false;

            Notification::make()
                ->title('Analisis gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isRunning = false;
        }
    }

    protected function callFastAPI(): array
    {
        $url = config('services.datamining.url').$this->getFastApiEndpoint();
        $response = Http::timeout($this->getFastApiTimeout())->post($url);

        if (! $response->successful()) {
            throw new \Exception('FastAPI merespons dengan status '.$response->status());
        }

        return $response->json();
    }

    protected function storeRun(array $data): DataMiningRun
    {
        return DataMiningRun::create([
            'analysis_type' => $this->getAnalysisType(),
            'status' => $data['status'] ?? 'completed',
            'date_range_start' => $data['date_range']['from'] ?? now()->subDays(30)->format('Y-m-d'),
            'date_range_end' => $data['date_range']['to'] ?? now()->format('Y-m-d'),
            'parameters' => [],
            'preprocessing_logs' => $data['preprocessing_logs'] ?? null,
            'result' => $data,
            'charts' => $data['charts'] ?? null,
            'error_message' => null,
            'run_at' => now(),
            'user_id' => Auth::id(),
        ]);
    }

    protected function getViewUrl(DataMiningRun $record): string
    {
        return url("admin/{$this->getViewSlug()}/{$record->id}");
    }

    protected function getViewSlug(): string
    {
        return str_replace('_', '-', $this->getAnalysisType());
    }
}
