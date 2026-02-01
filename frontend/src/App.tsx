import { Routes, Route } from 'react-router-dom';
import Box from '@mui/material/Box';

// Placeholder pages - will be implemented in Phase 7
function Dashboard() {
  return <Box p={3}>Dashboard - All recent articles</Box>;
}

function CategoryPage() {
  return <Box p={3}>Category Page</Box>;
}

function BookmarksPage() {
  return <Box p={3}>Bookmarks Page</Box>;
}

function App() {
  return (
    <Routes>
      <Route path="/" element={<Dashboard />} />
      <Route path="/categories/:id" element={<CategoryPage />} />
      <Route path="/bookmarks" element={<BookmarksPage />} />
    </Routes>
  );
}

export default App;
