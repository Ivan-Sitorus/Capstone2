<?php

namespace App\Filament\Resources\PiutangResource\Pages;

use App\Filament\Resources\PiutangResource;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListPiutangs extends ListRecords
{
    protected static string $resource = PiutangResource::class;

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
                                ->live(),
                            TextInput::make('quantity')
                                ->label('Jumlah')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->default(1),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->required(),
                    TextInput::make('paid_amount')
                        ->label('Sudah Dibayar')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->prefix('Rp'),
                ])
                ->action(function (array $data) {
                    $menuIds = array_column($data['items'], 'menu_id');
                    $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');
                    $total = 0;

                    foreach ($data['items'] as $item) {
                        $menu = $menus->get($item['menu_id']);
                        $total += ($menu->price ?? 0) * $item['quantity'];
                    }

                    $order = Order::create([
                        'cashier_id' => $data['cashier_id'],
                        'customer_name' => $data['customer_name'],
                        'total_amount' => $total,
                        'payment_method' => 'piutang',
                        'status' => 'belum_lunas',
                        'order_code' => Order::generateCode(),
                    ]);

                    $items = [];
                    foreach ($data['items'] as $item) {
                        $menu = $menus->get($item['menu_id']);
                        $items[] = [
                            'order_id' => $order->id,
                            'menu_id' => $item['menu_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $menu->price ?? 0,
                            'subtotal' => ($menu->price ?? 0) * $item['quantity'],
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
