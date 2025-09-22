<?php

class Buckets_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'settings']);
    }

    public function menu() {
        add_menu_page('Buckets Builder', 'Buckets', 'manage_options', 'buckets-settings', [$this, 'settings_page']);
    }

    public function settings() {
        register_setting('buckets_options', 'buckets_products'); // պահում ենք product IDs
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Buckets Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('buckets_options'); ?>
                <?php $products = get_option('buckets_products', []); ?>
                <label>Choose Products:</label><br>
                <select multiple name="buckets_products[]">
                    <?php
                    $args = ['post_type' => 'product', 'posts_per_page' => -1];
                    $loop = new WP_Query($args);
                    while ($loop->have_posts()): $loop->the_post();
                        $id = get_the_ID();
                        $sel = in_array($id, (array)$products) ? 'selected' : '';
                        echo "<option value='{$id}' {$sel}>" . get_the_title() . "</option>";
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </select>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
