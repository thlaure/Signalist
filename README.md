# Signalist

Here is the technical and functional specification document for **Signalist**.

---

# Technical Specifications: Signalist

## 1. Project Overview

**Signalist** is a smart intelligence platform (SaaS) designed to aggregate, filter, and synthesize RSS feeds using Artificial Intelligence. The application features a natural language command interface (like Spotlight on macOS) and exposes its data via the Model Context Protocol (MCP) to integrate with the broader LLM ecosystem.

## 2. Functional Requirements

### 2.1 Feed Management (RSS)

* **Categorization:** Create, update, and delete thematic categories.
* **Aggregation:** Link one or multiple RSS feeds to a specific category.
* **Content Extraction:** Use libraries (e.g., Readability) to fetch full article content for AI processing, bypassing partial RSS snippets.

### 2.2 Navigation & Filtering

* **Global Dashboard:** A chronological feed of all incoming articles.
* **Categorized Views:** Filter content by category, specific source, or date range.
* **Advanced Search:** Support for both full-text search and semantic search (Vector Search).

### 2.3 Bookmarking & Tagging

* **Storage:** Save articles to a "Bookmarks" section.
* **Intelligent Tagging:** * Automatic tag generation via LLM based on content analysis.
* Manual tag management.


* **Raindrop.io Integration:** Synchronize bookmarks and tags with the Raindrop.io API.

### 2.4 AI-Powered Newsletters

* **Dynamic Generation:** Use LLMs (via Symfony AI) to synthesize summaries of unread articles.
* **Newsletter Structure:** Grouped by category, including clickable titles and concise summaries.
* **Reading Time Calibration:** * Default duration: 5 minutes.
* Customizable duration: Logic based on a **200 words-per-minute** ratio.


* **Scheduling:** Automated delivery at set intervals (daily, weekly, custom) using Symfony Scheduler.
* **Manual Composition:** Interface to manually select articles and send to a custom mailing list.

### 2.5 Social Sharing

* Direct sharing integration for WhatsApp, X (Twitter), LinkedIn, Threads, Bluesky.

---

## 3. Interface & User Experience (UX)

### 3.1 Layout

* **Dashboard:** Central feed focused on readability and minimalism.
* **Sidepanel:** Left-aligned navigation for categories, bookmarks, and settings.
* **Visual Style:** Minimalist UI, high-contrast typography, native dark mode support.

### 3.2 "Spotlight" Command Center

* Global search/command bar (accessible via `Cmd+K`).
* **Natural Language Processing (NLP):** Map user intent to backend actions (e.g., "Add [https://example.com/feed](https://example.com/feed) to Dev category").
* **Direct Execution:** Perform CRUD operations and AI queries directly from the command bar.

---

## 4. Technical Stack

### 4.1 Backend

* **Framework:** Symfony 8.x (utilizing PHP 8.5 features).
* **Frontend:** React with TypeScript, Vite, and MUI for component styling.
* **Architecture:** CQRS, Clean Architecture, SOLID principles, Hexagonal architecture for maintainability and testability.
* **Testing:** PHPUnit for backend (unit and integration + code coverage UI, test what it needs to be tested, 100% coverage), Jest + React Testing Library for frontend.
* **Database:** PostgreSQL with **pgvector** extension for storing and querying embeddings.
* **Queue Management:** Symfony Messenger + Redis for background RSS crawling and LLM processing.

### 4.2 AI & Interoperability

* **Symfony AI:** Core component for LLM abstraction (OpenAI, Anthropic, Mistral).
* **AI Agents:** Implementation of `#[AsTool]` attributes to connect the Spotlight interface to PHP services.
* **MCP (Model Context Protocol):** Implementation of a native MCP server to allow external LLMs to query the user's Signalist data securely.

### 4.3 Third-Party APIs

* **Raindrop.io:** OAuth2 authentication for bookmark synchronization.
* **Emailing:** Symfony Mailer

---

## 5. Development Constraints & Accuracy

* **Factual Integrity:** All AI-generated summaries must provide a direct link to the original source to prevent misinformation.
* **Performance:** RSS parsing and vector generation must be handled asynchronously to maintain UI responsiveness.
* **Scalability:** The architecture must support the "Bring Your Own Key" (BYOK) model for LLM API costs, but if the user does not provide a key, the system should default to a shared organizational key with usage limits.

---

## 6. Project Roadmap

1. **Phase 1 (MVP):** Core RSS engine, PostgreSQL schema, and basic Dashboard UI.
2. **Phase 2 (AI Layer):** Symfony AI integration for summaries and automated tagging.
3. **Phase 3 (Automation):** Newsletter scheduler and Raindrop.io sync.
4. **Phase 4 (Ecosystem):** Spotlight command engine and MCP server implementation.

---

Would you like me to generate the **PostgreSQL schema** (SQL) specifically optimized for the `pgvector` implementation and article metadata?