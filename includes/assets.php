<?php
/**
 * Conditional asset loading: only enqueue the invitation letter's
 * CSS/JS on pages that actually contain the [wcph_invitation] shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers styles/scripts early so they're available to enqueue.
 */
function wcph_il_register_assets() {
	wp_register_style(
		'wcph-invitation-letter',
		WCPH_IL_URL . 'assets/invitation-letter.css',
		array(),
		WCPH_IL_VERSION
	);

	wp_register_script(
		'wcph-invitation-letter',
		WCPH_IL_URL . 'assets/invitation-letter.js',
		array(),
		WCPH_IL_VERSION,
		true
	);
}
add_action( 'init', 'wcph_il_register_assets' );

/**
 * Enqueues the invitation letter assets only on posts/pages whose
 * content contains the shortcode, so we don't load CSS/JS site-wide.
 */
function wcph_il_maybe_enqueue_assets() {
	if ( ! is_singular() ) {
		return;
	}

	if ( ! has_shortcode( get_post()->post_content, 'wcph_invitation' ) ) {
		return;
	}

	wp_enqueue_style( 'wcph-invitation-letter' );
	wp_enqueue_script( 'wcph-invitation-letter' );
}
add_action( 'wp_enqueue_scripts', 'wcph_il_maybe_enqueue_assets' );
