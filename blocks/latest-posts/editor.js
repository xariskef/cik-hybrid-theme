/**
 * Editor UI for cik-hybrid/latest-posts.
 *
 * Plain JavaScript on the wp.* globals, no build step: the block is dynamic,
 * so the editor only needs sidebar controls and a server-side preview.
 * Attributes, title and supports come from block.json via the server.
 */
( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, useBlockProps } = wp.blockEditor;
	const { Disabled, PanelBody, RangeControl, SelectControl, ToggleControl } = wp.components;
	const { useSelect } = wp.data;
	const { __ } = wp.i18n;
	const el = wp.element.createElement;
	const ServerSideRender = wp.serverSideRender;

	function Edit( { attributes, setAttributes } ) {
		const categories = useSelect(
			( select ) =>
				select( 'core' ).getEntityRecords( 'taxonomy', 'category', {
					per_page: 100,
					hide_empty: true,
					_fields: 'id,name',
				} ),
			[]
		);

		const categoryOptions = [ { label: __( 'All categories', 'cik-hybrid' ), value: '0' } ].concat(
			( categories || [] ).map( ( term ) => ( { label: term.name, value: String( term.id ) } ) )
		);

		const controls = el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{ title: __( 'Settings', 'cik-hybrid' ) },
				el( RangeControl, {
					label: __( 'Number of posts', 'cik-hybrid' ),
					value: attributes.count,
					min: 1,
					max: 12,
					onChange: ( value ) => setAttributes( { count: value || 1 } ),
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				el( RangeControl, {
					label: __( 'Columns', 'cik-hybrid' ),
					value: attributes.columns,
					min: 1,
					max: 4,
					onChange: ( value ) => setAttributes( { columns: value || 1 } ),
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				el( SelectControl, {
					label: __( 'Category', 'cik-hybrid' ),
					value: String( attributes.category ),
					options: categoryOptions,
					onChange: ( value ) => setAttributes( { category: parseInt( value, 10 ) || 0 } ),
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				el( ToggleControl, {
					label: __( 'Show excerpt', 'cik-hybrid' ),
					checked: attributes.showExcerpt,
					onChange: ( value ) => setAttributes( { showExcerpt: value } ),
					__nextHasNoMarginBottom: true,
				} )
			)
		);

		// Disabled keeps preview links from navigating away from the editor.
		return el(
			'div',
			useBlockProps(),
			controls,
			el( Disabled, null, el( ServerSideRender, { block: 'cik-hybrid/latest-posts', attributes } ) )
		);
	}

	registerBlockType( 'cik-hybrid/latest-posts', {
		edit: Edit,
		save: () => null,
	} );
} )( window.wp );
