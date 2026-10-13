import { render, screen, fireEvent } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import SocialPage from './SocialPage';
const { publish, generate } = vi.hoisted(() => ({ publish: vi.fn(), generate: vi.fn() }));
vi.mock('../hooks/useSocial', () => ({ useSocial: () => ({ recaps: { data: [] }, posts: { data: [] }, generate: { mutate: generate }, edit: {}, remove: {}, publish: { mutate: publish } }) }));
vi.mock('../hooks/useConnections', () => ({ useConnections: () => ({ accounts: { data: [{ id: 'x-account', provider: 'x', status: 'connected' }] } }) }));
describe('SocialPage', () => {
  beforeEach(() => { vi.clearAllMocks(); });
  it('queues a provider-independent draft with AI off by default', () => {
    render(<MemoryRouter><SocialPage /></MemoryRouter>);
    expect(screen.getByRole('checkbox', { name: 'Use AI (requires provider setup and consent)' })).not.toBeChecked();
    fireEvent.click(screen.getByRole('button', { name: 'Generate draft' }));
    expect(generate).toHaveBeenCalledWith(expect.objectContaining({ platform: 'linkedin', useAi: false }));
  });
  it('requires account selection and explicit approval before publication', () => {
    vi.spyOn(window, 'confirm').mockReturnValue(false);
    render(<MemoryRouter initialEntries={['/social?text=Reviewed%20source']}><SocialPage /></MemoryRouter>);
    const button = screen.getByRole('button', { name: 'Approve & publish' });
    expect(button).toBeDisabled();
    fireEvent.mouseDown(screen.getByRole('combobox', { name: 'Connected account' }));
    fireEvent.click(screen.getByRole('option', { name: 'x' }));
    fireEvent.click(button); expect(publish).not.toHaveBeenCalled();
    vi.mocked(window.confirm).mockReturnValue(true); fireEvent.click(button);
    expect(publish).toHaveBeenCalledWith({ accountId: 'x-account', text: 'Reviewed source', confirmed: true });
  });
});
