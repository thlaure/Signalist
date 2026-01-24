# Newsletter Domain Context

## Overview
The Newsletter domain handles AI-generated digests of unread articles, scheduling, and delivery.

---

## Business Rules

1. **Reading time calculation** - 200 words per minute
2. **Default duration: 5 minutes** - ~1000 words of content
3. **Group by category** - Organized structure
4. **Source URLs required** - Every article must link to source
5. **Scheduling via Symfony Scheduler** - Not cron
6. **Manual send also supported** - Immediate delivery

---

## Entities

### Newsletter
```
- id: UUID
- content: text (generated HTML/Markdown)
- articleIds: UUID[] (included articles)
- scheduledFor: DateTime (nullable)
- sentAt: DateTime (nullable)
- status: enum (draft, scheduled, sent, failed)
- readingTimeMinutes: int
- createdAt: DateTime
```

### NewsletterSchedule (future)
```
- id: UUID
- frequency: enum (daily, weekly, custom)
- dayOfWeek: int (nullable, for weekly)
- timeOfDay: time
- categoryFilter: UUID[] (nullable)
- isActive: bool
```

---

## Commands

| Command | Purpose | Handler |
|---------|---------|---------|
| `GenerateNewsletterCommand` | Create newsletter content | Uses LLM |
| `ScheduleNewsletterCommand` | Set delivery time | - |
| `SendNewsletterCommand` | Send immediately | Uses Mailer |
| `CancelNewsletterCommand` | Cancel scheduled | - |

---

## Queries

| Query | Purpose |
|-------|---------|
| `NewsletterListQuery` | List newsletters by status |
| `NewsletterDetailQuery` | Single newsletter with articles |
| `NewsletterPreviewQuery` | Preview before sending |

---

## Async Messages

| Message | Handler | Trigger |
|---------|---------|---------|
| `GenerateNewsletterMessage` | LLM synthesis | Manual or scheduled |
| `SendNewsletterMessage` | Email delivery | When scheduled time reached |

---

## Content Generation Pipeline

```
GenerateNewsletterCommand
    ↓
Select unread articles (NewsletterContentSelector)
    ├── Filter by category (optional)
    ├── Calculate total words
    └── Stop when reading time reached
    ↓
Build LLM prompt
    ├── Article titles
    ├── Summaries
    ├── Source URLs
    └── Categories
    ↓
Generate with LLM
    ↓
Store Newsletter
    ↓
Mark articles as read (optional)
```

### Content Selection
```php
class NewsletterContentSelector
{
    private const WORDS_PER_MINUTE = 200;

    public function select(int $minutes, ?string $categoryId = null): array
    {
        $targetWords = $minutes * self::WORDS_PER_MINUTE;
        $articles = $this->articleRepo->findUnread($categoryId);

        $selected = [];
        $totalWords = 0;

        foreach ($articles as $article) {
            $words = str_word_count($article->getSummary());
            if ($totalWords + $words > $targetWords) break;
            $selected[] = $article;
            $totalWords += $words;
        }

        return $selected;
    }
}
```

### LLM Prompt
```
Create a newsletter digest from these articles.
Group by category. For each article include:
- A clickable title (markdown link to source URL)
- A 1-2 sentence summary

Target reading time: {minutes} minutes

Articles:
{articlesJson}

IMPORTANT: Always include the source URL for each article.
```

---

## Scheduling

### Symfony Scheduler Integration
```php
#[AsSchedule('newsletter')]
class NewsletterSchedule implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                RecurringMessage::every('1 day', new GenerateNewsletterMessage())
                    ->at('08:00')
            );
    }
}
```

---

## Email Delivery

### Template Structure
```html
<h1>Your Daily Signal</h1>
<p>{{ date }}</p>

{% for category, articles in groupedArticles %}
<h2>{{ category }}</h2>
{% for article in articles %}
<div>
  <h3><a href="{{ article.sourceUrl }}">{{ article.title }}</a></h3>
  <p>{{ article.summary }}</p>
</div>
{% endfor %}
{% endfor %}

<footer>
  Powered by Signalist
</footer>
```

---

## Error Handling (RFC 7807)

| Error | Exception | Problem Type | Status |
|-------|-----------|--------------|--------|
| No unread articles | `NoContentForNewsletterException` | `/problems/unprocessable` | 422 |
| LLM generation failed | - | - (logged, async) | - |
| Email delivery failed | - | - (logged, retry) | - |
| Newsletter not found | `NewsletterNotFoundException` | `/problems/not-found` | 404 |

---

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/newsletters` | List newsletters |
| POST | `/api/v1/newsletters/generate` | Generate new |
| GET | `/api/v1/newsletters/{id}` | Get newsletter |
| GET | `/api/v1/newsletters/{id}/preview` | Preview content |
| POST | `/api/v1/newsletters/{id}/send` | Send now |
| POST | `/api/v1/newsletters/{id}/schedule` | Schedule |
| DELETE | `/api/v1/newsletters/{id}` | Delete/cancel |

---

## Related Domains

- **Article**: Content source
- **Spotlight**: "Generate newsletter" command
