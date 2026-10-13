import { useState } from 'react';
import { Alert, Box, Button, Divider, FormControlLabel, Stack, Switch, TextField, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { usePrivacy } from '../hooks/usePrivacy';
import { useAuth } from '../hooks/useAuth';
import type { ConsentPurpose } from '../api/privacy';

export default function SettingsPage() {
  const { t } = useTranslation();
  const { logout } = useAuth();
  const { profile, change, remove, download } = usePrivacy();
  const [password, setPassword] = useState('');
  const [confirmDelete, setConfirmDelete] = useState(false);
  const data = profile.data;
  const error = profile.error || change.error || remove.error || download.error;

  const exportData = async () => {
    const result = await download.mutateAsync();
    const url = URL.createObjectURL(new Blob([JSON.stringify(result, null, 2)], { type: 'application/json' }));
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = 'signalist-export.json';
    anchor.click();
    URL.revokeObjectURL(url);
  };

  return <Stack spacing={3} sx={{ maxWidth: 720, mx: 'auto', p: 3 }}>
    <Typography variant="h4">{t('settings.title')}</Typography>
    {error && <Alert severity="error">{t('settings.failed')}</Alert>}
    {profile.isPending && <Typography>{t('common.loading')}</Typography>}
    {data && <>
      <Typography>{data.email}</Typography>
      <Box component="form" key={data.displayName} onSubmit={(event) => {
        event.preventDefault();
        const value = new FormData(event.currentTarget).get('displayName');
        change.mutate({ type: 'profile', displayName: typeof value === 'string' ? value : '' });
      }}>
        <Stack direction="row" spacing={2}>
          <TextField name="displayName" label={t('settings.displayName')} defaultValue={data.displayName} slotProps={{ htmlInput: { maxLength: 100 } }} fullWidth />
          <Button type="submit" disabled={change.isPending}>{t('common.save')}</Button>
        </Stack>
      </Box>
      <Divider />
      <Typography variant="h6">{t('settings.privacy')}</Typography>
      <Typography color="text.secondary">{t('settings.consentHelp')}</Typography>
      {(['external_ai', 'newsletter', 'marketing'] as ConsentPurpose[]).map((purpose) =>
        <FormControlLabel key={purpose} label={t(`settings.${purpose}`)} control={<Switch checked={data.consent[purpose]} disabled={change.isPending || data.processingRestricted} onChange={(_, granted) => change.mutate({ type: 'consent', purpose, granted })} />} />)}
      <FormControlLabel label={t('settings.restrict')} control={<Switch checked={data.processingRestricted} disabled={change.isPending} onChange={(_, restricted) => change.mutate({ type: 'restrict', restricted })} />} />
      <Button variant="outlined" disabled={download.isPending} onClick={() => { void exportData().catch(() => undefined); }}>{t('settings.export')}</Button>
      <Divider />
      <Typography variant="h6" color="error">{t('settings.deleteTitle')}</Typography>
      <Typography>{t('settings.deleteHelp')}</Typography>
      {!confirmDelete ? <Button color="error" onClick={() => setConfirmDelete(true)}>{t('settings.deleteTitle')}</Button> : <Stack spacing={2}>
        <TextField label={t('settings.password')} type="password" autoComplete="current-password" value={password} onChange={(event) => setPassword(event.target.value)} />
        <Button variant="contained" color="error" disabled={!password || remove.isPending} onClick={() => remove.mutate(password, { onSuccess: logout })}>{t('settings.confirmDelete')}</Button>
        <Button onClick={() => { setConfirmDelete(false); setPassword(''); }}>{t('common.cancel')}</Button>
      </Stack>}
    </>}
  </Stack>;
}
