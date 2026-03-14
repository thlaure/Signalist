import { useNavigate, useLocation } from 'react-router-dom';
import Box from '@mui/material/Box';
import Drawer from '@mui/material/Drawer';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemButton from '@mui/material/ListItemButton';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import Typography from '@mui/material/Typography';
import IconButton from '@mui/material/IconButton';
import Tooltip from '@mui/material/Tooltip';
import Avatar from '@mui/material/Avatar';
import HomeIcon from '@mui/icons-material/Home';
import RssFeedIcon from '@mui/icons-material/RssFeed';
import BookmarkIcon from '@mui/icons-material/Bookmark';
import FolderIcon from '@mui/icons-material/Folder';
import CircleIcon from '@mui/icons-material/Circle';
import ChevronLeftIcon from '@mui/icons-material/ChevronLeft';
import ChevronRightIcon from '@mui/icons-material/ChevronRight';
import LogoutIcon from '@mui/icons-material/Logout';
import { useTranslation } from 'react-i18next';
import { useCategories } from '../../hooks/useCategories';
import { useAuth } from '../../hooks/useAuth';

function getUserEmail(token: string | null): string | null {
  if (!token) return null;
  try {
    const payload = JSON.parse(atob(token.split('.')[1]));
    return payload.username ?? payload.email ?? null;
  } catch {
    return null;
  }
}

interface SidebarProps {
  drawerWidth: number;
  collapsedWidth: number;
  collapsed: boolean;
  onToggleCollapse: () => void;
  mobileOpen: boolean;
  onClose: () => void;
}

