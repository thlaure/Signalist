import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import BookmarkList from '../components/Bookmark/BookmarkList';
import { useBookmarks, useDeleteBookmark } from '../hooks/useBookmarks';

export default function BookmarksPage() {
  const {
    data: bookmarks,
    isLoading,
    isError,
    error,
    refetch,
  } = useBookmarks();

  const deleteBookmark = useDeleteBookmark();

  const handleDelete = (id: string) => {
    if (window.confirm('Remove this bookmark?')) {
      deleteBookmark.mutate(id);
    }
  };

  return (
    <Box>
      <Box mb={3}>
        <Typography variant="h4" fontWeight="bold">
          Bookmarks
        </Typography>
        <Typography variant="body2" color="text.secondary">
          {bookmarks?.length ?? 0} saved article
          {bookmarks?.length !== 1 ? 's' : ''}
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
