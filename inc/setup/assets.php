<?php
/**
 * Vite integration: dev server (hot file) or production manifest.
 *
 * JavaScript is loaded through the Script Modules API (WP 6.5+). Vite emits ES
 * modules, and the API already prints type="module" and modulepreload links, so
 * no script_loader_tag rewriting is needed. In classic themes WordPress prints
 * enqueued modules in wp_footer, which lets templates enqueue them mid-render.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL of the running Vite dev server, read from the `hot` file that
 * vite.config.ts writes on start and deletes on exit.
 *
 * @return string|null Dev server origin without trailing slash, or null in production.
 */
function cik_hybrid_vite_dev_url(): ?string {
	static $url = false;

	if ( false === $url ) {
		$url      = null;
		$hot_file = CIK_HYBRID_DIR . '/hot';

		/**
		 * Filters whether the theme may use the Vite dev server when a hot file exists.
		 * Lets a production deploy that accidentally ships a hot file ignore it.
		 *
		 * @param bool $enabled Default true.
		 */
		if ( is_readable( $hot_file ) && apply_filters( 'cik_hybrid_vite_dev_enabled', true ) ) {
			$contents = trim( (string) file_get_contents( $hot_file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file, not a remote request.
			$scheme   = wp_parse_url( $contents, PHP_URL_SCHEME );
			if ( filter_var( $contents, FILTER_VALIDATE_URL ) && in_array( $scheme, array( 'http', 'https' ), true ) ) {
				$url = untrailingslashit( esc_url_raw( $contents ) );
			}
		}
	}

	return $url;
}

/**
 * Decoded Vite manifest (dist/.vite/manifest.json), cached for the request.
 *
 * @return array<string, array<string, mixed>>
 */
function cik_hybrid_vite_manifest(): array {
	static $manifest = null;

	if ( null === $manifest ) {
		$path     = CIK_HYBRID_DIR . '/dist/.vite/manifest.json';
		$decoded  = is_readable( $path ) ? wp_json_file_decode( $path, array( 'associative' => true ) ) : null;
		$manifest = is_array( $decoded ) ? $decoded : array();
	}

	return $manifest;
}

/**
 * Whether any build of the front-end assets is available.
 *
 * @return bool
 */
function cik_hybrid_assets_available(): bool {
	return null !== cik_hybrid_vite_dev_url() || array() !== cik_hybrid_vite_manifest();
}

/**
 * Enqueues a Vite entry by its source path, e.g. `src/js/app.ts` or `src/css/app.css`.
 *
 * @param string $entry Source path relative to the theme root.
 * @return bool Whether the entry was found and enqueued.
 */
function cik_hybrid_enqueue_vite_entry( string $entry ): bool {
	$dev_url = cik_hybrid_vite_dev_url();

	if ( null !== $dev_url ) {
		wp_register_script_module( 'cik-hybrid/vite-client', $dev_url . '/@vite/client', array(), null );
		// In dev, Vite serves CSS entries as JS modules that inject the styles (with HMR).
		wp_enqueue_script_module( 'cik-hybrid/' . $entry, $dev_url . '/' . $entry, array( 'cik-hybrid/vite-client' ), null );
		return true;
	}

	$manifest = cik_hybrid_vite_manifest();
	if ( empty( $manifest[ $entry ]['file'] ) ) {
		return false;
	}

	$chunk = $manifest[ $entry ];
	$base  = CIK_HYBRID_URI . '/dist/';

	// Filenames carry a content hash, so a ?ver= query string would add nothing: pass null.
	if ( str_ends_with( $chunk['file'], '.css' ) ) {
		wp_enqueue_style( 'cik-hybrid-' . sanitize_key( basename( $entry, '.css' ) ), $base . $chunk['file'], array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Hashed filename.
		return true;
	}

	wp_enqueue_script_module(
		'cik-hybrid/' . $entry,
		$base . $chunk['file'],
		cik_hybrid_register_vite_imports( $chunk, $manifest ),
		null
	);

	foreach ( cik_hybrid_collect_vite_css( $entry, $manifest ) as $css_file ) {
		wp_enqueue_style( 'cik-hybrid-' . sanitize_key( $css_file ), $base . $css_file, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Hashed filename.
	}

	return true;
}

/**
 * Registers a chunk's static imports as script modules and returns their ids.
 * Declaring them as dependencies makes WordPress print <link rel="modulepreload">
 * for each one, so the browser fetches them in parallel with the entry.
 *
 * @param array<string, mixed>                $chunk    Manifest chunk.
 * @param array<string, array<string, mixed>> $manifest Full manifest.
 * @return string[] Script module ids.
 */
function cik_hybrid_register_vite_imports( array $chunk, array $manifest ): array {
	$ids = array();

	foreach ( (array) ( $chunk['imports'] ?? array() ) as $import ) {
		if ( empty( $manifest[ $import ]['file'] ) ) {
			continue;
		}

		$id = 'cik-hybrid/' . $import;
		wp_register_script_module(
			$id,
			CIK_HYBRID_URI . '/dist/' . $manifest[ $import ]['file'],
			cik_hybrid_register_vite_imports( $manifest[ $import ], $manifest ),
			null
		);
		$ids[] = $id;
	}

	return $ids;
}

/**
 * CSS files emitted for an entry and everything it statically imports.
 *
 * @param string                              $key      Manifest key.
 * @param array<string, array<string, mixed>> $manifest Full manifest.
 * @param array<string, bool>                 $seen     Visited keys (guards against cycles).
 * @return string[]
 */
function cik_hybrid_collect_vite_css( string $key, array $manifest, array &$seen = array() ): array {
	if ( isset( $seen[ $key ] ) || empty( $manifest[ $key ] ) ) {
		return array();
	}
	$seen[ $key ] = true;

	$css = (array) ( $manifest[ $key ]['css'] ?? array() );
	foreach ( (array) ( $manifest[ $key ]['imports'] ?? array() ) as $import ) {
		$css = array_merge( $css, cik_hybrid_collect_vite_css( $import, $manifest, $seen ) );
	}

	return array_values( array_unique( $css ) );
}

/**
 * Built stylesheet path relative to the theme root, for add_editor_style().
 *
 * @return string|null
 */
function cik_hybrid_built_stylesheet(): ?string {
	$manifest = cik_hybrid_vite_manifest();
	$file     = $manifest['src/css/app.css']['file'] ?? '';

	return '' !== $file ? 'dist/' . $file : null;
}

/**
 * Global front-end assets. Island code is enqueued separately, only on pages
 * that render an island (see inc/helpers/islands.php).
 */
function cik_hybrid_enqueue_assets(): void {
	cik_hybrid_enqueue_vite_entry( 'src/css/app.css' );
	cik_hybrid_enqueue_vite_entry( 'src/js/app.ts' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'cik_hybrid_enqueue_assets' );

/**
 * The React refresh preamble that @vitejs/plugin-react expects in dev mode.
 * Vite normally injects it into index.html; WordPress renders the HTML, so we do.
 */
function cik_hybrid_print_react_refresh_preamble(): void {
	$dev_url = cik_hybrid_vite_dev_url();
	if ( null === $dev_url ) {
		return;
	}

	$preamble = sprintf(
		'import RefreshRuntime from %s;
RefreshRuntime.injectIntoGlobalHook(window);
window.$RefreshReg$ = () => {};
window.$RefreshSig$ = () => (type) => type;
window.__vite_plugin_react_preamble_installed__ = true;',
		wp_json_encode( $dev_url . '/@react-refresh' )
	);

	wp_print_inline_script_tag( $preamble, array( 'type' => 'module' ) );
}
add_action( 'wp_head', 'cik_hybrid_print_react_refresh_preamble', 1 );

/**
 * Tells administrators why the site is unstyled when neither a build nor a dev server exists.
 */
function cik_hybrid_missing_assets_notice(): void {
	if ( cik_hybrid_assets_available() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		wp_kses(
			__( '<strong>Kefallinos Hybrid:</strong> front-end assets are not built. Run <code>pnpm install &amp;&amp; pnpm build</code> in the theme folder, or <code>pnpm dev</code> while developing.', 'cik-hybrid' ),
			array(
				'strong' => array(),
				'code'   => array(),
			)
		)
	);
}
add_action( 'admin_notices', 'cik_hybrid_missing_assets_notice' );
