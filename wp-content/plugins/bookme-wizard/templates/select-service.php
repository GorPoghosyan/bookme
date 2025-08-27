<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$vendor_id = absint( get_query_var( 'bookme_company_id' ) );
$staff_id = isset($_GET['staff_id']) ? absint($_GET['staff_id']) : 0;
if ( ! $vendor_id || ! $staff_id ) wp_die( 'Vendor and Staff are required.' );


// Validate: ensure staff belongs to vendor
$valid = get_user_meta( $staff_id, BOOKME_WCFM_VENDOR_META_KEY, true );
if ( absint( $valid ) !== $vendor_id ) wp_die( 'This staff does not belong to this vendor.' );


$services = bookme_get_services_for_staff( $vendor_id, $staff_id );
$next = bookme_wizard_base_url( $vendor_id ) . 'select-time/';
get_header();
include __DIR__ . '/parts-header.php';
?>
    <main class="bookme-wrapper">
        <h1 class="bookme-title">Choose a service</h1>
        <a class="bookme-back" href="<?php echo esc_url( bookme_wizard_base_url($vendor_id) . 'select-master/' ); ?>">← Back to Staff</a>


        <?php if ( empty( $services ) ): ?>
            <p>No services found for this staff.</p>
        <?php else: ?>
            <div class="bookme-grid">
                <?php foreach( $services as $product ): ?>
                    <?php if ( ! $product ) continue; ?>
                    <?php $pid = $product->get_id(); ?>
                    <a class="bookme-card" href="<?php echo esc_url( add_query_arg( array( 'staff_id' => $staff_id, 'service_id' => $pid ), $next ) ); ?>">
                        <div class="bookme-card-title"><?php echo esc_html( $product->get_name() ); ?></div>
                        <div class="bookme-card-meta">
                            Duration: <?php echo esc_html( get_post_meta( $pid, '_wc_booking_duration', true ) ); ?>
                            <?php echo esc_html( get_woocommerce_currency_symbol() . $product->get_price() ); ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
<?php get_footer(); ?>