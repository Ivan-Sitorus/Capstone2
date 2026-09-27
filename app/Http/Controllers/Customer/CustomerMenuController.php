<?php

namespace App\Http\Controllers\Customer;

use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Category;
use App\Support\CustomerSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CustomerMenuController extends Controller
{
    private function findTable(mixed $token): ?CafeTable
    {
        // Token meja bersifat opaque UUID. Nilai apa pun yang bukan UUID
        // (mis. nomor lama seperti "1") langsung dianggap tidak valid,
        // sehingga tidak pernah dikirim ke kolom uuid (mencegah SQLSTATE 22P02).
        if (! is_string($token) || ! Str::isUuid($token)) {
            return null;
        }

        return Cache::remember("cafe_table_{$token}", 600, fn () =>
            CafeTable::select(['id', 'table_number', 'qr_token'])->where('qr_token', $token)->first()
        );
    }

    public function showIdentity(Request $request): Response
    {
        $tableParam = $request->query('table');
        $table = $this->findTable($tableParam);

        // Token ada tapi tidak valid/tidak ditemukan -> 404, bukan 500.
        if ($tableParam !== null && ! $table) {
            abort(404);
        }

        return Inertia::render('Customer/Identity', ['table' => $table]);
    }

    public function submitIdentity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'table_id' => 'nullable|integer|exists:cafe_tables,id',
        ]);

        // Persist the anonymous identity server-side so the order endpoints can
        // authorise against it rather than a client-supplied phone.
        CustomerSession::bind($validated['customer_name'], $validated['phone']);

        return redirect()->route('customer.menu');
    }

    public function index(Request $request): Response
    {
        $categories = Cache::remember('customer_menu', 300, function () {
            return Category::with([
                    'menus' => fn ($q) => $q
                        ->select(['id', 'category_id', 'name', 'price', 'image', 'status'])
                        ->where('status', MenuStatus::Active->value)
                        ->orderBy('name'),
            ])
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();
        });

        $tableParam = $request->query('table');
        $table = $this->findTable($tableParam);

        if ($tableParam !== null && ! $table) {
            abort(404);
        }

        return Inertia::render('Customer/Menu/Index', [
            'categories' => $categories,
            'table' => $table,
        ]);
    }
}
