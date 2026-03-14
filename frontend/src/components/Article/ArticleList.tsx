import Box from '@mui/material/Box';
import Pagination from '@mui/material/Pagination';
import { useTranslation } from 'react-i18next';
import ArticleCard from './ArticleCard';
import LoadingSpinner from '../Common/LoadingSpinner';
import ErrorAlert from '../Common/ErrorAlert';
import EmptyState from '../Common/EmptyState';
import type { Article, Bookmark, PaginatedArticles } from '../../types';
import ArticleIcon from '@mui/icons-material/Article';

interface ArticleListProps {
  data: PaginatedArticles | undefined;
  bookmarks: Bookmark[] | undefined;
  isLoading: boolean;
  isError: boolean;
  error: Error | null;
  onRefetch: () => void;
  onToggleRead: (id: string, isRead: boolean) => void;
  onToggleBookmark: (id: string, isBookmarked: boolean) => void;
  onPageChange: (page: number) => void;
  emptyMessage?: string;
}

export default function ArticleList({
  data,
  bookmarks,
  isLoading,
  isError,
  error,
  onRefetch,
  onToggleRead,
  onToggleBookmark,
  onPageChange,
  emptyMessage,
}: ArticleListProps) {
  const { t } = useTranslation();
  const bookmarkedArticleIds = new Set(
    bookmarks?.map((b) => b.articleId) || []
  );

  if (isLoading) {
    return <LoadingSpinner message={t('articleList.loading')} />;
  }

  if (isError) {
    return (
      <ErrorAlert
        title={t('articleList.failedToLoad')}
        message={error?.message || t('common.error')}
        onRetry={onRefetch}
      />
    );
  }

  const articles: Article[] = data?.items ?? [];

  if (articles.length === 0) {
    return (
      <EmptyState
        icon={<ArticleIcon fontSize="inherit" />}
        title={emptyMessage ?? t('articleList.noArticles')}
        description={t('articleList.willAppear')}
      />
    );
  }

  return (
    <Box>
      {articles.map((article) => (
        <ArticleCard
          key={article.id}
          article={article}
          isBookmarked={bookmarkedArticleIds.has(article.id)}
          onToggleRead={onToggleRead}
          onToggleBookmark={onToggleBookmark}
        />
      ))}

      {data && data.pages > 1 && (
        <Box display="flex" justifyContent="center" mt={3}>
          <Pagination
            count={data.pages}
            page={data.page}
            onChange={(_, page) => onPageChange(page)}
            color="primary"
            shape="rounded"
          />
        </Box>
      )}
    </Box>
  );
}
