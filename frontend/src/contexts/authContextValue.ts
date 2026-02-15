import { createContext } from 'react';
import type { LoginInput } from '../types';

export interface AuthContextValue {
  token: string | null;
  isAuthenticated: boolean;
  login: (input: LoginInput) => Promise<void>;
  logout: () => void;
}

export const AuthContext = createContext<AuthContextValue | null>(null);
