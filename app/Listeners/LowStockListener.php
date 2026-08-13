<?php

namespace App\Listeners;

use App\Events\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LowStockListener implements ShouldQueue
{
    public int $delay = 0;

    public function handle(LowStockNotification $event): void
    {
        $inventory = $event->inventory->load('product');

        // Real implementations can push to a notification channel (database, mail, push, SMS).
        Log::channel('stack')->warning('Low stock alert', [
            'product_id' => $inventory->product_id,
            'product' => $inventory->product?->name,
            'sku' => $inventory->product?->sku,
            'quantity' => $inventory->quantity,
            'reorder_level' => $inventory->reorder_level,
        ]);
    }
}
