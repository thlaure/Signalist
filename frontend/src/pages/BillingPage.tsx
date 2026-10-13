import { useState } from 'react';
import { Alert, Button, MenuItem, Stack, TextField, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { useBilling } from '../hooks/useBilling';
import { useWorkspaces } from '../hooks/useWorkspaces';
export default function BillingPage() {
  const { t } = useTranslation(); const { list } = useWorkspaces();
  const [organizationId, setOrganizationId] = useState<string | null>(null);
  const { status, create, link } = useBilling(organizationId); const data = status.data;
  return <Stack spacing={3} sx={{ maxWidth: 800, mx: 'auto', p: 3 }}>
    <Typography variant="h4">{t('billing.title')}</Typography>
    <TextField select label={t('billing.scope')} value={organizationId ?? ''} onChange={(event) => setOrganizationId(event.target.value || null)}>
      <MenuItem value="">{t('billing.personal')}</MenuItem>
      {list.data?.filter((workspace) => workspace.role === 'admin').map((workspace) => <MenuItem key={workspace.id} value={workspace.id}>{workspace.name}</MenuItem>)}
    </TextField>
    {(status.error || create.error || link.error || link.data?.status === 'failed') && <Alert severity="error">{t('billing.failed')}</Alert>}
    {data && <>
      <Typography variant="h6">{t(`billing.plan_${data.plan}`)}</Typography>
      <Typography>{t('billing.seats', { count: data.seats })}</Typography>
      <Stack spacing={1}>{(Object.entries(data.limits) as [keyof typeof data.limits, number][]).map(([metric, value]) => <Typography key={metric}>{t(`billing.${metric}`)}: {value < 0 ? t('billing.unlimited') : value}</Typography>)}</Stack>
      {!data.configured && <Alert severity="info">{t('billing.setupPending')}</Alert>}
      <Typography color="text.secondary">{t('billing.priceHelp')}</Typography>
      <Stack direction="row" spacing={2}>
        <Button variant="contained" disabled={!data.configured || create.isPending || data.status === 'active' || data.status === 'trialing'} onClick={() => create.mutate('checkout')}>{t('billing.upgrade')}</Button>
        <Button variant="outlined" disabled={!data.configured || create.isPending || data.status === 'inactive'} onClick={() => create.mutate('portal')}>{t('billing.manage')}</Button>
      </Stack>
    </>}
    {(create.isPending || link.data?.status === 'pending') && <Alert severity="info">{t('billing.preparing')}</Alert>}
    {link.data?.status === 'complete' && link.data.url && <Button component="a" href={link.data.url} variant="contained">{t('billing.continue')}</Button>}
    {link.data?.status === 'expired' && <Alert severity="warning">{t('billing.expired')}</Alert>}
  </Stack>;
}
