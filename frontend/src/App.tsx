import { Routes, Route } from 'react-router-dom';
import ProtectedRoute from './components/Auth/ProtectedRoute';

import AppLayout from './components/Layout/AppLayout';
import Feed from './pages/Feed';
import CategoryPage from './pages/CategoryPage';
import BookmarksPage from './pages/BookmarksPage';
import ArticlePage from './pages/ArticlePage';
import FeedManagementPage from './pages/FeedManagementPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import CheckEmailPage from './pages/CheckEmailPage';
import VerifyEmailPage from './pages/VerifyEmailPage';
import SettingsPage from './pages/SettingsPage';
import WaitlistPage from './pages/WaitlistPage';
import NewslettersPage from './pages/NewslettersPage';
import SearchPage from './pages/SearchPage';
import WorkspacesPage from './pages/WorkspacesPage';
import WorkspacePage from './pages/WorkspacePage';
import WorkspaceArticlePage from './pages/WorkspaceArticlePage';
import InvitationPage from './pages/InvitationPage';
import BillingPage from './pages/BillingPage';
import ConnectionsPage from './pages/ConnectionsPage';
import SocialPage from './pages/SocialPage';
import TagsPage from './pages/TagsPage';

function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/register" element={<RegisterPage />} />
      <Route path="/check-email" element={<CheckEmailPage />} />
      <Route path="/verify-email" element={<VerifyEmailPage />} />
      <Route path="/waitlist" element={<WaitlistPage />} />
      <Route element={<ProtectedRoute />}>
        <Route element={<AppLayout />}>
          <Route path="/" element={<Feed />} />
          <Route path="/articles/:id" element={<ArticlePage />} />
          <Route path="/feeds" element={<FeedManagementPage />} />
          <Route path="/categories/:id" element={<CategoryPage />} />
          <Route path="/bookmarks" element={<BookmarksPage />} />
          <Route path="/settings" element={<SettingsPage />} />
          <Route path="/newsletters" element={<NewslettersPage />} />
          <Route path="/search" element={<SearchPage />} />
          <Route path="/workspaces" element={<WorkspacesPage />} />
          <Route path="/workspaces/:id" element={<WorkspacePage />} />
          <Route path="/workspaces/:id/articles/:articleId" element={<WorkspaceArticlePage />} />
          <Route path="/invitations" element={<InvitationPage />} />
          <Route path="/billing" element={<BillingPage />} />
          <Route path="/connections" element={<ConnectionsPage />} />
          <Route path="/social" element={<SocialPage />} />
          <Route path="/tags" element={<TagsPage />} />
        </Route>
      </Route>
    </Routes>
  );
}

export default App;
