import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Button, Menu, MenuItem } from '@mui/material';
import ShareIcon from '@mui/icons-material/Share';
import { useTranslation } from 'react-i18next';
import { shareLinks } from '../../lib/shareLinks';
export default function ShareMenu({ title, url }: { title: string; url: string }) {
  const { t } = useTranslation();
  const [anchor, setAnchor] = useState<HTMLElement | null>(null);
  const links = shareLinks(title, url);
  if (links.length === 0) return null;
  return <>
    <Button startIcon={<ShareIcon />} onClick={(event) => setAnchor(event.currentTarget)}>{t('article.share')}</Button>
    <Menu anchorEl={anchor} open={!!anchor} onClose={() => setAnchor(null)}>
      {links.map((link) => <MenuItem key={link.platform} component="a" href={link.url} target="_blank" rel="noopener noreferrer" onClick={() => setAnchor(null)}>{link.platform}</MenuItem>)}
      <MenuItem component={Link} to={`/social?text=${encodeURIComponent(`${title}\n${url}`)}`} onClick={() => setAnchor(null)}>{t('social.editor')}</MenuItem>
    </Menu>
  </>;
}
