import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import ErrorAlert from './ErrorAlert';

describe('ErrorAlert', () => {
  it('renders error message', () => {
    render(<ErrorAlert message="Something went wrong" />);
    expect(screen.getByText('Something went wrong')).toBeInTheDocument();
    expect(screen.getByText('Error')).toBeInTheDocument();
  });

  it('renders custom title', () => {
    render(<ErrorAlert title="Connection Failed" message="Unable to reach server" />);
    expect(screen.getByText('Connection Failed')).toBeInTheDocument();
  });

  it('renders retry button that fires callback', async () => {
    const onRetry = vi.fn();
    render(<ErrorAlert message="Failed" onRetry={onRetry} />);

    const retryButton = screen.getByRole('button', { name: /retry/i });
    await userEvent.click(retryButton);

    expect(onRetry).toHaveBeenCalledOnce();
  });

  it('does not render retry button when no callback provided', () => {
    render(<ErrorAlert message="Failed" />);
    expect(screen.queryByRole('button', { name: /retry/i })).not.toBeInTheDocument();
  });
});
