import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Alert, Button, Card, CardContent, MenuItem, Stack, TextField, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { useTags } from '../hooks/useTags';
export default function TagsPage() {
  const { t } = useTranslation(); const [id, setId] = useState(''); const [name, setName] = useState('');
  const { tags, articles, save, remove } = useTags(id);
  return <Stack spacing={2}><Typography variant="h4">{t('tags.title')}</Typography>
    {(tags.isError || articles.isError || save.isError || remove.isError) && <Alert severity="error">{t('tags.error')}</Alert>}
    <TextField select label={t('tags.filter')} value={id} onChange={(event) => { const value = event.target.value; setId(value); setName(tags.data?.find((tag) => tag.id === value)?.name ?? ''); }}><MenuItem value="">{t('tags.new')}</MenuItem>{tags.data?.map((tag) => <MenuItem key={tag.id} value={tag.id}>{tag.name}</MenuItem>)}</TextField>
    <TextField label={t('tags.name')} value={name} onChange={(event) => setName(event.target.value)} />
    <Stack direction="row" spacing={1}><Button disabled={!name.trim() || name.length > 60 || save.isPending} onClick={() => save.mutate({ id: id || undefined, name })}>{t('tags.save')}</Button>{id && <Button color="error" onClick={() => { if (window.confirm(t('tags.confirmDelete'))) remove.mutate(id, { onSuccess: () => { setId(''); setName(''); } }); }}>{t('tags.delete')}</Button>}</Stack>
    {id && !articles.isLoading && articles.data?.length === 0 && <Typography>{t('tags.empty')}</Typography>}
    {articles.data?.map((article) => <Card key={article.id}><CardContent><Typography variant="h6" component={Link} to={`/articles/${article.id}`}>{article.title}</Typography><Typography>{article.aiSummary ?? article.summary}</Typography></CardContent></Card>)}
  </Stack>;
}
