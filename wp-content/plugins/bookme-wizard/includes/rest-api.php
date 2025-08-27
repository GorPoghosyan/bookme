<?php
// File: wp-content/plugins/bookme-wizard/includes/rest-api.php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', function () {

    // 1) GET staff for vendor: /wp-json/bookme/v1/staff/{vendor_id}
    register_rest_route( 'bookme/v1', '/staff/(?P<vendor>\d+)', array(
        'methods'  => 'GET',
        'callback' => function( $request ) {
            $vendor = absint( $request['vendor'] );
            if ( ! $vendor ) return new WP_Error( 'invalid_vendor', 'Vendor ID required', array( 'status' => 400 ) );

            // Query staff users that have meta BOOKME_WCFM_VENDOR_META_KEY == $vendor
            $args = array(
                'meta_key'   => BOOKME_WCFM_VENDOR_META_KEY,
                'meta_value' => $vendor,
                'number'     => 200,
                'fields'     => array( 'ID', 'display_name', 'user_email' )
            );
            $uquery = new WP_User_Query( $args );
            $users = $uquery->get_results();

            $out = array();
            foreach ( $users as $u ) {
                $out[] = array(
                    'id'    => $u->ID,
                    'name'  => bookme_staff_label( $u ),
                    'avatar'=> get_avatar_url( $u->ID ),
                    'bio'   => get_user_meta( $u->ID, 'description', true ),
                );
            }
            return rest_ensure_response( $out );
        },
        'permission_callback' => '__return_true',
    ));

    // 2) GET services for vendor+staff: /wp-json/bookme/v1/services?vendor=ID&staff=ID
    register_rest_route( 'bookme/v1', '/services', array(
        'methods'  => 'GET',
        'callback' => function( $request ) {
            $vendor = absint( $request->get_param('vendor') );
            $staff  = absint( $request->get_param('staff') );
            if ( ! $vendor || ! $staff ) return new WP_Error( 'missing_params', 'vendor and staff required', array( 'status' => 400 ) );

            $products = bookme_get_services_for_staff( $vendor, $staff ); // returns WC_Product objects
            $out = array();
            foreach ( $products as $p ) {
                if ( ! $p ) continue;
                $out[] = array(
                    'id' => $p->get_id(),
                    'name' => $p->get_name(),
                    'price' => $p->get_price(),
                    'duration' => get_post_meta( $p->get_id(), '_wc_booking_duration', true ),
                    'is_booking' => $p->is_type( 'booking' ),
                    'permalink' => get_permalink( $p->get_id() )
                );
            }
            return rest_ensure_response( $out );
        },
        'permission_callback' => '__return_true',
    ));

    // 3) GET booking form HTML (server renders woo booking form for product)
    // /wp-json/bookme/v1/booking-form?service_id=123&staff_id=45&vendor_id=67
    register_rest_route( 'bookme/v1', '/booking-form', array(
        'methods'  => 'GET',
        'callback' => function( $request ) {
            $service_id = absint( $request->get_param('service_id') );
            $staff_id   = absint( $request->get_param('staff_id') );
            $vendor_id  = absint( $request->get_param('vendor_id') );

            if ( ! $service_id || ! $staff_id || ! $vendor_id ) {
                return new WP_Error( 'missing', 'service_id, staff_id and vendor_id are required', array( 'status' => 400 ) );
            }

            $product = wc_get_product( $service_id );
            if ( ! $product || ! $product->is_type( 'booking' ) ) {
                return new WP_Error( 'invalid_product', 'Invalid booking product', array( 'status' => 400 ) );
            }
            if ( $product->is_type( 'booking' ) && $staff_id ) {
                // Preselect resource (if using resources)
                $resources = $product->get_resources(); // returns array of resource IDs
                foreach( $resources as $r_id ) {
                    $res_staff_user = get_post_meta($r_id, 'bookme_staff_id', true); // custom mapping
                    if( $res_staff_user == $staff_id ) {
                        // Woo template picks up $_POST or default resource
                        $_REQUEST['wc_bookings_field_resource_id'] = $r_id;
                        break;
                    }
                }
            }

            // Render the booking add-to-cart form template into a string
            ob_start();
            // Provide hidden inputs via a small template wrapper so the booking form sees them as part of DOM
            echo '<div class="bookme-booking-form-wrapper" data-staff="' . esc_attr( $staff_id ) . '" data-vendor="' . esc_attr( $vendor_id ) . '">';
            // render the default booking add-to-cart template (this will produce the booking date/time fields)
            wc_get_template( 'single-product/add-to-cart/booking.php', array( 'product' => $product ) );
            // add our hidden meta fields (form will include them when posting)
            echo '<input type="hidden" name="bookme_staff_id" value="' . esc_attr( $staff_id ) . '" />';
            echo '<input type="hidden" name="bookme_vendor_id" value="' . esc_attr( $vendor_id ) . '" />';
            echo '</div>';
            $html = ob_get_clean();

            return rest_ensure_response( array( 'html' => $html ) );
        },
        'permission_callback' => '__return_true',
    ));
});
