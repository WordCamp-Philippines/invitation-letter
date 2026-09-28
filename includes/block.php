<?php
/**
 * Registers the "Invitation Letter" block: an editable, in-editor
 * alternative to the [wcph_invitation] shortcode. Content typed into the
 * block (opening/benefits/closing) overrides the site-wide Settings text
 * for that one block instance; merge tags like {event_name} are supported
 * and can be inserted from a toolbar dropdown while editing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the block editor script/style and the block type itself.
 */
function wcph_il_register_block() {
	wp_register_script(
		'wcph-il-block-editor',
		WCPH_IL_URL . 'blocks/invitation-letter/index.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
		WCPH_IL_VERSION,
		true
	);

	$settings = wcph_il_get_settings();

	wp_localize_script(
		'wcph-il-block-editor',
		'wcphIlBlockData',
		array(
			'mergeTags' => array(
				array(
					'tag'   => '{event_name}',
					'label' => __( 'Event name', 'wcph-invitation-letter' ),
				),
				array(
					'tag'   => '{event_theme}',
					'label' => __( 'Event theme', 'wcph-invitation-letter' ),
				),
				array(
					'tag'   => '{event_dates}',
					'label' => __( 'Event dates', 'wcph-invitation-letter' ),
				),
				array(
					'tag'   => '{event_venue}',
					'label' => __( 'Event venue', 'wcph-invitation-letter' ),
				),
				array(
					'tag'   => '{event_city}',
					'label' => __( 'Event city', 'wcph-invitation-letter' ),
				),
				array(
					'tag'   => '{event_website}',
					'label' => __( 'Event website', 'wcph-invitation-letter' ),
				),
			),
			'defaults'  => array(
				// Plain-text placeholder shown in the block when the field is empty
				// (RichText placeholders render as plain text, not HTML).
				'letterBody' => wp_strip_all_tags( $settings['letter_body'] ),
			),
		)
	);

	register_block_type( WCPH_IL_PATH . 'blocks/invitation-letter' );
}
add_action( 'init', 'wcph_il_register_block' );
