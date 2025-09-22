<?php

class Buckets_Frontend {
    public function __construct() {
        add_shortcode('buckets_page', [$this, 'render']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
    }

    public function assets() {
        wp_enqueue_style('buckets-css', BUCKETS_URL.'assets/css/buckets.css');
        wp_enqueue_script('buckets-js', BUCKETS_URL.'assets/js/buckets.js', ['jquery'], false, true);
    }

    public function render() {
        ob_start();
        include BUCKETS_PATH.'templates/buckets-page.php';
        return ob_get_clean();
    }
}
