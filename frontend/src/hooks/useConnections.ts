import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import apiClient from '../api/client';
export interface Connection { id: string | null; provider: string; configured: boolean; status: string; lastSyncedAt: string | null; }
interface Authorization { status: string; url: string | null; }
interface SyncRun { id: string; status: string; phase: string; processed: number; }
export function useConnections() {
  const cache = useQueryClient();
  const [attemptId, setAttemptId] = useState<string | null>(null);
  const [runId, setRunId] = useState<string | null>(null);
  const accounts = useQuery({ queryKey: ['connections'], queryFn: async (): Promise<Connection[]> => (await apiClient.get('/connections')).data });
  const authorize = useMutation({ mutationFn: async ({ provider, handle }: { provider: string; handle?: string }): Promise<{ url: string | null; attemptId: string }> => (await apiClient.post(`/connections/${provider}/authorize`, { handle }, { withCredentials: true })).data, onSuccess: (data) => { if (data.url) window.location.assign(data.url); else setAttemptId(data.attemptId); } });
  const authorization = useQuery({ queryKey: ['authorization', attemptId], enabled: !!attemptId, queryFn: async (): Promise<Authorization> => (await apiClient.get(`/connections/authorizations/${attemptId}`)).data, refetchInterval: (query) => !query.state.data || query.state.data.status === 'preparing' ? 1000 : false });
  const disconnect = useMutation({ mutationFn: (id: string) => apiClient.delete(`/connections/${id}`), onSuccess: () => cache.invalidateQueries({ queryKey: ['connections'] }) });
  const sync = useMutation({ mutationFn: async ({ id, includeNotes }: { id: string; includeNotes: boolean }): Promise<SyncRun> => (await apiClient.post(`/connections/${id}/sync`, { includeNotes })).data, onSuccess: (data) => setRunId(data.id) });
  const run = useQuery({ queryKey: ['sync', runId], enabled: !!runId, queryFn: async (): Promise<SyncRun> => (await apiClient.get(`/sync/${runId}`)).data, refetchInterval: (query) => !query.state.data || query.state.data.status === 'pending' ? 1000 : false });
  return { accounts, authorize, authorization, disconnect, sync, run };
}
