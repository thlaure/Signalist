import apiClient from './client';
export interface Newsletter {
  id: string; title: string; readingMinutes: number; intervalHours: number | null;
  nextRunAt: string | null; html: string | null; generatedAt: string | null; lastRunStatus?: string | null;
}
export interface NewsletterInput { title: string; readingMinutes: number; intervalHours: number | null; }
export const newsletterApi = {
  list: async (): Promise<Newsletter[]> => (await apiClient.get('/newsletters')).data,
  save: async (input: NewsletterInput, id?: string): Promise<Newsletter> => (await (id ? apiClient.put(`/newsletters/${id}`, input) : apiClient.post('/newsletters', input))).data,
  delete: async (id: string): Promise<void> => { await apiClient.delete(`/newsletters/${id}`); },
  generate: async (id: string, deliver: boolean): Promise<{ runId: string; status: string }> => (await apiClient.post(`/newsletters/${id}/${deliver ? 'send' : 'generate'}`)).data,
};
