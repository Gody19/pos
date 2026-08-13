import { create } from 'zustand';
import { api } from '../api/client';
import type { User } from '../api/types';

interface AuthState {
  user: User | null;
  token: string | null;
  loading: boolean;
  setAuth: (token: string, user: User) => void;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  fetchMe: () => Promise<void>;
  clear: () => void;
}

const storedToken = localStorage.getItem('pos_token');
const storedUser = localStorage.getItem('pos_user');

export const useAuthStore = create<AuthState>((set) => ({
  user: storedUser ? (JSON.parse(storedUser) as User) : null,
  token: storedToken,
  loading: false,

  setAuth: (token, user) => {
    localStorage.setItem('pos_token', token);
    localStorage.setItem('pos_user', JSON.stringify(user));
    set({ token, user });
  },

  login: async (email, password) => {
    set({ loading: true });
    try {
      const { data } = await api.post('/login', { email, password, device_name: 'pos-terminal' });
      useAuthStore.getState().setAuth(data.token, data.user);
    } finally {
      set({ loading: false });
    }
  },

  logout: async () => {
    try {
      await api.post('/logout');
    } catch {
      // ignore network errors on logout
    }
    useAuthStore.getState().clear();
  },

  fetchMe: async () => {
    const { data } = await api.get('/me');
    localStorage.setItem('pos_user', JSON.stringify(data.data));
    set({ user: data.data });
  },

  clear: () => {
    localStorage.removeItem('pos_token');
    localStorage.removeItem('pos_user');
    set({ user: null, token: null });
  },
}));