import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import apiClient from '../api/client';
import type { Article } from '../types';
export interface Tag { id: string; name: string; }
export function useTags(selectedId?: string) {
  const cache = useQueryClient();
  const tags = useQuery({ queryKey: ['tags'], queryFn: async (): Promise<Tag[]> => (await apiClient.get('/tags')).data });
  const articles = useQuery({ queryKey: ['tag-articles', selectedId], enabled: !!selectedId, queryFn: async (): Promise<Article[]> => (await apiClient.get(`/tags/${selectedId}/articles`)).data });
  const invalidate = async () => { await Promise.all([cache.invalidateQueries({ queryKey: ['tags'] }), cache.invalidateQueries({ queryKey: ['tag-articles'] }), cache.invalidateQueries({ queryKey: ['articles'] }), cache.invalidateQueries({ queryKey: ['article'] })]); };
  const save = useMutation({ mutationFn: ({ id, name }: { id?: string; name: string }) => id ? apiClient.put(`/tags/${id}`, { name }) : apiClient.post('/tags', { name }), onSuccess: invalidate });
  const remove = useMutation({ mutationFn: (id: string) => apiClient.delete(`/tags/${id}`), onSuccess: invalidate });
  const assign = useMutation({ mutationFn: ({ id, names }: { id: string; names: string[] }) => apiClient.put(`/articles/${id}/tags`, { tags: names }), onSuccess: invalidate });
  return { tags, articles, save, remove, assign };
}
