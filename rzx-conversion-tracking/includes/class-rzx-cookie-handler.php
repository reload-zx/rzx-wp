<?php
/**
 * Cookie Handler class for RZX.bio Conversion Tracking.
 *
 * Handles capturing and storing the _rzx tracking parameter from URLs.
 *
 * @package RZX_Conversion_Tracking
 */

defined( 'ABSPATH' ) || exit;

/**
 * RZX Cookie Handler class.
 */
class RZX_Cookie_Handler {

	/**
	 * Cookie name for storing track ID.
	 *
	 * @var string
	 */
	const COOKIE_NAME = 'rzx_track_id';

	/**
	 * URL parameter name for tracking.
	 *
	 * @var string
	 */
	const URL_PARAM = '_rzx';

	/**
	 * Cookie duration in days.
	 *
	 * @var int
	 */
	const COOKIE_DAYS = 30;

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
		// Hook early to capture tracking parameter before any output.
		add_action( 'init', array( $this, 'capture_tracking_param' ), 1 );

		// Store track ID in order meta when order is created.
		add_action( 'woocommerce_checkout_create_order', array( $this, 'save_track_id_to_order' ), 10, 2 );

		// Also hook into store API for block checkout.
		add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'save_track_id_to_order_block_checkout' ) );
	}

	/**
	 * Capture the _rzx tracking parameter from URL and store in cookie.
	 */
	public function capture_tracking_param() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET[ self::URL_PARAM ] ) || empty( $_GET[ self::URL_PARAM ] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$track_id = absint( $_GET[ self::URL_PARAM ] );

		if ( $track_id <= 0 ) {
			return;
		}

		// Set secure cookie with 30 days expiration.
		$this->set_tracking_cookie( $track_id );

		// Also set in $_COOKIE for immediate use in same request.
		$_COOKIE[ self::COOKIE_NAME ] = $track_id;

		// Log if debug mode is enabled.
		$this->log_debug( sprintf( 'Captured tracking parameter: %d', $track_id ) );
	}

	/**
	 * Set the tracking cookie with security options.
	 *
	 * @param int $track_id The track link ID to store.
	 */
	private function set_tracking_cookie( $track_id ) {
		$expiration = time() + ( self::COOKIE_DAYS * DAY_IN_SECONDS );

		// Use modern cookie API with security options.
		setcookie(
			self::COOKIE_NAME,
			$track_id,
			array(
				'expires'  => $expiration,
				'path'     => '/',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	/**
	 * Get the current tracking ID from cookie.
	 *
	 * @return int|null Track ID or null if not set.
	 */
	public function get_track_id() {
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) && ! empty( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			$track_id = absint( $_COOKIE[ self::COOKIE_NAME ] );
			return $track_id > 0 ? $track_id : null;
		}

		return null;
	}

	/**
	 * Check if a tracking ID is available.
	 *
	 * @return bool
	 */
	public function has_track_id() {
		return null !== $this->get_track_id();
	}

	/**
	 * Save track ID to order meta during checkout (classic checkout).
	 *
	 * @param WC_Order $order Order object.
	 * @param array    $data  Posted data.
	 */
	public function save_track_id_to_order( $order, $data ) {
		$track_id = $this->get_track_id();

		if ( $track_id ) {
			$order->update_meta_data( '_rzx_track_link_id', $track_id );
			$this->log_debug( sprintf( 'Saved track ID %d to order %d', $track_id, $order->get_id() ) );
		}
	}

	/**
	 * Save track ID to order meta during block checkout.
	 *
	 * @param WC_Order $order Order object.
	 */
	public function save_track_id_to_order_block_checkout( $order ) {
		// Only save if not already saved (avoid duplicates from classic checkout hook).
		if ( $order->get_meta( '_rzx_track_link_id' ) ) {
			return;
		}

		$track_id = $this->get_track_id();

		if ( $track_id ) {
			$order->update_meta_data( '_rzx_track_link_id', $track_id );
			$order->save();
			$this->log_debug( sprintf( 'Saved track ID %d to order %d (block checkout)', $track_id, $order->get_id() ) );
		}
	}

	/**
	 * Clear the tracking cookie.
	 */
	public function clear_tracking_cookie() {
		if ( isset( $_COOKIE[ self::COOKIE_NAME ] ) ) {
			setcookie(
				self::COOKIE_NAME,
				'',
				array(
					'expires'  => time() - 3600,
					'path'     => '/',
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Lax',
				)
			);
			unset( $_COOKIE[ self::COOKIE_NAME ] );
		}
	}

	/**
	 * Log debug message if debug mode is enabled.
	 *
	 * @param string $message Message to log.
	 */
	private function log_debug( $message ) {
		$debug_enabled = get_option( 'rzx_debug_mode', 'no' );

		if ( 'yes' === $debug_enabled && function_exists( 'wc_get_logger' ) ) {
			$logger = wc_get_logger();
			$logger->debug( $message, array( 'source' => 'rzx-tracking' ) );
		}
	}
}
