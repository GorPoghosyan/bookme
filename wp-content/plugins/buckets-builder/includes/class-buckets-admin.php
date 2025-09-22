<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Buckets_Admin {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function menu() {
        add_menu_page( 'Buckets Builder', 'Buckets', 'manage_options', 'buckets-settings', [ $this, 'settings_page' ], 'dashicons-products', 56 );
    }

    public function register_settings() {
        register_setting( 'buckets_options', 'buckets_products', [
            'type' => 'array',
            'sanitize_callback' => [ $this, 'sanitize_products' ],
            'default' => []
        ] );
    }

    public function sanitize_products( $value ) {
        if ( ! is_array( $value ) ) return [];
        return array_map( 'absint', $value );
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Buckets Builder Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'buckets_options' ); ?>
                <?php $products = get_option( 'buckets_products', [] ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="buckets_products">Select products to show on Buckets page</label></th>
                        <td>
                            <select id="buckets_products" name="buckets_products[]" multiple size="10" style="min-width:350px;">
                                <?php
                                $args = [ 'post_type' => 'product', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ];
                                $loop = new WP_Query( $args );
                                while ( $loop->have_posts() ) : $loop->the_post();
                                    $id = get_the_ID();
                                    $sel = in_array( $id, (array) $products ) ? 'selected' : '';
                                    echo "<option value='{$id}' {$sel}>" . esc_html( get_the_title() ) . "</option>";
                                endwhile;
                                wp_reset_postdata();
                                ?>
                            </select>
                            <p class="description">Hold Ctrl (Cmd on mac) + click to select multiple. Save to apply.</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <h2>How to use</h2>
            <p>Create a new WordPress Page (Pages → Add New), give it a slug like <code>bukets</code> (or name "Buckets") and insert the shortcode <code>[buckets_page]</code> into the page content. Visit that page to use the builder.</p>
        </div>
        <?php
    }
}
