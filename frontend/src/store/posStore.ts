import { create } from 'zustand';
import type { Customer, Product } from '../api/types';

export interface CartItem {
  product: Product;
  quantity: number;
  discount: number;
  notes?: string;
  line_total: number;
  tax_amount: number;
  total: number;
}

interface POSState {
  cart: CartItem[];
  customer: Customer | null;
  cartDiscount: number;
  notes?: string;
  lastSaleId: number | null;

  addItem: (product: Product) => void;
  removeItem: (productId: number) => void;
  updateQty: (productId: number, quantity: number) => void;
  updateItemDiscount: (productId: number, discount: number) => void;
  updateItemNotes: (productId: number, notes: string) => void;
  setCartDiscount: (discount: number) => void;
  setNotes: (notes: string) => void;
  setCustomer: (customer: Customer | null) => void;
  setLastSaleId: (id: number | null) => void;
  clearCart: () => void;
}

function lineTotals(product: Product, quantity: number, discount: number) {
  const lineNet = product.selling_price - discount;
  const lineTotal = Math.round(lineNet * quantity * 100) / 100;
  const taxAmount = Math.round(lineTotal * (product.tax_rate / 100) * 100) / 100;

  return {
    line_total: lineTotal,
    tax_amount: taxAmount,
    total: Math.round((lineTotal + taxAmount) * 100) / 100,
  };
}

export const usePOSStore = create<POSState>((set) => ({
  cart: [],
  customer: null,
  cartDiscount: 0,
  notes: undefined,
  lastSaleId: null,

  addItem: (product) =>
    set((state) => {
      const existing = state.cart.find((item) => item.product.id === product.id);

      if (existing) {
        const quantity = Math.min(existing.quantity + 1, product.stock || 9999);
        const totals = lineTotals(product, quantity, existing.discount);

        return {
          cart: state.cart.map((item) =>
            item.product.id === product.id ? { ...item, quantity, ...totals } : item,
          ),
        };
      }

      const totals = lineTotals(product, 1, 0);

      return { cart: [...state.cart, { product, quantity: 1, discount: 0, ...totals }] };
    }),

  removeItem: (productId) =>
    set((state) => ({ cart: state.cart.filter((item) => item.product.id !== productId) })),

  updateQty: (productId, quantity) =>
    set((state) => ({
      cart: state.cart.map((item) => {
        if (item.product.id !== productId) return item;

        const qty = Math.max(0, Math.min(quantity, item.product.stock || 9999));

        if (qty === 0) return null as unknown as CartItem;

        return { ...item, quantity: qty, ...lineTotals(item.product, qty, item.discount) };
      }).filter(Boolean) as CartItem[],
    })),

  updateItemDiscount: (productId, discount) =>
    set((state) => ({
      cart: state.cart.map((item) =>
        item.product.id === productId
          ? {
              ...item,
              discount: Math.max(0, Math.min(discount, item.product.selling_price)),
              ...lineTotals(item.product, item.quantity, discount),
            }
          : item,
      ),
    })),

  updateItemNotes: (productId, notes) =>
    set((state) => ({
      cart: state.cart.map((item) => (item.product.id === productId ? { ...item, notes } : item)),
    })),

  setCartDiscount: (discount) => set({ cartDiscount: Math.max(0, discount) }),

  setNotes: (notes) => set({ notes }),

  setCustomer: (customer) => set({ customer }),

  setLastSaleId: (id) => set({ lastSaleId: id }),

  clearCart: () => set({ cart: [], customer: null, cartDiscount: 0, notes: undefined }),
}));