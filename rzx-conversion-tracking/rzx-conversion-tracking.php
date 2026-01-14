<?php
/**
 * Plugin Name: RZX.bio Conversion Tracking
 * Plugin URI: https://rzx.bio/conversions-wp-plugin
 * Description: Track WooCommerce sales and attribute revenue to your RZX.bio links automatically.
 * Version: 1.1.0
 * Author: RZX.bio
 * Author URI: https://rzx.bio
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rzx-conversion-tracking
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.7
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.5
 *
 * @package RZX_Conversion_Tracking
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'RZX_VERSION', '1.1.0' );
define( 'RZX_PLUGIN_FILE', __FILE__ );
define( 'RZX_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RZX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RZX_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Declare HPOS compatibility.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				__FILE__,
				true
			);
		}
	}
);

/**
 * Main plugin class.
 */
final class RZX_Conversion_Tracking {

	/**
	 * Single instance of the class.
	 *
	 * @var RZX_Conversion_Tracking
	 */
	private static $instance = null;

	/**
	 * Settings instance.
	 *
	 * @var RZX_Settings
	 */
	public $settings;

	/**
	 * Tracker instance.
	 *
	 * @var RZX_Tracker
	 */
	public $tracker;

	/**
	 * Cookie handler instance.
	 *
	 * @var RZX_Cookie_Handler
	 */
	public $cookie_handler;

	/**
	 * Get single instance of the class.
	 *
	 * @return RZX_Conversion_Tracking
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once RZX_PLUGIN_DIR . 'includes/class-rzx-cookie-handler.php';
		require_once RZX_PLUGIN_DIR . 'includes/class-rzx-settings.php';
		require_once RZX_PLUGIN_DIR . 'includes/class-rzx-tracker.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Load textdomain.
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Check WooCommerce dependency.
		add_action( 'admin_notices', array( $this, 'check_woocommerce' ) );

		// Initialize components if WooCommerce is active.
		add_action( 'plugins_loaded', array( $this, 'init_components' ), 20 );

		// Add settings link to plugins page.
		add_filter( 'plugin_action_links_' . RZX_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'rzx-conversion-tracking',
			false,
			dirname( RZX_PLUGIN_BASENAME ) . '/languages/'
		);
	}

	/**
	 * Check if WooCommerce is active and display notice if not.
	 */
	public function check_woocommerce() {
		if ( ! $this->is_woocommerce_active() ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( 'RZX.bio Conversion Tracking', 'rzx-conversion-tracking' ); ?>:</strong>
					<?php esc_html_e( 'WooCommerce is required for this plugin to work. Please install and activate WooCommerce.', 'rzx-conversion-tracking' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Initialize plugin components.
	 */
	public function init_components() {
		if ( ! $this->is_woocommerce_active() ) {
			return;
		}

		$this->cookie_handler = new RZX_Cookie_Handler();
		$this->settings       = new RZX_Settings();
		$this->tracker        = new RZX_Tracker();
	}

	/**
	 * Add settings link to plugins page.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wc-settings&tab=rzx_bio' ) ),
			esc_html__( 'Settings', 'rzx-conversion-tracking' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}

/**
 * Returns the main instance of RZX_Conversion_Tracking.
 *
 * @return RZX_Conversion_Tracking
 */
function RZX() {
	return RZX_Conversion_Tracking::instance();
}

// Initialize plugin.
RZX();
