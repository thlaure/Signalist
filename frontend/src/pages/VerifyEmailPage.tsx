import { useEffect, useState } from 'react';
import { useSearchParams, Link as RouterLink } from 'react-router-dom';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Typography from '@mui/material/Typography';
import Alert from '@mui/material/Alert';
import CircularProgress from '@mui/material/CircularProgress';
import Button from '@mui/material/Button';
import Link from '@mui/material/Link';
import { verifyEmail, resendVerification } from '../api/auth';
import { isProblemError } from '../api/client';

export default function VerifyEmailPage() {
  const [searchParams] = useSearchParams();
  const [status, setStatus] = useState<'loading' | 'success' | 'error'>(
    'loading'
  );
  const [errorMessage, setErrorMessage] = useState('');
  const [resending, setResending] = useState(false);
  const [resent, setResent] = useState(false);

  const email = searchParams.get('email') ?? '';

  useEffect(() => {
    const userId = searchParams.get('userId');
    const expiresAtParam = searchParams.get('expiresAt');
    const signature = searchParams.get('signature');

    if (!userId || !email || !expiresAtParam || !signature) {
      setStatus('error');
      setErrorMessage('Invalid verification link.');
      return;
    }

    const expiresAt = parseInt(expiresAtParam, 10);
    if (isNaN(expiresAt)) {
      setStatus('error');
      setErrorMessage('Invalid verification link.');
      return;
    }

    verifyEmail({ userId, email, expiresAt, signature })
      .then(() => setStatus('success'))
      .catch((err) => {
        setStatus('error');
        if (isProblemError(err)) {
          setErrorMessage(err.problem.detail);
        } else {
          setErrorMessage('Verification failed. Please try again.');
        }
      });
  }, [searchParams, email]);

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
          <Typography variant="h4" component="h1" mb={3}>
            Signalist
          </Typography>

          {status === 'loading' && (
            <>
              <CircularProgress sx={{ mb: 2 }} />
              <Typography>Verifying your email...</Typography>
            </>
          )}

          {status === 'success' && (
            <>
              <Alert severity="success" sx={{ mb: 3 }}>
                Your email has been verified!
              </Alert>
              <Button
                component={RouterLink}
                to="/login"
                variant="contained"
                fullWidth
                size="large"
              >
                Sign in
              </Button>
            </>
          )}

          {status === 'error' && (
            <>
              <Alert severity="error" sx={{ mb: 3 }}>
                {errorMessage}
              </Alert>
              {email && !resent && (
                <Button
                  variant="outlined"
                  onClick={handleResend}
                  disabled={resending}
                  sx={{ mb: 2 }}
                >
                  {resending ? (
                    <CircularProgress size={20} />
                  ) : (
                    'Resend verification email'
                  )}
                </Button>
              )}
              {resent && (
                <Alert severity="success" sx={{ mb: 2 }}>
                  Verification email resent.
                </Alert>
              )}
              <Typography variant="body2" color="text.secondary">
                <Link component={RouterLink} to="/login">
                  Back to sign in
                </Link>
              </Typography>
            </>
          )}
        </CardContent>
      </Card>
    </Box>
  );
}
