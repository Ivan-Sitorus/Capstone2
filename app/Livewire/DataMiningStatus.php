<?php

namespace App\Livewire;

use App\Models\DataminingRun;
use Filament\Notifications\Notification;
use Livewire\Component;

class DataMiningStatus extends Component
{
    public int $lastSeenId = 0;

    public function mount(): void
    {
        $this->lastSeenId = (int) (DataminingRun::max('id') ?? 0);
    }

    public function poll(): void
    {
        if (! auth('admin')->check()) {
            return;
        }

        $user = auth('admin')->user();

        $finished = DataminingRun::query()
            ->whereIn('status', ['completed', 'failed'])
            ->where('id', '>', $this->lastSeenId)
            ->orderBy('id')
            ->get();

        if ($finished->isEmpty()) {
            return;
        }

        $labels = [
            'prediction'            => 'Prediksi Menu',
            'clustering'            => 'Klasterisasi Menu',
            'association'           => 'Asosiatif Menu',
            'clustering-bahan-baku' => 'Klasterisasi Bahan Baku',
            'prediction-bahan-baku' => 'Prediksi Bahan Baku',
        ];

        foreach ($finished as $run) {
            $label = $labels[$run->type] ?? $run->type;

            $notification = Notification::make()
                ->title($run->status === 'completed' ? "{$label} selesai" : "{$label} gagal")
                ->body($run->status === 'completed'
                    ? 'Hasil sudah tersedia dan dapat dilihat pada halaman ringkasan.'
                    : ($run->error ?: 'Terjadi kesalahan saat memproses data.'));

            if ($run->status === 'completed') {
                $notification->success();
            } else {
                $notification->danger();
            }

            $notification->sendToDatabase($user);
        }

        $this->lastSeenId = (int) $finished->max('id');
    }

    public function render()
    {
        return view('livewire.data-mining-status');
    }
}
