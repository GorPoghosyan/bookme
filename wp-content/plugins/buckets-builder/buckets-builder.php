<?php
/**
 * Plugin Name: Buckets Builder
 * Description: Custom bucket page with selectable products and bundle add-to-cart.
 * Version: 1.0
 * Author: Gor Zakaryan
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('BUCKETS_PATH', plugin_dir_path(__FILE__));
define('BUCKETS_URL', plugin_dir_url(__FILE__));

require_once BUCKETS_PATH . 'includes/class-buckets-admin.php';
require_once BUCKETS_PATH . 'includes/class-buckets-frontend.php';
require_once BUCKETS_PATH . 'includes/class-buckets-cart.php';

new Buckets_Admin();
new Buckets_Frontend();
new Buckets_Cart();
