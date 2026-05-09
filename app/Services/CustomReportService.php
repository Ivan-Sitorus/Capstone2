<?php

namespace App\Services;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomReportService
{
    /**
     * Category identifier prefixes used in $config['categories']:
     * - menu:{id}              Menu category (by categories.id)
     * - unexpected_income      UnexpectedTransaction jenis='pemasukan'
     * - bahan_baku             IngredientBatch purchases
     * - unexpected_expense     UnexpectedTransaction jenis='pengeluaran'
     *
     * Empty categories array = include ALL categories from all sources.
     */

    /**
     * @param  array  $config  ['date_start','date_end','categories'=>[],'aggregation'=>'daily'|'monthly']
     * @return array  ['config','rows'=>[['date','category','type','amount','running_total']],'summary'=>[...]]
     */
    public function generate(array $config): array
    {
        $dateStart  = Carbon::parse($config['date_start']);
        $dateEnd    = Carbon::parse($config['date_end']);
        $from       = $dateStart->copy()->startOfDay();
        $to         = $dateEnd->copy()->endOfDay();
        $aggregation = ($config['aggregation'] ?? 'monthly') === 'daily' ? 'daily' : 'monthly';
        $categoryFilters = $config['categories'] ?? [];

        [$menuCatIds, $includeUncInc, $includeBb, $includeUncExp]
            = $this->parseCategoryFilters($categoryFilters);

        $dateExpr = $aggregation === 'daily'
            ? "DATE(%s)"
            : "DATE_TRUNC('month', %s)::date";

        $unionParts = [];
        $bindings   = [];

        if ($menuCatIds !== null) {
            $unionParts[] = $this->incomeMenuSql($from, $to, $dateExpr, $menuCatIds, $bindings);
        }

        if ($includeUncInc) {
            $unionParts[] = $this->incomeUnexpectedSql($from, $to, $dateExpr, $bindings);
        }

        if ($includeBb) {
            $unionParts[] = $this->expenseBahanBakuSql($from, $to, $dateExpr, $bindings);
        }

        if ($includeUncExp) {
            $unionParts[] = $this->expenseUnexpectedSql($from, $to, $dateExpr, $bindings);
        }

        if (empty($unionParts)) {
            $rows = [];
        } else {
            $unionSql = implode("\nUNION ALL\n", $unionParts);
            $sql = "SELECT bucket_date, category, type, CAST(amount AS NUMERIC(15,2)) AS amount\n"
                  . "FROM ({$unionSql}) combined\n"
                  . "ORDER BY bucket_date, type DESC, category";

            $rows = DB::select($sql, $bindings);
        }

        $allRows = $this->fillMissingCombos(
            $rows,
            $from,
            $to,
            $aggregation,
            $menuCatIds,
            $includeUncInc,
            $includeBb,
            $includeUncExp
        );

        $this->sortRows($allRows);
        $runningTotal = 0;
        foreach ($allRows as &$row) {
            $sign = $row['type'] === 'Income' ? 1 : -1;
            $runningTotal += $sign * (float) $row['amount'];
            $row['running_total'] = round($runningTotal, 2);
        }
        unset($row);

        $totalIncome  = 0;
        $totalExpense = 0;
        foreach ($allRows as $row) {
            if ($row['type'] === 'Income') {
                $totalIncome += (float) $row['amount'];
            } else {
                $totalExpense += (float) $row['amount'];
            }
        }

        return [
            'config'    => $config,
            'rows'      => $allRows,
            'summary'   => [
                'total_income'  => round($totalIncome, 2),
                'total_expense' => round($totalExpense, 2),
                'net'           => round($totalIncome - $totalExpense, 2),
            ],
        ];
    }

