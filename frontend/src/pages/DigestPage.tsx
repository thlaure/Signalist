import { Alert, Button, Link, Paper, Stack, Typography } from '@mui/material';
import { Link as RouterLink, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useDigest } from '../hooks/useDigestMvp';

export default function DigestPage() {
  const { id } = useParams(); const { t } = useTranslation(); const digest = useDigest(id);
  return <Stack spacing={3} sx={{ maxWidth: 850, mx: 'auto' }}>
    <Button component={RouterLink} to="/digests">{t('mvp.digests')}</Button>
    {digest.isPending && <Typography>{t('common.loading')}</Typography>}
    {digest.error && <Alert severity="error">{t('mvp.failed')}</Alert>}
    {digest.data && <>
      <Typography variant="h4" component="h1">Signalist · {digest.data.period}</Typography>
      <Typography>{t(`mvp.status.${digest.data.status}`)}</Typography>
      {digest.data.items.map((item) => <Paper component="article" variant="outlined" key={item.url} sx={{ p: 3 }}>
        <Typography variant="h6"><Link href={item.url} target="_blank" rel="noopener noreferrer">{item.title}</Link></Typography>
        <Typography color="text.secondary" variant="body2" sx={{ mb: 2 }}>{item.source}</Typography>
        <Typography>{item.summary}</Typography>
      </Paper>)}
    </>}
  </Stack>;
}
