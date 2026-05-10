<?php

namespace App\DTO;

class ReportRow
{
    /**
     * Valid row types.
     */
    public const TYPE_INCOME     = 'Income';
    public const TYPE_EXPENSE    = 'Expense';
    public const TYPE_SECTION    = 'Section';
    public const TYPE_TOTAL      = 'Total';
    public const TYPE_GRAND_TOTAL = 'GrandTotal';

    /**
     * @param  string       $date          Date bucket (Y-m-d format)
     * @param  string       $category      Category label (e.g. "Pendapatan", "Bahan Baku")
     * @param  string       $type          One of Income|Expense|Section|Total|GrandTotal
     * @param  float        $amount        Monetary value for this row
     * @param  float|null   $runningTotal  Cumulative running balance
     * @param  int          $indentLevel   Visual indentation depth (0 = root)
     * @param  bool         $isBold        Whether this row should be rendered bold
     * @param  array        $rawData       Original source data preserved for extensibility
     */
    public function __construct(
        public readonly string  $date,
        public readonly string  $category,
        public readonly string  $type,
        public readonly float   $amount,
        public readonly ?float  $runningTotal = null,
        public readonly int     $indentLevel = 0,
        public readonly bool    $isBold = false,
        public readonly array   $rawData = [],
    ) {
    }

    /**
     * Create a ReportRow from an associative array.
     *
     * Expected keys: date, category, type, amount, running_total (optional),
     *                indent_level (optional), is_bold (optional), raw_data (optional).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            date:        $data['date'] ?? '',
            category:    $data['category'] ?? '',
            type:        $data['type'] ?? self::TYPE_INCOME,
            amount:      (float) ($data['amount'] ?? 0),
            runningTotal: array_key_exists('running_total', $data) ? (float) $data['running_total'] : null,
            indentLevel: (int) ($data['indent_level'] ?? $data['indentLevel'] ?? 0),
            isBold:      (bool) ($data['is_bold'] ?? $data['isBold'] ?? false),
            rawData:     (array) ($data['raw_data'] ?? $data['rawData'] ?? []),
        );
    }

    /**
     * Convert back to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'date'          => $this->date,
            'category'      => $this->category,
            'type'          => $this->type,
            'amount'        => $this->amount,
            'running_total' => $this->runningTotal,
            'indent_level'  => $this->indentLevel,
            'is_bold'       => $this->isBold,
            'raw_data'      => $this->rawData,
        ];
    }

    // ─── Type-check helpers ───────────────────────────────────────────

    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    public function isTotal(): bool
    {
        return $this->type === self::TYPE_TOTAL;
    }

    public function isSection(): bool
    {
        return $this->type === self::TYPE_SECTION;
    }

    public function isGrandTotal(): bool
    {
        return $this->type === self::TYPE_GRAND_TOTAL;
    }
}
