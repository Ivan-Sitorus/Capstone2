<?php

namespace Database\Seeders;

use App\Models\CafeTable;
use App\Models\Menu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CafeSeeder extends Seeder
{
    private const MENUS = [
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
        'Air Mineral Botol' => [
            'category' => 'minuman_kemasan', 'price' => 5000, 'cashback' => 1000,
            'desc' => 'Air mineral kemasan botol 600ml',
            'ingredients' => ['air_mineral_gelas' => 1],
        ],
        'Cappuccino' => [
            'category' => 'kopi', 'price' => 18000, 'cashback' => 3000,
            'desc' => 'Espresso dengan busa susu lembut',
            'ingredients' => ['biji_kopi_arabika' => 0.015, 'susu_uht' => 0.15, 'krimer_kental_manis' => 1],
        ],
        'Cafe Latte' => [
            'category' => 'kopi', 'price' => 18000, 'cashback' => 3000,
            'desc' => 'Espresso dengan susu UHT steamed',
            'ingredients' => ['biji_kopi_arabika' => 0.015, 'susu_uht' => 0.2],
        ],
        'Kopi Tubruk' => [
            'category' => 'kopi', 'price' => 10000, 'cashback' => 2000,
            'desc' => 'Kopi robusta seduh tradisional',
            'ingredients' => ['biji_kopi_robusta' => 0.02],
        ],
        'Caramel Macchiato' => [
            'category' => 'kopi', 'price' => 20000, 'cashback' => 3000,
            'desc' => 'Espresso dengan susu dan sirup vanilla',
            'ingredients' => ['biji_kopi_arabika' => 0.015, 'susu_uht' => 0.2, 'sirup_vanilla' => 0.02],
        ],
        'Lemon Tea' => [
            'category' => 'teh', 'price' => 8000, 'cashback' => 2000,
            'desc' => 'Teh dengan perasan jeruk nipis',
            'ingredients' => ['teh_celup' => 1, 'gula_pasir' => 0.015, 'jeruk_nipis' => 0.05],
        ],
        'Teh Melati' => [
            'category' => 'teh', 'price' => 5000, 'cashback' => 1000,
            'desc' => 'Teh melati seduh hangat',
            'ingredients' => ['teh_celup' => 1],
        ],
        'Green Tea' => [
            'category' => 'teh', 'price' => 7000, 'cashback' => 1000,
            'desc' => 'Teh hijau dengan sedikit gula',
            'ingredients' => ['teh_celup' => 1, 'gula_pasir' => 0.01],
        ],
        'Milk Tea' => [
            'category' => 'minuman_susu', 'price' => 12000, 'cashback' => 2000,
            'desc' => 'Teh susu dengan krimer',
            'ingredients' => ['teh_celup' => 1, 'susu_uht' => 0.15, 'krimer_kental_manis' => 1, 'gula_pasir' => 0.01],
        ],
        'Strawberry Milk' => [
            'category' => 'minuman_susu', 'price' => 15000, 'cashback' => 3000,
            'desc' => 'Susu UHT dengan sirup stroberi',
            'ingredients' => ['susu_uht' => 0.2, 'sirup_strawberry' => 0.03, 'gula_pasir' => 0.01],
        ],
        'Vanilla Latte' => [
            'category' => 'minuman_susu', 'price' => 18000, 'cashback' => 3000,
            'desc' => 'Espresso, susu, dan sirup vanilla',
            'ingredients' => ['biji_kopi_arabika' => 0.015, 'susu_uht' => 0.2, 'sirup_vanilla' => 0.02],
        ],
        'Chocolate Latte' => [
            'category' => 'coklat', 'price' => 18000, 'cashback' => 3000,
            'desc' => 'Coklat dengan espresso dan susu',
            'ingredients' => ['bubuk_coklat' => 0.03, 'biji_kopi_arabika' => 0.01, 'susu_uht' => 0.2, 'gula_pasir' => 0.01],
        ],
        'Dark Chocolate' => [
            'category' => 'coklat', 'price' => 12000, 'cashback' => 2000,
            'desc' => 'Coklat pekat dengan sedikit gula',
            'ingredients' => ['bubuk_coklat' => 0.04, 'gula_pasir' => 0.015],
        ],
        'Es Lemon Tea' => [
            'category' => 'jus_segar', 'price' => 9000, 'cashback' => 2000,
            'desc' => 'Teh dingin dengan jeruk nipis',
            'ingredients' => ['teh_celup' => 1, 'jeruk_nipis' => 0.08, 'gula_pasir' => 0.015],
        ],
        'Es Jeruk Peras' => [
            'category' => 'jus_segar', 'price' => 8000, 'cashback' => 2000,
            'desc' => 'Es perasan jeruk dengan gula',
            'ingredients' => ['jeruk_nipis' => 0.12, 'gula_pasir' => 0.02],
        ],
        'Nasi Goreng Ayam' => [
            'category' => 'makanan_berat', 'price' => 18000, 'cashback' => 3000,
            'desc' => 'Nasi goreng dengan ayam dan telur',
            'ingredients' => ['beras' => 0.15, 'telur' => 1, 'dada_ayam' => 0.1, 'minyak_goreng' => 0.02, 'kecap_manis' => 0.01],
        ],
        'Mie Goreng Spesial' => [
            'category' => 'makanan_berat', 'price' => 15000, 'cashback' => 2000,
            'desc' => 'Mie goreng dengan telur dan ayam',
            'ingredients' => ['indomie_goreng' => 1, 'telur' => 1, 'dada_ayam' => 0.08, 'minyak_goreng' => 0.01, 'kecap_manis' => 0.005],
        ],
        'Nasi Telur Kecap' => [
            'category' => 'makanan_berat', 'price' => 12000, 'cashback' => 2000,
            'desc' => 'Nasi dengan telur kecap manis',
            'ingredients' => ['beras' => 0.15, 'telur' => 1, 'kecap_manis' => 0.01, 'minyak_goreng' => 0.02],
        ],
        'Pisang Goreng' => [
            'category' => 'makanan_ringan', 'price' => 8000, 'cashback' => 2000,
            'desc' => 'Pisang goreng renyah',
            'ingredients' => ['pisang' => 1, 'tepung_terigu' => 0.02, 'minyak_goreng' => 0.015],
        ],
        'Tahu Crispy' => [
            'category' => 'makanan_ringan', 'price' => 8000, 'cashback' => 2000,
            'desc' => 'Tahu goreng tepung crispy',
            'ingredients' => ['tahu' => 3, 'tepung_bumbu' => 0.03, 'minyak_goreng' => 0.02],
        ],
        'Singkong Goreng' => [
            'category' => 'makanan_ringan', 'price' => 9000, 'cashback' => 2000,
            'desc' => 'Singkong goreng gurih',
            'ingredients' => ['singkong' => 0.2, 'minyak_goreng' => 0.02],
        ],
        'Roti Bakar' => [
            'category' => 'makanan_ringan', 'price' => 12000, 'cashback' => 2000,
            'desc' => 'Roti bakar keju dengan gula',
            'ingredients' => ['roti_tawar' => 2, 'keju_parut' => 0.03, 'gula_pasir' => 0.01],
        ],
        'Teh Kemasan' => [
            'category' => 'minuman_kemasan', 'price' => 6000, 'cashback' => 1000,
            'desc' => 'Teh kemasan botol dingin',
            'ingredients' => ['teh_kemasan' => 1],
        ],
    ];

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
        'sirup_strawberry' => ['Sirup Strawberry', 'liter', 1],
        'tahu' => ['Tahu', 'pcs', 20],
        'singkong' => ['Singkong', 'kg', 5],
        'roti_tawar' => ['Roti Tawar', 'pcs', 10],
        'teh_kemasan' => ['Teh Kemasan', 'pcs', 30],
    ];

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
        'sirup_strawberry' => [45000, 0.5, 2, 12],
        'tahu' => [1000, 20, 30, 0.5],
        'singkong' => [8000, 8, 12, 1],
        'roti_tawar' => [15000, 10, 20, 0.5],
        'teh_kemasan' => [4000, 30, 48, 6],
    ];

    public const SUPPLIERS = [
        'PT Sumber Berkah' => 'PT Sumber Berkah',
        'CV Tani Makmur' => 'CV Tani Makmur',
        'Toko Bahan Kue Sari' => 'Toko Bahan Kue Sari',
        'UD Segar Abadi' => 'UD Segar Abadi',
        'PT Kopi Nusantara' => 'PT Kopi Nusantara',
        'CV Susu Sejahtera' => 'CV Susu Sejahtera',
    ];

    private const FIFO_INGREDIENTS = [
        'gula_pasir', 'susu_uht', 'krimer_kental_manis', 'teh_celup',
        'indomie_goreng', 'beras', 'minyak_goreng', 'kecap_manis',
        'tepung_bumbu', 'tepung_terigu', 'saus_sambal', 'air_mineral_gelas',
        'sirup_strawberry', 'teh_kemasan',
    ];

    private array $categoryIds = [];
    private array $ingredientIds = [];
    private array $ingredientKeys = [];
    private array $ingredientUnits = [];
    private array $ingredientModes = [];
    private array $menuIds = [];
    private array $menuNormalPrice = [];
    private array $menuStudentPrice = [];
    private array $menuCostPrice = [];
    private array $menuIngredientsByName = [];
    private array $batchCache = [];
    private array $batchPos = [];
    private array $dailyUsageEma = [];
    private array $cashierIds = [];
    private int $adminId = 1;
    private array $shiftIndex = [];
    private int $batchSeq = 0;
    private int $adjustmentSeq = 0;
    private object $rng;
    private Carbon $start;
    private Carbon $end;
    private int $ordersPerDay;
    private float $dailyJitter;
    private float $weekendMultiplier;
    private array $monthlySeasonality;
    private int $itemsMin;
    private int $itemsMax;
    private int $qtyMin;
    private int $qtyMax;
    private float $studentRate;
    private array $paymentMix;
    private float $payLaterFullRatio;
    private float $payLaterPartialRatio;
    private float $payableUnpaidRatio;
    private int $insertChunk;

    public function run(): void
    {
        $this->loadConfig();
        $this->rng = $this->makeRng(20220101);
        $this->resolveUserIds();

        $this->truncateTables();
        $this->seedCategories();
        $this->seedIngredients();
        $this->seedMenus();
        $this->seedMenuIngredients();
        $this->seedCafeTables();
        $this->seedInitialBatches();
        $this->seedCashierShifts();
        $this->seedOrdersAndStock();
        $this->finalizeBatchesAndPayments();

        $this->command?->info('CafeSeeder selesai: data ' . $this->start->toDateString() . ' s/d ' . $this->end->toDateString() . '.');
    }

    private function loadConfig(): void
    {
        $endRaw = config('seeding.end_date');
        $this->start = Carbon::parse(config('seeding.start_date'))->startOfDay();
        $this->end = ($endRaw ? Carbon::parse($endRaw) : now())->startOfDay();
        if ($this->end->greaterThan(now())) {
            $this->end = now()->startOfDay();
        }

        $this->ordersPerDay = max(0, (int) config('seeding.orders_per_day'));
        $this->dailyJitter = (float) config('seeding.daily_jitter');
        $this->weekendMultiplier = (float) config('seeding.weekend_multiplier');
        $this->monthlySeasonality = (array) config('seeding.monthly_seasonality');
        $this->itemsMin = max(1, (int) config('seeding.items_min'));
        $this->itemsMax = max($this->itemsMin, (int) config('seeding.items_max'));
        $this->qtyMin = max(1, (int) config('seeding.qty_min'));
        $this->qtyMax = max($this->qtyMin, (int) config('seeding.qty_max'));
        $this->studentRate = (float) config('seeding.student_discount_rate');
        $this->paymentMix = (array) config('seeding.payment_mix');
        $this->payLaterFullRatio = (float) config('seeding.pay_later_fully_paid_ratio');
        $this->payLaterPartialRatio = (float) config('seeding.pay_later_partial_ratio');
        $this->payableUnpaidRatio = (float) config('seeding.payable_unpaid_ratio');
        $this->insertChunk = max(100, (int) config('seeding.insert_chunk'));
    }

    private function resolveUserIds(): void
    {
        $this->cashierIds = User::where('role', 'cashier')->orderBy('id')->pluck('id')->all();
        if (empty($this->cashierIds)) {
            $this->cashierIds = [User::factory()->create(['role' => 'cashier'])->id];
        }
        $this->adminId = (int) (User::where('role', 'admin')->orderBy('id')->value('id') ?? $this->cashierIds[0]);
    }

    private function truncateTables(): void
    {
        DB::statement('SET session_replication_role = replica');

        foreach ([
            'stock_movements', 'stock_adjustments', 'order_payments', 'batch_payments',
            'order_items', 'orders', 'menu_ingredients', 'menus',
            'ingredient_batches', 'ingredients', 'menu_categories', 'cafe_tables',
            'cashier_histories',
        ] as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET session_replication_role = DEFAULT');
    }

    private function seedCategories(): void
    {
        $rows = [];
        foreach (self::CATEGORIES as $name) {
            $rows[] = ['name' => $name, 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('menu_categories')->insert($rows);

        $byName = DB::table('menu_categories')->pluck('id', 'name');
        foreach (self::CATEGORIES as $key => $name) {
            $this->categoryIds[$key] = $byName[$name];
        }
    }

    private function seedIngredients(): void
    {
        $rows = [];
        foreach (self::INGREDIENTS as $key => [$name, $unit, $threshold]) {
            $mode = in_array($key, self::FIFO_INGREDIENTS, true) ? 'fifo' : 'fefo';
            $rows[] = [
                'name' => $name,
                'unit' => $unit,
                'low_stock_threshold' => $threshold,
                'batch_mode' => $mode,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('ingredients')->insert($rows);

        $byName = DB::table('ingredients')->pluck('id', 'name');
        foreach (self::INGREDIENTS as $key => [$name, $unit]) {
            $id = $byName[$name];
            $this->ingredientIds[$key] = $id;
            $this->ingredientKeys[$id] = $key;
            $this->ingredientUnits[$key] = $unit;
            $this->ingredientModes[$id] = in_array($key, self::FIFO_INGREDIENTS, true) ? 'fifo' : 'fefo';
            $this->dailyUsageEma[$id] = max(0.001, self::BATCH_CONFIG[$key][1] / 30);
        }
    }

    private function seedMenus(): void
    {
        foreach (self::MENUS as $name => $def) {
            $menu = Menu::create([
                'category_id' => $this->categoryIds[$def['category']],
                'name' => $name,
                'price' => $def['price'],
                'cost_price' => (int) round($def['price'] * 0.6),
                'status' => 'active',
                'discounted_price' => $def['price'] - $def['cashback'],
            ]);
            $this->menuIds[$name] = $menu->id;
            $this->menuNormalPrice[$name] = $def['price'];
            $this->menuStudentPrice[$name] = $def['price'] - $def['cashback'];
            $this->menuCostPrice[$name] = (int) round($def['price'] * 0.6);
            $this->menuIngredientsByName[$name] = $def['ingredients'];
        }
    }

    private function seedMenuIngredients(): void
    {
        $rows = [];
        foreach (self::MENUS as $menuName => $def) {
            foreach ($def['ingredients'] as $ingKey => $qty) {
                $rows[] = [
                    'menu_id' => $this->menuIds[$menuName],
                    'ingredient_id' => $this->ingredientIds[$ingKey],
                    'quantity_used' => $qty,
                    'unit' => $this->ingredientUnits[$ingKey] ?? null,
                ];
            }
        }
        DB::table('menu_ingredients')->insert($rows);
    }

    private function seedCafeTables(): void
    {
        for ($n = 1; $n <= 10; $n++) {
            CafeTable::create([
                'table_number' => $n,
            ]);
        }
    }

    private function seedInitialBatches(): void
    {
        $rows = [];
        foreach (self::INGREDIENTS as $key => [$name, $unit]) {
            $ingId = $this->ingredientIds[$key];
            [$cost, , $batchSize, $expiryMonths] = self::BATCH_CONFIG[$key];
            $receivedAt = $this->start->copy()->subDays($this->rng->int(3, 20))->setTime($this->rng->int(7, 15), $this->rng->int(0, 59));
            $qty = round(max($batchSize, $this->dailyUsageEma[$ingId] * 14) * $this->rng->float(1.0, 1.5), 3);
            $expiry = $expiryMonths < 1
                ? $receivedAt->copy()->addDays(max(3, (int) ($expiryMonths * 30)))
                : $receivedAt->copy()->addMonths((int) $expiryMonths);
            $unitCost = (int) round($cost * $this->rng->float(0.85, 1.15));

            $rows[] = [
                'ingredient_id' => $ingId,
                'quantity' => $qty,
                'expiry_date' => $expiry->toDateString(),
                'received_at' => $receivedAt->toDateTimeString(),
                'initial_quantity' => $qty,
                'allow_expired_usage' => false,
                'supplier_name' => $this->rng->pick(array_keys(self::SUPPLIERS)),
                'total_cost' => (int) round($qty * $unitCost),
                'payment_status' => 'unpaid',
                'batch_code' => sprintf('BCH-%s-%d', $receivedAt->format('dmy'), ++$this->batchSeq),
            ];
        }
        DB::table('ingredient_batches')->insert($rows);

        $batches = DB::table('ingredient_batches')
            ->select('id', 'ingredient_id', 'quantity', 'expiry_date', 'received_at', 'total_cost', 'payment_status', 'batch_code')
            ->orderBy('id')
            ->get();

        $movements = [];
        foreach ($batches as $b) {
            $this->batchCache[$b->ingredient_id][] = [
                'id' => $b->id,
                'quantity' => (float) $b->quantity,
                'expiry_date' => $b->expiry_date,
                'received_at' => $b->received_at,
                'total_cost' => (int) $b->total_cost,
                'payment_status' => $b->payment_status,
            ];

            $movements[] = [
                'ingredient_id' => $b->ingredient_id,
                'ingredient_batch_id' => $b->id,
                'order_id' => null,
                'order_item_id' => null,
                'stock_adjustment_id' => null,
                'movement_type' => 'purchase',
                'source_type' => null,
                'source_id' => null,
                'quantity_before' => 0,
                'quantity_change' => round((float) $b->quantity, 3),
                'quantity_after' => round((float) $b->quantity, 3),
                'reference' => $b->batch_code,
                'created_at' => $b->received_at,
                'updated_at' => $b->received_at,
            ];
        }

        $this->insertChunked('stock_movements', $movements);

        foreach (array_keys($this->batchCache) as $ingId) {
            $this->sortBatches($ingId);
        }
    }

    private function seedCashierShifts(): void
    {
        $rows = [];
        $count = count($this->cashierIds);
        $dayIndex = 0;

        for ($date = $this->start->copy(); $date->lte($this->end); $date->addDay()) {
            $key = $date->toDateString();
            $shifts = [
                [$this->cashierIds[$dayIndex % $count], 7, 15],
                [$this->cashierIds[($dayIndex + 1) % $count], 14, 22],
            ];

            foreach ($shifts as $n => [$cashierId, $startHour, $endHour]) {
                $startedAt = $date->copy()->setTime($startHour, 0, 0);
                $endedAt = $date->copy()->setTime($endHour, 0, 0);

                $this->shiftIndex[$key][] = [
                    'cashier_id' => $cashierId,
                    'start' => $startedAt,
                    'end' => $endedAt,
                ];

                $rows[] = [
                    'user_id' => $cashierId,
                    'session_id' => 'shift-' . $date->format('Ymd') . '-' . ($n + 1),
                    'started_at' => $startedAt->toDateTimeString(),
                    'ended_at' => $endedAt->toDateTimeString(),
                    'last_activity_at' => $endedAt->toDateTimeString(),
                    'is_active' => false,
                    'created_at' => $startedAt->toDateTimeString(),
                    'updated_at' => $endedAt->toDateTimeString(),
                ];
            }
            $dayIndex++;
        }

        foreach (array_chunk($rows, $this->insertChunk) as $chunk) {
            DB::table('cashier_histories')->insert($chunk);
        }
    }

    private function seedOrdersAndStock(): void
    {
        $daySeq = [];
        $totalDays = $this->start->diffInDays($this->end) + 1;
        $processedDays = 0;

        for ($date = $this->start->copy(); $date->lte($this->end); $date->addDay()) {
            $processedDays++;
            $dayKey = $date->toDateString();
            $target = $this->dayOrderTarget($date);

            if ($target > 0 && ! empty($this->shiftIndex[$dayKey])) {
                $this->processOrderDay($date, $dayKey, $target, $daySeq);
            }

            $this->maybeSeedAdjustmentsForDay($date);

            if ($processedDays % 30 === 0) {
                $this->command?->info(sprintf('  seed: %s (%d/%d hari)', $dayKey, $processedDays, $totalDays));
            }
        }
    }

    private function processOrderDay(Carbon $date, string $dayKey, int $target, array &$daySeq): void
    {
        $maxTime = $date->isToday() ? now() : $date->copy()->endOfDay();
        $drafts = [];

        for ($i = 0; $i < $target; $i++) {
            $shift = $this->pickShift($dayKey, $maxTime);
            if ($shift === null) {
                continue;
            }

            $time = $this->randomTimeIn($shift['start'], $shift['end']->lessThan($maxTime) ? $shift['end'] : $maxTime);
            if ($time === null) {
                continue;
            }

            $menus = $this->pickMenus();
            $isStudent = $this->rng->int(1, 10000) <= (int) round($this->studentRate * 10000);
            $paymentMethod = $this->pickPaymentMethod();
            $outcome = $paymentMethod === 'pay_later' ? $this->pickPayLaterOutcome() : null;
            $status = $paymentMethod === 'pay_later'
                ? ($outcome === 'full' ? 'completed' : 'unpaid')
                : $this->pickStatus($date);

            $items = [];
            $total = 0;
            foreach ($menus as $menuName => $qty) {
                $unitPrice = $isStudent ? $this->menuStudentPrice[$menuName] : $this->menuNormalPrice[$menuName];
                $subtotal = $unitPrice * $qty;
                $total += $subtotal;
                $items[] = [
                    'menu_id' => $this->menuIds[$menuName],
                    'menu_name' => $menuName,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'cost_price' => (int) round($this->menuCostPrice[$menuName] * $this->rng->float(0.9, 1.1)),
                    'subtotal' => $subtotal,
                ];
            }

            $processedAt = in_array($status, ['processing', 'completed'], true)
                ? $time->copy()->addMinutes($this->rng->int(2, 10))->addSeconds($this->rng->int(0, 59))
                : null;
            $completedAt = $status === 'completed'
                ? $time->copy()->addMinutes($this->rng->int(10, 45))->addSeconds($this->rng->int(0, 59))
                : null;
            $cancelledAt = $status === 'cancelled'
                ? $time->copy()->addMinutes($this->rng->int(5, 30))->addSeconds($this->rng->int(0, 59))
                : null;

            $drafts[] = [
                'time' => $time->copy(),
                'row' => [
                    'order_code' => null,
                    'table_id' => $this->rng->int(1, 100) <= 70 ? $this->rng->int(1, 10) : null,
                    'cashier_id' => $shift['cashier_id'],
                    'customer_name' => $this->rng->int(1, 100) <= 65 ? fake('id_ID')->name() : null,
                    'phone' => null,
                    'status' => $status,
                    'order_type' => $this->rng->pick(['qr', 'qr', 'qr', 'cashier', 'cashier']),
                    'total_amount' => $total,
                    'payment_method' => $paymentMethod,
                    'uuid' => (string) Str::uuid(),
                    'processed_by' => $status !== 'pending' ? $shift['cashier_id'] : null,
                    'processed_at' => $processedAt?->toDateTimeString(),
                    'completed_at' => $completedAt?->toDateTimeString(),
                    'cancelled_at' => $cancelledAt?->toDateTimeString(),
                    'created_at' => $time->toDateTimeString(),
                    'updated_at' => ($completedAt ?? $cancelledAt ?? $processedAt ?? $time)->toDateTimeString(),
                ],
                'items' => $items,
                'meta' => [
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'outcome' => $outcome,
                    'status' => $status,
                    'cashier_id' => $shift['cashier_id'],
                    'time' => $time->copy(),
                ],
            ];
        }

        if (empty($drafts)) {
            return;
        }

        usort($drafts, fn (array $a, array $b): int => $a['time'] <=> $b['time']);

        $orders = [];
        $itemsByCode = [];
        $metaByCode = [];

        foreach ($drafts as $draft) {
            $daySeq[$date->format('dmy')] = ($daySeq[$date->format('dmy')] ?? 0) + 1;
            $code = sprintf('ORD-%s-%04d', $date->format('dmy'), $daySeq[$date->format('dmy')]);

            $row = $draft['row'];
            $row['order_code'] = $code;

            $orders[] = $row;
            $itemsByCode[$code] = $draft['items'];
            $metaByCode[$code] = $draft['meta'];
        }

        foreach (array_chunk($orders, $this->insertChunk) as $chunk) {
            DB::table('orders')->insert($chunk);
        }

        $ids = DB::table('orders')
            ->whereIn('order_code', array_column($orders, 'order_code'))
            ->pluck('id', 'order_code');

        $itemRows = [];
        $movementRows = [];
        $paymentRows = [];

        foreach ($orders as $order) {
            $code = $order['order_code'];
            $orderId = (int) $ids[$code];
            $meta = $metaByCode[$code];

            $position = 0;
            foreach ($itemsByCode[$code] as $item) {
                $itemRows[] = [
                    'order_id' => $orderId,
                    'menu_id' => $item['menu_id'],
                    'item_position' => $position++,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'],
                    'subtotal' => $item['subtotal'],
                    'created_at' => $order['created_at'],
                    'updated_at' => $order['created_at'],
                ];
            }

            if ($meta['status'] === 'completed') {
                $this->deductOrderStock($code, $orderId, $itemsByCode[$code], $meta, $movementRows);
            }

            if ($meta['payment_method'] === 'pay_later') {
                foreach ($this->buildOrderPayments($orderId, $meta) as $payment) {
                    $paymentRows[] = $payment;
                }
            }
        }

        $this->insertChunked('order_items', $itemRows);
        $this->insertChunked('stock_movements', $movementRows);
        $this->insertChunked('order_payments', $paymentRows);
    }

    private function deductOrderStock(string $code, int $orderId, array $items, array $meta, array &$movementRows): void
    {
        $neededByIngredient = [];
        foreach ($items as $item) {
            foreach ($this->menuIngredientsByName[$item['menu_name']] as $ingKey => $perPortion) {
                $ingId = $this->ingredientIds[$ingKey];
                $neededByIngredient[$ingId] = ($neededByIngredient[$ingId] ?? 0) + ($perPortion * $item['quantity']);
            }
        }

        foreach ($neededByIngredient as $ingId => $needed) {
            $this->deductStock($ingId, $needed, $orderId, $code, $meta, $movementRows);
        }
    }

    private function deductStock(int $ingId, float $needed, int $orderId, string $code, array $meta, array &$movementRows): void
    {
        $remaining = round($needed, 3);
        $index = $this->batchPos[$ingId] ?? 0;
        $count = count($this->batchCache[$ingId]);

        while ($index < $count && $remaining > 0) {
            $quantity = $this->batchCache[$ingId][$index]['quantity'];

            if ($quantity <= 0) {
                $index++;
                continue;
            }

            $before = round($quantity, 3);
            $take = min($before, $remaining);
            $this->batchCache[$ingId][$index]['quantity'] = round($before - $take, 4);
            $remaining = round($remaining - $take, 3);

            $movementRows[] = [
                'ingredient_id' => $ingId,
                'ingredient_batch_id' => $this->batchCache[$ingId][$index]['id'],
                'order_id' => $orderId,
                'order_item_id' => null,
                'stock_adjustment_id' => null,
                'movement_type' => 'sale',
                'source_type' => null,
                'source_id' => null,
                'quantity_before' => $before,
                'quantity_change' => -round($take, 3),
                'quantity_after' => round($this->batchCache[$ingId][$index]['quantity'], 3),
                'reference' => $code,
                'created_at' => $meta['time']->toDateTimeString(),
                'updated_at' => $meta['time']->toDateTimeString(),
            ];

            if ($this->batchCache[$ingId][$index]['quantity'] <= 0) {
                $index++;
            }
        }

        $this->batchPos[$ingId] = $index;

        if ($remaining > 0) {
            $this->restock($ingId, $remaining, $meta['time'], $movementRows);
            $lastIndex = array_key_last($this->batchCache[$ingId]);
            $before = round($this->batchCache[$ingId][$lastIndex]['quantity'], 3);
            $take = min($before, $remaining);
            $this->batchCache[$ingId][$lastIndex]['quantity'] = round($before - $take, 4);

            $movementRows[] = [
                'ingredient_id' => $ingId,
                'ingredient_batch_id' => $this->batchCache[$ingId][$lastIndex]['id'],
                'order_id' => $orderId,
                'order_item_id' => null,
                'stock_adjustment_id' => null,
                'movement_type' => 'sale',
                'source_type' => null,
                'source_id' => null,
                'quantity_before' => $before,
                'quantity_change' => -round($take, 3),
                'quantity_after' => round($before - $take, 3),
                'reference' => $code,
                'created_at' => $meta['time']->toDateTimeString(),
                'updated_at' => $meta['time']->toDateTimeString(),
            ];

            $this->batchPos[$ingId] = $this->batchCache[$ingId][$lastIndex]['quantity'] > 0
                ? $lastIndex
                : $lastIndex + 1;
        }
    }

    private function restock(int $ingId, float $extraNeeded, Carbon $at, array &$movementRows): int
    {
        $key = $this->ingredientKeys[$ingId];
        [$cost, , $batchSize, $expiryMonths] = self::BATCH_CONFIG[$key];
        $ema = max($this->dailyUsageEma[$ingId] ?? 0.001, 0.001);
        $qty = max((float) $batchSize, round($ema * 14 + $extraNeeded, 3));

        $receivedAt = $at->copy()->subMinutes($this->rng->int(20, 90));
        $expiry = $expiryMonths < 1
            ? $receivedAt->copy()->addDays(max(3, (int) ($expiryMonths * 30)))
            : $receivedAt->copy()->addMonths((int) $expiryMonths);
        $unitCost = (int) round($cost * $this->rng->float(0.85, 1.15));
        $batchCode = sprintf('BCH-%s-%d', $receivedAt->format('dmy'), ++$this->batchSeq);
        $totalCost = (int) round($qty * $unitCost);

        $id = DB::table('ingredient_batches')->insertGetId([
            'ingredient_id' => $ingId,
            'quantity' => $qty,
            'expiry_date' => $expiry->toDateString(),
            'received_at' => $receivedAt->toDateTimeString(),
            'initial_quantity' => $qty,
            'allow_expired_usage' => false,
            'supplier_name' => $this->rng->pick(array_keys(self::SUPPLIERS)),
            'total_cost' => $totalCost,
            'payment_status' => 'unpaid',
            'batch_code' => $batchCode,
        ]);

        $this->batchCache[$ingId][] = [
            'id' => $id,
            'quantity' => $qty,
            'expiry_date' => $expiry->toDateString(),
            'received_at' => $receivedAt->toDateTimeString(),
            'total_cost' => $totalCost,
            'payment_status' => 'unpaid',
        ];

        $movementRows[] = [
            'ingredient_id' => $ingId,
            'ingredient_batch_id' => $id,
            'order_id' => null,
            'order_item_id' => null,
            'stock_adjustment_id' => null,
            'movement_type' => 'purchase',
            'source_type' => null,
            'source_id' => null,
            'quantity_before' => 0,
            'quantity_change' => round($qty, 3),
            'quantity_after' => round($qty, 3),
            'reference' => $batchCode,
            'created_at' => $receivedAt->toDateTimeString(),
            'updated_at' => $receivedAt->toDateTimeString(),
        ];

        return (int) $id;
    }

    private function maybeSeedAdjustmentsForDay(Carbon $date): void
    {
        if ($this->rng->int(1, 10) > 1) {
            return;
        }

        $time = $date->copy()->setTime($this->rng->int(8, 16), $this->rng->int(0, 59));
        if ($time->greaterThan(now())) {
            return;
        }

        $isIncrease = $this->rng->int(1, 100) <= 60;
        $ingKey = $this->rng->pick(array_keys(self::INGREDIENTS));
        $ingId = $this->ingredientIds[$ingKey];
        $current = $this->getTotalStock($ingId);
        $movementRows = [];

        if ($isIncrease) {
            $qty = max(0.5, round(($this->dailyUsageEma[$ingId] ?? 1) * $this->rng->int(1, 5), 3));
            $before = $current;
            $after = round($current + $qty, 3);
            $movementRows[] = $this->adjustmentIncreaseBatch($ingId, $qty, $time);
        } else {
            $qty = min(round($current * $this->rng->float(0.05, 0.15), 3), max(0, $current - 0.001));
            if ($qty <= 0) {
                return;
            }
            $before = $current;
            $after = round($current - $qty, 3);
            $this->deductBatchesForAdjustment($ingId, $qty, $time, $movementRows);
        }

        if (empty($movementRows)) {
            return;
        }

        $adjustmentId = DB::table('stock_adjustments')->insertGetId([
            'ingredient_id' => $ingId,
            'adjustment_type' => $isIncrease ? 'increase' : 'decrease',
            'quantity' => $qty,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reason' => $isIncrease ? 'Koreksi stok setelah stock opname' : 'Bahan rusak / kedaluwarsa — penyesuaian stok',
            'reported_by' => $this->adminId,
            'adjusted_at' => $time->toDateTimeString(),
            'code' => sprintf('ADJ-%s-%d', $date->format('dmy'), ++$this->adjustmentSeq),
            'created_at' => $time->toDateTimeString(),
            'updated_at' => $time->toDateTimeString(),
        ]);

        foreach ($movementRows as &$movement) {
            $movement['stock_adjustment_id'] = $adjustmentId;
            $movement['movement_type'] = $isIncrease ? 'adjustment_increase' : 'adjustment_decrease';
            $movement['reference'] = sprintf('ADJ-%d', $adjustmentId);
        }
        unset($movement);

        $this->insertChunked('stock_movements', $movementRows);
    }

    private function adjustmentIncreaseBatch(int $ingId, float $qty, Carbon $time): array
    {
        $key = $this->ingredientKeys[$ingId];
        $expiryMonths = self::BATCH_CONFIG[$key][3];
        $expiry = $expiryMonths < 1
            ? $time->copy()->addDays(max(3, (int) ($expiryMonths * 30)))
            : $time->copy()->addMonths((int) $expiryMonths);
        $batchCode = sprintf('BCH-%s-%d', $time->format('dmy'), ++$this->batchSeq);

        $id = DB::table('ingredient_batches')->insertGetId([
            'ingredient_id' => $ingId,
            'quantity' => $qty,
            'expiry_date' => $expiry->toDateString(),
            'received_at' => $time->toDateTimeString(),
            'initial_quantity' => $qty,
            'allow_expired_usage' => false,
            'supplier_name' => null,
            'total_cost' => 0,
            'payment_status' => 'paid',
            'batch_code' => $batchCode,
        ]);

        $this->batchCache[$ingId][] = [
            'id' => $id,
            'quantity' => $qty,
            'expiry_date' => $expiry->toDateString(),
            'received_at' => $time->toDateTimeString(),
            'total_cost' => 0,
            'payment_status' => 'paid',
        ];

        return [
            'ingredient_id' => $ingId,
            'ingredient_batch_id' => $id,
            'order_id' => null,
            'order_item_id' => null,
            'stock_adjustment_id' => null,
            'movement_type' => 'adjustment_increase',
            'source_type' => null,
            'source_id' => null,
            'quantity_before' => 0,
            'quantity_change' => round($qty, 3),
            'quantity_after' => round($qty, 3),
            'reference' => $batchCode,
            'created_at' => $time->toDateTimeString(),
            'updated_at' => $time->toDateTimeString(),
        ];
    }

    private function deductBatchesForAdjustment(int $ingId, float $qty, Carbon $time, array &$movementRows): ?int
    {
        $remaining = round($qty, 3);
        $lastBatchId = null;

        foreach ($this->batchCache[$ingId] as &$batch) {
            if ($remaining <= 0) {
                break;
            }
            if ($batch['quantity'] <= 0) {
                continue;
            }
            $take = min($batch['quantity'], $remaining);
            $before = round($batch['quantity'], 3);
            $batch['quantity'] = round($before - $take, 4);
            $remaining = round($remaining - $take, 3);
            $lastBatchId = $batch['id'];

            $movementRows[] = [
                'ingredient_id' => $ingId,
                'ingredient_batch_id' => $batch['id'],
                'order_id' => null,
                'order_item_id' => null,
                'stock_adjustment_id' => null,
                'movement_type' => 'adjustment_decrease',
                'source_type' => null,
                'source_id' => null,
                'quantity_before' => $before,
                'quantity_change' => -round($take, 3),
                'quantity_after' => round($batch['quantity'], 3),
                'reference' => null,
                'created_at' => $time->toDateTimeString(),
                'updated_at' => $time->toDateTimeString(),
            ];
        }
        unset($batch);

        return $lastBatchId;
    }

    private function sortBatches(int $ingId): void
    {
        if (empty($this->batchCache[$ingId])) {
            return;
        }

        $mode = $this->ingredientModes[$ingId] ?? 'fefo';
        usort($this->batchCache[$ingId], function (array $a, array $b) use ($mode): int {
            if ($mode === 'fifo') {
                return [$a['received_at'], $a['expiry_date'], $a['id']] <=> [$b['received_at'], $b['expiry_date'], $b['id']];
            }

            return [$a['expiry_date'], $a['received_at'], $a['id']] <=> [$b['expiry_date'], $b['received_at'], $b['id']];
        });
    }

    private function finalizeBatchesAndPayments(): void
    {
        $paymentRows = [];
        $batchRows = [];

        foreach ($this->batchCache as $batches) {
            foreach ($batches as $batch) {
                $totalCost = (int) $batch['total_cost'];
                $unpaid = $this->rng->int(1, 10000) <= (int) round($this->payableUnpaidRatio * 10000);

                if ($unpaid && $totalCost > 0) {
                    $partial = $this->rng->int(1, 100) <= 50;
                    if ($partial) {
                        $amount = (int) round($totalCost * $this->rng->float(0.3, 0.7));
                        $paymentRows[] = $this->batchPaymentRow($batch['id'], $amount, $batch['received_at']);
                    }
                    $status = 'unpaid';
                } else {
                    if ($totalCost > 0) {
                        $paymentRows[] = $this->batchPaymentRow($batch['id'], $totalCost, $batch['received_at']);
                    }
                    $status = 'paid';
                }

                $batchRows[] = [
                    'id' => $batch['id'],
                    'quantity' => round($batch['quantity'], 3),
                    'payment_status' => $status,
                ];
            }
        }

        foreach (array_chunk($paymentRows, $this->insertChunk) as $chunk) {
            DB::table('batch_payments')->insert($chunk);
        }
        foreach (array_chunk($batchRows, $this->insertChunk) as $chunk) {
            $this->updateBatchRows($chunk);
        }
    }

    private function updateBatchRows(array $rows): void
    {
        $values = [];
        $bindings = [];
        foreach ($rows as $row) {
            $values[] = '(?::bigint, ?::numeric, ?)';
            $bindings[] = $row['id'];
            $bindings[] = $row['quantity'];
            $bindings[] = $row['payment_status'];
        }

        $sql = 'UPDATE ingredient_batches AS ib SET quantity = v.quantity, payment_status = v.payment_status '
            . 'FROM (VALUES ' . implode(', ', $values) . ') AS v(id, quantity, payment_status) WHERE ib.id = v.id';

        DB::update($sql, $bindings);
    }

    private function batchPaymentRow(int $batchId, int $amount, string $receivedAt): array
    {
        $paidAt = Carbon::parse($receivedAt)->addDays($this->rng->int(0, 14))->setTime($this->rng->int(8, 16), $this->rng->int(0, 59));

        return [
            'ingredient_batch_id' => $batchId,
            'amount' => $amount,
            'payment_date' => $paidAt->toDateTimeString(),
            'payment_method' => $this->rng->pick(['cash', 'transfer', 'qris']),
            'created_at' => $paidAt->toDateTimeString(),
            'updated_at' => $paidAt->toDateTimeString(),
        ];
    }

    private function buildOrderPayments(int $orderId, array $meta): array
    {
        if ($meta['outcome'] === 'none') {
            return [];
        }

        $total = (int) $meta['total'];
        $amount = $meta['outcome'] === 'full'
            ? $total
            : (int) round($total * $this->rng->float(0.3, 0.7));

        $paidAt = $meta['time']->copy()->addMinutes($this->rng->int(5, 240));

        return [[
            'order_id' => $orderId,
            'amount' => $amount,
            'payment_date' => $paidAt->toDateTimeString(),
            'payment_method' => $this->rng->pick(['cash', 'qris']),
            'created_at' => $paidAt->toDateTimeString(),
            'updated_at' => $paidAt->toDateTimeString(),
        ]];
    }

    private function getTotalStock(int $ingredientId): float
    {
        return round(array_sum(array_column($this->batchCache[$ingredientId] ?? [], 'quantity')), 3);
    }

    private function dayOrderTarget(Carbon $date): int
    {
        $multiplier = in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY], true) ? $this->weekendMultiplier : 1.0;
        $season = (float) ($this->monthlySeasonality[$date->month] ?? 1.0);
        $jitter = 1 + $this->rng->float(-$this->dailyJitter, $this->dailyJitter);

        return max(0, (int) round($this->ordersPerDay * $multiplier * $season * $jitter));
    }

    private function pickShift(string $dayKey, Carbon $maxTime): ?array
    {
        $shifts = $this->shiftIndex[$dayKey] ?? [];
        $available = array_values(array_filter($shifts, fn (array $shift): bool => $shift['start']->lessThan($maxTime)));

        if (empty($available)) {
            return null;
        }

        return $available[$this->rng->int(0, count($available) - 1)];
    }

    private function randomTimeIn(Carbon $start, Carbon $end): ?Carbon
    {
        $startMinutes = $start->hour * 60 + $start->minute;
        $endMinutes = $end->hour * 60 + $end->minute;

        if ($endMinutes - $startMinutes < 1) {
            return null;
        }

        $minute = $this->rng->int($startMinutes, $endMinutes - 1);

        return $start->copy()->setTime(intdiv($minute, 60), $minute % 60, $this->rng->int(0, 59));
    }

    private function pickMenus(): array
    {
        $count = $this->rng->int($this->itemsMin, $this->itemsMax);
        $names = array_keys(self::MENUS);
        $count = min($count, count($names));
        $picked = [];

        while (count($picked) < $count) {
            $weights = [];
            foreach ($names as $name) {
                if (isset($picked[$name])) {
                    continue;
                }
                $weights[$name] = $this->menuWeight($name, $picked);
            }
            $chosen = $this->weightedPick($weights);
            $picked[$chosen] = $this->rng->int($this->qtyMin, $this->qtyMax);
        }

        return $picked;
    }

    private function menuWeight(string $name, array $picked): float
    {
        $category = self::MENUS[$name]['category'];
        $weight = match ($category) {
            'makanan_berat' => 1.5,
            'makanan_ringan' => 2.0,
            'minuman_kemasan' => 2.0,
            default => 3.0,
        };

        if (! empty($picked)) {
            $pickedDrink = false;
            $pickedFood = false;
            foreach (array_keys($picked) as $pickedName) {
                $pickedCategory = self::MENUS[$pickedName]['category'];
                if (str_starts_with($pickedCategory, 'makanan')) {
                    $pickedFood = true;
                } else {
                    $pickedDrink = true;
                }
            }
            $isFood = str_starts_with($category, 'makanan');
            if ($pickedDrink && $isFood) {
                $weight *= 2.2;
            }
            if ($pickedFood && ! $isFood) {
                $weight *= 2.2;
            }
        }

        return $weight;
    }

    private function weightedPick(array $weights): string
    {
        $total = array_sum($weights);
        $roll = $this->rng->float(0, $total);

        foreach ($weights as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return (string) $key;
            }
        }

        return (string) array_key_first($weights);
    }

    private function pickPaymentMethod(): string
    {
        $total = array_sum($this->paymentMix);
        $roll = $this->rng->int(1, max(1, (int) $total));
        $cumulative = 0;
        foreach ($this->paymentMix as $method => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return (string) $method;
            }
        }

        return 'cash';
    }

    private function pickPayLaterOutcome(): string
    {
        $roll = $this->rng->float(0, 1);
        if ($roll <= $this->payLaterFullRatio) {
            return 'full';
        }
        if ($roll <= $this->payLaterFullRatio + $this->payLaterPartialRatio) {
            return 'partial';
        }

        return 'none';
    }

    private function pickStatus(Carbon $date): string
    {
        if ($date->lessThan(now()->subDays(30)->startOfDay())) {
            return 'completed';
        }

        $roll = $this->rng->int(1, 100);

        return match (true) {
            $roll <= 70 => 'completed',
            $roll <= 85 => 'processing',
            $roll <= 95 => 'pending',
            default => 'cancelled',
        };
    }

    private function insertChunked(string $table, array &$rows): void
    {
        if (empty($rows)) {
            return;
        }

        foreach (array_chunk($rows, $this->insertChunk) as $chunk) {
            DB::table($table)->insert($chunk);
        }

        $rows = [];
    }

    private function makeRng(int $seed): object
    {
        return new class($seed)
        {
            public function __construct(int $seed)
            {
                mt_srand($seed);
            }

            public function int(int $min, int $max): int
            {
                return mt_rand($min, $max);
            }

            public function float(float $min, float $max): float
            {
                return $min + mt_rand() / mt_getrandmax() * ($max - $min);
            }

            public function pick(array $items): mixed
            {
                return $items[mt_rand(0, count($items) - 1)];
            }
        };
    }
}
