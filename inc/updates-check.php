<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Function to check if a new version of the plugin is available
function coding_bunny_bulk_edit_check_version() {
	$current_version = CODING_BUNNY_BULK_EDIT_VERSION;
	$url = 'https://www.coding-bunny.com/plugins-updates/be-check-version.php';
	$response = wp_remote_post($url, [
		'body' => [
		'version' => sanitize_text_field($current_version),
	],
	'timeout' => 15,
	'sslverify' => true,
	]);

	if (is_wp_error($response)) {
		return false;
	}

	$body = wp_remote_retrieve_body($response);
	$decoded_body = json_decode($body, true);

	if (is_array($decoded_body) && isset($decoded_body['update_available']) && $decoded_body['update_available']) {
		return [
			'update_available' => true,
			'latest_version'   => sanitize_text_field($decoded_body['latest_version']),
			'download_url'     => esc_url_raw($decoded_body['download_url']),
		];
	}

	return ['update_available' => false];
}

// Function to show an update notice in the WordPress admin dashboard
function coding_bunny_bulk_edit_version_update_notice() {
	$update_check = coding_bunny_bulk_edit_check_version();

	if ($update_check['update_available']) {
		echo '<div class="notice notice-warning is-dismissible">';
		echo '<p>';
		printf(
		wp_kses_post(
		/* translators: 1: Latest plugin version, 2: Download URL. */
		__('A new version (%1$s) of the <b>CodingBunny Bulk Edit for WooCommerce</b> plugin is available. <a href="%2$s">Download the latest version here.</a>', 'coding-bunny-bulk-edit')
	),
	esc_html($update_check['latest_version']),
	esc_url($update_check['download_url'])
);
echo '</p>';
echo '</div>';
}
}
add_action('admin_notices', 'coding_bunny_bulk_edit_version_update_notice');