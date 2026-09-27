<?php

namespace App\Http\Controllers\Cashier;

use App\Actions\PlaceCashierOrderAction;
use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class CashierNewOrderController extends Controller
{
    public function index(): Response
    {
        // Cache 5 minutes — menu rarely changes, admin can clear cache when updating menu
        $categories = Cache::remember('menu_categories_cashier', 300, fn () => Category::with([
            'menus' => fn ($q) => $q->where('status', MenuStatus::Active->value)->orderBy('name'),
        ])
            ->orderBy('name')
            ->get()
        );

        // Ketersediaan dihitung di luar cache agar perubahan stok/resep selalu
        // terbaru walau daftar menu sendiri masih tersimpan di cache.
        $categories->each(function (Category $category) {
            $category->menus->each(function (Menu $menu) {
                $stock = $menu->computeAvailableServings();
                $menu->available_stock = $stock;
                $menu->is_available = $stock === null || $stock > 0;
            });
        });

        return Inertia::render('Cashier/NewOrder', ['categories' => $categories]);
    }

    public function store(StoreOrderRequest $request, PlaceCashierOrderAction $action): RedirectResponse
    {
        try {
            $result = $action->handle($request);

            return back()
                ->with('success', 'Pesanan berhasil dibuat')
                ->with('order_id', $result['order_id'])
                ->with('order_total', $result['order_total'])
                ->with('order_code', $result['order_code']);
        } catch (\RuntimeException $e) {
            return back()
                ->with('error', 'Gagal memproses pesanan: '.$e->getMessage());
        }
    }
}
