import { useState, useEffect } from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';
import { useTranslation } from 'react-i18next';
import type { Category, CreateCategoryInput } from '../../types';

interface CategoryDialogProps {
  open: boolean;
  onClose: () => void;
  onSubmit: (data: CreateCategoryInput) => void;
  category?: Category | null;
  isLoading?: boolean;
}

export default function CategoryDialog({
  open,
  onClose,
  onSubmit,
  category,
  isLoading = false,
}: CategoryDialogProps) {
  const { t } = useTranslation();
  const [name, setName] = useState('');
  const [slug, setSlug] = useState('');
  const [description, setDescription] = useState('');
  const [color, setColor] = useState('#1976d2');

  useEffect(() => {
    if (category) {
      // eslint-disable-next-line react-hooks/set-state-in-effect
      setName(category.name);
      setSlug(category.slug);
      setDescription(category.description || '');
      setColor(category.color || '#1976d2');
    } else {
      setName('');
      setSlug('');
      setDescription('');
      setColor('#1976d2');
    }
  }, [category, open]);

  const handleNameChange = (value: string) => {
    setName(value);
    if (!category) {
      setSlug(
        value
          .toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-|-$/g, '')
      );
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({
      name,
      slug,
      description: description || undefined,
      color: color || undefined,
    });
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <form onSubmit={handleSubmit}>
        <DialogTitle>
          {category ? t('categoryDialog.editTitle') : t('categoryDialog.addTitle')}
        </DialogTitle>
        <DialogContent>
          <Box display="flex" flexDirection="column" gap={2} mt={1}>
            <TextField
              label={t('categoryDialog.name')}
              value={name}
              onChange={(e) => handleNameChange(e.target.value)}
              required
              fullWidth
              autoFocus
            />
            <TextField
              label={t('categoryDialog.slug')}
              value={slug}
              onChange={(e) => setSlug(e.target.value)}
              required
              fullWidth
              helperText={t('categoryDialog.slugHelper')}
            />
            <TextField
              label={t('categoryDialog.description')}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              fullWidth
              multiline
              rows={2}
            />
            <Box display="flex" alignItems="center" gap={2}>
              <TextField
                label={t('categoryDialog.color')}
                type="color"
                value={color}
                onChange={(e) => setColor(e.target.value)}
                sx={{ width: 100 }}
                InputProps={{
                  sx: { height: 56 },
                }}
              />
              <Box
                sx={{
                  width: 40,
                  height: 40,
                  borderRadius: 1,
                  backgroundColor: color,
                }}
              />
            </Box>
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose} disabled={isLoading}>
            {t('categoryDialog.cancel')}
          </Button>
          <Button type="submit" variant="contained" disabled={isLoading}>
            {isLoading
              ? t('categoryDialog.saving')
              : category
                ? t('categoryDialog.update')
                : t('categoryDialog.create')}
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
}
