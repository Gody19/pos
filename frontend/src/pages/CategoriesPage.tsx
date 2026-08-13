import { useCallback, useEffect, useState, type FormEvent } from 'react';
import toast from 'react-hot-toast';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import { api } from '../api/client';
import type { Category, Paginated } from '../api/types';
import { Button } from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { Modal } from '../components/ui/Modal';
import { Field, Input, Textarea } from '../components/ui/Input';
import { Pagination } from '../components/ui/Pagination';
import { Spinner } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';

export function CategoriesPage() {
  const [data, setData] = useState<Paginated<Category> | null>(null);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState<Category | null>(null);
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<Paginated<Category>>('/categories', { params: { per_page: 15, page } })
      .then((res) => setData(res.data))
      .finally(() => setLoading(false));
  }, [page]);

  useEffect(load, [load]);

  const openCreate = () => {
    setEditing(null);
    setName('');
    setDescription('');
    setModalOpen(true);
  };

  const openEdit = (category: Category) => {
    setEditing(category);
    setName(category.name);
    setDescription(category.description ?? '');
    setModalOpen(true);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    try {
      if (editing) {
        await api.put(`/categories/${editing.id}`, { name, description });
        toast.success('Category updated.');
      } else {
        await api.post('/categories', { name, description });
        toast.success('Category created.');
      }
      setModalOpen(false);
      load();
    } catch {
      // toast handled by interceptor
    }
  };

  const handleDelete = async (category: Category) => {
    if (!window.confirm(`Delete category "${category.name}"?`)) return;
    try {
      await api.delete(`/categories/${category.id}`);
      toast.success('Category deleted.');
      load();
    } catch {
      // toast handled
    }
  };

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Categories</h1>
          <p className="text-sm text-gray-500">Organize products into categories.</p>
        </div>
        <Button onClick={openCreate}>
          <Plus className="size-4" /> New Category
        </Button>
      </div>

      <Card>
        {loading && !data ? (
          <Spinner />
        ) : (
          <DataTable<Category>
            columns={[
              { key: 'name', header: 'Name', render: (c) => <span className="font-medium text-gray-900">{c.name}</span> },
              { key: 'description', header: 'Description', render: (c) => c.description ?? '-' },
              { key: 'products_count', header: 'Products', render: (c) => c.products_count ?? 0 },
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
        title={editing ? 'Edit Category' : 'New Category'}
        footer={
          <>
            <Button variant="secondary" onClick={() => setModalOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" form="category-form">
              {editing ? 'Save' : 'Create'}
            </Button>
          </>
        }
      >
        <form id="category-form" onSubmit={handleSubmit} className="space-y-4">
          <Field label="Name">
            <Input required value={name} onChange={(e) => setName(e.target.value)} />
          </Field>
          <Field label="Description">
            <Textarea rows={3} value={description} onChange={(e) => setDescription(e.target.value)} />
          </Field>
        </form>
      </Modal>
    </div>
  );
}