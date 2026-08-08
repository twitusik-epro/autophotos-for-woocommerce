<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin-facing settings screen: paste a pairing code, connect/disconnect.
 * Connection state itself lives entirely on the AutoPhotos side (the
 * woocommerce_connections table there); the options this plugin stores
 * locally are only a mirror for the UI ("connected: yes/since when") plus
 * the id of the WC REST API key row it created, so disconnect can revoke
 * that specific key.
 */
class Autophotos_WC_Settings {

	const OPTION_CONNECTED    = 'autophotos_wc_connected';
	const OPTION_CONNECTED_AT = 'autophotos_wc_connected_at';
	const OPTION_KEY_ID       = 'autophotos_wc_key_id';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	public static function add_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'AutoPhotos', 'autophotos-for-woocommerce' ),
			__( 'AutoPhotos', 'autophotos-for-woocommerce' ),
			'manage_woocommerce',
			'autophotos-for-woocommerce',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function enqueue_assets( $hook ) {
		if ( 'woocommerce_page_autophotos-for-woocommerce' !== $hook ) {
			return;
		}
		wp_enqueue_script(
			'autophotos-wc-admin',
			plugins_url( 'assets/admin.js', AUTOPHOTOS_WC_PLUGIN_FILE ),
			array( 'jquery' ),
			AUTOPHOTOS_WC_VERSION,
			true
		);
		wp_localize_script(
			'autophotos-wc-admin',
			'autophotosWc',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'autophotos_wc_nonce' ),
				'i18n'    => array(
					'connecting'    => __( 'Connecting…', 'autophotos-for-woocommerce' ),
					'disconnecting' => __( 'Disconnecting…', 'autophotos-for-woocommerce' ),
					'enterCode'     => __( 'Paste a pairing code first.', 'autophotos-for-woocommerce' ),
				),
			)
		);
	}

	public static function is_connected() {
		return (bool) get_option( self::OPTION_CONNECTED, false );
	}

	public static function render_page() {
		$connected    = self::is_connected();
		$connected_at = get_option( self::OPTION_CONNECTED_AT, '' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'AutoPhotos for WooCommerce', 'autophotos-for-woocommerce' ); ?></h1>

			<?php if ( $connected ) : ?>
				<div class="notice notice-success inline">
					<p>
						<?php
						printf(
							/* translators: %s: connection date/time */
							esc_html__( 'Connected to AutoPhotos.ai since %s.', 'autophotos-for-woocommerce' ),
							esc_html( $connected_at )
						);
						?>
					</p>
				</div>
				<p>
					<?php
					printf(
						wp_kses(
							/* translators: %s: link to the AutoPhotos WooCommerce panel */
							__( 'Process product photos from your <a href="%s" target="_blank" rel="noopener noreferrer">AutoPhotos panel</a>.', 'autophotos-for-woocommerce' ),
							array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) )
						),
						esc_url( trailingslashit( autophotos_wc_api_base() ) . 'integrations/woocommerce/' )
					);
					?>
				</p>
				<p>
					<button type="button" class="button" id="autophotos-wc-disconnect">
						<?php esc_html_e( 'Disconnect', 'autophotos-for-woocommerce' ); ?>
					</button>
				</p>
			<?php else : ?>
				<p>
					<?php
					printf(
						wp_kses(
							/* translators: %s: link to the AutoPhotos WooCommerce panel */
							__( 'Generate a pairing code on your <a href="%s" target="_blank" rel="noopener noreferrer">AutoPhotos account</a> and paste it below to connect this store — no need to create WooCommerce REST API keys by hand.', 'autophotos-for-woocommerce' ),
							array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) )
						),
						esc_url( trailingslashit( autophotos_wc_api_base() ) . 'integrations/woocommerce/' )
					);
					?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="autophotos-wc-code"><?php esc_html_e( 'Pairing code', 'autophotos-for-woocommerce' ); ?></label>
						</th>
						<td>
							<input type="text" id="autophotos-wc-code" class="regular-text" autocomplete="off" />
						</td>
					</tr>
				</table>
				<p>
					<button type="button" class="button button-primary" id="autophotos-wc-connect">
						<?php esc_html_e( 'Connect', 'autophotos-for-woocommerce' ); ?>
					</button>
				</p>
			<?php endif; ?>

			<p id="autophotos-wc-message" class="hidden"></p>
		</div>
		<?php
	}
}
