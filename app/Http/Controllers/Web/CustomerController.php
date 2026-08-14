<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->withSum(['sales' => fn ($q) => $q->where('payment_status', 'completed')], 'total')
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q
                ->where('full_name', 'like', '%'.$request->search.'%')
                ->orWhere('phone', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%')
                ->orWhere('customer_code', 'like', '%'.$request->search.'%')))
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', ['customers' => $customers]);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create([
            ...$request->validated(),
            'customer_code' => $this->nextCustomerCode(),
        ]);

        AuditLog::record('customer_created', $customer);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'sales' => fn ($q) => $q->with(['items.product'])->latest('sold_at')->limit(20),
            'loyaltyTransactions' => fn ($q) => $q->latest()->limit(20),
        ]);

        $totalSpent = $customer->sales()
            ->where('payment_status', 'completed')
            ->sum('total');

        return view('customers.show', [
            'customer' => $customer,
            'totalSpent' => round($totalSpent, 2),
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(StoreCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        AuditLog::record('customer_updated', $customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        AuditLog::record('customer_deleted', $customer);

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->get('q', ''));

        $customers = Customer::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('full_name', 'like', '%'.$term.'%')
                ->orWhere('phone', 'like', '%'.$term.'%')
                ->orWhere('email', 'like', '%'.$term.'%')
                ->orWhere('customer_code', 'like', '%'.$term.'%')))
            ->orderBy('full_name')
            ->limit(10)
            ->get();

        return response()->json([
            'html' => view('customers._search_results', ['customers' => $customers])->render(),
        ]);
    }

    public function loyalty(Customer $customer): View
    {
        return view('customers.loyalty', [
            'customer' => $customer,
            'transactions' => $customer->loyaltyTransactions()->with('sale')->latest()->paginate(15)->withQueryString(),
        ]);
    }

    protected function nextCustomerCode(): string
    {
        $latest = Customer::withTrashed()->orderByDesc('id')->value('customer_code');

        $seq = $latest !== null ? ((int) Str::after($latest, 'CUS-') + 1) : 1;

        return 'CUS-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}