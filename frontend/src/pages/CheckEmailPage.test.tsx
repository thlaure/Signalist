import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { describe, it, expect } from 'vitest';
import CheckEmailPage from './CheckEmailPage';

describe('CheckEmailPage', () => {
  it('renders the translated email emphasis instead of literal markup', () => {
    render(<MemoryRouter initialEntries={[{ pathname: '/check-email', state: { email: 'browser-qa@example.test' } }]}><CheckEmailPage /></MemoryRouter>);
    expect(screen.getByText('browser-qa@example.test').tagName).toBe('STRONG');
    expect(screen.getByText(/We sent a verification link/)).not.toHaveTextContent('<strong>');
  });
});
