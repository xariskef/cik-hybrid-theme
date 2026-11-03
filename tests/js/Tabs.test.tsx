import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it } from 'vitest';
import Tabs from '../../src/islands/Tabs';

const tabs = [
  {
    label: 'News',
    items: [
      { title: 'Safe post', url: '/safe/', meta: '1 Nov 2026' },
      { title: 'Unsafe post', url: 'javascript:alert(1)' },
    ],
    moreUrl: '/category/news/',
    moreLabel: 'All news',
  },
  { label: 'Guides', items: [{ title: 'A guide', url: 'https://example.test/guide/' }] },
  { label: 'Reviews', items: [], moreUrl: 'data:text/html,x', moreLabel: 'All reviews' },
];

const selected = (): string[] =>
  screen.getAllByRole('tab').filter((tab) => tab.getAttribute('aria-selected') === 'true').map((tab) => tab.textContent ?? '');

describe('Tabs', () => {
  it('starts on the first tab with a roving tabindex', () => {
    render(<Tabs tabs={tabs} label="Topics" />);

    expect(screen.getByRole('tablist', { name: 'Topics' })).toBeInTheDocument();
    expect(selected()).toEqual(['News']);
    expect(screen.getAllByRole('tab').map((tab) => tab.tabIndex)).toEqual([0, -1, -1]);
    expect(screen.getByRole('tabpanel')).toHaveAccessibleName('News');
  });

  it('moves selection and focus with the arrow keys, wrapping at both ends', async () => {
    const user = userEvent.setup();
    render(<Tabs tabs={tabs} />);

    await user.click(screen.getByRole('tab', { name: 'News' }));
    await user.keyboard('{ArrowRight}');
    expect(selected()).toEqual(['Guides']);
    expect(screen.getByRole('tab', { name: 'Guides' })).toHaveFocus();

    await user.keyboard('{ArrowRight}{ArrowRight}');
    expect(selected()).toEqual(['News']);

    await user.keyboard('{ArrowLeft}');
    expect(selected()).toEqual(['Reviews']);
    expect(screen.getByRole('tab', { name: 'Reviews' })).toHaveFocus();
  });

  it('supports Home and End', async () => {
    const user = userEvent.setup();
    render(<Tabs tabs={tabs} />);

    await user.click(screen.getByRole('tab', { name: 'Guides' }));
    await user.keyboard('{End}');
    expect(selected()).toEqual(['Reviews']);
    await user.keyboard('{Home}');
    expect(selected()).toEqual(['News']);
    expect(screen.getByRole('tab', { name: 'News' })).toHaveFocus();
  });

  it('shows only the active panel, linked to its tab', async () => {
    const user = userEvent.setup();
    render(<Tabs tabs={tabs} />);

    await user.click(screen.getByRole('tab', { name: 'Guides' }));

    const panel = screen.getByRole('tabpanel');
    const tab = screen.getByRole('tab', { name: 'Guides' });
    expect(panel).toHaveAttribute('aria-labelledby', tab.id);
    expect(tab).toHaveAttribute('aria-controls', panel.id);
    expect(panel).toHaveTextContent('A guide');
    expect(screen.queryByText('Safe post')).not.toBeVisible();
  });

  it('renders unsafe item and "more" URLs as plain text', () => {
    const { container } = render(<Tabs tabs={tabs} />);

    expect(screen.getByRole('link', { name: 'Safe post' })).toHaveAttribute('href', '/safe/');
    expect(screen.getByText('Unsafe post').tagName).toBe('SPAN');
    expect(container.querySelector('a[href^="data:"], a[href^="javascript:"]')).toBeNull();
    expect(screen.queryByText('All reviews')).toBeNull();
  });
});
