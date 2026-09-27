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
        if (! is_string($token) || $token === '') {
            return null;
        }

        // QR meja memakai token opaque UUID.
        if (Str::isUuid($token)) {
            return Cache::remember("cafe_table_{$token}", 600, fn () =>
                CafeTable::select(['id', 'table_number', 'qr_token'])->where('qr_token', $token)->first()
            );
        }

        // Tautan internal aplikasi (identitas, keranjang, pembayaran) memakai id
        // meja numerik, jadi nilai numerik diresolusi lewat kolom id. Nilai lain
        // (termasuk array) ditolak agar tidak pernah dikirim ke kolom uuid
        // (mencegah SQLSTATE 22P02) dan tetap dijawab 404.
        if (ctype_digit($token)) {
            return CafeTable::select(['id', 'table_number', 'qr_token'])->where('id', (int) $token)->first();
        }

        return null;
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
