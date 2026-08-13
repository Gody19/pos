import { useState } from 'react';
import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import {
  LayoutDashboard,
  ShoppingCart,
  Package,
  Users,
  BarChart3,
  Settings,
  LogOut,
  Store,
  FolderTree,
  Boxes,
  Menu,
  X,
} from 'lucide-react';
import { useAuthStore } from '../store/authStore';
import { cn } from '../utils/cn';

const navSections = [
  {
    label: 'POS',
    items: [
      { to: '/pos', label: 'Point of Sale', icon: ShoppingCart, roles: ['admin', 'manager', 'cashier'] },
    ],
  },
  {
    label: 'Overview',
    items: [
      { to: '/dashboard', label: 'Dashboard', icon: LayoutDashboard, roles: ['admin', 'manager', 'cashier'] },
    ],
  },
  {
    label: 'Inventory',
    items: [
      { to: '/products', label: 'Products', icon: Package, roles: ['admin', 'manager', 'inventory_clerk'] },
      { to: '/categories', label: 'Categories', icon: FolderTree, roles: ['admin', 'manager', 'inventory_clerk'] },
      { to: '/stock-adjustments', label: 'Stock Adjustments', icon: Boxes, roles: ['admin', 'manager', 'inventory_clerk'] },
    ],
  },
  {
    label: 'Customers',
    items: [{ to: '/customers', label: 'Customers', icon: Users, roles: ['admin', 'manager', 'cashier'] }],
  },
  {
    label: 'Reports',
    items: [
      { to: '/reports/daily-sales', label: 'Daily Sales', icon: BarChart3, roles: ['admin', 'manager'] },
      { to: '/reports/monthly-sales', label: 'Monthly Sales', icon: BarChart3, roles: ['admin', 'manager'] },
      { to: '/reports/top-products', label: 'Top Products', icon: BarChart3, roles: ['admin', 'manager'] },
      { to: '/reports/shift-summary', label: 'Shift Summary', icon: BarChart3, roles: ['admin', 'manager'] },
      { to: '/reports/inventory', label: 'Inventory Report', icon: BarChart3, roles: ['admin', 'manager'] },
    ],
  },
  {
    label: 'Admin',
    items: [
      { to: '/admin/users', label: 'Users', icon: Users, roles: ['admin'] },
      { to: '/admin/roles', label: 'Roles & Permissions', icon: Settings, roles: ['admin'] },
      { to: '/admin/settings', label: 'Settings', icon: Settings, roles: ['admin'] },
    ],
  },
];

export function DashboardLayout() {
  const { user, logout } = useAuthStore();
  const navigate = useNavigate();
  const [mobileOpen, setMobileOpen] = useState(false);

  const role = user?.role_name ?? '';

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  const Sidebar = (
    <div className="flex h-full flex-col">
      <div className="flex items-center gap-2 px-5 py-5">
        <div className="flex size-9 items-center justify-center rounded-lg bg-indigo-600 text-white">
          <Store className="size-5" />
        </div>
        <div>
          <p className="text-sm font-bold text-gray-900">{user?.name ?? 'POS'}</p>
          <p className="text-xs capitalize text-gray-500">{role.replace('_', ' ')}</p>
        </div>
      </div>

      <nav className="flex-1 space-y-5 overflow-y-auto px-3 pb-6">
        {navSections
          .map((section) => ({
            ...section,
            items: section.items.filter((item) => item.roles.includes(role)),
          }))
          .filter((section) => section.items.length > 0)
          .map((section) => (
            <div key={section.label}>
              <p className="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400">{section.label}</p>
              <div className="space-y-0.5">
                {section.items.map((item) => (
                  <NavLink
                    key={item.to}
                    to={item.to}
                    onClick={() => setMobileOpen(false)}
                    className={({ isActive }) =>
                      cn(
                        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                        isActive ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100',
                      )
                    }
                  >
                    <item.icon className="size-4.5" />
                    {item.label}
                  </NavLink>
                ))}
              </div>
            </div>
          ))}
      </nav>

      <div className="border-t border-gray-200 p-3">
        <button
          onClick={handleLogout}
          className="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-600"
        >
          <LogOut className="size-4.5" />
          Logout
        </button>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-gray-100">
      {/* Desktop sidebar */}
      <aside className="fixed inset-y-0 left-0 z-40 hidden w-64 border-r border-gray-200 bg-white lg:block">
        {Sidebar}
      </aside>

      {/* Mobile sidebar */}
      {mobileOpen && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="absolute inset-0 bg-gray-900/50" onClick={() => setMobileOpen(false)} />
          <aside className="absolute inset-y-0 left-0 w-64 bg-white shadow-xl">
            <button
              onClick={() => setMobileOpen(false)}
              className="absolute right-3 top-3 rounded-md p-1 text-gray-400 hover:bg-gray-100"
            >
              <X className="size-5" />
            </button>
            {Sidebar}
          </aside>
        </div>
      )}

      <div className="lg:pl-64">
        <header className="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-gray-200 bg-white px-4 lg:hidden">
          <button onClick={() => setMobileOpen(true)} className="rounded-md p-1.5 text-gray-600 hover:bg-gray-100">
            <Menu className="size-6" />
          </button>
          <p className="text-sm font-semibold text-gray-900">POS System</p>
          <div className="size-6" />
        </header>

        <main className="p-4 lg:p-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
}