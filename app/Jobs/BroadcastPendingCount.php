<?php

namespace App\Jobs;

use App\Services\OrderBroadcastService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BroadcastPendingCount implements ShouldQueue
{
    use Queueable;

    public int $tries   = 1;
    public int $timeout = 10;

    public function handle(): void
    {
        OrderBroadcastService::broadcastPendingCount();
    }
}