export default function Sidebar({
  drawerWidth,
  collapsedWidth,
  collapsed,
  onToggleCollapse,
  mobileOpen,
  onClose,
}: SidebarProps) {
  const navigate = useNavigate();
  const location = useLocation();
  const { data: categories = [] } = useCategories();
  const { token, logout } = useAuth();
  const email = getUserEmail(token);
  const { t, i18n } = useTranslation();

  const handleNavigation = (path: string) => {
    navigate(path);
    onClose();
  };

  const isActive = (path: string) => location.pathname === path;

  const currentWidth = collapsed ? collapsedWidth : drawerWidth;

  const toggleLanguage = () => {
    const next = i18n.language === 'fr' ? 'en' : 'fr';
    i18n.changeLanguage(next);
    localStorage.setItem('language', next);
  };

  const navItems = [
    { label: t('nav.feed'), icon: <HomeIcon />, path: '/' },
    { label: t('nav.feeds'), icon: <RssFeedIcon />, path: '/feeds' },
    { label: t('nav.bookmarks'), icon: <BookmarkIcon />, path: '/bookmarks' },
  ];

  const drawerContent = (
    <Box sx={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      {/* Logo */}
      <Box sx={{ p: 2, display: 'flex', alignItems: 'center', justifyContent: collapsed ? 'center' : 'flex-start', minHeight: 48 }}>
        {collapsed ? (
          <Box component="img" src="/favicon.svg" alt="Signalist" sx={{ width: 28, height: 28 }} />
        ) : (
          <Typography variant="h6" color="primary" fontWeight="bold">
            Signalist
          </Typography>
        )}
      </Box>

      {/* Nav items */}
      <List>
        {navItems.map((item) => (
          <ListItem key={item.path} disablePadding>
            <Tooltip title={collapsed ? item.label : ''} placement="right" arrow>
              <ListItemButton
                selected={isActive(item.path)}
                onClick={() => handleNavigation(item.path)}
                sx={{
                  minHeight: 44,
                  justifyContent: collapsed ? 'center' : 'initial',
                  px: collapsed ? 2 : 2.5,
                  borderRadius: 0,
                }}
              >
                <ListItemIcon
                  sx={{
                    minWidth: 0,
                    mr: collapsed ? 0 : 2,
                    justifyContent: 'center',
                  }}
                >
                  {item.icon}
                </ListItemIcon>
                {!collapsed && <ListItemText primary={item.label} />}
              </ListItemButton>
            </Tooltip>
          </ListItem>
        ))}
      </List>

      {/* Categories section */}
      <Box sx={{ mt: 3 }}>
        {!collapsed && (
          <Box sx={{ px: 2.5, mb: 1 }}>
            <Typography
              variant="caption"
              color="text.secondary"
              sx={{ textTransform: 'uppercase', fontWeight: 600, letterSpacing: '0.05em' }}
            >
              {t('nav.categories')}
            </Typography>
          </Box>
        )}
        <List>
          {categories.map((category) => {
            const icon = category.color ? (
              <CircleIcon sx={{ color: category.color, fontSize: 16 }} />
            ) : (
              <FolderIcon />
            );
            return (
              <ListItem key={category.id} disablePadding>
                <Tooltip title={collapsed ? category.name : ''} placement="right" arrow>
                  <ListItemButton
                    selected={isActive(`/categories/${category.id}`)}
                    onClick={() => handleNavigation(`/categories/${category.id}`)}
                    sx={{
                      minHeight: 40,
                      justifyContent: collapsed ? 'center' : 'initial',
                      px: collapsed ? 2 : 2.5,
                      borderRadius: 0,
                    }}
                  >
                    <ListItemIcon
                      sx={{
                        minWidth: 0,
                        mr: collapsed ? 0 : 2,
                        justifyContent: 'center',
                      }}
                    >
                      {icon}
                    </ListItemIcon>
                    {!collapsed && <ListItemText primary={category.name} />}
                  </ListItemButton>
                </Tooltip>
              </ListItem>
            );
          })}
          {categories.length === 0 && !collapsed && (
            <ListItem>
              <ListItemText
                secondary={t('nav.noCategories')}
                sx={{ textAlign: 'center' }}
              />
            </ListItem>
          )}
        </List>
      </Box>

      {/* Spacer */}
      <Box sx={{ flexGrow: 1 }} />

      {/* User section */}
      {email && (
        <Box
          sx={{
            display: 'flex',
            flexDirection: collapsed ? 'column' : 'row',
            alignItems: 'center',
            gap: collapsed ? 0.5 : 1,
            px: collapsed ? 0 : 2,
            py: 1.5,
            borderTop: '1px solid',
            borderColor: 'divider',
            justifyContent: 'center',
          }}
        >
          <Tooltip title={collapsed ? email : ''} placement="right" arrow>
            <Avatar sx={{ width: 32, height: 32, fontSize: 14, bgcolor: 'primary.main' }}>
              {email[0].toUpperCase()}
            </Avatar>
          </Tooltip>
          {!collapsed ? (
            <>
              <Typography
                variant="body2"
                noWrap
                sx={{ flex: 1, minWidth: 0, color: 'text.secondary' }}
              >
                {email}
              </Typography>
              <Tooltip title={i18n.language === 'fr' ? 'Switch to English' : 'Passer en français'} placement="top" arrow>
                <Box
                  onClick={toggleLanguage}
                  sx={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 0.25,
                    cursor: 'pointer',
                    borderRadius: 1,
                    px: 0.75,
                    py: 0.25,
                    '&:hover': { bgcolor: 'action.hover' },
                  }}
                >
                  <Typography
                    variant="caption"
                    fontWeight={i18n.language === 'en' ? 700 : 400}
                    color={i18n.language === 'en' ? 'primary.main' : 'text.secondary'}
                  >
                    EN
                  </Typography>
                  <Typography variant="caption" color="text.disabled">|</Typography>
                  <Typography
                    variant="caption"
                    fontWeight={i18n.language === 'fr' ? 700 : 400}
                    color={i18n.language === 'fr' ? 'primary.main' : 'text.secondary'}
                  >
                    FR
                  </Typography>
                </Box>
              </Tooltip>
              <Tooltip title={t('nav.logout')} placement="top" arrow>
                <IconButton size="small" onClick={logout}>
                  <LogoutIcon fontSize="small" />
                </IconButton>
              </Tooltip>
            </>
          ) : (
            <>
              <Tooltip title={i18n.language === 'fr' ? 'Switch to English' : 'Passer en français'} placement="right" arrow>
                <Box
                  onClick={toggleLanguage}
                  sx={{ cursor: 'pointer', borderRadius: 1, px: 0.5, '&:hover': { bgcolor: 'action.hover' } }}
                >
                  <Typography variant="caption" fontWeight={700} color="primary.main">
                    {i18n.language.toUpperCase()}
                  </Typography>
                </Box>
              </Tooltip>
              <Tooltip title={t('nav.logout')} placement="right" arrow>
                <IconButton size="small" onClick={logout}>
                  <LogoutIcon fontSize="small" />
                </IconButton>
              </Tooltip>
            </>
          )}
        </Box>
      )}

      {/* Collapse toggle */}
      <Box
        sx={{
          display: { xs: 'none', sm: 'flex' },
          justifyContent: collapsed ? 'center' : 'flex-end',
          p: 1,
          borderTop: '1px solid',
          borderColor: 'divider',
        }}
      >
        <IconButton onClick={onToggleCollapse} size="small">
          {collapsed ? <ChevronRightIcon /> : <ChevronLeftIcon />}
        </IconButton>
      </Box>
    </Box>
  );

  const drawerPaperSx = {
    boxSizing: 'border-box' as const,
    width: currentWidth,
    transition: 'width 0.2s ease',
    overflowX: 'hidden' as const,
    borderRight: '1px solid',
    borderColor: 'divider',
  };

  return (
    <Box
      component="nav"
      sx={{ width: { sm: currentWidth }, flexShrink: { sm: 0 }, transition: 'width 0.2s ease' }}
    >
      {/* Mobile drawer — always expanded */}
      <Drawer
        variant="temporary"
        open={mobileOpen}
        onClose={onClose}
        ModalProps={{ keepMounted: true }}
        sx={{
          display: { xs: 'block', sm: 'none' },
          '& .MuiDrawer-paper': { ...drawerPaperSx, width: drawerWidth },
        }}
      >
        {drawerContent}
      </Drawer>
      {/* Desktop drawer */}
      <Drawer
        variant="permanent"
        sx={{
          display: { xs: 'none', sm: 'block' },
          '& .MuiDrawer-paper': drawerPaperSx,
        }}
        open
      >
        {drawerContent}
      </Drawer>
    </Box>
  );
}
