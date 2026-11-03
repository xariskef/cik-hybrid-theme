---
name: qa
description: Quality checks for the Kefallinos Hybrid theme — coding standards, PHP and JS unit tests, type checking, build and end-to-end smoke tests against a local WordPress. Use before every commit, before tagging a release, or when asked to "test", "check" or "QA" the theme.
---

# Kefallinos Hybrid — QA

Run the checks in this order and stop at the first failure; fix it, then start again.

## 1. Static checks

```bash
composer install            # first time only
vendor/bin/phpcs            # WordPress Coding Standards — must report 0 errors
pnpm install --prefer-offline
pnpm typecheck              # TypeScript, strict
```

No PHP locally? Use Docker:

```bash
docker run --rm -v "$PWD:/app" -w /app composer:2 vendor/bin/phpcs
```

## 2. Unit tests

```bash
composer test               # PHPUnit + Brain Monkey: helpers, asset loading, islands, settings, block attributes
pnpm test                   # Vitest: island loader and island components
```

A bug fix comes with a test that fails without the fix.

## 3. Build

```bash
pnpm build                  # writes dist/ and dist/.vite/manifest.json
```

Make sure no `hot` file is left in the theme root afterwards — with it, WordPress loads assets from the dev server.

## 4. Smoke tests in WordPress

```bash
pnpm env:start              # local WordPress with the theme active (Docker)
pnpm test:e2e               # Playwright
```

The suite opens the front page, a post, a page, an archive, search, a 404, the latest-posts block and (if
WooCommerce is active) the shop, and checks: HTTP status, no PHP warnings in the HTML, built CSS/JS load, islands
mount, no console errors, menus open with the keyboard.

Then look at `wp-content/debug.log` — it must not contain notices, warnings or fatals from the theme.

## 5. Manual checks for UI changes

- Keyboard only: tab through header, menu, islands; focus is always visible; Escape closes the mobile menu.
- 375 px width: no horizontal scroll.
- `prefers-reduced-motion: reduce`: the slider does not autoplay.
- Block editor: the latest-posts block previews and its controls work.

## Report

List each step with pass/fail and the exact error output for failures. Do not mark something as passing that
was not run.
