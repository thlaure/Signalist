import apiClient from './client';
import type {
  Feed,
  AddFeedInput,
  UpdateFeedInput,
  ApiCollection,
} from '../types';

export async function getFeeds(): Promise<Feed[]> {
  const response = await apiClient.get<ApiCollection<Feed>>('/feeds');
  return response.data['hydra:member'];
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
