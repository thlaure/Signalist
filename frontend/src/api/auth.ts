import apiClient from './client';
import type {
  LoginInput,
  LoginResponse,
  RegisterInput,
  RegisterResponse,
} from '../types';

export async function login(input: LoginInput): Promise<LoginResponse> {
  const response = await apiClient.post<LoginResponse>('/auth/login', input);
  return response.data;
}

export async function register(
  input: RegisterInput
): Promise<RegisterResponse> {
  const response = await apiClient.post<RegisterResponse>(
    '/auth/register',
    input
  );
  return response.data;
}
