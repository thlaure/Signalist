import { Box, Button, Card, CardContent, Stack, TextField, Typography, Alert } from '@mui/material';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useWorkspaces } from '../hooks/useWorkspaces';
export default function WorkspacesPage() {
  const { t } = useTranslation(); const { list, create } = useWorkspaces();
  return <Stack spacing={3} sx={{ p: 3, maxWidth: 900, mx: 'auto' }}>
    <Typography variant="h4">{t('workspaces.title')}</Typography>
    {(list.error || create.error) && <Alert severity="error">{t('workspaces.failed')}</Alert>}
    <Box component="form" onSubmit={(event) => { event.preventDefault(); const data = new FormData(event.currentTarget); create.mutate(String(data.get('name'))); }}>
      <Stack direction="row" spacing={2}><TextField name="name" required label={t('workspaces.name')} slotProps={{ htmlInput: { maxLength: 100 } }} /><Button type="submit" disabled={create.isPending}>{t('workspaces.create')}</Button></Stack>
    </Box>
    {list.data?.map((workspace) => <Card key={workspace.id}><CardContent><Typography variant="h6" component={Link} to={`/workspaces/${workspace.id}`}>{workspace.name}</Typography><Typography>{t(`workspaces.${workspace.role}`)}</Typography></CardContent></Card>)}
  </Stack>;
}
