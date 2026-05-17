<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired synchronously when a kasir reviews a QRIS payment order.
 *
 * ShouldBroadcastNow ensures zero-delay feedback for QRIS review,
 * so the customer sees the result immediately without queue latency.
 */
class OrderQrisReviewed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $decision,
        public readonly ?string $reason,
    ) {}

    /**
     * Public channel scoped to the specific order.
     * No auth needed — the customer polls or subscribes by order ID.
     */
    public function broadcastOn(): array
    {
        return [new Channel('order.' . $this->order->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'  => $this->order->id,
            'decision'  => $this->decision,
            'reason'    => $this->reason,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
