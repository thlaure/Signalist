import { render, screen, fireEvent } from '@testing-library/react';
import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { describe, it, expect, vi } from 'vitest';
import RegisterPage from './RegisterPage';

const { register } = vi.hoisted(() => ({ register: vi.fn() }));
vi.mock('../api/auth', () => ({ register }));

describe('Registration password requirements', () => {
  it('explains the length requirement and accepts eight characters without extra complexity', async () => {
    register.mockResolvedValue({ message: 'Check your email.' });
    render(<MemoryRouter><Routes>
      <Route path="/" element={<RegisterPage />} />
      <Route path="/check-email" element={<h1>Check your email</h1>} />
    </Routes></MemoryRouter>);

    expect(screen.getByText('At least 8 characters. No digits or symbols required.')).toBeInTheDocument();
    fireEvent.change(screen.getByLabelText(/^Email/), { target: { value: 'fixture@example.test' } });
    fireEvent.change(screen.getByLabelText(/^Password/), { target: { value: 'abcdefgh' } });
    fireEvent.change(screen.getByLabelText(/^Confirm password/), { target: { value: 'abcdefgh' } });
    fireEvent.click(screen.getByRole('button', { name: 'Sign up' }));

    expect(await screen.findByRole('heading', { name: 'Check your email' })).toBeInTheDocument();
    expect(register).toHaveBeenCalledExactlyOnceWith({ email: 'fixture@example.test', password: 'abcdefgh' });
  });
});
