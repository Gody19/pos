import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Printer, Download, PlusCircle, Store } from 'lucide-react';
import { api } from '../api/client';
import type { Sale } from '../api/types';
import { Button } from '../components/ui/Button';
import { Spinner } from '../components/ui/Spinner';
import { formatDate, formatMoney } from '../utils/format';
import { useAuthStore } from '../store/authStore';

export function ReceiptPreviewPage() {
  const { saleId } = useParams();
  const navigate = useNavigate();
  const { user } = useAuthStore();
  const [sale, setSale] = useState<Sale | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api
      .get<{ data: Sale }>(`/sales/${saleId}`)
      .then((res) => setSale(res.data.data))
      .finally(() => setLoading(false));
  }, [saleId]);

  if (loading) return <Spinner />;

  if (!sale) return <p className="py-16 text-center text-sm text-gray-500">Sale not found.</p>;

  const openReceipt = () => {
    const html = window.open('', '_blank');
    if (html) {
      api.get<{ data: string }>(`/receipts/${sale.id}/html`).then((res) => {
        html.document.write(res.data.data);
        html.document.close();
        html.print();
      });
    }
  };

  return (
    <div className="mx-auto max-w-md">
      <div className="mb-4 flex items-center justify-between">
        <h1 className="text-xl font-bold text-gray-900">Sale Completed</h1>
        <Link
          to="/shift/close"
          className="text-sm font-medium text-indigo-600 hover:text-indigo-500"
        >
          Close Shift →
        </Link>
      </div>

      <div className="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
        <div className="text-center">
          <div className="mx-auto mb-2 flex size-12 items-center justify-center rounded-full bg-emerald-100">
            <Store className="size-6 text-emerald-600" />
          </div>
          <h2 className="text-lg font-bold text-gray-900">Thank you!</h2>
          <p className="text-sm text-gray-500">{sale.invoice_number}</p>
          <p className="text-xs text-gray-400">{formatDate(sale.sold_at)}</p>
        </div>

        <div className="mt-5 space-y-1.5 border-t border-dashed border-gray-300 pt-4 text-sm">
          <div className="flex justify-between">
            <span className="text-gray-500">Cashier</span>
            <span className="font-medium">{sale.user?.name}</span>
          </div>
          {sale.customer && (
            <div className="flex justify-between">
              <span className="text-gray-500">Customer</span>
              <span className="font-medium">{sale.customer.full_name}</span>
            </div>
          )}
          {sale.customer && (
            <div className="flex justify-between">
              <span className="text-gray-500">Loyalty points</span>
              <span className="font-medium text-amber-600">{sale.customer.loyalty_points}</span>
            </div>
          )}
        </div>

        <div className="mt-4 space-y-1.5 border-t border-dashed border-gray-300 pt-4">
          {sale.items?.map((item) => (
            <div key={item.id} className="flex justify-between text-sm">
              <span className="text-gray-700">
                {item.product?.name ?? 'Item'}
                <span className="text-gray-400"> × {item.quantity}</span>
              </span>
              <span className="font-medium">{formatMoney(Number(item.total))}</span>
            </div>
          ))}
        </div>

        <div className="mt-4 space-y-1 border-t border-dashed border-gray-300 pt-4 text-sm">
          <div className="flex justify-between text-gray-500">
            <span>Subtotal</span>
            <span>{formatMoney(Number(sale.subtotal))}</span>
          </div>
          {Number(sale.discount) > 0 && (
            <div className="flex justify-between text-red-500">
              <span>Discount</span>
              <span>-{formatMoney(Number(sale.discount))}</span>
            </div>
          )}
          <div className="flex justify-between text-gray-500">
            <span>Tax</span>
            <span>{formatMoney(Number(sale.tax))}</span>
          </div>
          <div className="flex justify-between text-base font-bold text-gray-900">
            <span>Total</span>
            <span>{formatMoney(Number(sale.total))}</span>
          </div>
          <div className="flex justify-between text-gray-500">
            <span>Paid</span>
            <span>{formatMoney(Number(sale.amount_paid))}</span>
          </div>
          <div className="flex justify-between text-gray-500">
            <span>Change</span>
            <span>{formatMoney(Number(sale.change_due))}</span>
          </div>
        </div>

        <div className="mt-4 border-t border-dashed border-gray-300 pt-3">
          {sale.payments?.map((p, i) => (
            <div key={i} className="flex justify-between text-xs text-gray-500">
              <span className="capitalize">{p.method}</span>
              <span>{formatMoney(Number(p.amount))}</span>
            </div>
          ))}
        </div>

        <div className="mt-6 grid grid-cols-1 gap-2 sm:grid-cols-3">
          <Button onClick={openReceipt}>
            <Printer className="size-4" /> Print
          </Button>
          <Button variant="secondary" onClick={() => window.open(sale.receipt?.pdf_url ?? undefined, '_blank')}>
            <Download className="size-4" /> PDF
          </Button>
          <Button variant="secondary" onClick={() => navigate('/pos')}>
            <PlusCircle className="size-4" /> New Sale
          </Button>
        </div>
      </div>
    </div>
  );
}