import { useState } from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import { useTranslation } from 'react-i18next';
import type { Feed, Category, UpdateFeedInput } from '../../types';

interface EditFeedDialogProps {
  open: boolean;
  onClose: () => void;
  onSubmit: (data: UpdateFeedInput) => void;
  feed: Feed;
  categories: Category[];
  isLoading?: boolean;
}

export default function EditFeedDialog({
  open,
  onClose,
  onSubmit,
  feed,
  categories,
  isLoading = false,
}: EditFeedDialogProps) {
  const { t } = useTranslation();
  const [title, setTitle] = useState(feed.title);
  const [categoryId, setCategoryId] = useState(feed.categoryId);
  const [status, setStatus] = useState<'active' | 'paused'>(
    feed.status === 'error' ? 'paused' : feed.status as 'active' | 'paused'
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({ title, categoryId, status });
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <form onSubmit={handleSubmit}>
        <DialogTitle>{t('editFeedDialog.title')}</DialogTitle>
        <DialogContent>
          <Box display="flex" flexDirection="column" gap={2} mt={1}>
            <TextField
              label={t('editFeedDialog.titleLabel')}
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              required
              fullWidth
              autoFocus
            />
            <FormControl fullWidth required>
              <InputLabel>{t('editFeedDialog.categoryLabel')}</InputLabel>
              <Select
                value={categoryId}
                label={t('editFeedDialog.categoryLabel')}
                onChange={(e) => setCategoryId(e.target.value)}
              >
                {categories.map((category) => (
                  <MenuItem key={category.id} value={category.id}>
                    {category.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
            <FormControl fullWidth>
              <InputLabel>{t('editFeedDialog.statusLabel')}</InputLabel>
              <Select
                value={status}
                label={t('editFeedDialog.statusLabel')}
                onChange={(e) => setStatus(e.target.value as 'active' | 'paused')}
              >
                <MenuItem value="active">{t('editFeedDialog.active')}</MenuItem>
                <MenuItem value="paused">{t('editFeedDialog.paused')}</MenuItem>
              </Select>
            </FormControl>
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose} disabled={isLoading}>
            {t('editFeedDialog.cancel')}
          </Button>
          <Button type="submit" variant="contained" disabled={isLoading}>
            {isLoading ? t('editFeedDialog.saving') : t('editFeedDialog.update')}
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
}
