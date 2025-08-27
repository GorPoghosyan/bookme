<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$vendor_id = absint( get_query_var( 'bookme_company_id' ) );
$base = bookme_wizard_base_url( $vendor_id );
$step = sanitize_key( get_query_var( 'bookme_step' ) );
?>
<div class="bookme-wizard-header">
    <div class="bookme-wizard-brand">
        <a href="<?php echo esc_url( home_url('/') ); ?>" class="bookme-logo">BookMe</a>
        <span class="bookme-vendor">Vendor #<?php echo esc_html( $vendor_id ); ?></span>
    </div>
    <ol class="bookme-steps">
        <li class="<?php echo $step==='master' ? 'active' : ''; ?>">Select Staff</li>
        <li class="<?php echo $step==='service' ? 'active' : ''; ?>">Select Service</li>
        <li class="<?php echo $step==='time' ? 'active' : ''; ?>">Select Time</li>
        <li>Checkout</li>
    </ol>
</div>