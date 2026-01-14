=== RZX.bio Conversion Tracking ===
Contributors: rzxbio
Tags: woocommerce, conversion tracking, affiliate, attribution, analytics
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track WooCommerce sales and attribute revenue to your RZX.bio links automatically.

== Description ==

RZX.bio Conversion Tracking is a lightweight WordPress plugin that integrates WooCommerce with the RZX.bio conversion tracking system. When a customer completes an order, the plugin automatically sends sale data to RZX.bio for link attribution and revenue tracking.

= Features =

* **Automatic Tracking**: Captures the `_rzx` parameter from URLs when visitors arrive via RZX.bio links
* **Cookie-based Attribution**: Stores tracking data in a secure cookie for up to 30 days
* **Configurable Attribution Window**: Choose between 24 hours, 7 days, or 30 days
* **Detailed Conversion Data**: Sends order details including products, quantities, and revenue
* **Debug Logging**: Optional logging to WooCommerce logs for troubleshooting
* **HPOS Compatible**: Full support for WooCommerce High-Performance Order Storage
* **Block Checkout Compatible**: Works with both classic and block-based checkout

= How It Works =

1. A visitor clicks your RZX.bio link (e.g., `yoursite.com/product?_rzx=12345`)
2. The plugin captures the tracking parameter and stores it in a secure cookie
3. When the visitor completes a purchase, the plugin sends conversion data to RZX.bio
4. Revenue is attributed to the correct link in your RZX.bio dashboard

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 6.0 or higher
* PHP 7.4 or higher
* An active RZX.bio account with API access

== Installation ==

1. Upload the `rzx-conversion-tracking` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Settings > RZX.bio
4. Enter your RZX.bio API key
5. Click "Test Connection" to verify the setup
6. Enable tracking and save settings

== Frequently Asked Questions ==

= Where do I get my API key? =

Log in to your RZX.bio dashboard and navigate to Settings > API to generate your API key.

= Does this work with the new WooCommerce block checkout? =

Yes! The plugin is fully compatible with both the classic checkout and the new block-based checkout.

= What data is sent to RZX.bio? =

The plugin sends order information including:
* Order ID and total revenue
* Currency
* Product details (optional)
* Coupon codes used
* Payment and shipping methods
* A hashed version of the customer email (for analytics only)

No personally identifiable information (PII) is sent in plain text.

= Can I customize what data is sent? =

Yes, developers can use the `rzx_conversion_payload` and `rzx_conversion_metadata` filters to modify the data before it's sent.

= Where can I see debug logs? =

Enable Debug Mode in the settings, then go to WooCommerce > Status > Logs and look for files starting with `rzx-tracking`.

== Screenshots ==

1. Settings page in WooCommerce
2. Connection status indicator
3. Test connection feature

== Changelog ==

= 1.1.0 =
* Added full HPOS (High-Performance Order Storage) support
* Added Block Checkout compatibility
* New `track_link_id` parameter for link attribution
* Extended metadata (products, coupons, payment method)
* Save `conversion_id` to order meta
* Configurable attribution window (24h, 7 days, 30 days)
* Improved cookie security (httponly, samesite)
* Compatibility with WordPress 6.7, WooCommerce 9.5, PHP 8.3

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
This version adds HPOS and Block Checkout support. Recommended for all users.

== Developer Hooks ==

The plugin provides several hooks for developers:

= Filters =

* `rzx_conversion_payload` - Modify the full payload before sending
* `rzx_conversion_metadata` - Modify only the metadata portion
* `rzx_attribution_window_hours` - Change attribution window per order
* `rzx_settings` - Add or modify settings fields

= Actions =

* `rzx_conversion_sent` - Fired after successful conversion
* `rzx_conversion_error` - Fired when an error occurs

Example:

`
add_filter('rzx_conversion_metadata', function($metadata, $order) {
    $metadata['custom_field'] = $order->get_meta('my_custom_field');
    return $metadata;
}, 10, 2);
`
