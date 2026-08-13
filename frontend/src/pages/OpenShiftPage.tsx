import { useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { CalendarClock, DollarSign } from 'lucide-react';
import { api } from '../api/client';
import { useAuthStore } from '../store/authStore';
import { Button } from '../components/ui/Button';
import { Field, Input, Textarea } from '../components/ui/Input';
import { Card, CardHeader, CardBody } from '../components/ui/Card';
import { formatMoney } from '../utils/format';

export function OpenShiftPage() {
  const { user, fetchMe } = useAuthStore();
  const navigate = useNavigate();
  const [openingBalance, setOpeningBalance] = useState('20000');
  const [notes, setNotes] = useState('');
  const [loading, setLoading] = useState(false);

  const handleOpen = async (e: FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await api.post('/shifts/open', {
        opening_balance: Number(openingBalance) || 0,
        notes,
      });
      await fetchMe();
      toast.success('Shift opened successfully!');
      navigate('/pos');
    } catch {
      // toast handled by interceptor
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="mx-auto max-w-md py-10">
      <div className="mb-6 text-center">
        <div className="mx-auto mb-3 flex size-14 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
          <CalendarClock className="size-7" />
        </div>
        <h1 className="text-2xl font-bold text-gray-900">Open Shift</h1>
        <p className="text-sm text-gray-500">
          {user?.name} · {user?.email}
        </p>
      </div>

      <Card>
        <CardHeader title="Shift Opening" subtitle="Enter the opening cash balance in the drawer." />
        <CardBody>
          <form onSubmit={handleOpen} className="space-y-4">
            <Field label="Opening Balance">
              <div className="relative">
                <DollarSign className="pointer-events-none absolute left-3 top-2.5 size-5 text-gray-400" />
                <Input
                  type="number"
                  min="0"
                  step="0.01"
                  value={openingBalance}
                  onChange={(e) => setOpeningBalance(e.target.value)}
                  className="pl-10"
                />
              </div>
              <p className="mt-1 text-xs text-gray-500">
                Example: {formatMoney(20000)} — count the cash in the register before starting.
              </p>
            </Field>

            <Field label="Notes (optional)">
              <Textarea rows={3} value={notes} onChange={(e) => setNotes(e.target.value)} placeholder="Morning shift..." />
            </Field>

            <Button type="submit" loading={loading} className="w-full" size="lg">
              Open Shift
            </Button>
          </form>
        </CardBody>
      </Card>
    </div>
  );
}