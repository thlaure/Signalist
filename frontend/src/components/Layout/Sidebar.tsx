import { Box, Button, Drawer, List, ListItemButton, ListItemText, Typography } from '@mui/material';
import { NavLink } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../../hooks/useAuth';

interface Props { drawerWidth: number; collapsedWidth: number; collapsed: boolean; onToggleCollapse: () => void; mobileOpen: boolean; onClose: () => void; }
export default function Sidebar({ drawerWidth, mobileOpen, onClose }: Props) {
  const { t } = useTranslation();
  const { logout } = useAuth();
  const content = <Box sx={{ p: 2 }}>
    <Typography variant="h5" sx={{ mb: 1 }}>Signalist</Typography>
    <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>{t('mvp.promise')}</Typography>
    <List component="nav" aria-label={t('mvp.navigation')}>
      {['sources', 'digests', 'billing', 'settings'].map((page) => <ListItemButton key={page} component={NavLink} to={`/${page}`} onClick={onClose} sx={{ '&.active': { bgcolor: 'action.selected' }, borderRadius: 1 }}>
        <ListItemText primary={t(`mvp.${page}`)} />
      </ListItemButton>)}
    </List>
    <Button onClick={logout}>{t('auth.logout')}</Button>
  </Box>;
  return <Box component="aside" sx={{ width: { sm: drawerWidth }, flexShrink: 0 }}>
    <Drawer variant="temporary" open={mobileOpen} onClose={onClose} sx={{ display: { xs: 'block', sm: 'none' }, '& .MuiDrawer-paper': { width: drawerWidth } }}>{content}</Drawer>
    <Drawer variant="permanent" open sx={{ display: { xs: 'none', sm: 'block' }, '& .MuiDrawer-paper': { width: drawerWidth } }}>{content}</Drawer>
  </Box>;
}
