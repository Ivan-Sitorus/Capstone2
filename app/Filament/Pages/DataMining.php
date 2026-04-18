<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class DataMining extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Data Mining';

    protected static ?string $title = 'Data Mining';

    protected static ?int $navigationSort = 10;

    public function getView(): string
    {
        return 'filament.pages.data-mining';
    }

    // State untuk filter
    public string $period = '30';
    public string $activeTab = 'forecast';

    public function mount(): void
    {
        $this->period = '30';
        $this->activeTab = 'forecast';
    }

    public function getTitle(): string
    {
        return 'Data Mining';
    }

    /**
     * Data dummy placeholder — akan diganti dengan hasil API model data mining.
     * Struktur ini menyesuaikan output umum dari model prediksi & rekomendasi.
     */
    public function getForecastData(): array
    {
        // TODO: Ganti dengan pemanggilan ke FastAPI / endpoint model data mining
        return [
            ['date' => 'Senin',   'predicted' => 450000,  'actual' => 420000],
            ['date' => 'Selasa',  'predicted' => 380000,  'actual' => 395000],
            ['date' => 'Rabu',    'predicted' => 510000,  'actual' => null],
            ['date' => 'Kamis',   'predicted' => 490000,  'actual' => null],
            ['date' => 'Jumat',   'predicted' => 620000,  'actual' => null],
            ['date' => 'Sabtu',   'predicted' => 780000,  'actual' => null],
            ['date' => 'Minggu',  'predicted' => 720000,  'actual' => null],
        ];
    }

    public function getTopMenuData(): array
    {
        // TODO: Ganti dengan hasil model asosiasi / rekomendasi
        return [
            ['rank' => 1, 'name' => 'Kopi Robusta',    'orders' => 128, 'revenue' => 1536000, 'trend' => 'up'],
            ['rank' => 2, 'name' => 'Kopi Latte',      'orders' => 95,  'revenue' => 1330000, 'trend' => 'up'],
            ['rank' => 3, 'name' => 'Teh Tarik',       'orders' => 87,  'revenue' => 783000,  'trend' => 'down'],
            ['rank' => 4, 'name' => 'Roti Bakar',      'orders' => 76,  'revenue' => 912000,  'trend' => 'up'],
            ['rank' => 5, 'name' => 'Matcha Latte',    'orders' => 64,  'revenue' => 960000,  'trend' => 'stable'],
        ];
    }

    public function getAssociationRules(): array
    {
        // TODO: Ganti dengan hasil algoritma Apriori / FP-Growth dari model
        return [
            [
                'antecedent' => 'Kopi Robusta',
                'consequent' => 'Roti Bakar',
                'support'    => '32%',
                'confidence' => '78%',
                'lift'       => '2.4',
            ],
            [
                'antecedent' => 'Kopi Latte',
                'consequent' => 'Croissant',
                'support'    => '24%',
                'confidence' => '65%',
                'lift'       => '1.9',
            ],
            [
                'antecedent' => 'Matcha Latte',
                'consequent' => 'Cheesecake',
                'support'    => '18%',
                'confidence' => '71%',
                'lift'       => '2.1',
            ],
        ];
    }

    public function getPeakHourData(): array
    {
        // TODO: Ganti dengan hasil clustering jam ramai dari model
        return [
            ['hour' => '07-08', 'orders' => 12],
            ['hour' => '08-09', 'orders' => 28],
            ['hour' => '09-10', 'orders' => 35],
            ['hour' => '10-11', 'orders' => 42],
            ['hour' => '11-12', 'orders' => 58],
            ['hour' => '12-13', 'orders' => 75],
            ['hour' => '13-14', 'orders' => 68],
            ['hour' => '14-15', 'orders' => 45],
            ['hour' => '15-16', 'orders' => 38],
            ['hour' => '16-17', 'orders' => 52],
            ['hour' => '17-18', 'orders' => 61],
            ['hour' => '18-19', 'orders' => 48],
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Perbarui Data')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    // TODO: Trigger refresh dari API model data mining
                    Notification::make()
                        ->title('Data diperbarui')
                        ->success()
                        ->send();
                }),
        ];
    }
}
