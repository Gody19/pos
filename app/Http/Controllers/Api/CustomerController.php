<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\LoyaltyTransactionResource;
use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = Customer::query()
            ->withSum(['sales' => fn ($q) => $q->where('payment_status', 'completed')], 'total')
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q
                ->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('customer_code', 'like', "%{$request->search}%")))
            ->orderBy('full_name')
            ->paginate($request->integer('per_page', 20));

        return CustomerResource::collection($customers);
    }

    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->get('q', ''));

        $customers = Customer::query()
            ->withSum(['sales' => fn ($q) => $q->where('payment_status', 'completed')], 'total')
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('full_name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('customer_code', 'like', "%{$term}%")))
            ->orderBy('full_name')
            ->limit(min($request->integer('limit', 10), 50))
            ->get();

        return response()->json(['data' => CustomerResource::collection($customers)]);
    }

    public function store(StoreCustomerRequest $request): JsonResource
    {
        $customer = Customer::create([
            ...$request->validated(),
            'customer_code' => $this->nextCustomerCode(),
        ]);

        AuditLog::record('customer_created', $customer);

        return new CustomerResource($customer);
    }

    public function show(Customer $customer): JsonResource
    {
        $customer->load(['sales' => fn ($q) => $q->latest('sold_at')->limit(20)]);

        return new CustomerResource($customer->loadSum(['sales' => fn ($q) => $q->where('payment_status', 'completed')], 'total'));
    }

    public function update(StoreCustomerRequest $request, Customer $customer): JsonResource
    {
        $customer->update($request->validated());

        AuditLog::record('customer_updated', $customer);

        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        AuditLog::record('customer_deleted', $customer);

        return response()->json(['message' => 'Customer deleted.']);
    }

    public function loyalty(Customer $customer): JsonResponse
    {
        $transactions = $customer->loyaltyTransactions()->latest()->paginate(15);

        return response()->json([
            'data' => LoyaltyTransactionResource::collection($transactions),
            'meta' => [
                'balance' => $customer->loyalty_points,
            ],
        ]);
    }

    protected function nextCustomerCode(): string
    {
        $latest = Customer::withTrashed()->orderByDesc('id')->value('customer_code');

        $seq = $latest !== null ? ((int) Str::after($latest, 'CUS-') + 1) : 1;

        return 'CUS-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}
