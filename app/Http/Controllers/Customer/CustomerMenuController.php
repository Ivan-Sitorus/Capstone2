<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CustomerMenuController extends Controller
{
    private function findTable(?string $tableId): ?CafeTable
    {
        if (!$tableId) return null;

        return Cache::remember("cafe_table_{$tableId}", 600, fn() =>
            CafeTable::select(['id', 'table_number'])->find($tableId)
        );
    }

    public function showIdentitas(Request $request)
    {
        $table = $this->findTable($request->query('table'));

        if ($table && $table->table_number > 10) {
            abort(404);
        }

        return Inertia::render('Customer/Identitas', ['table' => $table]);
    }

    public function index(Request $request)
    {
        $categories = Cache::remember('customer_menu_v2', 300, function () {
            return Category::with([
                // Tetap kirim menu yang habis (is_available = false) agar
                // ditampilkan dengan label "Stok Habis", bukan disembunyikan.
                'menus' => fn($q) => $q
                    ->select(['id', 'category_id', 'name', 'price', 'cashback', 'image', 'is_available'])
                    ->orderBy('is_available', 'desc')
                    ->orderBy('name'),
            ])->where('is_active', true)
              ->select(['id', 'name', 'slug'])
              ->orderBy('name')
              ->get();
        });

        $table = $this->findTable($request->query('table'));

        if ($table && $table->table_number > 10) {
            abort(404);
        }

        return Inertia::render('Customer/Menu/Index', compact('categories', 'table'));
    }
}
