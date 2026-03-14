import { useState, useMemo, useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import IconButton from '@mui/material/IconButton';
import Tooltip from '@mui/material/Tooltip';
import Paper from '@mui/material/Paper';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import ListItemSecondaryAction from '@mui/material/ListItemSecondaryAction';
import RssFeedIcon from '@mui/icons-material/RssFeed';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import { useTranslation } from 'react-i18next';
import AddFeedDialog from '../components/Feed/AddFeedDialog';
import EditFeedDialog from '../components/Feed/EditFeedDialog';
import FeedStatusChip from '../components/Feed/FeedStatusChip';
import LoadingSpinner from '../components/Common/LoadingSpinner';
import ErrorAlert from '../components/Common/ErrorAlert';
import EmptyState from '../components/Common/EmptyState';
import { useFeeds, useAddFeed, useUpdateFeed, useDeleteFeed } from '../hooks/useFeeds';
import { useCategories } from '../hooks/useCategories';
import type { Feed, AddFeedInput, UpdateFeedInput } from '../types';

export default function FeedManagementPage() {
  const { t, i18n } = useTranslation();
  const [addDialogOpen, setAddDialogOpen] = useState(false);
  const [editDialogOpen, setEditDialogOpen] = useState(false);
  const [editingFeed, setEditingFeed] = useState<Feed | null>(null);

  const location = useLocation();
  const { data: feeds, isLoading, isError, error, refetch } = useFeeds();
  const { data: categories = [] } = useCategories();
  const addFeed = useAddFeed();
  const updateFeed = useUpdateFeed();
  const deleteFeed = useDeleteFeed();

  const feedsByCategory = useMemo(() => {
    if (!feeds) return {};
    return feeds.reduce<Record<string, { name: string; feeds: Feed[] }>>((acc, feed) => {
      const key = feed.categoryId;
      if (!acc[key]) acc[key] = { name: feed.categoryName, feeds: [] };
      acc[key].feeds.push(feed);
      return acc;
    }, {});
  }, [feeds]);

  useEffect(() => {
    if (!location.hash || isLoading) return;
    const id = location.hash.slice(1);
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }, [location.hash, isLoading, feedsByCategory]);

  const handleAddFeed = (data: AddFeedInput) => {
    addFeed.mutate(data, {
      onSuccess: () => setAddDialogOpen(false),
    });
  };

  const handleEditFeed = (feed: Feed) => {
    setEditingFeed(feed);
    setEditDialogOpen(true);
  };

  const handleUpdateFeed = (data: UpdateFeedInput) => {
    if (!editingFeed) return;
    updateFeed.mutate(
      { id: editingFeed.id, input: data },
      {
        onSuccess: () => {
          setEditDialogOpen(false);
          setEditingFeed(null);
        },
      },
    );
  };

  const handleDeleteFeed = (feed: Feed) => {
    if (window.confirm(t('feeds.deleteConfirm', { title: feed.title }))) {
      deleteFeed.mutate(feed.id);
    }
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return t('common.never');
    return new Date(dateString).toLocaleDateString(
      i18n.language === 'fr' ? 'fr-FR' : 'en-US',
      {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      }
    );
  };

  if (isLoading) {
    return <LoadingSpinner message={t('feeds.loading')} />;
  }

  if (isError) {
    return (
      <ErrorAlert
        title={t('feeds.failedToLoad')}
        message={error?.message || t('feeds.anErrorOccurred')}
        onRetry={refetch}
      />
    );
  }

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
            {t('feeds.title')}
          </Typography>
          <Typography variant="body2" color="text.secondary">
            {t('feeds.count', { count: feeds?.length ?? 0 })}
          </Typography>
        </Box>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => setAddDialogOpen(true)}
          disabled={categories.length === 0}
        >
          {t('feeds.addFeed')}
        </Button>
      </Box>

      {!feeds || feeds.length === 0 ? (
        <EmptyState
          icon={<RssFeedIcon fontSize="inherit" />}
          title={t('feeds.noFeeds')}
          description={t('feeds.addFirst')}
        />
      ) : (
        Object.entries(feedsByCategory)
          .sort(([, a], [, b]) => a.name.localeCompare(b.name))
          .map(([categoryId, { name, feeds: categoryFeeds }]) => (
            <Paper key={categoryId} id={categoryId} sx={{ mb: 3 }}>
              <Box sx={{ px: 2, pt: 2, pb: 1 }}>
                <Typography variant="subtitle2" color="text.secondary" textTransform="uppercase">
                  {name}
                </Typography>
              </Box>
              <List disablePadding>
                {categoryFeeds.map((feed, index) => (
                  <ListItem
                    key={feed.id}
                    sx={{
                      py: 1.5,
                      borderTop: index > 0 ? '1px solid' : 'none',
                      borderColor: 'divider',
                    }}
                  >
                    <ListItemText
                      primaryTypographyProps={{ component: 'div' }}
                      secondaryTypographyProps={{ component: 'div' }}
                      primary={
                        <Box display="flex" alignItems="center" gap={1}>
                          <Typography variant="subtitle1" fontWeight={500}>
                            {feed.title}
                          </Typography>
                          <FeedStatusChip status={feed.status} />
                        </Box>
                      }
                      secondary={
                        <Box mt={0.5}>
                          <Typography variant="caption" color="text.secondary" noWrap component="div">
                            {feed.url}
                          </Typography>
                          <Typography variant="caption" color="text.secondary" component="div">
                            {t('feeds.lastFetched', { date: formatDate(feed.lastFetchedAt) })}
                          </Typography>
                          {feed.lastError && (
                            <Typography variant="caption" color="error" component="div">
                              {feed.lastError}
                            </Typography>
                          )}
                        </Box>
                      }
                    />
                    <ListItemSecondaryAction>
                      <Tooltip title={t('feeds.edit')}>
                        <IconButton onClick={() => handleEditFeed(feed)}>
                          <EditIcon />
                        </IconButton>
                      </Tooltip>
                      <Tooltip title={t('feeds.delete')}>
                        <IconButton onClick={() => handleDeleteFeed(feed)} color="error">
                          <DeleteIcon />
                        </IconButton>
                      </Tooltip>
                    </ListItemSecondaryAction>
                  </ListItem>
                ))}
              </List>
            </Paper>
          ))
      )}

      <AddFeedDialog
        open={addDialogOpen}
        onClose={() => setAddDialogOpen(false)}
        onSubmit={handleAddFeed}
        categories={categories}
        isLoading={addFeed.isPending}
      />

      {editingFeed && (
        <EditFeedDialog
          key={editingFeed.id}
          open={editDialogOpen}
          onClose={() => {
            setEditDialogOpen(false);
            setEditingFeed(null);
          }}
          onSubmit={handleUpdateFeed}
          feed={editingFeed}
          categories={categories}
          isLoading={updateFeed.isPending}
        />
      )}
    </Box>
  );
}
