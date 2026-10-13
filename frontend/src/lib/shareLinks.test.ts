import { describe, it, expect } from 'vitest';
import { shareLinks } from './shareLinks';
describe('shareLinks', () => {
  it('preserves source query parameters and unicode without injecting intent parameters', () => {
    const source = 'https://example.com/a?x=1&y=2';
    const title = 'Économie & hashtags #news';
    const links = shareLinks(title, source);
    expect(links).toHaveLength(5);
    expect(new URL(links[0].url).searchParams.get('url')).toBe(source);
    expect(new URL(links[0].url).searchParams.get('text')).toBe(title);
    expect(new URL(links[3].url).searchParams.get('text')).toBe(`${title}\n${source}`);
  });
  it('refuses executable URLs and embedded credentials', () => {
    expect(shareLinks('Title', 'javascript:alert(1)')).toEqual([]);
    expect(shareLinks('Title', 'https://user:password@example.com/a')).toEqual([]);
  });
});
