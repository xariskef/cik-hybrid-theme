# Working on Kefallinos Hybrid

Classic WordPress theme: server-rendered PHP templates and components, Tailwind CSS 4 built by Vite, optional
React islands. Read `README.md` for the architecture.

## Map

- `functions.php` – constants only, then `inc/init.php` (the loader; add new includes there).
- `inc/setup/` – theme supports (`theme-setup.php`), Vite dev/manifest loading (`assets.php`).
- `inc/helpers/` – `cik_hybrid_component()`, `cik_hybrid_island()`, icons.
- `inc/navigation/` – header menu walker. `inc/blocks.php`, `inc/settings.php`, `inc/woocommerce.php`.
- `components/` – PHP components. `blocks/` – dynamic blocks. `page-templates/` – page templates.
- `src/css/app.css` – Tailwind entry and tokens (`@theme`). `src/js/*.ts` – entries. `src/islands/*.tsx` – islands.
- `theme.json` – editor settings; mirrors the tokens in `app.css`.

## Conventions

- PHP prefix `cik_hybrid_` for functions, hooks, handles and options; constants `CIK_HYBRID_*`; classes `Cik_Hybrid_*`.
- Text domain `cik-hybrid` for every user-facing string, PHP and data passed to islands.
- Sanitise early, escape late: escape at the point of output, also inside components.
- Reusable markup goes in `components/` and is rendered with `cik_hybrid_component( $name, $args )`; document
  `$args` in the docblock and merge defaults with `wp_parse_args()`.
- Islands (`cik_hybrid_island()`) are only for interactivity where SEO does not matter. Indexable content is
  rendered by PHP. Pass URLs through `esc_url_raw( $url, array( 'http', 'https' ) )`; never pass private data.
- Blocks are dynamic: `block.json` + `render.php`, `save()` returns `null`; re-validate attributes in PHP.
- Design tokens live in the `@theme` block of `src/css/app.css`; when they change, update `theme.json` too.
- Use full Tailwind class names in PHP/TSX (no string concatenation) so the scanner finds them.
- pnpm only (`pnpm install --prefer-offline`). Do not commit `dist/`, `hot`, `vendor/` or `node_modules/`.

## Commands

```bash
pnpm dev          # dev server, writes ./hot
pnpm build        # dist/ + manifest
pnpm typecheck
composer install
vendor/bin/phpcs  # vendor/bin/phpcbf fixes formatting
```

## Adding things

- Component: `components/<name>.php`, then `cik_hybrid_component( '<name>', array( ... ) )`.
- Island: `src/islands/<Name>.tsx` with a default export, then `cik_hybrid_island( '<Name>', $props, $fallback )`.
- Script entry: `src/js/<name>.ts`, enqueue with `cik_hybrid_enqueue_vite_entry( 'src/js/<name>.ts' )`.
- Block: `blocks/<name>/` (`block.json`, `render.php`, editor script + `.asset.php`), register in `inc/blocks.php`.

## Definition of done

- `vendor/bin/phpcs` reports no errors or warnings.
- `pnpm typecheck` and `pnpm build` pass.
- No debug output (`var_dump`, `error_log`, `console.log`) left in the code.
- All strings translatable with the `cik-hybrid` text domain; output escaped.
- Checked in a browser with and without JavaScript where behaviour changed.
