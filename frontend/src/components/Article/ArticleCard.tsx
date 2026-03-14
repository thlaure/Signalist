import { useNavigate } from 'react-router-dom';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import IconButton from '@mui/material/IconButton';
import Tooltip from '@mui/material/Tooltip';
import Divider from '@mui/material/Divider';
import OpenInNewIcon from '@mui/icons-material/OpenInNew';
import BookmarkBorderIcon from '@mui/icons-material/BookmarkBorder';
import BookmarkIcon from '@mui/icons-material/Bookmark';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CheckCircleOutlineIcon from '@mui/icons-material/CheckCircleOutline';
import type { Article } from '../../types';

interface ArticleCardProps {
  article: Article;
  isBookmarked?: boolean;
  onToggleRead: (id: string, isRead: boolean) => void;
  onToggleBookmark: (id: string, isBookmarked: boolean) => void;
}

export default function ArticleCard({
  article,
  isBookmarked = false,
  onToggleRead,
  onToggleBookmark,
}: ArticleCardProps) {
  const navigate = useNavigate();

  const formatDate = (dateString: string | null) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  };

  return (
    <Box>
      <Box
        sx={{
          display: 'flex',
          gap: 2.5,
          py: 2.5,
          cursor: 'pointer',
          opacity: article.isRead ? 0.5 : 1,
          transition: 'opacity 0.15s ease',
          '&:hover': {
            opacity: 1,
          },
          '&:hover .article-title': {
            color: 'primary.main',
            textDecoration: 'underline',
          },
          '&:hover .article-actions': {
            opacity: 1,
          },
        }}
      >
<Box sx={{ flex: 1, minWidth: 0, display: 'flex', flexDirection: 'column', gap: 0.5 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <Typography
              variant="overline"
              sx={{
                color: article.categoryColor ?? 'primary.main',
                fontWeight: 700,
                letterSpacing: 1,
                lineHeight: 1,
              }}
            >
              {article.categoryName}
            </Typography>
            <Typography variant="overline" sx={{ color: 'text.disabled', lineHeight: 1 }}>
              ·
            </Typography>
            <Typography variant="overline" sx={{ color: 'text.secondary', lineHeight: 1 }}>
              {article.feedTitle}
            </Typography>
          </Box>

          <Typography
            className="article-title"
            variant="h6"
            component="h2"
            onClick={() => navigate(`/articles/${article.id}`)}
            sx={{
              fontWeight: 700,
              lineHeight: 1.3,
              overflow: 'hidden',
              textOverflow: 'ellipsis',
              display: '-webkit-box',
              WebkitLineClamp: 2,
              WebkitBoxOrient: 'vertical',
              color: article.isRead ? 'text.secondary' : 'text.primary',
              transition: 'color 0.15s ease',
            }}
          >
            {article.title}
          </Typography>

          {article.summary && (
            <Typography
              variant="body2"
              color="text.secondary"
              sx={{
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                display: '-webkit-box',
                WebkitLineClamp: 2,
                WebkitBoxOrient: 'vertical',
              }}
            >
              {article.summary}
            </Typography>
          )}

          <Typography variant="caption" color="text.secondary">
            {formatDate(article.publishedAt || article.createdAt)}
            {article.author ? ` · ${article.author}` : ''}
          </Typography>

          <Box
            className="article-actions"
            sx={{ display: 'flex', gap: 0.5, mt: 'auto', opacity: 0, transition: 'opacity 0.15s ease' }}
          >
            <Tooltip title={article.isRead ? 'Mark as unread' : 'Mark as read'}>
              <IconButton
                size="small"
                onClick={(e) => { e.stopPropagation(); onToggleRead(article.id, article.isRead); }}
                color={article.isRead ? 'primary' : 'default'}
              >
                {article.isRead ? <CheckCircleIcon fontSize="small" /> : <CheckCircleOutlineIcon fontSize="small" />}
              </IconButton>
            </Tooltip>
            <Tooltip title={isBookmarked ? 'Remove bookmark' : 'Add bookmark'}>
              <IconButton
                size="small"
                onClick={(e) => { e.stopPropagation(); onToggleBookmark(article.id, isBookmarked); }}
                color={isBookmarked ? 'primary' : 'default'}
              >
                {isBookmarked ? <BookmarkIcon fontSize="small" /> : <BookmarkBorderIcon fontSize="small" />}
              </IconButton>
            </Tooltip>
            <Tooltip title="Open original article">
              <IconButton
                size="small"
                component="a"
                href={article.url}
                target="_blank"
                rel="noopener noreferrer"
                onClick={(e: React.MouseEvent) => e.stopPropagation()}
              >
                <OpenInNewIcon fontSize="small" />
              </IconButton>
            </Tooltip>
          </Box>
        </Box>
      </Box>
      <Divider />
    </Box>
  );
}
