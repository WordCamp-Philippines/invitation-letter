<?php
/**
 * Settings page markup for Settings > Invitation Letter.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = wcph_il_get_settings();
$logo_url = $settings['logo_id'] ? wp_get_attachment_image_url( $settings['logo_id'], 'medium' ) : '';
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Invitation Letter Settings', 'wcph-invitation-letter' ); ?></h1>
	<p><?php esc_html_e( 'Edit the event details, letterhead, and benefits shown on the invitation letter. Use the shortcode [wcph_invitation] on any page to display it.', 'wcph-invitation-letter' ); ?></p>

	<form method="post" action="options.php">
		<?php settings_fields( 'wcph_il_settings_group' ); ?>

		<h2><?php esc_html_e( 'Letterhead', 'wcph-invitation-letter' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="wcph_il_logo"><?php esc_html_e( 'Logo', 'wcph-invitation-letter' ); ?></label></th>
				<td>
					<input type="hidden" id="wcph_il_logo" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[logo_id]" value="<?php echo esc_attr( $settings['logo_id'] ); ?>" />
					<div id="wcph-il-logo-preview" style="margin-bottom:10px;">
						<?php if ( $logo_url ) : ?>
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="" style="max-width:200px;height:auto;display:block;" />
						<?php endif; ?>
					</div>
					<button type="button" class="button" id="wcph-il-logo-select"><?php esc_html_e( 'Select Logo', 'wcph-invitation-letter' ); ?></button>
					<button type="button" class="button" id="wcph-il-logo-remove" <?php echo $logo_url ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Remove', 'wcph-invitation-letter' ); ?></button>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wcph_il_letterhead_heading"><?php esc_html_e( 'Letterhead heading', 'wcph-invitation-letter' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="wcph_il_letterhead_heading" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[letterhead_heading]" value="<?php echo esc_attr( $settings['letterhead_heading'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. WordPress Foundation Philippines', 'wcph-invitation-letter' ); ?>" />
					<p class="description"><?php esc_html_e( 'Optional line shown above the event name. Leave blank to omit.', 'wcph-invitation-letter' ); ?></p>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Event Details', 'wcph-invitation-letter' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="wcph_il_event_name"><?php esc_html_e( 'Event name', 'wcph-invitation-letter' ); ?></label></th>
				<td><input type="text" class="regular-text" id="wcph_il_event_name" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[event_name]" value="<?php echo esc_attr( $settings['event_name'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="wcph_il_event_theme"><?php esc_html_e( 'Event theme', 'wcph-invitation-letter' ); ?></label></th>
				<td><input type="text" class="regular-text" id="wcph_il_event_theme" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[event_theme]" value="<?php echo esc_attr( $settings['event_theme'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="wcph_il_event_dates"><?php esc_html_e( 'Dates', 'wcph-invitation-letter' ); ?></label></th>
				<td><input type="text" class="regular-text" id="wcph_il_event_dates" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[event_dates]" value="<?php echo esc_attr( $settings['event_dates'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="wcph_il_event_venue"><?php esc_html_e( 'Venue', 'wcph-invitation-letter' ); ?></label></th>
				<td><input type="text" class="regular-text" id="wcph_il_event_venue" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[event_venue]" value="<?php echo esc_attr( $settings['event_venue'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="wcph_il_event_city"><?php esc_html_e( 'City / Location', 'wcph-invitation-letter' ); ?></label></th>
				<td><input type="text" class="regular-text" id="wcph_il_event_city" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[event_city]" value="<?php echo esc_attr( $settings['event_city'] ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="wcph_il_event_website"><?php esc_html_e( 'Website', 'wcph-invitation-letter' ); ?></label></th>
				<td><input type="url" class="regular-text" id="wcph_il_event_website" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[event_website]" value="<?php echo esc_attr( $settings['event_website'] ); ?>" /></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Signatory', 'wcph-invitation-letter' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="wcph_il_signatory_name"><?php esc_html_e( 'Signed by (name)', 'wcph-invitation-letter' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="wcph_il_signatory_name" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[signatory_name]" value="<?php echo esc_attr( $settings['signatory_name'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Maria Cruz', 'wcph-invitation-letter' ); ?>" />
					<p class="description"><?php esc_html_e( 'Optional. Leave blank to omit an individual name.', 'wcph-invitation-letter' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wcph_il_signatory_title"><?php esc_html_e( 'Title / Organizing team', 'wcph-invitation-letter' ); ?></label></th>
				<td><input type="text" class="regular-text" id="wcph_il_signatory_title" name="<?php echo esc_attr( WCPH_IL_OPTION_KEY ); ?>[signatory_title]" value="<?php echo esc_attr( $settings['signatory_title'] ); ?>" /></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Letter Content', 'wcph-invitation-letter' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Write the full body of the letter as one document — opening, benefits, and closing all in the same editor. You can use these merge tags anywhere in the text: {event_name}, {event_theme}, {event_dates}, {event_venue}, {event_city}, {event_website}.', 'wcph-invitation-letter' ); ?></p>
		<?php
		wp_editor(
			$settings['letter_body'],
			'wcph_il_letter_body',
			array(
				'textarea_name' => WCPH_IL_OPTION_KEY . '[letter_body]',
				'textarea_rows' => 18,
				'media_buttons' => false,
				'teeny'         => true,
			)
		);
		?>

		<?php submit_button(); ?>
	</form>
</div>
