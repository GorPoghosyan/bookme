<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Buckets_Cart {
    public function __construct() {
        add_action( 'wp_ajax_buckets_add_to_cart', [ $this, 'add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_buckets_add_to_cart', [ $this, 'add_to_cart' ] );
    }

    public function add_to_cart() {
        // Check nonce
        check_ajax_referer( 'buckets_nonce', 'nonce' );

        $items_raw = isset( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : '';
        $items = json_decode( $items_raw, true );

        if ( ! is_array( $items ) || empty( $items ) ) {
            wp_send_json_error( [ 'message' => 'No items provided' ], 400 );
        }

        $names = [];
        $total = 0.0;
        foreach ( $items as $it ) {
            $id = isset( $it['id'] ) ? absint( $it['id'] ) : 0;
            $qty = isset( $it['qty'] ) ? intval( $it['qty'] ) : 0;
            if ( $id <= 0 || $qty <= 0 ) continue;

            $product = wc_get_product( $id );
            if ( ! $product ) continue;

            $price = floatval( $product->get_price() );
            $subtotal = $price * $qty;
            $total += $subtotal;

            $names[] = sanitize_text_field( $product->get_name() ) . ' x ' . $qty;
        }

        if ( empty( $names ) ) {
            wp_send_json_error( [ 'message' => 'No valid items chosen' ], 400 );
        }

        // Create a new simple product for this bucket (unique SKU)
        $time = current_time( 'mysql' );
        $title = 'Custom Bucket - ' . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
        $sku = 'bucket-' . uniqid();

        $post_id = wp_insert_post( [
            'post_title'   => wp_strip_all_tags( $title ),
            'post_content' => implode( ', ', $names ),
            'post_status'  => 'publish',
            'post_type'    => 'product',
        ] );

        if ( is_wp_error( $post_id ) || $post_id <= 0 ) {
            wp_send_json_error( [ 'message' => 'Could not create product' ], 500 );
        }

        // mark as simple product
        wp_set_object_terms( $post_id, 'simple', 'product_type' );

        // Set product visibility as hidden
        wp_set_object_terms( $post_id, 'exclude-from-catalog', 'product_visibility', true );
        wp_set_object_terms( $post_id, 'exclude-from-search', 'product_visibility', true );
        update_post_meta( $post_id, '_visibility', 'hidden' );

        // product meta: prices & flags
        update_post_meta( $post_id, '_regular_price', wc_format_decimal( $total, wc_get_price_decimals() ) );
        update_post_meta( $post_id, '_price', wc_format_decimal( $total, wc_get_price_decimals() ) );
        update_post_meta( $post_id, '_virtual', 'yes' );
        update_post_meta( $post_id, '_downloadable', 'no' );
        update_post_meta( $post_id, '_stock_status', 'instock' );
        update_post_meta( $post_id, '_sku', $sku );
        update_post_meta( $post_id, '_manage_stock', 'no' );

        $image_path = WP_PLUGIN_DIR . '/buckets-builder/assets/img/bouquet.jpg';
        if ( file_exists( $image_path ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $upload_dir = wp_upload_dir();
            $filename   = basename( $image_path );
            $new_path   = $upload_dir['path'] . '/' . $filename;

            // Prevent duplicate copy
            if ( ! file_exists( $new_path ) ) {
                copy( $image_path, $new_path );
            }

            // Register image in Media Library
            $attachment = array(
                'guid'           => $upload_dir['url'] . '/' . $filename,
                'post_mime_type' => 'image/jpeg',
                'post_title'     => 'Custom Bucket Image',
                'post_content'   => '',
                'post_status'    => 'inherit',
            );

            $attach_id = wp_insert_attachment( $attachment, $new_path, $post_id );
            if ( $attach_id ) {
                $attach_data = wp_generate_attachment_metadata( $attach_id, $new_path );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                set_post_thumbnail( $post_id, $attach_id );
            }
        }

        // Ensure WooCommerce session & cart ready
        if ( ! WC()->session ) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }
        if ( ! WC()->cart ) {
            WC()->cart = new WC_Cart();
        }

        // Add to cart (1 qty)
        $added = WC()->cart->add_to_cart( $post_id, 1 );
        if ( ! $added ) {
            wp_send_json_error( [ 'message' => 'Could not add to cart' ], 500 );
        }

        wp_send_json_success( [ 'cart_url' => wc_get_cart_url() ] );
    }
}
