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
        $table   = $tableId
            ? CafeTable::select(['id', 'table_number'])->find($tableId)
            : null;

        return Inertia::render('Customer/Identitas', ['table' => $table]);
    }

    public function index(Request $request)
    {
        $categories = Cache::remember('customer_menu_v1', 300, function () {
            return Category::with([
                'menus' => fn($q) => $q
                    ->where('is_available', true)
                    ->select(['id', 'category_id', 'name', 'price', 'cashback', 'image'])
                    ->orderBy('name'),
            ])->where('is_active', true)
              ->select(['id', 'name', 'slug'])
              ->orderBy('name')
              ->get();
        });

        $table = $request->query('table')
            ? CafeTable::select(['id', 'table_number'])->find($request->query('table'))
            : null;

        return Inertia::render('Customer/Menu/Index', compact('categories', 'table'));
    }
}
