<?php
/**
 * Plugin Name:       AutoPhotos for WooCommerce – AI Product Photo Editor
 * Plugin URI:        https://autophotos.ai/integrations/woocommerce/
 * Description:       Connects your store to your AutoPhotos.ai account so you can edit product photos (background removal, drop shadow, color correction, AI upscaling, auto-crop, recoloring) from the AutoPhotos panel, with results written straight back to your product gallery.
 * Version:           1.0.4
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 7.0
 * Requires Plugins:  woocommerce
 * Author:            AutoPhotos.ai
 * Author URI:        https://autophotos.ai/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       autophotos-for-woocommerce
 * Domain Path:       /languages
 *
 * This plugin does not process photos itself — it is a thin bridge that
 * generates a WooCommerce REST API key pair for your store and hands it to
 * your AutoPhotos.ai account (identified by a one-time pairing code you
 * paste in below), the same key pair you could otherwise create by hand
 * under WooCommerce -> Settings -> Advanced -> REST API. All photo
 * processing continues to happen in your AutoPhotos panel at
 * https://autophotos.ai/integrations/woocommerce/ using your own
 * AutoPhotos credit balance — this plugin only removes the manual key
 * copy-paste step.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'AUTOPHOTOS_WC_VERSION', '1.0.4' );
define( 'AUTOPHOTOS_WC_PLUGIN_FILE', __FILE__ );
define( 'AUTOPHOTOS_WC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Base URL of the AutoPhotos API. Filterable only so the same codebase can
 * be pointed at a staging backend during development — not meant to be
 * changed by end users.
 */
function autophotos_wc_api_base() {
	return apply_filters( 'autophotos_wc_api_base', 'https://autophotos.ai' );
}

require_once AUTOPHOTOS_WC_PLUGIN_DIR . 'includes/class-autophotos-keys.php';
require_once AUTOPHOTOS_WC_PLUGIN_DIR . 'includes/class-autophotos-settings.php';
require_once AUTOPHOTOS_WC_PLUGIN_DIR . 'includes/class-autophotos-ajax.php';

/**
 * WooCommerce is a hard dependency (this plugin only exists to create WC
 * REST API keys and read the WC REST API keys table) — checked on every
 * load, not just activation, because WooCommerce can be deactivated later
 * without this plugin being deactivated alongside it.
 */
function autophotos_wc_missing_woocommerce_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			esc_html_e(
				'AutoPhotos for WooCommerce requires WooCommerce to be installed and active.',
				'autophotos-for-woocommerce'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * The AutoPhotos.ai service shuts down on 26 October 2026, after which this
 * plugin has nothing left to connect to. Shown on the Dashboard, the Plugins
 * screen and the plugin's own settings page only.
 */
function autophotos_wc_service_closing_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	if ( ! in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) && false === strpos( $screen->id, 'autophotos' ) ) {
		return;
	}
	?>
	<div class="notice notice-warning is-dismissible">
		<p>
			<?php
			esc_html_e(
				'AutoPhotos.ai is closing down on 26 October 2026. After that date this plugin will stop working and you can safely deactivate and delete it. Images already saved to your products are not affected.',
				'autophotos-for-woocommerce'
			);
			?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'autophotos_wc_service_closing_notice' );

function autophotos_wc_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'autophotos_wc_missing_woocommerce_notice' );
		return;
	}

	Autophotos_WC_Settings::init();
	Autophotos_WC_Ajax::init();
}
add_action( 'plugins_loaded', 'autophotos_wc_init' );

/**
 * Nothing to do on activation beyond the WooCommerce check above (no DB
 * tables, no options that need a default beyond WordPress's own "option
 * doesn't exist yet" default) — kept as an explicit no-op hook rather than
 * omitted, so a future version has an obvious place to add one.
 */
function autophotos_wc_activate() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__(
				'AutoPhotos for WooCommerce requires WooCommerce to be installed and active. The plugin has been deactivated.',
				'autophotos-for-woocommerce'
			),
			'',
			array( 'back_link' => true )
		);
	}
}
register_activation_hook( __FILE__, 'autophotos_wc_activate' );
