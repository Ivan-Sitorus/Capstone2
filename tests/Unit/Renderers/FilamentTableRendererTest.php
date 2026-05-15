<?php

namespace Tests\Unit\Renderers;

use App\DTO\ReportData;
use App\DTO\ReportRow;
use App\Renderers\FilamentTableRenderer;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class FilamentTableRendererTest extends TestCase
{
    private function makeReportRow(
        string $category = 'Test Item',
        string $type = ReportRow::TYPE_INCOME,
        float $amount = 100000,
        int $indentLevel = 0,
        bool $isBold = false,
    ): ReportRow {
        return new ReportRow(
            date: '',
            category: $category,
            type: $type,
            amount: $amount,
            indentLevel: $indentLevel,
            isBold: $isBold,
        );
    }

    private function makeReportData(array $rows = []): ReportData
    {
        if (empty($rows)) {
            $rows = [
                $this->makeReportRow('Pendapatan', ReportRow::TYPE_INCOME, 500000),
                $this->makeReportRow('HPP', ReportRow::TYPE_EXPENSE, 200000),
                $this->makeReportRow('Laba Bersih', ReportRow::TYPE_GRAND_TOTAL, 300000, isBold: true),
            ];
        }

        return new ReportData(
            type: ReportData::TYPE_SIMPLE,
            title: 'Test Report',
            dateStart: '2026-01-01',
            dateEnd: '2026-01-31',
            aggregation: 'daily',
            rows: $rows,
        );
    }

    // ─── Pagination ───────────────────────────────────────────────────

    public function test_table_is_paginated_false(): void
    {
        $data = $this->makeReportData();
        $table = FilamentTableRenderer::table($data);

        $this->assertFalse($table->isPaginated());
    }

    // ─── Search / Sort / Filters disabled ─────────────────────────────

    public function test_table_search_is_disabled(): void
    {
        $data = $this->makeReportData();
        $table = FilamentTableRenderer::table($data);

        $this->assertFalse($table->isSearchable());
    }

    public function test_table_has_no_filters(): void
    {
        $data = $this->makeReportData();
        $table = FilamentTableRenderer::table($data);

        $this->assertEmpty($table->getFilters());
    }

    // ─── Column alignment ─────────────────────────────────────────────

    public function test_amount_columns_are_right_aligned(): void
    {
        $data = $this->makeReportData();
        $table = FilamentTableRenderer::table($data);

        $amountColumn = $this->findColumnByName($table, 'amount');
        $this->assertNotNull($amountColumn, 'Amount column not found');

        $this->assertEquals(Alignment::End, $amountColumn->getAlignment());
    }

    public function test_amount_column_uses_monospace_font(): void
    {
        $data = $this->makeReportData();
        $table = FilamentTableRenderer::table($data);

        $amountColumn = $this->findColumnByName($table, 'amount');
        $this->assertNotNull($amountColumn);

        $this->assertEquals(FontFamily::Mono, $amountColumn->getFontFamily());
    }

    public function test_category_column_has_label_akun(): void
    {
        $data = $this->makeReportData();
        $table = FilamentTableRenderer::table($data);

        $categoryColumn = $this->findColumnByName($table, 'category');
        $this->assertNotNull($categoryColumn);

        $this->assertEquals('Akun', $categoryColumn->getLabel());
    }

    // ─── Row classes ──────────────────────────────────────────────────

    public function test_total_rows_have_bold_and_border_top(): void
    {
        $row = $this->makeReportRow('Laba Kotor', ReportRow::TYPE_TOTAL, 300000, isBold: true);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $classString = $this->getRecordClassesFor($table, $row);

        $this->assertStringContainsString('font-semibold', $classString);
        $this->assertStringContainsString('border-t', $classString);
        $this->assertStringContainsString('border-gray-300', $classString);
    }

    public function test_grand_total_rows_have_double_border_and_background(): void
    {
        $row = $this->makeReportRow('Net', ReportRow::TYPE_GRAND_TOTAL, 500000, isBold: true);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $classString = $this->getRecordClassesFor($table, $row);

        $this->assertStringContainsString('font-bold', $classString);
        $this->assertStringContainsString('border-t-2', $classString);
        $this->assertStringContainsString('bg-gray-50', $classString);
    }

    public function test_section_rows_have_background_color(): void
    {
        $row = $this->makeReportRow('Laporan Laba Rugi', ReportRow::TYPE_SECTION, 0, isBold: true);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $classString = $this->getRecordClassesFor($table, $row);

        $this->assertStringContainsString('bg-primary-50', $classString);
        $this->assertStringContainsString('font-semibold', $classString);
    }

    public function test_default_rows_have_hover_class(): void
    {
        $row = $this->makeReportRow('Pendapatan', ReportRow::TYPE_INCOME, 100000);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $classString = $this->getRecordClassesFor($table, $row);

        $this->assertStringContainsString('hover:bg-gray-50', $classString);
    }

    // ─── Indentation ──────────────────────────────────────────────────

    public function test_indented_rows_have_correct_padding(): void
    {
        $row = $this->makeReportRow('Sub Item', ReportRow::TYPE_INCOME, 50000, indentLevel: 2);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $categoryColumn = $this->findColumnByName($table, 'category');
        $this->assertNotNull($categoryColumn);

        $attrs = $this->getClosureResult($categoryColumn, 'extraAttributes', $row);
        $this->assertIsArray($attrs);
        $this->assertArrayHasKey('class', $attrs);
        $this->assertStringContainsString('indent-2', $attrs['class']);
    }

    public function test_level_zero_rows_have_no_indent_padding(): void
    {
        $row = $this->makeReportRow('Root Item', ReportRow::TYPE_INCOME, 50000, indentLevel: 0);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $categoryColumn = $this->findColumnByName($table, 'category');
        $this->assertNotNull($categoryColumn);

        $attrs = $this->getClosureResult($categoryColumn, 'extraAttributes', $row);
        $this->assertIsArray($attrs);
        $this->assertArrayHasKey('class', $attrs);
        $this->assertStringNotContainsString('indent-', $attrs['class']);
    }

    // ─── Dark mode ────────────────────────────────────────────────────

    public function test_dark_mode_classes_present_in_section_rows(): void
    {
        $row = $this->makeReportRow('Laporan Laba Rugi', ReportRow::TYPE_SECTION, 0, isBold: true);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $classString = $this->getRecordClassesFor($table, $row);

        $this->assertStringContainsString('dark:bg-primary-900/20', $classString);
    }

    public function test_dark_mode_classes_present_in_total_rows(): void
    {
        $row = $this->makeReportRow('Total', ReportRow::TYPE_TOTAL, 100000, isBold: true);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $classString = $this->getRecordClassesFor($table, $row);

        $this->assertStringContainsString('dark:border-gray-600', $classString);
    }

    public function test_dark_mode_classes_present_in_grand_total_rows(): void
    {
        $row = $this->makeReportRow('Grand', ReportRow::TYPE_GRAND_TOTAL, 100000, isBold: true);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $classString = $this->getRecordClassesFor($table, $row);

        $this->assertStringContainsString('dark:bg-gray-900/50', $classString);
        $this->assertStringContainsString('dark:border-gray-500', $classString);
    }

    public function test_dark_mode_classes_present_in_default_rows(): void
    {
        $row = $this->makeReportRow('Item', ReportRow::TYPE_INCOME, 50000);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $classString = $this->getRecordClassesFor($table, $row);

        $this->assertStringContainsString('dark:hover:bg-white/5', $classString);
    }

    // ─── Bold rows via extraAttributes ────────────────────────────────

    public function test_bold_rows_have_font_bold_in_category_extra_attributes(): void
    {
        $row = $this->makeReportRow('Bold Item', ReportRow::TYPE_TOTAL, 100000, isBold: true);
        $data = $this->makeReportData([$row]);
        $table = FilamentTableRenderer::table($data);

        $categoryColumn = $this->findColumnByName($table, 'category');
        $attrs = $this->getClosureResult($categoryColumn, 'extraAttributes', $row);

        $this->assertStringContainsString('font-bold', $attrs['class']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function findColumnByName(Table $table, string $name): ?Column
    {
        foreach ($table->getColumns() as $column) {
            if ($column->getName() === $name) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Extract a Closure from an object's property, invoke it with a value,
     * and return the result.
     *
     * Filament stores closures in protected properties (extraAttributes,
     * formatStateUsing, recordClasses, etc.). This helper uses reflection
     * to extract the closure and invoke it directly, bypassing the
     * evaluate() mechanism which requires a full rendering context.
     */
    private function getClosureResult(object $target, string $property, ReportRow $row): mixed
    {
        $ref = new ReflectionProperty($target, $property);
        $value = $ref->getValue($target);

        if (is_array($value) && count($value) > 0) {
            $closure = $value[0];
            if ($closure instanceof \Closure) {
                return $closure($row->toArray());
            }
        }

        return $value;
    }

    /**
     * Evaluate the recordClasses closure on the table for a given
     * ReportRow, returning the CSS class string.
     *
     * The Table::getRecordClasses() method expects Model|array typed
     * parameters, so we extract the closure via reflection and call
     * it directly with our ReportRow DTO.
     */
    private function getRecordClassesFor(Table $table, ReportRow $row): string
    {
        $ref = new ReflectionProperty($table, 'recordClasses');
        $closure = $ref->getValue($table);

        if ($closure instanceof \Closure) {
            return (string) $closure($row->toArray());
        }

        return '';
    }
}
