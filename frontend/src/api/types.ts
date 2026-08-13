export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string | null;
  role_name: string;
  roles?: string[];
  permissions?: string[];
  status: boolean;
  active_shift?: number | null;
  created_at?: string;
}

export interface Shift {
  id: number;
  user?: User;
  opening_balance: number;
  closing_balance?: number | null;
  expected_balance?: number | null;
  cash_sales: number;
  card_sales: number;
  mobile_sales: number;
  qr_sales: number;
  total_sales: number;
  sales_count: number;
  opened_at?: string;
  closed_at?: string | null;
  status: 'open' | 'closed';
  notes?: string | null;
  variance?: number | null;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  description?: string | null;
  status: boolean;
  products_count?: number;
}

export interface Inventory {
  id: number;
  product_id: number;
  quantity: number;
  reorder_level: number;
  location?: string | null;
  is_low_stock: boolean;
}

export interface Product {
  id: number;
  category_id: number;
  category?: Category;
  barcode?: string | null;
  sku: string;
  name: string;
  description?: string | null;
  cost_price: number;
  selling_price: number;
  tax_rate: number;
  image?: string | null;
  status: boolean;
  inventory?: Inventory;
  stock: number;
  is_out_of_stock: boolean;
  is_low_stock: boolean;
}

export interface Customer {
  id: number;
  customer_code: string;
  full_name: string;
  phone?: string | null;
  email?: string | null;
  address?: string | null;
  loyalty_points: number;
  total_spent?: number;
  sales?: Sale[];
}

export interface SaleItem {
  id: number;
  product_id: number;
  product?: Product;
  quantity: number;
  unit_price: number;
  discount: number;
  tax_rate: number;
  tax_amount: number;
  total: number;
  notes?: string | null;
}

export interface Payment {
  id: number;
  method: 'cash' | 'card' | 'mobile' | 'qr';
  amount: number;
  reference?: string | null;
  status: string;
  paid_at?: string;
}

export interface Receipt {
  id: number;
  receipt_number: string;
  sale_id: number;
  pdf_url?: string | null;
  emailed: boolean;
}

export interface Sale {
  id: number;
  invoice_number: string;
  customer?: Customer | null;
  customer_id?: number | null;
  user?: User;
  user_id: number;
  shift_id?: number | null;
  items?: SaleItem[];
  payments?: Payment[];
  receipt?: Receipt | null;
  subtotal: number;
  discount: number;
  tax: number;
  total: number;
  amount_paid: number;
  change_due: number;
  payment_status: 'pending' | 'completed' | 'cancelled' | 'refunded';
  payment_method?: string | null;
  notes?: string | null;
  sold_at?: string;
}

export interface InventoryMovement {
  id: number;
  product?: Product;
  product_id: number;
  type: 'in' | 'out' | 'adjustment' | 'sale';
  quantity: number;
  reference_type?: string | null;
  reference_id?: number | null;
  notes?: string | null;
  created_at?: string;
}

export interface LoyaltyTransaction {
  id: number;
  sale_id?: number | null;
  points: number;
  type: 'earned' | 'redeemed' | 'adjusted';
  notes?: string | null;
  created_at?: string;
}

export interface AuditLog {
  id: number;
  user?: User;
  event: string;
  details?: Record<string, unknown>;
  ip_address?: string;
  created_at?: string;
}

export interface RolePermission {
  name: string;
  permissions: string[];
}

export interface StoreSettings {
  store: {
    name: string;
    address?: string;
    phone?: string;
    tin?: string;
    footer?: string;
  };
  currency: string;
  loyalty: {
    points_per_currency: number;
  };
}

export interface DashboardData {
  today_revenue: number;
  today_orders: number;
  today_profit: number;
  open_shifts: number;
  low_stock_items: number;
  total_revenue: number;
  payment_mix: Array<{
    payment_method: string | null;
    count: number;
    amount: number;
  }>;
  hourly_sales: Array<{
    hour: number;
    revenue: number;
    orders: number;
  }>;
  sales_trend: Array<{
    date: string;
    revenue: number;
  }>;
  top_products: Array<{
    name: string;
    total_qty: number;
    revenue: number;
  }>;
}

export interface Paginated<T> {
  data: T[];
  meta: {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
  };
}

export interface ApiResponse<T = unknown> {
  message?: string;
  token?: string;
  user?: User;
  data?: T;
}