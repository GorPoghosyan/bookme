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
