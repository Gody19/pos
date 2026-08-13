import { useCallback, useEffect, useState } from 'react';
import { api } from '../api/client';
import type { Paginated, Shift } from '../api/types';
import { Card } from '../components/ui/Card';
import { Badge } from '../components/ui/Badge';
import { Pagination } from '../components/ui/Pagination';
import { EmptyState, Spinner } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';
import { formatDate, formatMoney } from '../utils/format';

export function ShiftReportPage() {
  const [data, setData] = useState<Paginated<Shift> | null>(null);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<Paginated<Shift>>('/reports/shift-summary', { params: { per_page: 15, page } })
      .then((res) => setData(res.data))
      .finally(() => setLoading(false));
  }, [page]);

  useEffect(load, [load]);

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Shift Report</h1>
        <p className="text-sm text-gray-500">Summary of all cashier shifts.</p>
      </div>

      <Card>
        {loading && !data ? (
          <Spinner />
        ) : data && data.data.length === 0 ? (
          <EmptyState title="No shifts found" description="No shifts have been opened in this period." />
        ) : (
          <DataTable<Shift>
            columns={[
              { key: 'id', header: 'Shift', render: (s) => <span className="font-medium">#{s.id}</span> },
              { key: 'user', header: 'Cashier', render: (s) => s.user?.name ?? '-' },
              { key: 'opened_at', header: 'Opened', render: (s) => formatDate(s.opened_at) },
              { key: 'closed_at', header: 'Closed', render: (s) => (s.closed_at ? formatDate(s.closed_at) : '-') },
              { key: 'status', header: 'Status', render: (s) => <Badge color={s.status === 'open' ? 'green' : 'gray'}>{s.status}</Badge> },
              { key: 'total_sales', header: 'Sales', render: (s) => formatMoney(s.total_sales) },
              { key: 'sales_count', header: 'Orders', render: (s) => s.sales_count },
              {
                key: 'variance',
                header: 'Variance',
                render: (s) =>
                  s.variance !== null && s.variance !== undefined ? (
                    <span className={s.variance >= 0 ? 'font-medium text-emerald-600' : 'font-medium text-red-500'}>
                      {formatMoney(s.variance)}
                    </span>
                  ) : (
                    '-'
                  ),
              },
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