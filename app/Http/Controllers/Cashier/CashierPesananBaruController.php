<?php

namespace App\Http\Controllers\Cashier;

use App\Actions\PlaceCashierOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CashierPesananBaruController extends Controller
{
    public function index()
    {
        // Cache 5 menit — menu jarang berubah, admin bisa clear cache jika update menu
        $categories = Cache::remember('menu_categories_cashier', 300, fn () => Category::with([
            'menus' => fn ($q) => $q->orderBy('name'),
        ])
            ->orderBy('name')
            ->get()
        );

        return Inertia::render('Kasir/PesananBaru', ['categories' => $categories]);
    }

    public function store(StoreOrderRequest $request, PlaceCashierOrderAction $action)
    {
        try {
            $result = $action->handle($request);

            return back()
                ->with('success', 'Pesanan berhasil dibuat')
                ->with('order_id', $result['order_id'])
                ->with('order_total', $result['order_total'])
                ->with('order_code', $result['order_code']);
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal memproses pesanan: '.$e->getMessage());
        }
    }
}
