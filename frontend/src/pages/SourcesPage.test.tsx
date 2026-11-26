import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import SourcesPage from './SourcesPage';

const state = vi.hoisted(() => ({ items: [] as { id: string; title: string; url: string; status: string; lastError: null }[], required: false, add: vi.fn(), remove: vi.fn(), select: vi.fn() }));
vi.mock('../hooks/useDigestMvp', () => ({ useSources: () => ({
  list: { data: { items: state.items, limit: 3, selectionRequired: state.required } },
  add: { mutate: state.add }, remove: { mutate: state.remove }, select: { mutate: state.select },
}) }));
describe('MVP sources', () => {
  beforeEach(() => { vi.clearAllMocks(); state.items = []; state.required = false; });
  it('adds an RSS address without requiring a category', () => {
    render(<MemoryRouter><SourcesPage /></MemoryRouter>);
    fireEvent.change(screen.getByRole('textbox', { name: /RSS feed address/ }), { target: { value: 'https://example.com/rss' } });
    fireEvent.click(screen.getByRole('button', { name: 'Add' }));
    expect(state.add).toHaveBeenCalledWith('https://example.com/rss', expect.any(Object));
  });
  it('requires selecting within the free quota after downgrade', async () => {
    state.items = ['a', 'b', 'c', 'd'].map((id) => ({ id, title: id, url: `https://example.com/${id}`, status: 'active', lastError: null }));
    state.required = true;
    render(<MemoryRouter><SourcesPage /></MemoryRouter>);
    expect(screen.getByRole('button', { name: 'Add' })).toBeDisabled();
    expect(screen.getByRole('button', { name: 'Save active sources' })).toBeDisabled();
    fireEvent.click(screen.getByRole('checkbox', { name: 'd' }));
    fireEvent.click(screen.getByRole('button', { name: 'Save active sources' }));
    await waitFor(() => expect(state.select).toHaveBeenCalledWith(['a', 'b', 'c'], expect.any(Object)));
  });
  it('confirms source removal before deleting its articles', () => {
    state.items = [{ id: 'a', title: 'My source', url: 'https://example.com/rss', status: 'active', lastError: null }];
    render(<MemoryRouter><SourcesPage /></MemoryRouter>);
    fireEvent.click(screen.getByRole('button', { name: 'Delete' }));
    expect(state.remove).not.toHaveBeenCalled();
    fireEvent.click(screen.getByRole('dialog').querySelectorAll('button')[1]);
    expect(state.remove).toHaveBeenCalledWith('a', expect.any(Object));
  });
});
