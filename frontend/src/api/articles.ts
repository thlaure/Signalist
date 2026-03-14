import apiClient from './client';
import type { Article, PaginatedArticles } from '../types';

export interface ArticleFilters {
  feedId?: string;
  categoryId?: string;
  isRead?: boolean;
  search?: string;
  page?: number;
  limit?: number;
}

export async function getArticles(filters?: ArticleFilters): Promise<PaginatedArticles> {
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
  if (filters?.page) {
    params.append('page', String(filters.page));
  }
  if (filters?.limit) {
    params.append('limit', String(filters.limit));
  }

  const queryString = params.toString();
  const url = queryString ? `/articles?${queryString}` : '/articles';

  const response = await apiClient.get<PaginatedArticles>(url);
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
