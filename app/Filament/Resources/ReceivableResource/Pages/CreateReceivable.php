<?php

namespace App\Filament\Resources\ReceivableResource\Pages;

use App\Filament\Resources\ReceivableResource;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateReceivable extends CreateRecord
{
    protected static string $resource = ReceivableResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Order
            $order = Order::create([
                'cashier_id' => Auth::id(),
                'order_type' => 'cashier',
                'payment_method' => 'bayar_nanti',
                'customer_name' => $data['customer_name'],
                'status' => Order::STATUS_PENDING,
                'is_paid' => false,
                'total_amount' => 0,
            ]);

            // 2. Create OrderItems from repeater items
            $totalAmount = 0;
            $menuIds = collect($data['items'])->pluck('menu_id')->filter()->unique();
            $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

            foreach ($data['items'] as $item) {
                $menu = $menus[$item['menu_id']] ?? null;
                if (! $menu) {
                    continue;
                }
                $qty = (int) $item['quantity'];
                $subtotal = $menu->price * $qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                    'quantity' => $qty,
                    'unit_price' => $menu->price,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            // 3. Update Order total
            $order->update(['total_amount' => $totalAmount]);

            // 4. Update Receivable (auto-created by Order::created event)
            $receivable = $order->receivable;
            $receivable->update([
                'amount' => $totalAmount,
                'customer_name' => $data['customer_name'],
                'invoice_date' => $data['invoice_date'] ?? now(),
                'due_date' => $data['due_date'] ?? now()->addDays(30),
                'status' => $data['status'] ?? 'pending',
                'paid_amount' => $data['paid_amount'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            return $receivable;
        });
    }
}
