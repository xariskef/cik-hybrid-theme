import { vi } from 'vitest';

/**
 * jsdom has no matchMedia. Every query is false unless a test passes the
 * queries that should match, e.g. mockMatchMedia(['(prefers-reduced-motion: reduce)']).
 */
export function mockMatchMedia(matching: string[] = []): void {
  vi.stubGlobal(
    'matchMedia',
    vi.fn((query: string) => ({
      matches: matching.includes(query),
      media: query,
      onchange: null,
      addEventListener: vi.fn(),
      removeEventListener: vi.fn(),
      addListener: vi.fn(),
      removeListener: vi.fn(),
      dispatchEvent: vi.fn(() => false),
    })),
  );
}
