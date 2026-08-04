<?php

namespace Database\Seeders;

use App\Models\CafeTable;
use App\Models\Menu;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CafeSeeder extends Seeder
{
    /** @var array<int, array<int, array{id:int,ingredient_id:int,quantity:float,expiry_date:string,received_at:string,cost_per_unit:float,status:string}>> */
    private array $batchCache = [];

    /** @var array<int, array> */
    private array $stockMovements = [];

    /**
     * All 22 menu definitions.
     * [name => [category_key, price, student_cashback, description, [ingredient_key => quantity_used]]]
     */
    private const MENUS = [
        // ── Kopi ──────────────────────────────────────
        'Espresso' => [
            'category' => 'kopi', 'price' => 12000, 'cashback' => 2000,
            'desc' => 'Espresso murni dari biji kopi Arabika pilihan',
            'ingredients' => ['biji_kopi_arabika' => 0.015],
        ],
        'Americano Panas' => [
            'category' => 'kopi', 'price' => 12000, 'cashback' => 2000,
            'desc' => 'Espresso dengan tambahan air panas',
            'ingredients' => ['biji_kopi_arabika' => 0.015],
        ],
        'Kopi Susu' => [
            'category' => 'kopi', 'price' => 16000, 'cashback' => 3000,
            'desc' => 'Kopi Robusta dengan susu UHT dan krimer',
            'ingredients' => ['biji_kopi_robusta' => 0.015, 'susu_uht' => 0.1, 'krimer_kental_manis' => 1],
        ],
        'Es Kopi Susu' => [
            'category' => 'kopi', 'price' => 18000, 'cashback' => 3000,
            'desc' => 'Kopi Susu dingin dengan es batu',
            'ingredients' => ['biji_kopi_robusta' => 0.015, 'susu_uht' => 0.1, 'krimer_kental_manis' => 1],
        ],

        // ── Teh ───────────────────────────────────────
        'Teh Tawar' => [
            'category' => 'teh', 'price' => 4000, 'cashback' => 1000,
            'desc' => 'Teh celup seduh tanpa gula',
            'ingredients' => ['teh_celup' => 1],
        ],
        'Teh Manis' => [
            'category' => 'teh', 'price' => 5000, 'cashback' => 1000,
            'desc' => 'Teh celup seduh dengan gula pasir',
            'ingredients' => ['teh_celup' => 1, 'gula_pasir' => 0.015],
        ],
        'Teh Susu' => [
            'category' => 'teh', 'price' => 8000, 'cashback' => 2000,
            'desc' => 'Teh celup dengan susu UHT dan gula',
            'ingredients' => ['teh_celup' => 1, 'susu_uht' => 0.1, 'gula_pasir' => 0.01],
        ],

        // ── Minuman Susu ──────────────────────────────
        'Susu Segar' => [
            'category' => 'minuman_susu', 'price' => 10000, 'cashback' => 2000,
            'desc' => 'Susu UHT segar dengan sedikit gula',
            'ingredients' => ['susu_uht' => 0.25, 'gula_pasir' => 0.01],
        ],
        'Matcha Latte' => [
            'category' => 'minuman_susu', 'price' => 15000, 'cashback' => 3000,
            'desc' => 'Bubuk matcha Jepang dengan susu UHT',
            'ingredients' => ['bubuk_matcha' => 1, 'susu_uht' => 0.2, 'gula_pasir' => 0.01],
        ],
        'Red Velvet' => [
            'category' => 'minuman_susu', 'price' => 16000, 'cashback' => 3000,
            'desc' => 'Sirup vanilla dengan susu UHT creamy',
            'ingredients' => ['sirup_vanilla' => 0.03, 'susu_uht' => 0.2, 'gula_pasir' => 0.01],
        ],

        // ── Coklat ────────────────────────────────────
        'Coklat Panas' => [
            'category' => 'coklat', 'price' => 10000, 'cashback' => 2000,
            'desc' => 'Bubuk coklat premium dengan susu hangat',
            'ingredients' => ['bubuk_coklat' => 0.03, 'susu_uht' => 0.15, 'gula_pasir' => 0.01],
        ],
        'Coklat Susu' => [
            'category' => 'coklat', 'price' => 12000, 'cashback' => 2000,
            'desc' => 'Coklat creamy dengan susu UHT ekstra',
            'ingredients' => ['bubuk_coklat' => 0.03, 'susu_uht' => 0.2, 'gula_pasir' => 0.01],
        ],
        'Moccacino' => [
            'category' => 'coklat', 'price' => 15000, 'cashback' => 3000,
            'desc' => 'Perpaduan coklat dan espresso Arabika',
            'ingredients' => ['bubuk_coklat' => 0.02, 'biji_kopi_arabika' => 0.01, 'susu_uht' => 0.15, 'gula_pasir' => 0.01],
        ],

        // ── Jus & Segar ───────────────────────────────
        'Jeruk Nipis Peras' => [
            'category' => 'jus_segar', 'price' => 7000, 'cashback' => 2000,
            'desc' => 'Perasan jeruk nipis segar dengan gula',
            'ingredients' => ['jeruk_nipis' => 0.1, 'gula_pasir' => 0.015],
        ],
        'Es Jeruk Nipis' => [
            'category' => 'jus_segar', 'price' => 8000, 'cashback' => 2000,
            'desc' => 'Jeruk nipis peras dingin menyegarkan',
            'ingredients' => ['jeruk_nipis' => 0.1, 'gula_pasir' => 0.015],
        ],

        // ── Makanan Berat ─────────────────────────────
        'Nasi Goreng Telur' => [
            'category' => 'makanan_berat', 'price' => 15000, 'cashback' => 2000,
            'desc' => 'Nasi goreng dengan telur dan kecap manis',
            'ingredients' => ['beras' => 0.15, 'telur' => 1, 'minyak_goreng' => 0.02, 'kecap_manis' => 0.01],
        ],
        'Mie Goreng Telur' => [
            'category' => 'makanan_berat', 'price' => 12000, 'cashback' => 2000,
            'desc' => 'Indomie goreng dengan telur ceplok',
            'ingredients' => ['indomie_goreng' => 1, 'telur' => 1, 'minyak_goreng' => 0.01, 'kecap_manis' => 0.005],
        ],
        'Nasi Ayam Geprek' => [
            'category' => 'makanan_berat', 'price' => 18000, 'cashback' => 3000,
            'desc' => 'Ayam geprek crispy dengan nasi dan sambal',
            'ingredients' => ['beras' => 0.15, 'dada_ayam' => 0.2, 'tepung_bumbu' => 0.05, 'minyak_goreng' => 0.03, 'saus_sambal' => 0.01],
        ],

        // ── Makanan Ringan ────────────────────────────
        'Pisang Coklat Keju' => [
            'category' => 'makanan_ringan', 'price' => 12000, 'cashback' => 2000,
            'desc' => 'Pisang goreng crispy dengan keju parut',
            'ingredients' => ['pisang' => 1, 'keju_parut' => 0.03, 'tepung_terigu' => 0.02, 'minyak_goreng' => 0.015],
        ],
        'Tempe Mendoan' => [
            'category' => 'makanan_ringan', 'price' => 8000, 'cashback' => 2000,
            'desc' => 'Tempe goreng tepung khas Banyumas',
            'ingredients' => ['tempe' => 2, 'tepung_terigu' => 0.03, 'minyak_goreng' => 0.02],
        ],
        'Kentang Goreng' => [
            'category' => 'makanan_ringan', 'price' => 14000, 'cashback' => 2000,
            'desc' => 'Kentang goreng renyah ala French Fries',
            'ingredients' => ['kentang' => 0.2, 'minyak_goreng' => 0.02],
        ],

        // ── Minuman Kemasan ───────────────────────────
        'Air Mineral Botol' => [
            'category' => 'minuman_kemasan', 'price' => 5000, 'cashback' => 1000,
            'desc' => 'Air mineral kemasan botol 600ml',
            'ingredients' => ['air_mineral_gelas' => 1],
        ],
    ];

    /** Category keys → display names */
    private const CATEGORIES = [
        'kopi' => 'Kopi',
        'teh' => 'Teh',
        'minuman_susu' => 'Minuman Susu',
        'coklat' => 'Coklat',
        'jus_segar' => 'Jus & Segar',
        'makanan_berat' => 'Makanan Berat',
        'makanan_ringan' => 'Makanan Ringan',
        'minuman_kemasan' => 'Minuman Kemasan',
    ];

    /** Ingredient keys → [name, unit, low_stock_threshold] */
    private const INGREDIENTS = [
        'biji_kopi_arabika' => ['Biji Kopi Arabika', 'kg', 2],
        'biji_kopi_robusta' => ['Biji Kopi Robusta', 'kg', 2],
        'gula_pasir' => ['Gula Pasir', 'kg', 8],
        'susu_uht' => ['Susu UHT', 'liter', 10],
        'krimer_kental_manis' => ['Krimer Kental Manis', 'sachet', 30],
        'bubuk_coklat' => ['Bubuk Coklat', 'kg', 2],
        'bubuk_matcha' => ['Bubuk Matcha', 'sachet', 15],
        'sirup_vanilla' => ['Sirup Vanilla', 'liter', 1],
        'teh_celup' => ['Teh Celup', 'pcs', 50],
        'jeruk_nipis' => ['Jeruk Nipis', 'kg', 3],
        'keju_parut' => ['Keju Parut', 'kg', 2],
        'pisang' => ['Pisang', 'pcs', 15],
        'tempe' => ['Tempe', 'pcs', 10],
        'kentang' => ['Kentang', 'kg', 5],
        'indomie_goreng' => ['Indomie Goreng', 'pcs', 30],
        'telur' => ['Telur', 'pcs', 40],
        'beras' => ['Beras', 'kg', 10],
        'dada_ayam' => ['Dada Ayam', 'kg', 5],
        'minyak_goreng' => ['Minyak Goreng', 'liter', 5],
        'kecap_manis' => ['Kecap Manis', 'ml', 500],
        'tepung_bumbu' => ['Tepung Bumbu', 'kg', 3],
        'tepung_terigu' => ['Tepung Terigu', 'kg', 3],
        'saus_sambal' => ['Saus Sambal', 'ml', 300],
        'air_mineral_gelas' => ['Air Mineral Gelas', 'pcs', 60],
    ];

    /** Ingredient key → [cost_per_unit, monthly_usage_estimate, batch_size, expiry_months] */
    private const BATCH_CONFIG = [
        'biji_kopi_arabika' => [80000, 2, 5, 12],
        'biji_kopi_robusta' => [50000, 3, 5, 12],
        'gula_pasir' => [15000, 8, 10, 24],
        'susu_uht' => [20000, 15, 15, 6],
        'krimer_kental_manis' => [1500, 30, 48, 12],
        'bubuk_coklat' => [60000, 1, 3, 12],
        'bubuk_matcha' => [5000, 8, 15, 18],
        'sirup_vanilla' => [40000, 0.5, 2, 12],
        'teh_celup' => [500, 60, 100, 24],
        'jeruk_nipis' => [12000, 4, 5, 1],
        'keju_parut' => [80000, 0.5, 2, 3],
        'pisang' => [2000, 15, 25, 0.5],
        'tempe' => [5000, 10, 15, 0.5],
        'kentang' => [18000, 4, 8, 1],
        'indomie_goreng' => [3000, 25, 40, 12],
        'telur' => [2000, 50, 80, 1],
        'beras' => [12000, 15, 25, 24],
        'dada_ayam' => [35000, 5, 8, 3],
        'minyak_goreng' => [18000, 12, 18, 24],
        'kecap_manis' => [25, 400, 600, 24],
        'tepung_bumbu' => [20000, 2, 4, 12],
        'tepung_terigu' => [10000, 1, 3, 12],
        'saus_sambal' => [20, 200, 300, 12],
        'air_mineral_gelas' => [1500, 40, 72, 24],
    ];

    /** Supplier names for ingredient batches (already-paid purchases) */
    public const SUPPLIERS = [
        'PT Sumber Berkah' => 'PT Sumber Berkah',
        'CV Tani Makmur' => 'CV Tani Makmur',
        'Toko Bahan Kue Sari' => 'Toko Bahan Kue Sari',
        'UD Segar Abadi' => 'UD Segar Abadi',
        'PT Kopi Nusantara' => 'PT Kopi Nusantara',
        'CV Susu Sejahtera' => 'CV Susu Sejahtera',
    ];

    /** @var array<string, int> category key → DB id */
    private array $categoryIds = [];

    /** @var array<string, int> ingredient key → DB id */
    private array $ingredientIds = [];

    /** @var array<int, int> menu name → DB id */
    private array $menuIds = [];

    /** @var int cashier user ID pool */
    private array $cashierIds = [2, 3, 4];

    /** Monthly order distribution */
    private const MONTHLY_ORDERS = [
        '2025-06' => 25, '2025-07' => 12, '2025-08' => 15,
        '2025-09' => 35, '2025-10' => 35, '2025-11' => 30,
        '2025-12' => 15, '2026-01' => 12, '2026-02' => 30,
        '2026-03' => 30, '2026-04' => 35, '2026-05' => 35,
    ];

    // ──────────────────────────────────────────────────────────────
    //  run()
    // ──────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->truncateTables();

        // 1–2. Categories + Ingredients
        $this->seedCategories();
        $this->seedIngredients();

        // 3. IngredientBatches
        $this->seedIngredientBatches();

        // 4–5. Menus + MenuIngredients
        $this->seedMenus();
        $this->seedMenuIngredients();

        // 6. CafeTables
        $this->seedCafeTables();

        // 7–9. Orders + OrderItems
        $orderData = $this->seedOrders();
        $this->seedOrderItems($orderData);

        // 10. StockMovements from Orders (FEFO deduction)
        $this->seedStockMovementsFromOrders($orderData);

        // 11. StockAdjustments + StockMovements from Adjustments
        $this->seedStockAdjustments();
        $this->seedStockMovementsFromAdjustments();
    }

    // ──────────────────────────────────────────────────────────────
    //  0. Truncate
    // ──────────────────────────────────────────────────────────────

    private function truncateTables(): void
    {
        DB::statement('SET session_replication_role = replica');

        DB::table('stock_movements')->truncate();
        DB::table('stock_adjustments')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('menu_ingredients')->truncate();
        DB::table('menus')->truncate();
        DB::table('ingredient_batches')->truncate();
        DB::table('ingredients')->truncate();
        DB::table('categories')->truncate();
        DB::table('cafe_tables')->truncate();

        DB::statement('SET session_replication_role = DEFAULT');
    }

    // ──────────────────────────────────────────────────────────────
    //  1. Categories
    // ──────────────────────────────────────────────────────────────

    private function seedCategories(): void
    {
        $rows = [];
        foreach (self::CATEGORIES as $key => $name) {
            $rows[] = ['name' => $name, 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('categories')->insert($rows);

        $this->categoryIds = DB::table('categories')->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [array_search($name, self::CATEGORIES) => $id])
            ->all();
    }

    // ──────────────────────────────────────────────────────────────
    //  2. Ingredients
    // ──────────────────────────────────────────────────────────────

    private function seedIngredients(): void
    {
        $rows = [];
        foreach (self::INGREDIENTS as $key => [$name, $unit, $threshold]) {
            $rows[] = [
                'name' => $name,
                'unit' => $unit,
                'low_stock_threshold' => $threshold,
                'batch_mode' => 'fefo',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('ingredients')->insert($rows);

        $this->ingredientIds = DB::table('ingredients')->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [
                array_search($name, array_map(fn ($v) => $v[0], self::INGREDIENTS)) => $id,
            ])
            ->all();
    }

    // ──────────────────────────────────────────────────────────────
    //  3. IngredientBatches (~55, 2-3 per ingredient)
    // ──────────────────────────────────────────────────────────────

    private function seedIngredientBatches(): void
    {
        $rows = [];
        $startDate = Carbon::create(2025, 6, 1);
        $rng = $this->rng(42);
        $batchSeq = 0; // global counter to prevent unique constraint violations

        foreach (self::INGREDIENTS as $key => [$name, $unit]) {
            $ingId = $this->ingredientIds[$key];
            [$cost, , $batchSize, $expiryMonths] = self::BATCH_CONFIG[$key];
            $numBatches = $rng->pick([2, 2, 2, 3, 3]);

            for ($b = 0; $b < $numBatches; $b++) {
                $receivedAt = (clone $startDate)->addDays($rng->int(0, 360))
                    ->setTime($rng->int(7, 15), $rng->int(0, 59), 0);
                $variation = $rng->float(0.8, 1.3);
                $qty = round($batchSize * $variation, 2);
                $expiry = $expiryMonths < 1
                    ? (clone $receivedAt)->addDays(max(7, (int) ($expiryMonths * 30)))
                    : (clone $receivedAt)->addMonths((int) $expiryMonths);

                $unitCost = (int) round($cost * $rng->float(0.85, 1.15));

                $rows[] = [
                    'ingredient_id' => $ingId,
                    'quantity' => $qty,
                    'expiry_date' => $expiry->toDateString(),
                    'received_at' => $receivedAt->toDateTimeString(),
                    'cost_per_unit' => $unitCost,
                    'initial_quantity' => $qty,
                    'supplier_name' => $rng->pick(array_keys(self::SUPPLIERS)),
                    'total_cost' => (int) round($qty * $unitCost),
                    'payment_status' => 'lunas',
                    'custom_order' => null,
                    'status' => 'active',
                    'batch_code' => sprintf('BCH-%s-%d', $receivedAt->format('dmy'), ++$batchSeq),
                ];
            }
        }

        DB::table('ingredient_batches')->insert($rows);

        // Load all batches into in-memory cache keyed by ingredient_id
        $allBatches = DB::table('ingredient_batches')
            ->select('id', 'ingredient_id', 'quantity', 'expiry_date', 'received_at', 'cost_per_unit', 'status')
            ->orderBy('expiry_date')
            ->get();

        foreach ($allBatches as $batch) {
            $this->batchCache[$batch->ingredient_id][] = [
                'id' => $batch->id,
                'ingredient_id' => $batch->ingredient_id,
                'quantity' => (float) $batch->quantity,
                'expiry_date' => $batch->expiry_date,
                'received_at' => $batch->received_at,
                'cost_per_unit' => (float) $batch->cost_per_unit,
                'status' => $batch->status,
            ];
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  4. Menus (22)
    // ──────────────────────────────────────────────────────────────

    private function seedMenus(): void
    {
        foreach (self::MENUS as $name => $def) {
            $catId = $this->categoryIds[$def['category']];
            $menu = Menu::create([
                'category_id' => $catId,
                'name' => $name,
                'price' => $def['price'],
                'discounted_price' => $def['price'] - $def['cashback'],
                'status' => 'active',
            ]);
            $this->menuIds[$name] = $menu->id;
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  5. MenuIngredients
    // ──────────────────────────────────────────────────────────────

    private function seedMenuIngredients(): void
    {
        $rows = [];
        foreach (self::MENUS as $menuName => $def) {
            $menuId = $this->menuIds[$menuName];
            foreach ($def['ingredients'] as $ingKey => $qty) {
                $rows[] = [
                    'menu_id' => $menuId,
                    'ingredient_id' => $this->ingredientIds[$ingKey],
                    'quantity_used' => $qty,
                ];
            }
        }
        DB::table('menu_ingredients')->insert($rows);
    }

    // ──────────────────────────────────────────────────────────────
    //  6. CafeTables (10)
    // ──────────────────────────────────────────────────────────────

    private function seedCafeTables(): void
    {
        for ($n = 1; $n <= 10; $n++) {
            CafeTable::create([
                'table_number' => $n,
                'qr_code' => "http://localhost/order?table={$n}",
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  7. Orders (300)
    // ──────────────────────────────────────────────────────────────

    private function seedOrders(): array
    {
        $rng = $this->rng(123);
        $menuNames = array_keys(self::MENUS);
        $menuCount = count($menuNames);
        $orderRows = [];
        $orderMeta = []; // order_code → [menu_name => qty, ...]
        $dailyCounter = []; // date key (dmy) → sequence number

        foreach (self::MONTHLY_ORDERS as $yearMonth => $targetCount) {
            [$year, $month] = explode('-', $yearMonth);
            $y = (int) $year;
            $m = (int) $month;
            $daysInMonth = Carbon::create($y, $m, 1)->daysInMonth;

            // Distribute targetCount across days, busier weekends
            $dayWeights = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dow = Carbon::create($y, $m, $d)->dayOfWeek;
                $dayWeights[$d] = ($dow === 0 || $dow === 6) ? 3 : 1;
            }
            $totalWeight = array_sum($dayWeights);
            $remaining = $targetCount;
            $assigned = array_fill_keys(range(1, $daysInMonth), 0);

            // Distribute proportionally, ensuring 0-3 per day
            for ($d = 1; $d <= $daysInMonth && $remaining > 0; $d++) {
                $prop = (int) round(($dayWeights[$d] / $totalWeight) * $targetCount);
                $prop = min($prop, 3, $remaining);
                $prop = max($prop, $remaining > ($daysInMonth - $d) * 3 ? min(3, $remaining) : 0);
                $assigned[$d] = $prop;
                $remaining -= $prop;
            }
            // Distribute any leftover
            for ($d = 1; $d <= $daysInMonth && $remaining > 0; $d++) {
                if ($assigned[$d] < 3) {
                    $add = min(3 - $assigned[$d], $remaining);
                    $assigned[$d] += $add;
                    $remaining -= $add;
                }
            }

            foreach ($assigned as $day => $count) {
                if ($count <= 0) {
                    continue;
                }
                $date = Carbon::create($y, $m, $day);
                for ($i = 0; $i < $count; $i++) {
                    $hour = $rng->int(7, 21);
                    $minute = $rng->int(0, 59);
                    $timestamp = (clone $date)->setTime($hour, $minute, $rng->int(0, 59));
                    $dateKey = $timestamp->format('dmy');
                    $dailyCounter[$dateKey] = ($dailyCounter[$dateKey] ?? 0) + 1;
                    $orderCode = sprintf('ORD-%s-%d', $dateKey, $dailyCounter[$dateKey]);

                    // Pick 1-4 menu items
                    $numItems = $rng->pick([1, 1, 2, 2, 2, 3, 3, 4]);
                    $items = [];
                    $totalAmount = 0;
                    $pickedKeys = $rng->sample($menuNames, $numItems);

                    foreach ($pickedKeys as $menuName) {
                        $qty = $rng->pick([1, 1, 1, 1, 2, 2, 3]);
                        $items[$menuName] = ($items[$menuName] ?? 0) + $qty;
                        $totalAmount += self::MENUS[$menuName]['price'] * $qty;
                    }

                    // Payment method distribution
                    $pmRoll = $rng->int(1, 100);
                    $paymentMethod = $pmRoll <= 60 ? 'cash' : ($pmRoll <= 90 ? 'qris' : 'pay_later');

                    // Status: orders in last 1-2 months may vary, older are completed
                    $monthsFromEnd = ($y === 2026 && $m >= 4) ? (($m - 4) * 30 + $day) / 30.0 : 999;
                    $roll = $rng->int(1, 100);
                    if ($monthsFromEnd <= 1.0 && $roll <= 40) {
                        $status = $rng->pick(['processing', 'processing', 'pending', 'pending', 'cancelled']);
                    } elseif ($monthsFromEnd <= 2.0 && $roll <= 15) {
                        $status = $rng->pick(['processing', 'pending', 'cancelled']);
                    } else {
                        $status = 'completed';
                    }

                    $completedAt = $status === 'completed' ? (clone $timestamp)->addMinutes($rng->int(10, 45)) : null;
                    $processedAt = in_array($status, ['processing', 'completed']) ? (clone $timestamp)->addMinutes($rng->int(2, 10)) : null;
                    $cancelledAt = $status === 'cancelled' ? (clone $timestamp)->addMinutes($rng->int(5, 30)) : null;

                    $isAvailable = $status !== 'cancelled';
                    $tableId = $rng->int(1, 100) <= 70 ? $rng->int(1, 10) : null;

                    $orderRows[] = [
                        'order_code' => $orderCode,
                        'table_id' => $tableId,
                        'cashier_id' => $this->cashierIds[$rng->int(0, 2)],
                        'customer_name' => $rng->pick([null, null, null, 'Budi', 'Ani', 'Citra', 'Dewi', 'Eko', 'Fajar']),
                        'phone' => null,
                        'status' => $status,
                        'order_type' => $rng->pick(['qr', 'qr', 'qr', 'cashier', 'cashier']),
                        'total_amount' => $totalAmount,
                        'payment_method' => $paymentMethod,
                        'uuid' => Str::uuid(),
                        'processed_by' => $status !== 'pending' ? $this->cashierIds[$rng->int(0, 2)] : null,
                        'processed_at' => $processedAt,
                        'completed_at' => $completedAt,
                        'cancelled_at' => $cancelledAt,
                        'created_at' => $timestamp->toDateTimeString(),
                        'updated_at' => $timestamp->toDateTimeString(),
                    ];

                    $orderMeta[$orderCode] = $items;
                }
            }
        }

        DB::table('orders')->insert($orderRows);

        // Fetch back IDs
        $orderIds = DB::table('orders')->pluck('id', 'order_code')->all();

        // Build return data: [order_id => [menu_name => qty, total_amount, payment_method, cashier_id, created_at]]
        $result = [];
        foreach ($orderRows as $row) {
            $code = $row['order_code'];
            $oid = $orderIds[$code];
            $result[$oid] = [
                'items' => $orderMeta[$code],
                'total_amount' => $row['total_amount'],
                'payment_method' => $row['payment_method'],
                'cashier_id' => $row['cashier_id'],
                'customer_name' => $row['customer_name'],
                'created_at' => $row['created_at'],
                'order_code' => $code,
                'status' => $row['status'],
            ];
        }

        return $result;
    }

    // ──────────────────────────────────────────────────────────────
    //  8. OrderItems (~600)
    // ──────────────────────────────────────────────────────────────

    private function seedOrderItems(array $orderData): void
    {
        $rows = [];
        foreach ($orderData as $orderId => $data) {
            foreach ($data['items'] as $menuName => $qty) {
                $menuId = $this->menuIds[$menuName];
                $price = self::MENUS[$menuName]['price'];
                $rows[] = [
                    'order_id' => $orderId,
                    'menu_id' => $menuId,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $price * $qty,
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['created_at'],
                ];
            }
        }
        DB::table('order_items')->insert($rows);
    }

    // ──────────────────────────────────────────────────────────────
    //  10. StockMovements from Orders (FEFO deduction)
    // ──────────────────────────────────────────────────────────────

    // ──────────────────────────────────────────────────────────────
    //  11. StockMovements from Orders — FEFO deduction
    // ──────────────────────────────────────────────────────────────

    private function seedStockMovementsFromOrders(array $orderData): void
    {
        $movements = [];

        // Sort orders by created_at to process chronologically
        uasort($orderData, fn ($a, $b) => $a['created_at'] <=> $b['created_at']);

        foreach ($orderData as $orderId => $data) {
            foreach ($data['items'] as $menuName => $orderQty) {
                $menuIngredients = self::MENUS[$menuName]['ingredients'];
                foreach ($menuIngredients as $ingKey => $qtyPerPortion) {
                    $ingId = $this->ingredientIds[$ingKey];
                    $totalNeeded = $qtyPerPortion * $orderQty;

                    $newMovements = $this->deductFefo(
                        $ingId,
                        $totalNeeded,
                        $orderId,
                        $data['order_code'],
                        $data['cashier_id'],
                        $data['created_at']
                    );
                    $movements = array_merge($movements, $newMovements);
                }
            }
        }

        // Persist StockMovements
        foreach (array_chunk($movements, 500) as $chunk) {
            DB::table('stock_movements')->insert($chunk);
        }

        // Persist updated batch quantities
        $batchUpdates = [];
        foreach ($this->batchCache as $ingId => $batches) {
            foreach ($batches as $batch) {
                $batchUpdates[] = [
                    'id' => $batch['id'],
                    'quantity' => $batch['quantity'],
                ];
            }
        }
        foreach ($batchUpdates as $update) {
            DB::table('ingredient_batches')
                ->where('id', $update['id'])
                ->update(['quantity' => $update['quantity']]);
        }
    }

    /**
     * Deduct ingredient from batches using FEFO (oldest expiry first).
     * Updates in-memory batch cache and returns StockMovement rows.
     */
    private function deductFefo(
        int $ingredientId,
        float $qtyNeeded,
        int $orderId,
        string $orderCode,
        int $cashierId,
        string $timestamp
    ): array {
        $movements = [];
        $remaining = $qtyNeeded;

        if (!isset($this->batchCache[$ingredientId])) {
            return $movements;
        }

        // Sort batches by expiry_date ASC (oldest first) — already sorted from DB query
        // But after deducing, we re-sort to maintain FEFO order
        foreach ($this->batchCache[$ingredientId] as &$batch) {
            if ($batch['quantity'] <= 0) {
                continue;
            }
            if ($remaining <= 0) {
                break;
            }

            $deduct = min($batch['quantity'], $remaining);
            $oldQty = $batch['quantity'];
            $batch['quantity'] = round($oldQty - $deduct, 4);
            $remaining = round($remaining - $deduct, 4);

            $movements[] = [
                'ingredient_id' => $ingredientId,
                'ingredient_batch_id' => $batch['id'],
                'order_id' => $orderId,
                'order_item_id' => null,
                'stock_adjustment_id' => null,
                'movement_type' => 'sale',
                'source_type' => null,
                'source_id' => null,
                'quantity_before' => round($oldQty, 2),
                'quantity_change' => -round($deduct, 2),
                'quantity_after' => round($batch['quantity'], 2),
                'unit_cost' => (int) round($batch['cost_per_unit']),
                'reference' => $orderCode,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }
        unset($batch);

        // Re-sort batches for this ingredient by expiry_date (FEFO)
        usort($this->batchCache[$ingredientId], fn ($a, $b) => $a['expiry_date'] <=> $b['expiry_date']);

        return $movements;
    }

    // ──────────────────────────────────────────────────────────────
    //  12. StockAdjustments (16 total)
    // ──────────────────────────────────────────────────────────────

    private function seedStockAdjustments(): void
    {
        $rng = $this->rng(789);
        $rows = [];
        $ingKeys = array_keys(self::INGREDIENTS);
        $now = Carbon::now();
        $adminId = 1;
        $dailyAdjCounter = []; // date key (dmy) → sequence number
        $seqDate = Carbon::create(2025, 6, 15);

        // --- 8 increase adjustments ---
        $increaseCategories = ['koreksi_stok', 'koreksi_stok', 'koreksi_stok', 'koreksi_stok', 'koreksi_stok', 'lainnya', 'lainnya', 'lainnya'];
        foreach ($increaseCategories as $i => $cat) {
            $ingKey = $rng->pick($ingKeys);
            $ingId = $this->ingredientIds[$ingKey];
            $adjQty = $rng->float(1, 10);
            $currentStock = $this->getTotalStock($ingId);
            $newStock = round($currentStock + $adjQty, 2);

            $adjustedAt = (clone $seqDate)->addDays($rng->int(0, 330))->setTime($rng->int(8, 16), $rng->int(0, 59), 0);
            $adjDateKey = $adjustedAt->format('dmy');
            $dailyAdjCounter[$adjDateKey] = ($dailyAdjCounter[$adjDateKey] ?? 0) + 1;
            $code = sprintf('ADJ-%s-%d', $adjDateKey, $dailyAdjCounter[$adjDateKey]);

            $rows[] = [
                'adjustable_type' => 'ingredient',
                'ingredient_id' => $ingId,
                'menu_id' => null,
                'adjustment_type' => 'increase',
                'category' => $cat,
                'quantity' => round($adjQty, 2),
                'quantity_before' => $currentStock,
                'quantity_after' => round($newStock, 2),
                'reason' => $this->adjustmentReason($cat),
                'status' => 'active',
                'cancel_reason' => null,
                'reported_by' => $adminId,
                'adjusted_at' => $adjustedAt,
                'code' => $code,
                'created_at' => $adjustedAt,
                'updated_at' => $adjustedAt,
            ];

            // Reflect increase in batch cache
            $this->addToBatch($ingId, $adjQty, $adjustedAt);
        }

        // --- 5 decrease adjustments ---
        $decreaseCategories = ['expired', 'expired', 'damaged', 'spilled', 'complaint'];
        foreach ($decreaseCategories as $cat) {
            $ingKey = $rng->pick($ingKeys);
            $ingId = $this->ingredientIds[$ingKey];
            $currentStock = $this->getTotalStock($ingId);
            $adjQty = round($currentStock * $rng->float(0.05, 0.15), 2);
            $adjQty = max($adjQty, 0.1);
            $newStock = round(max(0, $currentStock - $adjQty), 2);

            $adjustedAt = (clone $seqDate)->addDays($rng->int(30, 350))->setTime($rng->int(8, 16), $rng->int(0, 59), 0);
            $adjDateKey = $adjustedAt->format('dmy');
            $dailyAdjCounter[$adjDateKey] = ($dailyAdjCounter[$adjDateKey] ?? 0) + 1;
            $code = sprintf('ADJ-%s-%d', $adjDateKey, $dailyAdjCounter[$adjDateKey]);

            $rows[] = [
                'adjustable_type' => 'ingredient',
                'ingredient_id' => $ingId,
                'menu_id' => null,
                'adjustment_type' => 'decrease',
                'category' => $cat,
                'quantity' => round($adjQty, 2),
                'quantity_before' => $currentStock,
                'quantity_after' => round($newStock, 2),
                'reason' => $this->adjustmentReason($cat),
                'status' => 'active',
                'cancel_reason' => null,
                'reported_by' => $adminId,
                'adjusted_at' => $adjustedAt,
                'code' => $code,
                'created_at' => $adjustedAt,
                'updated_at' => $adjustedAt,
            ];

            // Reflect decrease in batch cache
            $this->deductFromBatch($ingId, $adjQty);
        }

        // --- 3 cancelled adjustments ---
        for ($c = 0; $c < 3; $c++) {
            $ingKey = $rng->pick($ingKeys);
            $ingId = $this->ingredientIds[$ingKey];
            $currentStock = $this->getTotalStock($ingId);
            $adjType = $rng->pick(['increase', 'decrease']);
            $adjQty = $rng->float(0.5, 5);
            $adjustedAt = (clone $seqDate)->addDays($rng->int(60, 360))->setTime($rng->int(8, 16), $rng->int(0, 59), 0);
            $adjDateKey = $adjustedAt->format('dmy');
            $dailyAdjCounter[$adjDateKey] = ($dailyAdjCounter[$adjDateKey] ?? 0) + 1;
            $code = sprintf('ADJ-%s-%d', $adjDateKey, $dailyAdjCounter[$adjDateKey]);

            $rows[] = [
                'adjustable_type' => 'ingredient',
                'ingredient_id' => $ingId,
                'menu_id' => null,
                'adjustment_type' => $adjType,
                'category' => 'koreksi_stok',
                'quantity' => round($adjQty, 2),
                'quantity_before' => $currentStock,
                'quantity_after' => $currentStock, // cancelled: no effect
                'reason' => 'Dibatalkan karena kesalahan input',
                'status' => 'cancelled',
                'cancel_reason' => 'Kesalahan input — dibatalkan oleh admin',
                'reported_by' => $adminId,
                'adjusted_at' => $adjustedAt,
                'code' => $code,
                'created_at' => $adjustedAt,
                'updated_at' => $adjustedAt,
            ];
        }

        DB::table('stock_adjustments')->insert($rows);
        $this->adjustmentRows = $rows;
    }

    // ──────────────────────────────────────────────────────────────
    //  13. StockMovements from Adjustments
    // ──────────────────────────────────────────────────────────────

    private function seedStockMovementsFromAdjustments(): void
    {
        $adjustments = DB::table('stock_adjustments')
            ->where('adjustable_type', 'ingredient')
            ->orderBy('id')
            ->get();

        // Build lookup ingredient_id → first active batch ID from cache
        $batchIds = [];
        foreach ($this->batchCache as $ingId => $batches) {
            foreach ($batches as $b) {
                if ($b['id'] > 0 && $b['quantity'] > 0) {
                    $batchIds[$ingId] = $b['id'];
                    break;
                }
            }
            // Fallback: any real batch even if quantity = 0
            if (!isset($batchIds[$ingId])) {
                foreach ($batches as $b) {
                    if ($b['id'] > 0) {
                        $batchIds[$ingId] = $b['id'];
                        break;
                    }
                }
            }
        }

        $movements = [];
        foreach ($adjustments as $adj) {
            if ($adj->status === 'cancelled') {
                continue; // cancelled adjustments don't generate movements
            }

            $movementType = $adj->adjustment_type === 'increase'
                ? 'adjustment_increase'
                : 'adjustment_decrease';

            $change = $adj->adjustment_type === 'increase'
                ? (float) $adj->quantity
                : -(float) $adj->quantity;

            $movements[] = [
                'ingredient_id' => $adj->ingredient_id,
                'ingredient_batch_id' => $batchIds[$adj->ingredient_id] ?? null,
                'order_id' => null,
                'order_item_id' => null,
                'stock_adjustment_id' => $adj->id,
                'movement_type' => $movementType,
                'source_type' => null,
                'source_id' => null,
                'quantity_before' => (float) $adj->quantity_before,
                'quantity_change' => round($change, 2),
                'quantity_after' => (float) $adj->quantity_after,
                'unit_cost' => null,
                'reference' => $adj->code,
                'created_at' => $adj->adjusted_at,
                'updated_at' => $adj->adjusted_at,
            ];
        }

        if (!empty($movements)) {
            DB::table('stock_movements')->insert($movements);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────

    private function getTotalStock(int $ingredientId): float
    {
        return round(array_sum(array_column(
            $this->batchCache[$ingredientId] ?? [], 'quantity'
        )), 2);
    }

    private function addToBatch(int $ingredientId, float $qty, Carbon $date): void
    {
        // Add as a new "virtual batch" for tracking
        $this->batchCache[$ingredientId][] = [
            'id' => -1, // placeholder — will not be referenced by FK
            'ingredient_id' => $ingredientId,
            'quantity' => $qty,
            'expiry_date' => $date->copy()->addMonths(12)->toDateString(),
            'received_at' => $date->toDateTimeString(),
            'cost_per_unit' => self::BATCH_CONFIG[
                array_search($ingredientId, $this->ingredientIds)
            ][0] ?? 0,
            'status' => 'active',
        ];
    }

    private function deductFromBatch(int $ingredientId, float $qty): void
    {
        $remaining = $qty;
        if (!isset($this->batchCache[$ingredientId])) {
            return;
        }
        foreach ($this->batchCache[$ingredientId] as &$batch) {
            if ($batch['quantity'] <= 0) {
                continue;
            }
            if ($remaining <= 0) {
                break;
            }
            $deduct = min($batch['quantity'], $remaining);
            $batch['quantity'] = round($batch['quantity'] - $deduct, 4);
            $remaining = round($remaining - $deduct, 4);
        }
        unset($batch);
    }

    private function adjustmentReason(string $cat): string
    {
        return match ($cat) {
            'expired' => 'Bahan kedaluwarsa — dibuang',
            'damaged' => 'Bahan rusak — tidak layak pakai',
            'spilled' => 'Bahan tumpah saat persiapan',
            'complaint' => 'Komplain pelanggan — penggantian',
            'koreksi_stok' => 'Koreksi stok setelah stock opname',
            'lainnya' => 'Penyesuaian stok rutin',
            default => 'Penyesuaian stok',
        };
    }

    /**
     * Simple reproducible RNG wrapper using mt_rand with a fixed seed.
     */
    private function rng(int $seed): object
    {
        return new class($seed)
        {
            private int $seed;

            public function __construct(int $seed)
            {
                $this->seed = $seed;
                mt_srand($seed);
            }

            public function int(int $min, int $max): int
            {
                return mt_rand($min, $max);
            }

            public function float(float $min, float $max): float
            {
                return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
            }

            public function pick(array $items): mixed
            {
                return $items[array_rand($items)];
            }

            public function sample(array $items, int $count): array
            {
                if ($count >= count($items)) {
                    shuffle($items);

                    return $items;
                }
                $keys = array_rand($items, $count);
                if (!is_array($keys)) {
                    $keys = [$keys];
                }

                return array_map(fn ($k) => $items[$k], $keys);
            }
        };
    }
}
