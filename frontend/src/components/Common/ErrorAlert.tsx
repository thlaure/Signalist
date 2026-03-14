import Alert from '@mui/material/Alert';
import AlertTitle from '@mui/material/AlertTitle';
import Button from '@mui/material/Button';
import Box from '@mui/material/Box';
import { useTranslation } from 'react-i18next';

interface ErrorAlertProps {
  title?: string;
  message: string;
  onRetry?: () => void;
}

export default function ErrorAlert({
  title,
  message,
  onRetry,
}: ErrorAlertProps) {
  const { t } = useTranslation();

  return (
    <Box p={2}>
      <Alert
        severity="error"
        action={
          onRetry && (
            <Button color="inherit" size="small" onClick={onRetry}>
              {t('common.retry')}
            </Button>
          )
        }
      >
        <AlertTitle>{title ?? t('common.error')}</AlertTitle>
        {message}
      </Alert>
    </Box>
  );
}
