import { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { Trash2, Calculator, UserPlus, Percent, ShoppingCart, RotateCcw } from 'lucide-react';
import { api } from '../api/client';
import type { Category, Product, Sale } from '../api/types';
import { usePOSStore } from '../store/posStore';
import { useAuthStore } from '../store/authStore';
import { useKeyboard } from '../hooks/useKeyboard';
import { ScannerInput } from '../components/pos/ScannerInput';
import { ProductGrid } from '../components/pos/ProductGrid';
import { CartPanel } from '../components/pos/CartPanel';
import { CustomerSelect } from '../components/pos/CustomerSelect';
import { PaymentModal, type PaymentLine } from '../components/pos/PaymentModal';
import { Button } from '../components/ui/Button';
import { Modal } from '../components/ui/Modal';
import { Input, Textarea } from '../components/ui/Input';
import { formatMoney } from '../utils/format';
import { Link } from 'react-router-dom';

export function POSPage() {
  const navigate = useNavigate();
  const { user } = useAuthStore();
  const {
    cart,
    customer,
    cartDiscount,
    notes,
    addItem,
    removeItem,
    updateQty,
    updateItemDiscount,
    setCartDiscount,
    setNotes,
    setCustomer,
    clearCart,
  } = usePOSStore();

  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [selectedCategory, setSelectedCategory] = useState<number | 'all'>('all');
  const [search, setSearch] = useState('');
  const [loadingProducts, setLoadingProducts] = useState(false);
  const [paymentOpen, setPaymentOpen] = useState(false);
  const [checkoutLoading, setCheckoutLoading] = useState(false);
  const [searchModalOpen, setSearchModalOpen] = useState(false);
  const [cartDiscountModal, setCartDiscountModal] = useState(false);
  const [discountInput, setDiscountInput] = useState('0');

  const loadCategories = useCallback(async () => {
    const { data } = await api.get<{ data: Category[] }>('/categories', { params: { per_page: 100 } });
    setCategories(data.data);
  }, []);

  const loadProducts = useCallback(
    async (query = '', category: number | 'all' = selectedCategory) => {
      setLoadingProducts(true);
      try {
        const params: Record<string, string | number> = { per_page: 48, sort_by: 'name' };
        if (query) params.search = query;
        if (category !== 'all') params.category_id = category;

        const { data } = await api.get<{ data: Product[] }>('/products', { params });
        setProducts(data.data);
      } finally {
        setLoadingProducts(false);
      }
    },
    [selectedCategory],
  );

  useEffect(() => {
    loadCategories();
    loadProducts();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleBarcode = async (barcode: string) => {
    try {
      const { data } = await api.get<{ data: Product }>(`/products/barcode/${encodeURIComponent(barcode)}`);
      addItem(data.data);
      toast.success(`${data.data.name} added to cart.`);
    } catch {
      toast.error('Product not found. Try a manual search.', { id: 'barcode' });
    }
  };

  const handleSearchModal = () => {
    setSearchModalOpen(true);
  };

  const submitSearch = (e: React.FormEvent) => {
    e.preventDefault();
    loadProducts(search);
    setSearchModalOpen(false);
  };

  const subtotal = cart.reduce((sum, item) => sum + item.line_total, 0);
  const tax = cart.reduce((sum, item) => sum + item.tax_amount, 0);
  const total = Math.round((subtotal - cartDiscount + tax) * 100) / 100;

  const newSale = () => {
    clearCart();
    setSearch('');
    loadProducts('', 'all');
    setSelectedCategory('all');
  };

  const handlePayment = async (payments: PaymentLine[]) => {
    setCheckoutLoading(true);
    try {
      const { data } = await api.post<{ data: Sale }>('/sales', {
        items: cart.map((item) => ({
          product_id: item.product.id,
          quantity: item.quantity,
          discount: item.discount,
          notes: item.notes,
        })),
        customer_id: customer?.id ?? null,
        discount: cartDiscount,
        notes,
        payments: payments.map((p) => ({ method: p.method, amount: p.amount, reference: p.reference })),
      });

      const sale = data.data;
      setPaymentOpen(false);
      clearCart();
      toast.success(`Sale ${sale.invoice_number} completed!`);

      if (user?.active_shift) {
        const { data: me } = await api.get('/me');
        useAuthStore.getState().setAuth(localStorage.getItem('pos_token') ?? '', me.data);
      }

      navigate(`/receipt/${sale.id}`);
    } catch {
      // toast handled by interceptor
    } finally {
      setCheckoutLoading(false);
    }
  };

  useKeyboard({
    F1: () => newSale(),
    F2: () => handleSearchModal(),
    F4: () => navigate('/customers'),
    F8: () => {
      if (cart.length > 0) setPaymentOpen(true);
    },
    Escape: () => {
      if (cart.length > 0 && window.confirm('Cancel current sale?')) newSale();
    },
  });

  return (
    <div className="flex h-[calc(100vh-3.5rem)] flex-col gap-4 lg:h-[calc(100vh-3rem)]">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2 text-xs text-gray-500">
          <span className="rounded bg-gray-100 px-2 py-1">F1 New Sale</span>
          <span className="rounded bg-gray-100 px-2 py-1">F2 Search</span>
          <span className="rounded bg-gray-100 px-2 py-1">F4 Customer</span>
          <span className="rounded bg-gray-100 px-2 py-1">F8 Pay</span>
          <span className="rounded bg-gray-100 px-2 py-1">ESC Cancel</span>
        </div>
        <Button variant="ghost" size="sm" onClick={newSale}>
          <RotateCcw className="size-4" /> New Sale
        </Button>
      </div>

      <div className="grid flex-1 grid-cols-1 gap-4 overflow-hidden lg:grid-cols-[1fr_400px]">
        {/* Left: products */}
        <div className="flex min-h-0 flex-col rounded-xl bg-gray-50 p-3 ring-1 ring-gray-200">
          <div className="mb-3">
            <ScannerInput onScan={handleBarcode} onSearch={handleSearchModal} />
          </div>
          <ProductGrid
            products={products}
            categories={categories}
            selectedCategory={selectedCategory}
            onSelectCategory={(id) => {
              setSelectedCategory(id);
              loadProducts(search, id);
            }}
            onAdd={addItem}
            loading={loadingProducts}
          />
        </div>

        {/* Right: cart */}
        <div className="flex min-h-0 flex-col rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
          <div className="flex items-center justify-between border-b border-gray-200 px-4 py-3">
            <h2 className="flex items-center gap-2 text-base font-semibold text-gray-900">
              <ShoppingCart className="size-5 text-indigo-600" /> Current Sale
            </h2>
            <span className="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{cart.length} items</span>
          </div>

          <div className="border-b border-gray-200 px-3 py-2">
            <CustomerSelect
              customer={customer}
              onSelect={setCustomer}
              onClear={() => setCustomer(null)}
            />
          </div>

          <div className="flex-1 overflow-y-auto">
            <CartPanel
              items={cart}
              onInc={(id) => {
                const item = cart.find((i) => i.product.id === id);
                if (item) updateQty(id, item.quantity + 1);
              }}
              onDec={(id) => {
                const item = cart.find((i) => i.product.id === id);
                if (item) updateQty(id, item.quantity - 1);
              }}
              onRemove={removeItem}
              onDiscount={updateItemDiscount}
            />
          </div>

          <div className="border-t border-gray-200 px-4 py-3">
            <div className="space-y-1.5 text-sm">
              <div className="flex justify-between text-gray-500">
                <span>Subtotal</span>
                <span>{formatMoney(subtotal)}</span>
              </div>
              <div className="flex items-center justify-between text-gray-500">
                <button
                  onClick={() => {
                    setDiscountInput(String(cartDiscount || 0));
                    setCartDiscountModal(true);
                  }}
                  className="inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-500"
                >
                  <Percent className="size-3.5" /> Discount
                </button>
                <span className="text-red-500">-{formatMoney(cartDiscount)}</span>
              </div>
              <div className="flex justify-between text-gray-500">
                <span>Tax</span>
                <span>{formatMoney(tax)}</span>
              </div>
              <div className="flex items-center justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900">
                <span>Total</span>
                <span className="text-xl text-indigo-600">{formatMoney(total)}</span>
              </div>
            </div>

            <Button
              size="lg"
              className="mt-3 w-full"
              disabled={cart.length === 0}
              onClick={() => setPaymentOpen(true)}
            >
              <Calculator className="size-5" /> Charge {formatMoney(total)}
            </Button>
          </div>
        </div>
      </div>

      {/* Search modal */}
      <Modal open={searchModalOpen} onClose={() => setSearchModalOpen(false)} title="Search Products (F2)">
        <form onSubmit={submitSearch} className="space-y-3">
          <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search by name, SKU, or barcode..." autoFocus />
          <div className="flex justify-end gap-2">
            <Button variant="secondary" onClick={() => setSearchModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit">Search</Button>
          </div>
        </form>
      </Modal>

      {/* Cart discount modal */}
      <Modal
        open={cartDiscountModal}
        onClose={() => setCartDiscountModal(false)}
        title="Apply Cart Discount"
        footer={
          <>
            <Button
              variant="secondary"
              onClick={() => {
                setCartDiscount(0);
                setCartDiscountModal(false);
              }}
            >
              Remove
            </Button>
            <Button
              onClick={() => {
                setCartDiscount(Number(discountInput) || 0);
                setCartDiscountModal(false);
              }}
            >
              Apply
            </Button>
          </>
        }
      >
        <Input type="number" min="0" max={subtotal} step="0.01" value={discountInput} onChange={(e) => setDiscountInput(e.target.value)} />
        <p className="mt-2 text-xs text-gray-500">Maximum discount: {formatMoney(subtotal)}</p>
        <div className="mt-3">
          <label className="block text-sm font-medium text-gray-700">Sale notes</label>
          <Textarea rows={2} className="mt-1" value={notes ?? ''} onChange={(e) => setNotes(e.target.value)} />
        </div>
      </Modal>

      <PaymentModal
        open={paymentOpen}
        onClose={() => setPaymentOpen(false)}
        total={total}
        onComplete={handlePayment}
        loading={checkoutLoading}
      />
    </div>
  );
}