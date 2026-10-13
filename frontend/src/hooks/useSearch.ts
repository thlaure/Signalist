import { useMutation, useQuery } from '@tanstack/react-query';
import { useState } from 'react';
import apiClient from '../api/client';
import type { Article } from '../types';
export type SearchMode = 'fulltext' | 'semantic' | 'hybrid' | 'question';
interface SearchResult { id: string; status: 'pending' | 'complete' | 'failed'; items: Article[]; answer?: string | null; }
export function useSearch() {
  const [id, setId] = useState<string | null>(null);
  const create = useMutation({ mutationFn: async (input: { query: string; mode: SearchMode }): Promise<SearchResult> => (await apiClient.post('/search', input)).data, onSuccess: (data) => setId(data.id) });
  const result = useQuery({ queryKey: ['search', id], queryFn: async (): Promise<SearchResult> => (await apiClient.get(`/search/${id}`)).data, enabled: !!id,
    refetchInterval: (query) => query.state.data?.status === 'pending' ? 1000 : false });
  return { create, result };
}
