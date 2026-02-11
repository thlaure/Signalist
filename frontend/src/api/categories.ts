import apiClient from './client';
import type {
  Category,
  CreateCategoryInput,
  UpdateCategoryInput,
} from '../types';

export async function getCategories(): Promise<Category[]> {
  const response = await apiClient.get<Category[]>('/categories');
  return response.data;
}

export async function getCategory(id: string): Promise<Category> {
  const response = await apiClient.get<Category>(`/categories/${id}`);
  return response.data;
}

export async function createCategory(
  input: CreateCategoryInput
): Promise<Category> {
  const response = await apiClient.post<Category>('/categories', input);
  return response.data;
}

export async function updateCategory(
  id: string,
  input: UpdateCategoryInput
): Promise<Category> {
  const response = await apiClient.put<Category>(`/categories/${id}`, input);
  return response.data;
}

export async function deleteCategory(id: string): Promise<void> {
  await apiClient.delete(`/categories/${id}`);
}
