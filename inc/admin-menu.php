<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to add a menu item to the WordPress admin
function coding_bunny_bulk_edit_page() {
    // Add a new top-level menu page
    add_menu_page(
        'CodingBunny Bulk Edit for WooCommerce', // Page title
        'Bulk Edit', // Menu title
        'manage_woocommerce', // Capability required to access this menu
        'coding-bunny-bulk-edit', // Menu slug
        'render_bulk_edit_products_page', // Function to display the page content
        'dashicons-edit', // Icon for the menu item
        56 // Position in the menu
    );
}

// Hook the coding_bunny_bulk_edit_page function into the admin_menu action
add_action( 'admin_menu', 'coding_bunny_bulk_edit_page' );