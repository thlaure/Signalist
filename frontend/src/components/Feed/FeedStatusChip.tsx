import Chip from '@mui/material/Chip';

interface FeedStatusChipProps {
  status: 'active' | 'paused' | 'error' | string;
}

export default function FeedStatusChip({ status }: FeedStatusChipProps) {
  const config = {
    active: { label: 'Active', color: 'success' as const },
    paused: { label: 'Paused', color: 'warning' as const },
    error: { label: 'Error', color: 'error' as const },
  };

  const { label, color } = config[status as keyof typeof config] ?? {
    label: status,
    color: 'default' as const,
  };

  return <Chip label={label} color={color} size="small" />;
}
