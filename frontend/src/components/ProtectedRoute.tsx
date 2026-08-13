import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import { Spinner } from './ui/Spinner';

export function ProtectedRoute({ roles }: { roles?: string[] }) {
  const { user, token } = useAuthStore();
  const location = useLocation();

  if (!token || !user) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  if (roles && !roles.includes(user.role_name)) {
    return <Navigate to="/dashboard" replace />;
  }

  if (user && !user.active_shift && !location.pathname.startsWith('/shift')) {
    if (['admin', 'manager', 'cashier'].includes(user.role_name)) {
      return <Navigate to="/shift/open" replace />;
    }
  }

  return <Outlet />;
}

export function OpenShiftGuard() {
  const { user } = useAuthStore();

  if (user && user.active_shift && user.role_name !== 'inventory_clerk') {
    return <Navigate to="/pos" replace />;
  }

  return <Outlet />;
}

export function LoadingScreen() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-gray-100">
      <Spinner label="Loading POS System..." />
    </div>
  );
}