<?php

class Buckets_Cart {
    public function __construct() {
        add_action('wp_ajax_buckets_add_to_cart', [$this, 'add_to_cart']);
        add_action('wp_ajax_nopriv_buckets_add_to_cart', [$this, 'add_to_cart']);
    }

    public function add_to_cart() {
        $items = $_POST['items'] ?? [];
        if (!$items) wp_die();

        $names = [];
        $total = 0;
        foreach($items as $it){
            $product = wc_get_product($it['id']);
            if(!$product) continue;
            $qty = intval($it['qty']);
            $total += $product->get_price() * $qty;
            $names[] = $product->get_name().' x '.$qty;
        }

        $custom_name = "Custom Bucket: ".implode(', ', $names);

        // Ստեղծում ենք վիրտուալ ապրանք
        $bucket_id = wc_get_product_id_by_sku('custom-bucket');
        if(!$bucket_id){
            $bucket_id = wp_insert_post([
                'post_title'   => 'Custom Bucket',
                'post_type'    => 'product',
                'post_status'  => 'publish',
            ]);
            update_post_meta($bucket_id, '_price', $total);
            update_post_meta($bucket_id, '_virtual', 'yes');
            update_post_meta($bucket_id, '_sold_individually', 'yes');
            wc_get_product($bucket_id)->set_sku('custom-bucket');
        }

        update_post_meta($bucket_id, '_price', $total);
        wp_set_post_terms($bucket_id, 'simple', 'product_type');
        wc()->cart->add_to_cart($bucket_id, 1);

        wp_die();
    }
}
