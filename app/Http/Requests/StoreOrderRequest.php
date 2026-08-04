<?php

namespace App\Http\Requests;

use App\Services\InventoryService;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function __construct(
        protected InventoryService $inventoryService,
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|uuid|unique:orders,uuid',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1|max:999999',
            'payment_method' => 'required|in:cash,qris,bayar_nanti',
            'customer_name' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Keranjang tidak boleh kosong.',
            'items.min' => 'Minimal 1 item dalam pesanan.',
            'items.*.menu_id.exists' => 'Menu tidak ditemukan.',
            'items.*.quantity.min' => 'Jumlah item minimal 1.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $result = $this->inventoryService
                ->canFulfillOrder($this->input('items'));

            if (!$result['can_fulfill']) {
                foreach ($result['insufficient_ingredients'] as $item) {
                    $name = $item['menu_name'] ?? $item['ingredient_name'] ?? 'Unknown';
                    $message = "Stok '{$name}' tidak mencukupi. Dibutuhkan: {$item['required']}, Tersedia: {$item['available']}";
                    $validator->errors()->add('items', $message);
                }
            }
        });
    }
}
