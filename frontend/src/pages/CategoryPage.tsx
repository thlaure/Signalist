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
import ArticleList from '../components/Article/ArticleList';
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

  const [addFeedOpen, setAddFeedOpen] = useState(false);
  const [editCategoryOpen, setEditCategoryOpen] = useState(false);
  const [menuAnchor, setMenuAnchor] = useState<null | HTMLElement>(null);

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
  } = useArticles({ categoryId: id });

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
    if (
      window.confirm(
        'Are you sure you want to delete this category? All feeds will be removed.'
      )
    ) {
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
    return <LoadingSpinner message="Loading category..." />;
  }

  if (categoryError || !category) {
    return (
      <ErrorAlert
        title="Failed to load category"
        message={categoryErrorData?.message || 'Category not found'}
        onRetry={refetchCategory}
      />
    );
  }

  const unreadCount = articles?.filter((a) => !a.isRead).length ?? 0;

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
              label={`${feeds.length} feed${feeds.length !== 1 ? 's' : ''}`}
              size="small"
              variant="outlined"
            />
            <Chip
              label={`${unreadCount} unread`}
              size="small"
              color={unreadCount > 0 ? 'primary' : 'default'}
              variant="outlined"
            />
          </Box>
        </Box>
        <Box display="flex" gap={1}>
          <Button
            variant="contained"
            startIcon={<RssFeedIcon />}
            onClick={() => setAddFeedOpen(true)}
          >
            Add Feed
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
              <ListItemText>Edit Category</ListItemText>
            </MenuItem>
            <Divider />
            <MenuItem onClick={handleDeleteCategory} sx={{ color: 'error.main' }}>
              <ListItemIcon>
                <DeleteIcon fontSize="small" color="error" />
              </ListItemIcon>
              <ListItemText>Delete Category</ListItemText>
            </MenuItem>
          </Menu>
        </Box>
      </Box>

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