    /**
     * Parse $config['categories'] into concrete category sets.
     * Returns [menuCatIds|null, bool, bool, bool].
     * null means "ALL" for that set. Empty filters = all null + all true.
     */
    private function parseCategoryFilters(array $categoryFilters): array
    {
        $includeAll = empty($categoryFilters);

        $menuCatIds    = null;
        $includeUncInc = true;
        $includeBb     = true;
        $includeUncExp = true;

        if ($includeAll) {
            return [$menuCatIds, $includeUncInc, $includeBb, $includeUncExp];
        }

        $menuIds = [];
        $uncInc  = false;
        $bb      = false;
        $uncExp  = false;

        foreach ($categoryFilters as $ident) {
            if ($ident === 'unexpected_income') {
                $uncInc = true;
            } elseif ($ident === 'bahan_baku') {
                $bb = true;
            } elseif ($ident === 'unexpected_expense') {
                $uncExp = true;
            } elseif (str_starts_with($ident, 'menu:')) {
                $id = (int) substr($ident, 5);
                if ($id > 0) {
                    $menuIds[] = $id;
                }
            }
        }

        return [
            ! empty($menuIds) ? $menuIds : null,
            $uncInc,
            $bb,
            $uncExp,
        ];
    }

    /**
     * Income from paid orders, grouped by menu category.
     */
    private function incomeMenuSql(Carbon $from, Carbon $to, string $dateExpr, ?array $menuCatIds, array &$bindings): string
    {
        $dateCol = sprintf($dateExpr, 'o.created_at');

        $where = "o.is_paid = true AND o.created_at BETWEEN ? AND ?";
        $bindings[] = $from;
        $bindings[] = $to;

        if (! empty($menuCatIds)) {
            $placeholders = $this->buildInPlaceholders($menuCatIds);
            $where .= " AND c.id IN ({$placeholders})";
            foreach ($menuCatIds as $id) {
                $bindings[] = $id;
            }
        }

        return "SELECT {$dateCol} AS bucket_date, c.name AS category, 'Income' AS type,\n"
              ."       COALESCE(SUM(oi.subtotal), 0) AS amount\n"
              ."FROM order_items oi\n"
              ."JOIN orders o ON o.id = oi.order_id\n"
              ."JOIN menus m ON m.id = oi.menu_id\n"
              ."JOIN categories c ON c.id = m.category_id\n"
              ."WHERE {$where}\n"
              ."GROUP BY {$dateCol}, c.name";
    }

    /**
     * Income from UnexpectedTransaction where jenis = 'pemasukan'.
     */
    private function incomeUnexpectedSql(Carbon $from, Carbon $to, string $dateExpr, array &$bindings): string
    {
        $dateCol = sprintf($dateExpr, 'created_at');

        $bindings[] = $from;
        $bindings[] = $to;

        return "SELECT {$dateCol} AS bucket_date, 'Unexpected Income' AS category, 'Income' AS type,\n"
              ."       COALESCE(SUM(nominal), 0) AS amount\n"
              ."FROM unexpected_transactions\n"
              ."WHERE jenis = 'pemasukan' AND created_at BETWEEN ? AND ?\n"
              ."GROUP BY {$dateCol}";
    }

    /**
     * Expense from IngredientBatch purchases as "Bahan Baku".
     */
    private function expenseBahanBakuSql(Carbon $from, Carbon $to, string $dateExpr, array &$bindings): string
    {
        $dateCol = sprintf($dateExpr, 'ib.received_at');

        $bindings[] = $from;
        $bindings[] = $to;

        return "SELECT {$dateCol} AS bucket_date, 'Bahan Baku' AS category, 'Expense' AS type,\n"
              ."       COALESCE(SUM(ib.quantity * ib.cost_per_unit), 0) AS amount\n"
              ."FROM ingredient_batches ib\n"
              ."WHERE ib.received_at BETWEEN ? AND ?\n"
              ."GROUP BY {$dateCol}";
    }

    /**
     * Expense from UnexpectedTransaction where jenis = 'pengeluaran'.
     */
    private function expenseUnexpectedSql(Carbon $from, Carbon $to, string $dateExpr, array &$bindings): string
    {
        $dateCol = sprintf($dateExpr, 'created_at');

        $bindings[] = $from;
        $bindings[] = $to;

        return "SELECT {$dateCol} AS bucket_date, 'Unexpected' AS category, 'Expense' AS type,\n"
              ."       COALESCE(SUM(nominal), 0) AS amount\n"
              ."FROM unexpected_transactions\n"
              ."WHERE jenis = 'pengeluaran' AND created_at BETWEEN ? AND ?\n"
              ."GROUP BY {$dateCol}";
    }

