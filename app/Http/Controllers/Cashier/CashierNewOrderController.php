<?php

namespace App\Http\Controllers\Cashier;

use App\Actions\PlaceCashierOrderAction;
use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class CashierNewOrderController extends Controller
{
    public function index(): Response
    {
        // Cache 5 minutes: menu rarely changes, admin can clear cache when
        // updating menu.
        //
        // Only the columns the POS grid actually reads are selected. The cashier
        // page has no use for cost_price or the ingredient/batch graph (which
        // carries supplier names, batch costs and payment status), so none of
        // that ever leaves the server.
        $categories = Cache::remember('menu_categories_cashier_v2', 300, fn () => Category::query()
            ->select(['id', 'name'])
            ->with([
                'menus' => fn ($q) => $q
                    ->select(['id', 'category_id', 'name', 'price'])
                    ->where('status', MenuStatus::Active->value)
                    ->orderBy('name'),
            ])
            ->orderBy('name')
            ->get()
        );

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
