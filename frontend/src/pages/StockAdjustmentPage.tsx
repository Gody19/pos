import { useCallback, useEffect, useState, type FormEvent } from 'react';
import toast from 'react-hot-toast';
import { Search, PackagePlus } from 'lucide-react';
import { api } from '../api/client';
import type { InventoryMovement, Paginated, Product } from '../api/types';
import { Button } from '../components/ui/Button';
import { Card, CardHeader, CardBody } from '../components/ui/Card';
import { Field, Input, Select } from '../components/ui/Input';
import { Badge } from '../components/ui/Badge';
import { Pagination } from '../components/ui/Pagination';
import { Spinner } from '../components/ui/Spinner';
import { DataTable } from '../components/ui/DataTable';
import { formatDate } from '../utils/format';

export function StockAdjustmentPage() {
  const [data, setData] = useState<Paginated<InventoryMovement> | null>(null);
  const [page, setPage] = useState(1);
  const [loading, setLoading] = useState(true);

  const [productId, setProductId] = useState('');
  const [type, setType] = useState<'in' | 'out' | 'adjustment'>('in');
  const [quantity, setQuantity] = useState('');
  const [reason, setReason] = useState('');
  const [products, setProducts] = useState<Product[]>([]);
  const [adjusting, setAdjusting] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    api
      .get<Paginated<InventoryMovement>>('/inventory/movements', { params: { per_page: 15, page } })
      .then((res) => setData(res.data))
      .finally(() => setLoading(false));
  }, [page]);

  useEffect(load, [load]);

  useEffect(() => {
    api.get<{ data: Product[] }>('/products', { params: { per_page: 100, sort_by: 'name' } }).then((res) => {
      setProducts(res.data.data);
    });
  }, []);

  const handleAdjust = async (e: FormEvent) => {
    e.preventDefault();
    setAdjusting(true);
    try {
      await api.post('/inventory/adjust', {
        product_id: Number(productId),
        type,
        quantity: Math.abs(Number(quantity) || 0),
        reason,
      });
      toast.success('Stock adjusted successfully.');
      setQuantity('');
      setReason('');
      load();
    } catch {
      // toast handled
    } finally {
      setAdjusting(false);
    }
  };

  const typeColor = (type: string) => {
    if (type === 'sale') return 'red';
    if (type === 'in') return 'green';
    if (type === 'out') return 'yellow';
    return 'gray';
  };

  return (
    <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
      <div className="xl:col-span-1">
        <Card>
          <CardHeader title="Adjust Stock" subtitle="Increase or decrease quantities." />
          <CardBody>
            <form onSubmit={handleAdjust} className="space-y-4">
              <Field label="Product">
                <div className="relative">
                  <Search className="pointer-events-none absolute left-3 top-2.5 size-4 text-gray-400" />
                  <Select value={productId} onChange={(e) => setProductId(e.target.value)} className="pl-9">
                    <option value="">Select product...</option>
                    {products.map((p) => (
                      <option key={p.id} value={p.id}>
                        {p.name} (stock: {p.stock})
                      </option>
                    ))}
                  </Select>
                </div>
              </Field>

              <div className="grid grid-cols-2 gap-3">
                <Field label="Type">
                  <Select value={type} onChange={(e) => setType(e.target.value as typeof type)}>
                    <option value="in">Stock In</option>
                    <option value="out">Stock Out</option>
                    <option value="adjustment">Adjustment</option>
                  </Select>
                </Field>
                <Field label="Quantity">
                  <Input type="number" min="1" required value={quantity} onChange={(e) => setQuantity(e.target.value)} />
                </Field>
              </div>

              <Field label="Reason">
                <Input value={reason} onChange={(e) => setReason(e.target.value)} placeholder="Delivery, damage, returns..." />
              </Field>

              <Button type="submit" loading={adjusting} className="w-full">
                <PackagePlus className="size-4" /> Apply Adjustment
              </Button>
            </form>
          </CardBody>
        </Card>
      </div>

      <div className="xl:col-span-2">
        <Card>
          <CardHeader title="Inventory Movements" subtitle="All stock transactions" />
          {loading && !data ? (
            <Spinner />
          ) : (
            <DataTable<InventoryMovement>
              columns={[
                { key: 'product', header: 'Product', render: (m) => m.product?.name ?? `#${m.product_id}` },
                { key: 'type', header: 'Type', render: (m) => <Badge color={typeColor(m.type)}>{m.type}</Badge> },
                { key: 'quantity', header: 'Qty', render: (m) => <span className="font-semibold">{m.type === 'out' || m.type === 'sale' ? '-' : '+'}{m.quantity}</span> },
                { key: 'notes', header: 'Notes', render: (m) => m.notes ?? '-' },
                { key: 'created_at', header: 'Date', render: (m) => formatDate(m.created_at) },
              ]}
              rows={data?.data ?? []}
              loading={loading}
            />
          )}
          {data && <Pagination page={page} lastPage={data.meta.last_page} total={data.meta.total} onChange={setPage} />}
        </Card>
      </div>
    </div>
  );
}