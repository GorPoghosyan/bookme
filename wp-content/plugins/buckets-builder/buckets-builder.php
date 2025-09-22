<?php
/**
 * Plugin Name: Buckets Builder
 * Description: Create a 2-step bucket/bundle page and add result to cart as a new product.
 * Version: 1.1
 * Author: Gor Zakaryan
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BUCKETS_PATH', plugin_dir_path( __FILE__ ) );
define( 'BUCKETS_URL',  plugin_dir_url( __FILE__ ) );

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p>Buckets Builder requires WooCommerce active.</p></div>';
    } );
    return;
}

require_once BUCKETS_PATH . 'includes/class-buckets-admin.php';
require_once BUCKETS_PATH . 'includes/class-buckets-frontend.php';
require_once BUCKETS_PATH . 'includes/class-buckets-cart.php';

new Buckets_Admin();
new Buckets_Frontend();
new Buckets_Cart();
