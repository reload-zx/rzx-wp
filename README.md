# RZX.bio Conversion Tracking for WooCommerce

[![WordPress](https://img.shields.io/badge/WordPress-5.8%20to%206.7-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-6.0%20to%209.5-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%20to%208.3-777BB4.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.1.0-orange.svg)](https://github.com/reload-zx/rzx-wp/releases)

Track WooCommerce sales and attribute revenue to your RZX.bio links automatically.

## Description

RZX.bio Conversion Tracking is a WordPress plugin that seamlessly integrates your WooCommerce store with [RZX.bio](https://rzx.bio), enabling automatic conversion tracking and revenue attribution for your bio links.

When a visitor clicks one of your RZX.bio links and makes a purchase on your WooCommerce store, the plugin automatically tracks the conversion and sends the data to your RZX.bio dashboard.

## Features

- **Automatic Conversion Tracking** - Tracks purchases automatically when orders are completed or processed
- **HPOS Compatible** - Full support for WooCommerce High-Performance Order Storage
- **Block Checkout Support** - Works with both classic and block-based checkout
- **Configurable Attribution Window** - Choose between 24 hours, 7 days, or 30 days
- **Secure Cookie Handling** - Uses httponly and samesite cookies for security
- **Extended Metadata** - Tracks products, coupons, shipping methods, and payment info
- **Duplicate Prevention** - Prevents sending the same conversion multiple times
- **First Order Detection** - Identifies new vs. returning customers
- **Debug Mode** - Log API requests for troubleshooting
- **Connection Testing** - Verify your API key with one click

## Requirements

- WordPress 5.8 or higher
- WooCommerce 6.0 or higher
- PHP 7.4 or higher
- An active [RZX.bio](https://rzx.bio) account

## Installation

### From GitHub Release

1. Download the latest release ZIP file from the [Releases page](https://github.com/reload-zx/rzx-wp/releases)
2. In your WordPress admin, go to **Plugins > Add New > Upload Plugin**
3. Choose the downloaded ZIP file and click **Install Now**
4. After installation, click **Activate Plugin**

### Manual Installation

1. Download or clone this repository
2. Copy the `rzx-conversion-tracking` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress

## Configuration

1. Go to **WooCommerce > Settings > RZX.bio**
2. Enter your **API Key** (get it from your [RZX.bio dashboard](https://rzx.bio))
3. Click **Test Connection** to verify your API key
4. Configure the following options:

| Setting | Description | Default |
|---------|-------------|---------|
| **Enable Tracking** | Turn conversion tracking on/off | Enabled |
| **Track Product Details** | Include product information in conversion data | Enabled |
| **Attribution Window** | Time window for attributing conversions to clicks | 7 days |
| **Debug Mode** | Log API requests to WooCommerce logs | Disabled |

## How It Works

1. A visitor clicks your RZX.bio link (e.g., `https://yoursite.com/?_rzx=12345`)
2. The plugin captures the tracking parameter and stores it in a secure cookie
3. When the visitor completes a purchase, the plugin sends conversion data to RZX.bio
4. View your conversion analytics in the RZX.bio dashboard

## Tracked Data

The plugin sends the following data to RZX.bio:

- Order total and currency
- Number of items purchased
- Payment method
- Shipping method
- Coupon codes used
- Product details (if enabled):
  - Product ID, SKU, name
  - Quantity and price
  - Category
- First order indicator
- Plugin and WooCommerce versions

**Privacy**: Customer email addresses are hashed (MD5) before sending for analytics purposes. No personal identifiable information (PII) is transmitted.

## Developer Hooks

### Filters

```php
// Modify the conversion payload before sending
add_filter( 'rzx_conversion_payload', function( $payload, $order ) {
    $payload['custom_field'] = 'custom_value';
    return $payload;
}, 10, 2 );

// Modify metadata included in conversion
add_filter( 'rzx_conversion_metadata', function( $metadata, $order ) {
    $metadata['custom_meta'] = 'value';
    return $metadata;
}, 10, 2 );

// Adjust attribution window per order
add_filter( 'rzx_attribution_window_hours', function( $hours, $order ) {
    return 720; // 30 days
}, 10, 2 );

// Modify available settings
add_filter( 'rzx_settings', function( $settings ) {
    // Add or modify settings
    return $settings;
} );
```

### Actions

```php
// Triggered after successful conversion tracking
add_action( 'rzx_conversion_sent', function( $order, $response, $conversion_id ) {
    // Do something after conversion is tracked
}, 10, 3 );

// Triggered when conversion tracking fails
add_action( 'rzx_conversion_error', function( $order, $error_message ) {
    // Handle error
}, 10, 2 );
```

## Troubleshooting

### Conversions not being tracked

1. Ensure the plugin is activated and tracking is enabled
2. Verify your API key with the **Test Connection** button
3. Enable **Debug Mode** and check logs in **WooCommerce > Status > Logs** (look for `rzx-tracking`)
4. Ensure the visitor arrived via an RZX.bio link with the `_rzx` parameter

### Connection test fails

1. Check that your API key is correct
2. Ensure your server can make outbound HTTPS requests
3. Check if a firewall or security plugin is blocking requests to `rzx.bio`

### Orders showing as already tracked

This is expected behavior - the plugin prevents duplicate tracking. Each order is only tracked once.

## Frequently Asked Questions

**Q: Do I need a paid RZX.bio account?**
A: Check [RZX.bio](https://rzx.bio) for current pricing and features.

**Q: Does this work with WooCommerce Subscriptions?**
A: The plugin tracks the initial subscription order. Renewal tracking depends on how renewals are processed.

**Q: Can I track conversions from multiple RZX.bio accounts?**
A: Currently, the plugin supports one API key at a time.

**Q: Is customer data shared with RZX.bio?**
A: Only hashed email addresses are sent for analytics. No personal information is transmitted.

## Changelog

### 1.1.0 (2025-01-14)
- Added: Full HPOS (High-Performance Order Storage) support
- Added: Block Checkout compatibility
- Added: Configurable attribution windows (24h, 7 days, 30 days)
- Added: Extended metadata (products, coupons, payment methods)
- Added: Conversion ID storage in order metadata
- Improved: Cookie security with httponly and samesite attributes
- Improved: Connection status display in settings

### 1.0.0
- Initial release
- Basic conversion tracking for WooCommerce orders
- API key configuration
- Debug logging

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## Support

- [Report Issues](https://github.com/reload-zx/rzx-wp/issues)
- [RZX.bio Help Center](https://rzx.bio)

---

Made with care for the WooCommerce community.
