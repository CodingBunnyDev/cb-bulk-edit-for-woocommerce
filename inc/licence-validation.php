<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CODING_BUNNY_BULK_EDIT_VERSION', '1.2.0' );

function coding_bunny_bulk_edit_submenu() {
	add_submenu_page(
	'coding-bunny-bulk-edit',
	__( "Manage Licence", 'coding-bunny-bulk-edit' ),
	__( "Manage Licence", 'coding-bunny-bulk-edit' ),
	'manage_options',
	'coding-bunny-bulk-edit-licence',
	'coding_bunny_bulk_edit_licence_page'
);

$licence_data = get_option( 'coding_bunny_bulk_edit_licence_data', [ 'key' => '', 'email' => '' ] );
$licence_key = esc_attr( $licence_data['key'] );
$licence_email = esc_attr( $licence_data['email'] );
$licence_active = coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email );

if ( !$licence_active['success'] ) {
	add_submenu_page(
	'coding-bunny-bulk-edit',
	__( "Go Pro", 'coding-bunny-bulk-edit' ),
	__( "Go Pro", 'coding-bunny-bulk-edit' ),
	'manage_options',
	'coding-bunny-bulk-edit-pro',
	'coding_bunny_bulk_edit_pro_redirect'
);
}
}
add_action( 'admin_menu', 'coding_bunny_bulk_edit_submenu' );

function coding_bunny_bulk_edit_pro_redirect() {
if (!headers_sent()) {
wp_safe_redirect( 'https://www.coding-bunny.com/bulk-edit/' );
exit;
}
}

function coding_bunny_bulk_edit_admin_styles() {
?>
<style>
#toplevel_page_coding-bunny-bulk-edit .wp-submenu li a[href$='coding-bunny-bulk-edit-pro'] {
	background-color: #00a22a !important;
	color: #fff !important;
	font-weight: bold !important;
}
#toplevel_page_coding-bunny-bulk-edit .wp-submenu li a[href$='coding-bunny-bulk-edit-pro']:hover {
	background-color: #008a1f !important;
	color: #fff !important;
}
</style>
<?php
}
add_action( 'admin_head', 'coding_bunny_bulk_edit_admin_styles' );

// Function to display the licence validation page content
function coding_bunny_bulk_edit_licence_page() {
$licence_data = get_option( 'coding_bunny_bulk_edit_licence_data', [ 'key' => '', 'email' => '' ] );
$licence_key = $licence_data['key'];
$licence_email = $licence_data['email'];
$licence_active = coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email );

if ( isset( $_POST['validate_licence'] ) ) {
	$licence_key = sanitize_text_field( $_POST['licence_key'] );
	$licence_email = sanitize_email( $_POST['licence_email'] );
	$response = coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email );

	if ( $response['success'] ) {
		update_option( 'coding_bunny_bulk_edit_licence_data', [ 'key' => $licence_key, 'email' => $licence_email ] );
		echo '<div class="notice notice-success"><p>' . esc_html__( "Licence successfully validated!", 'coding-bunny-bulk-edit' ) . '</p></div>';
		echo '<script>setTimeout(function(){ location.reload(); }, 1000);</script>';
	} else {
		echo '<div class="notice notice-error"><p>' . esc_html__( "Incorrect licence key or email: ", 'coding-bunny-bulk-edit' ) . esc_html( $response['error'] ) . '</p></div>';
	}
}

if ( isset( $_POST['deactivate_licence'] ) ) {
	delete_option( 'coding_bunny_bulk_edit_licence_data' );
	$licence_key = '';
	$licence_email = '';
	echo '<div class="notice notice-success"><p>' . esc_html__( "Licence successfully deactivated!", 'coding-bunny-bulk-edit' ) . '</p></div>';
	echo '<script>setTimeout(function(){ location.reload(); }, 1000);</script>';
}

