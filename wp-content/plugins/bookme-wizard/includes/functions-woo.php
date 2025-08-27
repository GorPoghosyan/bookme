<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * OPTIONAL: Staff → Bookable Resource mapping helpers (only if you choose to use Resources per staff).
 * Many sites can skip this if you don't need staff-specific calendars.
 */


/**
* Return an existing global bookable resource id mapped to a staff user, or 0 if none.
*/
function bookme_get_staff_resource_id( $staff_id ){
$rid = absint( get_user_meta( $staff_id, 'bookme_resource_id', true ) );
return $rid;
}


/**
* Create (if missing) a global Bookings Resource for this staff and return its ID.
* NOTE: Requires WooCommerce Bookings active.
*/
function bookme_ensure_staff_resource( $staff ){
if ( ! $staff ) return 0;
$rid = bookme_get_staff_resource_id( $staff->ID );
if ( $rid ) return $rid;


// Create a global resource (post type: bookable_resource)
$title = 'Staff: ' . bookme_staff_label( $staff );
$rid = wp_insert_post( array(
'post_type' => 'bookable_resource',
'post_status' => 'publish',
'post_title' => $title,
) );
if ( $rid ) {
update_user_meta( $staff->ID, 'bookme_resource_id', $rid );
// Optional: tag the resource with staff id for later lookup
update_post_meta( $rid, 'bookme_staff_id', $staff->ID );
}
return absint( $rid );
}


/**
* Assign the given staff resources to a booking product (adds has_resources and links resource ids).
*/
function bookme_assign_staff_resources_to_product( $product_id, array $staff_ids ){
$product = wc_get_product( $product_id );
if ( ! $product || ! $product->is_type( 'booking' ) ) return;


$resource_ids = array();
foreach( $staff_ids as $sid ){
$user = get_user_by( 'id', absint( $sid ) );
if ( ! $user ) continue;
$rid = bookme_ensure_staff_resource( $user );
if ( $rid ) $resource_ids[] = $rid;
}
$resource_ids = array_values( array_unique( array_filter( $resource_ids ) ) );


if ( empty( $resource_ids ) ) return;


// Link resources to product (stores on post meta for Woo Bookings)
update_post_meta( $product_id, '_has_resources', 'yes' );
update_post_meta( $product_id, '_resource_base_costs', array_fill_keys( $resource_ids, '0' ) );
update_post_meta( $product_id, '_resource_block_costs', array_fill_keys( $resource_ids, '0' ) );
update_post_meta( $product_id, '_resource_ids', $resource_ids );
}


?>