import { render, screen, fireEvent } from '@testing-library/react';
import { Link, MemoryRouter } from 'react-router-dom';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import App from './App';

const state = vi.hoisted(() => ({ authenticated: true }));
vi.mock('./hooks/useAuth', () => ({ useAuth: () => ({ isAuthenticated: state.authenticated }) }));
vi.mock('./components/Layout/Sidebar', () => ({ default: () => <nav aria-label="Application navigation"><Link to="/newsletters">Open newsletters</Link></nav> }));
vi.mock('./components/Layout/CommandPalette', () => ({ default: () => null }));
vi.mock('./pages/SettingsPage', () => ({ default: () => <h1>Account settings route</h1> }));
vi.mock('./pages/NewslettersPage', () => ({ default: () => <h1>Newsletter route</h1> }));
vi.mock('./pages/LoginPage', () => ({ default: () => <h1>Sign in route</h1> }));

describe('On-demand routes', () => {
  beforeEach(() => { state.authenticated = true; });

  it('loads protected pages while retaining application navigation', async () => {
    render(<MemoryRouter initialEntries={['/settings']}><App /></MemoryRouter>);
    expect(await screen.findByRole('heading', { name: 'Account settings route' })).toBeInTheDocument();
    const navigation = screen.getByRole('navigation', { name: 'Application navigation' });
    fireEvent.click(screen.getByRole('link', { name: 'Open newsletters' }));
    expect(await screen.findByRole('heading', { name: 'Newsletter route' })).toBeInTheDocument();
    expect(screen.getByRole('navigation', { name: 'Application navigation' })).toBe(navigation);
  });

  it('redirects unauthenticated deep links before showing protected content', async () => {
    state.authenticated = false;
    render(<MemoryRouter initialEntries={['/settings']}><App /></MemoryRouter>);
    expect(await screen.findByRole('heading', { name: 'Sign in route' })).toBeInTheDocument();
    expect(screen.queryByRole('heading', { name: 'Account settings route' })).not.toBeInTheDocument();
    expect(screen.queryByRole('navigation')).not.toBeInTheDocument();
  });
});
