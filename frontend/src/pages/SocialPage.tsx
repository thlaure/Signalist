import { useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Alert, Button, Card, CardContent, Checkbox, FormControlLabel, MenuItem, Stack, TextField, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { useSocial } from '../hooks/useSocial';
import { useConnections } from '../hooks/useConnections';
export default function SocialPage() {
  const { t } = useTranslation(); const [params] = useSearchParams();
  const { recaps, posts, generate, edit, remove, publish } = useSocial();
  const { accounts } = useConnections();
  const [platform, setPlatform] = useState('linkedin');
  const [start, setStart] = useState(() => new Date(Date.now() - 6 * 86400000).toISOString().slice(0, 10));
  const [end, setEnd] = useState(() => new Date().toISOString().slice(0, 10));
  const [useAi, setUseAi] = useState(false);
  const [text, setText] = useState(params.get('text')?.slice(0, 20000) ?? '');
  const [selected, setSelected] = useState<string | null>(null);
  const [accountId, setAccountId] = useState('');
  const [copied, setCopied] = useState(false); const [copyFailed, setCopyFailed] = useState(false);
  const connected = accounts.data?.filter((account) => account.status === 'connected' && ['linkedin', 'x', 'bluesky'].includes(account.provider)) ?? [];
  return <Stack spacing={2}>
    <Typography variant="h4">{t('social.title')}</Typography>
    <Alert severity="info">{t('social.about')}</Alert>
    {(recaps.isError || posts.isError || generate.isError || edit.isError || remove.isError || publish.isError) && <Alert severity="error">{t('social.error')}</Alert>}
    <Card><CardContent><Stack spacing={2}>
      <Typography variant="h6">{t('social.generateTitle')}</Typography>
      <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2}><TextField select label={t('social.platform')} value={platform} onChange={(event) => setPlatform(event.target.value)}>{['linkedin', 'x', 'instagram', 'bluesky'].map((value) => <MenuItem key={value} value={value}>{value}</MenuItem>)}</TextField><TextField type="date" label={t('social.start')} value={start} onChange={(event) => setStart(event.target.value)} slotProps={{ inputLabel: { shrink: true } }} /><TextField type="date" label={t('social.end')} value={end} onChange={(event) => setEnd(event.target.value)} slotProps={{ inputLabel: { shrink: true } }} /></Stack>
      <FormControlLabel control={<Checkbox checked={useAi} onChange={(event) => setUseAi(event.target.checked)} />} label={t('social.ai')} />
      <Button disabled={generate.isPending || !start || !end || start > end} onClick={() => generate.mutate({ platform, start, end, useAi })}>{t('social.generate')}</Button>
    </Stack></CardContent></Card>
    {recaps.data?.map((recap) => <Card key={recap.id}><CardContent><Stack spacing={1}><Typography>{recap.platform} · {recap.start} — {recap.end} · {t(`social.status.${recap.status}`)}</Typography>{recap.posts.map((post, index) => <Typography key={index} sx={{ whiteSpace: 'pre-wrap' }}>{post}</Typography>)}<Stack direction="row" spacing={1}><Button disabled={recap.status !== 'draft'} onClick={() => { setText(recap.posts.join('\n\n')); setSelected(recap.id); setCopied(false); }}>{t('social.edit')}</Button><Button color="error" onClick={() => { if (window.confirm(t('social.confirmDelete'))) remove.mutate(recap.id); }}>{t('social.delete')}</Button></Stack></Stack></CardContent></Card>)}
    <Card><CardContent><Stack spacing={2}>
      <Typography variant="h6">{t('social.editor')}</Typography>
      <TextField multiline minRows={6} label={t('social.content')} value={text} onChange={(event) => { setText(event.target.value); setCopied(false); }} />
      {copied && <Alert severity="success">{t('social.copied')}</Alert>}{copyFailed && <Alert severity="error">{t('social.copyFailed')}</Alert>}
      <Stack direction="row" spacing={1}><Button disabled={!text.trim()} onClick={async () => { try { await navigator.clipboard.writeText(text); setCopied(true); setCopyFailed(false); } catch { setCopyFailed(true); } }}>{t('social.copy')}</Button>{selected && <Button disabled={edit.isPending || !text.trim()} onClick={() => edit.mutate({ id: selected, text })}>{t('social.save')}</Button>}</Stack>
      <TextField select label={t('social.account')} value={accountId} onChange={(event) => setAccountId(event.target.value)}>{connected.map((account) => <MenuItem key={account.id} value={account.id!}>{account.provider}</MenuItem>)}</TextField>
      <Button variant="contained" disabled={publish.isPending || !accountId || !text.trim()} onClick={() => { const account = connected.find((item) => item.id === accountId); if (account && window.confirm(t('social.confirmPublish', { platform: account.provider }))) publish.mutate({ accountId, text, confirmed: true }); }}>{t('social.publish')}</Button>
    </Stack></CardContent></Card>
    {posts.data?.map((post) => <Alert key={post.id} severity={['uncertain', 'failed'].includes(post.status) ? 'warning' : 'info'}>{post.platform} · {t(`social.status.${post.status}`)} · {post.publishedIds.length}/{post.posts.length}{post.status === 'uncertain' && <Typography>{t('social.uncertain')}</Typography>}</Alert>)}
  </Stack>;
}
