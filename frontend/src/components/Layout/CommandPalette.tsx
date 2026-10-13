import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Alert, Box, Button, Dialog, DialogContent, DialogTitle, List, ListItemButton, ListItemText, TextField } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { useSpotlight } from '../../hooks/useSpotlight';

export default function CommandPalette() {
  const { t } = useTranslation(); const navigate = useNavigate();
  const [open, setOpen] = useState(false); const [query, setQuery] = useState('');
  const { articles, execute } = useSpotlight(query, open);
  useEffect(() => {
    const handler = (event: KeyboardEvent) => { if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); setOpen((value) => !value); } };
    window.addEventListener('keydown', handler); return () => window.removeEventListener('keydown', handler);
  }, []);
  const go = (path: string) => { setOpen(false); setQuery(''); navigate(path); };
  return <>
    <Button onClick={() => setOpen(true)} sx={{ mb: 2 }}>{t('spotlight.open')} ⌘K</Button>
    <Dialog open={open} onClose={() => setOpen(false)} fullWidth maxWidth="sm" aria-labelledby="spotlight-title">
      <DialogTitle id="spotlight-title">{t('spotlight.title')}</DialogTitle>
      <DialogContent>
        <Box component="form" onSubmit={(event) => { event.preventDefault(); execute.mutate(query, { onSuccess: (result) => go(result.path) }); }}>
          <TextField autoFocus fullWidth label={t('spotlight.query')} value={query} onChange={(event) => setQuery(event.target.value)} helperText={t('spotlight.help')} slotProps={{ htmlInput: { maxLength: 500 } }} />
          <Button type="submit" disabled={!query.trim() || execute.isPending}>{t('spotlight.execute')}</Button>
        </Box>
        {execute.isError && <Alert severity="error">{t('spotlight.failed')}</Alert>}
        <List>
          {['feeds', 'bookmarks', 'search', 'newsletters', 'settings'].map((page) => <ListItemButton key={page} onClick={() => go(`/${page}`)}><ListItemText primary={t(`nav.${page}`)} /></ListItemButton>)}
          {articles.data?.items.map((article) => <ListItemButton key={article.id} onClick={() => go(`/articles/${article.id}`)}><ListItemText primary={article.title} secondary={article.feedTitle} /></ListItemButton>)}
        </List>
      </DialogContent>
    </Dialog>
  </>;
}
