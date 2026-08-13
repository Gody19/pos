import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from './Button';

interface PaginationProps {
  page: number;
  lastPage: number;
  total: number;
  onChange: (page: number) => void;
}

export function Pagination({ page, lastPage, total, onChange }: PaginationProps) {
  if (lastPage <= 1) return null;

  return (
    <div className="flex items-center justify-between border-t border-gray-200 px-4 py-3">
      <p className="text-sm text-gray-500">
        Page <span className="font-medium">{page}</span> of <span className="font-medium">{lastPage}</span> · {total} records
      </p>
      <div className="flex gap-1">
        <Button variant="secondary" size="sm" disabled={page <= 1} onClick={() => onChange(page - 1)}>
          <ChevronLeft className="size-4" /> Prev
        </Button>
        <Button variant="secondary" size="sm" disabled={page >= lastPage} onClick={() => onChange(page + 1)}>
          Next <ChevronRight className="size-4" />
        </Button>
      </div>
    </div>
  );
}