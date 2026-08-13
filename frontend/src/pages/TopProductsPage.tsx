import { useCallback, useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { api } from '../api/client';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { EmptyState } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';
import { formatMoney } from '../utils/format';

interface TopProduct {
  id: number;
  name: string;
  sku: string;
  total_qty: number;
  revenue: number;
}

const dateToInput = (d: Date) => d.toISOString().slice(0, 10);

export function TopProductsPage() {
  const [params] = useSearchParams();
  const defaultFrom = params.get('from') ?? dateToInput(new Date(new Date().getFullYear(), new Date().getMonth(), 1));
  const defaultTo = params.get('to') ?? dateToInput(new Date());

  const [from, setFrom] = useState(defaultFrom);
  const [to, setTo] = useState(defaultTo);
  const [rows, setRows] = useState<TopProduct[]>([]);
  const [loading, setLoading] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<{ data: TopProduct[] }>('/reports/top-products', { params: { from, to, limit: 20 } })
      .then((res) => setRows(res.data.data))
      .finally(() => setLoading(false));
  }, [from, to]);

  useEffect(load, [load]);

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Top Products</h1>
          <p className="text-sm text-gray-500">Best selling products by quantity.</p>
        </div>
        <div className="flex items-end gap-2">
          <div>
            <label className="block text-xs font-medium text-gray-500">From</label>
            <Input type="date" value={from} onChange={(e) => setFrom(e.target.value)} />
          </div>
          <div>
            <label className="block text-xs font-medium text-gray-500">To</label>
            <Input type="date" value={to} onChange={(e) => setTo(e.target.value)} />
          </div>
          <Button onClick={load}>Apply</Button>
        </div>
      </div>

      <Card>
        {rows.length === 0 ? (
          <EmptyState title="No data" description="No product sales in this period." />
        ) : (
          <DataTable<TopProduct>
            columns={[
              {
                key: 'rank',
                header: '#',
                render: (_r, i) => (
                  <span className="inline-flex size-7 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-600">
                    {i + 1}
                  </span>
                ),
              },
              { key: 'name', header: 'Product', render: (p) => <span className="font-medium text-gray-900">{p.name}</span> },
              { key: 'sku', header: 'SKU', render: (p) => p.sku },
              { key: 'total_qty', header: 'Qty Sold', render: (p) => <span className="font-semibold">{p.total_qty}</span> },
              { key: 'revenue', header: 'Revenue', render: (p) => <span className="font-semibold text-indigo-600">{formatMoney(p.revenue)}</span> },
            ]}
            rows={rows}
            loading={loading}
          />
        )}
      </Card>
    </div>
  );
}