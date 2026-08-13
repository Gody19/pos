<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::query()
            ->with(['category', 'inventory'])
            ->when($request->filled('search'), fn ($q) => $q
                ->where(fn ($q) => $q
                    ->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%")
                    ->orWhere('barcode', 'like', "%{$request->search}%")))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->boolean('low_stock'), fn ($q) => $q->whereHas('inventory', fn ($i) => $i->whereColumn('quantity', '<=', 'reorder_level')))
            ->when($request->boolean('out_of_stock'), fn ($q) => $q->whereHas('inventory', fn ($i) => $i->where('quantity', '<=', 0)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->boolean('status')))
            ->orderBy($request->get('sort_by', 'name'), $request->get('sort_dir', 'asc'))
            ->paginate($request->integer('per_page', 24));

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResource
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

        return new ProductResource($product->load('category', 'inventory'));
    }

    public function show(Product $product): JsonResource
    {
        return new ProductResource($product->load('category', 'inventory'));
    }

    public function update(StoreProductRequest $request, Product $product): JsonResource
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

        return new ProductResource($product->load('category', 'inventory'));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->inventory?->delete();
        $product->delete();

        AuditLog::record('product_deleted', $product);

        return response()->json(['message' => 'Product deleted.']);
    }

    public function byBarcode(string $barcode): JsonResponse
    {
        $product = Product::with(['category', 'inventory'])
            ->where('barcode', $barcode)
            ->where('status', true)
            ->first();

        if ($product === null) {
            return response()->json(['message' => 'Product not found for this barcode.'], 404);
        }

        return response()->json(['data' => new ProductResource($product)]);
    }
}
