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
import OpenInNewIcon from '@mui/icons-material/OpenInNew';
import BookmarkIcon from '@mui/icons-material/Bookmark';
import { useTranslation } from 'react-i18next';
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
  const { t, i18n } = useTranslation();

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString(
      i18n.language === 'fr' ? 'fr-FR' : 'en-US',
      {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
      }
    );
  };

  if (isLoading) {
    return <LoadingSpinner message={t('bookmarks.loading')} />;
  }

  if (isError) {
    return (
      <ErrorAlert
        title={t('bookmarks.failedToLoad')}
        message={error?.message || t('common.error')}
        onRetry={onRefetch}
      />
    );
  }

  if (!bookmarks || bookmarks.length === 0) {
    return (
      <EmptyState
        icon={<BookmarkIcon fontSize="inherit" />}
        title={t('bookmarks.noBookmarks')}
        description={t('bookmarks.saveForLater')}
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
            secondaryTypographyProps={{ component: 'div' }}
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
                  {t('bookmarks.bookmarkedOn', { date: formatDate(bookmark.createdAt) })}
                </Typography>
              </Box>
            }
          />
          <ListItemSecondaryAction>
            <Tooltip title={t('bookmarks.openArticle')}>
              <IconButton
                component="a"
                href={bookmark.articleUrl}
                target="_blank"
                rel="noopener noreferrer"
              >
                <OpenInNewIcon />
              </IconButton>
            </Tooltip>
            <Tooltip title={t('bookmarks.removeBookmark')}>
              <IconButton
                edge="end"
                onClick={() => onDelete(bookmark.id)}
                color="primary"
              >
                <BookmarkIcon />
              </IconButton>
            </Tooltip>
          </ListItemSecondaryAction>
        </ListItem>
      ))}
    </List>
  );
}
