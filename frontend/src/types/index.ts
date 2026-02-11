// API Response Types

export interface Category {
  id: string;
  name: string;
  slug: string;
  description: string | null;
  color: string | null;
  position: number;
  createdAt: string;
  updatedAt: string;
}

export interface Feed {
  id: string;
  title: string;
  url: string;
  status: 'active' | 'paused' | 'error';
  lastError: string | null;
  lastFetchedAt: string | null;
  categoryId: string;
  categoryName: string;
  createdAt: string;
  updatedAt: string;
}

export interface Article {
  id: string;
  title: string;
  url: string;
  summary: string | null;
  content: string | null;
  author: string | null;
  imageUrl: string | null;
  isRead: boolean;
  publishedAt: string | null;
  createdAt: string;
  feedId: string;
  feedTitle: string;
  categoryId: string;
  categoryName: string;
}

export interface Bookmark {
  id: string;
  notes: string | null;
  createdAt: string;
  articleId: string;
  articleTitle: string;
  articleUrl: string;
  feedId: string;
  feedTitle: string;
  categoryId: string;
  categoryName: string;
}

// Input Types

export interface CreateCategoryInput {
  name: string;
  slug: string;
  description?: string;
  color?: string;
  position?: number;
}

export interface UpdateCategoryInput {
  name: string;
  slug: string;
  description?: string;
  color?: string;
  position?: number;
}

export interface AddFeedInput {
  url: string;
  categoryId: string;
  title?: string;
}

export interface UpdateFeedInput {
  title: string;
  categoryId: string;
  status: 'active' | 'paused' | 'error';
}

export interface CreateBookmarkInput {
  articleId: string;
  notes?: string;
}

// RFC 7807 Problem Details

export interface ProblemDetails {
  type: string;
  title: string;
  status: number;
  detail: string;
  instance?: string;
  errors?: Array<{
    field: string;
    message: string;
  }>;
}

// API Response wrapper for collections (plain JSON format from API Platform)
export interface ApiCollection<T> {
  member: T[];
  totalItems: number;
}
