<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function coding_bunny_bulk_edit_page() {
	add_menu_page(
	'CodingBunny Bulk Edit for WooCommerce',
	'Bulk Edit',
	'manage_woocommerce',
	'coding-bunny-bulk-edit',
	'render_bulk_edit_products_page',
	'dashicons-edit',
	56
);
}
add_action( 'admin_menu', 'coding_bunny_bulk_edit_page' );