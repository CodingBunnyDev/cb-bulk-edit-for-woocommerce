<?php

/**
* Plugin Name: CodingBunny Bulk Edit for WooCommerce
* Plugin URI:  https://coding-bunny.com/woocommerce-bulk-edit/
* Description: Quickly edit your e-commerce products.
* Version:     1.2.0
* Requires at least: 6.0
* Requires PHP: 8.0
* Author:      CodingBunny
* Author URI:  https://coding-bunny.com
* Text Domain: coding-bunny-bulk-edit
* Domain Path: /languages
* License: GNU General Public License v3.0 or later
* WC tested up to: 9.4.3
* Requires Plugins: woocommerce
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$inc_dir = plugin_dir_path( __FILE__ ) . 'inc/';

$files_to_include = [
	'admin-menu.php',
	'licence-validation.php',
	'settings-page.php',
	'enqueue-scripts.php',
	'updates-check.php'
];

foreach ( $files_to_include as $file ) {
	$file_path = $inc_dir . $file;
	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	}
}

// Load plugin text domain for translations
function coding_bunny_bulk_edit_load_textdomain() {
	load_plugin_textdomain( 'coding-bunny-bulk-edit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'coding_bunny_bulk_edit_load_textdomain' );

// Add "Settings" link in the plugins list page
function coding_bunny_bulk_edit_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=coding-bunny-bulk-edit' ) ) . '">' . esc_html__( 'Settings', 'coding-bunny-bulk-edit' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'coding_bunny_bulk_edit_action_links' );

// Add "Get PRO" link in the plugins list page
function coding_bunny_be_add_pro_link( $links ) {
	if ( ! be_is_licence_active() ) {
		$pro_link = '<a href="https://coding-bunny.com/bulk-edit/" style="color: #00A32A; font-weight: bold;">' . esc_html__( 'Get CodingBunny Bulk Edit for WooCommerce PRO!', 'coding-bunny-bulk-edit' ) . '</a>';

		array_unshift( $links, $pro_link );
	}

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'coding_bunny_be_add_pro_link' );

// Declare compatibility with WooCommerce custom order tables
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );