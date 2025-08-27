<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$vendor_id = absint( get_query_var( 'bookme_company_id' ) );
if ( ! $vendor_id ) wp_die( 'Vendor is required.' );

$staff = bookme_get_vendor_staff( $vendor_id );
$next = bookme_wizard_base_url( $vendor_id ) . 'select-service/';
get_header();
include __DIR__ . '/parts-header.php';
?>
    <main class="bookme-wrapper">
        <h1 class="bookme-title">Choose a staff member</h1>
        <?php if ( empty( $staff ) ): ?>
            <p>No staff found for this vendor yet.</p>
        <?php else: ?>
            <div class="bookme-grid">
                <?php foreach( $staff as $s ): ?>
                    <a class="bookme-card" href="<?php echo esc_url( add_query_arg( 'staff_id', $s->ID, $next ) ); ?>">
                        <div class="bookme-card-title"><?php echo esc_html( bookme_staff_label( $s ) ); ?></div>
                        <div class="bookme-card-meta">ID #<?php echo esc_html( $s->ID ); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
<?php get_footer(); ?>