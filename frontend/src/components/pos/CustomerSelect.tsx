import { useState } from 'react';
import { Search, UserPlus, X } from 'lucide-react';
import { api } from '../../api/client';
import type { Customer } from '../../api/types';
import { Button } from '../ui/Button';
import { Input } from '../ui/Input';
import { formatMoney } from '../../utils/format';
import { cn } from '../../utils/cn';

export function CustomerSelect({
  customer,
  onSelect,
  onClear,
}: {
  customer: Customer | null;
  onSelect: (customer: Customer) => void;
  onClear: () => void;
}) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<Customer[]>([]);
  const [open, setOpen] = useState(false);

  const search = async (term: string) => {
    setQuery(term);
    if (!term.trim()) {
      setResults([]);
      return;
    }
    const { data } = await api.get<{ data: Customer[] }>('/customers/search', { params: { q: term, limit: 6 } });
    setResults(data.data);
  };

  return (
    <div className="relative">
      {customer ? (
        <div className="flex items-center justify-between rounded-lg bg-indigo-50 px-3 py-2">
          <div>
            <p className="text-sm font-semibold text-indigo-900">{customer.full_name}</p>
            <p className="text-xs text-indigo-600">
              {customer.phone ?? customer.email} · {customer.loyalty_points} pts
            </p>
          </div>
          <button onClick={onClear} className="rounded-md p-1 text-indigo-400 hover:bg-indigo-100 hover:text-indigo-600">
            <X className="size-4" />
          </button>
        </div>
      ) : (
        <Button type="button" variant="secondary" className="w-full justify-start" onClick={() => setOpen((v) => !v)}>
          <Search className="size-4" /> Select customer
        </Button>
      )}

      {open && !customer && (
        <div className="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg">
          <div className="border-b border-gray-100 p-2">
            <Input value={query} onChange={(e) => search(e.target.value)} placeholder="Search by name, phone, email..." autoFocus />
          </div>
          <div className="max-h-52 overflow-y-auto p-1">
            {results.length === 0 ? (
              <p className="px-3 py-3 text-sm text-gray-500">
                No customers found.
                <a href="/customers" className="ml-1 inline-flex items-center gap-1 font-medium text-indigo-600">
                  <UserPlus className="size-3.5" /> Add customer
                </a>
              </p>
            ) : (
              results.map((c) => (
                <button
                  key={c.id}
                  onClick={() => {
                    onSelect(c);
                    setOpen(false);
                    setQuery('');
                  }}
                  className={cn('flex w-full items-center justify-between rounded-md px-3 py-2 text-left hover:bg-gray-50')}
                >
                  <div>
                    <p className="text-sm font-medium text-gray-900">{c.full_name}</p>
                    <p className="text-xs text-gray-500">{c.phone ?? c.email}</p>
                  </div>
                  <span className="text-xs font-semibold text-amber-600">{c.loyalty_points} pts</span>
                </button>
              ))
            )}
          </div>
        </div>
      )}
    </div>
  );
}