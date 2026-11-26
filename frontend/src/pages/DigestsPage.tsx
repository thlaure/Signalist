import { Alert, Button, FormControlLabel, Paper, Stack, Switch, Typography } from '@mui/material';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useDigests } from '../hooks/useDigestMvp';
import { usePrivacy } from '../hooks/usePrivacy';
import type { ConsentPurpose } from '../api/privacy';

export default function DigestsPage() {
  const { t } = useTranslation();
  const digests = useDigests();
  const { profile, change } = usePrivacy();
  return <Stack spacing={3} sx={{ maxWidth: 850, mx: 'auto' }}>
    <Typography variant="h4" component="h1">{t('mvp.digests')}</Typography>
    <Typography>{t('mvp.promise')}</Typography>
    <Typography color="text.secondary">{t('mvp.schedule')}</Typography>
    {(digests.isPending || profile.isPending) && <Typography>{t('common.loading')}</Typography>}
    {(digests.error || profile.error || change.error) && <Alert severity="error">{t('mvp.failed')}</Alert>}
    {profile.data && <Paper variant="outlined" sx={{ p: 2 }}>
      <Typography variant="h6">{t('mvp.delivery')}</Typography>
      {(['newsletter', 'external_ai'] as ConsentPurpose[]).map((purpose) => <FormControlLabel key={purpose} label={t(`settings.${purpose}`)} control={<Switch checked={profile.data.consent[purpose]} disabled={change.isPending || profile.data.processingRestricted} onChange={(_, granted) => change.mutate({ type: 'consent', purpose, granted })} />} />)}
      <Typography variant="body2">{t('mvp.consentHelp')}</Typography>
      {profile.data.processingRestricted && <Alert severity="warning">{t('mvp.restricted')}</Alert>}
    </Paper>}
    {digests.data && <>
      {!digests.data.configured && <Alert severity="info">{t('mvp.aiPending')}</Alert>}
      <Typography>{t('mvp.nextRun', { date: new Date(digests.data.nextRunAt).toLocaleString() })}</Typography>
      {digests.data.items.length === 0 && <Alert severity="info">{t('mvp.noDigests')}</Alert>}
      <Button component={Link} to="/sources">{t('mvp.sources')}</Button>
      {digests.data.items.map((digest) => <Paper variant="outlined" key={digest.id} sx={{ p: 2 }}>
        <Typography variant="h6">{digest.period}</Typography>
        <Typography>{t(`mvp.status.${digest.status}`)}</Typography>
        <Button component={Link} to={`/digests/${digest.id}`}>{t('mvp.read')}</Button>
      </Paper>)}
    </>}
  </Stack>;
}
