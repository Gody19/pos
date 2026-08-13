import axios from 'axios';
import { toast } from 'react-hot-toast';

const API_URL = import.meta.env.VITE_API_URL ?? '/api/v1';

export const api = axios.create({
  baseURL: API_URL,
  headers: { Accept: 'application/json' },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('pos_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status;

    if (status === 401) {
      localStorage.removeItem('pos_token');
      localStorage.removeItem('pos_user');
      if (!window.location.pathname.startsWith('/login')) {
        window.location.href = '/login';
      }
    }

    const message =
      error.response?.data?.message ??
      error.response?.data?.errors?.[Object.keys(error.response?.data?.errors ?? {})[0]]?.[0] ??
      'Something went wrong. Please try again.';

    toast.error(message);

    return Promise.reject(error);
  }
);

export function getErrorMessage(error: unknown): string {
  const e = error as { response?: { data?: { message?: string } } };
  return e.response?.data?.message ?? 'Something went wrong.';
}