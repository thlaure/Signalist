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
import type { Category, AddFeedInput } from '../../types';

interface AddFeedDialogProps {
  open: boolean;
  onClose: () => void;
  onSubmit: (data: AddFeedInput) => void;
  categories: Category[];
  isLoading?: boolean;
}

export default function AddFeedDialog({
  open,
  onClose,
  onSubmit,
  categories,
  isLoading = false,
}: AddFeedDialogProps) {
  const { t } = useTranslation();
  const [url, setUrl] = useState('');
  const [title, setTitle] = useState('');
  const [categoryId, setCategoryId] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({
      url,
      categoryId,
      title: title || undefined,
    });
  };

  const handleClose = () => {
    setUrl('');
    setTitle('');
    setCategoryId('');
    onClose();
  };

  return (
    <Dialog open={open} onClose={handleClose} maxWidth="sm" fullWidth>
      <form onSubmit={handleSubmit}>
        <DialogTitle>{t('addFeedDialog.title')}</DialogTitle>
        <DialogContent>
          <Box display="flex" flexDirection="column" gap={2} mt={1}>
            <TextField
              label={t('addFeedDialog.urlLabel')}
              value={url}
              onChange={(e) => setUrl(e.target.value)}
              required
              fullWidth
              autoFocus
              placeholder={t('addFeedDialog.urlPlaceholder')}
              type="url"
            />
            <TextField
              label={t('addFeedDialog.titleLabel')}
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              fullWidth
              helperText={t('addFeedDialog.titleHelper')}
            />
            <FormControl fullWidth required>
              <InputLabel>{t('addFeedDialog.categoryLabel')}</InputLabel>
              <Select
                value={categoryId}
                label={t('addFeedDialog.categoryLabel')}
                onChange={(e) => setCategoryId(e.target.value)}
              >
                {categories.map((category) => (
                  <MenuItem key={category.id} value={category.id}>
                    {category.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose} disabled={isLoading}>
            {t('addFeedDialog.cancel')}
          </Button>
          <Button type="submit" variant="contained" disabled={isLoading}>
            {isLoading ? t('addFeedDialog.adding') : t('addFeedDialog.addFeed')}
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
}
