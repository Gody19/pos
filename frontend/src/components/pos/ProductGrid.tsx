import { useMemo } from 'react';
import { PackageSearch } from 'lucide-react';
import type { Category, Product } from '../../api/types';
import { EmptyState } from '../ui/Spinner';
import { formatMoney } from '../../utils/format';
import { cn } from '../../utils/cn';

export function ProductGrid({
  products,
  categories,
  selectedCategory,
  onSelectCategory,
  onAdd,
  loading,
}: {
  products: Product[];
  categories: Category[];
  selectedCategory: number | 'all';
  onSelectCategory: (id: number | 'all') => void;
  onAdd: (product: Product) => void;
  loading: boolean;
}) {
  const grid = useMemo(() => products, [products]);

  return (
    <div className="flex h-full flex-col">
      <div className="flex gap-2 overflow-x-auto pb-3">
        <button
          onClick={() => onSelectCategory('all')}
          className={cn(
            'shrink-0 rounded-full px-3 py-1.5 text-sm font-medium transition-colors',
            selectedCategory === 'all' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50',
          )}
        >
          All
        </button>
        {categories.map((category) => (
          <button
            key={category.id}
            onClick={() => onSelectCategory(category.id)}
            className={cn(
              'shrink-0 rounded-full px-3 py-1.5 text-sm font-medium transition-colors',
              selectedCategory === category.id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50',
            )}
          >
            {category.name}
          </button>
        ))}
      </div>

      <div className="flex-1 overflow-y-auto pb-4">
        {loading ? (
          <div className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
            {Array.from({ length: 8 }).map((_, i) => (
              <div key={i} className="h-28 animate-pulse rounded-xl bg-gray-200" />
            ))}
          </div>
        ) : grid.length === 0 ? (
          <EmptyState
            icon={<PackageSearch className="size-10" />}
            title="No products found"
            description="Try a different search term or category."
          />
        ) : (
          <div className="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
            {grid.map((product) => {
              const disabled = product.is_out_of_stock;

              return (
                <button
                  key={product.id}
                  disabled={disabled}
                  onClick={() => onAdd(product)}
                  className={cn(
                    'group relative flex flex-col rounded-xl bg-white p-3 text-left shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-300',
                    disabled && 'cursor-not-allowed opacity-50 hover:ring-gray-200',
                  )}
                >
                  {product.is_low_stock && (
                    <span className="absolute right-2 top-2 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">
                      {product.stock} left
                    </span>
                  )}
                  <div className="mb-2 flex size-12 items-center justify-center rounded-lg bg-gray-100 text-lg font-bold text-gray-400">
                    {product.image ? (
                      <img src={product.image} alt={product.name} className="size-12 rounded-lg object-cover" />
                    ) : (
                      product.name.charAt(0)
                    )}
                  </div>
                  <p className="line-clamp-2 text-sm font-semibold text-gray-900">{product.name}</p>
                  <p className="mt-1 text-sm font-bold text-indigo-600">{formatMoney(product.selling_price)}</p>
                  {product.is_out_of_stock && <p className="mt-1 text-xs font-medium text-red-600">Out of stock</p>}
                </button>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}