import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { Plus, Pencil, Search, Package } from 'lucide-react';
import { api } from '../api/client';
import type { Paginated, Product } from '../api/types';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Badge, statusColor } from '../components/ui/Badge';
import { Pagination } from '../components/ui/Pagination';
import { EmptyState, Spinner } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';
import { Input } from '../components/ui/Input';
import { formatMoney } from '../utils/format';
import { useAuthStore } from '../store/authStore';

export function ProductsPage() {
  const { user } = useAuthStore();
  const [data, setData] = useState<Paginated<Product> | null>(null);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<Paginated<Product>>('/products', {
        params: { per_page: 15, page, search: search || undefined, sort_by: 'name' },
      })
      .then((res) => setData(res.data))
      .finally(() => setLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [page, search]);

  useEffect(() => {
    const t = setTimeout(load, 300);
    return () => clearTimeout(t);
  }, [load]);

  const canManage = ['admin', 'manager', 'inventory_clerk'].includes(user?.role_name ?? '');

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Products</h1>
          <p className="text-sm text-gray-500">Manage your product catalog and pricing.</p>
        </div>
        {canManage && (
          <Link to="/products/new">
            <Button>
              <Plus className="size-4" /> New Product
            </Button>
          </Link>
        )}
      </div>

      <Card>
        <div className="border-b border-gray-200 p-4">
          <div className="relative max-w-sm">
            <Search className="pointer-events-none absolute left-3 top-2.5 size-5 text-gray-400" />
            <Input
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(1);
              }}
              placeholder="Search products..."
              className="pl-10"
            />
          </div>
        </div>

        {loading && !data ? (
          <Spinner />
        ) : (
          <DataTable<Product>
            columns={[
              {
                key: 'name',
                header: 'Product',
                render: (p) => (
                  <div className="flex items-center gap-3">
                    <div className="flex size-9 items-center justify-center rounded-lg bg-gray-100 text-sm font-bold text-gray-500">
                      {p.image ? <img src={p.image} className="size-9 rounded-lg object-cover" alt="" /> : p.name.charAt(0)}
                    </div>
                    <div>
                      <p className="font-medium text-gray-900">{p.name}</p>
                      <p className="text-xs text-gray-500">SKU: {p.sku}</p>
                    </div>
                  </div>
                ),
              },
              {
                key: 'category',
                header: 'Category',
                render: (p) => p.category?.name ?? '-',
              },
              {
                key: 'selling_price',
                header: 'Price',
                render: (p) => <span className="font-medium">{formatMoney(p.selling_price)}</span>,
              },
              {
                key: 'cost_price',
                header: 'Cost',
                render: (p) => formatMoney(p.cost_price),
              },
              {
                key: 'stock',
                header: 'Stock',
                render: (p) => (
                  <Badge color={p.is_out_of_stock ? 'red' : p.is_low_stock ? 'yellow' : 'green'}>
                    {p.stock} {p.is_low_stock && '(low)'}
                  </Badge>
                ),
              },
              {
                key: 'status',
                header: 'Status',
                render: (p) => <Badge color={statusColor(p.status ? 'active' : 'closed')}>{p.status ? 'Active' : 'Inactive'}</Badge>,
              },
              ...(canManage
                ? [
                    {
                      key: 'actions',
                      header: '',
                      render: (p: Product) => (
                        <Link
                          to={`/products/${p.id}/edit`}
                          className="inline-flex items-center gap-1 rounded-md px-2 py-1 text-sm font-medium text-indigo-600 hover:bg-indigo-50"
                        >
                          <Pencil className="size-4" /> Edit
                        </Link>
                      ),
                    },
                  ]
                : []),
            ]}
            rows={data?.data ?? []}
            loading={loading}
          />
        )}

        {data && data.data.length > 0 && (
          <Pagination page={page} lastPage={data.meta.last_page} total={data.meta.total} onChange={setPage} />
        )}
      </Card>
    </div>
  );
}