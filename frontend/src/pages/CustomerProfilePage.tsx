import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';
import { api } from '../api/client';
import type { Customer, LoyaltyTransaction, Paginated, Sale } from '../api/types';
import { Card, CardHeader, CardBody } from '../components/ui/Card';
import { Badge } from '../components/ui/Badge';
import { Spinner } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';
import { formatDate, formatMoney } from '../utils/format';

export function CustomerProfilePage() {
  const { id } = useParams();
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [loyalty, setLoyalty] = useState<Paginated<LoyaltyTransaction> | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      api.get<{ data: Customer }>(`/customers/${id}`),
      api.get<Paginated<LoyaltyTransaction>>(`/customers/${id}/loyalty`),
    ])
      .then(([c, l]) => {
        setCustomer(c.data.data);
        setLoyalty(l.data);
      })
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <Spinner />;
  if (!customer) return <p className="py-16 text-center text-sm text-gray-500">Customer not found.</p>;

  const sales = customer.sales as Sale[] | undefined;

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-3">
        <Link to="/customers" className="rounded-md p-1.5 text-gray-500 hover:bg-gray-100">
          <ArrowLeft className="size-5" />
        </Link>
        <div>
          <h1 className="text-2xl font-bold text-gray-900">{customer.full_name}</h1>
          <p className="text-sm text-gray-500">{customer.customer_code}</p>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <Card>
          <CardBody>
            <p className="text-sm text-gray-500">Loyalty Points</p>
            <p className="text-2xl font-bold text-amber-600">{customer.loyalty_points}</p>
          </CardBody>
        </Card>
        <Card>
          <CardBody>
            <p className="text-sm text-gray-500">Total Spent</p>
            <p className="text-2xl font-bold text-gray-900">{formatMoney(customer.total_spent ?? 0)}</p>
          </CardBody>
        </Card>
        <Card>
          <CardBody>
            <p className="text-sm text-gray-500">Contact</p>
            <p className="text-sm font-medium text-gray-900">{customer.phone ?? '-'}</p>
            <p className="text-xs text-gray-500">{customer.email ?? customer.address ?? ''}</p>
          </CardBody>
        </Card>
      </div>

      {sales && sales.length > 0 && (
        <Card>
          <CardHeader title="Recent Purchases" />
          <CardBody>
            <DataTable<Sale>
              columns={[
                { key: 'invoice_number', header: 'Invoice', render: (s) => <span className="font-medium">{s.invoice_number}</span> },
                { key: 'sold_at', header: 'Date', render: (s) => formatDate(s.sold_at) },
                { key: 'total', header: 'Total', render: (s) => formatMoney(s.total) },
                {
                  key: 'status',
                  header: 'Status',
                  render: (s) => <Badge color={s.payment_status === 'completed' ? 'green' : 'gray'}>{s.payment_status}</Badge>,
                },
              ]}
              rows={sales}
              loading={false}
            />
          </CardBody>
        </Card>
      )}

      <Card>
        <CardHeader title="Loyalty History" />
        <CardBody>
          <DataTable<LoyaltyTransaction>
            columns={[
              {
                key: 'type',
                header: 'Type',
                render: (t) => (
                  <Badge color={t.type === 'earned' ? 'green' : t.type === 'redeemed' ? 'red' : 'gray'}>{t.type}</Badge>
                ),
              },
              {
                key: 'points',
                header: 'Points',
                render: (t) => <span className={t.points >= 0 ? 'font-semibold text-emerald-600' : 'font-semibold text-red-500'}>{t.points > 0 ? `+${t.points}` : t.points}</span>,
              },
              { key: 'notes', header: 'Notes', render: (t) => t.notes ?? '-' },
              { key: 'created_at', header: 'Date', render: (t) => formatDate(t.created_at) },
            ]}
            rows={loyalty?.data ?? []}
            loading={false}
          />
        </CardBody>
      </Card>
    </div>
  );
}