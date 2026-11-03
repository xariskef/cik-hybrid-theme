import { describe, expect, it } from 'vitest';
import { format, safeUrl } from '../../src/islands/utils';

describe('safeUrl', () => {
  it.each([
    ['https://example.test/post/', 'https://example.test/post/'],
    ['http://example.test', 'http://example.test/'],
    ['/relative/path?x=1', '/relative/path?x=1'],
    ['  /trimmed  ', '/trimmed'],
  ])('keeps %s', (input, expected) => {
    expect(safeUrl(input)).toBe(expected);
  });

  it.each([
    'javascript:alert(1)',
    ' JavaScript:alert(1)',
    'data:text/html;base64,PHNjcmlwdD4=',
    'vbscript:msgbox(1)',
    '//evil.test/path',
    'mailto:info@cikefallinos.com',
    'not a url',
    '',
  ])('rejects %j', (input) => {
    expect(safeUrl(input)).toBeUndefined();
  });

  it('rejects non-strings', () => {
    expect(safeUrl(undefined)).toBeUndefined();
    expect(safeUrl({ href: 'https://example.test' })).toBeUndefined();
  });
});

describe('format', () => {
  it('replaces numbered placeholders like sprintf', () => {
    expect(format('%2$s of %1$s', 5, 2)).toBe('2 of 5');
  });

  it('leaves placeholders without a value untouched', () => {
    expect(format('Slide %1$s: %2$s', 1)).toBe('Slide 1: %2$s');
  });
});
