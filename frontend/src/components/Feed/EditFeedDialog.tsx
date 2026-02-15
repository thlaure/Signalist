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
        <DialogTitle>Edit Feed</DialogTitle>
        <DialogContent>
          <Box display="flex" flexDirection="column" gap={2} mt={1}>
            <TextField
              label="Title"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              required
              fullWidth
              autoFocus
            />
            <FormControl fullWidth required>
              <InputLabel>Category</InputLabel>
              <Select
                value={categoryId}
                label="Category"
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
              <InputLabel>Status</InputLabel>
              <Select
                value={status}
                label="Status"
                onChange={(e) => setStatus(e.target.value as 'active' | 'paused')}
              >
                <MenuItem value="active">Active</MenuItem>
                <MenuItem value="paused">Paused</MenuItem>
              </Select>
            </FormControl>
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose} disabled={isLoading}>
            Cancel
          </Button>
          <Button type="submit" variant="contained" disabled={isLoading}>
            {isLoading ? 'Saving...' : 'Update'}
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
}
