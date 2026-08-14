<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'inventory'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('sku', 'like', '%'.$request->search.'%')))
            ->when($request->filled('filter'), function ($q) use ($request) {
                if ($request->filter === 'low') {
                    $q->whereHas('inventory', fn ($i) => $i->whereColumn('quantity', '<=', 'reorder_level'));
                } elseif ($request->filter === 'out') {
                    $q->whereHas('inventory', fn ($i) => $i->where('quantity', '<=', 0));
                }
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.index', [
            'products' => $products,
            'lowStockCount' => Inventory::whereColumn('quantity', '<=', 'reorder_level')->count(),
            'outOfStockCount' => Inventory::where('quantity', '<=', 0)->count(),
        ]);
    }

    public function movements(Request $request): View
    {
        $movements = InventoryMovement::query()
            ->with('product')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('inventory.movements', ['movements' => $movements]);
    }

    public function adjust(AdjustStockRequest $request): RedirectResponse
    {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $request->integer('product_id')],
            ['quantity' => 0, 'reorder_level' => 5]
        );

        $quantity = $request->integer('quantity');
        $type = $request->type;

        if ($type === 'in') {
            $inventory->increment('quantity', $quantity);
        } elseif ($type === 'out') {
            if ($inventory->quantity < $quantity) {
                return back()->withErrors(['quantity' => 'Not enough stock to remove.'])->withInput();
            }
            $inventory->decrement('quantity', $quantity);
        } else {
            $inventory->update(['quantity' => max(0, $quantity)]);
        }

        InventoryMovement::create([
            'product_id' => $request->integer('product_id'),
            'type' => $type,
            'quantity' => $quantity,
            'reference_type' => 'manual',
            'notes' => $request->reason ?? 'Manual stock adjustment',
        ]);

        AuditLog::record('inventory_adjusted', $inventory, [
            'type' => $type,
            'quantity' => $quantity,
            'reason' => $request->reason,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Stock adjusted successfully.');
    }
}