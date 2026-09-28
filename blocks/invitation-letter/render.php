<?php
/**
 * Server-side render for the wcph/invitation-letter block.
 *
 * @var array $attributes Block attributes (letterBody).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style( 'wcph-invitation-letter' );
wp_enqueue_script( 'wcph-invitation-letter' );

$letter_body = isset( $attributes['letterBody'] ) ? $attributes['letterBody'] : '';

// Apply the block's alignment toolbar choice (including "justify") as an
// inline style on the override so it survives into the rendered letter,
// the same way core text blocks apply text-align.
if ( ! empty( $attributes['textAlign'] ) && '' !== trim( $letter_body ) ) {
	$letter_body = '<div style="text-align:' . esc_attr( $attributes['textAlign'] ) . ';">' . $letter_body . '</div>';
}

$content_overrides = array(
	'letter_body' => $letter_body,
);

echo wcph_il_render_letter_content( array(), $content_overrides ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- template output is already escaped/sanitized internally.
