<?php
/**
 * Handles the "Email me a copy" action: builds a self-contained, printable
 * HTML version of the letter and emails it to the attendee.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WCPH_IL_PATH . 'includes/email-template.php';
require_once WCPH_IL_PATH . 'includes/email-logo.php';

/**
 * Handles the front-end "Email me a copy" form submission.
 */
function wcph_il_handle_email_request() {
	if ( ! isset( $_POST['wcph_il_email_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['wcph_il_email_nonce'] ), 'wcph_il_email_letter' ) ) {
		wp_die( esc_html__( 'Security check failed. Please go back and try again.', 'wcph-invitation-letter' ) );
	}

	$attendee = isset( $_POST['attendee'] ) ? sanitize_text_field( wp_unslash( $_POST['attendee'] ) ) : '';
	$email    = isset( $_POST['attendee_email'] ) ? sanitize_email( wp_unslash( $_POST['attendee_email'] ) ) : '';
	$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/' );

	if ( '' === $attendee || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'wcph_il_email_sent', 'invalid', $redirect ) );
		exit;
	}

	$event = wcph_il_get_event_details();

	// Convert/cache the logo as a mail-safe PNG and embed it inline via CID
	// (not a linked URL) so it displays reliably even when the recipient's
	// client blocks remote images or can't render WebP.
	$logo     = ! empty( $event['logo_id'] ) ? wcph_il_get_mail_safe_logo( $event['logo_id'] ) : null;
	$logo_cid = $logo ? 'wcph-il-logo-' . (int) $event['logo_id'] : null;

	$html = wcph_il_build_letter_email_html( $attendee, $event, $logo_cid, $logo );

	$subject = sprintf(
		/* translators: %s: event name */
		__( 'Your Invitation Letter — %s', 'wcph-invitation-letter' ),
		$event['name']
	);

	// Ensure a valid "From" address: some environments (e.g. local dev without
	// SMTP configured) reject WordPress's default wordpress@<host> address.
	$from_filter = static function () {
		return sanitize_email( get_option( 'admin_email' ) );
	};
	add_filter( 'wp_mail_from', $from_filter );

	$embed_logo = function ( $phpmailer ) use ( $logo, $logo_cid ) {
		if ( $logo && $logo_cid ) {
			$phpmailer->AddEmbeddedImage( $logo['path'], $logo_cid, basename( $logo['path'] ), 'base64', $logo['mime'] );
		}
	};
	add_action( 'phpmailer_init', $embed_logo );

	$sent = wp_mail( $email, $subject, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );

	remove_action( 'phpmailer_init', $embed_logo );
	remove_filter( 'wp_mail_from', $from_filter );

	wp_safe_redirect( add_query_arg( 'wcph_il_email_sent', $sent ? 'success' : 'error', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_wcph_il_email_letter', 'wcph_il_handle_email_request' );
add_action( 'admin_post_wcph_il_email_letter', 'wcph_il_handle_email_request' );
