<?php

namespace App\Http\Controllers\Cashier;

use App\Actions\PlaceCashierOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class CashierPesananBaruController extends Controller
{
    public function index(): Response
    {
        // Cache 5 menit — menu jarang berubah, admin bisa clear cache jika update menu
        $categories = Cache::remember('menu_categories_cashier_v2', 300, fn () => Category::with([
            'menus' => fn ($q) => $q->orderBy('name')
                ->with(['menuIngredients.ingredient.batches' => fn ($q) => $q
                    ->where('quantity', '>', 0)
                    ->where(fn ($q) => $q
                        ->whereNull('expiry_date')
                        ->orWhere('expiry_date', '>', now())
                        ->orWhere('allow_expired_usage', true)
                    ),
                ]),
        ])
            ->orderBy('name')
            ->get()
            ->each(function ($category) {
                $category->menus->each(function ($menu) {
                    $menu->available_stock = $menu->computeAvailableServings();
                });
            })
        );

        return Inertia::render('Kasir/PesananBaru', ['categories' => $categories]);
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
