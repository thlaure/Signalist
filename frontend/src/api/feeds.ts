import apiClient from './client';
import type {
  Feed,
  AddFeedInput,
  UpdateFeedInput,
} from '../types';

export interface FeedFilters {
  categoryId?: string;
}

export async function getFeeds(filters?: FeedFilters): Promise<Feed[]> {
  const params = new URLSearchParams();
  if (filters?.categoryId) {
    params.append('categoryId', filters.categoryId);
  }
  const query = params.toString();
  const url = query ? `/feeds?${query}` : '/feeds';
  const response = await apiClient.get<Feed[]>(url);
  return response.data;
}

export async function getFeed(id: string): Promise<Feed> {
  const response = await apiClient.get<Feed>(`/feeds/${id}`);
  return response.data;
}

export async function addFeed(input: AddFeedInput): Promise<Feed> {
  const response = await apiClient.post<Feed>('/feeds', input);
  return response.data;
}

export async function updateFeed(
  id: string,
  input: UpdateFeedInput
): Promise<Feed> {
  const response = await apiClient.put<Feed>(`/feeds/${id}`, input);
  return response.data;
}

export async function deleteFeed(id: string): Promise<void> {
  await apiClient.delete(`/feeds/${id}`);
}
