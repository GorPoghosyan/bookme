<?php

namespace WeDevs\DokanPro\Modules\DeliveryTime;

use WeDevs\DokanPro\Modules\DeliveryTime\Helper as DHelper;
use WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class RestController extends WP_REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @since 3.15.0
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route base.
     *
     * @since 3.15.0
     *
     * @var string
     */
    protected $rest_base = 'delivery-time';

    /**
     * Register routes
     *
     * @since 3.15.0
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/time-slot', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_vendor_delivery_time_slot' ],
                'permission_callback' => '__return_true'
            ],
            'args' => [
                'vendorId' => [
                    'description'       => __( 'Vendor id to get the time sot', 'dokan' ),
                    'type'              => 'integer',
                    'required'          => true,
                    'sanitize_callback' => 'absint'
                ],
                'date' => [
                    'description'       => __( 'Date to get the time sot', 'dokan' ),
                    'type'              => 'string',
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/location-slot', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_vendor_delivery_location_slot' ],
                'permission_callback' => '__return_true'
            ],
            'args' => [
                'vendorId' => [
                    'description'       => __( 'Vendor id to get the time sot', 'dokan' ),
                    'type'              => 'integer',
                    'required'          => true,
                    'sanitize_callback' => 'absint'
                ],
            ],
        ] );
    }

    /**
     * Gets vendor delivery time slot from ajax request.
     *
     * @since 3.15.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_vendor_delivery_time_slot( $request ) {
        $vendor_id = $request->get_param( 'vendorId' );
        $date      = $request->get_param( 'date' );

        $delivery_time_slots = DHelper::get_current_date_active_delivery_time_slots( $vendor_id, $date );

        return rest_ensure_response( $delivery_time_slots );
    }

    /**
     * Gets vendor delivery time slot from ajax request.
     *
     * @since 3.15.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_vendor_delivery_location_slot( $request ) {
        $vendor_id = $request->get_param( 'vendorId' );

        $vendor_store_locations = Helper::get_vendor_store_pickup_locations( $vendor_id, false, true );

        return $this->prepare_item_for_response( $vendor_store_locations, $request );
    }

    /**
     * Prepare delivery times.
     *
     * @since 3.15.0
     *
     * @param $vendor_store_locations
     * @param $request
     *
     * @return WP_Error|\WP_HTTP_Response|WP_REST_Response
     */
    public function prepare_item_for_response($vendor_store_locations, $request){
        $formatted_locations = [];

        foreach ( $vendor_store_locations as $location_name => $location ) {
            $formatted_locations[ $location_name ] = Helper::get_formatted_vendor_store_pickup_location( $location, ' ', $location['location_name'] );
        }

        return rest_ensure_response( $formatted_locations );
    }
}
