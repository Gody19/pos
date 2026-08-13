import { useCallback, useEffect, useMemo, useState } from 'react';
import { api } from '../api/client';
import type { Product } from '../api/types';
import { Card } from '../components/ui/Card';
import { Badge } from '../components/ui/Badge';
import { Spinner, EmptyState } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';
import { formatMoney } from '../utils/format';

export function InventoryReportPage() {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<{ data: Product[] }>('/products', { params: { per_page: 100, sort_by: 'name' } })
      .then((res) => setProducts(res.data.data))
      .finally(() => setLoading(false));
  }, []);

  useEffect(load, [load]);

  const stats = useMemo(() => {
    const low = products.filter((p) => p.is_low_stock && !p.is_out_of_stock);
    const out = products.filter((p) => p.is_out_of_stock);
    const stockValue = products.reduce((sum, p) => sum + p.cost_price * p.stock, 0);
    const retailValue = products.reduce((sum, p) => sum + p.selling_price * p.stock, 0);
    return { low: low.length, out: out.length, stockValue, retailValue };
  }, [products]);

  const problemProducts = useMemo(
    () => products.filter((p) => p.is_low_stock || p.is_out_of_stock),
    [products],
  );

  if (loading) return <Spinner />;

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Inventory Report</h1>
        <p className="text-sm text-gray-500">Stock levels and valuation.</p>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card>
          <div className="p-5">
            <p className="text-sm text-gray-500">Total Items</p>
            <p className="text-2xl font-bold text-gray-900">{products.length}</p>
          </div>
        </Card>
        <Card>
          <div className="p-5">
            <p className="text-sm text-gray-500">Stock Value (cost)</p>
            <p className="text-2xl font-bold text-gray-900">{formatMoney(stats.stockValue)}</p>
          </div>
        </Card>
        <Card>
          <div className="p-5">
            <p className="text-sm text-gray-500">Retail Value</p>
            <p className="text-2xl font-bold text-indigo-600">{formatMoney(stats.retailValue)}</p>
          </div>
        </Card>
        <Card>
          <div className="p-5">
            <p className="text-sm text-gray-500">Low / Out of Stock</p>
            <p className="text-2xl font-bold text-amber-600">{stats.low} / {stats.out}</p>
          </div>
        </Card>
      </div>

      <Card>
        <div className="border-b border-gray-200 px-5 py-4">
          <h2 className="text-base font-semibold text-gray-900">Low Stock & Out of Stock Products</h2>
        </div>
        {problemProducts.length === 0 ? (
          <EmptyState title="All stocked up" description="No products are low on stock." />
        ) : (
          <DataTable<Product>
            columns={[
              { key: 'name', header: 'Product', render: (p) => <span className="font-medium text-gray-900">{p.name}</span> },
              { key: 'sku', header: 'SKU', render: (p) => p.sku },
              {
                key: 'stock',
                header: 'Stock',
                render: (p) => <Badge color={p.is_out_of_stock ? 'red' : 'yellow'}>{p.stock} left</Badge>,
              },
              { key: 'reorder', header: 'Reorder Level', render: (p) => p.inventory?.reorder_level ?? '-' },
              { key: 'location', header: 'Location', render: (p) => p.inventory?.location ?? '-' },
            ]}
            rows={problemProducts}
            loading={false}
          />
        )}
      </Card>
    </div>
  );
}