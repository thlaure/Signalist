import { useState } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import apiClient from '../api/client';
export interface BillingStatus { plan: string; status: string; configured: boolean; seats: number; limits: { feeds: number; members: number; dailyArticles: number; monthlyAiCalls: number }; }
interface BillingLink { id: string; status: string; url: string | null; }
export function useBilling(organizationId: string | null) {
  const [pendingLink, setPendingLink] = useState<{ id: string; scope: string | null } | null>(null);
  const linkId = pendingLink?.scope === organizationId ? pendingLink.id : null;
  const status = useQuery({ queryKey: ['billing', organizationId], queryFn: async (): Promise<BillingStatus> => (await apiClient.get('/billing', { params: { organizationId: organizationId ?? undefined } })).data });
  const create = useMutation({ mutationFn: async (kind: 'checkout' | 'portal'): Promise<BillingLink> => (await apiClient.post('/billing/links', { kind, organizationId })).data, onSuccess: (data) => setPendingLink({ id: data.id, scope: organizationId }) });
  const link = useQuery({ queryKey: ['billing-link', linkId], queryFn: async (): Promise<BillingLink> => (await apiClient.get(`/billing/links/${linkId}`)).data, enabled: !!linkId, refetchInterval: (query) => query.state.data?.status === 'pending' ? 1000 : false });
  return { status, create, link };
}
