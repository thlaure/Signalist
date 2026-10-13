import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { privacyApi, type ConsentPurpose } from '../api/privacy';

export function usePrivacy() {
  const client = useQueryClient();
  const profile = useQuery({ queryKey: ['privacy'], queryFn: privacyApi.profile });
  const change = useMutation({
    mutationFn: async (action: { type: 'profile'; displayName: string } | { type: 'consent'; purpose: ConsentPurpose; granted: boolean } | { type: 'restrict'; restricted: boolean }) => {
      if (action.type === 'profile') return privacyApi.update(action.displayName);
      if (action.type === 'consent') return privacyApi.consent(action.purpose, action.granted);
      return privacyApi.restrict(action.restricted);
    },
    onSuccess: () => client.invalidateQueries({ queryKey: ['privacy'] }),
  });
  const remove = useMutation({ mutationFn: privacyApi.delete });
  const download = useMutation({ mutationFn: privacyApi.export });
  return { profile, change, remove, download };
}