    /**
     * Fill zero-amount rows for every expected (date, category, type) combination
     * so empty categories are not hidden.
     */
    private function fillMissingCombos(
        array   $rows,
        Carbon  $from,
        Carbon  $to,
        string  $aggregation,
        ?array  $menuCatIds,
        bool    $includeUncInc,
        bool    $includeBb,
        bool    $includeUncExp,
    ): array {
        $buckets = [];
        $cur     = $from->copy();
        if ($aggregation === 'daily') {
            while ($cur->lte($to)) {
                $buckets[] = $cur->format('Y-m-d');
                $cur->addDay();
            }
        } else {
            $cur = $cur->startOfMonth();
            $endMonth = $to->copy()->endOfMonth()->startOfMonth();
            while ($cur->lte($endMonth)) {
                $buckets[] = $cur->format('Y-m-d');
                $cur->addMonth();
            }
        }

        $incCategoryNames = [];

        if ($menuCatIds !== null) {
            $incCategoryNames = Category::whereIn('id', $menuCatIds)
                ->where('is_active', true)
                ->pluck('name')
                ->toArray();
        } else {
            $incCategoryNames = Category::where('is_active', true)->pluck('name')->toArray();
        }

        $keyed = [];
        foreach ($rows as $r) {
            $bd = $r->bucket_date;
            if ($aggregation === 'monthly' && strlen($bd) > 10) {
                $bd = substr($bd, 0, 10);
            }
            $keyed["{$bd}|{$r->category}|{$r->type}"] = $r;
        }

        $filled = [];
        foreach ($buckets as $date) {
            foreach ($incCategoryNames as $catName) {
                $k = "{$date}|{$catName}|Income";
                $filled[] = isset($keyed[$k])
                    ? $this->toArray($keyed[$k])
                    : $this->makeRow($date, $catName, 'Income', 0);
            }

            if ($includeUncInc) {
                $k = "{$date}|Unexpected Income|Income";
                $filled[] = isset($keyed[$k])
                    ? $this->toArray($keyed[$k])
                    : $this->makeRow($date, 'Unexpected Income', 'Income', 0);
            }

            if ($includeBb) {
                $k = "{$date}|Bahan Baku|Expense";
                $filled[] = isset($keyed[$k])
                    ? $this->toArray($keyed[$k])
                    : $this->makeRow($date, 'Bahan Baku', 'Expense', 0);
            }

            if ($includeUncExp) {
                $k = "{$date}|Unexpected|Expense";
                $filled[] = isset($keyed[$k])
                    ? $this->toArray($keyed[$k])
                    : $this->makeRow($date, 'Unexpected', 'Expense', 0);
            }
        }

        return $filled;
    }

    /**
     * Sort rows: date asc, then Income before Expense, then category asc.
     */
    private function sortRows(array &$rows): void
    {
        usort($rows, function (array $a, array $b) {
            $cmp = strcmp($a['date'], $b['date']);
            if ($cmp !== 0) {
                return $cmp;
            }
            $typeCmp = strcmp($b['type'], $a['type']);
            if ($typeCmp !== 0) {
                return $typeCmp;
            }
            return strcmp($a['category'], $b['category']);
        });
    }

    private function buildInPlaceholders(array $items): string
    {
        return implode(', ', array_fill(0, count($items), '?'));
    }

    private function makeRow(string $date, string $category, string $type, float $amount): array
    {
        return [
            'date'          => $date,
            'category'      => $category,
            'type'          => $type,
            'amount'        => round($amount, 2),
            'running_total' => 0,
        ];
    }

    private function toArray(\stdClass $obj): array
    {
        return [
            'date'          => $obj->bucket_date,
            'category'      => $obj->category,
            'type'          => $obj->type,
            'amount'        => (float) $obj->amount,
            'running_total' => 0,
        ];
    }
}
