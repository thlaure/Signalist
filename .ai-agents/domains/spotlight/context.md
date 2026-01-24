# Spotlight Domain Context

## Overview
The Spotlight domain handles the natural language command interface - a Cmd+K activated search/command bar that maps user intent to system actions.

---

## Business Rules

1. **Activation: Cmd+K** (Mac) / **Ctrl+K** (Windows)
2. **Natural language input** - "Add feed X to category Y"
3. **Fuzzy matching** - Tolerate typos, variations
4. **Quick results** - Suggestions appear as user types
5. **Keyboard navigation** - Arrow keys, Enter to execute
6. **Context-aware** - Suggestions based on current view

---

## Intent Types

### CRUD Operations
| Intent | Example Input | Action |
|--------|---------------|--------|
| ADD_FEED | "Add https://... to Tech" | Create feed |
| DELETE_FEED | "Remove feed X" | Delete feed |
| CREATE_CATEGORY | "Create category AI News" | Create category |
| BOOKMARK | "Save this article" | Bookmark current |
| TAG | "Tag as important" | Add tag |

### Navigation
| Intent | Example Input | Action |
|--------|---------------|--------|
| GO_TO | "Go to bookmarks" | Navigate |
| OPEN_ARTICLE | "Open article about..." | Open detail |
| FILTER | "Show unread" | Apply filter |

### Search
| Intent | Example Input | Action |
|--------|---------------|--------|
| SEARCH | "Find articles about LLMs" | Semantic search |
| SIMILAR | "Similar to this" | Find related |

### AI Actions
| Intent | Example Input | Action |
|--------|---------------|--------|
| SUMMARIZE | "Summarize this" | Generate summary |
| GENERATE_NEWSLETTER | "Create newsletter" | Newsletter flow |

---

## Command Parsing

### NLP Pipeline
```
User Input
    ↓
Tokenize
    ↓
Intent Classification (LLM or rule-based)
    ↓
Entity Extraction
    ├── URLs
    ├── Category names
    ├── Tags
    └── Search queries
    ↓
Command Generation
    ↓
Execute or Suggest
```

### Rule-Based Patterns
```javascript
const patterns = [
  { pattern: /^add\s+(https?:\/\/\S+)\s+to\s+(.+)$/i, intent: 'ADD_FEED' },
  { pattern: /^(search|find)\s+(.+)$/i, intent: 'SEARCH' },
  { pattern: /^go\s+to\s+(.+)$/i, intent: 'GO_TO' },
  { pattern: /^bookmark\s*(this)?$/i, intent: 'BOOKMARK' },
  { pattern: /^tag\s+(?:as\s+)?(.+)$/i, intent: 'TAG' },
];
```

### LLM-Based (Fallback)
For complex queries that don't match patterns:

```
Parse this user command and return structured JSON:
User: "{input}"

Return:
{
  "intent": "ADD_FEED|SEARCH|BOOKMARK|...",
  "entities": {
    "url": "...",
    "category": "...",
    "query": "..."
  },
  "confidence": 0.0-1.0
}
```

---

## Frontend Implementation

### Component Structure
```
<Spotlight>
  ├── <SpotlightInput />        # Text input
  ├── <SpotlightSuggestions />  # Result list
  │   └── <SuggestionItem />
  └── <SpotlightShortcuts />    # Keyboard hints
</Spotlight>
```

### Keyboard Shortcuts
| Key | Action |
|-----|--------|
| `Cmd+K` / `Ctrl+K` | Open Spotlight |
| `Escape` | Close |
| `↑` / `↓` | Navigate suggestions |
| `Enter` | Execute selected |
| `Tab` | Autocomplete |

### State Management
```typescript
interface SpotlightState {
  isOpen: boolean;
  query: string;
  suggestions: Suggestion[];
  selectedIndex: number;
  isLoading: boolean;
}

interface Suggestion {
  id: string;
  type: 'action' | 'navigation' | 'search';
  label: string;
  description?: string;
  icon?: string;
  action: () => void;
}
```

---

## Backend Support

### Endpoints
| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/v1/spotlight/parse` | Parse command, return intent |
| GET | `/api/v1/spotlight/suggest?q=` | Get suggestions |
| POST | `/api/v1/spotlight/execute` | Execute parsed command |

### Parse Response
```json
{
  "intent": "ADD_FEED",
  "entities": {
    "url": "https://example.com/feed",
    "category": "Tech"
  },
  "confidence": 0.95,
  "suggestions": [
    { "label": "Add to Tech", "action": "add_feed" },
    { "label": "Add to Technology", "action": "add_feed" }
  ]
}
```

---

## MCP Integration

Spotlight commands can be executed via MCP:

```php
#[AsTool(
    name: 'execute_command',
    description: 'Execute a Spotlight command'
)]
class ExecuteCommandTool
{
    public function __invoke(string $command): array
    {
        // Parse and execute
    }
}
```

---

## Error Handling

### UX Responses (Frontend)
| Error | Response |
|-------|----------|
| Unrecognized command | Show "I don't understand. Try..." |
| Ambiguous intent | Show multiple suggestions |
| Entity not found | "Category 'X' not found. Create it?" |
| Action failed | Show error, suggest retry |

### API Errors (RFC 7807)
Backend errors return Problem Details format:
```json
{
  "type": "https://signalist.app/problems/not-found",
  "title": "Category Not Found",
  "status": 404,
  "detail": "The category 'Tech' was not found"
}
```
Frontend transforms these into user-friendly messages.

---

## Accessibility

- Focus management when opening/closing
- ARIA labels for all interactive elements
- Screen reader announcements for suggestions
- High contrast mode support
- Reduced motion support

---

## Related Domains

- **Feed**: Add/remove feeds
- **Article**: Search, bookmark
- **Bookmark**: Save articles
- **Newsletter**: Generate command
- **Search**: Semantic queries
