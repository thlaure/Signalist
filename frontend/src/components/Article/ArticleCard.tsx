import { useNavigate } from 'react-router-dom';
import Card from '@mui/material/Card';
import CardActionArea from '@mui/material/CardActionArea';
import CardContent from '@mui/material/CardContent';
import CardActions from '@mui/material/CardActions';
import CardMedia from '@mui/material/CardMedia';
import Typography from '@mui/material/Typography';
import IconButton from '@mui/material/IconButton';
import Box from '@mui/material/Box';
import Tooltip from '@mui/material/Tooltip';
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
    <Card
      sx={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        '&:hover': {
          transform: 'translateY(-2px)',
          boxShadow: 4,
        },
        '&:hover .article-actions': {
          opacity: 1,
        },
      }}
    >
      <CardActionArea onClick={() => navigate(`/articles/${article.id}`)} sx={{ flexGrow: 1 }}>
        {article.imageUrl && (
          <CardMedia
            component="img"
            height="140"
            image={article.imageUrl}
            alt={article.title}
            sx={{ objectFit: 'cover' }}
          />
        )}
        <CardContent>
          <Typography variant="caption" color="text.secondary" gutterBottom component="div">
            {article.feedTitle}
            {article.feedTitle && (article.publishedAt || article.createdAt) ? ' \u00b7 ' : ''}
            {formatDate(article.publishedAt || article.createdAt)}
          </Typography>
          <Typography
            variant="h6"
            component="h2"
            gutterBottom
            sx={{
              overflow: 'hidden',
              textOverflow: 'ellipsis',
              display: '-webkit-box',
              WebkitLineClamp: 2,
              WebkitBoxOrient: 'vertical',
              color: article.isRead ? 'text.secondary' : 'text.primary',
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
                WebkitLineClamp: 3,
                WebkitBoxOrient: 'vertical',
              }}
            >
              {article.summary}
            </Typography>
          )}
          {article.author && (
            <Box mt={1}>
              <Typography variant="caption" color="text.secondary">
                {article.author}
              </Typography>
            </Box>
          )}
        </CardContent>
      </CardActionArea>
      <CardActions
        className="article-actions"
        sx={{
          justifyContent: 'space-between',
          px: 2,
          pb: 2,
          opacity: 0,
          transition: 'opacity 0.2s ease',
        }}
      >
        <Box>
          <Tooltip title={article.isRead ? 'Mark as unread' : 'Mark as read'}>
            <IconButton
              size="small"
              onClick={(e) => { e.stopPropagation(); onToggleRead(article.id, article.isRead); }}
              color={article.isRead ? 'primary' : 'default'}
            >
              {article.isRead ? <CheckCircleIcon /> : <CheckCircleOutlineIcon />}
            </IconButton>
          </Tooltip>
          <Tooltip title={isBookmarked ? 'Remove bookmark' : 'Add bookmark'}>
            <IconButton
              size="small"
              onClick={(e) => { e.stopPropagation(); onToggleBookmark(article.id, isBookmarked); }}
              color={isBookmarked ? 'primary' : 'default'}
            >
              {isBookmarked ? <BookmarkIcon /> : <BookmarkBorderIcon />}
            </IconButton>
          </Tooltip>
        </Box>
        <Tooltip title="Open article">
          <IconButton
            size="small"
            component="a"
            href={article.url}
            target="_blank"
            rel="noopener noreferrer"
            onClick={(e: React.MouseEvent) => e.stopPropagation()}
          >
            <OpenInNewIcon />
          </IconButton>
        </Tooltip>
      </CardActions>
    </Card>
  );
}
