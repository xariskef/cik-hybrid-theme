/**
 * Returns the URL only if it is relative to this site or uses http(s).
 * PHP already filters URLs with esc_url_raw(); this guards islands that receive
 * props from other sources, and blocks javascript:, data: and similar schemes.
 */
export function safeUrl(value: unknown): string | undefined {
  if (typeof value !== 'string' || value.trim() === '') return undefined;
  const url = value.trim();

  if (url.startsWith('/') && !url.startsWith('//')) return url;

  try {
    const parsed = new URL(url);
    return parsed.protocol === 'http:' || parsed.protocol === 'https:' ? parsed.href : undefined;
  } catch {
    return undefined;
  }
}

/** Replaces numbered placeholders (%1$s, %2$s, ...) the way PHP's sprintf() does. */
export function format(template: string, ...values: Array<string | number>): string {
  return template.replace(/%(\d+)\$[sd]/g, (match, position: string) => {
    const value = values[Number(position) - 1];
    return value === undefined ? match : String(value);
  });
}
