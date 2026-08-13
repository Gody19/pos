import { useEffect, useState } from 'react';
import {
  DollarSign,
  ShoppingBag,
  TrendingUp,
  Layers,
  AlertTriangle,
  CalendarClock,
  PieChart as PieChartIcon,
} from 'lucide-react';
import {
  AreaChart,
  Area,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from 'recharts';
import { api } from '../api/client';
import type { DashboardData } from '../api/types';
import { Card, CardHeader, CardBody } from '../components/ui/Card';
import { Spinner } from '../components/ui/Spinner';
import { formatMoney } from '../utils/format';
import { useAuthStore } from '../store/authStore';
import { Link } from 'react-router-dom';

const PIE_COLORS = ['#10b981', '#6366f1', '#f59e0b', '#3b82f6', '#ef4444'];

export function DashboardPage() {
  const { user } = useAuthStore();
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api
      .get<{ data: DashboardData }>('/dashboard')
      .then((res) => setData(res.data.data))
      .finally(() => setLoading(false));
  }, []);

  if (loading || !data) return <Spinner />;

  const stats = [
    { label: "Today's Sales", value: formatMoney(data.today_revenue), icon: DollarSign, color: 'bg-emerald-100 text-emerald-700' },
    { label: 'Orders Today', value: String(data.today_orders), icon: ShoppingBag, color: 'bg-indigo-100 text-indigo-700' },
    { label: 'Profit Today', value: formatMoney(data.today_profit), icon: TrendingUp, color: 'bg-sky-100 text-sky-700' },
    { label: 'Total Revenue', value: formatMoney(data.total_revenue), icon: Layers, color: 'bg-purple-100 text-purple-700' },
    { label: 'Open Shifts', value: String(data.open_shifts), icon: CalendarClock, color: 'bg-amber-100 text-amber-700' },
    { label: 'Low Stock Items', value: String(data.low_stock_items), icon: AlertTriangle, color: 'bg-red-100 text-red-700' },
  ];

  const paymentData = data.payment_mix.map((p) => ({
    name: (p.payment_method ?? 'unknown').toUpperCase(),
    value: Number(p.amount),
  }));

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Dashboard</h1>
          <p className="text-sm text-gray-500">
            Welcome back, {user?.name}. Here is what is happening today.
          </p>
        </div>
        {user?.role_name !== 'inventory_clerk' && (
          <Link
            to={user?.active_shift ? '/pos' : '/shift/open'}
            className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
          >
            {user?.active_shift ? 'Open POS' : 'Open Shift'}
          </Link>
        )}
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        {stats.map((stat) => (
          <Card key={stat.label} className="p-5">
            <div className="flex items-start justify-between">
              <div>
                <p className="text-xs font-medium uppercase tracking-wide text-gray-500">{stat.label}</p>
                <p className="mt-1 text-xl font-bold text-gray-900">{stat.value}</p>
              </div>
              <div className={`rounded-lg p-2 ${stat.color}`}>
                <stat.icon className="size-5" />
              </div>
            </div>
          </Card>
        ))}
      </div>

      <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <Card className="xl:col-span-2">
          <CardHeader title="Sales Trend" subtitle="Last 14 days" />
          <CardBody>
            <div className="h-72">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={data.sales_trend.map((d) => ({ ...d, revenue: Number(d.revenue) }))}>
                  <defs>
                    <linearGradient id="revenue" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#6366f1" stopOpacity={0.8} />
                      <stop offset="95%" stopColor="#6366f1" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                  <XAxis dataKey="date" tick={{ fontSize: 12 }} tickFormatter={(v) => v.slice(5)} />
                  <YAxis tick={{ fontSize: 12 }} />
                  <Tooltip formatter={(v: number) => formatMoney(v)} />
                  <Area type="monotone" dataKey="revenue" stroke="#6366f1" fill="url(#revenue)" strokeWidth={2} />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </CardBody>
        </Card>

        <Card>
          <CardHeader title="Payment Methods" subtitle="Today" />
          <CardBody>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie data={paymentData} dataKey="value" nameKey="name" innerRadius={50} outerRadius={85} label>
                    {paymentData.map((_, i) => (
                      <Cell key={i} fill={PIE_COLORS[i % PIE_COLORS.length]} />
                    ))}
                  </Pie>
                  <Tooltip formatter={(v: number) => formatMoney(v)} />
                </PieChart>
              </ResponsiveContainer>
            </div>
            <div className="mt-2 flex flex-wrap justify-center gap-3">
              {paymentData.map((p, i) => (
                <span key={p.name} className="inline-flex items-center gap-1 text-xs text-gray-600">
                  <span className="size-2 rounded-full" style={{ background: PIE_COLORS[i % PIE_COLORS.length] }} />
                  {p.name}
                </span>
              ))}
            </div>
          </CardBody>
        </Card>
      </div>

      <div className="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <Card>
          <CardHeader title="Hourly Sales" subtitle="Today" />
          <CardBody>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={data.hourly_sales.map((d) => ({ ...d, revenue: Number(d.revenue) }))}>
                  <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
                  <XAxis dataKey="hour" tick={{ fontSize: 12 }} tickFormatter={(v) => `${v}h`} />
                  <YAxis tick={{ fontSize: 12 }} />
                  <Tooltip formatter={(v: number) => formatMoney(v)} />
                  <Bar dataKey="revenue" fill="#10b981" radius={[4, 4, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </CardBody>
        </Card>

        <Card>
          <CardHeader title="Top Selling Products" subtitle="Last 14 days" />
          <CardBody>
            <div className="space-y-3">
              {data.top_products.map((p, i) => (
                <div key={p.name} className="flex items-center gap-3">
                  <span className="flex size-7 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600">
                    {i + 1}
                  </span>
                  <div className="flex-1">
                    <p className="text-sm font-medium text-gray-900">{p.name}</p>
                    <p className="text-xs text-gray-500">{p.total_qty} units sold</p>
                  </div>
                  <p className="text-sm font-semibold text-gray-900">{formatMoney(Number(p.revenue))}</p>
                </div>
              ))}
              {data.top_products.length === 0 && (
                <div className="flex items-center gap-2 py-8 text-sm text-gray-500">
                  <PieChartIcon className="size-4" /> No sales recorded yet.
                </div>
              )}
            </div>
          </CardBody>
        </Card>
      </div>
    </div>
  );
}