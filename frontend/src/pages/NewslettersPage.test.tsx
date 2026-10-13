import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import NewslettersPage from './NewslettersPage';
const { save } = vi.hoisted(() => ({ save: vi.fn() }));
vi.mock('../hooks/useNewsletters', () => ({ useNewsletters: () => ({
  list: { data: [] }, save: { mutate: save }, remove: { mutate: vi.fn() }, generate: { mutate: vi.fn() },
}) }));
describe('NewslettersPage', () => {
  it('creates a manual briefing with the selected reading budget', () => {
    render(<NewslettersPage />);
    fireEvent.change(screen.getByRole('textbox', { name: /Title/ }), { target: { value: 'Weekly highlights' } });
    fireEvent.change(screen.getByRole('spinbutton', { name: /Reading time/ }), { target: { value: '10' } });
    fireEvent.click(screen.getByRole('button', { name: 'Save' }));
    expect(save).toHaveBeenCalledWith({ id: undefined, input: { title: 'Weekly highlights', readingMinutes: 10, intervalHours: null } }, expect.objectContaining({ onSuccess: expect.any(Function) }));
  });
});
