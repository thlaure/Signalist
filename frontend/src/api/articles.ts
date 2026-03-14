import apiClient from './client';
import type { Article } from '../types';

export interface ArticleFilters {
  feedId?: string;
  categoryId?: string;
  isRead?: boolean;
  search?: string;
}

export async function getArticles(filters?: ArticleFilters): Promise<Article[]> {
  const params = new URLSearchParams();

  if (filters?.feedId) {
    params.append('feedId', filters.feedId);
  }
  if (filters?.categoryId) {
    params.append('categoryId', filters.categoryId);
  }
  if (filters?.isRead !== undefined) {
    params.append('isRead', String(filters.isRead));
  }
  if (filters?.search) {
    params.append('search', filters.search);
  }

  const queryString = params.toString();
  const url = queryString ? `/articles?${queryString}` : '/articles';

  const response = await apiClient.get<Article[]>(url);
  return response.data;
}

export async function getArticle(id: string): Promise<Article> {
  const response = await apiClient.get<Article>(`/articles/${id}`);
  return response.data;
}

const PATCH_HEADERS = { headers: { 'Content-Type': 'application/merge-patch+json' } };

export async function markArticleRead(id: string): Promise<Article> {
  const response = await apiClient.patch<Article>(`/articles/${id}/read`, {}, PATCH_HEADERS);
  return response.data;
}

export async function markArticleUnread(id: string): Promise<Article> {
  const response = await apiClient.patch<Article>(`/articles/${id}/unread`, {}, PATCH_HEADERS);
  return response.data;
}
