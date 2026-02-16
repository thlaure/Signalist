import { useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import IconButton from '@mui/material/IconButton';
import Chip from '@mui/material/Chip';
import Tooltip from '@mui/material/Tooltip';
import Divider from '@mui/material/Divider';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import OpenInNewIcon from '@mui/icons-material/OpenInNew';
import BookmarkBorderIcon from '@mui/icons-material/BookmarkBorder';
import BookmarkIcon from '@mui/icons-material/Bookmark';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CheckCircleOutlineIcon from '@mui/icons-material/CheckCircleOutline';
import LoadingSpinner from '../components/Common/LoadingSpinner';
import ErrorAlert from '../components/Common/ErrorAlert';
import { useArticle, useMarkArticleRead, useMarkArticleUnread } from '../hooks/useArticles';
import { useBookmarks, useCreateBookmark, useDeleteBookmark } from '../hooks/useBookmarks';

export default function ArticlePage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const {
    data: article,
    isLoading,
    isError,
    error,
    refetch,
  } = useArticle(id ?? '');

  const { data: bookmarks } = useBookmarks();
  const markRead = useMarkArticleRead();
  const markUnread = useMarkArticleUnread();
  const createBookmark = useCreateBookmark();
  const deleteBookmark = useDeleteBookmark();

  const bookmark = bookmarks?.find((b) => b.articleId === id);
  const isBookmarked = !!bookmark;

  // Auto-mark as read on view
  useEffect(() => {
    if (article && !article.isRead && id) {
      markRead.mutate(id);
    }
  }, [article?.id]); // eslint-disable-line react-hooks/exhaustive-deps

  const handleToggleRead = () => {
    if (!id) return;
    if (article?.isRead) {
      markUnread.mutate(id);
    } else {
      markRead.mutate(id);
    }
  };

  const handleToggleBookmark = () => {
    if (!id) return;
    if (isBookmarked && bookmark) {
      deleteBookmark.mutate(bookmark.id);
    } else {
      createBookmark.mutate({ articleId: id });
    }
  };

  const formatDate = (dateString: string | null) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
      weekday: 'long',
      month: 'long',
      day: 'numeric',
      year: 'numeric',
    });
  };

  if (isLoading) {
    return <LoadingSpinner message="Loading article..." />;
  }

  if (isError || !article) {
    return (
      <ErrorAlert
        title="Failed to load article"
        message={error?.message || 'Article not found'}
        onRetry={refetch}
      />
    );
  }

  return (
    <Box sx={{ maxWidth: 800, mx: 'auto', px: { xs: 1, sm: 2, md: 0 } }}>
      {/* Back button */}
      <Button
        startIcon={<ArrowBackIcon />}
        onClick={() => navigate(-1)}
        sx={{ mb: 2 }}
      >
        Back
      </Button>

      {/* Banner image */}
      {article.imageUrl && (
        <Box
          component="img"
          src={article.imageUrl}
          alt={article.title}
          sx={{
            width: '100%',
            maxHeight: 400,
            objectFit: 'cover',
            borderRadius: 2,
            mb: 3,
          }}
        />
      )}

      {/* Title */}
      <Typography variant="h4" fontWeight="bold" gutterBottom sx={{ fontSize: { xs: '1.5rem', sm: '2.125rem' } }}>
        {article.title}
      </Typography>

      {/* Metadata chips */}
      <Box display="flex" flexWrap="wrap" gap={1} mb={2}>
        <Chip label={article.feedTitle} size="small" variant="outlined" />
        <Chip label={article.categoryName} size="small" color="primary" variant="outlined" />
        {article.author && (
          <Chip label={article.author} size="small" variant="outlined" />
        )}
        {(article.publishedAt || article.createdAt) && (
          <Chip
            label={formatDate(article.publishedAt || article.createdAt)}
            size="small"
            variant="outlined"
          />
        )}
      </Box>

      {/* Action bar */}
      <Box display="flex" alignItems="center" gap={1} mb={3}>
        <Tooltip title={article.isRead ? 'Mark as unread' : 'Mark as read'}>
          <IconButton onClick={handleToggleRead} color={article.isRead ? 'primary' : 'default'}>
            {article.isRead ? <CheckCircleIcon /> : <CheckCircleOutlineIcon />}
          </IconButton>
        </Tooltip>
        <Tooltip title={isBookmarked ? 'Remove bookmark' : 'Add bookmark'}>
          <IconButton onClick={handleToggleBookmark} color={isBookmarked ? 'primary' : 'default'}>
            {isBookmarked ? <BookmarkIcon /> : <BookmarkBorderIcon />}
          </IconButton>
        </Tooltip>
        <Button
          variant="outlined"
          size="small"
          startIcon={<OpenInNewIcon />}
          component="a"
          href={article.url}
          target="_blank"
          rel="noopener noreferrer"
        >
          Open Original
        </Button>
      </Box>

      <Divider sx={{ mb: 3 }} />

      {/* Content */}
      {article.content ? (
        <Box
          sx={{
            '& img': { maxWidth: '100%', height: 'auto', borderRadius: 1 },
            '& a': { color: 'primary.main' },
            '& pre': { overflow: 'auto', p: 2, borderRadius: 1, bgcolor: 'grey.100' },
            lineHeight: 1.8,
            fontSize: '1.1rem',
          }}
          dangerouslySetInnerHTML={{ __html: article.content }}
        />
      ) : article.summary ? (
        <Typography variant="body1" sx={{ lineHeight: 1.8, fontSize: '1.1rem' }}>
          {article.summary}
        </Typography>
      ) : (
        <Box textAlign="center" py={4}>
          <Typography variant="body1" color="text.secondary">
            No content available.
          </Typography>
          <Button
            variant="contained"
            startIcon={<OpenInNewIcon />}
            component="a"
            href={article.url}
            target="_blank"
            rel="noopener noreferrer"
            sx={{ mt: 2 }}
          >
            Read on Original Site
          </Button>
        </Box>
      )}
    </Box>
  );
}
