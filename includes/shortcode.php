<?php
/**
 * [wcph_invitation] shortcode: renders the personalized invitation letter.
 *
 * Usage:
 *   [wcph_invitation name="Juan Dela Cruz"]
 *   or let the attendee fill in their own name + email via the on-page form
 *   (submitted as ?attendee=...&attendee_email=...)
 *   Falls back to a generic greeting when no name is provided.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves the attendee name from shortcode attribute, URL param, or fallback.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function wcph_il_resolve_attendee_name( $atts ) {
	if ( ! empty( $atts['name'] ) ) {
		return sanitize_text_field( $atts['name'] );
	}

	if ( ! empty( $_GET['attendee'] ) ) {
		return sanitize_text_field( wp_unslash( $_GET['attendee'] ) );
	}

	return '';
}

/**
 * Resolves the attendee email from shortcode attribute or URL param.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function wcph_il_resolve_attendee_email( $atts ) {
	if ( ! empty( $atts['email'] ) ) {
		return sanitize_email( $atts['email'] );
	}

	if ( ! empty( $_GET['attendee_email'] ) ) {
		return sanitize_email( wp_unslash( $_GET['attendee_email'] ) );
	}

	return '';
}

/**
 * Core letter renderer shared by the [wcph_invitation] shortcode and the
 * "Invitation Letter" block. Resolves the attendee, merges any per-instance
 * content overrides over the site-wide settings, logs a valid generation,
 * and renders the letter template.
 *
 * @param array $atts               Attendee resolution attributes ('name', 'email').
 * @param array $content_overrides  Optional override for the editable letter body, keyed by
 *                                  'letter_body'. Omitted/empty falls back to the site-wide Settings value.
 * @return string
 */
function wcph_il_render_letter_content( $atts = array(), $content_overrides = array() ) {
	$attendee       = wcph_il_resolve_attendee_name( $atts );
	$attendee_email = wcph_il_resolve_attendee_email( $atts );
	$event          = wcph_il_get_event_details();

	if ( ! empty( $content_overrides['letter_body'] ) ) {
		$event['letter_body'] = wp_kses_post( $content_overrides['letter_body'] );
	}

	// Require both a name and a valid email before unlocking the letter/print.
	$has_name = ( '' !== $attendee ) && is_email( $attendee_email );

	if ( $has_name ) {
		wcph_il_log_invitation( $attendee, $attendee_email );
	}

	ob_start();
	include WCPH_IL_PATH . 'templates/invitation-letter.php';
	return ob_get_clean();
}

/**
 * Renders the [wcph_invitation] shortcode output.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function wcph_il_render_invitation( $atts ) {
	$atts = shortcode_atts(
		array(
			'name'  => '',
			'email' => '',
		),
		$atts,
		'wcph_invitation'
	);

	return wcph_il_render_letter_content( $atts, array() );
}
add_shortcode( 'wcph_invitation', 'wcph_il_render_invitation' );
