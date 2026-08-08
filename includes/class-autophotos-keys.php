<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generates a WooCommerce REST API key pair using WooCommerce's own helpers
 * (wc_rand_hash()/wc_api_hash(), loaded by WooCommerce core) and inserts it
 * into the wp_woocommerce_api_keys table with the exact same shape
 * WooCommerce's own admin "Add key" screen produces (see
 * includes/admin/class-wc-admin-api-keys.php in WooCommerce core) — this
 * plugin does not reimplement WooCommerce's hashing/storage format, it just
 * drives it programmatically instead of through the settings UI.
 */
class Autophotos_WC_Keys {

	/**
	 * Creates a fresh read_write key pair owned by the current admin user
	 * and returns array( $consumer_key, $consumer_secret ) — the raw
	 * consumer_key is only ever available at creation time (WooCommerce
	 * stores it hashed), same as when a user clicks "Generate API key" by
	 * hand.
	 */
	public static function create_key_pair() {
		global $wpdb;

		$consumer_key    = 'ck_' . wc_rand_hash();
		$consumer_secret = 'cs_' . wc_rand_hash();

		$wpdb->insert(
			$wpdb->prefix . 'woocommerce_api_keys',
			array(
				'user_id'         => get_current_user_id(),
				'description'     => sprintf(
					/* translators: %s: site date/time the key was created */
					__( 'AutoPhotos.ai (connected %s)', 'autophotos-woocommerce' ),
					current_time( 'mysql' )
				),
				'permissions'     => 'read_write',
				'consumer_key'    => wc_api_hash( $consumer_key ),
				'consumer_secret' => $consumer_secret,
				'truncated_key'   => substr( $consumer_key, -7 ),
			)
		);

		return array( $consumer_key, $consumer_secret, $wpdb->insert_id );
	}

	/**
	 * Best-effort revocation of the key row this plugin created, called on
	 * disconnect. Not a hard requirement for correctness (an orphaned key
	 * with no server holding it is inert), but leaving unused live keys
	 * around after a user explicitly disconnects would be a needless
	 * standing credential — same reasoning as revoking an OAuth token on
	 * "disconnect" elsewhere.
	 */
	public static function revoke_key( $key_id ) {
		if ( ! $key_id ) {
			return;
		}
		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix . 'woocommerce_api_keys',
			array( 'key_id' => (int) $key_id )
		);
	}
}
