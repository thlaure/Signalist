import axios, { AxiosError } from 'axios';
import type { ProblemDetails } from '../types';

const apiClient = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

const AUTH_TOKEN_KEY = 'auth_token';

// Request interceptor to inject JWT token
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem(AUTH_TOKEN_KEY);
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor for RFC 7807 error handling
apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ProblemDetails>) => {
    // Clear token and redirect on 401 (except for login endpoint)
    if (
      error.response?.status === 401 &&
      !error.config?.url?.includes('/auth/login')
    ) {
      localStorage.removeItem(AUTH_TOKEN_KEY);
      window.location.href = '/login';
    }

    if (error.response?.data?.type) {
      // RFC 7807 Problem Details response
      const problem = error.response.data;
      const enhancedError = new Error(problem.detail) as Error & {
        problem: ProblemDetails;
      };
      enhancedError.problem = problem;
      return Promise.reject(enhancedError);
    }
    return Promise.reject(error);
  }
);

export default apiClient;

// Helper to check if error is a ProblemDetails error
export function isProblemError(
  error: unknown
): error is Error & { problem: ProblemDetails } {
  return (
    error instanceof Error &&
    'problem' in error &&
    typeof (error as { problem?: unknown }).problem === 'object'
  );
}
