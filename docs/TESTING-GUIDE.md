# Signalist MVP Testing Guide

This document provides step-by-step instructions to verify each phase of the MVP implementation.

---

## Prerequisites

Ensure containers are running:
```bash
docker compose up -d
docker compose ps  # All services should be "Up" and "healthy"
```

---

## Phase 0: Foundation

### 0.1 Verify PHP Version
```bash
docker compose exec app php -v
```
**Expected:** PHP 8.5.x

### 0.2 Verify Symfony Cache
```bash
docker compose exec app php bin/console cache:clear
```
**Expected:** "Cache for the dev environment was successfully cleared"

### 0.3 Verify Code Style (Lint)
```bash
docker compose exec app vendor/bin/php-cs-fixer fix --dry-run --diff
```
**Expected:** "0 of X files that can be fixed" (no issues)

### 0.4 Verify Static Analysis
```bash
docker compose exec app vendor/bin/phpstan analyse
```
**Expected:** "[OK] No errors"

### 0.5 Verify Symfony Routes
```bash
docker compose exec app php bin/console debug:router | grep api
```
**Expected:** API Platform routes listed with `/api/v1` prefix

### 0.6 Verify Messenger Configuration
```bash
docker compose exec app php bin/console debug:messenger
```
**Expected:** Shows `async` transport and `CrawlFeedMessage` routing

### 0.7 Test RFC 7807 Exception Handling
```bash
# Test 404 error format
curl -s http://localhost:8000/api/v1/nonexistent | jq .
```
**Expected:** JSON response with RFC 7807 format:
```json
{
  "type": "https://signalist.app/problems/http-error",
  "title": "Not Found",
  "status": 404,
  "detail": "...",
  "instance": "/api/v1/nonexistent"
}
```

### 0.8 Verify Database Connection
```bash
docker compose exec app php bin/console doctrine:database:create --if-not-exists
```
**Expected:** Database created or already exists message

### 0.9 Verify Redis Connection
```bash
docker compose exec redis redis-cli ping
```
**Expected:** `PONG`

### 0.10 Verify pgvector Extension
```bash
docker compose exec postgres psql -U signalist -d signalist -c "SELECT * FROM pg_extension WHERE extname = 'vector';"
```
**Expected:** Shows `vector` extension row

---

## Phase 1: Database Entities

### 1.1 Generate Migration
```bash
docker compose exec app php bin/console doctrine:migrations:diff
```
**Expected:** Migration file created in `migrations/`

### 1.2 Run Migration
```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```
**Expected:** Migration executed successfully

### 1.3 Verify Tables Created
```bash
docker compose exec postgres psql -U signalist -d signalist -c "\dt"
```
**Expected:** Tables listed:
- `category`
- `feed`
- `article`
- `bookmark`
- `article_embedding`
- `doctrine_migration_versions`

### 1.4 Verify Table Structure
```bash
# Check category table
docker compose exec postgres psql -U signalist -d signalist -c "\d category"

# Check feed table
docker compose exec postgres psql -U signalist -d signalist -c "\d feed"

# Check article table
docker compose exec postgres psql -U signalist -d signalist -c "\d article"
```
**Expected:** Columns match entity definitions

### 1.5 Run PHPStan on Entities
```bash
docker compose exec app vendor/bin/phpstan analyse src/Entity
```
**Expected:** "[OK] No errors"

---

## Phase 2: Category Domain

### 2.1 Run Unit Tests
```bash
docker compose exec app php bin/phpunit tests/Unit/Domain/Category
```
**Expected:** All tests pass

### 2.2 Test API - List Categories (Empty)
```bash
curl -s http://localhost:8000/api/v1/categories | jq .
```
**Expected:** Empty array `[]` or paginated response with empty items

### 2.3 Test API - Create Category
```bash
curl -s -X POST http://localhost:8000/api/v1/categories \
  -H "Content-Type: application/json" \
  -d '{"name": "Technology", "description": "Tech news and articles"}' | jq .
```
**Expected:** Created category with `id`, `name`, `slug`, `createdAt`

### 2.4 Test API - Get Category
```bash
# Replace {id} with actual UUID from previous response
curl -s http://localhost:8000/api/v1/categories/{id} | jq .
```
**Expected:** Category details

### 2.5 Test API - Update Category
```bash
curl -s -X PUT http://localhost:8000/api/v1/categories/{id} \
  -H "Content-Type: application/json" \
  -d '{"name": "Tech", "color": "#3498db"}' | jq .
```
**Expected:** Updated category

### 2.6 Test API - Delete Category
```bash
curl -s -X DELETE http://localhost:8000/api/v1/categories/{id}
```
**Expected:** 204 No Content

