import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import SettingsPage from './SettingsPage';

const { mutate, remove, logout } = vi.hoisted(() => ({ mutate: vi.fn(), remove: vi.fn(), logout: vi.fn() }));
vi.mock('../hooks/useAuth', () => ({ useAuth: () => ({ logout }) }));
vi.mock('../hooks/usePrivacy', () => ({ usePrivacy: () => ({
  profile: { data: { id: 'owner', email: 'owner@example.com', displayName: 'Owner', processingRestricted: false, consent: { external_ai: false, newsletter: false, marketing: false } }, isPending: false },
  change: { mutate, isPending: false }, remove: { mutate: remove, isPending: false }, download: { mutateAsync: vi.fn(), isPending: false },
}) }));

describe('SettingsPage', () => {
  beforeEach(() => { vi.clearAllMocks(); });
  it('records explicit AI consent and processing restriction independently', () => {
    render(<SettingsPage />);
    fireEvent.click(screen.getByRole('switch', { name: 'Allow minimized article text to be sent to external AI' }));
    expect(mutate).toHaveBeenCalledWith({ type: 'consent', purpose: 'external_ai', granted: true });
    fireEvent.click(screen.getByRole('switch', { name: 'Restrict processing' }));
    expect(mutate).toHaveBeenCalledWith({ type: 'restrict', restricted: true });
  });
  it('requires confirmation and password before account deletion', () => {
    render(<SettingsPage />);
    expect(screen.queryByLabelText('Current password')).not.toBeInTheDocument();
    fireEvent.click(screen.getByRole('button', { name: 'Delete account' }));
    const button = screen.getByRole('button', { name: 'Permanently delete my account' });
    expect(button).toBeDisabled();
    fireEvent.change(screen.getByLabelText('Current password'), { target: { value: 'confirmation' } });
    fireEvent.click(button);
    expect(remove).toHaveBeenCalledWith('confirmation', { onSuccess: logout });
  });
});
