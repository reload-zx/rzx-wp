<?php
/**
 * Settings class for RZX.bio Conversion Tracking.
 *
 * Handles the admin settings page under WooCommerce > Settings > RZX.bio.
 *
 * @package RZX_Conversion_Tracking
 */

defined( 'ABSPATH' ) || exit;

/**
 * RZX Settings class.
 */
class RZX_Settings {

	/**
	 * API endpoint for testing connection.
	 *
	 * @var string
	 */
	const TEST_ENDPOINT = 'https://rzx.bio/webhook-conversion/test';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Add WooCommerce settings tab.
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_tabs_rzx_bio', array( $this, 'output_settings_page' ) );
		add_action( 'woocommerce_update_options_rzx_bio', array( $this, 'save_settings' ) );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// AJAX handler for test connection.
		add_action( 'wp_ajax_rzx_test_connection', array( $this, 'ajax_test_connection' ) );
	}

	/**
	 * Add settings tab to WooCommerce settings.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['rzx_bio'] = __( 'RZX.bio', 'rzx-conversion-tracking' );
		return $tabs;
	}

	/**
	 * Output the settings page content.
	 */
	public function output_settings_page() {
		$this->output_status_section();
		woocommerce_admin_fields( $this->get_settings() );
		$this->output_test_connection_button();
	}

	/**
	 * Output status section at top of settings page.
	 */
	private function output_status_section() {
		$status = $this->get_connection_status();
		?>
		<div class="rzx-status-section" style="margin-bottom: 20px; padding: 15px; background: #fff; border-left: 4px solid <?php echo esc_attr( $status['color'] ); ?>;">
			<h3 style="margin-top: 0;">
				<?php esc_html_e( 'Connection Status', 'rzx-conversion-tracking' ); ?>
			</h3>
			<p style="margin-bottom: 0;">
				<span style="font-size: 18px; margin-right: 8px;"><?php echo esc_html( $status['icon'] ); ?></span>
				<strong><?php echo esc_html( $status['message'] ); ?></strong>
			</p>
		</div>
		<?php
	}

	/**
	 * Get current connection status.
	 *
	 * @return array Status info with icon, color and message.
	 */
	private function get_connection_status() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array(
				'icon'    => '🔴',
				'color'   => '#dc3232',
				'message' => __( 'WooCommerce is required', 'rzx-conversion-tracking' ),
			);
		}

		$api_key = get_option( 'rzx_api_key', '' );

		// Check if API key is configured.
		if ( empty( $api_key ) ) {
			return array(
				'icon'    => '🟡',
				'color'   => '#ffb900',
				'message' => __( 'Enter your API key to start tracking', 'rzx-conversion-tracking' ),
			);
		}

		// Check last connection test result.
		$last_test = get_option( 'rzx_last_connection_test', '' );

		if ( 'success' === $last_test ) {
			return array(
				'icon'    => '🟢',
				'color'   => '#46b450',
				'message' => __( 'Connected to RZX.bio', 'rzx-conversion-tracking' ),
			);
		}

		if ( 'error' === $last_test ) {
			return array(
				'icon'    => '🔴',
				'color'   => '#dc3232',
				'message' => __( 'Invalid API key', 'rzx-conversion-tracking' ),
			);
		}

		// API key set but not tested yet.
		return array(
			'icon'    => '🟡',
			'color'   => '#ffb900',
			'message' => __( 'API key configured - click Test Connection to verify', 'rzx-conversion-tracking' ),
		);
	}

	/**
	 * Get settings fields.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = array(
			array(
				'title' => __( 'RZX.bio Conversion Tracking Settings', 'rzx-conversion-tracking' ),
				'type'  => 'title',
				'desc'  => __( 'Configure your RZX.bio conversion tracking settings. Get your API key from your RZX.bio dashboard.', 'rzx-conversion-tracking' ),
				'id'    => 'rzx_settings_section',
			),
			array(
				'title'    => __( 'API Key', 'rzx-conversion-tracking' ),
				'desc'     => __( 'Enter your RZX.bio API key.', 'rzx-conversion-tracking' ),
				'id'       => 'rzx_api_key',
				'type'     => 'password',
				'css'      => 'min-width: 350px;',
				'desc_tip' => true,
			),
			array(
				'title'   => __( 'Enable Tracking', 'rzx-conversion-tracking' ),
				'desc'    => __( 'Enable conversion tracking for WooCommerce orders.', 'rzx-conversion-tracking' ),
				'id'      => 'rzx_enable_tracking',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Track Product Details', 'rzx-conversion-tracking' ),
				'desc'    => __( 'Include detailed product information in conversion data.', 'rzx-conversion-tracking' ),
				'id'      => 'rzx_track_product_details',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'    => __( 'Attribution Window', 'rzx-conversion-tracking' ),
				'desc'     => __( 'How long after clicking a link should conversions be attributed.', 'rzx-conversion-tracking' ),
				'id'       => 'rzx_attribution_window',
				'type'     => 'select',
				'default'  => '168',
				'options'  => array(
					'24'  => __( '24 hours', 'rzx-conversion-tracking' ),
					'168' => __( '7 days (recommended)', 'rzx-conversion-tracking' ),
					'720' => __( '30 days', 'rzx-conversion-tracking' ),
				),
				'desc_tip' => true,
			),
			array(
				'title'   => __( 'Debug Mode', 'rzx-conversion-tracking' ),
				'desc'    => __( 'Log API requests and responses in WooCommerce > Status > Logs.', 'rzx-conversion-tracking' ),
				'id'      => 'rzx_debug_mode',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rzx_settings_section',
			),
		);

		return apply_filters( 'rzx_settings', $settings );
	}

	/**
	 * Save settings.
	 */
	public function save_settings() {
		woocommerce_update_options( $this->get_settings() );

		// Clear connection test status when API key changes.
		$new_api_key = isset( $_POST['rzx_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['rzx_api_key'] ) ) : '';
		$old_api_key = get_option( 'rzx_api_key', '' );

		if ( $new_api_key !== $old_api_key ) {
			delete_option( 'rzx_last_connection_test' );
		}
	}

	/**
	 * Output test connection button.
	 */
	private function output_test_connection_button() {
		?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<?php esc_html_e( 'Test Connection', 'rzx-conversion-tracking' ); ?>
					</th>
					<td class="forminp">
						<button type="button" id="rzx-test-connection" class="button button-secondary">
							<?php esc_html_e( 'Test Connection', 'rzx-conversion-tracking' ); ?>
						</button>
						<span id="rzx-test-result" style="margin-left: 10px;"></span>
						<p class="description">
							<?php esc_html_e( 'Click to verify your API key is valid and connection to RZX.bio is working.', 'rzx-conversion-tracking' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on WooCommerce settings page.
		if ( 'woocommerce_page_wc-settings' !== $hook ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';

		if ( 'rzx_bio' !== $tab ) {
			return;
		}

		wp_enqueue_style(
			'rzx-admin-css',
			RZX_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			RZX_VERSION
		);

		wp_enqueue_script(
			'rzx-admin-js',
			RZX_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			RZX_VERSION,
			true
		);

		wp_localize_script(
			'rzx-admin-js',
			'rzxAdmin',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => wp_create_nonce( 'rzx_test_connection' ),
				'testing'   => __( 'Testing...', 'rzx-conversion-tracking' ),
				'success'   => __( 'Connection successful!', 'rzx-conversion-tracking' ),
				'error'     => __( 'Connection failed:', 'rzx-conversion-tracking' ),
				'noApiKey'  => __( 'Please enter an API key first.', 'rzx-conversion-tracking' ),
			)
		);
	}

	/**
	 * AJAX handler for test connection.
	 */
	public function ajax_test_connection() {
		// Verify nonce.
		if ( ! check_ajax_referer( 'rzx_test_connection', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'rzx-conversion-tracking' ) ) );
		}

		// Check capabilities.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'rzx-conversion-tracking' ) ) );
		}

		$api_key = get_option( 'rzx_api_key', '' );

		if ( empty( $api_key ) ) {
			wp_send_json_error( array( 'message' => __( 'API key is not configured.', 'rzx-conversion-tracking' ) ) );
		}

		// Make test request.
		$response = wp_remote_post(
			self::TEST_ENDPOINT,
			array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'X-RZX-API-Key' => $api_key,
				),
				'body'    => wp_json_encode(
					array(
						'test'           => true,
						'source'         => 'woocommerce',
						'plugin_version' => RZX_VERSION,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			update_option( 'rzx_last_connection_test', 'error' );
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		if ( 200 === $response_code && isset( $data['success'] ) && $data['success'] ) {
			update_option( 'rzx_last_connection_test', 'success' );
			wp_send_json_success( array( 'message' => __( 'Connection successful!', 'rzx-conversion-tracking' ) ) );
		}

		update_option( 'rzx_last_connection_test', 'error' );
		$error_message = isset( $data['error'] ) ? $data['error'] : __( 'Unknown error', 'rzx-conversion-tracking' );
		wp_send_json_error( array( 'message' => $error_message ) );
	}

	/**
	 * Check if tracking is enabled.
	 *
	 * @return bool
	 */
	public static function is_tracking_enabled() {
		return 'yes' === get_option( 'rzx_enable_tracking', 'yes' );
	}

	/**
	 * Check if product details should be tracked.
	 *
	 * @return bool
	 */
	public static function track_product_details() {
		return 'yes' === get_option( 'rzx_track_product_details', 'yes' );
	}

	/**
	 * Get attribution window in hours.
	 *
	 * @return int
	 */
	public static function get_attribution_window() {
		return absint( get_option( 'rzx_attribution_window', 168 ) );
	}

	/**
	 * Check if debug mode is enabled.
	 *
	 * @return bool
	 */
	public static function is_debug_enabled() {
		return 'yes' === get_option( 'rzx_debug_mode', 'no' );
	}

	/**
	 * Get API key.
	 *
	 * @return string
	 */
	public static function get_api_key() {
		return get_option( 'rzx_api_key', '' );
	}
}
