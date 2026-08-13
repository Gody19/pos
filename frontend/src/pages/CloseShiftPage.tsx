import { useEffect, useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { Banknote, CheckCircle2 } from 'lucide-react';
import { api } from '../api/client';
import { useAuthStore } from '../store/authStore';
import type { Shift } from '../api/types';
import { Button } from '../components/ui/Button';
import { Field, Input, Textarea } from '../components/ui/Input';
import { Card, CardHeader, CardBody } from '../components/ui/Card';
import { Spinner } from '../components/ui/Spinner';
import { formatMoney } from '../utils/format';

export function CloseShiftPage() {
  const { user, fetchMe } = useAuthStore();
  const navigate = useNavigate();
  const [shift, setShift] = useState<Shift | null>(null);
  const [loading, setLoading] = useState(true);
  const [closing, setClosing] = useState(false);
  const [closingBalance, setClosingBalance] = useState('');
  const [notes, setNotes] = useState('');

  useEffect(() => {
    api
      .get<{ data: Shift | null }>('/shifts/current')
      .then((res) => {
        setShift(res.data.data);
        setClosingBalance(String(res.data.data?.total_sales ?? 0));
      })
      .finally(() => setLoading(false));
  }, []);

  const handleClose = async (e: FormEvent) => {
    e.preventDefault();
    setClosing(true);
    try {
      await api.post('/shifts/close', {
        closing_balance: Number(closingBalance) || 0,
        notes,
      });
      await fetchMe();
      toast.success('Shift closed successfully!');
      navigate('/dashboard');
    } catch {
      // toast handled by interceptor
    } finally {
      setClosing(false);
    }
  };

  if (loading) return <Spinner />;

  if (!shift) {
    return (
      <div className="mx-auto max-w-md py-16 text-center">
        <p className="text-sm text-gray-500">No open shift found.</p>
      </div>
    );
  }

  const expected = Number(shift.expected_balance ?? shift.opening_balance + shift.total_sales);
  const entered = Number(closingBalance) || 0;
  const variance = entered - expected;

  return (
    <div className="mx-auto max-w-lg py-6">
      <div className="mb-6 text-center">
        <div className="mx-auto mb-3 flex size-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
          <Banknote className="size-7" />
        </div>
        <h1 className="text-2xl font-bold text-gray-900">Close Shift</h1>
        <p className="text-sm text-gray-500">End the shift and reconcile the cash drawer.</p>
      </div>

      <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
        {[
          { label: 'Cash', value: shift.cash_sales },
          { label: 'Card', value: shift.card_sales },
          { label: 'Mobile', value: shift.mobile_sales },
          { label: 'QR', value: shift.qr_sales },
        ].map((row) => (
          <Card key={row.label} className="p-4">
            <p className="text-xs font-medium text-gray-500">{row.label}</p>
            <p className="text-sm font-bold text-gray-900">{formatMoney(Number(row.value))}</p>
          </Card>
        ))}
      </div>

      <Card className="mt-4">
        <CardHeader
          title="Shift Summary"
          subtitle={`Opened with ${formatMoney(Number(shift.opening_balance))} · ${shift.sales_count} sale(s)`}
        />
        <CardBody>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between">
              <dt className="text-gray-500">Opening balance</dt>
              <dd className="font-medium">{formatMoney(Number(shift.opening_balance))}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-gray-500">Total sales</dt>
              <dd className="font-medium">{formatMoney(Number(shift.total_sales))}</dd>
            </div>
            <div className="flex justify-between border-t border-gray-200 pt-2">
              <dt className="font-semibold text-gray-900">Expected balance</dt>
              <dd className="font-semibold">{formatMoney(expected)}</dd>
            </div>
          </dl>

          <form onSubmit={handleClose} className="mt-5 space-y-4">
            <Field label="Counted Closing Balance">
              <Input
                type="number"
                min="0"
                step="0.01"
                required
                value={closingBalance}
                onChange={(e) => setClosingBalance(e.target.value)}
              />
            </Field>

            <div
              className={`flex items-center justify-between rounded-lg px-3 py-2 text-sm ${
                Math.abs(variance) > 0.01 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'
              }`}
            >
              <span>Variance</span>
              <span className="font-semibold">{formatMoney(variance)}</span>
            </div>

            <Field label="Notes (optional)">
              <Textarea rows={2} value={notes} onChange={(e) => setNotes(e.target.value)} placeholder="End of day..." />
            </Field>

            <Button type="submit" variant="success" loading={closing} className="w-full" size="lg">
              <CheckCircle2 className="size-5" /> Close Shift
            </Button>
          </form>
        </CardBody>
      </Card>
    </div>
  );
}