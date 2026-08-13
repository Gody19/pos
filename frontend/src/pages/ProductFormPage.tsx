import { useEffect, useState, type FormEvent } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import { ArrowLeft, Trash2 } from 'lucide-react';
import { api } from '../api/client';
import type { Category, Product } from '../api/types';
import { Button } from '../components/ui/Button';
import { Card, CardHeader, CardBody } from '../components/ui/Card';
import { Field, Input, Select, Textarea } from '../components/ui/Input';
import { Spinner } from '../components/ui/Spinner';

export function ProductFormPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = Boolean(id);

  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(isEdit);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({
    name: '',
    sku: '',
    barcode: '',
    category_id: '',
    cost_price: '',
    selling_price: '',
    tax_rate: '0',
    quantity: '0',
    reorder_level: '5',
    location: '',
    description: '',
    status: true,
  });

  useEffect(() => {
    api.get<{ data: Category[] }>('/categories', { params: { per_page: 100 } }).then((res) => {
      setCategories(res.data.data);
      if (res.data.data[0]) setForm((f) => ({ ...f, category_id: String(res.data.data[0].id) }));
    });

    if (isEdit) {
      api.get<{ data: Product }>(`/products/${id}`).then((res) => {
        const p = res.data.data;
        setForm({
          name: p.name,
          sku: p.sku,
          barcode: p.barcode ?? '',
          category_id: String(p.category_id),
          cost_price: String(p.cost_price),
          selling_price: String(p.selling_price),
          tax_rate: String(p.tax_rate),
          quantity: String(p.stock),
          reorder_level: String(p.inventory?.reorder_level ?? 5),
          location: p.inventory?.location ?? '',
          description: p.description ?? '',
          status: p.status,
        });
        setLoading(false);
      });
    }
  }, [id, isEdit]);

  const update = (key: string, value: string | boolean) => setForm((f) => ({ ...f, [key]: value }));

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      const payload = { ...form, category_id: Number(form.category_id) };
      if (isEdit) {
        await api.put(`/products/${id}`, payload);
        toast.success('Product updated.');
      } else {
        await api.post('/products', payload);
        toast.success('Product created.');
      }
      navigate('/products');
    } catch {
      // toast handled by interceptor
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!window.confirm('Delete this product permanently?')) return;
    try {
      await api.delete(`/products/${id}`);
      toast.success('Product deleted.');
      navigate('/products');
    } catch {
      // toast handled
    }
  };

  if (loading) return <Spinner />;

  return (
    <form onSubmit={handleSubmit} className="mx-auto max-w-3xl space-y-4">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <Link to="/products" className="rounded-md p-1.5 text-gray-500 hover:bg-gray-100">
            <ArrowLeft className="size-5" />
          </Link>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{isEdit ? 'Edit Product' : 'New Product'}</h1>
            <p className="text-sm text-gray-500">Product details, pricing, and stock.</p>
          </div>
        </div>
        {isEdit && (
          <Button type="button" variant="danger" onClick={handleDelete}>
            <Trash2 className="size-4" /> Delete
          </Button>
        )}
      </div>

      <Card>
        <CardHeader title="Basic Information" />
        <CardBody>
          <div className="space-y-4">
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <Field label="Product Name">
                <Input required value={form.name} onChange={(e) => update('name', e.target.value)} />
              </Field>
              <Field label="Category">
                <Select required value={form.category_id} onChange={(e) => update('category_id', e.target.value)}>
                  {categories.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.name}
                    </option>
                  ))}
                </Select>
              </Field>
              <Field label="SKU">
                <Input required value={form.sku} onChange={(e) => update('sku', e.target.value)} />
              </Field>
              <Field label="Barcode">
                <Input value={form.barcode} onChange={(e) => update('barcode', e.target.value)} placeholder="6xxxxxxxxxxxx" />
              </Field>
              <Field label="Cost Price">
                <Input required type="number" min="0" step="0.01" value={form.cost_price} onChange={(e) => update('cost_price', e.target.value)} />
              </Field>
              <Field label="Selling Price">
                <Input required type="number" min="0" step="0.01" value={form.selling_price} onChange={(e) => update('selling_price', e.target.value)} />
              </Field>
              <Field label="Tax Rate (%)">
                <Input type="number" min="0" max="100" step="0.01" value={form.tax_rate} onChange={(e) => update('tax_rate', e.target.value)} />
              </Field>
              <Field label="Status">
                <Select value={form.status ? '1' : '0'} onChange={(e) => update('status', e.target.value === '1')}>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </Select>
              </Field>
            </div>

            <Field label="Description">
              <Textarea rows={3} value={form.description} onChange={(e) => update('description', e.target.value)} />
            </Field>
          </div>
        </CardBody>
      </Card>

      <Card>
        <CardHeader title="Inventory" />
        <CardBody>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Field label="Opening Quantity">
              <Input type="number" min="0" value={form.quantity} onChange={(e) => update('quantity', e.target.value)} />
            </Field>
            <Field label="Reorder Level">
              <Input type="number" min="0" value={form.reorder_level} onChange={(e) => update('reorder_level', e.target.value)} />
            </Field>
            <Field label="Location">
              <Input value={form.location} onChange={(e) => update('location', e.target.value)} placeholder="A1 / Store Room" />
            </Field>
          </div>
        </CardBody>
      </Card>

      <div className="flex justify-end gap-2 pb-8">
        <Link to="/products">
          <Button type="button" variant="secondary">Cancel</Button>
        </Link>
        <Button type="submit" loading={saving}>
          {isEdit ? 'Save Changes' : 'Create Product'}
        </Button>
      </div>
    </form>
  );
}