### 2.7 Test Validation Error (RFC 7807)
```bash
curl -s -X POST http://localhost:8000/api/v1/categories \
  -H "Content-Type: application/json" \
  -d '{"name": ""}' | jq .
```
**Expected:** RFC 7807 validation error with `errors` array

### 2.8 Test Conflict Error (Duplicate)
```bash
# Create category
curl -s -X POST http://localhost:8000/api/v1/categories \
  -H "Content-Type: application/json" \
  -d '{"name": "Duplicate Test"}'

# Try to create same name again
curl -s -X POST http://localhost:8000/api/v1/categories \
  -H "Content-Type: application/json" \
  -d '{"name": "Duplicate Test"}' | jq .
```
**Expected:** 409 Conflict with RFC 7807 format

---

## Phase 3: Feed Domain

### 3.1 Run Unit Tests
```bash
docker compose exec app php bin/phpunit tests/Unit/Domain/Feed
```
**Expected:** All tests pass

### 3.2 Create Test Category First
```bash
CATEGORY_ID=$(curl -s -X POST http://localhost:8000/api/v1/categories \
  -H "Content-Type: application/json" \
  -d '{"name": "News"}' | jq -r '.id')
echo "Category ID: $CATEGORY_ID"
```

### 3.3 Test API - Add Feed
```bash
curl -s -X POST http://localhost:8000/api/v1/feeds \
  -H "Content-Type: application/json" \
  -d "{\"url\": \"https://feeds.bbci.co.uk/news/rss.xml\", \"categoryId\": \"$CATEGORY_ID\"}" | jq .
```
**Expected:** Created feed with `id`, `title`, `url`, `status: "active"`

### 3.4 Verify Async Message Dispatched
```bash
# Check messenger worker logs
docker compose logs messenger --tail=20
```
**Expected:** Shows `CrawlFeedMessage` being processed

### 3.5 Test API - List Feeds
```bash
curl -s http://localhost:8000/api/v1/feeds | jq .
```
**Expected:** List of feeds with article counts

### 3.6 Test API - Get Feed
```bash
curl -s http://localhost:8000/api/v1/feeds/{feedId} | jq .
```
**Expected:** Feed details with `lastFetchedAt` populated after crawl

### 3.7 Test Invalid Feed URL
```bash
curl -s -X POST http://localhost:8000/api/v1/feeds \
  -H "Content-Type: application/json" \
  -d "{\"url\": \"not-a-url\", \"categoryId\": \"$CATEGORY_ID\"}" | jq .
```
**Expected:** 400 Validation Error

### 3.8 Test Duplicate Feed URL
```bash
curl -s -X POST http://localhost:8000/api/v1/feeds \
  -H "Content-Type: application/json" \
  -d "{\"url\": \"https://feeds.bbci.co.uk/news/rss.xml\", \"categoryId\": \"$CATEGORY_ID\"}" | jq .
```
**Expected:** 409 Conflict

---

## Phase 4: Article Domain

### 4.1 Run Unit Tests
```bash
docker compose exec app php bin/phpunit tests/Unit/Domain/Article
```
**Expected:** All tests pass

### 4.2 Test API - List Articles
```bash
curl -s "http://localhost:8000/api/v1/articles" | jq .
```
**Expected:** Paginated list of articles (after feed crawl completes)

### 4.3 Test API - Filter by Feed
```bash
curl -s "http://localhost:8000/api/v1/articles?feed={feedId}" | jq .
```
**Expected:** Articles from specific feed only

### 4.4 Test API - Filter by Category
```bash
curl -s "http://localhost:8000/api/v1/articles?category={categoryId}" | jq .
```
**Expected:** Articles from feeds in specific category

### 4.5 Test API - Filter Unread
```bash
curl -s "http://localhost:8000/api/v1/articles?isRead=false" | jq .
```
**Expected:** Only unread articles

### 4.6 Test API - Get Article
```bash
curl -s http://localhost:8000/api/v1/articles/{articleId} | jq .
```
**Expected:** Full article details with content

### 4.7 Test API - Mark as Read
```bash
curl -s -X PATCH http://localhost:8000/api/v1/articles/{articleId}/read
```
**Expected:** 200 OK, article `isRead: true`

### 4.8 Test API - Mark as Unread
```bash
curl -s -X PATCH http://localhost:8000/api/v1/articles/{articleId}/unread
```
**Expected:** 200 OK, article `isRead: false`

---

## Phase 5: Bookmark Domain

### 5.1 Run Unit Tests
```bash
docker compose exec app php bin/phpunit tests/Unit/Domain/Bookmark
```
**Expected:** All tests pass

