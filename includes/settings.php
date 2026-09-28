<?php
/**
 * Admin settings: lets an admin edit event details, letterhead heading,
 * signatory, logo, and benefits list from wp-admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WCPH_IL_OPTION_KEY', 'wcph_il_settings' );

/**
 * Default settings, matching the letter's original hardcoded content.
 *
 * @return array
 */
function wcph_il_default_settings() {
	return array(
		'logo_id'            => 0,
		'letterhead_heading' => '',
		'event_name'         => 'WordCamp Philippines 2026',
		'event_dates'        => "March 20\xE2\x80\x9321, 2026",
		'event_venue'        => 'SMX Convention Center',
		'event_city'         => 'Pasay City, Metro Manila, Philippines',
		'event_theme'        => 'Building the Open Web, Together',
		'event_website'      => 'https://philippines.wordcamp.org',
		'signatory_name'     => '',
		'signatory_title'    => 'WordCamp Philippines 2026 Organizing Team',
		'letter_body'        => "<p>On behalf of the organizing team, it is our pleasure to formally invite you to {event_name}, themed \u201c{event_theme}.\u201d The event will be held on {event_dates} at {event_venue}, {event_city}, bringing together WordPress enthusiasts, developers, designers, and business owners from across the Philippines and beyond.</p>"
			. '<p>We believe your presence would add great value to this gathering. Attending offers the following opportunities: '
			. implode(
				' ',
				array(
					'Learn from local and international WordPress experts through hands-on talks and workshops.',
					'Network with developers, designers, agencies, and business owners in the WordPress community.',
					'Discover the latest in Gutenberg, block themes, plugin development, and site performance.',
					'Get exclusive access to sponsor booths, giveaways, and community meetups.',
					'Contribute to the WordPress open-source project through Contributor Day.',
					'Receive a certificate of attendance recognized by the global WordPress community.',
				)
			)
			. '</p>'
			. '<p>We look forward to welcoming you and hope this letter serves as your official invitation for any travel or leave arrangements you may need to make.</p>',
	);
}

/**
 * Combines the legacy 4-field letter content (intro/benefits intro/benefits/
 * closing) into the single "letter_body" rich-text field used going forward.
 * Used to migrate settings saved before the fields were merged.
 *
 * @param array $settings Settings array containing the legacy fields.
 * @return string Combined HTML.
 */
function wcph_il_combine_legacy_letter_fields( $settings ) {
	$parts = array();
	foreach ( array( 'intro_paragraph', 'benefits_intro', 'benefits', 'closing_paragraph' ) as $field ) {
		if ( ! empty( $settings[ $field ] ) ) {
			$parts[] = trim( $settings[ $field ] );
		}
	}
	return implode( '', $parts );
}

/**
 * Returns saved settings merged over defaults.
 *
 * @return array
 */
