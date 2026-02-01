import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardActions from '@mui/material/CardActions';
import CardMedia from '@mui/material/CardMedia';
import Typography from '@mui/material/Typography';
import IconButton from '@mui/material/IconButton';
import Chip from '@mui/material/Chip';
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
        opacity: article.isRead ? 0.7 : 1,
        transition: 'opacity 0.2s',
      }}
    >
      {article.imageUrl && (
        <CardMedia
          component="img"
          height="140"
          image={article.imageUrl}
          alt={article.title}
          sx={{ objectFit: 'cover' }}
        />
      )}
      <CardContent sx={{ flexGrow: 1 }}>
        <Box display="flex" gap={1} mb={1} flexWrap="wrap">
          <Chip
            label={article.categoryName}
            size="small"
            color="primary"
            variant="outlined"
          />
          <Chip label={article.feedTitle} size="small" variant="outlined" />
        </Box>
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
            textDecoration: article.isRead ? 'none' : 'none',
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
        <Box mt={1}>
          <Typography variant="caption" color="text.secondary">
            {article.author && `${article.author} • `}
            {formatDate(article.publishedAt || article.createdAt)}
          </Typography>
        </Box>
      </CardContent>
      <CardActions sx={{ justifyContent: 'space-between', px: 2, pb: 2 }}>
        <Box>
          <Tooltip title={article.isRead ? 'Mark as unread' : 'Mark as read'}>
            <IconButton
              size="small"
              onClick={() => onToggleRead(article.id, article.isRead)}
              color={article.isRead ? 'primary' : 'default'}
            >
              {article.isRead ? <CheckCircleIcon /> : <CheckCircleOutlineIcon />}
            </IconButton>
          </Tooltip>
          <Tooltip title={isBookmarked ? 'Remove bookmark' : 'Add bookmark'}>
            <IconButton
              size="small"
              onClick={() => onToggleBookmark(article.id, isBookmarked)}
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
          >
            <OpenInNewIcon />
          </IconButton>
        </Tooltip>
      </CardActions>
    </Card>
  );
}
