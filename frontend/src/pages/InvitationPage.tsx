import { useSearchParams, useNavigate } from 'react-router-dom';
import { useMutation } from '@tanstack/react-query';
import { Alert, Button, Stack, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { workspaceApi } from '../api/workspaces';
export default function InvitationPage() {
  const [params] = useSearchParams(); const navigate = useNavigate(); const { t } = useTranslation();
  const id = params.get('id'); const signature = params.get('signature');
  const accept = useMutation({ mutationFn: () => workspaceApi.accept(id ?? '', signature ?? ''), onSuccess: (workspace) => navigate(`/workspaces/${workspace.id}`) });
  return <Stack spacing={3} sx={{ p: 4, maxWidth: 600, mx: 'auto' }}>
    <Typography variant="h4">{t('workspaces.invitation')}</Typography>
    <Typography>{t('workspaces.invitationHelp')}</Typography>
    {accept.error && <Alert severity="error">{t('workspaces.invitationFailed')}</Alert>}
    <Button disabled={!id || !signature || accept.isPending} variant="contained" onClick={() => accept.mutate()}>{t('workspaces.accept')}</Button>
  </Stack>;
}
