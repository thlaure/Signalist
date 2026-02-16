import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi } from 'vitest';
import AddFeedDialog from './AddFeedDialog';
import type { Category } from '../../types';

const mockCategories: Category[] = [
  {
    id: '1',
    name: 'Tech',
    slug: 'tech',
    description: null,
    color: '#3498db',
    position: 0,
    createdAt: '2026-01-01T00:00:00+00:00',
    updatedAt: '2026-01-01T00:00:00+00:00',
  },
];

describe('AddFeedDialog', () => {
  it('renders form fields when open', () => {
    render(
      <AddFeedDialog
        open={true}
        onClose={vi.fn()}
        onSubmit={vi.fn()}
        categories={mockCategories}
      />
    );

    expect(screen.getByLabelText(/feed url/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/title/i)).toBeInTheDocument();
    expect(screen.getByRole('combobox', { hidden: true })).toBeInTheDocument();
  });

  it('does not render when closed', () => {
    render(
      <AddFeedDialog
        open={false}
        onClose={vi.fn()}
        onSubmit={vi.fn()}
        categories={mockCategories}
      />
    );

    expect(screen.queryByLabelText(/feed url/i)).not.toBeInTheDocument();
  });

  it('calls onClose when cancel is clicked', async () => {
    const onClose = vi.fn();
    render(
      <AddFeedDialog
        open={true}
        onClose={onClose}
        onSubmit={vi.fn()}
        categories={mockCategories}
      />
    );

    await userEvent.click(screen.getByRole('button', { name: /cancel/i }));

    expect(onClose).toHaveBeenCalled();
  });

  it('shows loading state on submit button', () => {
    render(
      <AddFeedDialog
        open={true}
        onClose={vi.fn()}
        onSubmit={vi.fn()}
        categories={mockCategories}
        isLoading={true}
      />
    );

    expect(screen.getByRole('button', { name: /adding/i })).toBeDisabled();
  });
});
