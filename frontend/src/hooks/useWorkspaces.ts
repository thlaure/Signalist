import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { workspaceApi } from '../api/workspaces';
export function useWorkspaces() {
  const cache = useQueryClient();
  const list = useQuery({ queryKey: ['workspaces'], queryFn: workspaceApi.list });
  const create = useMutation({ mutationFn: workspaceApi.create, onSuccess: () => cache.invalidateQueries({ queryKey: ['workspaces'] }) });
  return { list, create };
}
export function useWorkspace(id: string, page: number) {
  const cache = useQueryClient();
  const enabled = !!id;
  const workspace = useQuery({ queryKey: ['workspace', id], queryFn: () => workspaceApi.get(id), enabled });
  const members = useQuery({ queryKey: ['workspace', id, 'members'], queryFn: () => workspaceApi.members(id), enabled });
  const categories = useQuery({ queryKey: ['workspace', id, 'categories'], queryFn: () => workspaceApi.categories(id), enabled });
  const feeds = useQuery({ queryKey: ['workspace', id, 'feeds'], queryFn: () => workspaceApi.feeds(id), enabled });
  const articles = useQuery({ queryKey: ['workspace', id, 'articles', page], queryFn: () => workspaceApi.articles(id, page), enabled });
  const bookmarks = useQuery({ queryKey: ['workspace', id, 'bookmarks'], queryFn: () => workspaceApi.bookmarks(id), enabled });
  const activity = useQuery({ queryKey: ['workspace', id, 'activity'], queryFn: () => workspaceApi.activity(id), enabled });
  const action = useMutation({ mutationFn: (work: () => Promise<void>) => work(), onSuccess: () => cache.invalidateQueries({ queryKey: ['workspace', id] }) });
  return { workspace, members, categories, feeds, articles, bookmarks, activity, action };
}
