<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Http\Resources\ProductResource;
use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryController extends Controller
{
    public function movements(Request $request): AnonymousResourceCollection
    {
        $movements = InventoryMovement::query()
            ->with('product')
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->product_id))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return InventoryMovementResource::collection($movements);
    }

    public function adjust(AdjustStockRequest $request): JsonResponse
    {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $request->product_id],
            ['quantity' => 0, 'reorder_level' => 5]
        );

        $quantity = $request->integer('quantity');

        if ($request->type === 'in') {
            $inventory->increment('quantity', $quantity);
        } elseif ($request->type === 'out') {
            if ($inventory->quantity < $quantity) {
                return response()->json(['message' => 'Not enough stock to remove.'], 422);
            }
            $inventory->decrement('quantity', $quantity);
        } else {
            $inventory->update(['quantity' => max(0, $inventory->quantity + $quantity)]);
        }

        InventoryMovement::create([
            'product_id' => $request->product_id,
            'type' => $request->type,
            'quantity' => $quantity,
            'reference_type' => 'manual',
            'notes' => $request->reason ?? 'Manual stock adjustment',
        ]);

        AuditLog::record('inventory_adjusted', $inventory, [
            'type' => $request->type,
            'quantity' => $quantity,
            'reason' => $request->reason,
        ]);

        $product = $inventory->product->load('category', 'inventory');

        return response()->json([
            'message' => 'Stock adjusted successfully.',
            'data' => new ProductResource($product),
        ]);
    }
}
