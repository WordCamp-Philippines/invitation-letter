<?php
/**
 * Builds a self-contained, printable HTML version of the invitation letter
 * for use as the body of the "Email me a copy" message. Styles are inlined
 * because most email clients strip <style> blocks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds inline "style" margins to the block-level tags of the merged letter
 * body (paragraphs, lists), since most email clients strip <style> blocks
 * and only honor inline styles. Also preserves any text-align the admin
 * applied in the TinyMCE editor (e.g. justify) by merging into the existing
 * inline style rather than overwriting it.
 *
 * @param string $html Rich HTML from wcph_il_render_letter_field( 'letter_body', ... ).
 * @return string HTML with inline spacing styles added.
 */
function wcph_il_inline_paragraph_spacing( $html ) {
	foreach ( array( 'p', 'ul', 'ol' ) as $tag ) {
		$html = preg_replace_callback(
			'/<' . $tag . '((?:\s[^>]*)?)>/i',
			static function ( $matches ) use ( $tag ) {
				$attrs = $matches[1];
				if ( preg_match( '/style\s*=\s*"([^"]*)"/i', $attrs, $style_match ) ) {
					$new_style = rtrim( $style_match[1], '; ' ) . ';margin:0 0 0.85rem;';
					$attrs     = preg_replace( '/style\s*=\s*"[^"]*"/i', 'style="' . esc_attr( $new_style ) . '"', $attrs );
				} else {
					$attrs .= ' style="margin:0 0 0.85rem;"';
				}
				return '<' . $tag . $attrs . '>';
			},
			$html
		);
	}
	return $html;
}

/**
 * Builds the invitation letter email HTML and returns it as a string.
 *
 * @param string      $attendee Attendee full name.
 * @param array       $event    Event details, as returned by wcph_il_get_event_details().
 * @param string|null $logo_cid Content-ID of an inline-attached logo image, if any (see wcph_il_get_mail_safe_logo()).
 * @param array|null  $logo     Mail-safe logo info (path/width/height/mime) from wcph_il_get_mail_safe_logo(), used for sizing.
 * @return string Full HTML document for the email body.
 */
function wcph_il_build_letter_email_html( $attendee, $event, $logo_cid = null, $logo = null ) {
	$greeting        = $attendee ? sprintf( 'Dear %s,', esc_html( $attendee ) ) : 'Dear Attendee,';
	$letterhead_line = ! empty( $event['letterhead_heading'] ) ? $event['letterhead_heading'] : $event['name'];
	$letter_date     = date_i18n( get_option( 'date_format' ) );

	$letter_body = wcph_il_inline_paragraph_spacing( wcph_il_render_letter_field( 'letter_body', $event ) );

	$logo_html = '';
	if ( $logo_cid && $logo ) {
		// Explicit pixel width/height (not just CSS) so clients that ignore
		// CSS on <img> — Outlook chief among them — still render it undistorted.
		$target_height = 56;
		$target_width  = (int) round( ( $logo['width'] / $logo['height'] ) * $target_height );
		$logo_html     = sprintf(
			'<img src="cid:%1$s" width="%2$d" height="%3$d" alt="" style="display:block;margin:0 auto 12px;border:0;width:%2$dpx;height:%3$dpx;" />',
			esc_attr( $logo_cid ),
			$target_width,
			$target_height
		);
	}

	if ( ! empty( $event['signatory_name'] ) ) {
		$signoff_name_html = '<strong>' . esc_html( $event['signatory_name'] ) . '</strong><br />' . esc_html( $event['signatory_title'] ) . '<br />';
	} else {
		$signoff_name_html = '<strong>' . esc_html( $event['signatory_title'] ) . '</strong><br />';
	}

	ob_start();
	?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php echo esc_html( $letterhead_line ); ?></title>
</head>
<body style="margin:0;padding:24px 12px;background:#eef1f5;font-family:Georgia,'Times New Roman',serif;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;margin:0 auto;background:#fffdf8;border:1px solid #d8cdb8;">
		<tr>
			<td style="padding:2.25rem 2.5rem;">
				<div style="text-align:center;margin-bottom:1.25rem;">
					<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url above. ?>
					<p style="font-family:'Times New Roman',Times,Georgia,serif;font-size:1.85rem;font-weight:700;color:#1e3a5f;margin:0;">
						<?php echo esc_html( $letterhead_line ); ?>
					</p>
				</div>

				<p style="text-align:right;font-size:1rem;color:#5a5347;margin:0 0 1.25rem;">
					<?php echo esc_html( $letter_date ); ?>
				</p>

				<div style="font-size:1.15rem;line-height:1.6;color:#2c2a26;">
					<p style="font-weight:700;color:#1e3a5f;margin:0 0 0.85rem;"><?php echo esc_html( $greeting ); ?></p>

					<div><?php echo $letter_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized via wp_kses_post() in wcph_il_render_letter_field(), inline-styled via wcph_il_inline_paragraph_spacing(). ?></div>

					<p style="margin-top:1.25rem;">
						Warm regards,<br />
						<?php echo $signoff_name_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_html above. ?>
						<a href="<?php echo esc_url( $event['website'] ); ?>" style="color:#1e3a5f;"><?php echo esc_html( $event['website'] ); ?></a>
					</p>
				</div>
			</td>
		</tr>
	</table>

	<p style="max-width:680px;margin:1rem auto 0;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:0.8rem;color:#7a7669;">
		Tip: use your email app's Print option to print or save this letter as a PDF.
	</p>
</body>
</html>
	<?php
	return (string) ob_get_clean();
}
