import { render, screen, fireEvent } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { describe, expect, it, vi } from 'vitest';
import DigestsPage from './DigestsPage';
const change = vi.hoisted(() => vi.fn());
vi.mock('../hooks/useDigestMvp', () => ({ useDigests: () => ({ data: { configured: false, nextRunAt: '2026-10-19T08:00:00Z', items: [{ id: 'digest', period: '2026-10-12', status: 'empty', items: [] }] } }) }));
vi.mock('../hooks/usePrivacy', () => ({ usePrivacy: () => ({ profile: { data: { consent: { newsletter: false, external_ai: false }, processingRestricted: false } }, change: { mutate: change } }) }));
describe('Weekly digest onboarding', () => {
  it('shows empty weeks, real setup state and explicit email opt-in', () => {
    render(<MemoryRouter><DigestsPage /></MemoryRouter>);
    expect(screen.getByText('No usable new articles this week. No email sent.')).toBeInTheDocument();
    expect(screen.getByText(/AI generation requires local service configuration/)).toBeInTheDocument();
    expect(screen.getByRole('link', { name: 'Read' })).toHaveAttribute('href', '/digests/digest');
    const email = screen.getByRole('switch', { name: 'Receive scheduled newsletters' });
    fireEvent.click(email);
    expect(change).toHaveBeenCalledWith({ type: 'consent', purpose: 'newsletter', granted: true });
  });
});
