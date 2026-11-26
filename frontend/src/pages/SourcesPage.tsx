import { useState } from 'react';
import { Alert, Box, Button, Checkbox, Dialog, DialogActions, DialogContent, DialogTitle, FormControlLabel, Link, Paper, Stack, TextField, Typography } from '@mui/material';
import { Link as RouterLink } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useSources, type Source } from '../hooks/useDigestMvp';
import { isProblemError } from '../api/client';

export default function SourcesPage() {
  const { t } = useTranslation();
  const { list, add, remove, select } = useSources();
  const [url, setUrl] = useState('');
  const [selection, setSelection] = useState<string[] | null>(null);
  const [deleting, setDeleting] = useState<Source | null>(null);
  const data = list.data;
  const ids = selection ?? data?.items.filter((source) => source.status !== 'paused').map((source) => source.id) ?? [];
  const error = list.error || add.error || remove.error || select.error;
  return <Stack spacing={3} sx={{ maxWidth: 850, mx: 'auto' }}>
    <Typography variant="h4" component="h1">{t('mvp.sources')}</Typography>
    <Typography color="text.secondary">{t('mvp.sourcesHelp')}</Typography>
    {list.isPending && <Typography>{t('common.loading')}</Typography>}
    {error && <Alert severity="error">{isProblemError(error) ? error.problem.detail : t('mvp.failed')}</Alert>}
    {data && <>
      <Typography>{t('mvp.quota', { active: data.items.filter((source) => source.status !== 'paused').length, limit: data.limit })} · <Link component={RouterLink} to="/billing">{t('mvp.billing')}</Link></Typography>
      {data.selectionRequired && <Alert severity="warning">{t('mvp.selectionRequired', { limit: data.limit })}</Alert>}
      <Box component="form" onSubmit={(event) => { event.preventDefault(); add.mutate(url.trim(), { onSuccess: () => setUrl('') }); }}>
        <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2}>
          <TextField fullWidth required type="url" label={t('mvp.rssUrl')} value={url} onChange={(event) => setUrl(event.target.value)} slotProps={{ htmlInput: { maxLength: 2048 } }} />
          <Button type="submit" variant="contained" disabled={!url.trim() || add.isPending || data.items.filter((source) => source.status !== 'paused').length >= data.limit}>{t('mvp.add')}</Button>
        </Stack>
      </Box>
      {data.items.length === 0 && <Alert severity="info">{t('mvp.noSources')}</Alert>}
      {data.items.map((source) => <Paper key={source.id} variant="outlined" sx={{ p: 2 }}>
        <Stack direction="row" alignItems="center" justifyContent="space-between">
          <FormControlLabel label={source.title} control={<Checkbox checked={ids.includes(source.id)} disabled={select.isPending} onChange={(_, checked) => setSelection(checked ? [...ids, source.id] : ids.filter((id) => id !== source.id))} />} />
          <Button color="error" disabled={remove.isPending} onClick={() => setDeleting(source)}>{t('common.delete')}</Button>
        </Stack>
        <Typography variant="body2" sx={{ overflowWrap: 'anywhere' }}>{source.url}</Typography>
        {source.lastError && <Alert severity="warning" sx={{ mt: 1 }}>{t('mvp.feedError')}</Alert>}
      </Paper>)}
      {data.items.length > 0 && <Button variant="outlined" disabled={selection === null || ids.length > data.limit || select.isPending} onClick={() => select.mutate(ids, { onSuccess: () => setSelection(null) })}>{t('mvp.saveSelection')}</Button>}
    </>}
    <Dialog open={deleting !== null} onClose={() => setDeleting(null)}>
      <DialogTitle>{t('mvp.deleteSource')}</DialogTitle><DialogContent>{t('mvp.deleteSourceHelp')}</DialogContent>
      <DialogActions><Button onClick={() => setDeleting(null)}>{t('common.cancel')}</Button><Button color="error" disabled={remove.isPending} onClick={() => { if (deleting) remove.mutate(deleting.id, { onSuccess: () => { setDeleting(null); setSelection(null); } }); }}>{t('common.delete')}</Button></DialogActions>
    </Dialog>
  </Stack>;
}
