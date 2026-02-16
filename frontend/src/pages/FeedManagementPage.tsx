import { useState, useMemo } from 'react';
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
  const [addDialogOpen, setAddDialogOpen] = useState(false);
  const [editDialogOpen, setEditDialogOpen] = useState(false);
  const [editingFeed, setEditingFeed] = useState<Feed | null>(null);

  const { data: feeds, isLoading, isError, error, refetch } = useFeeds();
  const { data: categories = [] } = useCategories();
  const addFeed = useAddFeed();
  const updateFeed = useUpdateFeed();
  const deleteFeed = useDeleteFeed();

  const feedsByCategory = useMemo(() => {
    if (!feeds) return {};
    return feeds.reduce<Record<string, Feed[]>>((acc, feed) => {
      const key = feed.categoryName;
      if (!acc[key]) acc[key] = [];
      acc[key].push(feed);
      return acc;
    }, {});
  }, [feeds]);

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
    if (window.confirm(`Delete feed "${feed.title}"? This will also delete all its articles.`)) {
      deleteFeed.mutate(feed.id);
    }
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  if (isLoading) {
    return <LoadingSpinner message="Loading feeds..." />;
  }

  if (isError) {
    return (
      <ErrorAlert
        title="Failed to load feeds"
        message={error?.message || 'An error occurred'}
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
            Feeds
          </Typography>
          <Typography variant="body2" color="text.secondary">
            {feeds?.length ?? 0} feed{feeds?.length !== 1 ? 's' : ''}
          </Typography>
        </Box>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => setAddDialogOpen(true)}
          disabled={categories.length === 0}
        >
          Add Feed
        </Button>
      </Box>

      {!feeds || feeds.length === 0 ? (
        <EmptyState
          icon={<RssFeedIcon fontSize="inherit" />}
          title="No feeds yet"
          description="Add your first RSS feed to get started"
        />
      ) : (
        Object.entries(feedsByCategory)
          .sort(([a], [b]) => a.localeCompare(b))
          .map(([categoryName, categoryFeeds]) => (
            <Paper key={categoryName} sx={{ mb: 3 }}>
              <Box sx={{ px: 2, pt: 2, pb: 1 }}>
                <Typography variant="subtitle2" color="text.secondary" textTransform="uppercase">
                  {categoryName}
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
                            Last fetched: {formatDate(feed.lastFetchedAt)}
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
                      <Tooltip title="Edit">
                        <IconButton onClick={() => handleEditFeed(feed)}>
                          <EditIcon />
                        </IconButton>
                      </Tooltip>
                      <Tooltip title="Delete">
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
