import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  getBookmarks,
  getBookmark,
  createBookmark,
  deleteBookmark,
} from '../api/bookmarks';
import type { CreateBookmarkInput } from '../types';

export const BOOKMARKS_QUERY_KEY = ['bookmarks'];

export function useBookmarks() {
  return useQuery({
    queryKey: BOOKMARKS_QUERY_KEY,
    queryFn: getBookmarks,
  });
}

export function useBookmark(id: string) {
  return useQuery({
    queryKey: [...BOOKMARKS_QUERY_KEY, id],
    queryFn: () => getBookmark(id),
    enabled: !!id,
  });
}

export function useCreateBookmark() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (input: CreateBookmarkInput) => createBookmark(input),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: BOOKMARKS_QUERY_KEY });
    },
  });
}

export function useDeleteBookmark() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => deleteBookmark(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: BOOKMARKS_QUERY_KEY });
    },
  });
}
