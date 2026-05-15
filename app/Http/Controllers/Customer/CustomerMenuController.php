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
    public function showIdentitas(Request $request)
    {
        $tableId = $request->query('table');

        if (! $tableId) {
            return redirect()->route('pelanggan.menu');
        }

        return redirect()->route('pelanggan.menu', ['table' => $tableId]);
    }

    public function index(Request $request)
    {
        $categories = Cache::remember('customer_menu_v1', 300, function () {
            return Category::with([
                'menus' => fn ($q) => $q
                    ->where('is_available', true)
                    ->select(['id', 'category_id', 'name', 'price', 'cashback', 'image'])
                    ->orderBy('name'),
            ])->where('is_active', true)
                ->select(['id', 'name', 'slug'])
                ->orderBy('name')
                ->get();
        });

        $table = null;

        if ($request->has('table')) {
            $tableNumber = $request->query('table');

            if (! is_numeric($tableNumber) || (int) $tableNumber > 2147483647) {
                return Inertia::render('Errors/404', ['status' => 404, 'message' => 'Nomor meja tidak valid'])
                    ->toResponse($request)
                    ->setStatusCode(404);
            }

            $table = CafeTable::where('table_number', (int) $tableNumber)->first();

            if (! $table) {
                return Inertia::render('Errors/404', ['status' => 404, 'message' => 'Meja tidak ditemukan'])
                    ->toResponse($request)
                    ->setStatusCode(404);
            }
        }

        return Inertia::render('Pelanggan/Menu/Index', compact('categories', 'table'));
    }
}
