import { Link, useParams } from 'react-router-dom';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Alert, Box, Button, Stack, Typography } from '@mui/material';
import DOMPurify from 'dompurify';
import { useTranslation } from 'react-i18next';
import { workspaceApi } from '../api/workspaces';
import ShareMenu from '../components/Article/ShareMenu';
export default function WorkspaceArticlePage() {
  const { id = '', articleId = '' } = useParams(); const { t } = useTranslation(); const cache = useQueryClient();
  const article = useQuery({ queryKey: ['workspace', id, 'article', articleId], queryFn: () => workspaceApi.article(id, articleId) });
  const read = useMutation({ mutationFn: (isRead: boolean) => workspaceApi.read(id, articleId, isRead), onSuccess: () => cache.invalidateQueries({ queryKey: ['workspace', id] }) });
  return <Stack spacing={3} sx={{ maxWidth: 900, mx: 'auto', p: 3 }}>
    <Button component={Link} to={`/workspaces/${id}`}>{t('article.back')}</Button>
    {(article.error || read.error) && <Alert severity="error">{t('workspaces.failed')}</Alert>}
    {article.data && <>
      <Typography variant="h4">{article.data.title}</Typography>
      <Stack direction="row" spacing={2}><Button disabled={read.isPending} onClick={() => read.mutate(!article.data.isRead)}>{t(article.data.isRead ? 'article.markAsUnread' : 'article.markAsRead')}</Button><ShareMenu title={article.data.title} url={article.data.url} /></Stack>
      {article.data.aiSummary && <><Typography variant="h6">{t('article.aiSummary')}</Typography><Typography>{article.data.aiSummary}</Typography></>}
      {article.data.content ? <Box dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(article.data.content) }} sx={{ '& img': { maxWidth: '100%' }, lineHeight: 1.8 }} /> : <Typography>{article.data.summary}</Typography>}
      <Button component="a" href={article.data.url} target="_blank" rel="noopener noreferrer">{t('article.openOriginalButton')}</Button>
    </>}
  </Stack>;
}
