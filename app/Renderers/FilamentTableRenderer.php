<?php

namespace App\Renderers;

use App\DTO\ReportData;
use App\DTO\ReportRow;
use App\Helpers\AccountingFormatter;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class FilamentTableRenderer
{
    /**
     * Build a standalone Filament Table from a ReportData DTO.
     *
     * Uses a minimal internal livewire stub since Filament tables require a HasTable
     * implementation. Useful when a table must be rendered outside of a Livewire
     * component context.
     *
     * For integration with InteractsWithTable trait (i.e., inside a Filament Page or
     * Livewire component), use {@see self::configure()} instead.
     */
    public static function table(ReportData $data): Table
    {
        return self::configure(Table::make(self::makeLivewireStub()), $data);
    }

    /**
     * Configure an existing Filament Table from a ReportData DTO.
     *
     * Designed for use inside a Livewire component or Filament Page that already
     * implements HasTable + InteractsWithTable. The $table parameter is the
     * Livewire-managed table instance.
     *
     * @param  Table       $table  Livewire-managed table instance
     * @param  ReportData  $data   The report data to render
     * @return Table
     */
    public static function configure(Table $table, ReportData $data): Table
    {
        // Convert ReportRow DTOs to plain arrays before table consumption.
        // Filament's InteractsWithTable enforces Model|array record types
        // in its lifecycle (getTableRecords, getTableRecord, etc.), so
        // passing DTO objects causes a TypeError.
        $rows = array_map(fn (ReportRow $row): array => $row->toArray(), $data->rows);

        return $table
            ->records(fn (): \Illuminate\Support\Collection => collect($rows))
            ->paginated(false)
            ->searchable(false)
            ->columns([
                TextColumn::make('category')
                    ->label('Akun')
                    ->getStateUsing(fn (array $record): string => $record['category'] ?? '')
                    ->extraAttributes(function (array $record): array {
                        $indent = $record['indent_level'] ?? 0;
                        $isBold = $record['is_bold'] ?? false;
                        $type   = $record['type'] ?? '';
                        return [
                            'class' => 'text-sm'
                                . ($indent > 0 ? ' indent-' . $indent : '')
                                . ($isBold ? ' font-bold' : '')
                                . ($type === ReportRow::TYPE_SECTION ? ' font-semibold' : ''),
                        ];
                    }),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->getStateUsing(fn (array $record): string => AccountingFormatter::rupiahAccounting($record['amount'] ?? 0))
                    ->alignment(Alignment::End)
                    ->fontFamily(FontFamily::Mono)
                    ->extraAttributes(function (array $record): array {
                        return [
                            'class' => 'text-sm' . (($record['is_bold'] ?? false) ? ' font-bold' : ''),
                        ];
                    }),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([])
            ->recordUrl(null)
            ->defaultSort(null)
            ->recordClasses(function (array $record): string {
                $type = $record['type'] ?? '';
                if ($type === ReportRow::TYPE_GRAND_TOTAL) {
                    return 'font-bold border-t-2 border-gray-400 dark:border-gray-500 bg-gray-50 dark:bg-gray-900/50';
                }
                if ($type === ReportRow::TYPE_TOTAL) {
                    return 'font-semibold border-t border-gray-300 dark:border-gray-600';
                }
                if ($type === ReportRow::TYPE_SECTION) {
                    return 'bg-primary-50 dark:bg-primary-900/20 font-semibold';
                }
                return 'hover:bg-gray-50 dark:hover:bg-white/5';
            });
    }

    /**
     * Create a minimal HasTable stub sufficient for table configuration.
     *
     * Filament Tables require a HasTable implementation (typically a Livewire component
     * using InteractsWithTable). This anonymous class provides placeholder implementations
     * for every method in the interface. The table is configured solely via ->records()
     * (custom dataSource, no database query), so most of these methods return null/empty
     * defaults and are never called during configuration.
     *
     * @return HasTable
     */
    private static function makeLivewireStub(): HasTable
    {
        return new class implements HasTable
        {
            public function getTable(): Table
            {
                throw new \LogicException('Stub: getTable() should not be called during table configuration.');
            }

            public function callTableColumnAction(string $name, string $recordKey): mixed
            {
                return null;
            }

            public function deselectAllTableRecords(): void {}

            public function getActiveTableLocale(): ?string
            {
                return null;
            }

            /** @return array<int|string> */
            public function getAllSelectableTableRecordKeys(): array
            {
                return [];
            }

            public function getAllTableRecordsCount(): int
            {
                return 0;
            }

            public function getAllSelectableTableRecordsCount(): int
            {
                return 0;
            }

            /** @return array<string, mixed>|null */
            public function getTableFilterState(string $name): ?array
            {
                return null;
            }

            /** @return array<string, mixed>|null */
            public function getTableFilterFormState(string $name): ?array
            {
                return null;
            }

            public function getSelectedTableRecords(bool $shouldFetchSelectedRecords = true, ?int $chunkSize = null): \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|\Illuminate\Support\LazyCollection
            {
                return collect();
            }

            public function getSelectedTableRecordsQuery(bool $shouldFetchSelectedRecords = true, ?int $chunkSize = null): \Illuminate\Database\Eloquent\Builder
            {
                throw new \LogicException('Not implemented: stub does not support database queries.');
            }

            public function parseTableFilterName(string $name): string
            {
                return $name;
            }

            public function getTableGrouping(): ?\Filament\Tables\Grouping\Group
            {
                return null;
            }

            public function getMountedTableAction(): ?\Filament\Actions\Action
            {
                return null;
            }

            public function getMountedTableActionForm(): ?\Filament\Schemas\Schema
            {
                return null;
            }

            public function getMountedTableActionRecord(): ?\Illuminate\Database\Eloquent\Model
            {
                return null;
            }

            public function getMountedTableBulkAction(): ?\Filament\Actions\Action
            {
                return null;
            }

            public function getMountedTableBulkActionForm(): ?\Filament\Schemas\Schema
            {
                return null;
            }

            public function getTableFiltersForm(): \Filament\Schemas\Schema
            {
                throw new \LogicException('Not implemented: stub does not support filters.');
            }

            public function getTableRecords(): \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Contracts\Pagination\CursorPaginator
            {
                return collect();
            }

            public function getTableRecordsPerPage(): int|string|null
            {
                return null;
            }

            public function getTablePage(): int|string
            {
                return 1;
            }

            public function getTableSortColumn(): ?string
            {
                return null;
            }

            public function getTableSortDirection(): ?string
            {
                return null;
            }

            public function getAllTableSummaryQuery(): ?\Illuminate\Database\Eloquent\Builder
            {
                return null;
            }

            public function getPageTableSummaryQuery(): ?\Illuminate\Database\Eloquent\Builder
            {
                return null;
            }

            public function isTableColumnToggledHidden(string $name): bool
            {
                return false;
            }

            /** @return \Illuminate\Database\Eloquent\Model|array<string, mixed>|null */
            public function getTableRecord(?string $key): \Illuminate\Database\Eloquent\Model|array|null
            {
                return null;
            }

            /** @param \Illuminate\Database\Eloquent\Model|array<string, mixed> $record */
            public function getTableRecordKey(\Illuminate\Database\Eloquent\Model|array $record): string
            {
                return '';
            }

            public function toggleTableReordering(): void {}

            public function isTableReordering(): bool
            {
                return false;
            }

            public function isTableLoaded(): bool
            {
                return true;
            }

            public function hasTableSearch(): bool
            {
                return false;
            }

            public function resetTableSearch(): void {}

            public function resetTableColumnSearch(string $column): void {}

            public function getTableSearchIndicator(): \Filament\Tables\Filters\Indicator
            {
                throw new \LogicException('Not implemented.');
            }

            /** @return array<\Filament\Tables\Filters\Indicator> */
            public function getTableColumnSearchIndicators(): array
            {
                return [];
            }

            public function getFilteredTableQuery(): ?\Illuminate\Database\Eloquent\Builder
            {
                return null;
            }

            public function getFilteredSortedTableQuery(): ?\Illuminate\Database\Eloquent\Builder
            {
                return null;
            }

            public function getTableQueryForExport(): \Illuminate\Database\Eloquent\Builder
            {
                throw new \LogicException('Not implemented: stub does not support exports.');
            }

            public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
            {
                return null;
            }

            /** @param array<string, mixed> $arguments */
            public function callMountedTableAction(array $arguments = []): mixed
            {
                return null;
            }

            /** @param array<string, mixed> $arguments */
            public function mountTableAction(string $name, ?string $record = null, array $arguments = []): mixed
            {
                return null;
            }

            /** @param array<string, mixed> $arguments */
            public function replaceMountedTableAction(string $name, ?string $record = null, array $arguments = []): void {}

            /** @param array<int|string>|null $selectedRecords */
            public function mountTableBulkAction(string $name, ?array $selectedRecords = null): mixed
            {
                return null;
            }

            /** @param array<int|string>|null $selectedRecords */
            public function replaceMountedTableBulkAction(string $name, ?array $selectedRecords = null): void {}
        };
    }
}
