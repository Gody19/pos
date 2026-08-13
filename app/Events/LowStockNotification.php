<?php

namespace App\Events;

use App\Models\Inventory;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Inventory $inventory) {}
}
