import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import Box from '@mui/material/Box';
import IconButton from '@mui/material/IconButton';
import MenuIcon from '@mui/icons-material/Menu';
import { useTranslation } from 'react-i18next';
import Sidebar from './Sidebar';
import CommandPalette from './CommandPalette';

const DRAWER_WIDTH = 240;
const COLLAPSED_WIDTH = 68;

export default function AppLayout() {
  const { t } = useTranslation();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [collapsed, setCollapsed] = useState(false);

  const currentWidth = collapsed ? COLLAPSED_WIDTH : DRAWER_WIDTH;

  return (
    <Box sx={{ display: 'flex' }}>
      <Sidebar
        drawerWidth={DRAWER_WIDTH}
        collapsedWidth={COLLAPSED_WIDTH}
        collapsed={collapsed}
        onToggleCollapse={() => setCollapsed((prev) => !prev)}
        mobileOpen={mobileOpen}
        onClose={() => setMobileOpen(false)}
      />
      <Box
        component="main"
        sx={{
          flexGrow: 1,
          minWidth: 0,
          p: 3,
          width: { xs: '100%', sm: `calc(100% - ${currentWidth}px)` },
          minHeight: '100vh',
          backgroundColor: 'background.default',
          transition: 'width 0.2s ease',
        }}
      >
        <IconButton
          aria-label={t('nav.openNavigation')}
          aria-expanded={mobileOpen}
          onClick={() => setMobileOpen(!mobileOpen)}
          sx={{ display: { sm: 'none' }, mb: 1, ml: -1 }}
        >
          <MenuIcon />
        </IconButton>
        <Outlet />
        <CommandPalette />
      </Box>
    </Box>
  );
}
