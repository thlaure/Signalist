import { useState } from 'react';
import { Alert, Autocomplete, Button, Stack, TextField } from '@mui/material';
import { useTranslation } from 'react-i18next';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import apiClient from '../../api/client';
import { useTags } from '../../hooks/useTags';
export default function ArticleTagsEditor({ id, names }: { id: string; names: string[] }) {
  const { t } = useTranslation(); const { tags, assign } = useTags();
  const [selected, setSelected] = useState(names); const cache = useQueryClient();
  const enrich = useMutation({ mutationFn: () => apiClient.post(`/articles/${id}/enrich`), onSuccess: () => cache.invalidateQueries({ queryKey: ['article', id] }) });
  return <Stack spacing={1} sx={{ mb: 3 }}>
    <Autocomplete multiple freeSolo options={tags.data?.map((tag) => tag.name) ?? []} value={selected} onChange={(_, value) => setSelected(value)} renderInput={(params) => <TextField {...params} label={t('tags.labels')} />} />
    <Stack direction="row" spacing={1}><Button disabled={assign.isPending || selected.length > 20} onClick={() => assign.mutate({ id, names: selected })}>{t('tags.save')}</Button><Button disabled={enrich.isPending || enrich.isSuccess} onClick={() => enrich.mutate()}>{t('tags.enrich')}</Button></Stack>
    {enrich.isSuccess && <Alert severity="info">{t('tags.queued')}</Alert>}
    {(assign.isError || enrich.isError) && <Alert severity="error">{t('tags.error')}</Alert>}
  </Stack>;
}
