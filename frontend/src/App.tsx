import { lazy, Suspense } from 'react';
import { Routes, Route } from 'react-router-dom';
import ProtectedRoute from './components/Auth/ProtectedRoute';
import LoadingSpinner from './components/Common/LoadingSpinner';

const AppLayout = lazy(() => import('./components/Layout/AppLayout'));
const Feed = lazy(() => import('./pages/Feed'));
const CategoryPage = lazy(() => import('./pages/CategoryPage'));
const BookmarksPage = lazy(() => import('./pages/BookmarksPage'));
const ArticlePage = lazy(() => import('./pages/ArticlePage'));
const FeedManagementPage = lazy(() => import('./pages/FeedManagementPage'));
const LoginPage = lazy(() => import('./pages/LoginPage'));
const RegisterPage = lazy(() => import('./pages/RegisterPage'));
const CheckEmailPage = lazy(() => import('./pages/CheckEmailPage'));
const VerifyEmailPage = lazy(() => import('./pages/VerifyEmailPage'));
const SettingsPage = lazy(() => import('./pages/SettingsPage'));
const WaitlistPage = lazy(() => import('./pages/WaitlistPage'));
const NewslettersPage = lazy(() => import('./pages/NewslettersPage'));
const SearchPage = lazy(() => import('./pages/SearchPage'));
const WorkspacesPage = lazy(() => import('./pages/WorkspacesPage'));
const WorkspacePage = lazy(() => import('./pages/WorkspacePage'));
const WorkspaceArticlePage = lazy(() => import('./pages/WorkspaceArticlePage'));
const InvitationPage = lazy(() => import('./pages/InvitationPage'));
const BillingPage = lazy(() => import('./pages/BillingPage'));
const ConnectionsPage = lazy(() => import('./pages/ConnectionsPage'));
const SocialPage = lazy(() => import('./pages/SocialPage'));
const TagsPage = lazy(() => import('./pages/TagsPage'));

function App() {
  return (
    <Suspense fallback={<LoadingSpinner />}>
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
    </Suspense>
  );
}

export default App;
