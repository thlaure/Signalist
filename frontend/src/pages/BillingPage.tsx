import { Alert, Button, Paper, Stack, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { useBilling } from '../hooks/useBilling';
export default function BillingPage() {
  const { t } = useTranslation();
  const { status, create, link } = useBilling(null); const data = status.data;
  return <Stack spacing={3} sx={{ maxWidth: 800, mx: 'auto' }}>
    <Typography variant="h4" component="h1">{t('mvp.billing')}</Typography>
    {status.isPending && <Typography>{t('common.loading')}</Typography>}
    {(status.error || create.error || link.error || link.data?.status === 'failed') && <Alert severity="error">{t('billing.failed')}</Alert>}
    {data && <Paper variant="outlined" sx={{ p: 3 }}>
      <Stack spacing={2}>
        <Typography variant="h6">{t(data.plan === 'free' ? 'mvp.free' : 'mvp.paid')}</Typography>
        <Typography>{t('mvp.sourceLimit', { limit: data.limits.feeds })}</Typography>
        <Typography>{t('mvp.sameQuality')}</Typography>
        <Typography>{t('mvp.offer', { free: data.offers.free, paid: data.offers.paid })}</Typography>
        {!data.configured && <Alert severity="info">{t('billing.setupPending')}</Alert>}
        <Typography color="text.secondary">{t('mvp.priceHelp')}</Typography>
        <Typography variant="body2">{t('mvp.downgrade')}</Typography>
        <Stack direction="row" spacing={2}>
          <Button variant="contained" disabled={!data.configured || create.isPending || data.status === 'active' || data.status === 'trialing'} onClick={() => create.mutate('checkout')}>{t('billing.upgrade')}</Button>
          <Button variant="outlined" disabled={!data.configured || create.isPending || !data.canManage} onClick={() => create.mutate('portal')}>{t('billing.manage')}</Button>
        </Stack>
      </Stack>
    </Paper>}
    {(create.isPending || link.data?.status === 'pending') && <Alert severity="info">{t('billing.preparing')}</Alert>}
    {link.data?.status === 'complete' && link.data.url && <Button component="a" href={link.data.url} variant="contained">{t('billing.continue')}</Button>}
    {link.data?.status === 'expired' && <Alert severity="warning">{t('billing.expired')}</Alert>}
  </Stack>;
}
