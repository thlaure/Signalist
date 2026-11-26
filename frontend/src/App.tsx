import { lazy, Suspense } from 'react';
import { Navigate, Routes, Route } from 'react-router-dom';
import ProtectedRoute from './components/Auth/ProtectedRoute';
import LoadingSpinner from './components/Common/LoadingSpinner';

const AppLayout = lazy(() => import('./components/Layout/AppLayout'));
const LoginPage = lazy(() => import('./pages/LoginPage'));
const RegisterPage = lazy(() => import('./pages/RegisterPage'));
const CheckEmailPage = lazy(() => import('./pages/CheckEmailPage'));
const VerifyEmailPage = lazy(() => import('./pages/VerifyEmailPage'));
const SettingsPage = lazy(() => import('./pages/SettingsPage'));
const SourcesPage = lazy(() => import('./pages/SourcesPage'));
const DigestsPage = lazy(() => import('./pages/DigestsPage'));
const DigestPage = lazy(() => import('./pages/DigestPage'));
const BillingPage = lazy(() => import('./pages/BillingPage'));

export default function App() {
  return <Suspense fallback={<LoadingSpinner />}><Routes>
    <Route path="/login" element={<LoginPage />} />
    <Route path="/register" element={<RegisterPage />} />
    <Route path="/check-email" element={<CheckEmailPage />} />
    <Route path="/verify-email" element={<VerifyEmailPage />} />
    <Route element={<ProtectedRoute />}><Route element={<AppLayout />}>
      <Route path="/" element={<Navigate to="/digests" replace />} />
      <Route path="/sources" element={<SourcesPage />} />
      <Route path="/digests" element={<DigestsPage />} />
      <Route path="/digests/:id" element={<DigestPage />} />
      <Route path="/billing" element={<BillingPage />} />
      <Route path="/settings" element={<SettingsPage />} />
      <Route path="*" element={<Navigate to="/digests" replace />} />
    </Route></Route>
  </Routes></Suspense>;
}
