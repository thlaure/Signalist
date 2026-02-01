import Grid from '@mui/material/Grid';
import Box from '@mui/material/Box';
import ArticleCard from './ArticleCard';
import LoadingSpinner from '../Common/LoadingSpinner';
import ErrorAlert from '../Common/ErrorAlert';
import EmptyState from '../Common/EmptyState';
import type { Article, Bookmark } from '../../types';
import ArticleIcon from '@mui/icons-material/Article';

interface ArticleListProps {
  articles: Article[] | undefined;
  bookmarks: Bookmark[] | undefined;
  isLoading: boolean;
  isError: boolean;
  error: Error | null;
  onRefetch: () => void;
  onToggleRead: (id: string, isRead: boolean) => void;
  onToggleBookmark: (id: string, isBookmarked: boolean) => void;
  emptyMessage?: string;
}

export default function ArticleList({
  articles,
  bookmarks,
  isLoading,
  isError,
  error,
  onRefetch,
  onToggleRead,
  onToggleBookmark,
  emptyMessage = 'No articles found',
}: ArticleListProps) {
  const bookmarkedArticleIds = new Set(
    bookmarks?.map((b) => b.articleId) || []
  );

  if (isLoading) {
    return <LoadingSpinner message="Loading articles..." />;
  }

  if (isError) {
    return (
      <ErrorAlert
        title="Failed to load articles"
        message={error?.message || 'An error occurred'}
        onRetry={onRefetch}
      />
    );
  }

  if (!articles || articles.length === 0) {
    return (
      <EmptyState
        icon={<ArticleIcon fontSize="inherit" />}
        title={emptyMessage}
        description="Articles will appear here once feeds are crawled"
      />
    );
  }

  return (
    <Box>
      <Grid container spacing={3}>
        {articles.map((article) => (
          <Grid size={{ xs: 12, sm: 6, md: 4 }} key={article.id}>
            <ArticleCard
              article={article}
              isBookmarked={bookmarkedArticleIds.has(article.id)}
              onToggleRead={onToggleRead}
              onToggleBookmark={onToggleBookmark}
            />
          </Grid>
        ))}
      </Grid>
    </Box>
  );
}
