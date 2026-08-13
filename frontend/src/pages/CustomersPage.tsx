import { useCallback, useEffect, useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import toast from 'react-hot-toast';
import { Plus, Pencil, Trash2, Search } from 'lucide-react';
import { api } from '../api/client';
import type { Customer, Paginated } from '../api/types';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Modal } from '../components/ui/Modal';
import { Field, Input } from '../components/ui/Input';
import { Pagination } from '../components/ui/Pagination';
import { Spinner } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';
import { formatMoney } from '../utils/format';

export function CustomersPage() {
  const [data, setData] = useState<Paginated<Customer> | null>(null);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);

  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<Customer | null>(null);
  const [form, setForm] = useState({ full_name: '', phone: '', email: '', address: '' });
  const [saving, setSaving] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<Paginated<Customer>>('/customers', {
        params: { per_page: 15, page, search: search || undefined },
      })
      .then((res) => setData(res.data))
      .finally(() => setLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [page, search]);

  useEffect(() => {
    const t = setTimeout(load, 300);
    return () => clearTimeout(t);
  }, [load]);

  const openCreate = () => {
    setEditing(null);
    setForm({ full_name: '', phone: '', email: '', address: '' });
    setModalOpen(true);
  };

  const openEdit = (customer: Customer) => {
    setEditing(customer);
    setForm({
      full_name: customer.full_name,
      phone: customer.phone ?? '',
      email: customer.email ?? '',
      address: customer.address ?? '',
    });
    setModalOpen(true);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      if (editing) {
        await api.put(`/customers/${editing.id}`, form);
        toast.success('Customer updated.');
      } else {
        await api.post('/customers', form);
        toast.success('Customer created.');
      }
      setModalOpen(false);
      load();
    } catch {
      // toast handled by interceptor
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (customer: Customer) => {
    if (!window.confirm(`Delete customer "${customer.full_name}"?`)) return;
    try {
      await api.delete(`/customers/${customer.id}`);
      toast.success('Customer deleted.');
      load();
    } catch {
      // toast handled
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Customers</h1>
          <p className="text-sm text-gray-500">Manage customer profiles and loyalty points.</p>
        </div>
        <Button onClick={openCreate}>
          <Plus className="size-4" /> New Customer
        </Button>
      </div>

      <Card>
        <div className="border-b border-gray-200 p-4">
          <div className="relative max-w-sm">
            <Search className="pointer-events-none absolute left-3 top-2.5 size-5 text-gray-400" />
            <Input
              value={search}
              onChange={(e) => {
                setSearch(e.target.value);
                setPage(1);
              }}
              placeholder="Search name, phone, email..."
              className="pl-10"
            />
          </div>
        </div>

        {loading && !data ? (
          <Spinner />
        ) : (
          <DataTable<Customer>
            columns={[
              {
                key: 'name',
                header: 'Customer',
                render: (c) => (
                  <div>
                    <Link to={`/customers/${c.id}`} className="font-medium text-gray-900 hover:text-indigo-600">
                      {c.full_name}
                    </Link>
                    <p className="text-xs text-gray-500">{c.customer_code}</p>
                  </div>
                ),
              },
              { key: 'phone', header: 'Phone', render: (c) => c.phone ?? '-' },
              { key: 'email', header: 'Email', render: (c) => c.email ?? '-' },
              {
                key: 'loyalty_points',
                header: 'Points',
                render: (c) => <span className="font-semibold text-amber-600">{c.loyalty_points}</span>,
              },
              {
                key: 'total_spent',
                header: 'Total Spent',
                render: (c) => formatMoney(c.total_spent ?? 0),
              },
              {
                key: 'actions',
                header: '',
                render: (c) => (
                  <div className="flex gap-1">
                    <button onClick={() => openEdit(c)} className="rounded-md p-1.5 text-indigo-600 hover:bg-indigo-50">
                      <Pencil className="size-4" />
                    </button>
                    <button onClick={() => handleDelete(c)} className="rounded-md p-1.5 text-red-500 hover:bg-red-50">
                      <Trash2 className="size-4" />
                    </button>
                  </div>
                ),
              },
            ]}
            rows={data?.data ?? []}
            loading={loading}
          />
        )}
        {data && <Pagination page={page} lastPage={data.meta.last_page} total={data.meta.total} onChange={setPage} />}
      </Card>

      <Modal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editing ? 'Edit Customer' : 'New Customer'}
        footer={
          <>
            <Button variant="secondary" onClick={() => setModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" form="customer-form" loading={saving}>
              {editing ? 'Save' : 'Create'}
            </Button>
          </>
        }
      >
        <form id="customer-form" onSubmit={handleSubmit} className="space-y-4">
          <Field label="Full Name">
            <Input required value={form.full_name} onChange={(e) => setForm((f) => ({ ...f, full_name: e.target.value }))} />
          </Field>
          <Field label="Phone">
            <Input value={form.phone} onChange={(e) => setForm((f) => ({ ...f, phone: e.target.value }))} />
          </Field>
          <Field label="Email">
            <Input type="email" value={form.email} onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))} />
          </Field>
          <Field label="Address">
            <Input value={form.address} onChange={(e) => setForm((f) => ({ ...f, address: e.target.value }))} />
          </Field>
        </form>
      </Modal>
    </div>
  );
}