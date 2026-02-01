import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  getFeeds,
  getFeed,
  addFeed,
  updateFeed,
  deleteFeed,
} from '../api/feeds';
import type { AddFeedInput, UpdateFeedInput } from '../types';

export const FEEDS_QUERY_KEY = ['feeds'];

export function useFeeds() {
  return useQuery({
    queryKey: FEEDS_QUERY_KEY,
    queryFn: getFeeds,
  });
}

export function useFeed(id: string) {
  return useQuery({
    queryKey: [...FEEDS_QUERY_KEY, id],
    queryFn: () => getFeed(id),
    enabled: !!id,
  });
}

export function useAddFeed() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (input: AddFeedInput) => addFeed(input),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: FEEDS_QUERY_KEY });
    },
  });
}

export function useUpdateFeed() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, input }: { id: string; input: UpdateFeedInput }) =>
      updateFeed(id, input),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: FEEDS_QUERY_KEY });
    },
  });
}

export function useDeleteFeed() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => deleteFeed(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: FEEDS_QUERY_KEY });
    },
  });
}
