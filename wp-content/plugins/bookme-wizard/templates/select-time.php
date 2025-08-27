<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$vendor_id = absint( get_query_var( 'bookme_company_id' ) );
$staff_id = isset($_GET['staff_id']) ? absint($_GET['staff_id']) : 0;
$service_id = isset($_GET['service_id']) ? absint($_GET['service_id']) : 0;
if ( ! $vendor_id || ! $staff_id || ! $service_id ) wp_die( 'Vendor, Staff and Service are required.' );


// Validate ownerships
$valid_vendor = get_user_meta( $staff_id, BOOKME_WCFM_VENDOR_META_KEY, true );
if ( absint( $valid_vendor ) !== $vendor_id ) wp_die( 'Staff does not belong to vendor.' );
$product = wc_get_product( $service_id );
if ( ! $product || ! $product->is_type('booking') ) wp_die( 'Invalid booking product.' );


get_header();
include __DIR__ . '/parts-header.php';

?>
    <main class="bookme-wrapper">
        <h1 class="bookme-title">Choose a time</h1>
        <a class="bookme-back" href="<?php echo esc_url( add_query_arg( 'staff_id', $staff_id, bookme_wizard_base_url($vendor_id) . 'select-service/' ) ); ?>">← Back to Services</a>


        <div class="bookme-time">
            <?php
            // Try to render only the booking form (not the whole product page)
            global $product, $post;
            $__bookme_prev_product = isset( $product ) ? $product : null;
            $__bookme_prev_post = isset( $post ) ? $post : null;
            $product = wc_get_product( $service_id );
            $post = get_post( $service_id );
            setup_postdata( $post );
//var_dump($product, $post);die();

            // Output a hidden field with staff/vendor so it lands in the cart
            do_action( 'bookme_before_booking_form', $service_id, $staff_id, $vendor_id );
//            do_action( 'woocommerce_before_booking_form', $service_id, $staff_id, $vendor_id );
            ?>
            <form class="bookme-hidden-meta" method="post" style="display:none">
                <input type="hidden" name="bookme_staff_id" value="<?php echo esc_attr( $staff_id ); ?>" />
                <input type="hidden" name="bookme_vendor_id" value="<?php echo esc_attr( $vendor_id ); ?>" />
            </form>
            <?php
            // Load WooCommerce Bookings add-to-cart form
            if ( function_exists( 'wc_get_template' ) ) {
// This template comes from WooCommerce Bookings
                wc_get_template( 'single-product/add-to-cart/booking.php' );
            } else {
                echo do_shortcode( '[product_page id="' . $service_id . '"]' );
            }


            // Restore globals
            if ( $__bookme_prev_product ) $product = $__bookme_prev_product; else unset( $product );
            if ( $__bookme_prev_post ) $post = $__bookme_prev_post; else unset( $post );
            ?>
        </div>
    </main>
<?php  get_footer(); ?>