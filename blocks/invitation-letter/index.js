( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var RichText = wp.blockEditor.RichText;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var BlockControls = wp.blockEditor.BlockControls;
	var AlignmentToolbar = wp.blockEditor.AlignmentToolbar;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var DropdownMenu = wp.components.DropdownMenu;
	var MenuGroup = wp.components.MenuGroup;
	var MenuItem = wp.components.MenuItem;
	var Notice = wp.components.Notice;
	var ServerSideRender = wp.serverSideRender;

	var data      = window.wcphIlBlockData || {};
	var mergeTags = data.mergeTags || [];
	var defaults  = data.defaults || {};

	var ALLOWED_FORMATS = [ 'core/bold', 'core/italic', 'core/link' ];

	registerBlockType( 'wcph/invitation-letter', {
		edit: function ( editProps ) {
			var attributes = editProps.attributes;
			var setAttributes = editProps.setAttributes;
			var blockProps = useBlockProps( { className: 'wcph-il-block-editor' } );

			function insertTag( tag ) {
				var current = attributes.letterBody || '';
				setAttributes( { letterBody: current + ( current ? ' ' : '' ) + tag } );
			}

			return el(
				Fragment,
				{},
				el(
					BlockControls,
					{},
					el( AlignmentToolbar, {
						value: attributes.textAlign,
						onChange: function ( value ) {
							setAttributes( { textAlign: value || '' } );
						},
					} )
				),
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'About this block', 'wcph-invitation-letter' ) },
						el(
							'p',
							{},
							__( 'Leave this blank to use the site-wide text from Invitation Letter → Settings. Type here to override just this instance. Merge tags like {event_name} are replaced with live event details wherever they appear.', 'wcph-invitation-letter' )
						)
					)
				),
				el(
					'div',
					blockProps,
					el(
						Notice,
						{ status: 'info', isDismissible: false },
						__( 'Invitation Letter block — edit the letter text below. The full letter (with name/email gate, print & email buttons) is shown to visitors on the front end.', 'wcph-invitation-letter' )
					),
					el(
						'div',
						{ className: 'wcph-il-block-field' },
						el(
							'div',
							{ className: 'wcph-il-block-field-header' },
							el( 'label', { className: 'wcph-il-block-field-label' }, __( 'Letter body', 'wcph-invitation-letter' ) ),
							mergeTags.length > 0 &&
								el( DropdownMenu, {
									label: __( 'Insert merge tag', 'wcph-invitation-letter' ),
									text: __( 'Insert merge tag', 'wcph-invitation-letter' ),
									toggleProps: { variant: 'secondary', isSmall: true },
									children: function ( args ) {
										return el(
											MenuGroup,
											{},
											mergeTags.map( function ( item ) {
												return el( MenuItem, {
													key: item.tag,
													onClick: function () {
														insertTag( item.tag );
														args.onClose();
													},
												}, item.label + ' — ' + item.tag );
											} )
										);
									},
								} )
						),
						el( RichText, {
							tagName: 'div',
							className: 'wcph-il-block-richtext',
							style: { textAlign: attributes.textAlign || undefined },
							multiline: 'p',
							allowedFormats: ALLOWED_FORMATS,
							value: attributes.letterBody,
							onChange: function ( val ) {
								setAttributes( { letterBody: val } );
							},
							placeholder: defaults.letterBody,
						} )
					),
					ServerSideRender &&
						el(
							'div',
							{ className: 'wcph-il-block-preview' },
							el( 'p', { className: 'wcph-il-block-preview-label' }, __( 'Live preview:', 'wcph-invitation-letter' ) ),
							el( ServerSideRender, {
								block: 'wcph/invitation-letter',
								attributes: attributes,
							} )
						)
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
