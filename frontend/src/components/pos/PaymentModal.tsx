import { useEffect, useMemo, useState } from 'react';
import toast from 'react-hot-toast';
import { Banknote, CreditCard, Smartphone, QrCode, Plus, Trash2 } from 'lucide-react';
import { Modal } from '../ui/Modal';
import { Button } from '../ui/Button';
import { Input } from '../ui/Input';
import { formatMoney } from '../../utils/format';
import { cn } from '../../utils/cn';

export type PaymentMethod = 'cash' | 'card' | 'mobile' | 'qr';

export interface PaymentLine {
  id: number;
  method: PaymentMethod;
  amount: number;
  reference?: string;
}

const methods: Array<{ key: PaymentMethod; label: string; icon: typeof Banknote }> = [
  { key: 'cash', label: 'Cash', icon: Banknote },
  { key: 'card', label: 'Card', icon: CreditCard },
  { key: 'mobile', label: 'Mobile Wallet', icon: Smartphone },
  { key: 'qr', label: 'QR Payment', icon: QrCode },
];

export function PaymentModal({
  open,
  onClose,
  total,
  onComplete,
  loading,
}: {
  open: boolean;
  onClose: () => void;
  total: number;
  onComplete: (payments: PaymentLine[]) => void;
  loading?: boolean;
}) {
  const [lines, setLines] = useState<PaymentLine[]>([{ id: 1, method: 'cash', amount: 0 }]);
  const [activeMethod, setActiveMethod] = useState<PaymentMethod>('cash');

  const paid = useMemo(() => lines.reduce((sum, l) => sum + (Number(l.amount) || 0), 0), [lines]);
  const remaining = Math.round((total - paid) * 100) / 100;
  const change = remaining < 0 ? Math.abs(remaining) : 0;

  useEffect(() => {
    if (open) {
      setLines([{ id: 1, method: 'cash', amount: 0 }]);
      setActiveMethod('cash');
    }
  }, [open]);

  const updateLine = (id: number, patch: Partial<PaymentLine>) => {
    setLines((prev) => prev.map((l) => (l.id === id ? { ...l, ...patch } : l)));
  };

  const addLine = () => {
    setLines((prev) => [...prev, { id: Date.now(), method: activeMethod, amount: 0 }]);
  };

  const removeLine = (id: number) => {
    setLines((prev) => (prev.length > 1 ? prev.filter((l) => l.id !== id) : prev));
  };

  const setExact = () => {
    setLines((prev) => {
      const [first, ...rest] = prev;
      const restSum = rest.reduce((s, l) => s + (Number(l.amount) || 0), 0);
      return [{ ...first, amount: Math.round((total - restSum) * 100) / 100 }, ...rest];
    });
  };

  const handleConfirm = () => {
    if (Math.abs(remaining) > 0.01) {
      toast.error(`Payments total ${formatMoney(paid)} but the sale total is ${formatMoney(total)}.`);
      return;
    }

    onComplete(lines.map((l) => ({ ...l, amount: Number(l.amount) || 0 })));
  };

  return (
    <Modal
      open={open}
      onClose={onClose}
      title="Collect Payment"
      size="lg"
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            Cancel
          </Button>
          <Button onClick={handleConfirm} loading={loading} disabled={Math.abs(remaining) > 0.01}>
            Complete Sale
          </Button>
        </>
      }
    >
      <div className="space-y-4">
        <div className="rounded-xl bg-gray-900 p-4 text-center text-white">
          <p className="text-xs uppercase tracking-wide text-gray-400">Total Due</p>
          <p className="text-3xl font-bold">{formatMoney(total)}</p>
          <p className={cn('mt-1 text-sm', remaining < 0 ? 'text-emerald-400' : 'text-amber-400')}>
            {remaining < 0 ? `Change: ${formatMoney(change)}` : `Remaining: ${formatMoney(remaining)}`}
          </p>
        </div>

        <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
          {methods.map((method) => (
            <button
              key={method.key}
              onClick={() => {
                setActiveMethod(method.key);
                addLine();
              }}
              className={cn(
                'flex flex-col items-center gap-1 rounded-lg border-2 p-3 text-xs font-medium transition-colors',
                activeMethod === method.key ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500 hover:border-gray-300',
              )}
            >
              <method.icon className="size-5" />
              {method.label}
            </button>
          ))}
        </div>

        <div className="space-y-2">
          {lines.map((line, index) => {
            const method = methods.find((m) => m.key === line.method)!;

            return (
              <div key={line.id} className="flex items-center gap-2 rounded-lg border border-gray-200 p-2">
                <span className="flex size-8 shrink-0 items-center justify-center rounded-md bg-gray-100">
                  <method.icon className="size-4 text-gray-600" />
                </span>

                <select
                  value={line.method}
                  onChange={(e) => updateLine(line.id, { method: e.target.value as PaymentMethod })}
                  className="rounded-md border-0 text-sm font-medium text-gray-700 ring-1 ring-gray-200 focus:ring-2 focus:ring-indigo-500"
                >
                  {methods.map((m) => (
                    <option key={m.key} value={m.key}>
                      {m.label}
                    </option>
                  ))}
                </select>

                <Input
                  type="number"
                  min="0"
                  step="0.01"
                  value={line.amount || ''}
                  placeholder="Amount"
                  onChange={(e) => updateLine(line.id, { amount: Number(e.target.value) || 0 })}
                  className="flex-1"
                />

                {line.method !== 'cash' && (
                  <Input
                    value={line.reference ?? ''}
                    placeholder="Reference"
                    onChange={(e) => updateLine(line.id, { reference: e.target.value })}
                    className="hidden w-32 sm:block"
                  />
                )}

                {index > 0 && (
                  <button onClick={() => removeLine(line.id)} className="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500">
                    <Trash2 className="size-4" />
                  </button>
                )}
              </div>
            );
          })}

          <div className="flex items-center justify-between">
            <Button type="button" variant="ghost" size="sm" onClick={addLine}>
              <Plus className="size-4" /> Add split payment
            </Button>
            {lines.some((l) => l.method === 'cash') && (
              <Button type="button" variant="secondary" size="sm" onClick={setExact}>
                Exact amount
              </Button>
            )}
          </div>
        </div>

        <div className="grid grid-cols-3 gap-2 rounded-lg bg-gray-50 p-3 text-center text-sm">
          <div>
            <p className="text-xs text-gray-500">Paid</p>
            <p className="font-semibold text-gray-900">{formatMoney(paid)}</p>
          </div>
          <div>
            <p className="text-xs text-gray-500">Change</p>
            <p className="font-semibold text-emerald-600">{formatMoney(change)}</p>
          </div>
          <div>
            <p className="text-xs text-gray-500">Due</p>
            <p className={cn('font-semibold', remaining > 0 ? 'text-amber-600' : 'text-emerald-600')}>{formatMoney(Math.max(remaining, 0))}</p>
          </div>
        </div>
      </div>
    </Modal>
  );
}