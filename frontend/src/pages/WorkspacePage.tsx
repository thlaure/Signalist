import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { Alert, Box, Button, Card, CardContent, Divider, MenuItem, Stack, Tab, Tabs, TextField, Typography } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { useWorkspace } from '../hooks/useWorkspaces';
import { workspaceApi, type MemberRole } from '../api/workspaces';
import { isProblemError } from '../api/client';

export default function WorkspacePage() {
  const { id = '' } = useParams(); const { t } = useTranslation();
  const [tab, setTab] = useState('articles'); const [page, setPage] = useState(1);
  const { workspace, members, categories, feeds, articles, bookmarks, activity, action } = useWorkspace(id, page);
  const write = workspace.data?.role === 'admin' || workspace.data?.role === 'curator'; const admin = workspace.data?.role === 'admin';
  const error = workspace.error || members.error || categories.error || feeds.error || articles.error || bookmarks.error || activity.error || action.error;
  const errorMessage = isProblemError(error) && error.problem.type.endsWith('/plan-limit-reached') ? 'workspaces.planLimit' : 'workspaces.failed';
  const run = (work: () => Promise<void>) => action.mutate(work);
  return <Stack spacing={3} sx={{ p: 3 }}>
    <Typography variant="h4">{workspace.data?.name ?? t('workspaces.title')}</Typography>
    {error && <Alert severity="error">{t(errorMessage)}</Alert>}
    {action.isSuccess && <Alert severity="success">{t('workspaces.saved')}</Alert>}
    <Tabs value={tab} onChange={(_, value: string) => setTab(value)} variant="scrollable" scrollButtons="auto">
      {['articles', 'bookmarks', 'feeds', 'members', 'activity'].map((value) => <Tab key={value} value={value} label={t(`workspaces.${value}`)} />)}
    </Tabs>
    {tab === 'articles' && <Stack spacing={2}>
      {articles.data?.map((article) => <Card key={article.id}><CardContent>
        <Typography variant="h6" component={Link} to={`/workspaces/${id}/articles/${article.id}`}>{article.title}</Typography>
        <Typography>{article.aiSummary ?? article.summary}</Typography>
        <Button onClick={() => run(() => workspaceApi.read(id, article.id, !article.isRead))}>{t(article.isRead ? 'article.markAsUnread' : 'article.markAsRead')}</Button>
        {write && <Button disabled={action.isPending} onClick={() => run(() => workspaceApi.bookmark(id, article.id))}>{t('article.addBookmark')}</Button>}
      </CardContent></Card>)}
      <Stack direction="row" spacing={2}><Button disabled={page === 1} onClick={() => setPage((value) => value - 1)}>{t('workspaces.previous')}</Button><Button disabled={(articles.data?.length ?? 0) < 20} onClick={() => setPage((value) => value + 1)}>{t('workspaces.next')}</Button></Stack>
    </Stack>}
    {tab === 'bookmarks' && bookmarks.data?.map((bookmark) => <Card key={bookmark.id}><CardContent>
      <Typography variant="h6" component={Link} to={`/workspaces/${id}/articles/${bookmark.article.id}`}>{bookmark.article.title}</Typography>
      <Typography color="text.secondary">{t('workspaces.curatedBy')}: {bookmark.curator || t('workspaces.member')}</Typography>
      {bookmark.annotations.map((annotation) => <Box key={annotation.id} sx={{ my: 2 }}><Typography sx={{ whiteSpace: 'pre-wrap' }}>{annotation.body}</Typography><Typography variant="caption">{annotation.author || t('workspaces.member')} · {new Date(annotation.createdAt).toLocaleString()}</Typography></Box>)}
      {write && <Box component="form" onSubmit={(event) => { event.preventDefault(); const form = event.currentTarget; const body = String(new FormData(form).get('body')); action.mutate(() => workspaceApi.annotate(id, bookmark.id, body), { onSuccess: () => form.reset() }); }}>
        <Stack direction="row" spacing={2}><TextField name="body" label={t('workspaces.annotation')} required fullWidth multiline slotProps={{ htmlInput: { maxLength: 5000 } }} /><Button type="submit" disabled={action.isPending}>{t('common.save')}</Button></Stack>
        <Button color="error" onClick={() => { if (window.confirm(t('workspaces.removeBookmarkConfirm'))) run(() => workspaceApi.removeBookmark(id, bookmark.id)); }}>{t('workspaces.removeBookmark')}</Button>
      </Box>}
    </CardContent></Card>)}
    {tab === 'feeds' && <Stack spacing={3}>
      {write && <>
        <Box component="form" onSubmit={(event) => { event.preventDefault(); const form = event.currentTarget; const data = new FormData(form); action.mutate(() => workspaceApi.createCategory(id, { name: String(data.get('name')), slug: String(data.get('slug')) }), { onSuccess: () => form.reset() }); }}>
          <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2}><TextField name="name" label={t('workspaces.categoryName')} required slotProps={{ htmlInput: { maxLength: 100 } }} /><TextField name="slug" label={t('workspaces.categorySlug')} required slotProps={{ htmlInput: { pattern: '[a-z0-9-]+', maxLength: 100 } }} /><Button type="submit" disabled={action.isPending}>{t('workspaces.addCategory')}</Button></Stack>
        </Box>
        <Box component="form" onSubmit={(event) => { event.preventDefault(); const form = event.currentTarget; const data = new FormData(form); action.mutate(() => workspaceApi.createFeed(id, { url: String(data.get('url')), title: String(data.get('title')), categoryId: String(data.get('categoryId')) }), { onSuccess: () => form.reset() }); }}>
          <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2}><TextField name="title" label={t('workspaces.feedTitle')} required /><TextField name="url" label={t('workspaces.feedUrl')} type="url" required /><TextField select name="categoryId" label={t('workspaces.categoryName')} defaultValue="" required sx={{ minWidth: 180 }}>{categories.data?.map((category) => <MenuItem key={category.id} value={category.id}>{category.name}</MenuItem>)}</TextField><Button type="submit" disabled={action.isPending}>{t('workspaces.addFeed')}</Button></Stack>
        </Box>
      </>}
      {feeds.data?.map((feed) => <Card key={feed.id}><CardContent><Typography variant="h6">{feed.title}</Typography><Typography>{feed.categoryName} · {t(`feedStatus.${feed.status}`)}</Typography>
        {write && <Stack direction="row"><Button onClick={() => run(() => workspaceApi.updateFeed(id, feed.id, { title: feed.title, categoryId: feed.categoryId, status: feed.status === 'paused' ? 'active' : 'paused' }))}>{t(feed.status === 'paused' ? 'workspaces.resume' : 'workspaces.pause')}</Button><Button color="error" onClick={() => { if (window.confirm(t('workspaces.deleteFeedConfirm'))) run(() => workspaceApi.deleteFeed(id, feed.id)); }}>{t('common.delete')}</Button></Stack>}
      </CardContent></Card>)}
      <Divider />
      {categories.data?.map((category) => <Stack key={category.id} direction="row" spacing={2}><Typography>{category.name}</Typography>{write && <Button onClick={() => { const name = window.prompt(t('workspaces.categoryName'), category.name); if (name?.trim()) run(() => workspaceApi.updateCategory(id, category.id, { name: name.trim(), slug: category.slug, description: category.description ?? undefined, color: category.color ?? undefined, position: category.position })); }}>{t('common.edit')}</Button>}{write && <Button color="error" onClick={() => { if (window.confirm(t('workspaces.deleteCategoryConfirm'))) run(() => workspaceApi.deleteCategory(id, category.id)); }}>{t('common.delete')}</Button>}</Stack>)}
    </Stack>}
    {tab === 'members' && <Stack spacing={3}>
      {admin && <Box component="form" onSubmit={(event) => { event.preventDefault(); const form = event.currentTarget; const data = new FormData(form); action.mutate(() => workspaceApi.invite(id, String(data.get('email')), String(data.get('role')) as MemberRole), { onSuccess: () => form.reset() }); }}>
        <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2}><TextField name="email" label={t('workspaces.email')} type="email" required /><TextField select name="role" label={t('workspaces.role')} defaultValue="reader" sx={{ minWidth: 160 }}>{(['admin', 'curator', 'reader'] as MemberRole[]).map((role) => <MenuItem key={role} value={role}>{t(`workspaces.${role}`)}</MenuItem>)}</TextField><Button type="submit" disabled={action.isPending}>{t('workspaces.invite')}</Button></Stack>
      </Box>}
      {members.data?.map((member) => <Stack key={member.id} direction="row" spacing={2} alignItems="center"><Typography sx={{ flex: 1 }}>{member.name || member.email || t('workspaces.member')}</Typography>
        {admin ? <><TextField select size="small" label={t('workspaces.role')} value={member.role} disabled={action.isPending} onChange={(event) => run(() => workspaceApi.role(id, member.id, event.target.value as MemberRole))}>{(['admin', 'curator', 'reader'] as MemberRole[]).map((role) => <MenuItem key={role} value={role}>{t(`workspaces.${role}`)}</MenuItem>)}</TextField><Button color="error" disabled={action.isPending} onClick={() => { if (window.confirm(t('workspaces.removeMemberConfirm'))) run(() => workspaceApi.remove(id, member.id)); }}>{t('workspaces.removeMember')}</Button></> : <Typography>{t(`workspaces.${member.role}`)}</Typography>}
      </Stack>)}
      {admin && <Typography color="text.secondary">{t('workspaces.lastAdmin')}</Typography>}
    </Stack>}
    {tab === 'activity' && activity.data?.map((item, index) => <Typography key={`${item.createdAt}-${index}`}>{new Date(item.createdAt).toLocaleString()} · {item.actor || t('workspaces.member')} · {t(`workspaces.activity_${item.action}`)}</Typography>)}
  </Stack>;
}
