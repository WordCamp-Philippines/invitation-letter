<?php
/**
 * Admin page: lists everyone who has generated an invitation letter.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Handle "Clear log" action.
if ( isset( $_POST['wcph_il_clear_log_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['wcph_il_clear_log_nonce'] ), 'wcph_il_clear_log' ) ) {
	wcph_il_clear_invitation_log();
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'The invitation log has been cleared.', 'wcph-invitation-letter' ) . '</p></div>';
}

$per_page   = 20;
$paged      = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$total      = wcph_il_count_invitation_log();
$total_pages = (int) ceil( $total / $per_page );
$entries    = wcph_il_get_invitation_log( $per_page, $paged );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Invitation Log', 'wcph-invitation-letter' ); ?></h1>
	<p><?php esc_html_e( 'Everyone who has entered their name and email to generate an invitation letter.', 'wcph-invitation-letter' ); ?></p>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'wcph-invitation-letter' ); ?></th>
				<th><?php esc_html_e( 'Email', 'wcph-invitation-letter' ); ?></th>
				<th><?php esc_html_e( 'Generated At', 'wcph-invitation-letter' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( $entries ) : ?>
				<?php foreach ( $entries as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( $entry->full_name ); ?></td>
						<td><?php echo esc_html( $entry->email ); ?></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $entry->generated_at ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="3"><?php esc_html_e( 'No invitation letters have been generated yet.', 'wcph-invitation-letter' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav">
			<div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'current'   => $paged,
							'total'     => $total_pages,
							'prev_text' => __( '&laquo;', 'wcph-invitation-letter' ),
							'next_text' => __( '&raquo;', 'wcph-invitation-letter' ),
						)
					)
				);
				?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $total > 0 ) : ?>
		<form method="post" style="margin-top:1rem;" onsubmit="return confirm('<?php echo esc_js( __( 'Are you sure you want to permanently clear the invitation log?', 'wcph-invitation-letter' ) ); ?>');">
			<?php wp_nonce_field( 'wcph_il_clear_log', 'wcph_il_clear_log_nonce' ); ?>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'Clear Log', 'wcph-invitation-letter' ); ?></button>
		</form>
	<?php endif; ?>
</div>
