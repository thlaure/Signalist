import { render, screen, fireEvent } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { describe, it, expect, vi } from 'vitest';
import AppLayout from './AppLayout';

vi.mock('./Sidebar', () => ({ default: ({ mobileOpen }: { mobileOpen: boolean }) => mobileOpen ? <nav aria-label="Mobile navigation" /> : null }));
vi.mock('./CommandPalette', () => ({ default: () => null }));

describe('AppLayout mobile navigation', () => {
  it('exposes a named menu control that opens navigation', () => {
    render(<MemoryRouter><AppLayout /></MemoryRouter>);
    const menu = screen.getByLabelText('Open navigation');
    expect(menu).toHaveAttribute('aria-expanded', 'false');
    expect(screen.queryByRole('navigation')).not.toBeInTheDocument();
    fireEvent.click(menu);
    expect(screen.getByRole('navigation', { name: 'Mobile navigation' })).toBeInTheDocument();
    expect(menu).toHaveAttribute('aria-expanded', 'true');
  });
});
