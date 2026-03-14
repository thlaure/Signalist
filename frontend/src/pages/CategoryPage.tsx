import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import IconButton from '@mui/material/IconButton';
import Chip from '@mui/material/Chip';
import Menu from '@mui/material/Menu';
import MenuItem from '@mui/material/MenuItem';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import Divider from '@mui/material/Divider';
import RssFeedIcon from '@mui/icons-material/RssFeed';
import MoreVertIcon from '@mui/icons-material/MoreVert';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import { useTranslation } from 'react-i18next';
import ArticleList from '../components/Article/ArticleList';
import SearchBar from '../components/Article/SearchBar';
import AddFeedDialog from '../components/Feed/AddFeedDialog';
import CategoryDialog from '../components/Category/CategoryDialog';
import LoadingSpinner from '../components/Common/LoadingSpinner';
import ErrorAlert from '../components/Common/ErrorAlert';
import { useArticles, useMarkArticleRead, useMarkArticleUnread } from '../hooks/useArticles';
import {
  useCategory,
  useCategories,
  useUpdateCategory,
  useDeleteCategory,
} from '../hooks/useCategories';
import { useFeeds, useAddFeed } from '../hooks/useFeeds';
import { useBookmarks, useCreateBookmark, useDeleteBookmark } from '../hooks/useBookmarks';
import type { CreateCategoryInput, AddFeedInput } from '../types';

export default function CategoryPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { t } = useTranslation();

  const [addFeedOpen, setAddFeedOpen] = useState(false);
  const [editCategoryOpen, setEditCategoryOpen] = useState(false);
  const [menuAnchor, setMenuAnchor] = useState<null | HTMLElement>(null);
  const [search, setSearch] = useState('');
  const [unreadOnly, setUnreadOnly] = useState(false);
  const [page, setPage] = useState(1);

  const {
    data: category,
    isLoading: categoryLoading,
    isError: categoryError,
    error: categoryErrorData,
    refetch: refetchCategory,
  } = useCategory(id!);

  const { data: categories = [] } = useCategories();

  const {
    data: articles,
    isLoading: articlesLoading,
    isError: articlesError,
    error: articlesErrorData,
    refetch: refetchArticles,
  } = useArticles({ categoryId: id, ...(search ? { search } : {}), ...(unreadOnly ? { isRead: false } : {}), page });

  const { data: feeds = [] } = useFeeds({ categoryId: id });
  const { data: bookmarks } = useBookmarks();

  const addFeed = useAddFeed();
  const updateCategory = useUpdateCategory();
  const deleteCategory = useDeleteCategory();
  const markRead = useMarkArticleRead();
  const markUnread = useMarkArticleUnread();
  const createBookmark = useCreateBookmark();
  const deleteBm = useDeleteBookmark();

  const handleAddFeed = (data: AddFeedInput) => {
    addFeed.mutate(
      { ...data, categoryId: id! },
      {
        onSuccess: () => setAddFeedOpen(false),
      }
    );
  };

  const handleUpdateCategory = (data: CreateCategoryInput) => {
    if (!id) return;
    updateCategory.mutate(
      { id, input: data },
      {
        onSuccess: () => setEditCategoryOpen(false),
      }
    );
  };

  const handleDeleteCategory = () => {
    if (!id) return;
    if (window.confirm(t('category.deleteConfirm'))) {
      deleteCategory.mutate(id, {
        onSuccess: () => navigate('/'),
      });
    }
    setMenuAnchor(null);
  };

  const handleToggleRead = (articleId: string, isRead: boolean) => {
    if (isRead) {
      markUnread.mutate(articleId);
    } else {
      markRead.mutate(articleId);
    }
  };

  const handleToggleBookmark = (articleId: string, isBookmarked: boolean) => {
    if (isBookmarked) {
      const bookmark = bookmarks?.find((b) => b.articleId === articleId);
      if (bookmark) {
        deleteBm.mutate(bookmark.id);
      }
    } else {
      createBookmark.mutate({ articleId });
    }
  };

  if (categoryLoading) {
    return <LoadingSpinner message={t('category.loading')} />;
  }

  if (categoryError || !category) {
    return (
      <ErrorAlert
        title={t('category.failedToLoad')}
        message={categoryErrorData?.message || t('category.notFound')}
        onRetry={refetchCategory}
      />
    );
  }

  const unreadCount = articles?.items.filter((a) => !a.isRead).length ?? 0;

  return (
    <Box>
      <Box
        display="flex"
        justifyContent="space-between"
        alignItems="flex-start"
        mb={3}
      >
        <Box>
          <Box display="flex" alignItems="center" gap={2} mb={1}>
            <Typography variant="h4" fontWeight="bold">
              {category.name}
            </Typography>
            {category.color && (
              <Box
                sx={{
                  width: 16,
                  height: 16,
                  borderRadius: '50%',
                  backgroundColor: category.color,
                }}
              />
            )}
          </Box>
          {category.description && (
            <Typography variant="body2" color="text.secondary" mb={1}>
              {category.description}
            </Typography>
          )}
          <Box display="flex" gap={1}>
            <Chip
              label={t('category.feeds', { count: feeds.length })}
              size="small"
              variant="outlined"
              clickable
              onClick={() => navigate(`/feeds#${id}`)}
            />
            <Chip
              label={t('category.unread', { count: unreadCount })}
              size="small"
              color={unreadOnly ? 'primary' : unreadCount > 0 ? 'primary' : 'default'}
              variant={unreadOnly ? 'filled' : 'outlined'}
              clickable
              onClick={() => { setUnreadOnly((prev) => !prev); setPage(1); }}
            />
          </Box>
        </Box>
        <Box display="flex" gap={1}>
          <Button
            variant="contained"
            startIcon={<RssFeedIcon />}
            onClick={() => setAddFeedOpen(true)}
          >
            {t('category.addFeed')}
          </Button>
          <IconButton onClick={(e) => setMenuAnchor(e.currentTarget)}>
            <MoreVertIcon />
          </IconButton>
          <Menu
            anchorEl={menuAnchor}
            open={Boolean(menuAnchor)}
            onClose={() => setMenuAnchor(null)}
          >
            <MenuItem
              onClick={() => {
                setEditCategoryOpen(true);
                setMenuAnchor(null);
              }}
            >
              <ListItemIcon>
                <EditIcon fontSize="small" />
              </ListItemIcon>
              <ListItemText>{t('category.editCategory')}</ListItemText>
            </MenuItem>
            <Divider />
            <MenuItem onClick={handleDeleteCategory} sx={{ color: 'error.main' }}>
              <ListItemIcon>
                <DeleteIcon fontSize="small" color="error" />
              </ListItemIcon>
              <ListItemText>{t('category.deleteCategory')}</ListItemText>
            </MenuItem>
          </Menu>
        </Box>
      </Box>

      <Box mb={2}>
        <SearchBar value={search} onChange={(v) => { setSearch(v); setPage(1); }} />
      </Box>

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

      <AddFeedDialog
        open={addFeedOpen}
        onClose={() => setAddFeedOpen(false)}
        onSubmit={handleAddFeed}
        categories={categories}
        isLoading={addFeed.isPending}
      />

      <CategoryDialog
        open={editCategoryOpen}
        onClose={() => setEditCategoryOpen(false)}
        onSubmit={handleUpdateCategory}
        category={category}
        isLoading={updateCategory.isPending}
      />
    </Box>
  );
}
