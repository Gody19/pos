<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\NoActiveShiftException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PaymentMismatchException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PosCheckoutRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PosController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected SaleService $saleService,
    ) {}

    public function index(): View
    {
        $categories = Category::where('status', true)->orderBy('name')->get();

        $products = $this->queryProducts(request())
            ->limit(60)
            ->get();

        return view('pos.index', [
            'categories' => $categories,
            'products' => $products,
            'cart' => $this->cart->getState(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $products = $this->queryProducts($request)->limit(60)->get();

        return response()->json([
            'html' => view('pos._products', ['products' => $products])->render(),
        ]);
    }

    public function cart(): JsonResponse
    {
        return response()->json($this->cart->getState());
    }

    public function addToCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:999'],
        ]);

        try {
            return response()->json($this->cart->add($request->integer('product_id'), $request->integer('quantity', 1)));
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'in:-1,1'],
        ]);

        try {
            return response()->json($this->cart->updateQuantity($request->integer('product_id'), $request->integer('quantity')));
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function removeFromCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        return response()->json($this->cart->remove($request->integer('product_id')));
    }

    public function checkout(PosCheckoutRequest $request): JsonResponse
    {
        if ($this->cart->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        try {
            $data = $request->validated();
            $data['items'] = $this->cart->saleItems();

            $sale = $this->saleService->create(auth()->user(), $data);

            $this->cart->clear();

            return response()->json([
                'message' => 'Sale completed successfully.',
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
            ]);
        } catch (NoActiveShiftException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (OutOfStockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (PaymentMismatchException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    protected function queryProducts(Request $request)
    {
        return Product::query()
            ->with(['category', 'inventory'])
            ->where('status', true)
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('sku', 'like', '%'.$request->search.'%')
                ->orWhere('barcode', 'like', '%'.$request->search.'%')))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->orderBy('name');
    }
}