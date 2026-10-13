import { useState } from 'react';
import { Alert, Box, Button, Card, CardContent, MenuItem, Stack, TextField, Typography } from '@mui/material';
import DOMPurify from 'dompurify';
import { useTranslation } from 'react-i18next';
import { useNewsletters } from '../hooks/useNewsletters';
import type { Newsletter } from '../api/newsletters';

export default function NewslettersPage() {
  const { t } = useTranslation();
  const { list, save, remove, generate } = useNewsletters();
  const [editing, setEditing] = useState<Newsletter | null>(null);
  const [preview, setPreview] = useState<string | null>(null);
  const [schedule, setSchedule] = useState('manual');
  const error = list.error || save.error || remove.error || generate.error;
  return <Stack spacing={3} sx={{ p: 3, maxWidth: 1000, mx: 'auto' }}>
    <Typography variant="h4">{t('newsletters.title')}</Typography>
    <Typography color="text.secondary">{t('newsletters.help')}</Typography>
    {error && <Alert severity="error">{t('newsletters.failed')}</Alert>}
    {generate.isSuccess && <Alert severity="info">{t('newsletters.queued')}</Alert>}
    <Box component="form" key={editing?.id ?? 'new'} onSubmit={(event) => {
      event.preventDefault();
      const data = new FormData(event.currentTarget);
      save.mutate({ id: editing?.id, input: {
        title: String(data.get('title')), readingMinutes: Number(data.get('readingMinutes')),
        intervalHours: schedule === 'manual' ? null : schedule === 'custom' ? Number(data.get('intervalHours')) : Number(schedule),
      } }, { onSuccess: () => { setEditing(null); setSchedule('manual'); } });
    }}>
      <Stack spacing={2}>
        <TextField name="title" label={t('newsletters.name')} defaultValue={editing?.title ?? ''} required slotProps={{ htmlInput: { maxLength: 150 } }} />
        <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2}>
          <TextField name="readingMinutes" label={t('newsletters.minutes')} type="number" defaultValue={editing?.readingMinutes ?? 5} required slotProps={{ htmlInput: { min: 1, max: 60 } }} />
          <TextField select label={t('newsletters.schedule')} value={schedule} onChange={(event) => setSchedule(event.target.value)} sx={{ minWidth: 200 }}>
            {['manual', '24', '168', 'custom'].map((value) => <MenuItem key={value} value={value}>{t(`newsletters.schedule_${value}`)}</MenuItem>)}
          </TextField>
          {schedule === 'custom' && <TextField name="intervalHours" label={t('newsletters.interval')} type="number" defaultValue={editing?.intervalHours ?? 48} required slotProps={{ htmlInput: { min: 1, max: 8760 } }} />}
        </Stack>
        <Stack direction="row" spacing={2}><Button type="submit" variant="contained" disabled={save.isPending}>{t('common.save')}</Button>{editing && <Button onClick={() => { setEditing(null); setSchedule('manual'); }}>{t('common.cancel')}</Button>}</Stack>
      </Stack>
    </Box>
    {list.data?.map((newsletter) => <Card key={newsletter.id}><CardContent>
      <Typography variant="h6">{newsletter.title}</Typography>
      {newsletter.lastRunStatus && <Typography>{t(`newsletters.status.${newsletter.lastRunStatus}`)}</Typography>}
      {newsletter.nextRunAt && <Typography color="text.secondary">{t('newsletters.next')}: {new Date(newsletter.nextRunAt).toLocaleString()}</Typography>}
      <Stack direction="row" flexWrap="wrap" gap={1} mt={2}>
        <Button onClick={() => { setEditing(newsletter); setSchedule(newsletter.intervalHours === null ? 'manual' : [24, 168].includes(newsletter.intervalHours) ? String(newsletter.intervalHours) : 'custom'); }}>{t('common.edit')}</Button>
        <Button disabled={generate.isPending} onClick={() => generate.mutate({ id: newsletter.id, deliver: false })}>{t('newsletters.generate')}</Button>
        <Button disabled={!newsletter.html} onClick={() => setPreview(newsletter.html)}>{t('newsletters.preview')}</Button>
        <Button disabled={generate.isPending} onClick={() => { if (window.confirm(t('newsletters.sendConfirm'))) generate.mutate({ id: newsletter.id, deliver: true }); }}>{t('newsletters.send')}</Button>
        <Button color="error" disabled={remove.isPending} onClick={() => { if (window.confirm(t('newsletters.deleteConfirm'))) remove.mutate(newsletter.id); }}>{t('common.delete')}</Button>
      </Stack>
    </CardContent></Card>)}
    {preview && <Box component="iframe" title={t('newsletters.preview')} sandbox="" srcDoc={DOMPurify.sanitize(preview, { WHOLE_DOCUMENT: true })} sx={{ width: '100%', minHeight: 500, border: '1px solid', borderColor: 'divider' }} />}
  </Stack>;
}
