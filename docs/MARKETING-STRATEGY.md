# Signalist Marketing Strategy

> Communication and pricing strategy synchronized with development phases.

---

## Table of Contents

1. [Pricing Strategy](#1-pricing-strategy)
2. [Phase-Aligned Communication](#2-phase-aligned-communication)
3. [Social Media Guidelines](#3-social-media-guidelines)
4. [Content Templates](#4-content-templates)
5. [Launch Checklist](#5-launch-checklist)

---

## 1. Pricing Strategy

### 1.1 Tier Structure (Freemium Model)

| Tier | Price | Target Audience | Key Features |
|------|-------|-----------------|--------------|
| **Free** | $0 | Curious individuals, evaluators | 5 feeds, 50 articles/month, basic search, 1 category |
| **Pro** | $12/month | Knowledge workers, researchers | 50 feeds, unlimited articles, AI summaries, semantic search, unlimited categories, bookmarks |
| **Team** | $29/user/month | Small businesses, agencies | Everything in Pro + shared feeds, team bookmarks, collaborative categories, priority support, admin dashboard |

**Annual discount:** 2 months free (17% off)
- Pro Annual: $120/year (vs $144 monthly)
- Team Annual: $290/user/year (vs $348 monthly)

### 1.2 Feature Comparison Matrix

| Feature | Free | Pro | Team |
|---------|:----:|:---:|:----:|
| RSS Feeds | 5 | 50 | Unlimited |
| Articles/month | 50 | Unlimited | Unlimited |
| Categories | 1 | Unlimited | Unlimited + Shared |
| Basic search | Yes | Yes | Yes |
| **AI Summaries** | - | Yes | Yes |
| **Semantic search** | - | Yes | Yes |
| **Auto-tagging** | - | Yes | Yes |
| Bookmarks | 10 | Unlimited | Unlimited + Shared |
| **Newsletter generation** | - | Yes | Yes |
| Raindrop.io sync | - | Yes | Yes |
| **MCP API access** | - | - | Yes |
| Spotlight commands | Basic | Full | Full |
| Support | Community | Email | Priority |
| Data export | - | Yes | Yes |

### 1.3 Pricing Psychology

- **Free tier:** Generous enough to be useful, limited enough to create upgrade desire
- **Pro tier:** Sweet spot for individual power users ($12 = less than 2 coffees)
- **Team tier:** Per-user pricing scales with value, not punitive for small teams
- **Annual discount:** Strong incentive (2 months free) without devaluing monthly

### 1.4 Upgrade Triggers

| From | To | Trigger |
|------|----|---------|
| Free | Pro | Hits 5 feed limit, wants AI features |
| Pro | Team | Needs to share feeds with colleagues |

---

## 2. Phase-Aligned Communication

### 2.1 Phase 1: MVP (Current)

**Development focus:** Core RSS engine, PostgreSQL schema, Dashboard UI

**Communication goal:** Build early adopter list, validate positioning

**Key message:** "Finally, an RSS reader that respects your intelligence."

| Week | Action | Channel | Content Type |
|------|--------|---------|--------------|
| Pre-launch | Building in public updates | Twitter/X | Thread |
| Pre-launch | Landing page live | Website | Page |
| Launch | "RSS is back" thought piece | Blog + LinkedIn | Article |
| Week 1 | Launch announcement | All channels | Announcement |
| Week 2 | Tutorial: First intelligence feed | Blog | How-to |
| Week 3 | Personal use case story | Twitter | Thread |
| Week 4 | User feedback roundup | Twitter + Email | Engagement |

**What to emphasize:**
- Clean, fast interface
- No algorithmic manipulation
- You own your feed
- Privacy-first approach

**What NOT to mention yet:**
- AI features (not built)
- Team features (not built)
- MCP integration (not built)

---

### 2.2 Phase 2: AI Layer

**Development focus:** Symfony AI for summaries and auto-tagging

**Communication goal:** Differentiate from basic RSS readers

**Key message:** "RSS Intelligence for the AI Era"

| Week | Action | Channel | Content Type |
|------|--------|---------|--------------|
| Feature drop | AI summaries announcement | All channels | Announcement |
| Week 1 | Demo video: AI in action | Twitter, LinkedIn | Video |
| Week 2 | Tutorial: Auto-tagging workflow | Blog | How-to |
| Week 3 | "How we built AI summaries" | Blog, HN | Technical |
| Week 4 | User testimonials | Social | Social proof |

**What to emphasize:**
- Time saved (quantify: "5 min summaries of 50 articles")
- AI assists, doesn't replace your judgment
- Multiple LLM providers (OpenAI, Anthropic, Mistral)
- Your data stays yours

---

### 2.3 Phase 3: Automation

**Development focus:** Newsletter scheduler, Raindrop.io sync

**Communication goal:** Convert Pro users, attract content creators

**Key message:** "Your newsletter, written in minutes."

| Week | Action | Channel | Content Type |
|------|--------|---------|--------------|
| Feature drop | Newsletter generator launch | Email + All | Announcement |
| Week 1 | "Newsletter in 5 minutes" tutorial | Blog + Video | How-to |
| Week 2 | Partner testimonials | Twitter | Social proof |
| Week 3 | Raindrop.io integration guide | Blog | Tutorial |
| Week 4 | Showcase user newsletters | Social | Community |

**What to emphasize:**
- Reading time calculations (200 wpm)
- Customizable scheduling
- Category-based organization
- One-click sharing

---

### 2.4 Phase 4: Ecosystem

**Development focus:** Spotlight command engine, MCP server

**Communication goal:** Developer audience, enterprise interest

**Key message:** "Your intelligence layer. MCP-native, API-ready."

| Week | Action | Channel | Content Type |
|------|--------|---------|--------------|
| Feature drop | MCP server announcement | HN, Dev.to, Twitter | Announcement |
| Week 1 | Technical deep-dive | Blog | Technical |
| Week 2 | Integration examples | GitHub + Blog | Tutorial |
| Week 3 | Spotlight command showcase | Twitter, YouTube | Demo |
| Week 4 | Developer community launch | Discord | Community |

**What to emphasize:**
- MCP protocol compliance
- pgvector semantic search
- API documentation
- Integration possibilities with Claude, GPT, etc.

---

## 3. Social Media Guidelines

### 3.1 Platform Strategy Overview

| Platform | Audience | Tone | Frequency | Best For |
|----------|----------|------|-----------|----------|
| **Twitter/X** | Tech-savvy, developers, indie hackers | Casual, direct, technical | 3-5/week | Threads, quick tips, engagement |
| **LinkedIn** | Professionals, decision-makers | Professional, insightful | 2-3/week | Thought leadership, announcements |
| **Hacker News** | Developers, technical audience | Technical, no-BS | Major launches only | Launch posts, technical articles |
| **Dev.to** | Developers | Educational, helpful | 1-2/month | Tutorials, technical deep-dives |

---

### 3.2 Twitter/X Guidelines

#### What Works

- **Threads:** 5-10 tweets telling a story or explaining a concept
- **Hot takes:** Contrarian but defensible opinions about RSS, AI, information
- **Building in public:** Share progress, challenges, decisions
- **Quick tips:** One actionable insight per tweet
- **Engagement:** Reply to others in the RSS/productivity/AI space

#### Post Types to Rotate

1. **Progress update** (1-2x/week)
   ```
   Shipped this week:
   - Feature X
   - Improvement Y
   - Bug fix Z

   Next up: [teaser]

   [Screenshot]
   ```

2. **Insight/Opinion** (1-2x/week)
   ```
   Unpopular opinion: [contrarian take]

   Here's why: [reasoning]

   [Optional: what you're doing differently]
   ```

3. **Tutorial thread** (1x/week)
   ```
   How I [achieve outcome] in [time]:

   1/ [Step 1]
   2/ [Step 2]
   ...

   Thread
   ```

4. **Engagement bait** (sparingly)
   ```
   What's the one RSS feed you can't live without?

   I'll start: [your answer]
   ```

#### What to Avoid

- Excessive self-promotion without value
- Vague "exciting news coming soon" posts
- Threads that could be one tweet
- Engagement farming without substance
- Negativity about competitors

#### Hashtags

Use sparingly (0-2 per post):
- `#buildinpublic` (for progress updates)
- `#indiehackers` (for founder content)
- Avoid: `#AI` `#SaaS` (too generic, spammy)

---

### 3.3 LinkedIn Guidelines

#### What Works

- **Thought leadership:** Industry insights, trends analysis
- **Personal stories:** Challenges faced, lessons learned
- **Data-driven posts:** Statistics, research, results
- **Professional announcements:** Launches, milestones
- **Carousel posts:** Step-by-step guides, comparisons

#### Post Structure

```
[Hook - First line is critical, make it count]

[Blank line]

[Body - 3-5 short paragraphs or bullet points]

[Blank line]

[Call to action or question]

[Blank line]

[Optional: 3-5 relevant hashtags]
```

#### Example Post

```
Information overload isn't a time management problem.

It's a filtering problem.

The average knowledge worker:
- Subscribes to 15+ newsletters
- Follows 200+ accounts
- Reads 0 RSS feeds

Yet RSS gives you:
- No algorithm
- No ads
- Complete control

The tools changed. The principle didn't.

What's your information diet look like?

#ProductivityTips #KnowledgeManagement
```

#### What to Avoid

- Humble bragging
- "I'm thrilled to announce..." openings
- Walls of text without formatting
- Pure product promotion
- Excessive hashtags (max 5)

---

### 3.4 Content Pillars for Social

Rotate between these themes:

| Pillar | % of Content | Example Topics |
|--------|--------------|----------------|
| **Educational** | 40% | How-tos, tips, tutorials |
| **Thought leadership** | 25% | Industry insights, opinions, trends |
| **Product updates** | 20% | Features, improvements, roadmap |
| **Community** | 15% | User stories, engagement, behind-scenes |

---

### 3.5 Engagement Rules

1. **Reply to every comment** in first 2 hours (algorithm boost)
2. **Ask questions** to encourage discussion
3. **Tag relevant people** only when genuinely relevant
4. **Share others' content** with your take (not just retweets)
5. **Be consistent** - same time slots each day

---

### 3.6 Visual Guidelines

- **Screenshots:** Clean, focused on one feature
- **Dimensions:**
  - Twitter: 1200x675px (16:9) or 1080x1080px (square)
  - LinkedIn: 1200x627px or 1080x1080px
- **Style:** Light mode for readability, minimal UI chrome
- **Text on images:** Large, readable, minimal words

---

## 4. Content Templates

### 4.1 Launch Announcement (Twitter Thread)

```
1/ Introducing Signalist: RSS intelligence for the AI era.

After mass exodus from Twitter algorithms and newsletter fatigue, I built what I wanted: a reader that respects my attention.

Here's what makes it different:

2/ The problem:
- Newsletters pile up unread
- Social algorithms show you what THEY want
- Google Reader died 10 years ago
- Modern RSS readers feel like 2010

3/ Signalist is RSS rebuilt for 2024:

 Clean, fast interface
 AI-powered summaries (optional)
 Semantic search across all your feeds
 No tracking, no algorithms

4/ Free tier includes:
- 5 feeds
- 50 articles/month
- Basic search

Enough to try it properly.

5/ Pro ($12/mo) unlocks:
- 50 feeds
- Unlimited articles
- AI summaries
- Semantic search
- Auto-tagging

6/ Try it free: [link]

I'm building this in public. Follow along for updates, and tell me what features you need.

What RSS feeds would you add first?
```

### 4.2 Feature Announcement (Twitter)

```
New in Signalist: AI-powered summaries

Get the key points from 50 articles in 5 minutes.

How it works:
1. Select articles or entire category
2. Choose summary length
3. AI extracts what matters

Your reading time. Your choice.

Try it: [link]

[GIF showing feature]
```

### 4.3 Thought Leadership (LinkedIn)

```
RSS isn't dead. Your attention just got hijacked.

10 years ago, Google killed Reader.
We moved to Twitter, newsletters, algorithmic feeds.

Now:
- Twitter is chaos
- Newsletters are unread
- TikTok optimizes for addiction

RSS never had these problems because:
- YOU choose the sources
- YOU set the frequency
- No one profits from your attention

I'm building Signalist to bring this back, with modern AI features that help (not manipulate).

What if your information diet was 100% intentional?

#InformationDiet #Productivity #RSS
```

### 4.4 Building in Public (Twitter)

```
Week 4 building Signalist:

Shipped:
- Semantic search (pgvector)
- Feed categorization
- Dark mode (finally)

Learned:
- pgvector indexing is tricky at scale
- Users want OPML import (adding next)

Next week:
- AI summary MVP
- Mobile responsive fixes

What should I prioritize?
```

### 4.5 Tutorial Thread (Twitter)

```
1/ How to build a personal intelligence system in 15 minutes:

You'll never miss important news in your field again.

Thread:

2/ Step 1: Identify your 5 core topics

Mine:
- AI/ML developments
- PHP/Symfony ecosystem
- Indie hacking
- Productivity
- Tech business

3/ Step 2: Find quality RSS sources

Best discovery methods:
- Check blogs you already read (most have RSS)
- Search "[topic] RSS feeds"
- Use Feedly's discovery
- Ask Twitter

4/ Step 3: Organize by priority

I use 3 tiers:
- Daily: Must-read sources (5 feeds)
- Weekly: Good but not urgent (15 feeds)
- Archive: Reference material (10 feeds)

5/ Step 4: Set a reading ritual

Mine: 20 min with morning coffee
- Scan Daily feeds
- Star anything interesting
- Deep read 2-3 articles

6/ Step 5: Review and prune monthly

Ask: Did I read this last month?
No? Unsubscribe.

Your feed should spark curiosity, not guilt.

7/ Tools I use:
- Signalist for aggregation [link]
- Readwise for highlights
- Notion for notes

What's your system? Reply with your setup.
```

---

## 5. Launch Checklist

### 5.1 Pre-Launch (2 weeks before)

**Website:**
- [ ] Landing page live with clear value proposition
- [ ] Email capture working (waitlist)
- [ ] Pricing page ready
- [ ] Basic SEO (title, meta, OG images)

**Content:**
- [ ] Launch blog post drafted
- [ ] Twitter thread drafted
- [ ] LinkedIn post drafted
- [ ] 3 "building in public" posts scheduled

**Product:**
- [ ] Free tier functional
- [ ] Onboarding flow smooth
- [ ] Critical bugs fixed
- [ ] Analytics in place

**Community:**
- [ ] Identify 10-20 people to notify personally
- [ ] Prepare Product Hunt listing (don't submit yet)
- [ ] Draft Hacker News "Show HN" post

### 5.2 Launch Day

**Morning:**
- [ ] Final product check
- [ ] Publish blog post
- [ ] Post Twitter thread
- [ ] Post LinkedIn announcement
- [ ] Email waitlist
- [ ] Submit to Product Hunt
- [ ] Submit to Hacker News

**Throughout day:**
- [ ] Respond to all comments within 2 hours
- [ ] Monitor for bugs/issues
- [ ] Share user feedback/testimonials
- [ ] Thank early supporters publicly

### 5.3 Post-Launch (Week 1)

- [ ] Daily social engagement
- [ ] Collect and share testimonials
- [ ] Address common questions in content
- [ ] Publish tutorial/how-to content
- [ ] Email non-converting signups
- [ ] Retrospective: what worked?

---

## 6. Metrics to Track

### 6.1 Awareness Metrics

| Metric | Tool | Target (Month 1) |
|--------|------|------------------|
| Website visitors | Analytics | 1,000 |
| Twitter followers | Twitter | +200 |
| LinkedIn connections | LinkedIn | +100 |
| Email subscribers | Email tool | 300 |

### 6.2 Conversion Metrics

| Metric | Target |
|--------|--------|
| Visitor  Signup | 5% |
| Free  Pro | 3% |
| Monthly churn | <5% |

### 6.3 Engagement Metrics

| Metric | Target |
|--------|--------|
| Twitter engagement rate | >2% |
| Email open rate | >40% |
| Email click rate | >5% |

---

## 7. Competitive Positioning

### 7.1 How We're Different

| Competitor | Their Focus | Our Differentiation |
|------------|-------------|---------------------|
| Feedly | Enterprise, teams | AI-native, simpler, cheaper |
| Inoreader | Power users, filters | Semantic search, modern UI |
| NewsBlur | Open source, privacy | AI features, polish |
| Matter | Read-later, newsletters | RSS-first, not newsletter-first |

### 7.2 Positioning Statement

> For knowledge workers who need to stay informed, Signalist is an RSS intelligence platform that uses AI to filter signal from noise. Unlike traditional RSS readers, Signalist offers semantic search and AI-powered summaries while respecting your attention and privacy.

---

## 8. Key Messages by Audience

### 8.1 Knowledge Workers

**Pain:** Information overload, missing important signals

**Message:** "Never miss what matters. AI filters the noise so you don't have to."

**Proof points:**
- AI summaries save X hours/week
- Semantic search finds related content
- No algorithmic manipulation

### 8.2 Developers

**Pain:** Need clean data sources for RAG, LLM applications

**Message:** "Your intelligence layer. MCP-native, pgvector-ready."

**Proof points:**
- Full API access
- MCP server for LLM integration
- pgvector for semantic search
- Self-hostable (future)

### 8.3 Content Curators

**Pain:** Manual curation is time-consuming

**Message:** "AI-assisted newsletters in minutes, not hours."

**Proof points:**
- One-click newsletter generation
- Customizable reading time
- Category-based organization

---

*Last updated: January 2025*
*Review quarterly and update based on learnings.*
