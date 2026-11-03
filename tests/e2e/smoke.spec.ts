import { test as base, expect, type APIRequestContext, type Page } from '@playwright/test';

/**
 * Smoke test of the theme on a real WordPress (wp-env, seeded by tests/e2e/seed.sh,
 * or any site given with WP_BASE_URL). URLs are looked up through the REST API,
 * so the suite does not depend on the permalink structure or on fixed IDs.
 *
 * Pages are opened on the site's own home URL, which may differ from WP_BASE_URL
 * (e.g. 127.0.0.1 vs localhost): WordPress prints asset URLs for its home host,
 * and module scripts from another origin would fail CORS.
 */

type Site = { home: string; namespaces: string[] };

const test = base.extend<object, { site: Site }>({
  site: [
    async ({ playwright }, use, workerInfo) => {
      const api = await playwright.request.newContext({ baseURL: workerInfo.project.use.baseURL });
      const index = (await (await api.get('/wp-json/')).json()) as Site;
      await api.dispose();
      await use(index);
    },
    { scope: 'worker' },
  ],
});

const onSite = (site: Site, path: string): string => new URL(path.replace(/^\//, ''), `${site.home.replace(/\/$/, '')}/`).href;

// What PHP prints when display_errors is on (WP_DEBUG_DISPLAY).
const PHP_ERROR = /<b>(?:Warning|Notice|Deprecated|Fatal error|Parse error)<\/b>:|\b(?:Warning|Notice|Deprecated|Fatal error): .+ in \/.+ on line \d+/;

type Visit = { status: number; errors: string[] };

/** Opens a page and records console errors and uncaught exceptions while it loads. */
async function visit(page: Page, url: string): Promise<Visit> {
  const errors: string[] = [];
  page.on('console', (message) => {
    if (message.type() === 'error') errors.push(message.text());
  });
  page.on('pageerror', (error) => errors.push(error.message));

  const response = await page.goto(url);
  await page.waitForLoadState('load');

  return { status: response?.status() ?? 0, errors };
}

async function expectHealthyPage(page: Page, { status, errors }: Visit, expectedStatus = 200): Promise<void> {
  expect(status).toBe(expectedStatus);
  expect(await page.content()).not.toMatch(PHP_ERROR);

  // The Tailwind build is linked and applied (body has the `flex` utility class).
  await expect(page.locator('link[rel="stylesheet"][href*="/themes/cik-hybrid-theme/dist/"]')).not.toHaveCount(0);
  await expect(page.locator('body')).toHaveCSS('display', 'flex');

  // A 404 page logs its own document request as a failed resource; nothing else may fail.
  const ignored = expectedStatus === 404 ? /status of 404/ : null;
  expect(errors.filter((error) => !ignored?.test(error))).toEqual([]);
}

/** Link of the first REST item, or null when there is none. */
async function firstLink(request: APIRequestContext, endpoint: string): Promise<string | null> {
  const response = await request.get(`/wp-json/wp/v2/${endpoint}`);
  if (!response.ok()) return null;
  const [item] = (await response.json()) as Array<{ link: string }>;
  return item?.link ?? null;
}

test.describe('templates', () => {
  test('front page renders and mounts its islands', async ({ page, site }) => {
    await expectHealthyPage(page, await visit(page, onSite(site, '/')));

    const islands = page.locator('[data-island]');
    expect(await islands.count(), 'front page islands (seed content provides a slider and tabs)').toBeGreaterThan(0);

    // Islands mount when they get close to the viewport.
    for (const island of await islands.all()) {
      await island.scrollIntoViewIfNeeded();
      await expect(island).toHaveAttribute('data-island-mounted', '');
      await expect(island).not.toHaveAttribute('aria-busy');
    }
  });

  test('single post', async ({ page, request }) => {
    const link = await firstLink(request, 'posts?per_page=1');
    expect(link, 'a published post').not.toBeNull();

    await expectHealthyPage(page, await visit(page, link!));
    await expect(page.locator('main h1')).toBeVisible();
  });

  test('page', async ({ page, request }) => {
    const link = await firstLink(request, 'pages?slug=about');
    expect(link, 'a page with the slug "about"').not.toBeNull();

    await expectHealthyPage(page, await visit(page, link!));
    await expect(page.locator('main h1')).toBeVisible();
  });

  test('category archive', async ({ page, request }) => {
    const link = await firstLink(request, 'categories?orderby=count&order=desc&per_page=1');
    expect(link, 'a category').not.toBeNull();

    await expectHealthyPage(page, await visit(page, link!));
    await expect(page.locator('main article').first()).toBeVisible();
  });

  test('search results', async ({ page, site }) => {
    await expectHealthyPage(page, await visit(page, onSite(site, '/?s=a')));
    await expect(page.locator('main h1')).toBeVisible();
  });

  test('404', async ({ page, site }) => {
    await expectHealthyPage(page, await visit(page, onSite(site, '/cik-hybrid-smoke-missing-page/')), 404);
    await expect(page.locator('main')).toContainText(/not found|nothing/i);
  });

  test('page with the latest-posts block', async ({ page, request }) => {
    const link = await firstLink(request, 'pages?slug=latest-posts-block');
    test.skip(link === null, 'No page with the slug "latest-posts-block" (tests/e2e/seed.sh creates one).');

    await expectHealthyPage(page, await visit(page, link!));
    const block = page.locator('.wp-block-cik-hybrid-latest-posts');
    await expect(block).toBeVisible();
    await expect(block.locator('article').first()).toBeVisible();
  });

  test('shop', async ({ page, site }) => {
    test.skip(!site.namespaces.includes('wc/store/v1'), 'WooCommerce is not active.');

    await expectHealthyPage(page, await visit(page, onSite(site, '/?post_type=product')));
    await expect(page.locator('main')).toBeVisible();
  });
});

test.describe('mobile navigation', () => {
  test.use({ viewport: { width: 390, height: 844 } });

  test('opens with the keyboard and closes with Escape', async ({ page, site }) => {
    await expectHealthyPage(page, await visit(page, onSite(site, '/')));

    const toggle = page.locator('#cik-menu-toggle');
    const nav = page.locator('#cik-primary-nav');
    await expect(toggle).toBeVisible();
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');

    await toggle.focus();
    await page.keyboard.press('Enter');
    await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    await expect(nav).toHaveClass(/\bis-open\b/);
    await expect(nav.locator('a').first()).toBeVisible();
    // Focus moves into the menu.
    await expect(nav.locator(':focus')).toHaveCount(1);

    await page.keyboard.press('Escape');
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
    await expect(nav).not.toHaveClass(/\bis-open\b/);
    await expect(toggle).toBeFocused();
  });
});
