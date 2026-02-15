import { Routes, Route } from 'react-router-dom';
import AppLayout from './components/Layout/AppLayout';
import ProtectedRoute from './components/Auth/ProtectedRoute';
import Dashboard from './pages/Dashboard';
import CategoryPage from './pages/CategoryPage';
import BookmarksPage from './pages/BookmarksPage';
import ArticlePage from './pages/ArticlePage';
import FeedManagementPage from './pages/FeedManagementPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import CheckEmailPage from './pages/CheckEmailPage';
import VerifyEmailPage from './pages/VerifyEmailPage';

function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/register" element={<RegisterPage />} />
      <Route path="/check-email" element={<CheckEmailPage />} />
      <Route path="/verify-email" element={<VerifyEmailPage />} />
      <Route element={<ProtectedRoute />}>
        <Route element={<AppLayout />}>
          <Route path="/" element={<Dashboard />} />
          <Route path="/articles/:id" element={<ArticlePage />} />
          <Route path="/feeds" element={<FeedManagementPage />} />
          <Route path="/categories/:id" element={<CategoryPage />} />
          <Route path="/bookmarks" element={<BookmarksPage />} />
        </Route>
      </Route>
    </Routes>
  );
}

export default App;
