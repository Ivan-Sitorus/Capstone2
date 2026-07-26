<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * Computed items with unit_price and subtotal calculated in mutateFormDataBeforeCreate.
     * Stored here so afterCreate can use them without recomputing.
     */
    private array $computedItems = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;
        $menuIds = array_column($data['items'], 'menu_id');
        $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

        foreach ($data['items'] as $item) {
            $menu = $menus->get($item['menu_id']);
            $unitPrice = $menu ? (int) $menu->price : 0;
            $subtotal = $unitPrice * (int) $item['quantity'];
            $total += $subtotal;
            $this->computedItems[] = [
                'menu_id' => $item['menu_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];
        }

        unset($data['items']);

        $data['total_amount'] = $total;
        $data['status'] = ($data['payment_method'] ?? 'piutang') === 'piutang' ? 'belum_lunas' : 'pending';
        $data['order_code'] = Order::generateCode();

        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;

        $items = [];
        foreach ($this->computedItems as $item) {
            $items[] = [
                'order_id' => $order->id,
                'menu_id' => $item['menu_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        OrderItem::insert($items);
    }

    public function getFormSchema(): array
    {
        return [
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
                        ->reactive()
                        ->afterStateUpdated(fn ($state, Set $set) => $set('unit_price', Menu::find($state)?->price ?? 0)),
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
            Select::make('payment_method')
                ->label('Metode Pembayaran')
                ->options([
                    'cash' => 'Tunai',
                    'qris' => 'QRIS',
                    'piutang' => 'Piutang',
                ])
                ->default('piutang')
                ->required(),
        ];
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Buat Pesanan');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
