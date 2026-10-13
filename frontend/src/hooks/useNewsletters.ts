import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { newsletterApi, type NewsletterInput } from '../api/newsletters';
export function useNewsletters() {
  const cache = useQueryClient();
  const list = useQuery({ queryKey: ['newsletters'], queryFn: newsletterApi.list, refetchInterval: 5000 });
  const refresh = () => cache.invalidateQueries({ queryKey: ['newsletters'] });
  const save = useMutation({ mutationFn: ({ input, id }: { input: NewsletterInput; id?: string }) => newsletterApi.save(input, id), onSuccess: refresh });
  const remove = useMutation({ mutationFn: newsletterApi.delete, onSuccess: refresh });
  const generate = useMutation({ mutationFn: ({ id, deliver }: { id: string; deliver: boolean }) => newsletterApi.generate(id, deliver), onSuccess: refresh });
  return { list, save, remove, generate };
}
