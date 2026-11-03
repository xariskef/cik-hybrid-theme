import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mockMatchMedia } from './helpers';

const tabsProps = JSON.stringify({
  label: 'Topics',
  tabs: [
    { label: 'News', items: [{ title: 'First post', url: '/first/' }] },
    { label: 'Guides', items: [] },
  ],
});

function island(name: string, props: string): HTMLElement {
  const element = document.createElement('div');
  element.className = 'cik-island';
  element.dataset.island = name;
  element.dataset.props = props;
  element.setAttribute('aria-busy', 'true');
  element.innerHTML = '<p role="status">Loading…</p>';
  document.body.append(element);
  return element;
}

const mounted = (element: HTMLElement): boolean => element.hasAttribute('data-island-mounted');

// The loader runs on import, so each test imports a fresh copy after building the DOM.
async function runLoader(): Promise<void> {
  vi.resetModules();
  await import('../../src/js/islands');
}

beforeEach(() => {
  mockMatchMedia();
});

afterEach(() => {
  document.body.innerHTML = '';
});

describe('islands loader', () => {
  it('mounts the component named in data-island with the decoded props', async () => {
    const element = island('Tabs', tabsProps);

    await runLoader();

    await vi.waitFor(() => expect(mounted(element)).toBe(true));
    expect(element.querySelector('[role="tablist"]')).toHaveAttribute('aria-label', 'Topics');
    expect(element.querySelectorAll('[role="tab"]')).toHaveLength(2);
    expect(element).not.toHaveAttribute('aria-busy');
  });

  it('leaves unknown islands alone and keeps their fallback', async () => {
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => undefined);
    const unknown = island('DoesNotExist', '{}');
    const known = island('Tabs', tabsProps);

    await runLoader();

    await vi.waitFor(() => expect(mounted(known)).toBe(true));
    expect(mounted(unknown)).toBe(false);
    expect(unknown).toHaveAttribute('aria-busy', 'true');
    expect(unknown).toHaveTextContent('Loading…');
    expect(warn).toHaveBeenCalledWith(expect.stringContaining('DoesNotExist'));
  });

  it.each([
    ['invalid JSON', '{not json'],
    ['a JSON array', '[1, 2]'],
    ['JSON null', 'null'],
  ])('mounts with empty props when data-props is %s', async (_label, props) => {
    const element = island('Tabs', props);

    await runLoader();

    // Tabs without tabs renders nothing, but the mount itself must not throw.
    await vi.waitFor(() => expect(mounted(element)).toBe(true));
    expect(element).toBeEmptyDOMElement();
  });

  it('waits for islands to approach the viewport when IntersectionObserver exists', async () => {
    let notify: IntersectionObserverCallback = () => undefined;
    const observe = vi.fn();
    const unobserve = vi.fn();
    vi.stubGlobal(
      'IntersectionObserver',
      class {
        constructor(callback: IntersectionObserverCallback) {
          notify = callback;
        }
        observe = observe;
        unobserve = unobserve;
        disconnect = vi.fn();
      },
    );

    const near = island('Tabs', tabsProps);
    const far = island('Tabs', tabsProps);

    await runLoader();
    expect(observe).toHaveBeenCalledTimes(2);

    notify(
      [
        { target: near, isIntersecting: true },
        { target: far, isIntersecting: false },
      ] as unknown as IntersectionObserverEntry[],
      {} as IntersectionObserver,
    );

    await vi.waitFor(() => expect(mounted(near)).toBe(true));
    expect(unobserve).toHaveBeenCalledWith(near);
    expect(mounted(far)).toBe(false);
  });
});
