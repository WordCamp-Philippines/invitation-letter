<?php
/**
 * Plugin Name:       WCPH Invitation Letter
 * Description:       Generates a personalized, printable invitation letter for WordCamp Philippines 2026, with event details and attendee benefits.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            WordCamp Philippines
 * Text Domain:       wcph-invitation-letter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'WCPH_IL_VERSION', '1.0.0' );
define( 'WCPH_IL_PATH', plugin_dir_path( __FILE__ ) );
define( 'WCPH_IL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin pieces. Each include focuses on one responsibility:
 * - settings.php  : admin settings (event details, logo, letterhead, signatory, benefits)
 * - assets.php    : conditional front-end CSS/JS enqueueing
 * - shortcode.php : the [wcph_invitation] shortcode + template rendering + name-form handling
 */
require_once WCPH_IL_PATH . 'includes/logging.php';
require_once WCPH_IL_PATH . 'includes/settings.php';
require_once WCPH_IL_PATH . 'includes/assets.php';
require_once WCPH_IL_PATH . 'includes/shortcode.php';
require_once WCPH_IL_PATH . 'includes/block.php';
require_once WCPH_IL_PATH . 'includes/email.php';
