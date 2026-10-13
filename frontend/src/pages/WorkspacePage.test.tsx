import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import WorkspacePage from './WorkspacePage';

const state = vi.hoisted(() => ({ error: undefined as Error | undefined }));
vi.mock('../hooks/useWorkspaces', () => ({ useWorkspace: () => ({
  workspace: { data: { name: 'QA workspace', role: 'admin' } },
  members: { data: [] }, categories: { data: [] }, feeds: { data: [] },
  articles: { data: [] }, bookmarks: { data: [] }, activity: { data: [] },
  action: { error: state.error, mutate: vi.fn(), isPending: false, isSuccess: false },
}) }));

describe('WorkspacePage failure feedback', () => {
  beforeEach(() => { state.error = undefined; });

  it('explains a plan limit without exposing server details', () => {
    state.error = Object.assign(new Error('private server detail'), {
      problem: { type: 'https://signalist.app/problems/plan-limit-reached', status: 402 },
    });
    render(<MemoryRouter><WorkspacePage /></MemoryRouter>);
    expect(screen.getByRole('alert')).toHaveTextContent('Your workspace plan limit has been reached. Review plans and billing before adding more resources or members.');
    expect(screen.queryByText('private server detail')).not.toBeInTheDocument();
  });

  it('keeps unexpected failures generic', () => {
    state.error = new Error('private server detail');
    render(<MemoryRouter><WorkspacePage /></MemoryRouter>);
    expect(screen.getByRole('alert')).toHaveTextContent('Action failed. Check your permissions and supplied values.');
  });
});
