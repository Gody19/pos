import { cn } from '../../utils/cn';

type BadgeColor = 'gray' | 'green' | 'red' | 'yellow' | 'indigo' | 'blue';

const colors: Record<BadgeColor, string> = {
  gray: 'bg-gray-100 text-gray-700 ring-gray-200',
  green: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  red: 'bg-red-50 text-red-700 ring-red-200',
  yellow: 'bg-amber-50 text-amber-700 ring-amber-200',
  indigo: 'bg-indigo-50 text-indigo-700 ring-indigo-200',
  blue: 'bg-sky-50 text-sky-700 ring-sky-200',
};

export function Badge({ color = 'gray', children, className }: { color?: BadgeColor; children: React.ReactNode; className?: string }) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset',
        colors[color],
        className,
      )}
    >
      {children}
    </span>
  );
}

export function statusColor(status: string): BadgeColor {
  switch (status) {
    case 'completed':
    case 'open':
    case 'active':
      return 'green';
    case 'cancelled':
    case 'closed':
    case 'refunded':
      return 'red';
    case 'pending':
      return 'yellow';
    case 'low':
      return 'yellow';
    case 'out':
      return 'red';
    default:
      return 'gray';
  }
}