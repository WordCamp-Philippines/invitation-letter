<?php
/**
 * Invitation letter template — formal business-letter layout.
 *
 * Available variables: $attendee, $attendee_email, $has_name, $event, $benefits
 *
 * @var string $attendee
 * @var string $attendee_email
 * @var bool   $has_name
 * @var array  $event
 * @var array  $benefits
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$greeting = $attendee ? sprintf(
	/* translators: %s: attendee name */
	esc_html__( 'Dear %s,', 'wcph-invitation-letter' ),
	esc_html( $attendee )
) : esc_html__( 'Dear Attendee,', 'wcph-invitation-letter' );

// If a name was typed but the email was missing/invalid, surface an inline notice and keep the name filled in.
$show_email_notice = ( '' !== $attendee ) && ! $has_name;

$letterhead_line = ! empty( $event['letterhead_heading'] ) ? $event['letterhead_heading'] : $event['name'];
$letter_date     = date_i18n( get_option( 'date_format' ) );
?>
<div class="wcph-il-wrapper">

	<?php if ( ! $has_name ) : ?>
	<form class="wcph-il-name-form" method="get">
		<label for="wcph-il-attendee-name"><?php esc_html_e( 'Enter your name and email to generate your personalized invitation letter:', 'wcph-invitation-letter' ); ?></label>
		<?php if ( $show_email_notice ) : ?>
			<p class="wcph-il-form-notice"><?php esc_html_e( 'Please enter a valid email address to continue.', 'wcph-invitation-letter' ); ?></p>
		<?php endif; ?>
		<div class="wcph-il-name-form-row">
			<input type="text" id="wcph-il-attendee-name" name="attendee" value="<?php echo esc_attr( $attendee ); ?>" placeholder="<?php esc_attr_e( 'Your full name', 'wcph-invitation-letter' ); ?>" required />
			<input type="email" id="wcph-il-attendee-email" name="attendee_email" value="<?php echo esc_attr( $attendee_email ); ?>" placeholder="<?php esc_attr_e( 'Your email address', 'wcph-invitation-letter' ); ?>" required />
			<button type="submit" class="wcph-il-generate-btn"><?php esc_html_e( 'Generate My Invitation', 'wcph-invitation-letter' ); ?></button>
		</div>
	</form>
	<?php endif; ?>

	<div class="wcph-il-actions" <?php echo $has_name ? '' : 'style="display:none;"'; ?>>
		<button type="button" class="wcph-il-print-btn">
			<?php esc_html_e( 'Print / Save as PDF', 'wcph-invitation-letter' ); ?>
		</button>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wcph-il-email-form">
			<input type="hidden" name="action" value="wcph_il_email_letter" />
			<input type="hidden" name="attendee" value="<?php echo esc_attr( $attendee ); ?>" />
			<input type="hidden" name="attendee_email" value="<?php echo esc_attr( $attendee_email ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( remove_query_arg( 'wcph_il_email_sent' ) ); ?>" />
			<?php wp_nonce_field( 'wcph_il_email_letter', 'wcph_il_email_nonce' ); ?>
			<button type="submit" class="wcph-il-email-btn"><?php esc_html_e( 'Email Me a Copy', 'wcph-invitation-letter' ); ?></button>
		</form>
	</div>

	<?php
	if ( isset( $_GET['wcph_il_email_sent'] ) ) :
		$email_status = sanitize_text_field( wp_unslash( $_GET['wcph_il_email_sent'] ) );
		if ( 'success' === $email_status ) :
			?>
			<p class="wcph-il-email-status wcph-il-email-status--success"><?php esc_html_e( 'A printable copy of your invitation letter has been emailed to you.', 'wcph-invitation-letter' ); ?></p>
			<?php
		elseif ( 'error' === $email_status ) :
			?>
			<p class="wcph-il-email-status wcph-il-email-status--error"><?php esc_html_e( 'Sorry, we could not send the email. Please try again later.', 'wcph-invitation-letter' ); ?></p>
			<?php
		endif;
	endif;
	?>

	<?php if ( $has_name ) : ?>
	<p class="wcph-il-change-name">
		<a href="<?php echo esc_url( remove_query_arg( array( 'attendee', 'attendee_email' ) ) ); ?>">&larr; <?php esc_html_e( 'Generate for a different name', 'wcph-invitation-letter' ); ?></a>
	</p>
	<?php endif; ?>

	<article class="wcph-il-letter" <?php echo $has_name ? '' : 'style="display:none;"'; ?>>
		<header class="wcph-il-letterhead">
			<?php if ( ! empty( $event['logo_id'] ) ) : ?>
				<div class="wcph-il-logo">
					<?php echo wp_get_attachment_image( $event['logo_id'], 'medium', false, array( 'alt' => '' ) ); ?>
				</div>
			<?php endif; ?>
			<p class="wcph-il-letterhead-heading"><?php echo esc_html( $letterhead_line ); ?></p>
		</header>

		<p class="wcph-il-date"><?php echo esc_html( $letter_date ); ?></p>

		<div class="wcph-il-body">
			<p class="wcph-il-greeting"><?php echo $greeting; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped above. */ ?></p>

			<div class="wcph-il-rich wcph-il-letter-body"><?php echo wcph_il_render_letter_field( 'letter_body', $event ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized via wp_kses_post() in wcph_il_render_letter_field(). */ ?></div>

			<p class="wcph-il-signoff">
				<?php esc_html_e( 'Warm regards,', 'wcph-invitation-letter' ); ?><br />
				<?php if ( ! empty( $event['signatory_name'] ) ) : ?>
					<strong><?php echo esc_html( $event['signatory_name'] ); ?></strong><br />
					<?php echo esc_html( $event['signatory_title'] ); ?><br />
				<?php else : ?>
					<strong><?php echo esc_html( $event['signatory_title'] ); ?></strong><br />
				<?php endif; ?>
				<a href="<?php echo esc_url( $event['website'] ); ?>"><?php echo esc_html( $event['website'] ); ?></a>
			</p>
		</div>
	</article>
</div>
