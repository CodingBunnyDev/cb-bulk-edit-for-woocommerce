<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access to the file
}

// Function to check if a new version of the plugin is available
function coding_bunny_bulk_edit_check_version() {
    $current_version = CODING_BUNNY_BULK_EDIT_VERSION; // Current plugin version
    $url = 'https://www.coding-bunny.com/plugins-updates/be-check-version.php'; // URL of the script to check for updates

    // Perform a request to the server to check the version
    $response = wp_remote_post($url, [
        'body' => [
            'version' => sanitize_text_field($current_version), // Ensure the version string is sanitized before sending
        ],
        'timeout' => 15, // Set a reasonable timeout for the request
        'sslverify' => true, // Ensure SSL verification is enabled for security
    ]);

    // Handle the response
    if (is_wp_error($response)) {
        return false; // Return false if there was an error with the request
    }

    // Retrieve and decode the response body
    $body = wp_remote_retrieve_body($response);

    $decoded_body = json_decode($body, true);

    // Verify if the response is valid and check for an available update
    if (is_array($decoded_body) && isset($decoded_body['update_available']) && $decoded_body['update_available']) {
        // Return update details if a new version is available
        return [
            'update_available' => true,
            'latest_version'   => sanitize_text_field($decoded_body['latest_version']), // Sanitize the version data
            'download_url'     => esc_url_raw($decoded_body['download_url']), // Ensure the URL is safe
        ];
    }

    return ['update_available' => false]; // No update available
}

// Function to show an update notice in the WordPress admin dashboard
function coding_bunny_bulk_edit_version_update_notice() {
    $update_check = coding_bunny_bulk_edit_check_version(); // Check for available updates

    // If an update is available, display a notice
    if ($update_check['update_available']) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>';
        echo sprintf(
            __('A new version (%s) of the <b>CodingBunny Bulk Edit for WooCommerce</b> plugin is available. <a href="%s">Download the latest version here.</a>', 'coding-bunny-bulk-edit'),
            esc_html($update_check['latest_version']), // Escape version output for safety
            esc_url($update_check['download_url']) // Escape the download URL for safety
        );
        echo '</p>';
        echo '</div>';
    }
}

// Add the update notification to the WordPress admin dashboard
add_action('admin_notices', 'coding_bunny_bulk_edit_version_update_notice');