### 5.2 Test API - Create Bookmark
```bash
curl -s -X POST http://localhost:8000/api/v1/bookmarks \
  -H "Content-Type: application/json" \
  -d "{\"articleId\": \"{articleId}\", \"notes\": \"Interesting article\"}" | jq .
```
**Expected:** Created bookmark with `id`, `articleId`, `notes`, `createdAt`

### 5.3 Test API - List Bookmarks
```bash
curl -s http://localhost:8000/api/v1/bookmarks | jq .
```
**Expected:** List of bookmarks with embedded article data

### 5.4 Test API - Update Bookmark Notes
```bash
curl -s -X PUT http://localhost:8000/api/v1/bookmarks/{bookmarkId} \
  -H "Content-Type: application/json" \
  -d '{"notes": "Updated notes"}' | jq .
```
**Expected:** Updated bookmark

### 5.5 Test API - Delete Bookmark
```bash
curl -s -X DELETE http://localhost:8000/api/v1/bookmarks/{bookmarkId}
```
**Expected:** 204 No Content

### 5.6 Test Duplicate Bookmark
```bash
# Try to bookmark same article twice
curl -s -X POST http://localhost:8000/api/v1/bookmarks \
  -H "Content-Type: application/json" \
  -d "{\"articleId\": \"{articleId}\"}" | jq .
```
**Expected:** 409 Conflict - Article already bookmarked

---

## Phase 6: Frontend Setup

### 6.1 Verify Node/npm
```bash
cd frontend
node -v  # Should be 18+
npm -v   # Should be 9+
```

### 6.2 Install Dependencies
```bash
npm install
```
**Expected:** No errors, `node_modules` created

### 6.3 Run Development Server
```bash
npm run dev
```
**Expected:** Vite server starts at http://localhost:5173

### 6.4 Verify API Proxy
```bash
# In another terminal, with frontend dev server running
curl -s http://localhost:5173/api/v1/categories | jq .
```
**Expected:** Same response as http://localhost:8000/api/v1/categories

### 6.5 Run Tests
```bash
npm test
```
**Expected:** All tests pass

### 6.6 Build Production
```bash
npm run build
```
**Expected:** Build completes, `dist/` folder created

### 6.7 Type Check
```bash
npm run typecheck  # or: npx tsc --noEmit
```
**Expected:** No TypeScript errors

---

## Phase 7: Frontend Components

### 7.1 Visual Testing Checklist

Open http://localhost:5173 in browser and verify:

- [ ] **Layout:** Header, sidebar, main content area visible
- [ ] **Sidebar:** Categories listed with feed counts
- [ ] **Dashboard:** Recent articles displayed as cards
- [ ] **Article Card:** Shows title, summary, feed name, published date
- [ ] **Read/Unread:** Visual indicator for read status
- [ ] **Bookmark Button:** Toggles bookmark state
- [ ] **Category Navigation:** Clicking category filters articles
- [ ] **Responsive:** Layout adapts on mobile viewport

### 7.2 Functional Testing Checklist

- [ ] **Add Category:** Dialog opens, form submits, category appears
- [ ] **Add Feed:** Dialog opens, URL validates, feed appears after crawl
- [ ] **Mark Read:** Click article, status changes
- [ ] **Bookmark:** Click bookmark icon, appears in bookmarks page
- [ ] **Navigation:** All routes work (`/`, `/categories/:id`, `/bookmarks`)
- [ ] **Error Handling:** Invalid actions show error messages
- [ ] **Loading States:** Spinners shown during API calls

### 7.3 Run Component Tests
```bash
npm test -- --coverage
```
**Expected:** Coverage report generated, all tests pass

---

## Full Integration Test

### End-to-End Flow
```bash
# 1. Create category
CATEGORY=$(curl -s -X POST http://localhost:8000/api/v1/categories \
  -H "Content-Type: application/json" \
  -d '{"name": "Integration Test"}')
CATEGORY_ID=$(echo $CATEGORY | jq -r '.id')
echo "Created category: $CATEGORY_ID"

# 2. Add feed
FEED=$(curl -s -X POST http://localhost:8000/api/v1/feeds \
  -H "Content-Type: application/json" \
  -d "{\"url\": \"https://hnrss.org/frontpage\", \"categoryId\": \"$CATEGORY_ID\"}")
FEED_ID=$(echo $FEED | jq -r '.id')
echo "Created feed: $FEED_ID"

# 3. Wait for crawl (check messenger logs)
sleep 10

# 4. List articles
ARTICLES=$(curl -s "http://localhost:8000/api/v1/articles?feed=$FEED_ID")
ARTICLE_ID=$(echo $ARTICLES | jq -r '.[0].id // empty')
echo "First article: $ARTICLE_ID"

# 5. Bookmark article
if [ -n "$ARTICLE_ID" ]; then
  curl -s -X POST http://localhost:8000/api/v1/bookmarks \
    -H "Content-Type: application/json" \
    -d "{\"articleId\": \"$ARTICLE_ID\"}"
  echo "Bookmarked article"
fi

# 6. Verify bookmark
curl -s http://localhost:8000/api/v1/bookmarks | jq .

# 7. Cleanup
curl -s -X DELETE "http://localhost:8000/api/v1/feeds/$FEED_ID"
curl -s -X DELETE "http://localhost:8000/api/v1/categories/$CATEGORY_ID"
echo "Cleanup complete"
```

