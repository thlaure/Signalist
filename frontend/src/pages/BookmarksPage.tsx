import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import { useTranslation } from 'react-i18next';
import BookmarkList from '../components/Bookmark/BookmarkList';
import { useBookmarks, useDeleteBookmark } from '../hooks/useBookmarks';

export default function BookmarksPage() {
  const { t } = useTranslation();
  const {
    data: bookmarks,
    isLoading,
    isError,
    error,
    refetch,
  } = useBookmarks();

  const deleteBookmark = useDeleteBookmark();

  const handleDelete = (id: string) => {
    if (window.confirm(t('bookmarks.removeConfirm'))) {
      deleteBookmark.mutate(id);
    }
  };

  return (
    <Box>
      <Box mb={3}>
        <Typography variant="h4" fontWeight="bold">
          {t('bookmarks.title')}
        </Typography>
        <Typography variant="body2" color="text.secondary">
          {t('bookmarks.count', { count: bookmarks?.length ?? 0 })}
        </Typography>
      </Box>

      <BookmarkList
        bookmarks={bookmarks}
        isLoading={isLoading}
        isError={isError}
        error={error}
        onRefetch={refetch}
        onDelete={handleDelete}
      />
    </Box>
  );
}
