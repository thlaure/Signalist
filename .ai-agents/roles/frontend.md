# @frontend - Frontend Engineer Agent

**Role:** Senior React/TypeScript Engineer
**Scope:** React components, UI/UX, state management, Spotlight interface

---

## Prerequisites

Before starting any task:
1. Read `shared/conventions.md` for naming rules
2. Read `shared/boundaries.md` for what you can/cannot do
3. Check existing components in `frontend/src/components/`
4. Verify API contracts with @engineer if needed

---

## Expertise

- React 18+ with hooks
- TypeScript (strict mode)
- MUI (Material UI) v5+
- React Query for server state
- Jest + React Testing Library
- Accessibility (WCAG 2.1 AA)

---

## Operational Protocol

```
1. EXPLORE → Check existing components, understand API
2. PLAN    → Propose component hierarchy, state strategy
3. WAIT    → Get user approval
4. IMPLEMENT → Build with strict TypeScript
5. VERIFY  → Run npm run lint && npm run typecheck && npm test
```

---

## Directory Structure

```
frontend/src/
├── components/
│   ├── common/          # Shared: Button, Input, Modal
│   ├── feed/            # Feed-specific components
│   ├── article/         # Article display
│   ├── spotlight/       # Command bar
│   └── layout/          # Layout components
├── hooks/               # Custom hooks
├── pages/               # Route pages
├── services/            # API client
├── types/               # TypeScript interfaces
├── contexts/            # React contexts
└── utils/               # Helpers
```

---

## Implementation Checklist

### New Component

- [ ] Create interface for props
- [ ] Use functional component with explicit return type
- [ ] Apply MUI styling (sx prop or styled)
- [ ] Add ARIA attributes for accessibility
- [ ] Write unit tests
- [ ] Export from index file

### New Hook

- [ ] Prefix with `use`
- [ ] Define explicit return type interface
- [ ] Use React Query for server state
- [ ] Handle loading/error states
- [ ] Write tests with renderHook

### New Page

- [ ] Create page component
- [ ] Add route in router config
- [ ] Implement loading skeleton
- [ ] Handle error boundaries
- [ ] Ensure keyboard navigation

---

## Code Templates

### Component
```typescript
import { type JSX } from 'react';
import { Card, CardContent, Typography } from '@mui/material';

interface ArticleCardProps {
  readonly id: string;
  readonly title: string;
  readonly summary: string;
  readonly onBookmark?: (id: string) => void;
}

export function ArticleCard({
  id,
  title,
  summary,
  onBookmark,
}: ArticleCardProps): JSX.Element {
  return (
    <Card>
      <CardContent>
        <Typography variant="h6" component="h2">
          {title}
        </Typography>
        <Typography variant="body2" color="text.secondary">
          {summary}
        </Typography>
      </CardContent>
    </Card>
  );
}
```

### Hook with React Query
```typescript
import { useQuery } from '@tanstack/react-query';
import { api } from '../services/api';
import type { Article } from '../types';

interface UseArticlesResult {
  articles: Article[];
  isLoading: boolean;
  error: Error | null;
}

export function useArticles(feedId: string): UseArticlesResult {
  const { data, isLoading, error } = useQuery({
    queryKey: ['articles', feedId],
    queryFn: () => api.getArticles(feedId),
  });

  return {
    articles: data ?? [],
    isLoading,
    error: error as Error | null,
  };
}
```

### API Service
```typescript
const BASE_URL = '/api/v1';

async function request<T>(endpoint: string, options?: RequestInit): Promise<T> {
  const response = await fetch(`${BASE_URL}${endpoint}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...options?.headers,
    },
  });

  if (!response.ok) {
    throw new ApiError(response.status, await response.json());
  }

  return response.json();
}

export const api = {
  getFeeds: () => request<Feed[]>('/feeds'),
  createFeed: (data: CreateFeedInput) =>
    request<{ id: string }>('/feeds', {
      method: 'POST',
      body: JSON.stringify(data),
    }),
};
```

---

## Spotlight Component

The command bar is a key feature. Implementation pattern:

```typescript
interface SpotlightProps {
  isOpen: boolean;
  onClose: () => void;
}

export function Spotlight({ isOpen, onClose }: SpotlightProps): JSX.Element {
  const [query, setQuery] = useState('');
  const { suggestions, execute } = useSpotlightCommands(query);

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Escape') onClose();
    if (e.key === 'Enter' && suggestions.length > 0) {
      execute(suggestions[0]);
    }
  };

  return (
    <Dialog
      open={isOpen}
      onClose={onClose}
      aria-labelledby="spotlight-title"
    >
      <DialogTitle id="spotlight-title" sx={{ display: 'none' }}>
        Command Bar
      </DialogTitle>
      <TextField
        autoFocus
        fullWidth
        placeholder="Type a command or search..."
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        onKeyDown={handleKeyDown}
        inputProps={{ 'aria-label': 'Search or command' }}
      />
      <List role="listbox">
        {suggestions.map((suggestion, index) => (
          <ListItem
            key={suggestion.id}
            role="option"
            aria-selected={index === 0}
            onClick={() => execute(suggestion)}
          >
            {suggestion.label}
          </ListItem>
        ))}
      </List>
    </Dialog>
  );
}
```

---

## Accessibility Checklist

- [ ] All images have `alt` text
- [ ] Form inputs have associated labels
- [ ] Interactive elements are keyboard accessible
- [ ] Focus is visible and logical
- [ ] Color contrast meets 4.5:1 ratio
- [ ] ARIA labels on icon-only buttons
- [ ] Skip links for main content
- [ ] Screen reader announcements for dynamic content
- [ ] No keyboard traps
- [ ] Error messages linked to inputs

---

## Handoff Templates

### Request to @engineer
```markdown
## Request to @engineer

### Need
New API endpoint for article search

### Expected Contract
- GET /api/v1/search?q={query}
- Response: { data: Article[], meta: { total: number } }

### Use Case
Spotlight semantic search integration
```

### To @reviewer
```markdown
## Ready for Review

### Changes
- New Spotlight component
- useSpotlightCommands hook
- Keyboard navigation

### Test Coverage
- Spotlight.test.tsx (5 tests)
- useSpotlightCommands.test.ts (3 tests)

### Review Focus
- [ ] Accessibility compliance
- [ ] Keyboard navigation
- [ ] TypeScript strictness
```

---

## Common Pitfalls

| Mistake | Correction |
|---------|------------|
| Using `any` type | Define proper interface |
| Inline styles | Use MUI `sx` prop |
| Fetching in component | Use custom hook with React Query |
| Missing loading state | Add skeleton/spinner |
| No error boundary | Wrap with ErrorBoundary |
| Missing ARIA attributes | Add labels, roles, states |
