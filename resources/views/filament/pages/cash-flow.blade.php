<x-filament-panels::page>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap');

    .cf-wrap {
        --cf-card      : #1c2130;
        --cf-bg        : #080c14;
        --cf-text      : #ffffff;
        --cf-text-sub  : #d0d8e8;
        --cf-text-muted: #7e8fa8;
        --cf-border    : rgba(255,255,255,0.13);
        --cf-surface   : #242b3d;
        --cf-surface-h : #2d3650;
        --cf-tab-active: #2d3650;
        --cf-shadow    : 0px 24px 48px rgba(0,0,0,0.6);
        --cf-tab-txt   : #8898b4;
        font-family: 'Inter', system-ui, sans-serif;
    }
    .cf-wrap * { box-sizing: border-box; }

    .ms {
        font-family: 'Material Symbols Outlined';
        font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24;
        vertical-align: middle;
        display: inline-flex;
        align-items: center;
        line-height: 1;
    }
    .ms-fill { font-variation-settings: 'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 24; }

    .cf-card {
        background   : var(--cf-card);
        border-radius: 12px;
        box-shadow   : var(--cf-shadow);
    }

    .cf-stat {
        display        : flex;
        align-items    : center;
        justify-content: space-between;
        padding        : 13px 15px;
        border-radius  : 10px;
        background     : var(--cf-surface);
        transition     : background 0.15s;
    }
    .cf-stat:hover { background: var(--cf-surface-h); }

    .cf-tbl { width:100%; border-collapse:collapse; text-align:left; }
    .cf-tbl th {
        padding-bottom : 12px;
        font-size      : 11px;
        font-weight    : 700;
        text-transform : uppercase;
        letter-spacing : 0.06em;
        color          : var(--cf-text-muted);
        border-bottom  : 1px solid var(--cf-border);
    }
    .cf-tbl td { padding: 16px 0; border-bottom: 1px solid var(--cf-border); }
    .cf-tbl tr:last-child td { border-bottom: none; }
    .cf-tbl tbody tr { transition: background 0.12s; }
    .cf-tbl tbody tr:hover { background: var(--cf-surface); }

    .cf-search {
        padding     : 8px 12px 8px 34px;
        font-size   : 12px;
        font-weight : 500;
        background  : var(--cf-surface);
        border      : none;
        border-radius:8px;
        outline     : none;
        color       : var(--cf-text);
        font-family : 'Inter', system-ui;
        width       : 180px;
    }

    .cf-ghost {
        width        : 100%;
        padding      : 12px;
        border-radius: 8px;
        border       : 1px solid var(--cf-border);
        background   : transparent;
        color        : #3b82f6;
        font-size    : 13px;
        font-weight  : 700;
        cursor       : pointer;
        transition   : background 0.15s;
        font-family  : 'Inter', system-ui;
    }
    .cf-ghost:hover { background: rgba(59,130,246,0.08); }

    .cf-gradient { background: linear-gradient(135deg,#004bca 0%,#0061ff 100%); }

    .cf-badge {
        display      : inline-flex;
        align-items  : center;
        gap          : 2px;
        padding      : 3px 10px;
        border-radius: 9999px;
        font-size    : 11px;
        font-weight  : 700;
    }

    .g7-3 { display:grid; grid-template-columns:7fr 3fr; gap:24px; }
    .g6-4 { display:grid; grid-template-columns:6fr 4fr; gap:24px; }

    @media(max-width:1200px){ .g7-3,.g6-4{ grid-template-columns:1fr; } }
</style>

@php $period = $this->period; @endphp

<div class="cf-wrap" style="display:flex;flex-direction:column;gap:26px;">

    {{-- ── Period tabs (Filament native) ─────────────────────────── --}}
    <x-filament::tabs label="Periode" contained>
        <x-filament::tabs.item :active="$period === 'day'" wire:click="$set('period','day')">
            Hari Ini
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$period === 'month'" wire:click="$set('period','month')">
            Bulan Ini
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$period === 'year'" wire:click="$set('period','year')">
            Tahun Ini
        </x-filament::tabs.item>
        <x-filament::tabs.item :active="$period === 'all_time'" wire:click="$set('period','all_time')">
            Semua Waktu
        </x-filament::tabs.item>
    </x-filament::tabs>

    {{-- ── Filament StatsOverviewWidget (4 KPI cards) ──────────────── --}}
    @livewire(\App\Filament\Widgets\CashFlowStatsWidget::class, ['period' => $this->period])

    {{-- ── Filament ChartWidget (full width) ──────────────────────── --}}
    @livewire(\App\Filament\Widgets\CashFlowChartWidget::class, ['period' => $this->period])

    {{-- ── Tabel Transaksi Tak Terduga ───────────────────────────── --}}
    @livewire(\App\Filament\Widgets\UnexpectedTransactionWidget::class)


</div>
</x-filament-panels::page>
