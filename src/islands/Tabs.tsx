import { useId, useRef, useState, type KeyboardEvent } from 'react';
import { safeUrl } from './utils';

type TabItem = {
  title: string;
  url?: string;
  meta?: string;
};

type Tab = {
  label: string;
  items: TabItem[];
  moreUrl?: string;
  moreLabel?: string;
};

type TabsProps = {
  tabs?: Tab[];
  label?: string;
};

/**
 * Tabs following the WAI-ARIA APG pattern: roving tabindex, arrow keys and
 * Home/End move between tabs and activate them immediately.
 */
export default function Tabs({ tabs = [], label }: TabsProps) {
  const baseId = useId();
  const [active, setActive] = useState(0);
  const tabRefs = useRef<Array<HTMLButtonElement | null>>([]);

  if (tabs.length === 0) return null;

  const select = (index: number): void => {
    const next = (index + tabs.length) % tabs.length;
    setActive(next);
    tabRefs.current[next]?.focus();
  };

  const onKeyDown = (event: KeyboardEvent<HTMLButtonElement>): void => {
    const keys: Record<string, number> = {
      ArrowRight: active + 1,
      ArrowLeft: active - 1,
      Home: 0,
      End: tabs.length - 1,
    };
    const target = keys[event.key];
    if (target === undefined) return;
    event.preventDefault();
    select(target);
  };

  return (
    <div>
      <div role="tablist" aria-label={label} className="flex flex-wrap gap-2 border-b border-line">
        {tabs.map((tab, index) => (
          <button
            key={index}
            ref={(element) => {
              tabRefs.current[index] = element;
            }}
            id={`${baseId}-tab-${index}`}
            type="button"
            role="tab"
            aria-selected={index === active}
            aria-controls={`${baseId}-panel-${index}`}
            tabIndex={index === active ? 0 : -1}
            onClick={() => setActive(index)}
            onKeyDown={onKeyDown}
            className={`-mb-px border-b-2 px-4 py-3 font-semibold ${
              index === active ? 'border-accent text-accent' : 'border-transparent text-muted hover:text-ink'
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {tabs.map((tab, index) => {
        const moreUrl = safeUrl(tab.moreUrl);
        return (
          <div
            key={index}
            id={`${baseId}-panel-${index}`}
            role="tabpanel"
            aria-labelledby={`${baseId}-tab-${index}`}
            tabIndex={0}
            hidden={index !== active}
            className="py-6"
          >
            <ul className="divide-y divide-line">
              {tab.items.map((item, itemIndex) => {
                const href = safeUrl(item.url);
                return (
                  <li key={itemIndex} className="flex flex-wrap items-baseline justify-between gap-2 py-3">
                    {href ? (
                      <a className="font-medium text-ink no-underline hover:text-accent" href={href}>
                        {item.title}
                      </a>
                    ) : (
                      <span className="font-medium">{item.title}</span>
                    )}
                    {item.meta && <span className="text-sm text-muted">{item.meta}</span>}
                  </li>
                );
              })}
            </ul>
            {moreUrl && tab.moreLabel && (
              <a className="mt-4 inline-block font-semibold" href={moreUrl}>
                {tab.moreLabel}
              </a>
            )}
          </div>
        );
      })}
    </div>
  );
}
