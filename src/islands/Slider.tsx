import { useCallback, useEffect, useId, useState, type KeyboardEvent } from 'react';
import { format, safeUrl } from './utils';

type SlideImage = {
  src: string;
  srcset?: string;
  alt: string;
  width?: number;
  height?: number;
};

type Slide = {
  title: string;
  text?: string;
  url?: string;
  image?: SlideImage;
};

/** Strings come from PHP (translated with __()); these English defaults are a safety net. */
const defaultLabels = {
  region: 'Featured',
  start: 'Start automatic slide show',
  stop: 'Stop automatic slide show',
  previous: 'Previous slide',
  next: 'Next slide',
  slide: '%1$s of %2$s',
  goTo: 'Show slide %1$s: %2$s',
  readMore: 'Read more',
};

type SliderProps = {
  slides?: Slide[];
  interval?: number;
  autoplay?: boolean;
  labels?: Partial<typeof defaultLabels>;
};

function usePrefersReducedMotion(): boolean {
  const query = '(prefers-reduced-motion: reduce)';
  const [reduced, setReduced] = useState(() => window.matchMedia(query).matches);

  useEffect(() => {
    const media = window.matchMedia(query);
    const onChange = (): void => setReduced(media.matches);
    media.addEventListener('change', onChange);
    return () => media.removeEventListener('change', onChange);
  }, []);

  return reduced;
}

function Icon({ path }: { path: string }) {
  return (
    <svg className="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden="true">
      <path d={path} />
    </svg>
  );
}

/**
 * Carousel following the WAI-ARIA APG pattern: the rotation control comes
 * first, rotation stops on hover and focus, the live region is only polite
 * while not rotating, and hidden slides are inert so their links are not
 * focusable. With prefers-reduced-motion there is no autoplay or animation.
 */
export default function Slider({ slides = [], interval = 6000, autoplay = true, labels = {} }: SliderProps) {
  const t = { ...defaultLabels, ...labels };
  const id = useId();
  const reducedMotion = usePrefersReducedMotion();
  const [index, setIndex] = useState(0);
  const [playing, setPlaying] = useState(autoplay);
  const [paused, setPaused] = useState(false);

  const count = slides.length;
  const rotating = playing && !paused && !reducedMotion && count > 1;

  const go = useCallback((next: number) => setIndex(((next % count) + count) % count), [count]);

  useEffect(() => {
    if (!rotating) return undefined;
    const timer = window.setTimeout(() => go(index + 1), Math.max(interval, 3000));
    return () => window.clearTimeout(timer);
  }, [rotating, index, interval, go]);

  if (count === 0) return null;

  const onKeyDown = (event: KeyboardEvent<HTMLElement>): void => {
    if (event.key === 'ArrowLeft') go(index - 1);
    if (event.key === 'ArrowRight') go(index + 1);
  };

  // Round controls for the dark band: outlined, and the "next" arrow filled with the accent.
  const control =
    'inline-flex size-12 items-center justify-center rounded-full border-2 border-night-ink/40 text-night-ink transition-colors hover:border-night-ink hover:bg-night-ink hover:text-night';
  const primaryControl =
    'inline-flex size-12 items-center justify-center rounded-full border-2 border-accent bg-accent text-accent-ink transition-colors hover:border-accent-strong hover:bg-accent-strong';

  return (
    <section
      className="relative flex flex-col"
      aria-roledescription="carousel"
      aria-label={t.region}
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      onFocus={() => setPaused(true)}
      onBlur={(event) => {
        if (!event.currentTarget.contains(event.relatedTarget as Node | null)) setPaused(false);
      }}
      onKeyDown={onKeyDown}
    >
      {/* The controls come first in the DOM (APG) and are moved below the slides visually. */}
      {count > 1 && (
        <div className="order-last mt-8 flex items-center gap-3">
          {!reducedMotion && (
            <button type="button" className={control} onClick={() => setPlaying((value) => !value)} aria-label={playing ? t.stop : t.start}>
              <Icon path={playing ? 'M8 5v14M16 5v14' : 'M7 5l12 7-12 7V5z'} />
            </button>
          )}
          <div className="ml-auto flex gap-3">
            <button type="button" className={control} onClick={() => go(index - 1)} aria-controls={id} aria-label={t.previous}>
              <Icon path="M19 12H5M11 6l-6 6 6 6" />
            </button>
            <button type="button" className={primaryControl} onClick={() => go(index + 1)} aria-controls={id} aria-label={t.next}>
              <Icon path="M5 12h14M13 6l6 6-6 6" />
            </button>
          </div>
        </div>
      )}

      <div className="overflow-hidden rounded-card border border-night-ink/25">
        <div
          id={id}
          className={`flex ${reducedMotion ? '' : 'transition-transform duration-500 ease-out'}`}
          style={{ transform: `translateX(-${index * 100}%)` }}
          aria-live={rotating ? 'off' : 'polite'}
        >
          {slides.map((slide, slideIndex) => {
            const href = safeUrl(slide.url);
            const imageSrc = safeUrl(slide.image?.src);
            return (
              <div
                key={slideIndex}
                className="grid w-full shrink-0 bg-night/60 text-night-ink backdrop-blur-sm lg:grid-cols-[3fr_2fr]"
                role="group"
                aria-roledescription="slide"
                aria-label={format(t.slide, slideIndex + 1, count)}
                inert={slideIndex !== index}
              >
                {slide.image && imageSrc ? (
                  <img
                    className="aspect-video size-full object-cover lg:aspect-auto lg:min-h-[26rem]"
                    src={imageSrc}
                    srcSet={slide.image.srcset}
                    sizes="(min-width: 64rem) 44rem, 100vw"
                    width={slide.image.width}
                    height={slide.image.height}
                    alt={slide.image.alt}
                    loading={slideIndex === 0 ? 'eager' : 'lazy'}
                  />
                ) : (
                  <div className="aspect-video w-full bg-night-card lg:aspect-auto" />
                )}
                <div className="flex flex-col justify-center gap-4 p-6 sm:p-10">
                  <p className="font-display text-sm font-bold tracking-wider text-brand-bright uppercase">
                    {format(t.slide, slideIndex + 1, count)}
                  </p>
                  <h3 className="font-display text-2xl leading-tight font-bold text-balance sm:text-4xl">{slide.title}</h3>
                  {slide.text && <p className="text-night-ink/80">{slide.text}</p>}
                  {href && (
                    <a
                      className="mt-2 inline-flex self-start rounded-full border-2 border-night-ink/70 px-5 py-2.5 font-display font-bold text-night-ink no-underline transition-colors hover:border-night-ink hover:bg-night-ink hover:text-night"
                      href={href}
                    >
                      {t.readMore}
                      <span className="sr-only">: {slide.title}</span>
                    </a>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {count > 1 && (
        <div className="order-last -mt-12 flex justify-center gap-1 self-center">
          {slides.map((slide, slideIndex) => (
            <button
              key={slideIndex}
              type="button"
              className="inline-flex h-12 items-center justify-center px-1"
              onClick={() => go(slideIndex)}
              aria-label={format(t.goTo, slideIndex + 1, slide.title)}
              aria-current={slideIndex === index ? 'true' : undefined}
            >
              <span
                className={`block h-2.5 rounded-full transition-all ${slideIndex === index ? 'w-8 bg-brand-bright' : 'w-2.5 bg-night-ink/40'}`}
              />
            </button>
          ))}
        </div>
      )}
    </section>
  );
}
