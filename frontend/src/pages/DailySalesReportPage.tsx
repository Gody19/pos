import { useCallback, useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { api } from '../api/client';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { EmptyState } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';
import { formatMoney } from '../utils/format';

interface DailyRow {
  id: string;
  date: string;
  orders: number;
  revenue: number;
  gross: number;
}

const dateToInput = (d: Date) => d.toISOString().slice(0, 10);

export function DailySalesReportPage() {
  const [params] = useSearchParams();
  const defaultFrom = params.get('from') ?? dateToInput(new Date(new Date().getFullYear(), new Date().getMonth(), 1));
  const defaultTo = params.get('to') ?? dateToInput(new Date());

  const [from, setFrom] = useState(defaultFrom);
  const [to, setTo] = useState(defaultTo);
  const [rows, setRows] = useState<DailyRow[]>([]);
  const [loading, setLoading] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<{ data: DailyRow[] }>('/reports/daily-sales', { params: { from, to } })
      .then((res) =>
        setRows(
          res.data.data.map((r) => ({
            ...r,
            id: String(r.date),
            orders: Number(r.orders),
            revenue: Number(r.revenue),
            gross: Number(r.gross),
          })),
        ),
      )
      .finally(() => setLoading(false));
  }, [from, to]);

  useEffect(load, [load]);

  const totals = useMemo(
    () =>
      rows.reduce(
        (acc, r) => ({ orders: acc.orders + r.orders, revenue: acc.revenue + r.revenue }),
        { orders: 0, revenue: 0 },
      ),
    [rows],
  );

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Daily Sales Report</h1>
          <p className="text-sm text-gray-500">Sales breakdown per day.</p>
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

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <Card>
          <div className="p-5">
            <p className="text-sm text-gray-500">Total Revenue</p>
            <p className="text-2xl font-bold text-gray-900">{formatMoney(totals.revenue)}</p>
          </div>
        </Card>
        <Card>
          <div className="p-5">
            <p className="text-sm text-gray-500">Total Orders</p>
            <p className="text-2xl font-bold text-gray-900">{totals.orders}</p>
          </div>
        </Card>
        <Card>
          <div className="p-5">
            <p className="text-sm text-gray-500">Avg Order</p>
            <p className="text-2xl font-bold text-gray-900">
              {formatMoney(totals.orders ? totals.revenue / totals.orders : 0)}
            </p>
          </div>
        </Card>
      </div>

      <Card>
        <div className="h-64 p-4">
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={rows}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
              <XAxis dataKey="date" tick={{ fontSize: 12 }} />
              <YAxis tick={{ fontSize: 12 }} tickFormatter={(v: number) => (v >= 1000 ? `${(v / 1000).toFixed(0)}k` : String(v))} />
              <Tooltip formatter={(v: number) => formatMoney(v)} />
              <Area type="monotone" dataKey="revenue" stroke="#4f46e5" fill="#c7d2fe" name="Revenue" />
            </AreaChart>
          </ResponsiveContainer>
        </div>
      </Card>

      <Card>
        {rows.length === 0 ? (
          <EmptyState title="No sales found" description="Adjust the date range." />
        ) : (
          <DataTable<DailyRow>
            columns={[
              { key: 'date', header: 'Date', render: (r) => <span className="font-medium">{r.date}</span> },
              { key: 'orders', header: 'Orders', render: (r) => r.orders },
              { key: 'gross', header: 'Gross', render: (r) => formatMoney(r.gross) },
              { key: 'revenue', header: 'Revenue', render: (r) => <span className="font-semibold text-indigo-600">{formatMoney(r.revenue)}</span> },
            ]}
            rows={rows}
            loading={loading}
          />
        )}
      </Card>
    </div>
  );
}