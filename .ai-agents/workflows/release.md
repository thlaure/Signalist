# Workflow: Release

Use this workflow when preparing and deploying a release.

---

## Overview

```
1. PREPARE    → Gather changes, update version
2. TEST       → Full test suite
3. CHANGELOG  → Document changes
4. TAG        → Create release tag
5. DEPLOY     → Deploy to environment
6. VERIFY     → Post-deployment checks
7. ANNOUNCE   → Communicate release
```

---

## Step 1: Prepare Release

### Determine Version

Follow **Semantic Versioning** (semver):
- **MAJOR** (X.0.0): Breaking changes
- **MINOR** (0.X.0): New features, backward compatible
- **PATCH** (0.0.X): Bug fixes, backward compatible

### Pre-Release Checklist

- [ ] All planned features merged
- [ ] All critical bugs fixed
- [ ] No blocking issues
- [ ] Dependencies up to date
- [ ] Documentation updated

### Create Release Branch (optional)
```bash
git checkout main
git pull
git checkout -b release/v1.2.0
```

---

## Step 2: Full Test Suite

### Run All Tests
```bash
# Backend
make lint
make analyse
make tests

# Frontend
cd frontend
npm run lint
npm run typecheck
npm test
npm run build
```

### Manual Testing Checklist

- [ ] Core features work
  - [ ] Add feed
  - [ ] View articles
  - [ ] Bookmark article
  - [ ] Search (semantic)
  - [ ] Generate newsletter
  - [ ] Spotlight commands

- [ ] Edge cases
  - [ ] Empty states
  - [ ] Error handling
  - [ ] Large data sets

- [ ] Cross-browser (if applicable)
  - [ ] Chrome
  - [ ] Firefox
  - [ ] Safari

### Performance Check
- [ ] API response times acceptable
- [ ] No memory leaks in workers
- [ ] Database queries optimized

---

## Step 3: Changelog

### Generate Changelog

List all changes since last release:
```bash
git log v1.1.0..HEAD --oneline
```

### Changelog Format
```markdown
# Changelog

## [1.2.0] - 2024-01-15

### Added
- Semantic search using pgvector (#123)
- Newsletter scheduling feature (#125)
- Spotlight command bar (#130)

### Changed
- Improved feed crawling performance (#128)
- Updated MUI to v5.15 (#132)

### Fixed
- Fixed newsletter word count calculation (#127)
- Fixed bookmark sync with Raindrop (#129)

### Security
- Updated dependencies for security patches (#131)

### Deprecated
- Legacy search endpoint (use /api/v1/search instead)

### Removed
- Removed deprecated /api/feeds endpoint
```

### Update CHANGELOG.md
Add new version section at the top.

---

## Step 4: Tag Release

### Update Version Files
```bash
# If using version file
echo "1.2.0" > VERSION

# Update composer.json if applicable
# Update package.json if applicable
```

### Commit Version Bump
```bash
git add .
git commit -m "chore(release): bump version to 1.2.0"
```

### Create Tag
```bash
git tag -a v1.2.0 -m "Release v1.2.0

Features:
- Semantic search
- Newsletter scheduling
- Spotlight command bar

See CHANGELOG.md for details."
```

### Push
```bash
git push origin main
git push origin v1.2.0
```

---

## Step 5: Deploy

### Deployment Checklist

#### Pre-Deployment
- [ ] Database backup taken
- [ ] Rollback plan documented
- [ ] Team notified of deployment window

#### Deployment Steps
```bash
# 1. Pull latest code
git checkout main
git pull

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
cd frontend && npm ci && npm run build

# 3. Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Clear caches
php bin/console cache:clear --env=prod

# 5. Restart workers
supervisorctl restart messenger-consume:*

# 6. Warm up cache (optional)
php bin/console cache:warmup --env=prod
```

#### Environment-Specific

**Staging**
```bash
# Deploy to staging first
./deploy.sh staging

# Test on staging
# Get approval
```

**Production**
```bash
# Deploy to production
./deploy.sh production
```

---

## Step 6: Post-Deployment Verification

### Health Checks
```bash
# Check health endpoint
curl https://app.example.com/health

# Check API
curl https://app.example.com/api/v1/feeds

# Check logs for errors
tail -f /var/log/app/prod.log | grep -i error
```

### Smoke Tests
- [ ] Homepage loads
- [ ] Login works
- [ ] Core API endpoints respond
- [ ] Workers processing messages
- [ ] Scheduled tasks running

### Monitoring
- [ ] Error rates normal
- [ ] Response times normal
- [ ] CPU/Memory normal
- [ ] Queue depth normal

---

## Step 7: Announce Release

### Internal Announcement
```markdown
## Release v1.2.0 Deployed

### Highlights
- Semantic search is now live
- Newsletter scheduling available
- New Spotlight command bar (Cmd+K)

### Breaking Changes
None

### Migration Notes
None

### Known Issues
- [Any known issues]

### Documentation
- [Link to updated docs]
```

### External Announcement (if public)
Coordinate with @marketer:
- Blog post
- Social media
- Email to users

---

## Rollback Procedure

If issues are found post-deployment:

### Immediate Rollback
```bash
# 1. Revert to previous version
git checkout v1.1.0

# 2. Deploy previous version
./deploy.sh production

# 3. Rollback migrations (if needed)
php bin/console doctrine:migrations:migrate prev --no-interaction

# 4. Restart services
supervisorctl restart all
```

### Post-Rollback
1. Communicate rollback to team
2. Investigate root cause
3. Fix issues
4. Plan re-deployment

---

## Release Cadence

### Recommended Schedule
- **Major releases**: Quarterly (or as needed for breaking changes)
- **Minor releases**: Monthly (new features)
- **Patch releases**: As needed (bug fixes)

### Hotfix Releases
For critical production bugs:
1. Create `hotfix/` branch
2. Fix and test
3. Merge to `main`
4. Tag as patch (e.g., v1.2.1)
5. Deploy immediately

---

## Release Artifacts

### What to Archive
- Git tag
- Docker images (tagged with version)
- CHANGELOG entry
- Deployment record

### Retention
- Keep last 10 releases deployable
- Archive older release notes
