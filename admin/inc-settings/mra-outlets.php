<?php
// no outside access
if (!defined('WPINC')) die('No access outside of wordpress.');

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

add_action('admin_notices', 'check_conflicting_plugins');
function check_conflicting_plugins() {
    $is_pos_active = is_plugin_active('woocommerce-point-of-sale/woocommerce-point-of-sale.php');
    $is_pos_mod_active = is_plugin_active('woocommerce-point-of-sale-modification/woocommerce-point-of-sale-modification.php');

    if ($is_pos_active || $is_pos_mod_active) {
        echo '<div class="notice notice-error"><p>';
        echo 'For the MAKE READY CONNECT plugin to work correctly, deactivate the following plugins: “woocommerce-point-of-sale” and “woocommerce-point-of-sale-modification”.';
        echo '</p></div>';
    }
}

if (
    !is_plugin_active('woocommerce-point-of-sale/woocommerce-point-of-sale.php') &&
    !is_plugin_active('woocommerce-point-of-sale-modification/woocommerce-point-of-sale-modification.php')
) {

    function register_pos_outlet_post_type() {
        $labels = array(
            'name'               => 'MRA Outlets',
            'singular_name'      => 'MRA Outlet',
            'menu_name'          => 'MRA Outlets',
            'name_admin_bar'     => 'MRA Outlet',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New MRA Outlet',
            'edit_item'          => 'Edit MRA Outlet',
            'new_item'           => 'New MRA Outlet',
            'view_item'          => 'View MRA Outlet',
            'search_items'       => 'Search MRA Outlets',
            'not_found'          => 'No MRA Outlets found',
            'not_found_in_trash' => 'No MRA Outlets found in Trash',
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'show_in_menu'       => true,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-store', // Store icon
            'supports'           => array('title', 'editor'), // Supports title and description
            'has_archive'        => true,
            'rewrite'            => array('slug' => 'mra-outlets'), // Pretty URL
            'show_in_rest'       => true, // Supports Gutenberg
        );

        register_post_type('pos_outlet', $args);
    }
    add_action('init', 'register_pos_outlet_post_type');
    
} else {
    error_log('One of the “woocommerce-point-of-sale” and “woocommerce-point-of-sale-modification” plugins is active and the Connect plugin cannot work correctly.');
}


