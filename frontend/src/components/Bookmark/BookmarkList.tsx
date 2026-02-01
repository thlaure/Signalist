import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import ListItemSecondaryAction from '@mui/material/ListItemSecondaryAction';
import IconButton from '@mui/material/IconButton';
import Typography from '@mui/material/Typography';
import Chip from '@mui/material/Chip';
import Box from '@mui/material/Box';
import Paper from '@mui/material/Paper';
import Tooltip from '@mui/material/Tooltip';
import DeleteIcon from '@mui/icons-material/Delete';
import OpenInNewIcon from '@mui/icons-material/OpenInNew';
import BookmarkIcon from '@mui/icons-material/Bookmark';
import LoadingSpinner from '../Common/LoadingSpinner';
import ErrorAlert from '../Common/ErrorAlert';
import EmptyState from '../Common/EmptyState';
import type { Bookmark } from '../../types';

interface BookmarkListProps {
  bookmarks: Bookmark[] | undefined;
  isLoading: boolean;
  isError: boolean;
  error: Error | null;
  onRefetch: () => void;
  onDelete: (id: string) => void;
}

export default function BookmarkList({
  bookmarks,
  isLoading,
  isError,
  error,
  onRefetch,
  onDelete,
}: BookmarkListProps) {
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  };

  if (isLoading) {
    return <LoadingSpinner message="Loading bookmarks..." />;
  }

  if (isError) {
    return (
      <ErrorAlert
        title="Failed to load bookmarks"
        message={error?.message || 'An error occurred'}
        onRetry={onRefetch}
      />
    );
  }

  if (!bookmarks || bookmarks.length === 0) {
    return (
      <EmptyState
        icon={<BookmarkIcon fontSize="inherit" />}
        title="No bookmarks yet"
        description="Bookmark articles to save them for later"
      />
    );
  }

  return (
    <Paper>
      <List>
        {bookmarks.map((bookmark, index) => (
          <ListItem
            key={bookmark.id}
            divider={index < bookmarks.length - 1}
            sx={{ py: 2 }}
          >
            <ListItemText
              primary={
                <Typography variant="subtitle1" fontWeight={500}>
                  {bookmark.articleTitle}
                </Typography>
              }
              secondary={
                <Box mt={1}>
                  <Box display="flex" gap={1} mb={1}>
                    <Chip
                      label={bookmark.categoryName}
                      size="small"
                      color="primary"
                      variant="outlined"
                    />
                    <Chip
                      label={bookmark.feedTitle}
                      size="small"
                      variant="outlined"
                    />
                  </Box>
                  {bookmark.notes && (
                    <Typography variant="body2" color="text.secondary" mb={1}>
                      {bookmark.notes}
                    </Typography>
                  )}
                  <Typography variant="caption" color="text.secondary">
                    Bookmarked on {formatDate(bookmark.createdAt)}
                  </Typography>
                </Box>
              }
            />
            <ListItemSecondaryAction>
              <Tooltip title="Open article">
                <IconButton
                  component="a"
                  href={bookmark.articleUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <OpenInNewIcon />
                </IconButton>
              </Tooltip>
              <Tooltip title="Remove bookmark">
                <IconButton
                  edge="end"
                  onClick={() => onDelete(bookmark.id)}
                  color="error"
                >
                  <DeleteIcon />
                </IconButton>
              </Tooltip>
            </ListItemSecondaryAction>
          </ListItem>
        ))}
      </List>
    </Paper>
  );
}