?>
<div class="wrap coding-bunny-bulk-edit-wrap">
	<h1><?php esc_html_e( 'CodingBunny Bulk Edit for WooCommerce', 'coding-bunny-bulk-edit' ); ?> 
		<span style="font-size: 10px;">v<?php echo esc_html( CODING_BUNNY_BULK_EDIT_VERSION ); ?></span></h1>
		<h3>
			<span class="dashicons dashicons-admin-network"></span>
			<?php esc_html_e( "Manage Licence", 'coding-bunny-bulk-edit' ); ?>
		</h3>
		<form method="post" action="">
			<div class="cb-flex-container">
				<div class="cb-flex-item">
					<label for="licence_email"><?php esc_html_e( "Email Account:", 'coding-bunny-bulk-edit' ); ?></label>
				</div>
				<div class="cb-flex-item">
					<input type="email" id="licence_email" name="licence_email" value="<?php echo esc_attr( $licence_email ); ?>" required />
				</div>
				<div class="cb-flex-item">
					<label for="licence_key"><?php esc_html_e( "Licence Key:", 'coding-bunny-bulk-edit' ); ?></label>
				</div>
				<div class="cb-flex-item">
					<input type="text" id="licence_key" name="licence_key" 
					value="<?php echo $licence_active['success'] ? str_repeat('*', strlen( $licence_key )) : esc_attr( $licence_key ); ?>" 
					required />   
				</div>
				<div class="cb-flex-item">
					<?php if ( $licence_active['success'] ) : ?>
						<button type="submit" name="deactivate_licence" class="button button-primary">
							<?php esc_html_e( "Deactive Licence", 'coding-bunny-bulk-edit' ); ?>
						</button>
					<?php else : ?>
						<button type="submit" name="validate_licence" class="button button-primary">
							<?php esc_html_e( "Active Licence", 'coding-bunny-bulk-edit' ); ?>
						</button>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( $licence_active['success'] ) : ?>
				<div style="margin-top: 20px;">
					<div style="margin-top: 20px; font-weight: bold;">
						<span style="color: green;">&#x25CF;</span> <?php esc_html_e( "Licence Active", 'coding-bunny-bulk-edit' ); ?>
					</div><br>
					<?php esc_html_e( "Your licence expires on:", 'coding-bunny-bulk-edit' ); ?>
					<span style="font-weight: bold;">
						<?php 
						$expiration_date = DateTime::createFromFormat( 'Y-m-d', $licence_active['expiration'] );
						echo esc_html( $expiration_date->format( 'd-m-Y' ) ); 
						?>
					</span>
				</div>
			<?php endif; ?>
		</form>
		<p>
			<?php esc_html_e( "Having problems with your licence? Contact our support: ", 'coding-bunny-bulk-edit' ); ?>
			<a href="mailto:support@coding-bunny.com">support@coding-bunny.com</a>
		</p>
		<hr>
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> - <?php esc_html_e( 'Powered by CodingBunny', 'coding-bunny-bulk-edit' ); ?></p>
	</div>
	<?php
}

// Function to validate the licence key
function coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email ) {
	$url = 'https://www.coding-bunny.com/plugins-licence/be-active-licence.php';

	$response = wp_remote_post( $url, [
		'body' => wp_json_encode( [ 'licence_key' => $licence_key, 'email' => $licence_email ] ),
		'headers' => [
		'Content-Type' => 'application/json',
	],
	'timeout' => 15,
	'sslverify' => true,
	]);

	if ( is_wp_error( $response ) ) {
		return [ 'success' => false, 'error' => $response->get_error_message() ];
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( isset( $body['success'] ) && $body['success'] ) {
		return [ 'success' => true, 'expiration' => $body['expiration'] ];
	} else {
		return [ 'success' => false, 'error' => isset( $body['error'] ) ? $body['error'] : __( "Incorrect licence key or email", 'coding-bunny-bulk-edit' ) ];
	}
}

// Function to show the warning notice on the dashboard
function coding_bunny_bulk_edit_licence_expiration_notice() {
	$licence_data = get_option( 'coding_bunny_bulk_edit_licence_data', [ 'key' => '', 'email' => '' ] );
	$licence_key = $licence_data['key'];
	$licence_email = $licence_data['email'];
	$licence_active = coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email );

	if ( $licence_active['success'] ) {
		$expiration_date = DateTime::createFromFormat( 'Y-m-d', $licence_active['expiration'] );
		$current_date = new DateTime();
		$days_until_expiration = $expiration_date->diff( $current_date )->days;

		if ( $days_until_expiration <= 30 && $days_until_expiration > 0 ) {
			add_action('admin_notices', function () use ($days_until_expiration) {
				if (!isset($days_until_expiration) || !is_numeric($days_until_expiration)) {
					return;
				}
				echo '<div class="notice notice-warning is-dismissible">';
				echo '<p>';
				printf(
				wp_kses_post(
				/* translators: 1: Number of days until expiration, 2: Renewal link. */
				__( 'Your <b>CodingBunny Bulk Edit for WooCommerce</b> licence expires in <b>%1$d days</b>! <a href="%2$s">Renew now.</a>', 'coding-bunny-bulk-edit' )
			),
			intval($days_until_expiration),
			esc_url('mailto:support@coding-bunny.com')
		);
		echo '</p>';
		echo '</div>';
	});
}
}
}
add_action( 'admin_init', 'coding_bunny_bulk_edit_licence_expiration_notice' );

// Function to add the licence expiration badge to the menu
function coding_bunny_bulk_edit_licence_menu_badge() {
global $submenu;

if ( isset( $submenu['coding-bunny-bulk-edit'] ) ) {
$licence_data = get_option( 'coding_bunny_bulk_edit_licence_data', [ 'key' => '', 'email' => '' ] );
$licence_key = sanitize_text_field( $licence_data['key'] );
$licence_email = sanitize_email( $licence_data['email'] );
$licence_active = coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email );

if ( $licence_active['success'] ) {
	$expiration_date = DateTime::createFromFormat( 'Y-m-d', $licence_active['expiration'] );
	$current_date = new DateTime();
	$days_until_expiration = $expiration_date->diff( $current_date )->days;

	if ( $days_until_expiration <= 30 && $days_until_expiration > 0 ) {
		foreach ( $submenu['coding-bunny-bulk-edit'] as &$item ) {
			if ( $item[2] === 'coding-bunny-bulk-edit-licence' ) {
				$item[0] .= ' <span class="update-plugins count-1"><span class="plugin-count">!</span></span>';
			}
		}
	}
}
}
}
add_action( 'admin_menu', 'coding_bunny_bulk_edit_licence_menu_badge', 100 );