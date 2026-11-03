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

  const control =
    'inline-flex size-10 items-center justify-center rounded-full border border-line bg-canvas text-ink hover:border-accent hover:text-accent';

  return (
    <section
      className="relative"
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
      {count > 1 && (
        <div className="mb-4 flex items-center gap-2">
          {!reducedMotion && (
            <button type="button" className={control} onClick={() => setPlaying((value) => !value)} aria-label={playing ? t.stop : t.start}>
              <Icon path={playing ? 'M8 5v14M16 5v14' : 'M7 5l12 7-12 7V5z'} />
            </button>
          )}
          <button type="button" className={control} onClick={() => go(index - 1)} aria-controls={id} aria-label={t.previous}>
            <Icon path="M15 6l-6 6 6 6" />
          </button>
          <button type="button" className={control} onClick={() => go(index + 1)} aria-controls={id} aria-label={t.next}>
            <Icon path="M9 6l6 6-6 6" />
          </button>
        </div>
      )}

      <div className="overflow-hidden rounded-card">
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
                className="relative w-full shrink-0 bg-ink text-canvas"
                role="group"
                aria-roledescription="slide"
                aria-label={format(t.slide, slideIndex + 1, count)}
                inert={slideIndex !== index}
              >
                {slide.image && imageSrc ? (
                  <img
                    className="aspect-video w-full object-cover opacity-70"
                    src={imageSrc}
                    srcSet={slide.image.srcset}
                    sizes="(min-width: 72rem) 72rem, 100vw"
                    width={slide.image.width}
                    height={slide.image.height}
                    alt={slide.image.alt}
                    loading={slideIndex === 0 ? 'eager' : 'lazy'}
                  />
                ) : (
                  <div className="aspect-video w-full" />
                )}
                <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink/90 to-transparent p-6 sm:p-10">
                  <h3 className="max-w-2xl text-2xl font-bold text-balance sm:text-3xl">{slide.title}</h3>
                  {slide.text && <p className="mt-2 hidden max-w-2xl opacity-90 sm:block">{slide.text}</p>}
                  {href && (
                    <a className="mt-4 inline-block font-semibold text-canvas underline underline-offset-4 hover:text-canvas" href={href}>
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
        <div className="mt-4 flex justify-center gap-1">
          {slides.map((slide, slideIndex) => (
            <button
              key={slideIndex}
              type="button"
              className="group inline-flex size-6 items-center justify-center"
              onClick={() => go(slideIndex)}
              aria-label={format(t.goTo, slideIndex + 1, slide.title)}
              aria-current={slideIndex === index ? 'true' : undefined}
            >
              <span className={`size-3 rounded-full border border-accent ${slideIndex === index ? 'bg-accent' : 'bg-canvas'}`} />
            </button>
          ))}
        </div>
      )}
    </section>
  );
}
