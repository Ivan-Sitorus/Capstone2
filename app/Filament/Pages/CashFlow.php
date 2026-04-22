<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;

class CashFlow extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'Finance Details';
    protected static ?string $navigationLabel = 'Cash Flow';
    protected static ?string $title = 'Cash Flow';
    protected static ?int $navigationSort = 0;

    public string $period       = 'day';
    public string $typeFilter   = 'all';
    public string $search       = '';
    public string $catFilter    = '';

    public function mount(): void
    {
        $this->period     = 'day';
        $this->typeFilter = 'all';
        $this->search     = '';
        $this->catFilter  = '';
    }

    public function updatedPeriod(): void
    {
        $this->dispatch('cashflow-period-changed', period: $this->period);
    }

    public function getView(): string { return 'filament.pages.cash-flow'; }
    public function getTitle(): string { return 'Cash Flow'; }

    // ── internal ───────────────────────────────────────────────────────────

    private function dateRange(): array
    {
        return match ($this->period) {
            'day'      => [Carbon::today(),              Carbon::today()->endOfDay()],
            'year'     => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'all_time' => [Carbon::createFromDate(2000, 1, 1), Carbon::now()->endOfDay()],
            default    => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    private function prevRange(): array
    {
        [$s, $e] = $this->dateRange();
        if ($this->period === 'all_time') return [$s, $e];
        $days    = $s->diffInDays($e) + 1;
        $prevEnd = $s->copy()->subDay();
        return [$prevEnd->copy()->subDays($days - 1), $prevEnd];
    }

    private static function pctChange(float $current, float $prev): ?float
    {
        return $prev > 0 ? (($current - $prev) / $prev) * 100 : null;
    }

    // ── public data ────────────────────────────────────────────────────────

    public function getSummary(): array
    {
        [$s, $e]   = $this->dateRange();
        [$ps, $pe] = $this->prevRange();

        $income  = (float) Income::whereBetween('date', [$s, $e])->sum('amount');
        $expense = (float) Expense::whereBetween('date', [$s, $e])->sum('amount');
        $net     = $income - $expense;
        $margin  = $income > 0 ? ($net / $income) * 100 : 0;
        $txnCnt  = Income::whereBetween('date', [$s, $e])->count()
                 + Expense::whereBetween('date', [$s, $e])->count();

        $pIncome  = (float) Income::whereBetween('date', [$ps, $pe])->sum('amount');
        $pExpense = (float) Expense::whereBetween('date', [$ps, $pe])->sum('amount');
        $pNet     = $pIncome - $pExpense;

        return [
            'totalIncome'   => $income,
            'totalExpense'  => $expense,
            'netFlow'       => $net,
            'margin'        => $margin,
            'txnCount'      => $txnCnt,
            'incomeChange'  => self::pctChange($income, $pIncome),
            'expenseChange' => self::pctChange($expense, $pExpense),
            'netChange'     => $pNet != 0 ? (($net - $pNet) / abs($pNet)) * 100 : null,
        ];
    }

    public function getCategoryBreakdown(): array
    {
        [$s, $e] = $this->dateRange();

        $iTot = (float) Income::whereBetween('date', [$s, $e])->sum('amount') ?: 1;
        $eTot = (float) Expense::whereBetween('date', [$s, $e])->sum('amount') ?: 1;

        $incomeRows = Income::whereBetween('date', [$s, $e])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')->orderByDesc('total')->get()
            ->map(fn ($r) => [
                'name'   => ucfirst($r->category),
                'key'    => $r->category,
                'amount' => (float) $r->total,
                'pct'    => round(($r->total / $iTot) * 100, 1),
            ])->toArray();

        $expenseRows = Expense::whereBetween('date', [$s, $e])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')->orderByDesc('total')->get()
            ->map(fn ($r) => [
                'name'   => ucfirst(str_replace('_', ' ', $r->category)),
                'key'    => $r->category,
                'amount' => (float) $r->total,
                'pct'    => round(($r->total / $eTot) * 100, 1),
            ])->toArray();

        return ['income' => $incomeRows, 'expense' => $expenseRows];
    }

    public function getTopSources(): array
    {
        [$s, $e] = $this->dateRange();
        $max = (float) Income::whereBetween('date', [$s, $e])
            ->selectRaw('SUM(amount) as total')->groupBy('source')
            ->orderByDesc('total')->value('total') ?: 1;

        return Income::whereBetween('date', [$s, $e])
            ->selectRaw('source, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('source')->orderByDesc('total')->limit(5)->get()
            ->map(fn ($r) => [
                'name'  => $r->source,
                'total' => (float) $r->total,
                'cnt'   => $r->cnt,
                'pct'   => round(($r->total / $max) * 100),
            ])->toArray();
    }

    public function getTopVendors(): array
    {
        [$s, $e] = $this->dateRange();
        $max = (float) Expense::whereBetween('date', [$s, $e])
            ->selectRaw('SUM(amount) as total')->groupBy('vendor')
            ->orderByDesc('total')->value('total') ?: 1;

        return Expense::whereBetween('date', [$s, $e])
            ->selectRaw('vendor, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('vendor')->orderByDesc('total')->limit(5)->get()
            ->map(fn ($r) => [
                'name'  => $r->vendor,
                'total' => (float) $r->total,
                'cnt'   => $r->cnt,
                'pct'   => round(($r->total / $max) * 100),
            ])->toArray();
    }

    public function getTransactions(): array
    {
        [$s, $e] = $this->dateRange();
        $rows    = [];

        if ($this->typeFilter !== 'expense') {
            $q = Income::whereBetween('date', [$s, $e]);
            if ($this->search)    $q->where(fn ($q) => $q->where('source', 'ilike', "%{$this->search}%")->orWhere('description', 'ilike', "%{$this->search}%"));
            if ($this->catFilter) $q->where('category', $this->catFilter);
            foreach ($q->orderBy('date', 'desc')->get() as $r) {
                $rows[] = ['type' => 'income', 'date' => $r->date, 'label' => $r->source,
                           'category' => $r->category, 'amount' => (float) $r->amount,
                           'description' => $r->description, 'method' => null];
            }
        }

        if ($this->typeFilter !== 'income') {
            $q = Expense::whereBetween('date', [$s, $e]);
            if ($this->search)    $q->where(fn ($q) => $q->where('vendor', 'ilike', "%{$this->search}%")->orWhere('description', 'ilike', "%{$this->search}%"));
            if ($this->catFilter) $q->where('category', $this->catFilter);
            foreach ($q->orderBy('date', 'desc')->get() as $r) {
                $rows[] = ['type' => 'expense', 'date' => $r->date, 'label' => $r->vendor,
                           'category' => $r->category, 'amount' => (float) $r->amount,
                           'description' => $r->description, 'method' => $r->payment_method];
            }
        }

        usort($rows, fn ($a, $b) => $b['date'] <=> $a['date']);
        return $rows;
    }

    public function getSvgChart(): array
    {
        [$s, $e]  = $this->dateRange();
        $diffDays = $s->diffInDays($e);

        $raw = $diffDays > 60
            ? $this->chartByWeek($s, $e)
            : $this->chartByDay($s, $e);

        if (count($raw) < 2) return ['empty' => true];

        $vW = 800; $vH = 200;
        $pL = 72; $pR = 16; $pT = 12; $pB = 32;
        $cW = $vW - $pL - $pR;
        $cH = $vH - $pT - $pB;
        $bot = $pT + $cH;
        $n   = count($raw);
        $max = collect($raw)->map(fn ($d) => max($d['income'], $d['expense']))->max() ?: 1;

        $pts = [];
        foreach ($raw as $i => $d) {
            $x  = $pL + ($n > 1 ? $i / ($n - 1) : 0.5) * $cW;
            $yi = $pT + $cH - ($d['income']  / $max) * $cH;
            $ye = $pT + $cH - ($d['expense'] / $max) * $cH;
            $pts[] = ['x' => round($x, 1), 'yi' => round($yi, 1), 'ye' => round($ye, 1),
                      'label' => $d['label'], 'income' => $d['income'], 'expense' => $d['expense']];
        }

        $fx = $pts[0]['x']; $lx = $pts[count($pts) - 1]['x'];
        $iLine = implode(' ', array_map(fn ($p) => "{$p['x']},{$p['yi']}", $pts));
        $eLine = implode(' ', array_map(fn ($p) => "{$p['x']},{$p['ye']}", $pts));
        $iArea = "M{$fx},{$bot} " . implode(' ', array_map(fn ($p) => "L{$p['x']},{$p['yi']}", $pts)) . " L{$lx},{$bot}Z";
        $eArea = "M{$fx},{$bot} " . implode(' ', array_map(fn ($p) => "L{$p['x']},{$p['ye']}", $pts)) . " L{$lx},{$bot}Z";

        $grid = [];
        for ($g = 0; $g <= 4; $g++) {
            $y   = $pT + ($g / 4) * $cH;
            $val = $max * (1 - $g / 4);
            $grid[] = ['y' => round($y, 1), 'val' => $val, 'x1' => $pL, 'x2' => $vW - $pR];
        }

        $step    = max(1, intval($n / 7));
        $xLabels = array_values(array_filter($pts, fn ($p, $i) => $i % $step === 0 || $i === $n - 1, ARRAY_FILTER_USE_BOTH));

        return compact('pts', 'iLine', 'eLine', 'iArea', 'eArea', 'grid', 'xLabels', 'vW', 'vH', 'pL', 'bot', 'max', 'cH', 'pT');
    }

    private function chartByDay(Carbon $s, Carbon $e): array
    {
        $inc = Income::whereBetween('date', [$s, $e])->selectRaw('date, SUM(amount) as total')
            ->groupBy('date')->orderBy('date')->get()
            ->mapWithKeys(fn ($r) => [Carbon::parse($r->date)->format('Y-m-d') => (float) $r->total])->toArray();
        $exp = Expense::whereBetween('date', [$s, $e])->selectRaw('date, SUM(amount) as total')
            ->groupBy('date')->orderBy('date')->get()
            ->mapWithKeys(fn ($r) => [Carbon::parse($r->date)->format('Y-m-d') => (float) $r->total])->toArray();

        $result = []; $cur = $s->copy();
        while ($cur->lte($e)) {
            $k = $cur->format('Y-m-d');
            $result[] = ['label' => $cur->format('d/m'), 'income' => $inc[$k] ?? 0, 'expense' => $exp[$k] ?? 0];
            $cur->addDay();
        }
        return $result;
    }

    private function chartByWeek(Carbon $s, Carbon $e): array
    {
        $data = []; $cur = $s->copy()->startOfWeek();
        while ($cur->lte($e)) {
            $we  = $cur->copy()->endOfWeek();
            $inc = (float) Income::whereBetween('date', [$cur, $we])->sum('amount');
            $exp = (float) Expense::whereBetween('date', [$cur, $we])->sum('amount');
            $data[] = ['label' => $cur->format('d/m'), 'income' => $inc, 'expense' => $exp];
            $cur->addWeek();
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_income')
                ->label('Tambah Pemasukan')
                ->color('success')
                ->icon('heroicon-o-plus-circle')
                ->url(fn () => route('filament.admin.resources.incomes.create')),
            Action::make('add_expense')
                ->label('Tambah Pengeluaran')
                ->color('danger')
                ->icon('heroicon-o-minus-circle')
                ->url(fn () => route('filament.admin.resources.expenses.create')),
        ];
    }
}
