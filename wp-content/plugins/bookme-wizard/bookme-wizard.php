<?php
/**
 * Plugin Name: BookMe Wizard (Vendor → Staff → Service → Time)
 * Description: Adds a clean multi-step booking wizard at /company/{vendor_id}/personal/(select-master|select-service|select-time)
 * Author: You
 * Version: 1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;


// === Constants ===
define( 'BOOKME_WIZARD_VER', '1.0.0' );
define( 'BOOKME_WIZARD_PATH', plugin_dir_path( __FILE__ ) );
define( 'BOOKME_WIZARD_URL', plugin_dir_url( __FILE__ ) );


// Meta keys used by WCFM (adjust if your site uses different ones)
// Vendor id stored on staff user meta:
if ( ! defined( 'BOOKME_WCFM_VENDOR_META_KEY' ) ) {
    define( 'BOOKME_WCFM_VENDOR_META_KEY', '_wcfm_vendor' );
}
// Product meta – list of staff IDs assigned to a product (array or serialized)
if ( ! defined( 'BOOKME_ASSIGNED_STAFFS_META_KEY' ) ) {
    define( 'BOOKME_ASSIGNED_STAFFS_META_KEY', '_wcfm_assigned_staffs' );
}


// === Includes ===
require_once BOOKME_WIZARD_PATH . 'includes/functions-staff.php';
require_once BOOKME_WIZARD_PATH . 'includes/functions-services.php';
require_once BOOKME_WIZARD_PATH . 'includes/functions-woo.php';
require_once BOOKME_WIZARD_PATH . 'includes/rest-api.php';


// === Activation: flush rewrites ===
register_activation_hook( __FILE__, function() {
    bookme_wizard_add_rewrite_rules();
    flush_rewrite_rules();
});
register_deactivation_hook( __FILE__, function() {
    flush_rewrite_rules();
});


// === Rewrite rules & query vars ===
add_action( 'init', 'bookme_wizard_add_rewrite_rules' );
function bookme_wizard_add_rewrite_rules() {
    add_rewrite_rule( '^company/([0-9]+)/personal/select-master/?$', 'index.php?bookme_company_id=$matches[1]&bookme_step=master', 'top' );
    add_rewrite_rule( '^company/([0-9]+)/personal/select-service/?$', 'index.php?bookme_company_id=$matches[1]&bookme_step=service', 'top' );
    add_rewrite_rule( '^company/([0-9]+)/personal/select-time/?$', 'index.php?bookme_company_id=$matches[1]&bookme_step=time', 'top' );
}


add_filter( 'query_vars', function( $vars ){
    $vars[] = 'bookme_company_id';
    $vars[] = 'bookme_step';
    return $vars;
});


// === Template loader ===
add_filter( 'template_include', function( $template ){
    $vendor_id = absint( get_query_var( 'bookme_company_id' ) );
    $step = sanitize_key( get_query_var( 'bookme_step' ) );
    if ( $vendor_id && in_array( $step, array( 'master', 'service', 'time' ), true ) ) {
        $file = BOOKME_WIZARD_PATH . 'templates/select-' . $step . '.php';
        if ( file_exists( $file ) ) return $file;
    }
    return $template;
});


// === Assets ===
add_action( 'wp_enqueue_scripts', function(){
    if ( get_query_var( 'bookme_company_id' ) && get_query_var( 'bookme_step' ) ) {
        wp_enqueue_style( 'bookme-wizard', BOOKME_WIZARD_URL . 'assets/wizard.css', array(), BOOKME_WIZARD_VER );
        wp_enqueue_script( 'bookme-wizard', BOOKME_WIZARD_URL . 'assets/wizard.js', array( 'jquery' ), BOOKME_WIZARD_VER, true );
    }
});


// === Helpers ===
function bookme_wizard_base_url( $vendor_id ){
    return home_url( '/company/' . absint( $vendor_id ) . '/personal/' );
}


// === WooCommerce: carry staff/service meta into cart & order ===
// Add hidden meta from wizard into cart items
add_filter( 'woocommerce_add_cart_item_data', function( $cart_item_data, $product_id, $variation_id ){
    if ( isset( $_POST['bookme_staff_id'] ) ) {
        $cart_item_data['bookme_staff_id'] = absint( $_POST['bookme_staff_id'] );
    }
    if ( isset( $_POST['bookme_vendor_id'] ) ) {
        $cart_item_data['bookme_vendor_id'] = absint( $_POST['bookme_vendor_id'] );
    }
    return $cart_item_data;
}, 10, 3 );

// Persist to order line item
add_action( 'woocommerce_checkout_create_order_line_item', function( $item, $cart_item_key, $values, $order ){
    if ( ! empty( $values['bookme_staff_id'] ) ) {
        $item->add_meta_data( 'Staff ID', absint( $values['bookme_staff_id'] ) );
    }
    if ( ! empty( $values['bookme_vendor_id'] ) ) {
        $item->add_meta_data( 'Vendor ID', absint( $values['bookme_vendor_id'] ) );
    }
}, 10, 4 );


// Display on admin order screen
add_action( 'woocommerce_after_order_itemmeta', function( $item_id, $item, $product ){
    $staff_id = wc_get_order_item_meta( $item_id, 'Staff ID', true );
    $vendor_id = wc_get_order_item_meta( $item_id, 'Vendor ID', true );
    if ( $staff_id ) echo '<p><strong>Staff ID:</strong> ' . esc_html( $staff_id ) . '</p>';
    if ( $vendor_id ) echo '<p><strong>Vendor ID:</strong> ' . esc_html( $vendor_id ) . '</p>';
}, 10, 3 );


// Optional: When vendors save a product via WCFM, auto-ensure staff assignments are mirrored to product resources (if you use Bookings Resources per staff)
add_action( 'after_wcfm_products_manage_meta_save', function( $product_id, $form_data ){
// Only for booking products
    $product = function_exists('wc_get_product') ? wc_get_product( $product_id ) : null;
    if ( ! $product || ! $product->is_type( 'booking' ) ) return;


    $assigned = array();
    if ( isset( $form_data[ BOOKME_ASSIGNED_STAFFS_META_KEY ] ) ) {
        $assigned = (array) $form_data[ BOOKME_ASSIGNED_STAFFS_META_KEY ];
    }
// Make sure resources exist & are linked (safe no-op if you don't use resources)
    if ( function_exists( 'bookme_assign_staff_resources_to_product' ) ) {
        bookme_assign_staff_resources_to_product( $product_id, array_map( 'absint', $assigned ) );
    }
}, 490, 2 );

add_action( 'wp_enqueue_scripts', function(){
    // only enqueue on our bookme routes OR you may enqueue globally
    wp_register_script( 'bookme-wizard-spa', BOOKME_WIZARD_URL . 'assets/bookme-wizard-spa.js', array('jquery'), BOOKME_WIZARD_VER, true );

    // Localize REST root & nonce
    wp_localize_script( 'bookme-wizard-spa', 'BOOKME_WIZARD_DATA', array(
        'rest_root' => esc_url_raw( rest_url( 'bookme/v1/' ) ),
        'nonce'     => wp_create_nonce( 'wp_rest' ), // for endpoints that require nonce (we used none, but good to have)
        'home_url'  => esc_url_raw( home_url('/') ),
    ) );
    wp_enqueue_script( 'bookme-wizard-spa' );

    wp_enqueue_style( 'bookme-wizard-spa-css', BOOKME_WIZARD_URL . 'assets/bookme-wizard-spa.css', array(), BOOKME_WIZARD_VER );
});



?>