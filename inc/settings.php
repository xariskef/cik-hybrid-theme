<?php
/**
 * Theme settings: ACF options page when available, Customizer otherwise.
 *
 * Both sources describe the same fields, so templates only ever call
 * cik_hybrid_setting() and the theme works the same without ACF.
 *
 * @package Cik_Hybrid
 */

defined( 'ABSPATH' ) || exit;

/**
 * Setting definitions shared by ACF and the Customizer.
 *
 * Types: 'text', 'textarea', 'url' and 'checkbox' (stored as '1' or '0').
 * 'default' is what an unsaved field holds, also used as the Customizer default.
 *
 * @return array<string, array{label: string, type: string, default?: string}>
 */
function cik_hybrid_setting_fields(): array {
	return array(
		'show_site_name'   => array(
			'label'   => __( 'Show site name next to the logo', 'cik-hybrid' ),
			'type'    => 'checkbox',
			'default' => '1',
		),
		'social_facebook'  => array(
			'label' => __( 'Facebook URL', 'cik-hybrid' ),
			'type'  => 'url',
		),
		'social_instagram' => array(
			'label' => __( 'Instagram URL', 'cik-hybrid' ),
			'type'  => 'url',
		),
		'social_linkedin'  => array(
			'label' => __( 'LinkedIn URL', 'cik-hybrid' ),
			'type'  => 'url',
		),
		'footer_text'      => array(
			'label' => __( 'Footer text', 'cik-hybrid' ),
			'type'  => 'textarea',
		),
	);
}

/**
 * Sanitises a setting value according to its field type.
 *
 * @param mixed  $value Raw value.
 * @param string $type  Field type: 'url', 'checkbox', 'text' or 'textarea'.
 * @return string
 */
function cik_hybrid_sanitize_setting( $value, string $type ): string {
	if ( 'checkbox' === $type ) {
		// Never saved (null or '') stays empty, so the caller's default applies.
		if ( null === $value || '' === $value ) {
			return '';
		}
		return in_array( $value, array( true, 1, '1', 'on', 'yes' ), true ) ? '1' : '0';
	}

	$value = is_scalar( $value ) ? (string) $value : '';

	if ( 'url' === $type ) {
		return esc_url_raw( $value, array( 'http', 'https' ) );
	}

	return wp_kses(
		$value,
		array(
			'a'      => array(
				'href' => true,
				'rel'  => true,
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
		)
	);
}

/**
 * Sanitize callback for Customizer URL settings.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function cik_hybrid_sanitize_url_setting( $value ): string {
	return cik_hybrid_sanitize_setting( $value, 'url' );
}

/**
 * Sanitize callback for Customizer text settings.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function cik_hybrid_sanitize_text_setting( $value ): string {
	return cik_hybrid_sanitize_setting( $value, 'textarea' );
}

/**
 * Sanitize callback for Customizer checkboxes: an unchecked box is saved as '0'.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function cik_hybrid_sanitize_checkbox_setting( $value ): string {
	return '1' === cik_hybrid_sanitize_setting( $value, 'checkbox' ) ? '1' : '0';
}

/**
 * Sanitize callback for a field type.
 *
 * @param string $type Field type.
 * @return string Function name.
 */
function cik_hybrid_setting_sanitize_callback( string $type ): string {
	$callbacks = array(
		'url'      => 'cik_hybrid_sanitize_url_setting',
		'checkbox' => 'cik_hybrid_sanitize_checkbox_setting',
	);

	return $callbacks[ $type ] ?? 'cik_hybrid_sanitize_text_setting';
}

/**
 * Whether settings are managed through the ACF options page.
 *
 * @return bool
 */
function cik_hybrid_uses_acf(): bool {
	return function_exists( 'acf_add_options_page' ) && function_exists( 'acf_add_local_field_group' ) && function_exists( 'get_field' );
}

/**
 * Reads a theme setting. Values are sanitised on read as well, because ACF
 * stores what the editor typed.
 *
 * @param string $key           Setting key, e.g. 'footer_text'.
 * @param string $default_value Returned when the setting is empty. Defaults to the field's own default.
 * @return string
 */
function cik_hybrid_setting( string $key, string $default_value = '' ): string {
	$fields = cik_hybrid_setting_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return $default_value;
	}

	if ( '' === $default_value ) {
		$default_value = $fields[ $key ]['default'] ?? '';
	}

	$raw = cik_hybrid_uses_acf()
		? get_field( 'cik_hybrid_' . $key, 'option' )
		: get_theme_mod( 'cik_hybrid_' . $key, '' );

	$value = cik_hybrid_sanitize_setting( $raw, $fields[ $key ]['type'] );

	return '' !== $value ? $value : $default_value;
}

/**
 * Registers the Customizer section. Skipped when ACF owns the settings, so
 * editors never see two places for the same value.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function cik_hybrid_customize_register( WP_Customize_Manager $wp_customize ): void {
	if ( cik_hybrid_uses_acf() ) {
		return;
	}

	$wp_customize->add_section(
		'cik_hybrid_settings',
		array(
			'title'    => __( 'Theme settings', 'cik-hybrid' ),
			'priority' => 160,
		)
	);

	foreach ( cik_hybrid_setting_fields() as $key => $field ) {
		$wp_customize->add_setting(
			'cik_hybrid_' . $key,
			array(
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'default'           => $field['default'] ?? '',
				'sanitize_callback' => cik_hybrid_setting_sanitize_callback( $field['type'] ),
			)
		);
		$wp_customize->add_control(
			'cik_hybrid_' . $key,
			array(
				'label'   => $field['label'],
				'section' => 'cik_hybrid_settings',
				'type'    => $field['type'],
			)
		);
	}
}
add_action( 'customize_register', 'cik_hybrid_customize_register' );

/**
 * Registers the ACF options page and its local field group (ACF PRO or
 * Secure Custom Fields, which provide options pages).
 */
function cik_hybrid_acf_register(): void {
	if ( ! cik_hybrid_uses_acf() ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title'  => __( 'Theme settings', 'cik-hybrid' ),
			'menu_title'  => __( 'Theme settings', 'cik-hybrid' ),
			'menu_slug'   => 'cik-hybrid-settings',
			'parent_slug' => 'themes.php',
			'capability'  => 'edit_theme_options',
			'redirect'    => false,
		)
	);

	$fields = array();
	foreach ( cik_hybrid_setting_fields() as $key => $field ) {
		$is_checkbox = 'checkbox' === $field['type'];
		$fields[]    = array(
			'key'           => 'field_cik_hybrid_' . $key,
			'name'          => 'cik_hybrid_' . $key,
			'label'         => $field['label'],
			'type'          => $is_checkbox ? 'true_false' : $field['type'],
			'ui'            => $is_checkbox ? 1 : null,
			'default_value' => $field['default'] ?? '',
			'rows'          => 'textarea' === $field['type'] ? 3 : null,
		);
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_cik_hybrid_settings',
			'title'    => __( 'Theme settings', 'cik-hybrid' ),
			'fields'   => $fields,
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'cik-hybrid-settings',
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'cik_hybrid_acf_register' );
