/**
 * WCPH Invitation Letter — settings page logo picker using the WP media modal.
 */
( function ( $ ) {
	$( document ).ready( function () {
		var frame;
		var $selectBtn = $( '#wcph-il-logo-select' );
		var $removeBtn = $( '#wcph-il-logo-remove' );
		var $input = $( '#wcph_il_logo' );
		var $preview = $( '#wcph-il-logo-preview' );

		$selectBtn.on( 'click', function ( e ) {
			e.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: wcphIlAdmin.title,
				button: { text: wcphIlAdmin.buttonText },
				library: { type: 'image' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$input.val( attachment.id );
				var previewUrl = attachment.sizes && attachment.sizes.medium
					? attachment.sizes.medium.url
					: attachment.url;
				$preview.html( '<img src="' + previewUrl + '" alt="" style="max-width:200px;height:auto;display:block;" />' );
				$removeBtn.show();
			} );

			frame.open();
		} );

		$removeBtn.on( 'click', function ( e ) {
			e.preventDefault();
			$input.val( '' );
			$preview.empty();
			$removeBtn.hide();
		} );
	} );
} )( jQuery );
