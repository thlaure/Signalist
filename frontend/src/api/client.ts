import axios, { AxiosError } from 'axios';
import type { ProblemDetails } from '../types';

const apiClient = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Response interceptor for RFC 7807 error handling
apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ProblemDetails>) => {
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
