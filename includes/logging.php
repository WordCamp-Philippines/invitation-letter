<?php
/**
 * Logs every attendee who successfully generates an invitation letter
 * (i.e. supplied a valid name + email and unlocked the letter), so an
 * admin can review who has requested one.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WCPH_IL_DB_VERSION', '1.0' );

/**
 * Returns the fully-prefixed log table name.
 *
 * @return string
 */
function wcph_il_log_table() {
	global $wpdb;
	return $wpdb->prefix . 'wcph_il_invitations';
}

/**
 * Creates the log table if it doesn't exist yet, or is out of date.
 * Safe to call on every request — dbDelta() only does work when needed.
 */
function wcph_il_maybe_create_log_table() {
	if ( get_option( 'wcph_il_db_version' ) === WCPH_IL_DB_VERSION ) {
		return;
	}

	global $wpdb;
	$table           = wcph_il_log_table();
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		full_name    VARCHAR(255) NOT NULL,
		email        VARCHAR(255) NOT NULL,
		generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY          email (email),
		KEY          generated_at (generated_at)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'wcph_il_db_version', WCPH_IL_DB_VERSION );
}
add_action( 'plugins_loaded', 'wcph_il_maybe_create_log_table' );
register_activation_hook( WCPH_IL_PATH . 'wcph-invitation-letter.php', 'wcph_il_maybe_create_log_table' );

/**
 * Records a successful invitation-letter generation, skipping duplicate
 * inserts if the same email generated one in the last minute (avoids
 * flooding the log from page refreshes).
 *
 * @param string $name  Attendee full name.
 * @param string $email Attendee email address.
 */
function wcph_il_log_invitation( $name, $email ) {
	global $wpdb;
	$table = wcph_il_log_table();

	$recent = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id FROM {$table} WHERE email = %s AND generated_at >= %s ORDER BY id DESC LIMIT 1",
			$email,
			gmdate( 'Y-m-d H:i:s', time() - MINUTE_IN_SECONDS )
		)
	);

	if ( $recent ) {
		return;
	}

	$wpdb->insert(
		$table,
		array(
			'full_name'    => $name,
			'email'        => $email,
			'generated_at' => current_time( 'mysql' ),
		),
		array( '%s', '%s', '%s' )
	);
}

/**
 * Returns a page of logged invitation entries, most recent first.
 *
 * @param int $per_page Entries per page.
 * @param int $paged    1-indexed page number.
 * @return object[]
 */
function wcph_il_get_invitation_log( $per_page = 20, $paged = 1 ) {
	global $wpdb;
	$table  = wcph_il_log_table();
	$offset = max( 0, ( $paged - 1 ) * $per_page );

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT full_name, email, generated_at FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		)
	);
}

/**
 * Returns the total number of logged entries.
 *
 * @return int
 */
function wcph_il_count_invitation_log() {
	global $wpdb;
	$table = wcph_il_log_table();
	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
}

/**
 * Deletes all logged entries. Used by the "Clear log" admin action.
 */
function wcph_il_clear_invitation_log() {
	global $wpdb;
	$table = wcph_il_log_table();
	$wpdb->query( "TRUNCATE TABLE {$table}" );
}
