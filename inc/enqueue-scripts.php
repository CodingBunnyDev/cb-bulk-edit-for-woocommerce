<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to enqueue admin styles for the Bulk Edit settings page
function coding_bunny_bulk_admin_styles() {
    // Check if we are on the correct admin page and if the user has the correct capabilities
    if ( isset( $_GET['page'] ) && ( sanitize_text_field( $_GET['page'] ) === 'coding-bunny-bulk-edit' || sanitize_text_field( $_GET['page'] ) === 'coding-bunny-bulk-edit-licence' ) && current_user_can( 'manage_options' ) ) {
        // Get the version of the CSS file based on its last modified time
        $version = filemtime( plugin_dir_path( __FILE__ ) . '../css/coding-bunny-bulk-editor.css' );

        // Enqueue the CSS file for admin styles
        wp_enqueue_style( 'coding-bunny-admin-styles', plugin_dir_url( __FILE__ ) . '../css/coding-bunny-bulk-editor.css', [], $version );
        
        // Enqueue the JavaScript file
        wp_enqueue_script( 'coding-bunny-bulk-edit-js', plugin_dir_url( __FILE__ ) . '../js/coding-bunny-bulk-edit.js', [ 'jquery' ], $version, true );
    }
}

// Hook the coding_bunny_bulk_admin_styles function into the admin_enqueue_scripts action
add_action( 'admin_enqueue_scripts', 'coding_bunny_bulk_admin_styles' );