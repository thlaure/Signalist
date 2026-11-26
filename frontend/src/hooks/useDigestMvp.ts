import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import apiClient from '../api/client';
export interface Source { id: string; title: string; url: string; status: string; lastError: string | null; }
export interface Sources { items: Source[]; limit: number; selectionRequired: boolean; }
export interface Digest { id: string; period: string; status: string; sentAt: string | null; items: { title: string; url: string; source: string; summary: string }[]; }
export function useSources() {
  const client = useQueryClient();
  const refresh = () => { void client.invalidateQueries({ queryKey: ['sources'] }); void client.invalidateQueries({ queryKey: ['billing'] }); };
  const list = useQuery({ queryKey: ['sources'], queryFn: async (): Promise<Sources> => (await apiClient.get('/sources')).data, refetchInterval: 30000 });
  const add = useMutation({ mutationFn: async (url: string) => apiClient.post('/sources', { url }), onSuccess: refresh });
  const remove = useMutation({ mutationFn: async (id: string) => apiClient.delete(`/sources/${id}`), onSuccess: refresh });
  const select = useMutation({ mutationFn: async (ids: string[]) => apiClient.put('/sources/selection', { ids }), onSuccess: refresh });
  return { list, add, remove, select };
}
export function useDigests() {
  return useQuery({ queryKey: ['digests'], queryFn: async (): Promise<{ items: Digest[]; configured: boolean; nextRunAt: string }> => (await apiClient.get('/digests')).data, refetchInterval: 30000 });
}
export function useDigest(id: string | undefined) {
  return useQuery({ queryKey: ['digests', id], queryFn: async (): Promise<Digest> => (await apiClient.get(`/digests/${id}`)).data, enabled: !!id });
}
