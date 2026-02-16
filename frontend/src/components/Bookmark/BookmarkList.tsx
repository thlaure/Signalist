import { useNavigate } from 'react-router-dom';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import ListItemSecondaryAction from '@mui/material/ListItemSecondaryAction';
import IconButton from '@mui/material/IconButton';
import Link from '@mui/material/Link';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
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
  const navigate = useNavigate();

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
    <List>
      {bookmarks.map((bookmark, index) => (
        <ListItem
          key={bookmark.id}
          sx={{
            py: 2,
            borderBottom: index < bookmarks.length - 1 ? '1px solid' : 'none',
            borderColor: 'divider',
            flexDirection: { xs: 'column', sm: 'row' },
            alignItems: { xs: 'flex-start', sm: 'center' },
            gap: { xs: 1, sm: 0 },
          }}
        >
          <ListItemText
            primary={
              <Link
                component="button"
                variant="subtitle1"
                underline="hover"
                onClick={() => navigate(`/articles/${bookmark.articleId}`)}
                sx={{ fontWeight: 500, textAlign: 'left' }}
              >
                {bookmark.articleTitle}
              </Link>
            }
            secondary={
              <Box mt={0.5}>
                <Typography variant="caption" color="text.secondary">
                  {bookmark.feedTitle} &middot; {bookmark.categoryName}
                </Typography>
                {bookmark.notes && (
                  <Typography variant="body2" color="text.secondary" mt={0.5}>
                    {bookmark.notes}
                  </Typography>
                )}
                <Typography variant="caption" color="text.secondary" component="div" mt={0.5}>
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
  );
}
