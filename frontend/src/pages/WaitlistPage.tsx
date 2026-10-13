import { useMutation } from '@tanstack/react-query';
import { useSearchParams } from 'react-router-dom';
import { Alert, Button, Stack, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import apiClient from '../api/client';

export default function WaitlistPage() {
  const [params] = useSearchParams();
  const { t } = useTranslation();
  const id = params.get('id');
  const signature = params.get('signature');
  const action = params.get('action');
  const valid = !!id && !!signature && (action === 'confirm' || action === 'withdraw');
  const request = useMutation({ mutationFn: () => apiClient.post(`/waitlist/${encodeURIComponent(id ?? '')}/${action}`, null, { params: { signature } }) });
  return <Stack spacing={3} sx={{ maxWidth: 560, mx: 'auto', p: 4 }}>
    <Typography variant="h4">{t('waitlist.title')}</Typography>
    {(!valid || request.isError) && <Alert severity="error">{t('waitlist.invalid')}</Alert>}
    {request.isSuccess ? <Alert severity="success">{t(action === 'withdraw' ? 'waitlist.withdrawn' : 'waitlist.confirmed')}</Alert> : <Button disabled={!valid || request.isPending} variant="contained" onClick={() => request.mutate()}>{t(action === 'withdraw' ? 'waitlist.withdraw' : 'waitlist.confirm')}</Button>}
  </Stack>;
}
