<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Function to enqueue admin styles for the Bulk Edit settings page
function coding_bunny_bulk_admin_styles() {
	if ( isset( $_GET['page'] ) && ( sanitize_text_field( $_GET['page'] ) === 'coding-bunny-bulk-edit' || sanitize_text_field( $_GET['page'] ) === 'coding-bunny-bulk-edit-licence' ) && current_user_can( 'manage_options' ) ) {
		$version = filemtime( plugin_dir_path( __FILE__ ) . '../css/coding-bunny-bulk-editor.css' );
		wp_enqueue_style( 'coding-bunny-admin-styles', plugin_dir_url( __FILE__ ) . '../css/coding-bunny-bulk-editor.css', [], $version );
		wp_enqueue_script( 'coding-bunny-bulk-edit-js', plugin_dir_url( __FILE__ ) . '../js/coding-bunny-bulk-edit.js', [ 'jquery' ], $version, true );
	}
}
add_action( 'admin_enqueue_scripts', 'coding_bunny_bulk_admin_styles' );