<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'inventory'])
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('sku', 'like', '%'.$request->search.'%')
                ->orWhere('barcode', 'like', '%'.$request->search.'%')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->boolean('status')))
            ->orderBy($request->get('sort_by', 'name'), $request->get('sort_dir', 'asc'))
            ->paginate(20)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'categories' => Category::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['quantity', 'reorder_level', 'location', 'image']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        Inventory::create([
            'product_id' => $product->id,
            'quantity' => $request->integer('quantity', 0),
            'reorder_level' => $request->integer('reorder_level', 5),
            'location' => $request->location,
        ]);

        AuditLog::record('product_created', $product, ['sku' => $product->sku]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product->load('category', 'inventory'),
            'categories' => Category::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except(['quantity', 'reorder_level', 'location', 'image']);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        $product->inventory()->updateOrCreate([], [
            'quantity' => $request->integer('quantity', $product->inventory?->quantity ?? 0),
            'reorder_level' => $request->integer('reorder_level', $product->inventory?->reorder_level ?? 5),
            'location' => $request->location ?? $product->inventory?->location,
        ]);

        AuditLog::record('product_updated', $product, ['sku' => $product->sku]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->inventory?->delete();
        $product->delete();

        AuditLog::record('product_deleted', $product);

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function byBarcode(string $barcode): JsonResponse
    {
        $product = Product::with(['category', 'inventory'])
            ->where('barcode', $barcode)
            ->where('status', true)
            ->first();

        if ($product === null) {
            return response()->json(['message' => 'No product found for this barcode.'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'selling_price' => (float) $product->selling_price,
                'stock' => $product->stockQuantity(),
                'tax_rate' => (float) $product->tax_rate,
            ],
        ]);
    }
}