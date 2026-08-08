<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Autophotos_WC_Ajax {

	public static function init() {
		add_action( 'wp_ajax_autophotos_wc_connect', array( __CLASS__, 'connect' ) );
		add_action( 'wp_ajax_autophotos_wc_disconnect', array( __CLASS__, 'disconnect' ) );
	}

	private static function check_request() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Not allowed.', 'autophotos-woocommerce' ) ), 403 );
		}
		check_ajax_referer( 'autophotos_wc_nonce', 'nonce' );
	}

	/**
	 * Generates a fresh WC REST API key pair locally, then hands it to
	 * AutoPhotos's /api/woocommerce/pair endpoint together with the
	 * one-time pairing code the user pasted in — mirrors exactly what a
	 * user does by hand today (WooCommerce -> Settings -> Advanced -> REST
	 * API -> "Add key", then paste the two values into the AutoPhotos
	 * connect form), just without the manual key-creation step.
	 */
	public static function connect() {
		self::check_request();

		$code = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
		if ( '' === $code ) {
			wp_send_json_error( array( 'message' => __( 'Missing pairing code.', 'autophotos-woocommerce' ) ), 400 );
		}

		list( $consumer_key, $consumer_secret, $key_id ) = Autophotos_WC_Keys::create_key_pair();

		$response = wp_remote_post(
			trailingslashit( autophotos_wc_api_base() ) . 'api/woocommerce/pair',
			array(
				'timeout' => 20,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'code'             => $code,
						'store_url'        => home_url(),
						'consumer_key'     => $consumer_key,
						'consumer_secret'  => $consumer_secret,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			// Pairing failed — clean up the key we just created so we don't
			// leave a dangling, never-used credential behind.
			Autophotos_WC_Keys::revoke_key( $key_id );
			wp_send_json_error( array( 'message' => $response->get_error_message() ), 502 );
		}

		$code_status = wp_remote_retrieve_response_code( $response );
		if ( $code_status >= 400 ) {
			Autophotos_WC_Keys::revoke_key( $key_id );
			$body    = json_decode( wp_remote_retrieve_body( $response ), true );
			$message = is_array( $body ) && isset( $body['detail'] )
				? ( is_string( $body['detail'] ) ? $body['detail'] : wp_json_encode( $body['detail'] ) )
				: __( 'AutoPhotos rejected the pairing request.', 'autophotos-woocommerce' );
			wp_send_json_error( array( 'message' => $message ), $code_status );
		}

		update_option( Autophotos_WC_Settings::OPTION_CONNECTED, true );
		update_option( Autophotos_WC_Settings::OPTION_CONNECTED_AT, current_time( 'mysql' ) );
		update_option( Autophotos_WC_Settings::OPTION_KEY_ID, $key_id );

		wp_send_json_success();
	}

	public static function disconnect() {
		self::check_request();

		$key_id = get_option( Autophotos_WC_Settings::OPTION_KEY_ID );
		Autophotos_WC_Keys::revoke_key( $key_id );

		delete_option( Autophotos_WC_Settings::OPTION_CONNECTED );
		delete_option( Autophotos_WC_Settings::OPTION_CONNECTED_AT );
		delete_option( Autophotos_WC_Settings::OPTION_KEY_ID );

		wp_send_json_success();
	}
}
