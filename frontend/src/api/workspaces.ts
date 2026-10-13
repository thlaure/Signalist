import apiClient from './client';
import type { Article, Category, Feed, AddFeedInput, CreateCategoryInput, UpdateFeedInput } from '../types';
export type MemberRole = 'admin' | 'curator' | 'reader';
export interface Workspace { id: string; name: string; role: MemberRole; }
export interface WorkspaceMember { id: string; name: string; email?: string | null; role: MemberRole; }
export interface SharedBookmark { id: string; article: Article; curator: string | null; annotations: { id: string; body: string; author: string | null; createdAt: string }[]; }
const base = (id: string) => `/organizations/${id}`;
export const workspaceApi = {
  list: async (): Promise<Workspace[]> => (await apiClient.get('/organizations')).data,
  create: async (name: string): Promise<Workspace> => (await apiClient.post('/organizations', { name })).data,
  get: async (id: string): Promise<Workspace> => (await apiClient.get(base(id))).data,
  members: async (id: string): Promise<WorkspaceMember[]> => (await apiClient.get(`${base(id)}/members`)).data,
  invite: async (id: string, email: string, role: MemberRole): Promise<void> => { await apiClient.post(`${base(id)}/invitations`, { email, role }); },
  role: async (id: string, memberId: string, role: MemberRole): Promise<void> => { await apiClient.patch(`${base(id)}/members/${memberId}`, { role }); },
  remove: async (id: string, memberId: string): Promise<void> => { await apiClient.delete(`${base(id)}/members/${memberId}`); },
  accept: async (id: string, signature: string): Promise<Workspace> => (await apiClient.post(`/organizations/invitations/${encodeURIComponent(id)}/accept`, null, { params: { signature } })).data,
  articles: async (id: string, page: number): Promise<Article[]> => (await apiClient.get(`${base(id)}/articles`, { params: { page } })).data,
  article: async (id: string, articleId: string): Promise<Article> => (await apiClient.get(`${base(id)}/articles/${articleId}`)).data,
  read: async (id: string, articleId: string, isRead: boolean): Promise<void> => { await apiClient.patch(`${base(id)}/articles/${articleId}/read`, { isRead }); },
  categories: async (id: string): Promise<Category[]> => (await apiClient.get(`${base(id)}/categories`)).data,
  createCategory: async (id: string, input: CreateCategoryInput): Promise<void> => { await apiClient.post(`${base(id)}/categories`, input); },
  updateCategory: async (id: string, categoryId: string, input: CreateCategoryInput): Promise<void> => { await apiClient.put(`${base(id)}/categories/${categoryId}`, input); },
  deleteCategory: async (id: string, categoryId: string): Promise<void> => { await apiClient.delete(`${base(id)}/categories/${categoryId}`); },
  feeds: async (id: string): Promise<Feed[]> => (await apiClient.get(`${base(id)}/feeds`)).data,
  createFeed: async (id: string, input: AddFeedInput): Promise<void> => { await apiClient.post(`${base(id)}/feeds`, input); },
  updateFeed: async (id: string, feedId: string, input: UpdateFeedInput): Promise<void> => { await apiClient.put(`${base(id)}/feeds/${feedId}`, input); },
  deleteFeed: async (id: string, feedId: string): Promise<void> => { await apiClient.delete(`${base(id)}/feeds/${feedId}`); },
  bookmarks: async (id: string): Promise<SharedBookmark[]> => (await apiClient.get(`${base(id)}/bookmarks`)).data,
  bookmark: async (id: string, articleId: string): Promise<void> => { await apiClient.post(`${base(id)}/bookmarks`, { articleId }); },
  annotate: async (id: string, bookmarkId: string, body: string): Promise<void> => { await apiClient.post(`${base(id)}/bookmarks/${bookmarkId}/annotations`, { body }); },
  removeBookmark: async (id: string, bookmarkId: string): Promise<void> => { await apiClient.delete(`${base(id)}/bookmarks/${bookmarkId}`); },
  activity: async (id: string): Promise<{ action: string; actor: string | null; createdAt: string }[]> => (await apiClient.get(`${base(id)}/activity`)).data,
};
