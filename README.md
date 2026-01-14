# RZX.bio Conversion Tracking

A lightweight WordPress plugin that integrates WooCommerce with the RZX.bio conversion tracking system. Automatically attribute revenue to your RZX.bio links when customers complete purchases.

## Requirements

- WordPress 5.8+
- WooCommerce 6.0+
- PHP 7.4+

## Installation

### Method 1: Download from Release (Recommended)

1. Go to [Releases](../../releases)
2. Download `rzx-conversion-tracking.zip` from the latest release
3. In WordPress, go to **Plugins → Add New → Upload Plugin**
4. Select the zip file and click **Install Now**
5. Activate the plugin

### Method 2: Manual Upload

1. Download or clone this repository
2. Copy the `rzx-conversion-tracking` folder to `/wp-content/plugins/`
3. Activate the plugin from the WordPress admin panel

## Configuration

1. Go to **WooCommerce → Settings → RZX.bio**
2. Enter your **API Key** (available in your RZX.bio dashboard)
3. Click **Test Connection** to verify
4. Enable tracking and save

## How It Works

```
Visitor clicks RZX.bio link
         ↓
yoursite.com/product?_rzx=12345
         ↓
Plugin stores ID in cookie (30 days)
         ↓
Visitor completes purchase
         ↓
Plugin sends conversion to RZX.bio
         ↓
Revenue attributed to the correct link
```

## Settings

| Setting | Description |
|---------|-------------|
| API Key | Your RZX.bio API key (required) |
| Enable Tracking | Enable/disable conversion tracking |
| Track Product Details | Include product information in metadata |
| Attribution Window | Attribution window (24h / 7 days / 30 days) |
| Debug Mode | Log requests in WooCommerce → Status → Logs |

## Compatibility

- ✅ HPOS (High-Performance Order Storage)
- ✅ Block Checkout
- ✅ Classic Checkout
- ✅ PHP 8.0 - 8.3
- ✅ WordPress 6.7
- ✅ WooCommerce 9.5

## File Structure

```
rzx-conversion-tracking/
├── rzx-conversion-tracking.php       # Main plugin file
├── includes/
│   ├── class-rzx-cookie-handler.php  # Cookie management
│   ├── class-rzx-settings.php        # Settings page
│   └── class-rzx-tracker.php         # Conversion tracking
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
├── languages/
│   └── rzx-conversion-tracking.pot
└── readme.txt
```

## Developer Hooks

### Filters

```php
// Modify payload before sending
add_filter('rzx_conversion_payload', function($payload, $order) {
    $payload['custom_field'] = 'value';
    return $payload;
}, 10, 2);

// Modify metadata only
add_filter('rzx_conversion_metadata', function($metadata, $order) {
    $metadata['custom_data'] = $order->get_meta('my_field');
    return $metadata;
}, 10, 2);

// Custom attribution window per order
add_filter('rzx_attribution_window_hours', function($hours, $order) {
    return 720; // 30 days
}, 10, 2);
```

### Actions

```php
// After successful conversion
add_action('rzx_conversion_sent', function($order, $response, $conversion_id) {
    // Do something after conversion is tracked
}, 10, 3);

// After error
add_action('rzx_conversion_error', function($order, $error_message) {
    // Handle error
}, 10, 2);
```

## Changelog

### 1.1.0
- Full HPOS and Block Checkout support
- Configurable attribution window
- Extended metadata (products, coupons, payment method)
- Save conversion_id to order meta
- Improved cookie security (httponly, samesite)
- Compatibility with WordPress 6.7, WooCommerce 9.5, PHP 8.3

### 1.0.0
- Initial release

## License

GPL v2 or later
