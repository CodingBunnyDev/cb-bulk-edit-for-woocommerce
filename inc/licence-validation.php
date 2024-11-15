<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define the plugin version
define( 'CODING_BUNNY_BULK_EDIT_VERSION', '1.1.0' );

// Function to add a submenu item for licence validation
function coding_bunny_bulk_edit_submenu() {
    add_submenu_page(
        'coding-bunny-bulk-edit', // Parent slug
        __( "Manage Licence", 'coding-bunny-bulk-edit' ), // Page title
        __( "Manage Licence", 'coding-bunny-bulk-edit' ), // Menu title
        'manage_options', // Capability required to access this menu
        'coding-bunny-bulk-edit-licence', // Menu slug
        'coding_bunny_bulk_edit_licence_page' // Function to display the page content
    );

    // Check if the licence is inactive
    $licence_data = get_option( 'coding_bunny_bulk_edit_licence_data', [ 'key' => '', 'email' => '' ] );
    $licence_key = esc_attr( $licence_data['key'] );
    $licence_email = esc_attr( $licence_data['email'] );
    $licence_active = coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email );

    // Add "Go Pro" menu item if the licence is inactive
    if ( !$licence_active['success'] ) {
		add_submenu_page(
		    'coding-bunny-bulk-edit', // Usa lo stesso slug del parent
		    __( "Go Pro", 'coding-bunny-bulk-edit' ), // Titolo della pagina
		    __( "Go Pro", 'coding-bunny-bulk-edit' ), // Titolo del menu
		    'manage_options', // Capacità richiesta
		    'coding-bunny-bulk-edit-pro', // Slug del menu
		    'coding_bunny_bulk_edit_pro_redirect' // Funzione di reindirizzamento
		);
    }
}

// Hook the coding_bunny_whatsapp_submenu function into the admin_menu action
add_action( 'admin_menu', 'coding_bunny_bulk_edit_submenu' );

// Function to handle redirection to external URL
function coding_bunny_bulk_edit_pro_redirect() {
    if (!headers_sent()) {
        wp_safe_redirect( 'https://www.coding-bunny.com/bulk-edit/' );
        exit;
    }
}

