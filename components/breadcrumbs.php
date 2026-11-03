<?php
/**
 * Component: breadcrumbs for the current request.
 *
 * @package Cik_Hybrid
 *
 * @var array $args {
 *     @type string $class Extra CSS classes. Default ''.
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( is_front_page() ) {
	return;
}

$args  = wp_parse_args( $args, array( 'class' => '' ) );
$trail = array(
	array(
		'label' => __( 'Home', 'cik-hybrid' ),
		'url'   => home_url( '/' ),
	),
);

if ( is_home() ) {
	$trail[] = array( 'label' => get_the_title( (int) get_option( 'page_for_posts' ) ) );
} elseif ( is_singular() ) {
	$current = get_queried_object();

	if ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( $current ) ) as $ancestor_id ) {
			$trail[] = array(
				'label' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			);
		}
	} elseif ( is_singular( 'post' ) ) {
		$categories = get_the_category( $current->ID );
		if ( ! empty( $categories ) ) {
			$trail[] = array(
				'label' => $categories[0]->name,
				'url'   => get_category_link( $categories[0] ),
			);
		}
	} else {
		$archive_url = get_post_type_archive_link( $current->post_type );
		if ( $archive_url ) {
			$trail[] = array(
				'label' => get_post_type_object( $current->post_type )->labels->name,
				'url'   => $archive_url,
			);
		}
	}

	$trail[] = array( 'label' => get_the_title( $current ) );
} elseif ( is_category() || is_tag() || is_tax() ) {
	$current_term = get_queried_object();
	foreach ( array_reverse( get_ancestors( $current_term->term_id, $current_term->taxonomy, 'taxonomy' ) ) as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, $current_term->taxonomy );
		if ( $ancestor instanceof WP_Term ) {
			$trail[] = array(
				'label' => $ancestor->name,
				'url'   => get_term_link( $ancestor ),
			);
		}
	}
	$trail[] = array( 'label' => $current_term->name );
} elseif ( is_search() ) {
	/* translators: %s: search query. */
	$trail[] = array( 'label' => sprintf( __( 'Search results for "%s"', 'cik-hybrid' ), get_search_query( false ) ) );
} elseif ( is_404() ) {
	$trail[] = array( 'label' => __( 'Page not found', 'cik-hybrid' ) );
} elseif ( is_archive() ) {
	$trail[] = array( 'label' => wp_strip_all_tags( get_the_archive_title() ) );
}

/**
 * Filters the breadcrumb trail before it is printed.
 *
 * @param array<int, array{label: string, url?: string}> $trail Items from Home to the current page.
 */
$trail = (array) apply_filters( 'cik_hybrid_breadcrumbs', $trail );
$last  = count( $trail ) - 1;
?>
<nav class="text-sm text-muted <?php echo esc_attr( $args['class'] ); ?>" aria-label="<?php esc_attr_e( 'Breadcrumb', 'cik-hybrid' ); ?>">
	<ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
		<?php foreach ( $trail as $index => $crumb ) : ?>
			<li class="flex items-center gap-2">
				<?php if ( $index < $last && ! empty( $crumb['url'] ) && ! is_wp_error( $crumb['url'] ) ) : ?>
					<a class="text-muted hover:text-accent" href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ); ?></a>
					<?php
					cik_hybrid_component(
						'icon',
						array(
							'name'  => 'chevron-right',
							'class' => 'size-3.5',
						)
					);
					?>
				<?php elseif ( $index === $last ) : ?>
					<span aria-current="page" class="text-ink"><?php echo esc_html( $crumb['label'] ); ?></span>
				<?php else : ?>
					<span><?php echo esc_html( $crumb['label'] ); ?></span>
					<?php
					cik_hybrid_component(
						'icon',
						array(
							'name'  => 'chevron-right',
							'class' => 'size-3.5',
						)
					);
					?>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
