<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Buckets_Frontend {
    public function __construct() {
        add_shortcode( 'buckets_page', [ $this, 'render' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'assets' ] );
    }

    public function assets() {
        wp_enqueue_style( 'buckets-css', BUCKETS_URL . 'assets/css/buckets.css', [], '1.0' );
        wp_register_script( 'buckets-js', BUCKETS_URL . 'assets/js/buckets.js', [ 'jquery' ], '1.1', true );

        $currency_symbol = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency_symbol() : '$';
        $currency_pos = get_option( 'woocommerce_currency_pos', 'left' );

        wp_localize_script( 'buckets-js', 'buckets_builder', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'buckets_nonce' ),
            'currency_symbol' => $currency_symbol,
            'currency_pos' => $currency_pos,
            'cart_url' => wc_get_cart_url()
        ] );

        wp_enqueue_script( 'buckets-js' );
    }

    public function render() {
        ob_start();
        include BUCKETS_PATH . 'templates/buckets-page.php';
        return ob_get_clean();
    }
}
