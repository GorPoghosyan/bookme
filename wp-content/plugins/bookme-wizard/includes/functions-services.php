<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get booking services (products) for a VENDOR that are assigned to a STAFF member.
 * Uses author=vendor_user_id and meta LIKE search on BOOKME_ASSIGNED_STAFFS_META_KEY.
 */
function bookme_get_services_for_staff( $vendor_user_id, $staff_id ){
    global $wpdb;

    $assigned_products = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_value 
         FROM $wpdb->postmeta 
         WHERE meta_key = '_wcfm_assigned_staffs' 
         AND meta_value LIKE %s",
        '%' . $wpdb->esc_like($staff_id) . '%'
    ));

    // If no results, return early
    if (empty($assigned_products)) {
        return '<p>No products assigned to you.</p>';
    }

    // Extract post IDs
    $product_ids = array();
    foreach ($assigned_products as $product) {
        // Check if the user ID is actually in the array
        $staff_ids = maybe_unserialize($product->meta_value);

        if (is_array($staff_ids) && in_array($staff_id, $staff_ids, true)) {
            $product_ids[] = $product->post_id;
        }
    }

    // If no valid products found
    if (empty($product_ids)) {
        return '<p>No products assigned to you.</p>';
    }

    // Query products
    $args = array(
        'post_type' => 'product',
        'post__in' => $product_ids,
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );

    $products = new WP_Query($args);
    if ( $products->have_posts() ) {
        foreach( $products->posts as $p ) {
            $items[] = wc_get_product( $p->ID );
        }
    }
    wp_reset_postdata();

    return $items ?? [];
}
?>