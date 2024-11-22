# CodingBunny Bulk Edit for WooCommerce

![License: GPL v3](https://img.shields.io/badge/license-GPL%20v3-blue.svg)
![WordPress Version](https://img.shields.io/badge/WordPress-%3E%3D%206.0-blue.svg)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.0-orange.svg)
![Version](https://img.shields.io/badge/version-1.1.0-green.svg)
![WooCommerce Tested Up To](https://img.shields.io/badge/WooCommerce-9.4.2-green.svg)

**CodingBunny Bulk Edit for WooCommerce** is a WordPress plugin that enables you to quickly and easily bulk edit WooCommerce products, saving you time and making it easier to manage your store’s product catalog. This plugin also provides options for a PRO version, unlocking additional advanced bulk editing features.

## Features

- **Quick Bulk Edit**: Easily bulk edit multiple WooCommerce product fields.
- **Admin Menu Integration**: Access plugin settings directly in the WordPress admin.
- **WooCommerce Compatibility**: Fully compatible with WooCommerce, including custom order tables.
- **PRO Version**: Advanced editing options for WooCommerce products, available with CodingBunny Bulk Edit PRO.
- **Multi-language Support**: Translation-ready for multilingual use.

## Installation

1. Download the plugin and unzip it.
2. Upload the `coding-bunny-bulk-edit` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin via the 'Plugins' menu in WordPress.
4. Access the **Settings** page through the WordPress admin menu to configure the plugin.

## Usage

After activating the plugin, go to **Settings** to configure bulk editing preferences. Once set up, the plugin will allow you to quickly manage and edit multiple WooCommerce products.

## PRO Version

For enhanced features, check out the **Get CodingBunny Bulk Edit for WooCommerce PRO!** link, available on the plugins list.

## Actions & Filters

- **`plugin_action_links_coding-bunny-bulk-edit`**: Adds "Settings" and "Get PRO" links to the plugin's row on the WordPress plugins page.
- **`plugins_loaded`**: Loads the text domain for translations.
- **`before_woocommerce_init`**: Ensures compatibility with WooCommerce’s custom order tables.

## Development

For developers interested in customization or contributing:

1. Clone this repository: `git clone https://github.com/CodingBunny/woocommerce-bulk-edit.git`
2. Navigate to the plugin's folder: `cd coding-bunny-bulk-edit`
3. Make necessary changes or add new features. Pull requests are always welcome!

### File Structure

- `inc/admin-menu.php` - Handles the admin menu configuration.
- `inc/licence-validation.php` - Manages license validation for the PRO version.
- `inc/settings-page.php` - Contains settings page definitions.
- `inc/enqueue-scripts.php` - Enqueues CSS and JS files.
- `inc/updates-check.php` - Checks for updates.

## Text Domain & Translations

This plugin is translation-ready, using the text domain `coding-bunny-bulk-edit` for translating plugin strings. Translation files are located in the `/languages` folder.

## WooCommerce Compatibility

The plugin is compatible with WooCommerce custom order tables, ensuring seamless integration with your WooCommerce environment.

## License

This plugin is licensed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).

## Author

**CodingBunny**  
[Website](https://coding-bunny.com)  
[Support](https://coding-bunny.com/support)

## Changelog

### 1.0.1
Fix - Solved licence validation error.

### 1.0.0
New - Initial release.

---

Thank you for using CodingBunny Bulk Edit for WooCommerce!
