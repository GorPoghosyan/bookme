<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Hook into init to override
add_action('init', function() {
    // Remove plugin's default AJAX handler
    remove_all_actions('wp_ajax_wcfm_ajax_controller');

    // Add your own handler
    add_action('wp_ajax_wcfm_ajax_controller', 'my_custom_wcfm_staffs_controller');
});

function my_custom_wcfm_staffs_controller() {
    if (isset($_POST['controller']) && $_POST['controller'] === 'wcfm-staffs') {
        global $WCFM, $wpdb;

        $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $offset = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;

        $staff_user_role = apply_filters('wcfm_staff_user_role', 'shop_staff');

        $args = array(
            'role__in'     => array($staff_user_role),
            'orderby'      => 'ID',
            'order'        => 'ASC',
            'offset'       => $offset,
            'number'       => $length,
        );

        if (isset($_POST['search']) && !empty($_POST['search']['value'])) {
            $serach_str = sanitize_text_field($_POST['search']['value']);
            $args['search'] = "*{$serach_str}*";
        }

        // Total count (without pagination/filter)
        $total_args = $args;
        $total_args['number'] = -1;
        $total_args['offset'] = 0;
        $total_args['search'] = '';
        $recordsTotal = count(get_users($total_args));

        // Filtered users
        $users = get_users($args);
        $recordsFiltered = count(get_users(array_merge($args, ['number' => -1, 'offset' => 0])));

        $data = [];
        foreach ($users as $user) {
            $row = [];

            // Staff (link)
            $row[] = '<a href="' . get_wcfm_shop_staffs_manage_url($user->ID) . '" class="wcfm_dashboard_item_title">' . esc_html($user->user_login) . '</a>';

            // Store
            $vendor_id = get_user_meta($user->ID, '_wcfm_vendor', true);
            if ($vendor_id) {
                $row[] = '<span class="wcfm_vendor_store">' . $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor($vendor_id) . '</span>';
            } else {
                $row[] = '&ndash;';
            }

            // Name
            $row[] = esc_html($user->first_name . ' ' . $user->last_name);

            // Email
            $row[] = esc_html($user->user_email);

            // Actions
            $actions  = '<a class="wcfm-action-icon" href="' . get_wcfm_shop_staffs_manage_url($user->ID) . '"><span class="wcfmfa fa-edit text_tip" data-tip="Manage Staff"></span></a>';
            $actions .= '<a class="wcfm_staff_delete wcfm-action-icon" href="#" data-staffid="' . $user->ID . '"><span class="wcfmfa fa-trash-alt text_tip" data-tip="Delete"></span></a>';
            $row[] = $actions;

            $data[] = $row;
        }

        $response = [
            "draw"            => $draw,
            "recordsTotal"    => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $data,
        ];

        wp_send_json($response);
    }
}
