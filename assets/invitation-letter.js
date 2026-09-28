/**
 * WCPH Invitation Letter — print button behavior.
 * Progressive enhancement: the button works via inline onclick as a fallback,
 * this script just keeps behavior centralized for future enhancements.
 */
( function () {
	document.addEventListener( 'DOMContentLoaded', function () {
		var buttons = document.querySelectorAll( '.wcph-il-print-btn' );
		buttons.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				window.print();
			} );
		} );
	} );
} )();
