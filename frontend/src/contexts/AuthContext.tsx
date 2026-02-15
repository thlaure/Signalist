import {
  useState,
  useCallback,
  useMemo,
  type ReactNode,
} from 'react';
import { useNavigate } from 'react-router-dom';
import { login as loginApi } from '../api/auth';
import type { LoginInput } from '../types';
import { AuthContext } from './authContextValue';

const AUTH_TOKEN_KEY = 'auth_token';

export function AuthProvider({ children }: { children: ReactNode }) {
  const [token, setToken] = useState<string | null>(() =>
    localStorage.getItem(AUTH_TOKEN_KEY)
  );
  const navigate = useNavigate();

  const login = useCallback(
    async (input: LoginInput) => {
      const response = await loginApi(input);
      localStorage.setItem(AUTH_TOKEN_KEY, response.token);
      setToken(response.token);
    },
    []
  );

  const logout = useCallback(() => {
    localStorage.removeItem(AUTH_TOKEN_KEY);
    setToken(null);
    navigate('/login');
  }, [navigate]);

  const value = useMemo(
    () => ({
      token,
      isAuthenticated: token !== null,
      login,
      logout,
    }),
    [token, login, logout]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
