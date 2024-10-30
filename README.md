# CodingBunny Bulk Edit for WooCommerce

**Quickly edit your e-commerce products with ease.**

![License](https://img.shields.io/badge/License-GPL%20v3-blue.svg) ![WooCommerce](https://img.shields.io/badge/WooCommerce-Compatible-brightgreen.svg)

## Description

CodingBunny Bulk Edit for WooCommerce is a powerful yet simple plugin designed to let you quickly bulk edit your WooCommerce products. Whether you need to update pricing, descriptions, stock levels, or other product details, CodingBunny Bulk Edit simplifies the process with an easy-to-use interface integrated right into your WordPress admin panel.

## Features

- Quickly bulk edit WooCommerce products from the admin panel
- Includes a settings page for custom configurations
- "Get PRO" link for enhanced version with additional features
- Automatic updates and license validation
- Translation-ready
- WooCommerce compatibility up to version 9.3.2

## Requirements

- **WordPress** 6.0 or higher
- **PHP** 8.0 or higher
- **WooCommerce** plugin (latest version recommended)

## Installation

1. Download the latest release from the [releases page](https://https://github.com/CodingBunnyDev/cb-bulk-edit-for-woocommerce/releases).
2. Upload the plugin files to the `/wp-content/plugins/codingbunny-bulk-edit/` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Go to WooCommerce > Bulk Edit to start editing your products in bulk.

## Usage

1. **Navigate to the Plugin Settings**  
   Once activated, go to **WooCommerce > Bulk Edit** from your WordPress dashboard.

2. **Edit Products in Bulk**  
   Select the products you want to update and edit key attributes such as pricing, stock status, and more.

3. **PRO Version**  
   Need more advanced features? Click the "Get PRO" link in the plugins list for information on upgrading to the PRO version, which unlocks additional functionality.

### Available Hooks & Filters

- **`coding_bunny_bulk_edit_action_links`** - Filter for adding a custom "Settings" link in the plugins list.
- **`coding_bunny_be_add_pro_link`** - Adds a "Get PRO" link to the plugins list if the license is not active.

### Compatibility

This plugin is compatible with WooCommerce custom order tables, ensuring it works seamlessly with WooCommerce's latest custom table features.

## Development

### Directory Structure

- **`/inc`** - Contains the primary PHP files required for the plugin, including:
  - `admin-menu.php` - Handles the admin menu.
  - `licence-validation.php` - Manages license validation.
  - `settings-page.php` - Creates a settings page.
  - `enqueue-scripts.php` - Enqueues necessary CSS and JavaScript.
  - `updates-check.php` - Manages plugin updates.

### Translations

The plugin is fully translatable. Text domains are loaded in `/languages` directory.

## License

This plugin is licensed under the GNU General Public License v3.0 or later. See [LICENSE](LICENSE) for details.

## Changelog

### 1.0.1
Fix - Solved licence validation error.

### 1.0.0
New - Initial release.

## Support

For support or to report a bug, please visit [CodingBunny Support](https://coding-bunny.com/support).

## Authors

- **CodingBunny** - [CodingBunny.com](https://coding-bunny.com)

---

Thank you for using **CodingBunny Bulk Edit for WooCommerce**!
