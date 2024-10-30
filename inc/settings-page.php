<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Function to check if the licence is valid
function be_is_licence_active() {
    $licence_data = get_option( 'coding_bunny_bulk_edit_licence_data', ['key' => '', 'email' => ''] );
    $licence_key = $licence_data['key'];
    $licence_email = $licence_data['email'];

    if ( empty( $licence_key ) || empty( $licence_email ) ) {
        return false;
    }

    $response = coding_bunny_bulk_edit_validate_licence( $licence_key, $licence_email );
    return $response['success'];
}

// Hook the function into the 'plugins_loaded' action to ensure text domain is loaded after the plugin is fully initialized
add_action( 'plugins_loaded', 'coding_bunny_bulk_edit_load_textdomain' );

// Renderizza la pagina di modifica in blocco dei prodotti
function render_bulk_edit_products_page() {
    $licence_active = be_is_licence_active();
    
	// Controlla se è stata inviata una richiesta per aggiornare i prodotti in blocco
    if ( isset( $_POST['bulk_update_products'] ) ) {
        update_bulk_products();
    }

    // Controlla se è stata inviata una richiesta per aggiornare singoli prodotti
    if ( isset( $_POST['update_products'] ) ) {
        update_individual_products();
    }

    // Filtri per categoria, disponibilità, attributo, termine e stato
    $selected_category = isset( $_GET['product_category'] ) ? sanitize_text_field( wp_unslash( $_GET['product_category'] ) ) : '';
    $availability_filter = isset( $_GET['availability'] ) ? sanitize_text_field( wp_unslash( $_GET['availability'] ) ) : '';
    $selected_attribute = isset( $_GET['product_attribute'] ) ? sanitize_text_field( wp_unslash( $_GET['product_attribute'] ) ) : '';
    $selected_term = isset( $_GET['product_term'] ) ? sanitize_text_field( wp_unslash( $_GET['product_term'] ) ) : '';
    $selected_status = isset( $_GET['post_status'] ) ? sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) : '';
    
    // Nuovo parametro di ordinamento
    $order_by = isset( $_GET['order_by'] ) ? sanitize_text_field( wp_unslash( $_GET['order_by'] ) ) : 'ASC';

    // Nuovo parametro di ricerca
    $search_product_name = isset( $_GET['search_product_name'] ) ? sanitize_text_field( wp_unslash( $_GET['search_product_name'] ) ) : '';

    // Recupera le categorie e gli attributi
    $categories = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false ] );
    $attributes = wc_get_attribute_taxonomies();

	echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'CodingBunny Bulk Edit for WooCommerce', 'coding-bunny-bulk-edit' ) . 
         ' <span style="font-size: 10px;">v' . CODING_BUNNY_BULK_EDIT_VERSION . '</span></h1>';
    echo '<form method="get" action="">';
    echo '<input type="hidden" name="page" value="coding-bunny-bulk-edit">';
echo '</div>';

    
    // Inizio del contenitore per i filtri
    echo '<div class="filter-container">';
    
    // Barra di ricerca per nome prodotto
    echo '<label for="search_product_name" class="filter-label">' . esc_html__( 'Product Finder', 'coding-bunny-bulk-edit' ) . '</label>';
	echo '<input type="text" name="search_product_name" id="search_product_name" class="filter-input" value="' . esc_attr( $search_product_name ) . '" placeholder="' . esc_attr__( 'Product name...', 'coding-bunny-bulk-edit' ) . '" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';

    
    // Aggiunge il filtro per nome prodotto se presente
