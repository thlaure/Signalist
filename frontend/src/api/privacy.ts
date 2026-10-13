import apiClient from './client';

export type ConsentPurpose = 'external_ai' | 'newsletter' | 'marketing';
export interface PrivacyProfile {
  id: string;
  email: string;
  displayName: string;
  processingRestricted: boolean;
  consent: Record<ConsentPurpose, boolean>;
}

export const privacyApi = {
  profile: async (): Promise<PrivacyProfile> => (await apiClient.get('/user/profile')).data,
  update: async (displayName: string): Promise<void> => { await apiClient.put('/user/profile', { displayName }); },
  consent: async (purpose: ConsentPurpose, granted: boolean): Promise<void> => { await apiClient.post('/user/consent', { purpose, granted }); },
  restrict: async (restricted: boolean): Promise<void> => { await apiClient.post('/user/restrict-processing', { restricted }); },
  export: async (): Promise<unknown> => (await apiClient.get('/user/data-export')).data,
  delete: async (password: string): Promise<void> => { await apiClient.delete('/user/account', { data: { password } }); },
};
