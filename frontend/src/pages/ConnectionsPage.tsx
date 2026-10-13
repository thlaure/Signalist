import { useState } from 'react';
import { Alert, Button, Card, CardContent, Checkbox, FormControlLabel, Stack, TextField, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { useConnections } from '../hooks/useConnections';
export default function ConnectionsPage() {
  const { t } = useTranslation();
  const { accounts, authorize, authorization, disconnect, sync, run } = useConnections();
  const [handle, setHandle] = useState('');
  const [includeNotes, setIncludeNotes] = useState(false);
  return <Stack spacing={2}>
    <Typography variant="h4">{t('connections.title')}</Typography>
    <Alert severity="info">{t('connections.about')}</Alert>
    {(accounts.isError || authorize.isError || disconnect.isError || sync.isError || authorization.isError || run.isError) && <Alert severity="error">{t('connections.error')}</Alert>}
    {authorization.data && <Alert severity={authorization.data.status === 'failed' || authorization.data.status === 'expired' ? 'error' : 'info'}>{t(`connections.status.${authorization.data.status}`)} {authorization.data.url && <Button onClick={() => window.location.assign(authorization.data!.url!)}>{t('connections.continue')}</Button>}</Alert>}
    {run.data && <Alert severity={['failed', 'uncertain'].includes(run.data.status) ? 'warning' : 'info'}>{t(`connections.status.${run.data.status}`)} · {run.data.processed} {t('connections.processed')}{run.data.status === 'uncertain' && <Typography>{t('connections.uncertain')}</Typography>}</Alert>}
    {accounts.data?.map((account) => <Card key={account.provider}><CardContent><Stack spacing={2}>
      <Typography variant="h6">{account.provider === 'x' ? 'X' : account.provider === 'raindrop' ? 'Raindrop.io' : account.provider === 'linkedin' ? 'LinkedIn' : 'Bluesky'}</Typography>
      <Typography>{t(`connections.status.${account.status}`)}</Typography>
      {!account.configured && <Alert severity="info">{t('connections.setup')}</Alert>}
      {account.provider === 'bluesky' && <TextField label={t('connections.handle')} value={handle} onChange={(event) => setHandle(event.target.value)} placeholder="alice.bsky.social" />}
      <Stack direction="row" spacing={1} flexWrap="wrap"><Button variant="outlined" disabled={!account.configured || authorize.isPending || (account.provider === 'bluesky' && !handle.trim())} onClick={() => authorize.mutate({ provider: account.provider, handle: account.provider === 'bluesky' ? handle.trim() : undefined })}>{t('connections.connect')}</Button>
      {account.id && account.status !== 'disconnected' && <Button color="error" disabled={disconnect.isPending} onClick={() => { if (window.confirm(t('connections.confirmDisconnect'))) disconnect.mutate(account.id!); }}>{t('connections.disconnect')}</Button>}</Stack>
      {account.provider === 'raindrop' && account.status === 'connected' && account.id && <><FormControlLabel control={<Checkbox checked={includeNotes} onChange={(event) => setIncludeNotes(event.target.checked)} />} label={t('connections.notes')} /><Button disabled={sync.isPending || run.data?.status === 'pending'} onClick={() => sync.mutate({ id: account.id!, includeNotes })}>{t('connections.sync')}</Button></>}
    </Stack></CardContent></Card>)}
  </Stack>;
}
