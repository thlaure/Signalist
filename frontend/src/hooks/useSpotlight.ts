import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import apiClient from '../api/client';
import { getArticles } from '../api/articles';
export function useSpotlight(query: string, open: boolean) {
  const cache = useQueryClient();
  const articles = useQuery({ queryKey: ['spotlight', query], queryFn: () => getArticles({ search: query, limit: 5 }), enabled: open && query.trim().length > 1 });
  const execute = useMutation({ mutationFn: async (command: string): Promise<{ path: string }> => (await apiClient.post('/spotlight', { command })).data,
    onSuccess: async () => { await cache.invalidateQueries({ queryKey: ['categories'] }); await cache.invalidateQueries({ queryKey: ['feeds'] }); } });
  return { articles, execute };
}
