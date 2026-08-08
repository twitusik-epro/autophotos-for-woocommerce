<?php
// Runs only on actual uninstall (delete), not deactivation — WordPress
// guarantees this file is only loaded via the uninstall hook mechanism, but
// the WP.org guideline is to double-check WP_UNINSTALL_PLUGIN anyway.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Best-effort: revoke the WooCommerce REST API key this plugin created, so
// uninstalling doesn't leave a live, unused credential behind. Silently
// skipped if WooCommerce's table is already gone (e.g. WooCommerce was
// removed first).
global $wpdb;
$key_id = get_option( 'autophotos_wc_key_id' );
if ( $key_id ) {
	$table = $wpdb->prefix . 'woocommerce_api_keys';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$wpdb->delete( $table, array( 'key_id' => (int) $key_id ) );
	}
}

delete_option( 'autophotos_wc_connected' );
delete_option( 'autophotos_wc_connected_at' );
delete_option( 'autophotos_wc_key_id' );
