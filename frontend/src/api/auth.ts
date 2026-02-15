import apiClient from './client';
import type {
  LoginInput,
  LoginResponse,
  RegisterInput,
  RegisterResponse,
  ResendVerificationInput,
  ResendVerificationResponse,
  VerifyEmailInput,
  VerifyEmailResponse,
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

export async function verifyEmail(
  input: VerifyEmailInput
): Promise<VerifyEmailResponse> {
  const response = await apiClient.post<VerifyEmailResponse>(
    '/auth/verify-email',
    input
  );
  return response.data;
}

export async function resendVerification(
  input: ResendVerificationInput
): Promise<ResendVerificationResponse> {
  const response = await apiClient.post<ResendVerificationResponse>(
    '/auth/resend-verification',
    input
  );
  return response.data;
}
