import { useState, type FormEvent } from 'react';
import { useNavigate, Link as RouterLink } from 'react-router-dom';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import TextField from '@mui/material/TextField';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import Alert from '@mui/material/Alert';
import CircularProgress from '@mui/material/CircularProgress';
import Link from '@mui/material/Link';
import { useAuth } from '../hooks/useAuth';
import { isProblemError } from '../api/client';

export default function LoginPage() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [emailNotVerified, setEmailNotVerified] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);
    setEmailNotVerified(false);
    setLoading(true);

    try {
      await login({ email, password });
      navigate('/', { replace: true });
    } catch (err) {
      if (isProblemError(err)) {
        if (err.problem.type.endsWith('/email-not-verified')) {
          setEmailNotVerified(true);
        }
        setError(err.problem.detail);
      } else {
        setError('An unexpected error occurred. Please try again.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Box
      sx={{
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        bgcolor: 'background.default',
      }}
    >
      <Card sx={{ width: '100%', maxWidth: 420, mx: 2 }} elevation={3}>
        <CardContent sx={{ p: 4 }}>
          <Typography variant="h4" component="h1" textAlign="center" mb={1}>
            Signalist
          </Typography>
          <Typography
            variant="body2"
            color="text.secondary"
            textAlign="center"
            mb={4}
          >
            Sign in to your account
          </Typography>

          {error && (
            <Alert severity="error" sx={{ mb: emailNotVerified ? 1 : 3 }}>
              {error}
            </Alert>
          )}

          {emailNotVerified && (
            <Typography variant="body2" sx={{ mb: 3 }}>
              <Link
                component={RouterLink}
                to="/check-email"
                state={{ email }}
              >
                Resend verification email
              </Link>
            </Typography>
          )}

          <Box component="form" onSubmit={handleSubmit} noValidate>
            <TextField
              label="Email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              fullWidth
              required
              autoComplete="email"
              autoFocus
              sx={{ mb: 2 }}
            />
            <TextField
              label="Password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              fullWidth
              required
              autoComplete="current-password"
              sx={{ mb: 3 }}
            />
            <Button
              type="submit"
              variant="contained"
              fullWidth
              size="large"
              disabled={loading || !email || !password}
            >
              {loading ? <CircularProgress size={24} /> : 'Sign in'}
            </Button>
          </Box>

          <Typography
            variant="body2"
            color="text.secondary"
            textAlign="center"
            mt={3}
          >
            Don't have an account?{' '}
            <Link component={RouterLink} to="/register">
              Sign up
            </Link>
          </Typography>
        </CardContent>
      </Card>
    </Box>
  );
}
