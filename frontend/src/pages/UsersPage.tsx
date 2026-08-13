import { useCallback, useEffect, useState, type FormEvent } from 'react';
import toast from 'react-hot-toast';
import { Plus, Pencil, Search, Trash2 } from 'lucide-react';
import { api } from '../api/client';
import type { Paginated, User } from '../api/types';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Badge, statusColor } from '../components/ui/Badge';
import { Modal } from '../components/ui/Modal';
import { Field, Input, Select } from '../components/ui/Input';
import { Pagination } from '../components/ui/Pagination';
import { Spinner } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';

const ROLES = [
  { value: 'admin', label: 'Admin' },
  { value: 'manager', label: 'Manager' },
  { value: 'cashier', label: 'Cashier' },
  { value: 'inventory_clerk', label: 'Inventory Clerk' },
];

const ROLE_COLOR: Record<string, 'green' | 'blue' | 'yellow' | 'indigo' | 'gray'> = {
  admin: 'indigo',
  manager: 'blue',
  cashier: 'green',
  inventory_clerk: 'yellow',
};

export function UsersPage() {
  const [data, setData] = useState<Paginated<User> | null>(null);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);

  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<User | null>(null);
  const [form, setForm] = useState({
    name: '',
    email: '',
    phone: '',
    role_name: 'cashier',
    password: '',
    password_confirmation: '',
    status: true,
  });
  const [saving, setSaving] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<Paginated<User>>('/users', { params: { per_page: 15, page, search: search || undefined } })
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
    setForm({ name: '', email: '', phone: '', role_name: 'cashier', password: '', password_confirmation: '', status: true });
    setModalOpen(true);
  };

  const openEdit = (user: User) => {
    setEditing(user);
    setForm({
      name: user.name,
      email: user.email,
      phone: user.phone ?? '',
      role_name: user.role_name,
      password: '',
      password_confirmation: '',
      status: user.status,
    });
    setModalOpen(true);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (form.password !== form.password_confirmation) {
      toast.error('Passwords do not match.');
      return;
    }
    setSaving(true);
    try {
      const payload = {
        name: form.name,
        email: form.email,
        phone: form.phone,
        role_name: form.role_name,
        status: form.status,
        ...(form.password ? { password: form.password, password_confirmation: form.password_confirmation } : {}),
      };
      if (editing) {
        await api.put(`/users/${editing.id}`, payload);
        toast.success('User updated.');
      } else {
        await api.post('/users', payload);
        toast.success('User created.');
      }
      setModalOpen(false);
      load();
    } catch {
      // toast handled by interceptor
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (user: User) => {
    if (!window.confirm(`Deactivate user "${user.name}"?`)) return;
    try {
      await api.delete(`/users/${user.id}`);
      toast.success('User deactivated.');
      load();
    } catch {
      // toast handled
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Users</h1>
          <p className="text-sm text-gray-500">Manage staff accounts and roles.</p>
        </div>
        <Button onClick={openCreate}>
          <Plus className="size-4" /> New User
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
              placeholder="Search name or email..."
              className="pl-10"
            />
          </div>
        </div>

        {loading && !data ? (
          <Spinner />
        ) : (
          <DataTable<User>
            columns={[
              {
                key: 'name',
                header: 'User',
                render: (u) => (
                  <div>
                    <p className="font-medium text-gray-900">{u.name}</p>
                    <p className="text-xs text-gray-500">{u.email}</p>
                  </div>
                ),
              },
              { key: 'phone', header: 'Phone', render: (u) => u.phone ?? '-' },
              { key: 'role_name', header: 'Role', render: (u) => <Badge color={ROLE_COLOR[u.role_name] ?? 'gray'}>{u.role_name}</Badge> },
              { key: 'status', header: 'Status', render: (u) => <Badge color={statusColor(u.status ? 'active' : 'closed')}>{u.status ? 'Active' : 'Inactive'}</Badge> },
              {
                key: 'actions',
                header: '',
                render: (u) => (
                  <div className="flex gap-1">
                    <button onClick={() => openEdit(u)} className="rounded-md p-1.5 text-indigo-600 hover:bg-indigo-50">
                      <Pencil className="size-4" />
                    </button>
                    {u.role_name !== 'admin' && (
                      <button onClick={() => handleDelete(u)} className="rounded-md p-1.5 text-red-500 hover:bg-red-50">
                        <Trash2 className="size-4" />
                      </button>
                    )}
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
        title={editing ? 'Edit User' : 'New User'}
        footer={
          <>
            <Button variant="secondary" onClick={() => setModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" form="user-form" loading={saving}>
              {editing ? 'Save' : 'Create'}
            </Button>
          </>
        }
      >
        <form id="user-form" onSubmit={handleSubmit} className="space-y-4">
          <Field label="Full Name">
            <Input required value={form.name} onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))} />
          </Field>
          <Field label="Email">
            <Input required type="email" value={form.email} onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))} />
          </Field>
          <Field label="Phone">
            <Input value={form.phone} onChange={(e) => setForm((f) => ({ ...f, phone: e.target.value }))} />
          </Field>
          <div className="grid grid-cols-2 gap-3">
            <Field label="Role">
              <Select value={form.role_name} onChange={(e) => setForm((f) => ({ ...f, role_name: e.target.value }))}>
                {ROLES.map((r) => (
                  <option key={r.value} value={r.value}>
                    {r.label}
                  </option>
                ))}
              </Select>
            </Field>
            <Field label="Status">
              <Select
                value={form.status ? '1' : '0'}
                onChange={(e) => setForm((f) => ({ ...f, status: e.target.value === '1' }))}
              >
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </Select>
            </Field>
          </div>
          <div className="grid grid-cols-2 gap-3">
            <Field label={editing ? 'New Password' : 'Password'}>
              <Input
                type="password"
                required={!editing}
                minLength={8}
                value={form.password}
                onChange={(e) => setForm((f) => ({ ...f, password: e.target.value }))}
                placeholder={editing ? 'Leave blank to keep' : 'Min 8 characters'}
              />
            </Field>
            <Field label="Confirm Password">
              <Input
                type="password"
                required={!editing}
                value={form.password_confirmation}
                onChange={(e) => setForm((f) => ({ ...f, password_confirmation: e.target.value }))}
              />
            </Field>
          </div>
        </form>
      </Modal>
    </div>
  );
}