if ( ! empty( $search_product_name ) ) {
    add_filter( 'posts_search', 'search_only_in_titles', 10, 2 );

    function search_only_in_titles( $search, $wp_query ) {
        global $wpdb;

        if ( isset( $wp_query->query_vars['s'] ) && ! empty( $wp_query->query_vars['s'] ) ) {
            $search_term = esc_sql( $wp_query->query_vars['s'] );
            $search = " AND {$wpdb->posts}.post_title LIKE '%{$search_term}%'";
        }

        return $search;
    }

    $args['s'] = $search_product_name;  // Passa il termine di ricerca
}
    
    // Selettore di ordinamento
    echo '<label for="order_by" class="filter-label">' . esc_html__( 'Sort by', 'coding-bunny-bulk-edit' ) . '</label>';
    echo '<select name="order_by" id="order_by" class="filter-select">';
    echo '<option value="ASC"' . selected( $order_by, 'ASC', false ) . '>' . esc_html__( 'A-Z', 'coding-bunny-bulk-edit' ) . '</option>';
    echo '<option value="DESC"' . selected( $order_by, 'DESC', false ) . '>' . esc_html__( 'Z-A', 'coding-bunny-bulk-edit' ) . '</option>';
    echo '</select>';
    
	// Filtro per disponibilità
	echo '<label for="availability" class="filter-label">' . esc_html__( 'Filter by availability', 'coding-bunny-bulk-edit' ) . '</label>';
	echo '<select name="availability" id="availability" class="filter-select" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
	echo '<option value="">' . esc_html__( 'All', 'coding-bunny-bulk-edit' ) . '</option>';
	echo '<option value="instock"' . selected( $availability_filter, 'instock', false ) . '>' . esc_html__( 'Available', 'coding-bunny-bulk-edit' ) . '</option>';
	echo '<option value="outofstock"' . selected( $availability_filter, 'outofstock', false ) . '>' . esc_html__( 'Out of Stock', 'coding-bunny-bulk-edit' ) . '</option>';
	echo '</select>';
    
    // Filtro per stato
    echo '<label for="post_status" class="filter-label">' . esc_html__( 'Filter by Status', 'coding-bunny-bulk-edit' ) . '</label>';
    echo '<select name="post_status" id="post_status" class="filter-select" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';

    echo '<option value="">' . esc_html__( 'All', 'coding-bunny-bulk-edit' ) . '</option>';
    echo '<option value="publish"' . selected( $selected_status, 'publish', false ) . '>' . esc_html__( 'Published', 'coding-bunny-bulk-edit' ) . '</option>';
    echo '<option value="draft"' . selected( $selected_status, 'draft', false ) . '>' . esc_html__( 'Draft', 'coding-bunny-bulk-edit' ) . '</option>';
    echo '</select>';
    
    echo '</div>'; // Fine del contenitore per i filtri
    // Inizio del contenitore per i filtri
    echo '<div class="filter-container">';
    
    // Filtro per categoria
    echo '<label for="product_category" class="filter-label">' . esc_html__( 'Filter by Category', 'coding-bunny-bulk-edit' ) . '</label>';
    echo '<select name="product_category" id="product_category" class="filter-select">';
    echo '<option value="">' . esc_html__( 'All', 'coding-bunny-bulk-edit' ) . '</option>';
    foreach ( $categories as $category ) {
        echo '<option value="' . esc_attr( $category->term_id ) . '"' . selected( $selected_category, $category->term_id, false ) . '>' . esc_html( $category->name ) . '</option>';
    }
    echo '</select>';
    
    // Filtro per attributi
    echo '<label for="product_attribute" class="filter-label">' . esc_html__( 'Filter by Attributes', 'coding-bunny-bulk-edit' ) . '</label>';
    echo '<select name="product_attribute" id="product_attribute" class="filter-select" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
    echo '<option value="">' . esc_html__( 'All', 'coding-bunny-bulk-edit' ) . '</option>';
    foreach ( $attributes as $attribute ) {
        echo '<option value="' . esc_attr( $attribute->attribute_name ) . '"' . selected( $selected_attribute, $attribute->attribute_name, false ) . '>' . esc_html( $attribute->attribute_label ) . '</option>';
    }
    echo '</select>';

    // Filtro per termini degli attributi, se un attributo è stato selezionato
    if ( ! empty( $selected_attribute ) ) {
        $terms = get_terms( [ 'taxonomy' => 'pa_' . $selected_attribute, 'hide_empty' => false ] );
        echo '<label for="product_term" class="filter-label">' . esc_html__( 'Filter by term', 'coding-bunny-bulk-edit' ) . '</label>';
        echo '<select name="product_term" id="product_term" class="filter-select">';
        echo '<option value="">' . esc_html__( 'All', 'coding-bunny-bulk-edit' ) . '</option>';
        foreach ( $terms as $term ) {
            echo '<option value="' . esc_attr( $term->slug ) . '"' . selected( $selected_term, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
        }
        echo '</select>';
    }

    // Pulsante per applicare i filtri
    echo '<input type="submit" value="' . esc_html__( 'Filter', 'coding-bunny-bulk-edit' ) . '" class="button button-primary" >';
    echo '</div>'; // Fine del contenitore per i filtri
    echo '</form>';

    // Recupera tutti i prodotti in base ai filtri selezionati
    $args = [
        'post_type' => [ 'product', 'product_variation' ],
        'posts_per_page' => -1,
        'post_status' => [ 'publish', 'draft' ], // Include anche i prodotti in stato di bozza
        'orderby' => 'title',
        'order' => $order_by,
        'no_found_rows' => true,
    ];

    // Aggiunge il filtro per nome prodotto se presente
    if ( ! empty( $search_product_name ) ) {
        $args['s'] = $search_product_name;
    }

    // Aggiunge il filtro per categoria se selezionato
    if ( ! empty( $selected_category ) ) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => (int) $selected_category,
            ],
        ];
    }

    // Aggiunge il filtro per disponibilità
    if ( ! empty( $availability_filter ) ) {
        $args['meta_query'] = [
            [
                'key' => '_stock_status',
                'value' => $availability_filter,
            ],
        ];
    }

    // Aggiunge il filtro per attributo se selezionato
    if ( ! empty( $selected_attribute ) ) {
        $args['tax_query'][] = [
            'taxonomy' => 'pa_' . $selected_attribute,
            'field' => 'slug',
            'terms' => ! empty( $selected_term ) ? $selected_term : wp_list_pluck( get_terms( [ 'taxonomy' => 'pa_' . $selected_attribute, 'hide_empty' => false ] ), 'slug' ),
        ];
    }

    // Aggiunge il filtro per stato se selezionato
    if ( ! empty( $selected_status ) ) {
        $args['post_status'] = $selected_status; // Filtro per stato
    }

    $products = new WP_Query( $args ); // Esegue la query per recuperare i prodotti

    // Ottiene informazioni sulle unità di misura
    $currency_symbol = get_woocommerce_currency_symbol();
    $weight_unit = get_option( 'woocommerce_weight_unit' ); // Unità di peso
    $dimension_unit = get_option( 'woocommerce_dimension_unit' ); // Unità di dimensione

    echo '<form method="post" action="">';
    echo '<div class="product-table-container" style="overflow-x: auto;">'; // Contenitore per la tabella dei prodotti
    echo '<table class="wp-list-table widefat fixed striped" style="min-width: 1920px;">';
    echo '<thead><tr><th style="width: 10px;"></th><th style="width: 10px;"></th><th style="width: 10px;"><input type="checkbox" id="select-all"></th><th style="width: 150px;">' . esc_html__( 'Product name', 'coding-bunny-bulk-edit' ) . '</th><th style="width: 80px;">' . esc_html__( 'Regular Price', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $currency_symbol ) . ')</th><th style="width: 80px;">' . esc_html__( 'Sale Price', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $currency_symbol ) . ')</th><th style="width: 80px;">' . esc_html__( 'Manage Stock', 'coding-bunny-bulk-edit' ) . '</th><th style="width: 80px;">' . esc_html__( 'Stock Quantity', 'coding-bunny-bulk-edit' ) . '</th><th style="width: 80px;">' . esc_html__( 'SKU', 'coding-bunny-bulk-edit' ) . '</th><th style="width: 80px;">' . esc_html__( 'Weight', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $weight_unit ) . ')</th><th style="width: 80px;">' . esc_html__( 'Lenght', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $dimension_unit ) . ')</th><th style="width: 80px;">' . esc_html__( 'Width', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $dimension_unit ) . ')</th><th style="width: 80px;">' . esc_html__( 'Height', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $dimension_unit ) . ')</th><th style="width: 80px;">' . esc_html__( 'Status', 'coding-bunny-bulk-edit' ) . '</th><th style="width: 40px;">ID</th></tr></thead>';
    echo '<tbody>';

    // Controlla se ci sono prodotti da visualizzare
    if ( $products->have_posts() ) {
        while ( $products->have_posts() ) : $products->the_post();
            global $product;

            // Verifica se il prodotto è di tipo semplice o variabile
            if ( $product->is_type( 'simple' ) || $product->is_type( 'variable' ) ) {
                $image_id = $product->get_image_id();
                $product_id = $product->get_id();
                $regular_price = $product->get_regular_price();
                $sale_price = $product->get_sale_price();
                $stock = $product->get_stock_quantity();
                $product_name = $product->get_name();
                $product_weight = $product->get_weight();
                $product_length = $product->get_length();
                $product_width = $product->get_width();
                $product_height = $product->get_height();
                $icon = $product->is_type( 'variable' ) ? '<span class="toggle-icon" style="cursor:pointer;"> + </span>' : ''; // Icona per espandere le variazioni
                $icon_var = $product->is_type( 'variable' ) ? '# ' : ''; // Prefisso per i prodotti variabili
                $product_sku = $product->get_sku();
                $manage_stock = $product->get_manage_stock();
                $selected_option = $manage_stock ? '1' : '0'; // Stato di gestione del magazzino
                $product_link = get_permalink( $product_id );
                $edit_link = get_edit_post_link( $product_id );

                // Righe della tabella per il prodotto principale
echo '<tr class="main-product" data-product-id="' . esc_attr( $product_id ) . '">';
echo '<td><a href="' . esc_url( $edit_link ) . '" target="_blank" class="dashicons dashicons-edit" title="' . esc_attr__( 'Edit Product', 'coding-bunny-bulk-edit' ) . '"></a></td>';
echo '<td><a href="' . esc_url( $product_link ) . '" target="_blank" class="dashicons dashicons-visibility" title="' . esc_attr__( 'View Product', 'coding-bunny-bulk-edit' ) . '"></a></td>';
echo '<td><input type="checkbox" class="product-checkbox" name="selected_products[]" value="' . esc_attr( $product_id ) . '"></td>';
echo '<td style="font-weight: 600;">' . esc_html( $product_name ) . $icon . '</td>';
echo '<td><input type="number" step="0.01" name="regular_price[' . esc_attr( $product_id ) . ']" value="' . esc_attr( $regular_price ) . '" style="width: 100%;"></td>';
echo '<td><input type="number" step="0.01" name="sale_price[' . esc_attr( $product_id ) . ']" value="' . esc_attr( $sale_price ) . '" style="width: 100%;"></td>';
echo '<td><select name="manage_stock[' . esc_attr( $product_id ) . ']" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
echo '<option value="0"' . selected( $selected_option, '0', false ) . '>' . esc_html__( 'NO', 'coding-bunny-bulk-edit' ) . '</option>';
echo '<option value="1"' . selected( $selected_option, '1', false ) . '>' . esc_html__( 'YES', 'coding-bunny-bulk-edit' ) . '</option>';
echo '</select></td>'; 
echo '<td><input type="number" step="1" name="stock[' . esc_attr( $product_id ) . ']" value="' . esc_attr( $stock ) . '" style="width: 100%;"></td>';
echo '<td><input type="text" name="sku[' . esc_attr( $product_id ) . ']" value="' . esc_attr( $product_sku ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
echo '<td><input type="number" step="0.01" name="weight[' . esc_attr( $product_id ) . ']" value="' . esc_attr( $product_weight ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '></td>';
echo '<td><input type="number" step="0.01" name="length[' . esc_attr( $product_id ) . ']" value="' . esc_attr( $product_length ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '></td>';
echo '<td><input type="number" step="0.01" name="width[' . esc_attr( $product_id ) . ']" value="' . esc_attr( $product_width ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '></td>';
echo '<td><input type="number" step="0.01" name="height[' . esc_attr( $product_id ) . ']" value="' . esc_attr( $product_height ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '></td>';   
echo '<td>';
echo '<select name="post_status[' . esc_attr( $product_id ) . ']" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
echo '<option value="publish"' . selected( $product->get_status(), 'publish', false ) . '>' . esc_html__( 'Published', 'coding-bunny-bulk-edit' ) . '</option>';
echo '<option value="draft"' . selected( $product->get_status(), 'draft', false ) . '>' . esc_html__( 'Draft', 'coding-bunny-bulk-edit' ) . '</option>';
echo '</select>';
echo '</td>';
echo '<td>' . esc_html( $product_id ) . '</td>';
echo '</tr>';

                // Se il prodotto è variabile, visualizza le variazioni
                if ( $product->is_type( 'variable' ) ) {
                    $variations = $product->get_available_variations();

                    foreach ( $variations as $variation ) {
                    $variation_id = $variation['variation_id'];
    
    // Ottieni i valori
    $variation_regular_price = get_post_meta( $variation_id, '_regular_price', true );
    $variation_sale_price = get_post_meta( $variation_id, '_sale_price', true );
    $variation_stock = get_post_meta( $variation_id, '_stock', true );
    $variation_name = get_the_title( $variation_id );
    
    // Rimuovi il nome del prodotto dal nome della variazione
    $variation_name = str_replace( $product_name, '', $variation_name );

    // Sostituisci le virgole con i trattini
    $variation_name = str_replace( ',', ' - ', $variation_name );
        
                        // Recupera lo stato di gestione del magazzino per la variazione
                        $variation_product = wc_get_product( $variation_id );
                        $variation_manage_stock = $variation_product->get_manage_stock();
                        $variation_selected_option = $variation_manage_stock ? '1' : '0';
                        $variation_link = get_permalink( $variation_id );
                       
// Righe della tabella per la variazione
echo '<tr class="variation" data-parent-id="' . esc_attr( $product_id ) . '" style="display:none; background-color: #eeeeee;">';
echo '<td></td>';
echo '<td><a href="' . esc_url( $variation_link ) . '" target="_blank" class="dashicons dashicons-visibility" title="' . esc_attr__( 'Vedi prodotto', 'coding-bunny-bulk-edit' ) . '"></a></td>';
echo '<td><input type="checkbox" class="product-checkbox" name="selected_products[]" value="' . esc_attr( $variation_id ) . '"></td>';
echo '<td>' . esc_html( $icon_var . $product_name ) . '<span style="color: #0d47a1;">' . esc_html( $variation_name ) . '</span></td>';
echo '<td><input type="number" step="0.01" name="regular_price[' . esc_attr( $variation_id ) . ']" value="' . esc_attr( $variation_regular_price ) . '" style="width: 100%;"></td>';
echo '<td><input type="number" step="0.01" name="sale_price[' . esc_attr( $variation_id ) . ']" value="' . esc_attr( $variation_sale_price ) . '" style="width: 100%;"></td>';
echo '<td><select name="manage_stock[' . esc_attr( $variation_id ) . ']" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
echo '<option value="0"' . selected( $variation_selected_option, '0', false ) . '>' . esc_html__( 'NO', 'coding-bunny-bulk-edit' ) . '</option>';
echo '<option value="1"' . selected( $variation_selected_option, '1', false ) . '>' . esc_html__( 'YES', 'coding-bunny-bulk-edit' ) . '</option>';
echo '</select></td>'; 
echo '<td><input type="number" step="1" name="stock[' . esc_attr( $variation_id ) . ']" value="' . esc_attr( $variation_stock ) . '" style="width: 100%;"></td>';
echo '<td><input type="text" name="sku[' . esc_attr( $variation_id ) . ']" value="' . esc_attr( $variation_sku ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '></td>';
echo '<td><input type="number" step="0.01" name="weight[' . esc_attr( $variation_id ) . ']" value="' . esc_attr( $variation_product->get_weight() ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '></td>';
echo '<td><input type="number" step="0.01" name="length[' . esc_attr( $variation_id ) . ']" value="' . esc_attr( $variation_product->get_length() ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '></td>';
echo '<td><input type="number" step="0.01" name="width[' . esc_attr( $variation_id ) . ']" value="' . esc_attr( $variation_product->get_width() ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '></td>';
echo '<td><input type="number" step="0.01" name="height[' . esc_attr( $variation_id ) . ']" value="' . esc_attr( $variation_product->get_height() ) . '" style="width: 100%;" ' . ( ! $licence_active ? 'disabled' : '' ) . '></td>';   
echo '<td></td>';
echo '<td>' . esc_html( $variation_id ) . '</td>';                                                   
echo '</tr>';
                    }
                }
            }
        endwhile;
    }

    echo '</tbody></table>';
    echo '</div>';
    echo '<input type="submit" name="update_products" value="' . esc_html__( 'Update products', 'coding-bunny-bulk-edit' ) . '" class="button button-primary" style="margin-top: 20px; margin-bottom: 20px;">';
    echo '<hr>';
    echo '<h3>' . esc_html__( 'Bulk Edit', 'coding-bunny-bulk-edit' ) . '</h3>';
    echo '<p><span class="dashicons dashicons-info"></span> ' . esc_html__( 'Select the products before making the bulk edit. Type ‘100’ to delete the ‘% Discount’.', 'coding-bunny-bulk-edit' ) . '</p>';
    echo '<div class="bulk-edit-container">';
    echo '<label for="bulk_regular_price" class="bulk-label">' . esc_html__( 'Regular Price', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $currency_symbol ) . ')</label>';
    echo '<input type="number" step="0.01" id="bulk_regular_price" name="bulk_regular_price" class="bulk-input">';
    echo '<label for="bulk_sale_price" class="bulk-label">' . esc_html__( 'Sale Price', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $currency_symbol ) . ')</label>';
    echo '<input type="number" step="0.01" id="bulk_sale_price" name="bulk_sale_price" class="bulk-input" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
    echo '<label for="bulk_discount_percentage" class="bulk-label">' . esc_html__( 'Discount (%)', 'coding-bunny-bulk-edit' ) . '</label>';
    echo '<input type="number" step="0.01" id="bulk_discount_percentage" name="bulk_discount_percentage" class="bulk-input" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
    echo '<label for="bulk_stock" class="bulk-label">' . esc_html__( 'Stock Quantity', 'coding-bunny-bulk-edit' ) . '</label>';
    echo '<input type="number" step="1" id="bulk_stock" name="bulk_stock" class="bulk-input">';
    echo '</div>';
    echo '<div class="bulk-edit-container">';
    echo '<label for="bulk_weight" class="bulk-label">' . esc_html__( 'Weight', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $weight_unit ) . ')</label>';
    echo '<input type="number" step="1" id="bulk_weight" name="bulk_weight" class="bulk-input" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
    echo '<label for="bulk_length" class="bulk-label">' . esc_html__( 'Lenght', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $dimension_unit ) . ')</label>';
    echo '<input type="number" step="1" id="bulk_length" name="bulk_length" class="bulk-input" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
    echo '<label for="bulk_width" class="bulk-label">' . esc_html__( 'Width', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $dimension_unit ) . ')</label>';
    echo '<input type="number" step="1" id="bulk_width" name="bulk_width" class="bulk-input" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
    echo '<label for="bulk_height" class="bulk-label">' . esc_html__( 'Height', 'coding-bunny-bulk-edit' ) . ' (' . esc_html( $dimension_unit ) . ')</label>';
    echo '<input type="number" step="1" id="bulk_height" name="bulk_height" class="bulk-input" ' . ( ! $licence_active ? 'disabled' : '' ) . '>';
	echo '</div>';
	echo '<div>';
	echo '<input type="submit" name="bulk_update_products" value="' . esc_html__( 'Update Products in Bulk', 'coding-bunny-bulk-edit' ) . '" class="button button-primary" style="margin-bottom: 20px;">'; 
    echo '<hr>';
    echo '<p>© ' . esc_html( gmdate( 'Y' ) ) . ' - ' . esc_html__( 'Powered by CodingBunny', 'coding-bunny-bulk-edit' ) . '</p>';
    echo '</form>';
    echo '</div>';

    wp_reset_postdata(); // Ripristina i dati del post
}

// Aggiungi la colonna SKU alla tabella dei prodotti
add_filter( 'manage_edit-product_columns', 'coding_bunny_add_product_sku_column', 15 );

function coding_bunny_add_product_sku_column( $columns ) {
    $new_columns = [];
    foreach ( $columns as $key => $value ) {
        $new_columns[$key] = $value;
        // Aggiunge le nuove colonne per SKU e dimensioni
        if ( $key === 'title' ) { 
            $new_columns['product_sku'] = __( 'SKU', 'coding-bunny-bulk-edit' );
            $new_columns['product_weight'] = __( 'Weight', 'coding-bunny-bulk-edit' );
            $new_columns['product_length'] = __( 'Lenght', 'coding-bunny-bulk-edit' );
            $new_columns['product_width'] = __( 'Width', 'coding-bunny-bulk-edit' );
            $new_columns['product_height'] = __( 'Height', 'coding-bunny-bulk-edit' );
            // Aggiunge la colonna per lo stato
            $new_columns['post_status'] = __( 'Status', 'coding-bunny-bulk-edit' );
        }
    }
    return $new_columns; // Restituisce le nuove colonne
}

// Mostra il valore SKU e stato nella nuova colonna
add_action( 'manage_product_posts_custom_column', 'coding_bunny_show_product_sku_column', 10, 2 );

function coding_bunny_show_product_sku_column( $column, $post_id ) {
    $product = wc_get_product( $post_id );

    if ( $column === 'product_sku' ) {
        $sku = $product->get_sku();
        echo '<input type="text" name="sku[' . esc_attr( $post_id ) . ']" value="' . esc_attr( $sku ) . '" class="widefat" style="width: 100%;">';
    } elseif ( $column === 'product_weight' ) {
        $weight = $product->get_weight();
        echo '<input type="number" step="0.01" name="weight[' . esc_attr( $post_id ) . ']" value="' . esc_attr( $weight ) . '" class="widefat" style="width: 100%;">';
    } elseif ( $column === 'product_length' ) {
        $length = $product->get_length();
        echo '<input type="number" step="0.01" name="length[' . esc_attr( $post_id ) . ']" value="' . esc_attr( $length ) . '" class="widefat" style="width: 100%;">';
    } elseif ( $column === 'product_width' ) {
        $width = $product->get_width();
        echo '<input type="number" step="0.01" name="width[' . esc_attr( $post_id ) . ']" value="' . esc_attr( $width ) . '" class="widefat" style="width: 100%;">';
    } elseif ( $column === 'product_height' ) {
        $height = $product->get_height();
        echo '<input type="number" step="0.01" name="height[' . esc_attr( $post_id ) . ']" value="' . esc_attr( $height ) . '" class="widefat" style="width: 100%;">';
    } elseif ( $column === 'post_status' ) {
        $status = $product->get_status();
        echo '<select name="post_status[' . esc_attr( $post_id ) . ']" style="width: 100%;">';
        echo '<option value="publish"' . selected( $status, 'publish', false ) . '>' . esc_html__( 'Published', 'coding-bunny-bulk-edit' ) . '</option>';
        echo '<option value="draft"' . selected( $status, 'draft', false ) . '>' . esc_html__( 'Draft', 'coding-bunny-bulk-edit' ) . '</option>';
        echo '</select>';
    }
}

// Aggiungi la colonna per la gestione del magazzino
add_filter( 'manage_edit-product_columns', 'coding_bunny_add_product_stock_management_column', 15 );

function coding_bunny_add_product_stock_management_column( $columns ) {
    $new_columns = [];
    foreach ( $columns as $key => $value ) {
        $new_columns[$key] = $value;
        // Aggiunge la colonna per la gestione del magazzino
        if ( $key === 'product_sku' ) {
            $new_columns['stock_management'] = __( 'Manage Stock', 'coding-bunny-bulk-edit' );
        }
    }
    return $new_columns; // Restituisce le nuove colonne
}

// Mostra il selettore per la gestione del magazzino nella nuova colonna
add_action( 'manage_product_posts_custom_column', 'coding_bunny_show_product_stock_management_column', 10, 2 );

function coding_bunny_show_product_stock_management_column( $column, $post_id ) {
    if ( $column === 'stock_management' ) {
        $product = wc_get_product( $post_id );
        $manage_stock = $product->get_manage_stock();
        $selected_option = $manage_stock ? '1' : '0';
        echo '<select name="manage_stock[' . esc_attr( $post_id ) . ']" style="width: 100%;">'; // Selettore per la gestione del magazzino
        echo '<option value="0"' . selected( $selected_option, '0', false ) . '>' . esc_html__( 'NO', 'coding-bunny-bulk-edit' ) . '</option>';
        echo '<option value="1"' . selected( $selected_option, '1', false ) . '>' . esc_html__( 'YES', 'coding-bunny-bulk-edit' ) . '</option>';
        echo '</select>';
    }
}

// Aggiorna lo stato di gestione del magazzino per i prodotti singoli
function update_individual_products() {
    // Controlla se tutte le informazioni necessarie sono state inviate
    if (
        isset( $_POST['regular_price'] ) && 
        isset( $_POST['sale_price'] ) && 
        isset( $_POST['stock'] ) && 
        isset( $_POST['sku'] ) && 
        isset( $_POST['manage_stock'] ) && 
        isset( $_POST['weight'] ) && 
        isset( $_POST['length'] ) && 
        isset( $_POST['width'] ) && 
        isset( $_POST['height'] ) && 
        isset( $_POST['post_status'] )
    ) {
        // Sanitizza e prepara i dati per l'aggiornamento
        $regular_prices = array_map( 'wc_format_decimal', wp_unslash( $_POST['regular_price'] ) );
        $sale_prices = array_map( 'wc_format_decimal', wp_unslash( $_POST['sale_price'] ) );
        $stocks = array_map( 'wc_stock_amount', wp_unslash( $_POST['stock'] ) );
        $skus = array_map( 'sanitize_text_field', wp_unslash( $_POST['sku'] ) );
        $manage_stocks = wp_unslash( $_POST['manage_stock'] );
        $weights = array_map( 'wc_format_decimal', wp_unslash( $_POST['weight'] ) );
        $lengths = array_map( 'wc_format_decimal', wp_unslash( $_POST['length'] ) );
        $widths = array_map( 'wc_format_decimal', wp_unslash( $_POST['width'] ) );
        $heights = array_map( 'wc_format_decimal', wp_unslash( $_POST['height'] ) );
        $post_statuses = wp_unslash( $_POST['post_status'] );

        // Aggiorna i prodotti con i nuovi valori
        foreach ( $regular_prices as $product_id => $regular_price ) {
            $product = wc_get_product( $product_id );

            if ( $product ) {
                // Aggiorna il prezzo di listino
                if ( ! empty( $regular_price ) ) {
                    $product->set_regular_price( $regular_price );
                }

                // Aggiorna il prezzo scontato
                if ( isset( $sale_prices[$product_id] ) ) {
                    $sale_price = $sale_prices[$product_id];
                    if ( $sale_price === '' || $sale_price < 0 ) {
                        $product->set_sale_price( '' );
                    } else {
                        $product->set_sale_price( $sale_price );
                    }
                }

                // Aggiorna la quantità di stock
                if ( isset( $stocks[$product_id] ) && $stocks[$product_id] !== '' ) {
                    $stock = $stocks[$product_id];
                    $product->set_stock_quantity( $stock );

                    // Aggiorna lo stato di stock se necessario
                    $stock_status = $stock > 0 ? 'instock' : 'outofstock';
                    $product->set_stock_status( $stock_status );
                }

                // Aggiorna il SKU
                if ( isset( $skus[$product_id] ) ) {
                    $sku = $skus[$product_id];
                    $product->set_sku( $sku );
                }

                // Aggiorna il peso
                if ( isset( $weights[$product_id] ) ) {
                    $weight = $weights[$product_id];
                    $product->set_weight( $weight );
                }

                // Aggiorna le dimensioni
                if ( isset( $lengths[$product_id] ) ) {
                    $length = $lengths[$product_id];
                    $product->set_length( $length );
                }
                if ( isset( $widths[$product_id] ) ) {
                    $width = $widths[$product_id];
                    $product->set_width( $width );
                }
                if ( isset( $heights[$product_id] ) ) {
                    $height = $heights[$product_id];
                    $product->set_height( $height );
                }

                // Aggiorna lo stato di gestione del magazzino
                if ( isset( $manage_stocks[$product_id] ) && $manage_stocks[$product_id] === '1' ) {
                    $product->set_manage_stock( true );
                } else {
                    $product->set_manage_stock( false );
                }

                // Aggiorna lo stato del post
                if ( isset( $post_statuses[$product_id] ) ) {
                    $post_status = $post_statuses[$product_id];
                    $product->set_status( $post_status );
                }

                // Salva le modifiche al prodotto
                $product->save();
                wc_delete_product_transients( $product_id ); // Elimina i transienti del prodotto per forzare il ricaricamento dei dati
            }
        }

        echo '<div class="updated"><p>' . esc_html__( 'Products successfully updated!', 'coding-bunny-bulk-edit' ) . '</p></div>'; // Notifica di successo
    }
}

// Aggiorna i prodotti in blocco
function update_bulk_products() {
    // Controlla se è stato inviata una richiesta per aggiornare i prodotti in blocco
    if ( isset( $_POST['bulk_update_products'] ) ) {
        // Sanitizza e prepara i dati per l'aggiornamento
        $bulk_regular_price = isset( $_POST['bulk_regular_price'] ) ? wc_format_decimal( wp_unslash( $_POST['bulk_regular_price'] ) ) : '';
        $bulk_sale_price = isset( $_POST['bulk_sale_price'] ) ? wc_format_decimal( wp_unslash( $_POST['bulk_sale_price'] ) ) : '';
        $bulk_discount_percentage = isset( $_POST['bulk_discount_percentage'] ) ? floatval( wp_unslash( $_POST['bulk_discount_percentage'] ) ) : 0;
        $bulk_stock = isset( $_POST['bulk_stock'] ) ? wc_stock_amount( wp_unslash( $_POST['bulk_stock'] ) ) : '';
        $bulk_weight = isset( $_POST['bulk_weight'] ) ? wc_format_decimal( wp_unslash( $_POST['bulk_weight'] ) ) : '';
        $bulk_length = isset( $_POST['bulk_length'] ) ? wc_format_decimal( wp_unslash( $_POST['bulk_length'] ) ) : '';
        $bulk_width = isset( $_POST['bulk_width'] ) ? wc_format_decimal( wp_unslash( $_POST['bulk_width'] ) ) : '';
        $bulk_height = isset( $_POST['bulk_height'] ) ? wc_format_decimal( wp_unslash( $_POST['bulk_height'] ) ) : '';

        // Aggiorna i prodotti selezionati
        if ( ! empty( $_POST['selected_products'] ) && is_array( $_POST['selected_products'] ) ) {
            foreach ( $_POST['selected_products'] as $product_id ) {
                $product = wc_get_product( $product_id );

                if ( $product ) {
                    // Aggiorna il prezzo di listino
                    if ( $bulk_regular_price !== '' ) {
                        $product->set_regular_price( $bulk_regular_price );
                    }
                    
                    // Aggiorna il prezzo scontato
                    if ( $bulk_sale_price !== '' ) {
                        $product->set_sale_price( $bulk_sale_price );
                    }
                        
                    // Calcola e aggiorna il prezzo scontato basato sullo sconto percentuale
					if ( isset( $bulk_discount_percentage ) && is_numeric( $bulk_discount_percentage ) ) {
						// Controlla se il valore è maggiore o uguale a 0
						if ( $bulk_discount_percentage > 0 && $bulk_discount_percentage < 100 ) {
							$current_regular_price = $product->get_regular_price(); // Recupera il prezzo di listino attuale
							if ( ! empty( $current_regular_price ) ) {
								$discount = ( $current_regular_price * $bulk_discount_percentage ) / 100;
								$sale_price = $current_regular_price - $discount;
								$product->set_sale_price( $sale_price ); // Sovrascrive il prezzo scontato
							}
						} elseif ( $bulk_discount_percentage == 100 ) {
							// Cancella il prezzo scontato se lo sconto è del 100%
							$product->set_sale_price( '' ); // Cancella il prezzo scontato
						} 
					}
                   
                    // Aggiorna la quantità di stock
                    if ( $bulk_stock > 0 ) {
                        $product->set_stock_quantity( $bulk_stock );
                    } else {
                        $current_stock = $product->get_stock_quantity();
                        $product->set_stock_quantity( $current_stock ); // Mantiene la quantità attuale se il valore è zero
                    }

                    // Aggiorna il peso
                    if ( $bulk_weight !== '' ) {
                        $product->set_weight( $bulk_weight );
                    }

                    // Aggiorna le dimensioni
                    if ( $bulk_length !== '' ) {
                        $product->set_length( $bulk_length );
                    }
                    if ( $bulk_width !== '' ) {
                        $product->set_width( $bulk_width );
                    }
                    if ( $bulk_height !== '' ) {
                        $product->set_height( $bulk_height );
                    }

                    // Aggiorna lo stato di stock
                    $stock_status = $bulk_stock > 0 ? 'instock' : 'outofstock';
                    $product->set_stock_status( $stock_status );

                    // Salva le modifiche al prodotto
                    $product->save();
                    wc_delete_product_transients( $product_id ); // Elimina i transienti del prodotto per forzare il ricaricamento dei dati
                }
            }

            echo '<div class="updated"><p>' . esc_html__( 'Products successfully updated!', 'coding-bunny-bulk-edit' ) . '</p></div>'; // Notifica di successo
        } else {
            echo '<div class="error"><p>' . esc_html__( 'Please select at least one product to update.', 'coding-bunny-bulk-edit' ) . '</p></div>'; // Notifica di errore
        }
    }
}