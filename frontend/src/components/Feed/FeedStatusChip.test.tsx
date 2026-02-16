import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import FeedStatusChip from './FeedStatusChip';

describe('FeedStatusChip', () => {
  it('renders Active label for active status', () => {
    render(<FeedStatusChip status="active" />);
    expect(screen.getByText('Active')).toBeInTheDocument();
  });

  it('renders Paused label for paused status', () => {
    render(<FeedStatusChip status="paused" />);
    expect(screen.getByText('Paused')).toBeInTheDocument();
  });

  it('renders Error label for error status', () => {
    render(<FeedStatusChip status="error" />);
    expect(screen.getByText('Error')).toBeInTheDocument();
  });

  it('renders unknown status as-is', () => {
    render(<FeedStatusChip status="unknown" />);
    expect(screen.getByText('unknown')).toBeInTheDocument();
  });
});
