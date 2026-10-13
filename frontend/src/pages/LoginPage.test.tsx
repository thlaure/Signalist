import { render, screen, fireEvent } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import LoginPage from './LoginPage';

const { login } = vi.hoisted(() => ({ login: vi.fn() }));
vi.mock('../hooks/useAuth', () => ({ useAuth: () => ({ login }) }));

describe('LoginPage failure feedback', () => {
  beforeEach(() => { login.mockReset(); });

  it.each([500, 503])('hides internal server details for HTTP %i', async (status) => {
    login.mockRejectedValue(Object.assign(new Error('private database details'), {
      problem: { type: 'about:blank', status, detail: 'private database details' },
    }));
    render(<MemoryRouter><LoginPage /></MemoryRouter>);
    fireEvent.change(screen.getByLabelText(/Email/), { target: { value: 'qa@example.test' } });
    fireEvent.change(screen.getByLabelText(/Password/), { target: { value: 'test-only' } });
    fireEvent.click(screen.getByRole('button', { name: 'Sign in' }));
    expect(await screen.findByRole('alert')).not.toHaveTextContent('private database details');
    expect(login).toHaveBeenCalledOnce();
  });
});
