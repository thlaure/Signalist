import { useState } from 'react';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import Grid from '@mui/material/Grid';
import AddIcon from '@mui/icons-material/Add';
import RssFeedIcon from '@mui/icons-material/RssFeed';
import ArticleList from '../components/Article/ArticleList';
import AddFeedDialog from '../components/Feed/AddFeedDialog';
import CategoryDialog from '../components/Category/CategoryDialog';
import { useArticles, useMarkArticleRead, useMarkArticleUnread } from '../hooks/useArticles';
import { useCategories, useCreateCategory } from '../hooks/useCategories';
import { useFeeds, useAddFeed } from '../hooks/useFeeds';
import { useBookmarks, useCreateBookmark, useDeleteBookmark } from '../hooks/useBookmarks';
import type { CreateCategoryInput, AddFeedInput } from '../types';

export default function Dashboard() {
  const [addFeedOpen, setAddFeedOpen] = useState(false);
  const [addCategoryOpen, setAddCategoryOpen] = useState(false);

  const {
    data: articles,
    isLoading: articlesLoading,
    isError: articlesError,
    error: articlesErrorData,
    refetch: refetchArticles,
  } = useArticles();

  const { data: categories = [] } = useCategories();
  const { data: feeds = [] } = useFeeds();
  const { data: bookmarks } = useBookmarks();

  const createCategory = useCreateCategory();
  const addFeed = useAddFeed();
  const markRead = useMarkArticleRead();
  const markUnread = useMarkArticleUnread();
  const createBookmark = useCreateBookmark();
  const deleteBookmark = useDeleteBookmark();

  const handleAddCategory = (data: CreateCategoryInput) => {
    createCategory.mutate(data, {
      onSuccess: () => setAddCategoryOpen(false),
    });
  };

  const handleAddFeed = (data: AddFeedInput) => {
    addFeed.mutate(data, {
      onSuccess: () => setAddFeedOpen(false),
    });
  };

  const handleToggleRead = (id: string, isRead: boolean) => {
    if (isRead) {
      markUnread.mutate(id);
    } else {
      markRead.mutate(id);
    }
  };

  const handleToggleBookmark = (articleId: string, isBookmarked: boolean) => {
    if (isBookmarked) {
      const bookmark = bookmarks?.find((b) => b.articleId === articleId);
      if (bookmark) {
        deleteBookmark.mutate(bookmark.id);
      }
    } else {
      createBookmark.mutate({ articleId });
    }
  };

  const unreadCount = articles?.filter((a) => !a.isRead).length ?? 0;

  return (
    <Box>
      <Box
        display="flex"
        justifyContent="space-between"
        alignItems="center"
        mb={3}
      >
        <Box>
          <Typography variant="h4" fontWeight="bold">
            Dashboard
          </Typography>
          <Typography variant="body2" color="text.secondary">
            {unreadCount} unread article{unreadCount !== 1 ? 's' : ''}
          </Typography>
        </Box>
        <Box display="flex" gap={1}>
          <Button
            variant="outlined"
            startIcon={<AddIcon />}
            onClick={() => setAddCategoryOpen(true)}
          >
            Category
          </Button>
          <Button
            variant="contained"
            startIcon={<RssFeedIcon />}
            onClick={() => setAddFeedOpen(true)}
            disabled={categories.length === 0}
          >
            Add Feed
          </Button>
        </Box>
      </Box>

      {categories.length === 0 && feeds.length === 0 ? (
        <Box textAlign="center" py={8}>
          <Typography variant="h6" color="text.secondary" gutterBottom>
            Welcome to Signalist
          </Typography>
          <Typography variant="body2" color="text.secondary" mb={3}>
            Get started by creating a category and adding your first RSS feed.
          </Typography>
          <Button
            variant="contained"
            startIcon={<AddIcon />}
            onClick={() => setAddCategoryOpen(true)}
          >
            Create Your First Category
          </Button>
        </Box>
      ) : (
        <Grid container spacing={3}>
          <Grid size={12}>
            <ArticleList
              articles={articles}
              bookmarks={bookmarks}
              isLoading={articlesLoading}
              isError={articlesError}
              error={articlesErrorData}
              onRefetch={refetchArticles}
              onToggleRead={handleToggleRead}
              onToggleBookmark={handleToggleBookmark}
            />
          </Grid>
        </Grid>
      )}

      <AddFeedDialog
        open={addFeedOpen}
        onClose={() => setAddFeedOpen(false)}
        onSubmit={handleAddFeed}
        categories={categories}
        isLoading={addFeed.isPending}
      />

      <CategoryDialog
        open={addCategoryOpen}
        onClose={() => setAddCategoryOpen(false)}
        onSubmit={handleAddCategory}
        isLoading={createCategory.isPending}
      />
    </Box>
  );
}
