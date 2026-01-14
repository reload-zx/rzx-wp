<?php
/**
 * Tracker class for RZX.bio Conversion Tracking.
 *
 * Handles sending conversion data to RZX.bio when orders are completed.
 *
 * @package RZX_Conversion_Tracking
 */

defined( 'ABSPATH' ) || exit;

/**
 * RZX Tracker class.
 */
class RZX_Tracker {

	/**
	 * API endpoint for conversions.
	 *
	 * @var string
	 */
	const API_ENDPOINT = 'https://rzx.bio/webhook-conversion';

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
		// Track on order status changes.
		add_action( 'woocommerce_order_status_completed', array( $this, 'track_conversion' ), 10, 1 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'track_conversion' ), 10, 1 );
		add_action( 'woocommerce_payment_complete', array( $this, 'track_conversion' ), 10, 1 );
	}

	/**
	 * Track a conversion for an order.
	 *
	 * @param int $order_id Order ID.
	 */
	public function track_conversion( $order_id ) {
		// Check if tracking is enabled.
		if ( ! RZX_Settings::is_tracking_enabled() ) {
			return;
		}

		// Get order object.
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			$this->log( sprintf( 'Order %d not found', $order_id ), 'error' );
			return;
		}

		// Check if already sent (prevent duplicates).
		if ( $this->is_already_sent( $order ) ) {
			$this->log( sprintf( 'Order %d already tracked, skipping', $order_id ), 'info' );
			return;
		}

		// Check for lock (race condition prevention).
		$lock_key = 'rzx_sending_' . $order_id;
		if ( get_transient( $lock_key ) ) {
			$this->log( sprintf( 'Order %d is currently being processed, skipping', $order_id ), 'info' );
			return;
		}

		// Set lock.
		set_transient( $lock_key, true, 60 );

		// Get track link ID.
		$track_link_id = $this->get_track_link_id( $order );

		// Build payload.
		$payload = $this->build_payload( $order, $track_link_id );

		// Allow filtering of payload.
		$payload = apply_filters( 'rzx_conversion_payload', $payload, $order );

		// Send to API.
		$result = $this->send_conversion( $order, $payload );

		// Release lock.
		delete_transient( $lock_key );

		return $result;
	}

	/**
	 * Check if conversion was already sent for this order.
	 *
	 * @param WC_Order $order Order object.
	 * @return bool
	 */
	private function is_already_sent( $order ) {
		return 'yes' === $order->get_meta( '_rzx_conversion_sent' );
	}

	/**
	 * Get track link ID from order or cookie.
	 *
	 * @param WC_Order $order Order object.
	 * @return int|null
	 */
	private function get_track_link_id( $order ) {
		// First check order meta (set during checkout).
		$track_id = $order->get_meta( '_rzx_track_link_id' );

		if ( ! empty( $track_id ) ) {
			return absint( $track_id );
		}

		// Fallback to cookie handler.
		if ( function_exists( 'RZX' ) && RZX()->cookie_handler ) {
			return RZX()->cookie_handler->get_track_id();
		}

		return null;
	}

	/**
	 * Build the conversion payload.
	 *
	 * @param WC_Order $order         Order object.
	 * @param int|null $track_link_id Track link ID.
	 * @return array
	 */
	private function build_payload( $order, $track_link_id ) {
		$payload = array(
			'conversion_type'          => 'purchase',
			'conversion_name'          => 'WooCommerce Order',
			'revenue'                  => (float) $order->get_total(),
			'currency'                 => $order->get_currency(),
			'order_id'                 => 'WC-' . $order->get_id(),
			'quantity'                 => $this->get_total_quantity( $order ),
			'source'                   => 'woocommerce',
			'attribution_window_hours' => $this->get_attribution_window( $order ),
			'metadata'                 => $this->build_metadata( $order ),
		);

		// Add track link ID if available.
		if ( $track_link_id ) {
			$payload['track_link_id'] = $track_link_id;
		}

		return $payload;
	}

	/**
	 * Get total quantity of items in order.
	 *
	 * @param WC_Order $order Order object.
	 * @return int
	 */
	private function get_total_quantity( $order ) {
		$quantity = 0;

		foreach ( $order->get_items() as $item ) {
			$quantity += $item->get_quantity();
		}

		return $quantity;
	}

	/**
	 * Get attribution window for order.
	 *
	 * @param WC_Order $order Order object.
	 * @return int Hours.
	 */
	private function get_attribution_window( $order ) {
		$hours = RZX_Settings::get_attribution_window();
		return apply_filters( 'rzx_attribution_window_hours', $hours, $order );
	}

	/**
	 * Build metadata for the conversion.
	 *
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	private function build_metadata( $order ) {
		$metadata = array(
			'plugin_version' => RZX_VERSION,
			'wc_version'     => WC()->version,
			'wp_version'     => get_bloginfo( 'version' ),
			'payment_method' => $order->get_payment_method(),
			'order_status'   => $order->get_status(),
		);

		// Add shipping method.
		$shipping_methods = $order->get_shipping_methods();
		if ( ! empty( $shipping_methods ) ) {
			$shipping_method             = reset( $shipping_methods );
			$metadata['shipping_method'] = $shipping_method->get_method_id();
		}

		// Add coupon codes.
		$coupons = $order->get_coupon_codes();
		if ( ! empty( $coupons ) ) {
			$metadata['coupon_code'] = implode( ', ', $coupons );
		}

		// Add customer email hash (for analytics, not PII).
		$email = $order->get_billing_email();
		if ( ! empty( $email ) ) {
			$metadata['customer_email_hash'] = md5( strtolower( trim( $email ) ) );
		}

		// Check if first order for this customer.
		$metadata['is_first_order'] = $this->is_first_order( $order );

		// Add product details if enabled.
		if ( RZX_Settings::track_product_details() ) {
			$metadata['products'] = $this->get_product_details( $order );
		}

		// Allow filtering of metadata.
		return apply_filters( 'rzx_conversion_metadata', $metadata, $order );
	}

	/**
	 * Check if this is the customer's first order.
	 *
	 * @param WC_Order $order Order object.
	 * @return bool
	 */
	private function is_first_order( $order ) {
		$customer_id = $order->get_customer_id();

		if ( ! $customer_id ) {
			// Guest checkout - check by email.
			$email        = $order->get_billing_email();
			$order_count  = wc_get_orders(
				array(
					'billing_email' => $email,
					'status'        => array( 'completed', 'processing' ),
					'limit'         => 2,
					'return'        => 'ids',
				)
			);
			return count( $order_count ) <= 1;
		}

		// Registered customer.
		$customer = new WC_Customer( $customer_id );
		return $customer->get_order_count() <= 1;
	}

	/**
	 * Get product details for all items in order.
	 *
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	private function get_product_details( $order ) {
		$products = array();

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();

			if ( ! $product ) {
				continue;
			}

			$product_data = array(
				'id'       => $product->get_id(),
				'sku'      => $product->get_sku(),
				'name'     => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'price'    => (float) ( $item->get_total() / max( 1, $item->get_quantity() ) ),
			);

			// Add category.
			$categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );
			if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
				$product_data['category'] = $categories[0];
			}

			$products[] = $product_data;
		}

		return $products;
	}

	/**
	 * Send conversion to RZX.bio API.
	 *
	 * @param WC_Order $order   Order object.
	 * @param array    $payload Conversion payload.
	 * @return bool Success or failure.
	 */
	private function send_conversion( $order, $payload ) {
		$api_key = RZX_Settings::get_api_key();

		if ( empty( $api_key ) ) {
			$this->log( 'API key not configured, skipping conversion', 'error' );
			do_action( 'rzx_conversion_error', $order, 'API key not configured' );
			return false;
		}

		$this->log( sprintf( 'RZX Conversion - Order #%d', $order->get_id() ), 'info' );
		$this->log( sprintf( 'Payload: %s', wp_json_encode( $payload ) ), 'info' );

		// Make API request.
		$response = wp_remote_post(
			self::API_ENDPOINT,
			array(
				'timeout' => 15,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'X-RZX-API-Key' => $api_key,
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		// Handle WP error.
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$this->log( sprintf( 'RZX API Error: %s', $error_message ), 'error' );
			do_action( 'rzx_conversion_error', $order, $error_message );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$data          = json_decode( $response_body, true );

		$this->log( sprintf( 'Response: %s', $response_body ), 'info' );
		$this->log( sprintf( 'HTTP Response Code: %d', $response_code ), 'info' );

		// Check for success.
		if ( 200 === $response_code && isset( $data['success'] ) && $data['success'] ) {
			$conversion_id = isset( $data['conversion_id'] ) ? $data['conversion_id'] : null;

			// Save meta to order.
			$this->save_order_meta( $order, $conversion_id, $payload );

			$this->log( sprintf( 'Conversion ID %s saved to order meta', $conversion_id ), 'info' );

			// Fire success action.
			do_action( 'rzx_conversion_sent', $order, $data, $conversion_id );

			return true;
		}

		// Handle error.
		$error_message = isset( $data['error'] ) ? $data['error'] : 'Unknown error';
		$this->log( sprintf( 'RZX API Error: %s', $error_message ), 'error' );
		do_action( 'rzx_conversion_error', $order, $error_message );

		return false;
	}

	/**
	 * Save conversion meta to order.
	 *
	 * @param WC_Order $order         Order object.
	 * @param int|null $conversion_id Conversion ID from API.
	 * @param array    $payload       The sent payload.
	 */
	private function save_order_meta( $order, $conversion_id, $payload ) {
		$order->update_meta_data( '_rzx_conversion_sent', 'yes' );
		$order->update_meta_data( '_rzx_conversion_sent_at', current_time( 'mysql' ) );

		if ( $conversion_id ) {
			$order->update_meta_data( '_rzx_conversion_id', $conversion_id );
		}

		if ( isset( $payload['track_link_id'] ) ) {
			$order->update_meta_data( '_rzx_track_link_id', $payload['track_link_id'] );
		}

		$order->save();
	}

	/**
	 * Log a message.
	 *
	 * @param string $message Message to log.
	 * @param string $level   Log level (info, error, debug).
	 */
	private function log( $message, $level = 'info' ) {
		if ( ! RZX_Settings::is_debug_enabled() ) {
			return;
		}

		if ( ! function_exists( 'wc_get_logger' ) ) {
			return;
		}

		$logger  = wc_get_logger();
		$context = array( 'source' => 'rzx-tracking' );

		switch ( $level ) {
			case 'error':
				$logger->error( $message, $context );
				break;
			case 'debug':
				$logger->debug( $message, $context );
				break;
			default:
				$logger->info( $message, $context );
				break;
		}
	}
}
