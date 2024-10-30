<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to enqueue admin styles for the Bulk Edit settings page
function coding_bunny_bulk_admin_styles() {
    // Check if we are on the correct admin page
    if ( isset( $_GET['page'] ) && ( $_GET['page'] === 'coding-bunny-bulk-edit' || $_GET['page'] === 'coding-bunny-bulk-edit-licence' ) ) {
        // Get the version of the CSS file based on its last modified time
        $version = filemtime( plugin_dir_path( __FILE__ ) . '../css/coding-bunny-bulk-editor.css' );

        // Enqueue the CSS file for admin styles
        wp_enqueue_style( 'coding-bunny-admin-styles', plugin_dir_url( __FILE__ ) . '../css/coding-bunny-bulk-editor.css', [], $version );
		
		// Carica il file JavaScript
        wp_enqueue_script( 'coding-bunny-bulk-edit-js', plugin_dir_url( __FILE__ ) . '../js/coding-bunny-bulk-edit.js', [ 'jquery' ], $version, true );
    }
}

// Hook the dmm_bulk_admin_styles function into the admin_enqueue_scripts action
add_action( 'admin_enqueue_scripts', 'coding_bunny_bulk_admin_styles' );