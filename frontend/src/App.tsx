import { Routes, Route } from 'react-router-dom';
import AppLayout from './components/Layout/AppLayout';
import ProtectedRoute from './components/Auth/ProtectedRoute';
import Dashboard from './pages/Dashboard';
import CategoryPage from './pages/CategoryPage';
import BookmarksPage from './pages/BookmarksPage';
import LoginPage from './pages/LoginPage';

function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route element={<ProtectedRoute />}>
        <Route element={<AppLayout />}>
          <Route path="/" element={<Dashboard />} />
          <Route path="/categories/:id" element={<CategoryPage />} />
          <Route path="/bookmarks" element={<BookmarksPage />} />
        </Route>
      </Route>
    </Routes>
  );
}

export default App;
