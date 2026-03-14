import { useState } from 'react';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import Chip from '@mui/material/Chip';
import Grid from '@mui/material/Grid';
import AddIcon from '@mui/icons-material/Add';
import RssFeedIcon from '@mui/icons-material/RssFeed';
import { useTranslation } from 'react-i18next';
import ArticleList from '../components/Article/ArticleList';
import SearchBar from '../components/Article/SearchBar';
import AddFeedDialog from '../components/Feed/AddFeedDialog';
import CategoryDialog from '../components/Category/CategoryDialog';
import { useArticles, useMarkArticleRead, useMarkArticleUnread } from '../hooks/useArticles';
import { useCategories, useCreateCategory } from '../hooks/useCategories';
import { useFeeds, useAddFeed } from '../hooks/useFeeds';
import { useBookmarks, useCreateBookmark, useDeleteBookmark } from '../hooks/useBookmarks';
import type { CreateCategoryInput, AddFeedInput } from '../types';

export default function Dashboard() {
  const { t } = useTranslation();
  const [addFeedOpen, setAddFeedOpen] = useState(false);
  const [addCategoryOpen, setAddCategoryOpen] = useState(false);
  const [search, setSearch] = useState('');
  const [unreadOnly, setUnreadOnly] = useState(false);
  const [page, setPage] = useState(1);

  const {
    data: articles,
    isLoading: articlesLoading,
    isError: articlesError,
    error: articlesErrorData,
    refetch: refetchArticles,
  } = useArticles({ ...(search ? { search } : {}), ...(unreadOnly ? { isRead: false } : {}), page });

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

  const unreadCount = articles?.items.filter((a) => !a.isRead).length ?? 0;

  return (
    <Box>
      <Box
        display="flex"
        justifyContent="space-between"
        alignItems={{ xs: 'flex-start', sm: 'center' }}
        flexDirection={{ xs: 'column', sm: 'row' }}
        gap={2}
        mb={3}
      >
        <Box>
          <Typography variant="h4" fontWeight="bold">
            {t('dashboard.title')}
          </Typography>
          <Box display="flex" gap={1} mt={0.5}>
            <Chip
              label={t('dashboard.unread', { count: unreadCount })}
              size="small"
              color={unreadOnly ? 'primary' : unreadCount > 0 ? 'primary' : 'default'}
              variant={unreadOnly ? 'filled' : 'outlined'}
              clickable
              onClick={() => { setUnreadOnly((prev) => !prev); setPage(1); }}
            />
          </Box>
        </Box>
        <Box display="flex" gap={1} flexWrap="wrap">
          <Button
            variant="outlined"
            startIcon={<AddIcon />}
            onClick={() => setAddCategoryOpen(true)}
          >
            {t('dashboard.addCategory')}
          </Button>
          <Button
            variant="contained"
            startIcon={<RssFeedIcon />}
            onClick={() => setAddFeedOpen(true)}
            disabled={categories.length === 0}
          >
            {t('dashboard.addFeed')}
          </Button>
        </Box>
      </Box>

      {categories.length === 0 && feeds.length === 0 ? (
        <Box textAlign="center" py={8}>
          <Typography variant="h6" color="text.secondary" gutterBottom>
            {t('dashboard.welcome')}
          </Typography>
          <Typography variant="body2" color="text.secondary" mb={3}>
            {t('dashboard.welcomeMessage')}
          </Typography>
          <Button
            variant="contained"
            startIcon={<AddIcon />}
            onClick={() => setAddCategoryOpen(true)}
          >
            {t('dashboard.createFirstCategory')}
          </Button>
        </Box>
      ) : (
        <>
          <Box mb={2}>
            <SearchBar value={search} onChange={(v) => { setSearch(v); setPage(1); }} />
          </Box>
          <Grid container spacing={3}>
            <Grid size={12}>
              <ArticleList
                data={articles}
                bookmarks={bookmarks}
                isLoading={articlesLoading}
                isError={articlesError}
                error={articlesErrorData}
                onRefetch={refetchArticles}
                onToggleRead={handleToggleRead}
                onToggleBookmark={handleToggleBookmark}
                onPageChange={setPage}
              />
            </Grid>
          </Grid>
        </>
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
