import { render, screen, fireEvent, act } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, it, expect, vi, afterEach } from 'vitest';
import SearchBar from './SearchBar';

describe('SearchBar', () => {
  afterEach(() => {
    vi.useRealTimers();
  });

  it('renders input with placeholder', () => {
    render(<SearchBar value="" onChange={vi.fn()} />);
    expect(screen.getByPlaceholderText('Search articles...')).toBeInTheDocument();
  });

  it('renders custom placeholder', () => {
    render(<SearchBar value="" onChange={vi.fn()} placeholder="Find feeds..." />);
    expect(screen.getByPlaceholderText('Find feeds...')).toBeInTheDocument();
  });

  it('shows clear button when value is present', () => {
    render(<SearchBar value="test" onChange={vi.fn()} />);
    expect(screen.getByRole('button')).toBeInTheDocument();
  });

  it('does not show clear button when value is empty', () => {
    render(<SearchBar value="" onChange={vi.fn()} />);
    expect(screen.queryByRole('button')).not.toBeInTheDocument();
  });

  it('calls onChange after debounce delay', () => {
    vi.useFakeTimers();
    const onChange = vi.fn();
    render(<SearchBar value="" onChange={onChange} />);

    const input = screen.getByPlaceholderText('Search articles...');
    fireEvent.change(input, { target: { value: 'css' } });

    expect(onChange).not.toHaveBeenCalled();

    act(() => {
      vi.advanceTimersByTime(300);
    });

    expect(onChange).toHaveBeenCalledWith('css');
  });

  it('clears input when clear button is clicked', async () => {
    const user = userEvent.setup();
    const onChange = vi.fn();
    render(<SearchBar value="test" onChange={onChange} />);

    await user.click(screen.getByRole('button'));

    expect(onChange).toHaveBeenCalledWith('');
  });
});
