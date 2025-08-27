<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Get all staff users for a vendor.
 * Adjust role names if your WCFM uses a different role for staff.
 */
function bookme_get_vendor_staff( $vendor_user_id ){
    $roles = array( 'wcfm_staff', 'shop_staff', 'staff' );
    $args = array(
        'role__in' => $roles,
        'number' => 200,
        'fields' => array( 'ID', 'display_name', 'user_email' ),
        'meta_query' => array(
            array(
                'key' => BOOKME_WCFM_VENDOR_META_KEY,
                'value' => absint( $vendor_user_id ),
                'compare' => '=',
            ),
        ),
    );
    $user_query = new WP_User_Query( $args );
    return $user_query->get_results();
}


/**
 * Human-friendly name for a staff user
 */
function bookme_staff_label( $user_obj ){
    if ( ! $user_obj ) return '';
    $name = $user_obj->display_name ?: ( $user_obj->user_email ?: ( 'Staff #' . $user_obj->ID ) );
    return $name;
}


?>