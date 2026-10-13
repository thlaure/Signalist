export function shareLinks(title: string, url: string): { platform: string; url: string }[] {
  let source: URL;
  try { source = new URL(url); } catch { return []; }
  if (!['http:', 'https:'].includes(source.protocol) || source.username || source.password) return [];
  const text = `${title}\n${url}`;
  return [
    { platform: 'X', url: `https://twitter.com/intent/tweet?${new URLSearchParams({ text: title, url })}` },
    { platform: 'LinkedIn', url: `https://www.linkedin.com/sharing/share-offsite/?${new URLSearchParams({ url })}` },
    { platform: 'Threads', url: `https://www.threads.net/intent/post?${new URLSearchParams({ text })}` },
    { platform: 'Bluesky', url: `https://bsky.app/intent/compose?${new URLSearchParams({ text })}` },
    { platform: 'WhatsApp', url: `https://wa.me/?${new URLSearchParams({ text })}` },
  ];
}
