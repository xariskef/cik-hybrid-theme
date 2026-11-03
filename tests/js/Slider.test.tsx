import { act, fireEvent, render, screen } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import Slider from '../../src/islands/Slider';
import { mockMatchMedia } from './helpers';

const slides = [
  { title: 'One', url: 'https://example.test/one/' },
  { title: 'Two', url: '/two/' },
  { title: 'Three', url: 'javascript:alert(1)' },
  { title: 'Four', url: 'data:text/html,<script>alert(1)</script>' },
  { title: 'Five', url: '//evil.test/five/' },
];

const groups = (): HTMLElement[] => screen.getAllByRole('group', { hidden: true });
const activeTitle = (): string | null | undefined =>
  groups().find((group) => !group.hasAttribute('inert'))?.querySelector('h3')?.textContent;

beforeEach(() => {
  vi.useFakeTimers();
  mockMatchMedia();
});

afterEach(() => {
  vi.useRealTimers();
});

describe('Slider', () => {
  it('only renders links for relative and http(s) URLs', () => {
    const { container } = render(<Slider slides={slides} autoplay={false} />);

    const hrefs = Array.from(container.querySelectorAll('a')).map((link) => link.getAttribute('href'));
    expect(hrefs).toEqual(['https://example.test/one/', '/two/']);
  });

  it('drops images with an unsafe src', () => {
    const { container } = render(
      <Slider slides={[{ title: 'One', image: { src: 'javascript:alert(1)', alt: '' } }]} autoplay={false} />,
    );

    expect(container.querySelector('img')).toBeNull();
  });

  it('makes every slide but the current one inert', () => {
    render(<Slider slides={slides.slice(0, 3)} autoplay={false} />);

    expect(groups().map((group) => group.hasAttribute('inert'))).toEqual([false, true, true]);

    fireEvent.click(screen.getByRole('button', { name: 'Next slide' }));
    expect(groups().map((group) => group.hasAttribute('inert'))).toEqual([true, false, true]);

    fireEvent.click(screen.getByRole('button', { name: 'Previous slide' }));
    fireEvent.click(screen.getByRole('button', { name: 'Previous slide' }));
    expect(activeTitle()).toBe('Three');
  });

  it('rotates automatically until the stop button is pressed', () => {
    render(<Slider slides={slides.slice(0, 3)} interval={4000} />);
    const live = groups()[0]?.parentElement;

    expect(live).toHaveAttribute('aria-live', 'off');
    act(() => vi.advanceTimersByTime(4000));
    expect(activeTitle()).toBe('Two');

    fireEvent.click(screen.getByRole('button', { name: 'Stop automatic slide show' }));
    expect(screen.getByRole('button', { name: 'Start automatic slide show' })).toBeInTheDocument();
    // Announced politely again once it no longer moves on its own.
    expect(live).toHaveAttribute('aria-live', 'polite');

    act(() => vi.advanceTimersByTime(20000));
    expect(activeTitle()).toBe('Two');

    fireEvent.click(screen.getByRole('button', { name: 'Start automatic slide show' }));
    act(() => vi.advanceTimersByTime(4000));
    expect(activeTitle()).toBe('Three');
  });

  it('pauses while the pointer is over it', () => {
    const { container } = render(<Slider slides={slides.slice(0, 3)} interval={4000} />);
    const region = container.querySelector('section') as HTMLElement;

    fireEvent.mouseEnter(region);
    act(() => vi.advanceTimersByTime(12000));
    expect(activeTitle()).toBe('One');

    fireEvent.mouseLeave(region);
    act(() => vi.advanceTimersByTime(4000));
    expect(activeTitle()).toBe('Two');
  });

  it('never rotates faster than every 3 seconds', () => {
    render(<Slider slides={slides.slice(0, 3)} interval={500} />);

    act(() => vi.advanceTimersByTime(2999));
    expect(activeTitle()).toBe('One');
    act(() => vi.advanceTimersByTime(1));
    expect(activeTitle()).toBe('Two');
  });

  it('does not autoplay or animate with prefers-reduced-motion: reduce', () => {
    mockMatchMedia(['(prefers-reduced-motion: reduce)']);
    render(<Slider slides={slides.slice(0, 3)} interval={3000} />);

    act(() => vi.advanceTimersByTime(30000));

    expect(activeTitle()).toBe('One');
    expect(screen.queryByRole('button', { name: /automatic slide show/ })).toBeNull();
    expect(groups()[0]?.parentElement).not.toHaveClass('transition-transform');
    // Manual navigation still works.
    fireEvent.click(screen.getByRole('button', { name: 'Next slide' }));
    expect(activeTitle()).toBe('Two');
  });

  it('uses translated labels from PHP', () => {
    render(
      <Slider
        slides={slides.slice(0, 2)}
        autoplay={false}
        labels={{ next: 'Επόμενο', goTo: 'Διαφάνεια %1$s: %2$s' }}
      />,
    );

    expect(screen.getByRole('button', { name: 'Επόμενο' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: 'Διαφάνεια 2: Two' })).toBeInTheDocument();
  });

  it('renders nothing without slides', () => {
    const { container } = render(<Slider />);
    expect(container).toBeEmptyDOMElement();
  });
});
