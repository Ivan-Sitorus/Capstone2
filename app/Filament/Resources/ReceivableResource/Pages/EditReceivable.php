<?php

namespace App\Filament\Resources\ReceivableResource\Pages;

use App\Filament\Resources\ReceivableResource;
use App\Models\Menu;
use App\Models\OrderItem;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditReceivable extends EditRecord
{
    protected static string $resource = ReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        if ($record->order) {
            $data['items'] = $record->order->items->map(fn ($item) => [
                'menu_id' => $item->menu_id,
                'quantity' => $item->quantity,
            ])->values()->toArray();
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $receivableData = collect($data)->except(['items'])->toArray();
            $record->update($receivableData);

            if (isset($data['items']) && is_array($data['items']) && ! empty($data['items'])) {
                $order = $record->order;

                if ($order) {
                    $order->items()->delete();

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

                    $order->update(['total_amount' => $totalAmount]);
                    $record->update(['amount' => $totalAmount]);
                }
            }

            return $record;
        });
    }
}
