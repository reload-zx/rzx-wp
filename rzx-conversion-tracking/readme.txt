=== RZX.bio Conversion Tracking for WooCommerce ===
Contributors: rzxbio
Tags: woocommerce, conversion tracking, analytics, attribution, bio links
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
WC requires at least: 6.0
WC tested up to: 9.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track WooCommerce sales and attribute revenue to your RZX.bio links automatically.

== Description ==

RZX.bio Conversion Tracking is a WordPress plugin that seamlessly integrates your WooCommerce store with [RZX.bio](https://rzx.bio), enabling automatic conversion tracking and revenue attribution for your bio links.

When a visitor clicks one of your RZX.bio links and makes a purchase on your WooCommerce store, the plugin automatically tracks the conversion and sends the data to your RZX.bio dashboard.

= Key Features =

* **Automatic Conversion Tracking** - Tracks purchases automatically when orders are completed or processed
* **HPOS Compatible** - Full support for WooCommerce High-Performance Order Storage
* **Block Checkout Support** - Works with both classic and block-based checkout
* **Configurable Attribution Window** - Choose between 24 hours, 7 days, or 30 days
* **Secure Cookie Handling** - Uses httponly and samesite cookies for security
* **Extended Metadata** - Tracks products, coupons, shipping methods, and payment info
* **Duplicate Prevention** - Prevents sending the same conversion multiple times
* **First Order Detection** - Identifies new vs. returning customers
* **Debug Mode** - Log API requests for troubleshooting
* **Connection Testing** - Verify your API key with one click

= How It Works =

1. A visitor clicks your RZX.bio link (e.g., `https://yoursite.com/?_rzx=12345`)
2. The plugin captures the tracking parameter and stores it in a secure cookie
3. When the visitor completes a purchase, the plugin sends conversion data to RZX.bio
4. View your conversion analytics in the RZX.bio dashboard

= Privacy =

This plugin respects user privacy:

* Customer email addresses are hashed (MD5) before transmission
* No personal identifiable information (PII) is sent to RZX.bio
* Cookies are set with secure, httponly, and samesite attributes
* Data is only sent when a tracked conversion occurs

= WooCommerce Compatibility =

* WooCommerce 6.0 to 9.5
* Full HPOS (High-Performance Order Storage) support
* Block Checkout compatibility
* Classic Checkout compatibility

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 6.0 or higher
* PHP 7.4 or higher
* An active RZX.bio account

== Installation ==

1. Upload the `rzx-conversion-tracking` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Settings > RZX.bio
4. Enter your API Key from your RZX.bio dashboard
5. Click "Test Connection" to verify your setup
6. Configure your preferred settings and save

== Frequently Asked Questions ==

= Where do I get my API key? =

Log in to your RZX.bio account and navigate to your dashboard settings to find your API key.

= Do I need a paid RZX.bio account? =

Check [RZX.bio](https://rzx.bio) for current pricing and available plans.

= Does this work with WooCommerce Subscriptions? =

The plugin tracks the initial subscription order. Renewal tracking depends on how renewals are processed by WooCommerce.

= Can I track conversions from multiple RZX.bio accounts? =

Currently, the plugin supports one API key at a time.

= Is customer data shared with RZX.bio? =

Only hashed email addresses are sent for analytics purposes. No personal identifiable information is transmitted.

= What data is sent to RZX.bio? =

The plugin sends:

* Order total and currency
* Number of items purchased
* Payment and shipping methods
* Coupon codes used
* Product details (ID, SKU, name, quantity, price, category)
* First order indicator
* Plugin and WooCommerce version info

= How do I troubleshoot tracking issues? =

1. Enable Debug Mode in the plugin settings
2. Check WooCommerce > Status > Logs for "rzx-tracking" entries
3. Verify your API key using the Test Connection button
4. Ensure visitors arrive via RZX.bio links with the `_rzx` parameter

= Does this plugin affect site performance? =

The plugin is lightweight and only sends data when orders are completed. API calls are made server-side and do not affect checkout speed.

= Does this work with the new WooCommerce block checkout? =

Yes! The plugin is fully compatible with both the classic checkout and the new block-based checkout.

= Can I customize what data is sent? =

Yes, developers can use the `rzx_conversion_payload` and `rzx_conversion_metadata` filters to modify the data before it's sent.

== Screenshots ==

1. Plugin settings page in WooCommerce with connection status
2. API key configuration and test connection button
3. Attribution window and tracking options
4. Debug mode settings for troubleshooting

== Changelog ==

= 1.1.0 =
* Added: Full HPOS (High-Performance Order Storage) support
* Added: Block Checkout compatibility
* Added: Configurable attribution windows (24 hours, 7 days, 30 days)
* Added: Extended metadata tracking (products, coupons, payment methods)
* Added: Conversion ID storage in order metadata
* Added: Connection status display in settings
* Added: First order detection for guests and registered customers
* Improved: Cookie security with httponly and samesite attributes
* Improved: Race condition prevention with transient locks
* Tested: WordPress 6.7, WooCommerce 9.5, PHP 8.3

= 1.0.0 =
* Initial release
* Basic conversion tracking for WooCommerce orders
* API key configuration
* Debug logging support

== Upgrade Notice ==

= 1.1.0 =
This update adds HPOS and Block Checkout support, configurable attribution windows, and improved security. Recommended for all users.

== Developer Documentation ==

The plugin provides several hooks for developers to customize behavior.

= Filters =

**rzx_conversion_payload**
Modify the conversion payload before sending to RZX.bio.

`
add_filter( 'rzx_conversion_payload', function( $payload, $order ) {
    $payload['custom_field'] = 'custom_value';
    return $payload;
}, 10, 2 );
`

**rzx_conversion_metadata**
Modify metadata included in the conversion.

`
add_filter( 'rzx_conversion_metadata', function( $metadata, $order ) {
    $metadata['custom_meta'] = $order->get_meta( 'my_custom_field' );
    return $metadata;
}, 10, 2 );
`

**rzx_attribution_window_hours**
Adjust attribution window per order.

`
add_filter( 'rzx_attribution_window_hours', function( $hours, $order ) {
    // Use 30 days for high-value orders
    if ( $order->get_total() > 500 ) {
        return 720;
    }
    return $hours;
}, 10, 2 );
`

**rzx_settings**
Modify available plugin settings.

`
add_filter( 'rzx_settings', function( $settings ) {
    // Add custom settings
    return $settings;
} );
`

= Actions =

**rzx_conversion_sent**
Triggered after successful conversion tracking.

`
add_action( 'rzx_conversion_sent', function( $order, $response, $conversion_id ) {
    // Log successful conversion
    error_log( "Conversion {$conversion_id} tracked for order {$order->get_id()}" );
}, 10, 3 );
`

**rzx_conversion_error**
Triggered when conversion tracking fails.

`
add_action( 'rzx_conversion_error', function( $order, $error_message ) {
    // Handle or log error
    error_log( "RZX tracking failed for order {$order->get_id()}: {$error_message}" );
}, 10, 2 );
`

= Order Meta Fields =

The plugin stores the following meta fields on orders:

* `_rzx_track_link_id` - The RZX.bio link ID that referred this order
* `_rzx_conversion_sent` - Whether conversion was sent (yes/no)
* `_rzx_conversion_sent_at` - Timestamp when conversion was sent
* `_rzx_conversion_id` - The conversion ID returned by RZX.bio API
