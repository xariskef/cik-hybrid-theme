---
name: theme-development
description: Conventions and step-by-step recipes for working on the Kefallinos Hybrid theme — templates, PHP components, React islands, dynamic blocks, assets, i18n and security. Use when adding or changing any template, component, island, block, setting or asset in this theme.
---

# Kefallinos Hybrid — development

Read `AGENTS.md` first for the folder map and commands. This skill adds the rules and recipes.

## Rules

**Rendering**
- Everything a search engine should see is rendered by PHP. React islands are only for interactive extras
  (landing pages, widgets) where SEO does not matter.
- Templates stay thin: query/prepare data, then call components. No markup duplicated between templates —
  extract a component instead.

**Security**
- Sanitise early (`sanitize_text_field`, `absint`, `sanitize_key`, `esc_url_raw`), escape late, at the point of output
  (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`). Components escape their own args — callers pass raw values.
- Never echo a URL that came from content or settings without `esc_url()`; links passed to islands are validated
  in PHP (`http`, `https` or relative only).
- Island props go through `wp_json_encode()` and then `esc_attr()`.
- Any form or admin action: nonce + `current_user_can()`. SQL only through `WP_Query` or `$wpdb->prepare()`.

**WordPress**
- Prefix everything: functions/hooks/options `cik_hybrid_`, constants `CIK_HYBRID_`, handles/CSS `cik-hybrid-`.
- Text domain `cik-hybrid`; English source strings; use `_x()` / translator comments when the context is unclear.
- Optional integrations (WooCommerce, ACF) must degrade: guard with `class_exists()` / `function_exists()`, and the
  theme must work without them.
- Blocks are dynamic (`render.php`), so changing markup never breaks saved content.

**Front end**
- Tailwind tokens live in the CSS `@theme`; `theme.json` mirrors colours, font sizes and spacing — change both together.
- Keyboard and screen-reader support is part of done: real `<button>`s, `aria-expanded` on toggles, visible focus,
  respect `prefers-reduced-motion`.
- pnpm only.

## Recipes

### Add a component
1. Create `components/<name>.php`. Start with defaults:
   `$args = wp_parse_args( $args, array( 'title' => '', 'url' => '' ) );`
2. Escape every value where it is printed. Return early if required args are empty.
3. Use it: `cik_hybrid_component( '<name>', array( ... ) );`
4. If it holds logic worth testing (e.g. building classes, choosing an image size), move that logic to a function in
   `inc/helpers/` and add a PHPUnit test.

Where things live: component loader `inc/helpers/components.php`, islands `inc/helpers/islands.php`, icons
`inc/helpers/icons.php`, Vite/asset loading `inc/setup/assets.php`, theme supports `inc/setup/theme-setup.php`,
nav walker `inc/navigation/`, WooCommerce `inc/woocommerce.php`.

### Add a React island
1. Create `src/islands/<Name>.tsx` with a default export; props typed. It is picked up by `import.meta.glob`, no
   registration needed.
2. In the template: `cik_hybrid_island( '<Name>', $props, $fallback_html );` — props validated/sanitised in PHP first.
3. Keep it accessible (labels, focus, reduced motion) and add a Vitest test for any non-trivial logic.
4. `pnpm typecheck && pnpm build`.

### Add a dynamic block
1. `blocks/<name>/block.json` (apiVersion 3, `"render": "file:./render.php"`, attributes with defaults and types).
2. `render.php`: sanitise attributes (`absint`, caps, allow-lists), query with `WP_Query` (`no_found_rows` when no
   pagination), output with `get_block_wrapper_attributes()`, reuse components.
3. Editor script: buildless `editor.js` using `wp.*` globals + `editor.asset.php` with dependencies and version.
4. Register it next to the others in `cik_hybrid_register_blocks()` (`inc/blocks.php`) and add a PHPUnit test for
   the attribute sanitising.

### Add a setting
1. Add one entry to `cik_hybrid_setting_fields()` in `inc/settings.php` (`label` + `type`: `text`, `textarea`
   or `url`). That single definition feeds both the ACF options page and the Customizer.
2. If you need a new type, extend `cik_hybrid_sanitize_setting()` — values are sanitised on read as well.
3. Read it only through `cik_hybrid_setting( $key, $default )`.

## Done means
PHPCS clean, PHPUnit and Vitest green, `pnpm typecheck` and `pnpm build` pass, no PHP notices in `debug.log`,
no `console.log` / `error_log` / `var_dump` left, new strings translatable. Run the `qa` skill before committing.
