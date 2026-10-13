import { useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { Alert, Box, Button, Card, CardContent, MenuItem, Stack, TextField, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { useSearch, type SearchMode } from '../hooks/useSearch';

export default function SearchPage() {
  const { t } = useTranslation();
  const { create, result } = useSearch();
  const [params] = useSearchParams();
  const [query, setQuery] = useState(params.get('q') ?? '');
  const [mode, setMode] = useState<SearchMode>(params.get('mode') === 'question' ? 'question' : 'fulltext');
  return <Stack spacing={3} sx={{ p: 3, maxWidth: 900, mx: 'auto' }}>
    <Typography variant="h4">{t('search.title')}</Typography>
    <Box component="form" onSubmit={(event) => { event.preventDefault(); create.mutate({ query, mode }); }}>
      <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2}>
        <TextField value={query} onChange={(event) => setQuery(event.target.value)} label={t('search.query')} required fullWidth slotProps={{ htmlInput: { maxLength: 500 } }} />
        <TextField select value={mode} label={t('search.mode')} onChange={(event) => setMode(event.target.value as SearchMode)} sx={{ minWidth: 180 }}>
          {(['fulltext', 'semantic', 'hybrid', 'question'] as SearchMode[]).map((value) => <MenuItem key={value} value={value}>{t(`search.${value}`)}</MenuItem>)}
        </TextField>
        <Button type="submit" variant="contained" disabled={create.isPending || !query.trim()}>{t('search.submit')}</Button>
      </Stack>
    </Box>
    {(create.error || result.error || result.data?.status === 'failed') && <Alert severity="error">{t('search.failed')}</Alert>}
    {result.data?.status === 'pending' && <Alert severity="info">{t('search.pending')}</Alert>}
    {result.data?.status === 'complete' && result.data.items.length === 0 && <Typography>{t('search.empty')}</Typography>}
    {result.data?.answer && <Card><CardContent><Typography sx={{ whiteSpace: 'pre-wrap' }}>{result.data.answer}</Typography></CardContent></Card>}
    {result.data?.items.map((article) => <Card key={article.id}><CardContent>
      <Typography component={Link} to={`/articles/${article.id}`} variant="h6">{article.title}</Typography>
      <Typography>{article.aiSummary ?? article.summary}</Typography>
    </CardContent></Card>)}
  </Stack>;
}
