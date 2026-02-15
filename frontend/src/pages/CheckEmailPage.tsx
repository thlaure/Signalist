import { useState } from 'react';
import { useLocation, Link as RouterLink } from 'react-router-dom';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import Alert from '@mui/material/Alert';
import Link from '@mui/material/Link';
import CircularProgress from '@mui/material/CircularProgress';
import { resendVerification } from '../api/auth';

export default function CheckEmailPage() {
  const location = useLocation();
  const email = (location.state as { email?: string })?.email ?? '';
  const [resending, setResending] = useState(false);
  const [resent, setResent] = useState(false);

  const handleResend = async () => {
    if (!email) return;
    setResending(true);
    try {
      await resendVerification({ email });
      setResent(true);
    } finally {
      setResending(false);
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
        <CardContent sx={{ p: 4, textAlign: 'center' }}>
          <Typography variant="h4" component="h1" mb={1}>
            Signalist
          </Typography>
          <Typography variant="h6" mb={2}>
            Check your email
          </Typography>
          <Typography variant="body1" color="text.secondary" mb={3}>
            We sent a verification link to{' '}
            {email ? <strong>{email}</strong> : 'your email address'}. Click the
            link to activate your account.
          </Typography>

          {resent && (
            <Alert severity="success" sx={{ mb: 2 }}>
              Verification email resent.
            </Alert>
          )}

          {email && (
            <Button
              variant="outlined"
              onClick={handleResend}
              disabled={resending || resent}
              sx={{ mb: 3 }}
            >
              {resending ? <CircularProgress size={20} /> : 'Resend email'}
            </Button>
          )}

          <Typography variant="body2" color="text.secondary">
            Already verified?{' '}
            <Link component={RouterLink} to="/login">
              Sign in
            </Link>
          </Typography>
        </CardContent>
      </Card>
    </Box>
  );
}
