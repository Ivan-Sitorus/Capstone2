<?php

namespace App\Filament\Resources\ReceivableResource\Pages;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Filament\Forms\Components\NumericInput;
use App\Filament\Resources\ReceivableResource;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;

class ListReceivables extends ListRecords
{
    protected static string $resource = ReceivableResource::class;

    public function getTitle(): string
    {
        return 'Piutang';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Piutang';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Piutang')
                ->icon('heroicon-o-plus')
                ->modalHeading('Buat Piutang Baru')
                ->form([
                    Select::make('cashier_id')
                        ->label('Kasir')
                        ->options(fn () => User::whereIn('role', ['cashier', 'admin'])->orderBy('name')->pluck('name', 'id'))
                        ->default(fn () => auth()->id())
                        ->searchable()
                        ->native(false)
                        ->required(),
                    TextInput::make('customer_name')
                        ->label('Nama Pelanggan')
                        ->required()
                        ->maxLength(255),
                    Repeater::make('items')
                        ->label('Item Pesanan')
                        ->schema([
                            Select::make('menu_id')
                                ->label('Menu')
                                ->options(fn () => Menu::where('status', 'active')->orderBy('name')->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->native(false)
                                ->live(),
                            NumericInput::apply(TextInput::make('quantity'), maxDigits: 5)
                                ->label('Jumlah')
                                ->required()
                                ->minValue(1)
                                ->default(1),
                            Select::make('price_type')
                                ->label('Jenis Harga')
                                ->options(function (Get $get): array {
                                    $menu = Menu::find($get('menu_id'));
                                    if ($menu && $menu->discounted_price) {
                                        return [
                                            'normal'   => 'Normal (Rp ' . number_format($menu->price, 0, ',', '.') . ')',
                                            'discount' => 'Diskon (Rp ' . number_format($menu->discounted_price, 0, ',', '.') . ')',
                                        ];
                                    }
                                    return ['normal' => 'Normal'];
                                })
                                ->native(false)
                                ->default('normal')
                                ->live(),
                        ])
                        ->columns(3)
                        ->minItems(1)
                        ->required(),
                    NumericInput::money('paid_amount')
                        ->label('Sudah Dibayar')
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp'),
                ])
                ->action(function (array $data) {
                    $menuIds = array_column($data['items'], 'menu_id');
                    $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

                    $resolveUnitPrice = function (array $item, \App\Models\Menu $menu): int {
                        if (($item['price_type'] ?? 'normal') === 'discount' && $menu->discounted_price) {
                            return $menu->discounted_price;
                        }
                        return $menu->price ?? 0;
                    };

                    $total = 0;

                    foreach ($data['items'] as $item) {
                        $menu = $menus->get($item['menu_id']);
                        $unitPrice = $resolveUnitPrice($item, $menu);
                        $total += $unitPrice * $item['quantity'];
                    }

                    $order = Order::create([
                        'cashier_id' => $data['cashier_id'],
                        'customer_name' => $data['customer_name'],
                        'total_amount' => $total,
                        'payment_method' => PaymentMethod::PayLater,
                        'status' => OrderStatus::Unpaid,
                        'order_code' => Order::generateCode(),
                    ]);

                    $items = [];
                    foreach ($data['items'] as $item) {
                        $menu = $menus->get($item['menu_id']);
                        $unitPrice = $resolveUnitPrice($item, $menu);
                        $items[] = [
                            'order_id' => $order->id,
                            'menu_id' => $item['menu_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $unitPrice,
                            'subtotal' => $unitPrice * $item['quantity'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    \App\Models\OrderItem::insert($items);

                    if (($data['paid_amount'] ?? 0) > 0) {
                        $order->orderPayments()->create([
                            'amount' => $data['paid_amount'],
                            'payment_date' => now(),
                            'payment_method' => 'cash',
                        ]);
                    }
                }),
        ];
    }
}