// Function to add custom CSS to highlight the "Go Pro" menu item
function coding_bunny_bulk_edit_admin_styles() {
    ?>
    <style>
        /* Target the 'Go Pro' submenu item by matching the slug in the href */
        #toplevel_page_coding-bunny-bulk-edit .wp-submenu li a[href$='coding-bunny-bulk-edit-pro'] {
            background-color: #00a22a !important; /* Apply custom background color */
            color: #fff !important; /* Apply custom text color */
            font-weight: bold !important; /* Make the text bold */
        }
        #toplevel_page_coding-bunny-bulk-edit .wp-submenu li a[href$='coding-bunny-bulk-edit-pro']:hover {
            background-color: #008a1f !important; /* Change background on hover */
            color: #fff !important; /* Keep the text color white on hover */
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

    // Handle the licence validation
    if ( isset( $_POST['validate_licence'] ) ) {
        $licence_key = sanitize_text_field( $_POST['licence_key'] );
        $licence_email = sanitize_email( $_POST['licence_email'] );
        $response = coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email );

        if ( $response['success'] ) {
            // Save the valid licence key and email in the database
            update_option( 'coding_bunny_bulk_edit_licence_data', [ 'key' => $licence_key, 'email' => $licence_email ] );
            echo '<div class="notice notice-success"><p>' . __( "Licence successfully validated!", 'coding-bunny-bulk-edit' ) . '</p></div>';
            echo '<script>setTimeout(function(){ location.reload(); }, 1000);</script>'; // Reload the page after 1 second
        } else {
            echo '<div class="notice notice-error"><p>' . __( "Incorrect licence key or email: ", 'coding-bunny-bulk-edit' ) . esc_html( $response['error'] ) . '</p></div>';
        }
    }

    // Handle the licence deactivation
    if ( isset( $_POST['deactivate_licence'] ) ) {
        delete_option( 'coding_bunny_bulk_edit_licence_data' );
        $licence_key = '';
        $licence_email = '';
        echo '<div class="notice notice-success"><p>' . __( "Licence successfully deactivated!", 'coding-bunny-bulk-edit' ) . '</p></div>';
        echo '<script>setTimeout(function(){ location.reload(); }, 1000);</script>'; // Reload the page after 1 second
    }

    ?>
    <div class="wrap coding-bunny-bulk-edit-wrap">
    <h1><?php esc_html_e( 'CodingBunny Bulk Edit for WooCommerce', 'coding-bunny-bulk-edit' ); ?> 
       <span style="font-size: 10px;">v<?php echo CODING_BUNNY_BULK_EDIT_VERSION; ?></span></h1>
    <h3><?php esc_html_e( "Manage Licence", 'coding-bunny-bulk-edit' ); ?></h3>
    <form method="post" action="">
        <div class="cb-flex-container">
            <div class="cb-flex-item">
                <label for="licence_email"><?php _e( "Email Account:", 'coding-bunny-bulk-edit' ); ?></label>
            </div>
            <div class="cb-flex-item">
                <input type="email" id="licence_email" name="licence_email" value="<?php echo esc_attr( $licence_email ); ?>" required />
            </div>
            <div class="cb-flex-item">
                <label for="licence_key"><?php _e( "Licence Key:", 'coding-bunny-bulk-edit' ); ?></label>
            </div>
            <div class="cb-flex-item">
                <input type="text" id="licence_key" name="licence_key" 
                    value="<?php echo $licence_active['success'] ? str_repeat('*', strlen( $licence_key )) : esc_attr( $licence_key ); ?>" 
                    required />   
            </div>
            <div class="cb-flex-item">
                <?php if ( $licence_active['success'] ) : ?>
                    <button type="submit" name="deactivate_licence" class="button button-primary">
                        <?php _e( "Deactive Licence", 'coding-bunny-bulk-edit' ); ?>
                    </button>
                <?php else : ?>
                    <button type="submit" name="validate_licence" class="button button-primary">
                        <?php _e( "Active Licence", 'coding-bunny-bulk-edit' ); ?>
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
                        // Format the expiration date
                        $expiration_date = DateTime::createFromFormat( 'Y-m-d', $licence_active['expiration'] );
                        echo esc_html( $expiration_date->format( 'd-m-Y' ) ); 
                    ?>
                </span>
            </div>
        <?php endif; ?>
    </form>
    <p>
        <?php esc_html_e( "Having problems with your licence? Contact our support: ", 'coding-bunny-whatsapp-chat' ); ?>
        <a href="mailto:support@coding-bunny.com">support@coding-bunny.com</a>
    </p>
    <hr>
    <p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> - <?php esc_html_e( 'Powered by CodingBunny', 'coding-bunny-bulk-edit' ); ?></p>
</div>
    <?php
}

// Function to validate the licence key
function coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email ) {
    $url = 'https://www.coding-bunny.com/plugins-licence/be-active-licence.php'; // Replace with your server URL

    $response = wp_remote_post( $url, [
        'body' => json_encode( [ 'licence_key' => $licence_key, 'email' => $licence_email ] ),
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
        return [ 'success' => true, 'expiration' => $body['expiration'] ]; // Get expiration date from server response
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
            add_action( 'admin_notices', function() use ( $days_until_expiration ) {
                echo '<div class="notice notice-warning is-dismissible"><p>' . 
                    sprintf( 
                        __( 'Your <b>CodingBunny Bulk Edit for WooCommerce</b> licence expires in <b>%d days</b>! <a href="%s">Renew now.</a>', 'coding-bunny-bulk-edit' ), 
                        $days_until_expiration, 
                        esc_url( 'mailto:support@coding-bunny.com' ) 
                    ) . 
                '</p></div>';
            });
        }
    }
}

// Hook the coding_bunny_bulk_edit_licence_expiration_notice function into the admin_init action
add_action( 'admin_init', 'coding_bunny_bulk_edit_licence_expiration_notice' );