import { Minus, Plus, Trash2, Percent } from 'lucide-react';
import type { CartItem } from '../../store/posStore';
import { formatMoney } from '../../utils/format';

export function CartPanel({
  items,
  onInc,
  onDec,
  onRemove,
  onDiscount,
}: {
  items: CartItem[];
  onInc: (productId: number) => void;
  onDec: (productId: number) => void;
  onRemove: (productId: number) => void;
  onDiscount: (productId: number, discount: number) => void;
}) {
  if (items.length === 0) {
    return (
      <div className="flex h-full flex-col items-center justify-center py-12 text-center">
        <div className="mb-3 flex size-16 items-center justify-center rounded-full bg-gray-100">
          <Minus className="size-8 text-gray-300" />
        </div>
        <p className="text-sm font-medium text-gray-500">Cart is empty</p>
        <p className="mt-1 text-xs text-gray-400">Scan or click a product to add it to the cart.</p>
      </div>
    );
  }

  return (
    <div className="flex h-full flex-col divide-y divide-gray-100">
      {items.map((item) => (
        <div key={item.product.id} className="flex items-center gap-3 px-3 py-2.5">
          <div className="min-w-0 flex-1">
            <p className="truncate text-sm font-medium text-gray-900">{item.product.name}</p>
            <p className="text-xs text-gray-500">
              {formatMoney(item.product.selling_price)}
              {item.product.tax_rate > 0 && ` · tax ${item.product.tax_rate}%`}
            </p>

            <div className="mt-1 flex items-center gap-2">
              <button
                onClick={() => onDec(item.product.id)}
                className="flex size-6 items-center justify-center rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200"
              >
                <Minus className="size-3.5" />
              </button>
              <span className="w-8 text-center text-sm font-semibold">{item.quantity}</span>
              <button
                onClick={() => onInc(item.product.id)}
                disabled={item.quantity >= item.product.stock}
                className="flex size-6 items-center justify-center rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 disabled:opacity-40"
              >
                <Plus className="size-3.5" />
              </button>

              <div className="ml-2 flex items-center gap-1 rounded-md bg-gray-100 px-1.5 py-0.5">
                <Percent className="size-3 text-gray-400" />
                <input
                  type="number"
                  min="0"
                  max={item.product.selling_price}
                  value={item.discount || ''}
                  placeholder="0"
                  onChange={(e) => onDiscount(item.product.id, Number(e.target.value) || 0)}
                  className="w-14 bg-transparent text-xs font-medium text-gray-700 outline-none"
                />
              </div>
            </div>
          </div>

          <div className="text-right">
            <p className="text-sm font-bold text-gray-900">{formatMoney(item.total)}</p>
            {item.discount > 0 && <p className="text-xs text-red-500">-{formatMoney(item.discount * item.quantity)}</p>}
          </div>

          <button onClick={() => onRemove(item.product.id)} className="rounded-md p-1 text-gray-400 hover:bg-red-50 hover:text-red-500">
            <Trash2 className="size-4" />
          </button>
        </div>
      ))}
    </div>
  );
}