function wcph_il_get_settings() {
	$saved = get_option( WCPH_IL_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	// Migrate settings saved before the 4 separate letter fields (opening/
	// benefits intro/benefits/closing) were merged into one "letter_body" field.
	if ( empty( $saved['letter_body'] ) && ! empty( array_filter( array(
		isset( $saved['intro_paragraph'] ) ? $saved['intro_paragraph'] : '',
		isset( $saved['benefits_intro'] ) ? $saved['benefits_intro'] : '',
		isset( $saved['benefits'] ) ? $saved['benefits'] : '',
		isset( $saved['closing_paragraph'] ) ? $saved['closing_paragraph'] : '',
	) ) ) ) {
		$saved['letter_body'] = wcph_il_combine_legacy_letter_fields( $saved );
		unset( $saved['intro_paragraph'], $saved['benefits_intro'], $saved['benefits'], $saved['closing_paragraph'] );
		update_option( WCPH_IL_OPTION_KEY, $saved );
	}

	return wp_parse_args( $saved, wcph_il_default_settings() );
}

/**
 * Registers a top-level admin menu "Invitation Letter" with two submenus:
 * Settings and the generated-letters Log.
 */
function wcph_il_add_settings_page() {
	$settings_hook = add_menu_page(
		__( 'Invitation Letter', 'wcph-invitation-letter' ),
		__( 'Invitation Letter', 'wcph-invitation-letter' ),
		'manage_options',
		'wcph-invitation-letter',
		'wcph_il_render_settings_page',
		'dashicons-email-alt',
		30
	);
	add_submenu_page(
		'wcph-invitation-letter',
		__( 'Settings', 'wcph-invitation-letter' ),
		__( 'Settings', 'wcph-invitation-letter' ),
		'manage_options',
		'wcph-invitation-letter',
		'wcph_il_render_settings_page'
	);
	add_submenu_page(
		'wcph-invitation-letter',
		__( 'Invitation Log', 'wcph-invitation-letter' ),
		__( 'Invitation Log', 'wcph-invitation-letter' ),
		'manage_options',
		'wcph-invitation-log',
		'wcph_il_render_log_page'
	);

	// Store the real hook suffix WordPress assigned so wcph_il_admin_assets()
	// can match it reliably.
	if ( $settings_hook ) {
		update_option( 'wcph_il_settings_hook', $settings_hook, false );
	}
}
add_action( 'admin_menu', 'wcph_il_add_settings_page' );

/**
 * Enqueues the WP media uploader script/styles on our settings screen only.
 *
 * @param string $hook Current admin page hook suffix.
 */
function wcph_il_admin_assets( $hook ) {
	if ( get_option( 'wcph_il_settings_hook' ) !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_script(
		'wcph-il-admin',
		WCPH_IL_URL . 'assets/admin-settings.js',
		array( 'jquery' ),
		WCPH_IL_VERSION,
		true
	);
	wp_localize_script(
		'wcph-il-admin',
		'wcphIlAdmin',
		array(
			'title'      => __( 'Select Logo', 'wcph-invitation-letter' ),
			'buttonText' => __( 'Use this logo', 'wcph-invitation-letter' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'wcph_il_admin_assets' );

/**
 * Adds the "Justify" alignment button to the teenyMCE toolbar used by our
 * letter body editor (teeny mode omits it by default).
 *
 * @param string[] $buttons   Existing teenyMCE button list.
 * @param string   $editor_id Editor instance ID.
 * @return string[]
 */
function wcph_il_add_justify_button( $buttons, $editor_id ) {
	if ( 'wcph_il_letter_body' !== $editor_id ) {
		return $buttons;
	}
	$position = array_search( 'alignright', $buttons, true );
	if ( false === $position ) {
		$buttons[] = 'alignjustify';
		return $buttons;
	}
	array_splice( $buttons, $position + 1, 0, array( 'alignjustify' ) );
	return $buttons;
}
add_filter( 'teeny_mce_buttons', 'wcph_il_add_justify_button', 10, 2 );

/**
 * Sanitizes the settings array before saving.
 *
 * @param array $input Raw posted values.
 * @return array
 */
function wcph_il_sanitize_settings( $input ) {
	$defaults = wcph_il_default_settings();
	$output   = array();

	$output['logo_id']            = isset( $input['logo_id'] ) ? absint( $input['logo_id'] ) : 0;
	$output['letterhead_heading'] = isset( $input['letterhead_heading'] ) ? sanitize_text_field( $input['letterhead_heading'] ) : '';
	$output['event_name']         = isset( $input['event_name'] ) ? sanitize_text_field( $input['event_name'] ) : $defaults['event_name'];
	$output['event_dates']        = isset( $input['event_dates'] ) ? sanitize_text_field( $input['event_dates'] ) : $defaults['event_dates'];
	$output['event_venue']        = isset( $input['event_venue'] ) ? sanitize_text_field( $input['event_venue'] ) : $defaults['event_venue'];
	$output['event_city']         = isset( $input['event_city'] ) ? sanitize_text_field( $input['event_city'] ) : $defaults['event_city'];
	$output['event_theme']        = isset( $input['event_theme'] ) ? sanitize_text_field( $input['event_theme'] ) : $defaults['event_theme'];
	$output['event_website']      = isset( $input['event_website'] ) ? esc_url_raw( $input['event_website'] ) : $defaults['event_website'];
	$output['signatory_name']     = isset( $input['signatory_name'] ) ? sanitize_text_field( $input['signatory_name'] ) : '';
	$output['signatory_title']    = isset( $input['signatory_title'] ) ? sanitize_text_field( $input['signatory_title'] ) : $defaults['signatory_title'];
	// The letter body is edited with a rich text (TinyMCE) editor, so it
	// contains formatting markup (bold, italics, lists, links, alignment).
	// wp_kses_post() keeps the same safe subset of HTML WordPress allows in
	// post content (including the "text-align" style property for justify).
	$output['letter_body']        = isset( $input['letter_body'] ) ? wp_kses_post( $input['letter_body'] ) : $defaults['letter_body'];

	return $output;
}

/**
 * Registers the setting so the Settings API handles save/nonce/capability checks.
 */
function wcph_il_register_settings() {
	register_setting(
		'wcph_il_settings_group',
		WCPH_IL_OPTION_KEY,
		array(
			'sanitize_callback' => 'wcph_il_sanitize_settings',
			'default'           => wcph_il_default_settings(),
		)
	);
}
add_action( 'admin_init', 'wcph_il_register_settings' );

/**
 * Returns event details in the shape the letter template expects,
 * sourced from admin settings.
 *
 * @return array
 */
function wcph_il_get_event_details() {
	$settings = wcph_il_get_settings();
	return array(
		'name'               => $settings['event_name'],
		'dates'              => $settings['event_dates'],
		'venue'              => $settings['event_venue'],
		'city'               => $settings['event_city'],
		'theme'              => $settings['event_theme'],
		'website'            => $settings['event_website'],
		'letterhead_heading' => $settings['letterhead_heading'],
		'logo_id'            => $settings['logo_id'],
		'signatory_name'     => $settings['signatory_name'],
		'signatory_title'    => $settings['signatory_title'],
		'letter_body'        => $settings['letter_body'],
	);
}

/**
 * Replaces {merge_tags} in an editable letter paragraph with live event
 * details, so admins can reference the event name/dates/etc. in their
 * custom prose without hardcoding it twice.
 *
 * @param string $text  Raw text possibly containing {tags}.
 * @param array  $event Event details as returned by wcph_il_get_event_details().
 * @return string Text with tags replaced (caller is responsible for escaping/sanitizing on output).
 */
function wcph_il_replace_merge_tags( $text, $event ) {
	$replacements = array(
		'{event_name}'   => $event['name'],
		'{event_theme}'  => $event['theme'],
		'{event_dates}'  => $event['dates'],
		'{event_venue}'  => $event['venue'],
		'{event_city}'   => $event['city'],
		'{event_website}' => $event['website'],
	);
	return strtr( (string) $text, $replacements );
}

/**
 * Resolves an editable rich-text letter field (opening/benefits/closing) for
 * output: replaces merge tags, then re-runs it through wp_kses_post() as a
 * defense-in-depth safety net (the value was already sanitized on save).
 *
 * @param string $field Field key on the settings array (e.g. 'intro_paragraph').
 * @param array  $event Event details as returned by wcph_il_get_event_details().
 * @return string Safe HTML ready to echo directly (already escaped).
 */
function wcph_il_render_letter_field( $field, $event ) {
	$raw = isset( $event[ $field ] ) ? $event[ $field ] : '';
	return wp_kses_post( wcph_il_replace_merge_tags( $raw, $event ) );
}

/**
 * Renders the settings page by including its template.
 */
function wcph_il_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	include WCPH_IL_PATH . 'templates/settings-page.php';
}

/**
 * Renders the invitation log page by including its template.
 */
function wcph_il_render_log_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	include WCPH_IL_PATH . 'templates/log-page.php';
}
