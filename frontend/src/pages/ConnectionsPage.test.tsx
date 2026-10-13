import { render, screen, fireEvent } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import ConnectionsPage from './ConnectionsPage';
const { sync, disconnect, authorize, connection } = vi.hoisted(() => ({ sync: vi.fn(), disconnect: vi.fn(), authorize: vi.fn(), connection: { id: 'owner-account', provider: 'raindrop', configured: true, status: 'connected', lastSyncedAt: null } }));
vi.mock('../hooks/useConnections', () => ({ useConnections: () => ({ accounts: { data: [connection] }, authorize: { mutate: authorize }, authorization: {}, disconnect: { mutate: disconnect }, sync: { mutate: sync }, run: {} }) }));
describe('ConnectionsPage', () => {
  beforeEach(() => { vi.clearAllMocks(); connection.configured = true; });
  it('keeps notes local unless explicitly selected for this sync', () => {
    render(<ConnectionsPage />);
    fireEvent.click(screen.getByRole('button', { name: 'Sync bookmarks' }));
    expect(sync).toHaveBeenLastCalledWith({ id: 'owner-account', includeNotes: false });
    fireEvent.click(screen.getByRole('checkbox', { name: 'Include private notes in this sync' }));
    fireEvent.click(screen.getByRole('button', { name: 'Sync bookmarks' }));
    expect(sync).toHaveBeenLastCalledWith({ id: 'owner-account', includeNotes: true });
  });
  it('requires confirmation before erasing local authorization', () => {
    vi.spyOn(window, 'confirm').mockReturnValue(false);
    render(<ConnectionsPage />);
    fireEvent.click(screen.getByRole('button', { name: 'Disconnect' }));
    expect(disconnect).not.toHaveBeenCalled();
    vi.mocked(window.confirm).mockReturnValue(true);
    fireEvent.click(screen.getByRole('button', { name: 'Disconnect' }));
    expect(disconnect).toHaveBeenCalledWith('owner-account');
  });
  it('disables authorization until provider configuration exists', () => {
    connection.configured = false; render(<ConnectionsPage />);
    expect(screen.getByRole('button', { name: 'Connect / reconnect' })).toBeDisabled();
  });
});
