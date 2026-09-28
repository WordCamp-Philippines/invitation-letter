<?php
/**
 * Prepares a mail-safe version of the event logo for embedding in the
 * "Email me a copy" message. Most email clients render WebP incorrectly
 * (or not at all) and many ignore CSS sizing on <img>, so we convert to
 * PNG and always report explicit pixel dimensions for the <img> tag.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a mail-safe logo file (PNG or JPEG) plus its pixel dimensions.
 * Converted PNGs are cached on disk so repeat sends don't re-decode the
 * source image every time.
 *
 * @param int $logo_id Attachment ID.
 * @return array{path:string,width:int,height:int,mime:string}|null
 */
function wcph_il_get_mail_safe_logo( $logo_id ) {
	$logo_id = (int) $logo_id;
	if ( ! $logo_id ) {
		return null;
	}

	$full_path = get_attached_file( $logo_id );
	if ( ! $full_path || ! file_exists( $full_path ) ) {
		return null;
	}

	// Prefer the "medium" sub-size over the original — logos don't need
	// to be huge in an email, and a smaller file is safer for deliverability.
	$source_path = $full_path;
	$mime        = get_post_mime_type( $logo_id );
	$metadata    = wp_get_attachment_metadata( $logo_id );

	if ( ! empty( $metadata['sizes']['medium']['file'] ) ) {
		$candidate = trailingslashit( dirname( $full_path ) ) . $metadata['sizes']['medium']['file'];
		if ( file_exists( $candidate ) ) {
			$source_path = $candidate;
			$mime        = ! empty( $metadata['sizes']['medium']['mime-type'] ) ? $metadata['sizes']['medium']['mime-type'] : $mime;
		}
	}

	// PNG/JPEG can be embedded as-is.
	if ( in_array( $mime, array( 'image/png', 'image/jpeg' ), true ) ) {
		$size = getimagesize( $source_path );
		if ( ! $size ) {
			return null;
		}
		return array(
			'path'   => $source_path,
			'width'  => (int) $size[0],
			'height' => (int) $size[1],
			'mime'   => $mime,
		);
	}

	if ( 'image/webp' !== $mime || ! function_exists( 'imagecreatefromwebp' ) ) {
		return null;
	}

	return wcph_il_convert_webp_to_cached_png( $logo_id, $source_path );
}

/**
 * Converts a WebP image to PNG and caches the result in the uploads dir
 * (keyed by attachment ID + source mtime) so we don't reconvert on every send.
 *
 * @param int    $logo_id     Attachment ID (used for the cache filename).
 * @param string $source_path Path to the source WebP file.
 * @return array{path:string,width:int,height:int,mime:string}|null
 */
function wcph_il_convert_webp_to_cached_png( $logo_id, $source_path ) {
	$upload_dir = wp_upload_dir();
	if ( ! empty( $upload_dir['error'] ) ) {
		return null;
	}

	$cache_dir = trailingslashit( $upload_dir['basedir'] ) . 'wcph-il-cache/';
	if ( ! file_exists( $cache_dir ) ) {
		wp_mkdir_p( $cache_dir );
	}

	$cache_file = $cache_dir . 'logo-' . $logo_id . '-' . filemtime( $source_path ) . '.png';

	if ( ! file_exists( $cache_file ) ) {
		$image = @imagecreatefromwebp( $source_path );
		if ( ! $image ) {
			return null;
		}
		imagesavealpha( $image, true );
		$saved = imagepng( $image, $cache_file );
		imagedestroy( $image );
		if ( ! $saved ) {
			return null;
		}
	}

	$size = getimagesize( $cache_file );
	if ( ! $size ) {
		return null;
	}

	return array(
		'path'   => $cache_file,
		'width'  => (int) $size[0],
		'height' => (int) $size[1],
		'mime'   => 'image/png',
	);
}
