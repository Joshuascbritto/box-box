import { useEffect } from 'react';
import { Route, Routes, useLocation } from 'react-router-dom';
import { AuthProvider } from './auth/AuthContext';
import { ProtectedRoute } from './auth/ProtectedRoute';
import { Layout } from './components/Layout/Layout';
import { ErrorBoundary } from './components/ErrorBoundary';
import { Schedule } from './pages/Schedule';
import { RaceDetail } from './pages/RaceDetail';
import { Leaderboard } from './pages/Leaderboard';
import { Archive } from './pages/Archive';
import { MyLog } from './pages/MyLog';
import { Login } from './pages/Login';
import { Verify } from './pages/Verify';
import { NotFound } from './pages/NotFound';

const TITLES: Record<string, string> = {
  '/': 'schedule',
  '/leaderboard': 'standings',
  '/archive': 'archive',
  '/me': 'log',
  '/login': 'request access',
  '/auth/verify': 'verifying',
};

function TitleSync() {
  const location = useLocation();
  useEffect(() => {
    const matched = TITLES[location.pathname];
    const page = matched ?? location.pathname.replace(/^\//, '').split('/')[0] ?? 'page';
    document.title = `box-box · ${page || 'home'}`;
  }, [location.pathname]);
  return null;
}

export default function App() {
  return (
    <AuthProvider>
      <TitleSync />
      <Layout>
        <ErrorBoundary>
          <Routes>
            <Route path="/" element={<Schedule />} />
            <Route path="/races/:id" element={<RaceDetail />} />
            <Route path="/leaderboard" element={<Leaderboard />} />
            <Route path="/archive" element={<Archive />} />
            <Route
              path="/me"
              element={
                <ProtectedRoute>
                  <MyLog />
                </ProtectedRoute>
              }
            />
            <Route path="/login" element={<Login />} />
            <Route path="/auth/verify" element={<Verify />} />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </ErrorBoundary>
      </Layout>
    </AuthProvider>
  );
}
