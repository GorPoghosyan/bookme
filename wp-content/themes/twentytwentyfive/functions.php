<?php
/**
 * Twenty Twenty-Five functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Twenty Twenty-Five 1.0
 */

// Adds theme support for post formats.
if ( ! function_exists( 'twentytwentyfive_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_post_format_setup' );

// Enqueues editor-style.css in the editors.
if ( ! function_exists( 'twentytwentyfive_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_editor_style() {
		add_editor_style( 'assets/css/editor-style.css' );
	}
endif;
add_action( 'after_setup_theme', 'twentytwentyfive_editor_style' );

// Enqueues style.css on the front.
if ( ! function_exists( 'twentytwentyfive_enqueue_styles' ) ) :
	/**
	 * Enqueues style.css on the front.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_enqueue_styles() {
		wp_enqueue_style(
			'twentytwentyfive-style',
			get_parent_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'twentytwentyfive_enqueue_styles' );

// Registers custom block styles.
if ( ! function_exists( 'twentytwentyfive_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfive' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_block_styles' );

// Registers pattern categories.
if ( ! function_exists( 'twentytwentyfive_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_pattern_categories() {

		register_block_pattern_category(
			'twentytwentyfive_page',
			array(
				'label'       => __( 'Pages', 'twentytwentyfive' ),
				'description' => __( 'A collection of full page layouts.', 'twentytwentyfive' ),
			)
		);

		register_block_pattern_category(
			'twentytwentyfive_post-format',
			array(
				'label'       => __( 'Post formats', 'twentytwentyfive' ),
				'description' => __( 'A collection of post format patterns.', 'twentytwentyfive' ),
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_pattern_categories' );

// Registers block binding sources.
if ( ! function_exists( 'twentytwentyfive_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return void
	 */
	function twentytwentyfive_register_block_bindings() {
		register_block_bindings_source(
			'twentytwentyfive/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'twentytwentyfive' ),
				'get_value_callback' => 'twentytwentyfive_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'twentytwentyfive_register_block_bindings' );

// Registers block binding callback function for the post format name.
if ( ! function_exists( 'twentytwentyfive_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since Twenty Twenty-Five 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function twentytwentyfive_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;

// Add custom CSS via functions.php
function custom_inline_styles() {
    ?>
    <style>
        /* Your custom styles here */
	.is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)) {
	    max-width: 100% !important;
	    margin-left: auto !important;
	    margin-right: auto !important;
	}
    </style>
    <?php
}
add_action('wp_head', 'custom_inline_styles');

add_filter( 'wcfm_staffs_query_args', function( $args, $vendor_id ) {
    // $vendor_id is automatically passed by WCFM
    if ( $vendor_id ) {
        $args['meta_query'][] = [
            'key'     => '_wcfm_staff_vendor', // meta key used by WCFM
            'value'   => $vendor_id,
            'compare' => '='
        ];
    }
    return $args;
}, 10, 2 );

add_action( 'after_wcfm_products_manage_tabs_content', 'my_custom_staff_notes_field', 700, 4 );
function my_custom_staff_notes_field( $product_id, $product_type, $wcfm_is_translated_product = false, $wcfm_wpml_edit_disable_element = '' ) {
    global $WCFM;

    $vendor_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
    $staff_user_role = apply_filters('wcfm_staff_user_role', 'shop_staff');

    // If admin, show all staff
    if ( current_user_can('manage_options') ) {
        $vendorId = get_post_meta( $product_id, '_wcfm_vendor', true );
        if ( empty( $vendorId ) ) {
            $vendorId = (int) get_post_field( 'post_author', $product_id );
        }
        $args = [
            'role__in'    => [$staff_user_role],
            'meta_key'   => '_wcfm_vendor',
            'meta_value' => $vendorId ?? '',
            'orderby'    => 'ID',
            'order'      => 'ASC',
            'fields'     => ['ID','display_name'],
        ];
    } else {
        $args = [
            'role__in'    => [$staff_user_role],
            'meta_key'   => '_wcfm_vendor',
            'meta_value' => $vendor_id,
            'orderby'    => 'ID',
            'order'      => 'ASC',
            'fields'     => ['ID','display_name'],
        ];
    }
    $staff_users = get_users( $args );

    $vendor_staff = [];
    foreach ( $staff_users as $staff ) {
        $vendor_staff[$staff->ID] = $staff->display_name;
    }

    $saved_staffs = (array) get_post_meta( $product_id, '_wcfm_assigned_staffs', true );
    ?>
    <div class="page_collapsible products_manage_vendor_association simple variable grouped external booking <?php echo esc_attr($wcfm_wpml_edit_disable_element); ?>" id="wcfm_products_manage_form_vendor_association_head">
        <label class="wcfmfa fa-users"></label>
        <?php esc_html_e( 'Assign Staff Members', 'wc-frontend-manager' ); ?>
    </div>
    <div class="wcfm-container simple variable external grouped booking">
        <div id="wcfm_products_manage_form_vendor_association_expander" class="wcfm-content">
            <?php
            $WCFM->wcfm_fields->wcfm_generate_form_field( array(
                "wcfm_assigned_staffs" => array(
                    'label'       => __( 'Select Staff', 'wc-frontend-manager' ),
                    'type'        => 'select',
                    'options'     => $vendor_staff,
                    'attributes'  => array(
                        'multiple' => 'multiple',
                        'style'    => 'width: 60%;'
                    ),
                    'class'       => 'wcfm-select wcfm_multi_select',
                    'label_class' => 'wcfm_title',
                    'value'       => $saved_staffs,
                ),
            ) );
            ?>
        </div>
    </div>
    <div class="wcfm_clearfix"></div>
    <?php
}

add_action( 'after_wcfm_products_manage_meta_save', 'my_save_assigned_staffs', 700, 2 );
function my_save_assigned_staffs( $new_product_id, $wcfm_products_manage_form_data ) {
    if ( isset( $wcfm_products_manage_form_data['wcfm_assigned_staffs'] ) ) {
        $assigned = (array) $wcfm_products_manage_form_data['wcfm_assigned_staffs'];
        update_post_meta(
            $new_product_id,
            '_wcfm_assigned_staffs',
            array_map( 'intval', $assigned )
        );
    } else {
        delete_post_meta( $new_product_id, '_wcfm_assigned_staffs' );
    }
}

// 28.08

// Register custom rewrite rules
function bookme_custom_rewrites() {
    add_rewrite_rule(
        '^company/([0-9]+)/personal/select-master/?$',
        'index.php?bookme_step=select-master&vendor_id=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^company/([0-9]+)/personal/select-service/?$',
        'index.php?bookme_step=select-service&vendor_id=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^company/([0-9]+)/personal/select-time/?$',
        'index.php?bookme_step=select-time&vendor_id=$matches[1]',
        'top'
    );
}
add_action('init', 'bookme_custom_rewrites');

// Add custom query vars
function bookme_custom_query_vars($vars) {
    $vars[] = 'bookme_step';
    $vars[] = 'vendor_id';
    return $vars;
}
add_filter('query_vars', 'bookme_custom_query_vars');




function bookme_wizard_shortcode() {
    ob_start();
    ?>
    <div id="bookme-wizard">
        <div id="bookme-step-content">
            Loading...
        </div>
    </div>
    <script>
    jQuery(document).ready(function($){

        // Function to load a step via AJAX
        function loadStep(step, vendor_id, extra = {}){
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php", "relative"); ?>',
                type: 'POST',
                data: {
                    action: 'bookme_load_step',
                    step: step,
                    vendor_id: vendor_id,
                    extra: extra
                },
                success: function(response){
                    $('#bookme-step-content').html(response);
                    window.history.replaceState({}, '', '/company/' + vendor_id + '/personal/' + step);
                }
            });
        }

        // On page load, get current step and vendor_id
        var step = '<?php echo get_query_var("bookme_step"); ?>';
        var vendor_id = '<?php echo get_query_var("vendor_id"); ?>';
        if(step && vendor_id){
            loadStep(step, vendor_id);
        }

        // Handle "Next Step" button clicks
        $(document).on('click', '.bookme-next-step', function(){
            var nextStep = $(this).data('next-step');
            var vendor_id = $(this).data('vendor-id');
            var extra = {}; 
            if($(this).data('staff-id')) extra.staff_id = $(this).data('staff-id');
            if($(this).data('service-id')) extra.service_id = $(this).data('service-id');
            loadStep(nextStep, vendor_id, extra);
        });

    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('bookme_wizard', 'bookme_wizard_shortcode');




add_filter('template_include', function($template) {
    $bookme_step = get_query_var('bookme_step');
    if($bookme_step) {
        // Load a custom template that outputs the shortcode
        $custom_template = get_stylesheet_directory() . '/bookme-wizard-template.php';
        if(file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
});


function bookme_load_step_ajax() {
    // Enable inline error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    try {

        $step = $_POST['step'] ?? $_POST['bookme_step'] ?? '';
        $extra = $_POST['extra'] ?? [];

        $vendor_id  = intval($_POST['vendor_id'] ?? $extra['vendor_id'] ?? 0);
        $staff_id   = intval($extra['staff_id'] ?? 0);
        $service_id = intval($extra['service_id'] ?? 0);

        if(!$vendor_id) wp_die('Vendor not found');

        switch($step){

            // Step 1: Select Master / Staff
            case 'select-master':
                $staff_users = new WP_User_Query([
                    'role'       => 'shop_staff', 
                    'meta_query' => [
                        [
                            'key'     => '_wcfm_vendor',
                            'value'   => $vendor_id,
                            'compare' => '='
                        ]
                    ]
                ]);

                $staff_list = $staff_users->get_results();

                if($staff_list){
                    echo '<h3>Select Staff</h3><ul>';
                    foreach($staff_list as $staff){
                        echo '<li>
                            <button class="bookme-next-step" 
                                data-next-step="select-service" 
                                data-vendor-id="'.$vendor_id.'" 
                                data-staff-id="'.$staff->ID.'">'
                                .$staff->display_name.
                            '</button>
                        </li>';
                    }
                    echo '</ul>';
                } else {
                    echo 'No staff available.';
                }
            break;

            // Step 2: Select Service
            case 'select-service':
                if(!$staff_id){
                    echo 'No staff selected.';
                    break;
                }

                $args = [
                    'post_type'      => 'product',
                    'posts_per_page' => -1,
                    'meta_query'     => [
                        [
                            'key'     => '_wcfm_assigned_staffs',
                            'value'   => 'i:' . $staff_id . ';',
                            'compare' => 'LIKE'
                        ]
                    ]
                ];

                $services = get_posts($args);

                if($services){
                    echo '<h3>Select Service</h3><ul>';
                    foreach($services as $service){
                        echo '<li>
                            <button class="bookme-next-step" 
                                data-next-step="select-time"
                                data-vendor-id="'.$vendor_id.'"
                                data-staff-id="'.$staff_id.'" 
                                data-service-id="'.$service->ID.'">'
                                .$service->post_title.
                            '</button>
                        </li>';
                    }
                    echo '</ul>';
                } else {
                    echo 'No services available for this staff.';
                }
            break;

            // Step 3: Select Time
            case 'select-time':
                if(!$staff_id || !$service_id){
                    echo 'No staff or service selected.';
                    break;
                }

                $product = wc_get_product($service_id);
                if(!$product || !is_a($product, 'WC_Product_Booking')){
                    echo 'Invalid service or product not found.';
                    break;
                }

                $today = new DateTime('today');
                $slots = [];

                for($i = 0; $i < 7; $i++){
                    $date = (clone $today)->modify("+$i day");

                    // get_bookable_slots expects DateTime object
                    $bookable_slots = $product->get_bookable_slots($date);

                    if(!empty($bookable_slots)){
                        foreach($bookable_slots as $slot){
                            // Filter by assigned staff
                            if(in_array($staff_id, $product->get_staff_ids())){
                                $slots[] = [
                                    'date' => $date->format('Y-m-d'),
                                    'time' => $slot['from']->format('H:i')
                                ];
                            }
                        }
                    }
                }

                if(!empty($slots)){
                    echo '<h3>Select Time</h3><ul>';
                    foreach($slots as $s){
                        echo '<li>
                            <button class="bookme-next-step" 
                                data-next-step="checkout" 
                                data-vendor-id="'.$vendor_id.'" 
                                data-staff-id="'.$staff_id.'" 
                                data-service-id="'.$service_id.'" 
                                data-date="'.$s['date'].'" 
                                data-time="'.$s['time'].'">'
                                .$s['date'].' '.$s['time'].
                            '</button>
                        </li>';
                    }
                    echo '</ul>';
                } else {
                    echo 'No available time slots.';
                }
            break;

            default:
                echo 'Invalid step.';
        }

    } catch (Throwable $e) {
        echo '<pre>';
        echo "PHP ERROR: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
        echo '</pre>';
        wp_die();
    }

    wp_die();
}



add_action('wp_ajax_bookme_load_step', 'bookme_load_step_ajax');
add_action('wp_ajax_nopriv_bookme_load_step', 'bookme_load_step_ajax');





// Show booking link in vendor dashboard
function bookme_show_vendor_booking_link() {
    // Only for vendor role
    if( current_user_can('wcfm_vendor') ) {
        $vendor_id = get_current_user_id();
        $booking_link = site_url("/company/{$vendor_id}/personal/select-master");
        echo '<div class="bookme-vendor-link" style="margin:20px 0; padding:10px; background:#f9f9f9; border:1px solid #ddd;">
            <strong>Your Booking Page Link:</strong><br>
            <input type="text" value="'.$booking_link.'" readonly style="width:100%; padding:5px;" />
            <small>Copy this link and share it with your clients.</small>
        </div>';
    }
}
add_action('wcfm_vendor_dashboard_after', 'bookme_show_vendor_booking_link');


function bookme_enqueue_scripts() {
    wp_enqueue_script('bookme-ajax', get_stylesheet_directory_uri() . '/assets/js/bookme.js', ['jquery'], '1.0', true);

    wp_localize_script('bookme-ajax', 'bookme_ajax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'bookme_enqueue_scripts');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
