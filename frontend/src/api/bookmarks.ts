import apiClient from './client';
import type { Bookmark, CreateBookmarkInput, ApiCollection } from '../types';

export async function getBookmarks(): Promise<Bookmark[]> {
  const response = await apiClient.get<ApiCollection<Bookmark>>('/bookmarks');
  return response.data.member;
}

export async function getBookmark(id: string): Promise<Bookmark> {
  const response = await apiClient.get<Bookmark>(`/bookmarks/${id}`);
  return response.data;
}

export async function createBookmark(
  input: CreateBookmarkInput
): Promise<Bookmark> {
  const response = await apiClient.post<Bookmark>('/bookmarks', input);
  return response.data;
}

export async function deleteBookmark(id: string): Promise<void> {
  await apiClient.delete(`/bookmarks/${id}`);
}
