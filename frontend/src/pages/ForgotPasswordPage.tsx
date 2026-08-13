import { useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { ArrowLeft } from 'lucide-react';
import toast from 'react-hot-toast';
import { Button } from '../components/ui/Button';
import { Field, Input } from '../components/ui/Input';

export function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [sent, setSent] = useState(false);

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    setSent(true);
    toast.success('If an account exists, a reset link has been sent.');
  };

  return (
    <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600 p-4">
      <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl">
        <h2 className="text-lg font-semibold text-gray-900">Reset your password</h2>
        <p className="mt-1 text-sm text-gray-500">
          Enter your email and we will send you a password reset link.
        </p>

        {sent ? (
          <div className="mt-6 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-700">
            Check your inbox. If the account exists you will receive instructions shortly.
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <Field label="Email">
              <Input
                type="email"
                required
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="you@example.com"
              />
            </Field>

            <Button type="submit" className="w-full">
              Send reset link
            </Button>
          </form>
        )}

        <Link to="/login" className="mt-6 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-500">
          <ArrowLeft className="size-4" /> Back to login
        </Link>
      </div>
    </div>
  );
}