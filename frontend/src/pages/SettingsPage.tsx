import { useEffect, useState, type FormEvent } from 'react';
import toast from 'react-hot-toast';
import { Store } from 'lucide-react';
import { api } from '../api/client';
import type { StoreSettings } from '../api/types';
import { Button } from '../components/ui/Button';
import { Card, CardHeader, CardBody } from '../components/ui/Card';
import { Field, Input } from '../components/ui/Input';
import { Spinner } from '../components/ui/Spinner';

export function SettingsPage() {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({
    store_name: '',
    store_address: '',
    store_phone: '',
    store_tin: '',
    store_footer: '',
    currency: 'TZS',
    points_per_currency: '1000',
  });

  useEffect(() => {
    api.get<{ data: StoreSettings }>('/settings').then((res) => {
      const s = res.data.data;
      setForm({
        store_name: s.store.name,
        store_address: s.store.address ?? '',
        store_phone: s.store.phone ?? '',
        store_tin: s.store.tin ?? '',
        store_footer: s.store.footer ?? '',
        currency: s.currency,
        points_per_currency: String(s.loyalty.points_per_currency),
      });
      setLoading(false);
    });
  }, []);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await api.put('/settings', {
        store: {
          name: form.store_name,
          address: form.store_address,
          phone: form.store_phone,
          tin: form.store_tin,
          footer: form.store_footer,
        },
        currency: form.currency,
        loyalty: { points_per_currency: Number(form.points_per_currency) },
      });
      toast.success('Settings saved.');
    } catch {
      // toast handled
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <Spinner />;

  return (
    <div className="mx-auto max-w-2xl space-y-4">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Settings</h1>
        <p className="text-sm text-gray-500">Store information, currency, and loyalty settings.</p>
      </div>

      <Card>
        <CardHeader
          title={
            <span className="flex items-center gap-2">
              <Store className="size-5 text-indigo-600" /> Store Information
            </span>
          }
        />
        <CardBody>
          <form onSubmit={handleSubmit} className="space-y-4">
            <Field label="Store Name">
              <Input required value={form.store_name} onChange={(e) => setForm((f) => ({ ...f, store_name: e.target.value }))} />
            </Field>
            <Field label="Address">
              <Input value={form.store_address} onChange={(e) => setForm((f) => ({ ...f, store_address: e.target.value }))} />
            </Field>
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <Field label="Phone">
                <Input value={form.store_phone} onChange={(e) => setForm((f) => ({ ...f, store_phone: e.target.value }))} />
              </Field>
              <Field label="TIN Number">
                <Input value={form.store_tin} onChange={(e) => setForm((f) => ({ ...f, store_tin: e.target.value }))} />
              </Field>
            </div>
            <Field label="Receipt Footer">
              <Input
                value={form.store_footer}
                onChange={(e) => setForm((f) => ({ ...f, store_footer: e.target.value }))}
                placeholder="Thank you for your business!"
              />
            </Field>

            <div className="border-t border-gray-200 pt-4">
              <h3 className="mb-3 text-sm font-semibold text-gray-700">Currency & Loyalty</h3>
              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Field label="Currency Code">
                  <Input required maxLength={8} value={form.currency} onChange={(e) => setForm((f) => ({ ...f, currency: e.target.value.toUpperCase() }))} />
                </Field>
                <Field label="Points per Currency (1 point = X amount)">
                  <Input
                    type="number"
                    min="1"
                    required
                    value={form.points_per_currency}
                    onChange={(e) => setForm((f) => ({ ...f, points_per_currency: e.target.value }))}
                  />
                </Field>
              </div>
              <p className="mt-1 text-xs text-gray-500">
                Example: 1,000 means a customer earns 1 point for every {form.currency} 1,000 spent.
              </p>
            </div>

            <div className="flex justify-end pt-2">
              <Button type="submit" loading={saving}>
                Save Settings
              </Button>
            </div>
          </form>
        </CardBody>
      </Card>
    </div>
  );
}