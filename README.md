# Kefallinos Hybrid

A classic WordPress theme: PHP templates and reusable PHP components rendered on the server, styled with
Tailwind CSS 4 built by Vite, with optional React "islands" for interactive widgets. A small `theme.json`
mirrors the design tokens so the block editor matches the front end.

"Hybrid" refers to two things:

- **PHP first, React where it helps.** All indexable content (posts, pages, archives, the shop) is rendered by
  PHP. React islands are opt-in, for landing-page extras or widgets where search engines don't need the
  content. Pages without an island don't load React at all.
- **Classic theme with block-editor awareness.** Templates are PHP, but `theme.json` provides the palette, font
  sizes, spacing and layout widths, and the theme ships a dynamic block.

## Features

- Template hierarchy: `front-page`, `home`, `index`, `single`, `page`, `archive`, `search`, `404`, `comments`,
  `searchform`, plus page templates *Full width* and *Landing page*.
- Components in `components/` loaded with `cik_hybrid_component()`: button, card, product card, hero,
  section heading, page header, post grid, pagination, breadcrumbs, icon, site logo.
- Vite 6 + Tailwind 4 pipeline with a Laravel-style `hot` file for the dev server and the Vite manifest in
  production.
- React islands loader with two examples: an accessible `Slider` and `Tabs`.
- Dynamic block `cik-hybrid/latest-posts` with a buildless editor script.
- Accessible navigation: dropdown toggles with `aria-expanded`, mobile menu, Escape handling, no-JS fallback.
- WooCommerce support (when active): page wrappers, header cart count via cart fragments, product card.
- Theme settings (social links, footer text) from an ACF options page when available, otherwise the Customizer.
- PHPCS (WordPress Coding Standards) and a GitHub Actions workflow that also builds a release zip on tags.

## Requirements

- WordPress 6.5+ (uses the Script Modules API), PHP 8.1+
- For development: Node 20+, pnpm 10, Composer (or Docker) for PHPCS

## Installation

From a release: download `cik-hybrid-theme-vX.Y.Z.zip` from the GitHub release and upload it under
*Appearance → Themes → Add New*. The zip already contains the built `dist/`.

From source:

```bash
cd wp-content/themes
git clone <repo-url> cik-hybrid-theme
cd cik-hybrid-theme
pnpm install
pnpm build
composer install   # only needed for PHPCS
```

## Development

```bash
pnpm dev        # Vite dev server with HMR; writes ./hot while running
pnpm build      # production build into dist/
pnpm typecheck  # tsc --noEmit, strict mode
vendor/bin/phpcs
```

While `pnpm dev` runs, the theme loads assets from the dev server; stop it and the theme falls back to `dist/`.

## Architecture

### Request flow

```
WordPress template hierarchy → template (e.g. single.php)
  → get_header() / get_footer()
  → cik_hybrid_component( 'card', [ ... ] )   → components/card.php
  → cik_hybrid_island( 'Slider', $props )      → <div data-island> + islands loader
```

`functions.php` only defines constants and requires `inc/init.php`, which loads everything else in order.

### Components

```php
cik_hybrid_component( 'button', array(
	'label' => __( 'Read the blog', 'cik-hybrid' ),
	'url'   => get_permalink( $page_id ),
	'icon'  => 'arrow-right',
) );
```

`cik_hybrid_component()` wraps `get_template_part( 'components/' . $name, null, $args )`, so a child theme can
override any component by shipping a file with the same name. Each component documents its arguments in its
docblock, applies defaults with `wp_parse_args()` and escapes its own output.

### Asset pipeline

`vite.config.ts` builds `src/css/app.css` and every `src/js/*.ts` file as separate entries (new files are
picked up without config changes) and writes `dist/.vite/manifest.json`.

`inc/setup/assets.php` decides where assets come from:

- **Dev:** a small Vite plugin writes the dev server URL to `./hot` when the server starts and removes it on
  exit. If the file exists, PHP enqueues `@vite/client` and the source entries from that URL, plus the React
  refresh preamble. There is no HTTP probing from PHP: a file check is free and cannot slow down or hang a request.
- **Production:** PHP reads the manifest once per request and enqueues the hashed files. Static imports of an
  entry are registered as script module dependencies, so WordPress prints `modulepreload` links for them.
  Files are content-hashed, so no `?ver=` is added.
- **Neither:** administrators see a notice explaining how to build.

JavaScript is loaded with `wp_enqueue_script_module()` rather than `wp_enqueue_script()` plus a
`script_loader_tag` filter. Vite outputs ES modules, and the Script Modules API already prints
`type="module"`, handles dependencies and preloads. In classic themes WordPress prints enqueued modules in
`wp_footer`, which is what allows islands to enqueue their loader while the template is rendering.

### Islands

```php
cik_hybrid_island( 'Tabs', array( 'tabs' => $tabs, 'label' => __( 'Topics', 'cik-hybrid' ) ) );
```

