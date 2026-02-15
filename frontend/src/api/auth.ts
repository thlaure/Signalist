import apiClient from './client';
import type { LoginInput, LoginResponse } from '../types';

export async function login(input: LoginInput): Promise<LoginResponse> {
  const response = await apiClient.post<LoginResponse>('/auth/login', input);
  return response.data;
}
