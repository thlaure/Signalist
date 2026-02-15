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
import HomeIcon from '@mui/icons-material/Home';
import RssFeedIcon from '@mui/icons-material/RssFeed';
import BookmarkIcon from '@mui/icons-material/Bookmark';
import FolderIcon from '@mui/icons-material/Folder';
import CircleIcon from '@mui/icons-material/Circle';
import ChevronLeftIcon from '@mui/icons-material/ChevronLeft';
import ChevronRightIcon from '@mui/icons-material/ChevronRight';
import { useCategories } from '../../hooks/useCategories';

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

  const handleNavigation = (path: string) => {
    navigate(path);
    onClose();
  };

  const isActive = (path: string) => location.pathname === path;

  const currentWidth = collapsed ? collapsedWidth : drawerWidth;

  const navItems = [
    { label: 'Dashboard', icon: <HomeIcon />, path: '/' },
    { label: 'Feeds', icon: <RssFeedIcon />, path: '/feeds' },
    { label: 'Bookmarks', icon: <BookmarkIcon />, path: '/bookmarks' },
  ];

  const drawerContent = (
    <Box sx={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      {/* Logo */}
      <Box sx={{ p: 2, display: 'flex', alignItems: 'center', minHeight: 48 }}>
        {!collapsed && (
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
              Categories
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
                secondary="No categories yet"
                sx={{ textAlign: 'center' }}
              />
            </ListItem>
          )}
        </List>
      </Box>

      {/* Spacer */}
      <Box sx={{ flexGrow: 1 }} />

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
