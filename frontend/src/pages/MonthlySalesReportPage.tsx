import { useCallback, useEffect, useMemo, useState } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { api } from '../api/client';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Select } from '../components/ui/Input';
import { EmptyState } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';
import { formatMoney } from '../utils/format';

interface MonthlyRow {
  id: number;
  month: number;
  orders: number;
  revenue: number;
}

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

export function MonthlySalesReportPage() {
  const currentYear = new Date().getFullYear();
  const years = useMemo(() => Array.from({ length: 5 }, (_, i) => currentYear - i), [currentYear]);

  const [year, setYear] = useState(currentYear);
  const [rows, setRows] = useState<MonthlyRow[]>([]);
  const [loading, setLoading] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<{ data: MonthlyRow[] }>('/reports/monthly-sales', { params: { year } })
      .then((res) =>
        setRows(
          res.data.data.map((r) => ({ ...r, id: r.month, orders: Number(r.orders), revenue: Number(r.revenue) })),
        ),
      )
      .finally(() => setLoading(false));
  }, [year]);

  useEffect(load, [load]);

  const chartData = useMemo(
    () => MONTHS.map((name, i) => {
      const row = rows.find((r) => r.month === i + 1);
      return { name, revenue: row?.revenue ?? 0, orders: row?.orders ?? 0 };
    }),
    [rows],
  );

  const totals = useMemo(
    () => rows.reduce((acc, r) => ({ orders: acc.orders + r.orders, revenue: acc.revenue + r.revenue }), { orders: 0, revenue: 0 }),
    [rows],
  );

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Monthly Sales Report</h1>
          <p className="text-sm text-gray-500">Sales performance by month.</p>
        </div>
        <div className="flex items-end gap-2">
          <div>
            <label className="block text-xs font-medium text-gray-500">Year</label>
            <Select value={year} onChange={(e) => setYear(Number(e.target.value))} className="w-32">
              {years.map((y) => (
                <option key={y} value={y}>
                  {y}
                </option>
              ))}
            </Select>
          </div>
          <Button onClick={load}>Apply</Button>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <Card>
          <div className="p-5">
            <p className="text-sm text-gray-500">Annual Revenue</p>
            <p className="text-2xl font-bold text-gray-900">{formatMoney(totals.revenue)}</p>
          </div>
        </Card>
        <Card>
          <div className="p-5">
            <p className="text-sm text-gray-500">Annual Orders</p>
            <p className="text-2xl font-bold text-gray-900">{totals.orders}</p>
          </div>
        </Card>
      </div>

      <Card>
        <div className="h-64 p-4">
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={chartData}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
              <XAxis dataKey="name" tick={{ fontSize: 12 }} />
              <YAxis tick={{ fontSize: 12 }} tickFormatter={(v: number) => (v >= 1000 ? `${(v / 1000).toFixed(0)}k` : String(v))} />
              <Tooltip formatter={(v: number) => formatMoney(v)} />
              <Bar dataKey="revenue" fill="#4f46e5" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </Card>

      <Card>
        {rows.length === 0 ? (
          <EmptyState title="No sales found" description="No data for this year yet." />
        ) : (
          <DataTable<MonthlyRow>
            columns={[
              { key: 'month', header: 'Month', render: (r) => <span className="font-medium">{MONTHS[r.month - 1]}</span> },
              { key: 'orders', header: 'Orders', render: (r) => r.orders },
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