/* global jQuery, autophotosWc */
( function ( $ ) {
	'use strict';

	function showMessage( text, isError ) {
		var $msg = $( '#autophotos-wc-message' );
		$msg
			.text( text )
			.removeClass( 'hidden notice notice-success notice-error' )
			.addClass( 'notice ' + ( isError ? 'notice-error' : 'notice-success' ) )
			.show();
	}

	$( function () {
		$( '#autophotos-wc-connect' ).on( 'click', function () {
			var $btn = $( this );
			var code = $.trim( $( '#autophotos-wc-code' ).val() );
			if ( ! code ) {
				showMessage( autophotosWc.i18n.enterCode, true );
				return;
			}
			$btn.prop( 'disabled', true ).text( autophotosWc.i18n.connecting );
			$.post( autophotosWc.ajaxUrl, {
				action: 'autophotos_wc_connect',
				nonce: autophotosWc.nonce,
				code: code,
			} )
				.done( function ( res ) {
					if ( res && res.success ) {
						window.location.reload();
					} else {
						showMessage( ( res && res.data && res.data.message ) || 'Error', true );
						$btn.prop( 'disabled', false ).text( 'Connect' );
					}
				} )
				.fail( function () {
					showMessage( 'Network error', true );
					$btn.prop( 'disabled', false ).text( 'Connect' );
				} );
		} );

		$( '#autophotos-wc-disconnect' ).on( 'click', function () {
			var $btn = $( this );
			$btn.prop( 'disabled', true ).text( autophotosWc.i18n.disconnecting );
			$.post( autophotosWc.ajaxUrl, {
				action: 'autophotos_wc_disconnect',
				nonce: autophotosWc.nonce,
			} ).always( function () {
				window.location.reload();
			} );
		} );
	} );
} )( jQuery );
