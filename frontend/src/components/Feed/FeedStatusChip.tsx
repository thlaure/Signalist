import Chip from '@mui/material/Chip';
import { useTranslation } from 'react-i18next';

interface FeedStatusChipProps {
  status: 'active' | 'paused' | 'error' | string;
}

export default function FeedStatusChip({ status }: FeedStatusChipProps) {
  const { t } = useTranslation();

  const config = {
    active: { label: t('feedStatus.active'), color: 'success' as const },
    paused: { label: t('feedStatus.paused'), color: 'warning' as const },
    error: { label: t('feedStatus.error'), color: 'error' as const },
  };

  const { label, color } = config[status as keyof typeof config] ?? {
    label: status,
    color: 'default' as const,
  };

  return <Chip label={label} color={color} size="small" />;
}
