<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $menu_id,
        public readonly float $stock,
        public readonly string $unit,
        public readonly string $menu_name,
        public readonly mixed $timestamp,
    ) {}

    /**
     * Broadcast on a public channel — no auth needed for stock updates.
     * All kasir/admin tabs listen on this single channel.
     */
    public function broadcastOn(): array
    {
        return [new Channel('stock')];
    }

    public function broadcastAs(): string
    {
        return 'StockUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'menu_id' => $this->menu_id,
            'stock' => $this->stock,
            'unit' => $this->unit,
            'menu_name' => $this->menu_name,
            'timestamp' => $this->timestamp,
        ];
    }
}
