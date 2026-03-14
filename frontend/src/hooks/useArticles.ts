import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  getArticles,
  getArticle,
  markArticleRead,
  markArticleUnread,
  type ArticleFilters,
} from '../api/articles';
import type { Article } from '../types';

export const ARTICLES_QUERY_KEY = ['articles'];

export function useArticles(filters?: ArticleFilters) {
  return useQuery({
    queryKey: [...ARTICLES_QUERY_KEY, filters],
    queryFn: () => getArticles(filters),
  });
}

export function useArticle(id: string) {
  return useQuery({
    queryKey: [...ARTICLES_QUERY_KEY, id],
    queryFn: () => getArticle(id),
    enabled: !!id,
  });
}

function updateArticleInCache(queryClient: ReturnType<typeof useQueryClient>, updated: Article) {
  queryClient.setQueriesData<Article[]>(
    { queryKey: ARTICLES_QUERY_KEY },
    (old) => old?.map((a) => (a.id === updated.id ? updated : a)),
  );
}

export function useMarkArticleRead() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => markArticleRead(id),
    onSuccess: (updated) => updateArticleInCache(queryClient, updated),
  });
}

export function useMarkArticleUnread() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => markArticleUnread(id),
    onSuccess: (updated) => updateArticleInCache(queryClient, updated),
  });
}

export function useToggleArticleRead() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, isRead }: { id: string; isRead: boolean }) =>
      isRead ? markArticleUnread(id) : markArticleRead(id),
    onSuccess: (updated) => updateArticleInCache(queryClient, updated),
  });
}
