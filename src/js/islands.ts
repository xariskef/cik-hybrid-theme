/**
 * Mounts React islands rendered by cik_hybrid_island().
 *
 * PHP enqueues this module only on pages that contain an island, so React is
 * never downloaded elsewhere. Each island component is its own chunk and is
 * fetched when its container gets close to the viewport.
 */
import { createElement, type ComponentType } from 'react';
import { createRoot } from 'react-dom/client';

type IslandProps = Record<string, unknown>;
type IslandModule = { default: ComponentType<IslandProps> };

const components = import.meta.glob<IslandModule>('../islands/*.tsx');

function readProps(element: HTMLElement): IslandProps {
  try {
    const parsed: unknown = JSON.parse(element.dataset.props ?? '{}');
    return parsed !== null && typeof parsed === 'object' && !Array.isArray(parsed) ? (parsed as IslandProps) : {};
  } catch {
    return {};
  }
}

async function mount(element: HTMLElement): Promise<void> {
  const name = element.dataset.island ?? '';
  const load = components[`../islands/${name}.tsx`];

  if (!load) {
    if (import.meta.env.DEV) console.warn(`[islands] No component found for "${name}".`);
    return;
  }

  const { default: Component } = await load();
  createRoot(element).render(createElement(Component, readProps(element)));
  element.removeAttribute('aria-busy');
  element.dataset.islandMounted = '';
}

const islands = Array.from(document.querySelectorAll<HTMLElement>('[data-island]'));

if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (!entry.isIntersecting) continue;
        observer.unobserve(entry.target);
        void mount(entry.target as HTMLElement);
      }
    },
    { rootMargin: '200px 0px' },
  );
  islands.forEach((island) => observer.observe(island));
} else {
  islands.forEach((island) => void mount(island));
}
