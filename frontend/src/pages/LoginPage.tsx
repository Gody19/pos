import { useState, type FormEvent } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Store } from 'lucide-react';
import toast from 'react-hot-toast';
import { useAuthStore } from '../store/authStore';
import { Button } from '../components/ui/Button';
import { Field, Input } from '../components/ui/Input';

export function LoginPage() {
  const { login, loading } = useAuthStore();
  const navigate = useNavigate();
  const location = useLocation();
  const [email, setEmail] = useState('admin@pos.com');
  const [password, setPassword] = useState('password');

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    try {
      await login(email, password);
      toast.success('Welcome back!');
      const from = (location.state as { from?: { pathname: string } } | null)?.from?.pathname ?? '/dashboard';
      navigate(from, { replace: true });
    } catch {
      // error toast shown by interceptor
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600 p-4">
      <div className="w-full max-w-md">
        <div className="mb-6 flex items-center justify-center gap-2 text-white">
          <div className="flex size-10 items-center justify-center rounded-lg bg-white/20">
            <Store className="size-6" />
          </div>
          <h1 className="text-2xl font-bold">POS System</h1>
        </div>

        <div className="rounded-2xl bg-white p-8 shadow-2xl">
          <h2 className="text-lg font-semibold text-gray-900">Sign in to your account</h2>
          <p className="mt-1 text-sm text-gray-500">Enter your credentials to continue.</p>

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <Field label="Email">
              <Input
                type="email"
                required
                autoComplete="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="you@example.com"
              />
            </Field>

            <Field label="Password">
              <Input
                type="password"
                required
                autoComplete="current-password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="••••••••"
              />
            </Field>

            <div className="flex items-center justify-between">
              <label className="flex items-center text-sm text-gray-600">
                <input type="checkbox" className="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                Remember me
              </label>
              <Link to="/forgot-password" className="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Forgot password?
              </Link>
            </div>

            <Button type="submit" loading={loading} className="w-full" size="lg">
              Sign in
            </Button>
          </form>

          <div className="mt-6 rounded-lg bg-gray-50 p-3 text-xs text-gray-500">
            <p className="font-semibold text-gray-700">Demo accounts (password: password)</p>
            <p>admin@pos.com · manager@pos.com · cashier1@pos.com · clerk@pos.com</p>
          </div>
        </div>
      </div>
    </div>
  );
}