1. PHP prints `<div data-island="Tabs" data-props="{...}">` with an optional fallback (filtered with
   `wp_kses_post()`); without a fallback it prints a small loading status.
2. On first use per request, it enqueues `src/js/islands.ts`.
3. The loader finds `[data-island]` elements and, when one approaches the viewport, imports the matching
   `src/islands/<Name>.tsx` chunk (via `import.meta.glob`) and mounts it with `createRoot`.

Islands replace their fallback rather than hydrating it; they are for interactivity, not for content that has
to be indexed. Props end up in the HTML, so never pass private data, and pass URLs through
`esc_url_raw( $url, array( 'http', 'https' ) )`. The components check URLs again before rendering links.

The `Slider` follows the WAI-ARIA carousel pattern: a play/pause control first, rotation pauses on hover and
focus, hidden slides are `inert`, buttons have labels, and with `prefers-reduced-motion` there is no autoplay or
animation. All visible strings are passed from PHP so they are translatable.

### Dynamic block

`blocks/latest-posts/` contains `block.json` (apiVersion 3), `render.php` and `editor.js`. The block is
registered with `register_block_type( __DIR__ . '/blocks/latest-posts' )`.

- `render.php` re-validates attributes (`absint`, count capped at 12, columns 1–4), runs a `WP_Query` with
  `no_found_rows`, and renders each post with the same `card` component the archives use.
- `save()` returns `null`, so markup changes in PHP apply to every existing block without block validation
  errors.
- `editor.js` is plain JavaScript using the `wp.*` globals and `ServerSideRender`, with a hand-written
  `editor.asset.php` for dependencies. The editor UI is a handful of controls, so a build step (and a second
  toolchain next to Vite) would add more than it saves. A block with a richer editor UI would move to
  `@wordpress/scripts`.

### Optional integrations

| Integration | When inactive |
| --- | --- |
| WooCommerce | `inc/woocommerce.php` is not loaded. When active, WooCommerce keeps its own templates and hooks (no template overrides); the theme adds wrappers, styles, a header cart link updated through `woocommerce_add_to_cart_fragments`, and a "From the shop" section on the front page. |
| ACF PRO / Secure Custom Fields | Settings are registered in the Customizer instead. `cik_hybrid_setting( $key, $default )` reads from whichever source is active and sanitises on read. |

## Folder structure

```
blocks/latest-posts/   dynamic block (block.json, render.php, editor.js)
components/            PHP components
inc/
  init.php             loader
  setup/               theme supports, asset loading
  helpers/             component, island and icon helpers
  navigation/          menu walker
  blocks.php           block registration
  settings.php         ACF options / Customizer
  woocommerce.php      loaded only with WooCommerce
page-templates/        Full width, Landing page
src/
  css/app.css          Tailwind entry and design tokens (@theme)
  js/                  entries: app.ts (navigation), islands.ts (loader)
  islands/             React components
theme.json             editor settings mirroring the tokens
vite.config.ts         build config and hot-file plugin
```

## Adding things

**A component:** create `components/promo.php`, document the `$args` it accepts, merge defaults with
`wp_parse_args()`, escape every output, then call `cik_hybrid_component( 'promo', array( ... ) )`.

**An island:** create `src/islands/Counter.tsx` with a default export, then call
`cik_hybrid_island( 'Counter', $props )` in a template. No registration step: the loader picks it up through
`import.meta.glob`.

**A script entry:** add `src/js/contact.ts` and enqueue it where needed with
`cik_hybrid_enqueue_vite_entry( 'src/js/contact.ts' )`.

**A block:** add `blocks/<name>/` with `block.json`, `render.php` and an editor script, then register it in
`inc/blocks.php`.

## Coding standards and CI

- `phpcs.xml.dist` uses the WordPress ruleset and PHPCompatibilityWP (PHP 8.1+), text domain `cik-hybrid`,
  prefixes `cik_hybrid` / `CIK_HYBRID`.
- TypeScript runs in strict mode (`noUncheckedIndexedAccess` included).
- `.github/workflows/ci.yml` runs `php -l` and PHPCS on PHP 8.1, then `pnpm typecheck` and `pnpm build`. On a
  `v*` tag it packages the theme with `git archive` (dev files are `export-ignore`d) plus the built `dist/` and
  attaches the zip to a GitHub release.

## Security notes

- Output is escaped late, at the point of printing (`esc_html`, `esc_attr`, `esc_url`, `wp_kses*`), including
  inside components, even when the caller already sanitised the value.
- Input is sanitised early: Customizer settings have `sanitize_callback`s, ACF values are sanitised on read,
  block attributes are re-validated in `render.php`.
- Island fallbacks go through `wp_kses_post()`; props are JSON-encoded and attribute-escaped.
- Slider and tab URLs are limited to `http`/`https` in PHP and again in TypeScript, so `javascript:` URLs never
  reach an `href`.
- Heading levels passed to components are checked against an allow-list.

## License

Copyright © Cikefallinos. All rights reserved. Source code published for portfolio review only. See
[LICENSE](LICENSE).