---

## Troubleshooting

### Container Issues
```bash
# View all logs
docker compose logs -f

# Restart specific service
docker compose restart app

# Rebuild from scratch
docker compose down -v
docker compose up -d --build
```

### Database Issues
```bash
# Reset database
docker compose exec app php bin/console doctrine:database:drop --force
docker compose exec app php bin/console doctrine:database:create
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

### Cache Issues
```bash
docker compose exec app php bin/console cache:clear
docker compose exec app php bin/console cache:warmup
```

### Messenger Issues
```bash
# Check failed messages
docker compose exec app php bin/console messenger:failed:show

# Retry failed messages
docker compose exec app php bin/console messenger:failed:retry
```

---

## Authentication for API Requests

All API endpoints (except `POST /api/v1/auth/login`) require JWT authentication.

### Obtain a JWT Token
```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@signalist.app", "password": "password"}' | jq -r '.token')
echo "Token: $TOKEN"
```

### Use the Token in Requests
```bash
curl -s http://localhost:8000/api/v1/categories \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/ld+json" | jq .
```

All `curl` examples in earlier sections of this guide require the `-H "Authorization: Bearer $TOKEN"` header to work.

---

## Behat Acceptance Tests

Behat tests cover the full HTTP flow for all API endpoints.

### Run All Behat Tests
```bash
docker compose exec app vendor/bin/behat --suite=api
```

### Run a Specific Feature
```bash
docker compose exec app vendor/bin/behat --suite=api features/api/login.feature
```

### Available Feature Files

| Feature | Scenarios | Covers |
|---------|-----------|--------|
| `features/api/login.feature` | 7 | Auth: success, bad credentials, validation |
| `features/api/categories.feature` | 7 | CRUD, validation, conflicts |
| `features/api/feeds.feature` | 6 | CRUD, validation, conflicts |
| `features/api/articles.feature` | 4 | List, get, mark read/unread |
| `features/api/bookmarks.feature` | 3 | Create, list, delete |

### Behat Setup Notes
- Test database is reset before each scenario (schema drop + recreate + migrations)
- Fixtures are loaded via `Given there are default users` step
- JWT auth is handled via `Given I am authenticated as "admin@signalist.app"` step
- Test users: `admin@signalist.app` / `password` and `user@signalist.app` / `password`

---

## Test Coverage

### Generate Coverage Report
```bash
make tests-coverage
```
This generates an HTML report in `var/coverage/` and prints a text summary.

Coverage is scoped to `src/Domain/` (business logic layer). Current coverage: ~93%.

### CI Coverage Threshold
GitHub Actions enforces a minimum 80% line coverage. The check parses clover XML output and fails the build if coverage drops below the threshold.

---

## GrumPHP Pre-Commit Hooks

GrumPHP runs automatically on every `git commit`. It executes:

1. **PHP CS Fixer** - Code style
2. **PHPStan** - Static analysis
3. **Rector** - Automated refactoring checks
4. **PHPUnit** - Unit test suite
5. **Behat** - API acceptance tests
6. **Commit message** - Conventional Commits format, 72-char line limit

### Run Manually
```bash
make grumphp
```

### Commit Message Rules
- Must follow Conventional Commits: `feat(scope): description`
- Subject line max 72 characters
- Body lines max 72 characters
- Blank line between subject and body

---

## Quick Verification Commands

```bash
# All-in-one health check
docker compose exec app php bin/console cache:clear && \
docker compose exec app vendor/bin/php-cs-fixer fix --dry-run && \
docker compose exec app vendor/bin/phpstan analyse && \
docker compose exec app vendor/bin/rector process --dry-run && \
docker compose exec app vendor/bin/phpunit --testsuite=Unit
```

**Expected:** All commands complete without errors.

Or use the Makefile shortcuts:
```bash
make quality        # lint + analyse + rector
make tests-unit     # PHPUnit unit tests
make grumphp        # All pre-commit checks
make tests-coverage # PHPUnit with coverage report
```
