import apiClient from './client';
import type { Article, ApiCollection } from '../types';

export interface ArticleFilters {
  feedId?: string;
  categoryId?: string;
  isRead?: boolean;
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

  const queryString = params.toString();
  const url = queryString ? `/articles?${queryString}` : '/articles';

  const response = await apiClient.get<ApiCollection<Article>>(url);
  return response.data['hydra:member'];
}

export async function getArticle(id: string): Promise<Article> {
  const response = await apiClient.get<Article>(`/articles/${id}`);
  return response.data;
}

export async function markArticleRead(id: string): Promise<Article> {
  const response = await apiClient.patch<Article>(`/articles/${id}/read`);
  return response.data;
}

export async function markArticleUnread(id: string): Promise<Article> {
  const response = await apiClient.patch<Article>(`/articles/${id}/unread`);
  return response.data;
}
