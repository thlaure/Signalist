import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import apiClient from '../api/client';
export interface Recap { id: string; platform: string; start: string; end: string; status: string; posts: string[]; useAi: boolean; }
interface Crosspost { id: string; platform: string; status: string; posts: string[]; publishedIds: string[]; }
export function useSocial() {
  const cache = useQueryClient();
  const recaps = useQuery({ queryKey: ['social-recaps'], queryFn: async (): Promise<Recap[]> => (await apiClient.get('/social-recaps')).data, refetchInterval: (query) => query.state.data?.some((recap) => recap.status === 'pending') ? 1500 : false });
  const posts = useQuery({ queryKey: ['crossposts'], queryFn: async (): Promise<Crosspost[]> => (await apiClient.get('/crossposts')).data, refetchInterval: (query) => query.state.data?.some((post) => ['pending', 'uncertain'].includes(post.status)) ? 3000 : false });
  const generate = useMutation({ mutationFn: (input: { platform: string; start: string; end: string; useAi: boolean }) => apiClient.post('/social-recaps', input), onSuccess: () => cache.invalidateQueries({ queryKey: ['social-recaps'] }) });
  const edit = useMutation({ mutationFn: ({ id, text }: { id: string; text: string }) => apiClient.put(`/social-recaps/${id}`, { text }), onSuccess: () => cache.invalidateQueries({ queryKey: ['social-recaps'] }) });
  const remove = useMutation({ mutationFn: (id: string) => apiClient.delete(`/social-recaps/${id}`), onSuccess: () => cache.invalidateQueries({ queryKey: ['social-recaps'] }) });
  const publish = useMutation({ mutationFn: (input: { accountId: string; text: string; confirmed: true }) => apiClient.post('/crossposts', input), onSuccess: () => cache.invalidateQueries({ queryKey: ['crossposts'] }) });
  return { recaps, posts, generate, edit, remove, publish };
}
