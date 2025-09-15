<?php
/*
Plugin Name: Make Ready Connect
Description: Make Ready Connect
Version: 1.6.5
Author: MRA
*/

// no outside access
if (!defined('WPINC')) die('No access outside of wordpress.');

define('MRA_IMPORT_PSQL_NAME', 'Make Ready Connect');
define('MRA_IMPORT_PSQL_VERSION', '1.6.3');
define('MRA_IMPORT_PSQL_DIR', plugin_dir_path(__FILE__));
define('MRA_IMPORT_PSQL_URL', plugins_url('/', __FILE__));
define('MRA_IMPORT_PSQL_BASENAME', plugin_basename(__FILE__));


// foreach (glob(MRA_IMPORT_PSQL_DIR . "includes/*.php") as $filename) {
//     include($filename);
// }

foreach (glob(MRA_IMPORT_PSQL_DIR . "admin/*.php") as $filename) {
    include($filename);
}

$mysqli_read = mra_import_psql_db_connection_read();
$mysqli_read_write = mra_import_psql_db_connection_read_write();
if (!$mysqli_read->connect_error or !$mysqli_read_write->connect_error) {

    $date_text = date("d_m_y");
    if(!file_exists(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt"))
        file_put_contents(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt", '');
    $log_file = fopen(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt", "a");

    $list_vendors_arr = array();
    if(get_option('list_vendors_connect')) {
        $list_vendors_arr = unserialize( get_option('list_vendors_connect') );
    }

    if (!empty($list_vendors_arr)) {
        include(MRA_IMPORT_PSQL_DIR . "/cron-update-product.php");
    } else {
        if ( ! wp_next_scheduled( 'cron_mra_psql_main_interval_event_cheat' ) ) {
            wp_unschedule_hook( 'cron_mra_psql_main_interval_event_cheat' );
            $text_log = date("Y-m-d H:i:s").": stop cron MAIN CRON.\r\n";
            $result_fwrite = fwrite($log_file, $text_log);
        }

        if ( ! wp_next_scheduled( 'cron_mra_psql_event' ) ) {
            wp_unschedule_hook( 'cron_mra_psql_event' );
            $text_log = date("Y-m-d H:i:s").": stop cron INTERMEDIATE CRON.\r\n";
            $result_fwrite = fwrite($log_file, $text_log);
        }

        if (get_option('cron_update_product_data')) {
            delete_option('cron_update_product_data');
            $text_log = date("Y-m-d H:i:s").": Delete cron array data.\r\n";
            $result_fwrite = fwrite($log_file, $text_log);
        }
    }

    fclose($log_file);
} else {

    $date_text = date("d_m_y");
    if(!file_exists(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt"))
        file_put_contents(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt", '');
    $log_file = fopen(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt", "a");

    if ( ! wp_next_scheduled( 'cron_mra_psql_main_interval_event_cheat' ) ) {
        wp_unschedule_hook( 'cron_mra_psql_main_interval_event_cheat' );
        $text_log = date("Y-m-d H:i:s").": stop cron MAIN CRON.\r\n";
        $result_fwrite = fwrite($log_file, $text_log);
    }

    if ( ! wp_next_scheduled( 'cron_mra_psql_event' ) ) {
        wp_unschedule_hook( 'cron_mra_psql_event' );
        $text_log = date("Y-m-d H:i:s").": stop cron INTERMEDIATE CRON.\r\n";
        $result_fwrite = fwrite($log_file, $text_log);
    }
        
    if (get_option('cron_update_product_data')) {
        delete_option('cron_update_product_data');
        $text_log = date("Y-m-d H:i:s").": Delete cron array data.\r\n";
        $result_fwrite = fwrite($log_file, $text_log);
    }

    fclose($log_file); 
}

add_action('woocommerce_product_query', 'custom_product_query');
function custom_product_query($q) {
   
  if (get_option('mra_import_psql_hide_out_of_stock') == 'on') {

    if (!is_admin() && (is_shop() || is_archive())) {
        $meta_query = $q->get('meta_query');

        $meta_query[] = array(
            'key' => '_stock',
            'value' => 1,
            'compare' => '>=',
            'type' => 'NUMERIC',
        );

        $q->set('meta_query', $meta_query);
    }
  }
}


/***** Code of plugin error logging *****/

// Define the path to the log file
define('PLUGIN_ERROR_LOG_FILE', plugin_dir_path(__FILE__) . 'plugin_errors.log');

// Get the plugin directory path
$plugin_dir = plugin_dir_path(__FILE__);

// Custom error handler function
function custom_plugin_error_handler($errno, $errstr, $errfile, $errline)
{
    global $plugin_dir;
    
    // Log only errors related to this plugin
    if (strpos($errfile, $plugin_dir) !== false) {
        $log_entry = "[" . date("Y-m-d H:i:s") . "] Error: {$errstr} in {$errfile} on line {$errline}\n";
        
        // Write to the log file
        error_log($log_entry, 3, PLUGIN_ERROR_LOG_FILE);
    }
}

// Function to handle fatal errors
function custom_plugin_shutdown_function()
{
    global $plugin_dir;
    
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        if (strpos($error['file'], $plugin_dir) !== false) {
            $log_entry = "[" . date("Y-m-d H:i:s") . "] Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}\n";
            error_log($log_entry, 3, PLUGIN_ERROR_LOG_FILE);
        }
    }
}

// Function to clean up old logs (older than 7 days)
function cleanup_old_logs()
{
    if (file_exists(PLUGIN_ERROR_LOG_FILE)) {
        $file_modified_time = filemtime(PLUGIN_ERROR_LOG_FILE);
        $seven_days_ago = time() - (7 * 24 * 60 * 60);
        if ($file_modified_time < $seven_days_ago) {
            unlink(PLUGIN_ERROR_LOG_FILE);
        }
    }
}

///////////////////////////////////////////////
/*
add_action('rest_api_init', function () {
    register_rest_route('getProducts/v1', '/products', [
        'methods' => 'GET',
        'callback' => 'getProducts',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getProducts(WP_REST_Request $request) {
    global $wpdb;

    $query = "
        SELECT p.ID, p.post_title, pm.meta_value AS sku
        FROM {$wpdb->posts} AS p
        LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id AND pm.meta_key = '_sku'
        WHERE p.post_type = 'product'
        ORDER BY p.ID ASC
    ";

    $result = $wpdb->get_results($query);

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('getOption/v1', '/options', [
        'methods' => 'GET',
        'callback' => 'getOption',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getOption(WP_REST_Request $request) {
    $result = get_option($request->get_param('option'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('addOption/v1', '/options', [
        'methods' => 'GET',
        'callback' => 'addOption',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function addOption(WP_REST_Request $request) {
    $result = add_option($request->get_param('option'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('updateOption/v1', '/options', [
        'methods' => 'GET',
        'callback' => 'updateOption',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function updateOption(WP_REST_Request $request) {
    $result = update_option($request->get_param('option'), $request->get_param('value'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('deleteOption/v1', '/options', [
        'methods' => 'GET',
        'callback' => 'deleteOption',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function deleteOption(WP_REST_Request $request) {
    $result = delete_option($request->get_param('option'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('getSinglePosOutletIdByTitle/v1', '/outlet', [
        'methods' => 'GET',
        'callback' => 'getSinglePosOutletIdByTitle',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getSinglePosOutletIdByTitle(WP_REST_Request $request) {
    $title = $request->get_param('title');

    global $wpdb;

    // Prepare a query to retrieve the record ID with post_type = pos_outlet and a title containing $title
    $query = $wpdb->prepare(
        "
        SELECT ID
        FROM {$wpdb->posts}
        WHERE post_type = %s
        AND post_title LIKE %s
        LIMIT 1
        ",
        'pos_outlet',
        '%' . $wpdb->esc_like( $title ) . '%'
    );

    // Executing the query and getting one result
    $result = $wpdb->get_var( $query );

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('getPostStatus/v1', '/status', [
        'methods' => 'GET',
        'callback' => 'getPostStatus',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getPostStatus(WP_REST_Request $request) {
    $result = get_post_status($request->get_param('wpid'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('getAttr/v1', '/attr', [
        'methods' => 'GET',
        'callback' => 'getAttr',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getAttr(WP_REST_Request $request) {
    $product = wc_get_product( $request->get_param('wpid') );
    if ( ! $product ) {
        return new WP_REST_Response(['data' => (false)], 200);
    }
    $upc = $product->get_attribute("pa_upc");

    if ($request->get_param('bool') == true) {
        return new WP_REST_Response(['data' => (! empty($upc))], 200);
    } else {
        return new WP_REST_Response(['data' => ($upc)], 200);
    }
}

add_action('rest_api_init', function () {
    register_rest_route('getPostMeta/v1', '/meta', [
        'methods' => 'GET',
        'callback' => 'getPostMeta',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getPostMeta(WP_REST_Request $request) {
    $result = get_post_meta($request->get_param('wpid'), $request->get_param('option'), $request->get_param('bool'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('hasTerm/v1', '/term', [
        'methods' => 'GET',
        'callback' => 'hasTerm',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function hasTerm(WP_REST_Request $request) {
    $result = has_term($request->get_param('name'), $request->get_param('product_tag'), $request->get_param('wpid'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('getPostTerms/v1', '/postTerm', [
        'methods' => 'GET',
        'callback' => 'getPostTerms',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getPostTerms(WP_REST_Request $request) {
    $result = wp_get_post_terms($request->get_param('wpid'), $request->get_param('pa_upc'), array('fields' => 'names'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('updatePostMeta/v1', '/meta', [
        'methods' => 'GET',
        'callback' => 'updatePostMeta',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function updatePostMeta(WP_REST_Request $request) {
    update_post_meta($request->get_param('wpid'), $request->get_param('field'), $request->get_param('value'));

    return new WP_REST_Response(['data' => (true)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('getPost/v1', '/post', [
        'methods' => 'GET',
        'callback' => 'getPost',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getPost(WP_REST_Request $request) {
    $result = get_post($request->get_param('key'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('getPosts/v1', '/post', [
        'methods' => 'GET',
        'callback' => 'getPosts',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getPosts(WP_REST_Request $request) {
    $result = get_terms();

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('getBlogInfo/v1', '/bloginfo', [
        'methods' => 'GET',
        'callback' => 'getBlogInfo',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function getBlogInfo(WP_REST_Request $request) {
    $result = get_bloginfo($request->get_param('field'));

    return new WP_REST_Response(['data' => ($result)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('checkProductAttr/v1', '/attr', [
        'methods' => 'GET',
        'callback' => 'checkProductAttr',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function checkProductAttr(WP_REST_Request $request) {
    $product = wc_get_product($request->get_param('wpid'));
    if (!$product) {
        return new WP_REST_Response(['data' => (false)], 200); // Продукт не найден
    }

    // Получаем все атрибуты продукта
    $attributes = $product->get_attributes();
    
    // Проверка на наличие атрибутов
    if (empty($attributes)) {
        return new WP_REST_Response(['data' => (false)], 200);
    }

    // Флаг для проверки обязательного атрибута pa_upc
    $has_upc = false;

    // Проверка каждого атрибута
    foreach ($attributes as $attribute) {
        // Пропускаем, если атрибут не заполнен
        if (!$attribute->get_options()) {
            return new WP_REST_Response(['data' => (false)], 200);
        }

        // Проверяем наличие атрибута pa_upc
        if ($attribute->get_name() === 'pa_upc') {
            $has_upc = true;
            // Проверка, что значение атрибута pa_upc не пустое
            if (empty($attribute->get_options())) {
                return new WP_REST_Response(['data' => (false)], 200);
            }
        }
    }

    return new WP_REST_Response(['data' => ($has_upc)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('saveWcCustomAttributes1111/v1', '/attr', [
        'methods' => 'GET',
        'callback' => 'saveWcCustomAttributes1111',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function saveWcCustomAttributes1111(WP_REST_Request $request) {
    $i = 0;
    foreach ($custom_attributes as $name => $value) {
        wp_set_object_terms($post_id, $value, $name, true);
        $product_attributes[$i] = array(
            'name' => $name,
            'value' => $value,
            'is_visible' => 1,
            'is_variation' => 0,
            'is_taxonomy' => 1
        );
        $i++;
    }
    update_post_meta($post_id, '_product_attributes', $product_attributes);

    return new WP_REST_Response(['data' => (true)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('mraCheckAndAddTag/v1', '/tag', [
        'methods' => 'GET',
        'callback' => 'mraCheckAndAddTag',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});

function mraCheckAndAddTag(WP_REST_Request $request) {
    $product_id = $request->get_param('wpid');
    $tag_name = 'personal product';

    $current_tags = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'names' ) );
    if ( in_array( $tag_name, $current_tags, true ) ) {
        return new WP_REST_Response(['data' => (false)], 200);
    }

    $tag = term_exists( $tag_name, 'product_tag' );
    if ( ! $tag ) {
        $tag = wp_insert_term( $tag_name, 'product_tag' );
    }

    if ( ! is_wp_error( $tag ) ) {
        wp_set_post_terms( $product_id, $tag_name, 'product_tag', true );
        return new WP_REST_Response(['data' => (true)], 200);
    }

    return new WP_REST_Response(['data' => (false)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('checkAndUpdateProductStatus/v1', '/status', [
        'methods' => 'GET',
        'callback' => 'checkAndUpdateProductStatus',
        'permission_callback' => '__return_true',  // Allows public access; change for authentication if needed
    ]);
});



function checkAndUpdateProductStatus(WP_REST_Request $request) {
    $product_id = $request->get_param('wpid');

    if (!function_exists('wc_get_product')) {
        return new WP_REST_Response(['data' => (false)], 200);
    }
    
    $product = wc_get_product($product_id);
    if (!$product) {
        return new WP_REST_Response(['data' => (false)], 200);
    }

    if (is_personal_product($product_id)) {
        return new WP_REST_Response(['data' => (false)], 200);
    }
    
    // Get the value of the meta field _wc_pos_outlet_stock
    $outlet_stock = get_post_meta($product_id, '_wc_pos_outlet_stock', true);
    
    // Check if the field is empty or does not exist
    $is_outlet_stock_empty = empty($outlet_stock) || (is_array($outlet_stock) && count($outlet_stock) === 0);
    
    // Get price
    $price = $product->get_price();
    
    // Get current product status
    $current_status = $product->get_status();
    
    if (($is_outlet_stock_empty && (empty($price) || $price == 0) && $current_status !== 'draft')) {
        wp_update_post([
            'ID' => $product_id,
            'post_status' => 'draft'
        ]);
        return new WP_REST_Response(['data' => ('draft')], 200);
    } elseif (($current_status === 'draft' && $price > 0)) {
        wp_update_post([
            'ID' => $product_id,
            'post_status' => 'publish'
        ]);
        return new WP_REST_Response(['data' => ('publish')], 200);
    }

    return new WP_REST_Response(['data' => (false)], 200);
}

add_action('rest_api_init', function () {
    register_rest_route('getProductByUPC/v1', '/product', array(
        'methods' => 'GET',
        'callback' => 'wh_get_product_by_upc',
        'permission_callback' => '__return_true', // Adjust for authentication if needed
        'args' => array(
            'upc' => array(
                'required' => true,
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param) && !empty($param);
                },
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('batch/v1', '/products-details', [
        'methods' => 'POST',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_json_params();
            $wpids = $params['wpids'] ?? [];
            $meta_keys = $params['meta_keys'] ?? [
                '_wc_pos_outlet_stock',
                '_wc_pos_outlet_cost',
                '_minimum_advertised_price',
                '_regular_price',
                '_price',
                '_wc_cog_cost'
            ];
            $results = [];

            foreach ($wpids as $wpid) {
                $results[$wpid] = [
                    'meta' => [],
                    'terms' => [
                        'ffl_firearm' => has_term('ffl_firearm', 'product_tag', $wpid),
                        'pa_upc' => wp_get_post_terms($wpid, 'pa_upc', ['fields' => 'names'])
                    ],
                    'status' => get_post_status($wpid),
                    'attributes' => function_exists('check_product_attributes') ? check_product_attributes($wpid) : false,
                    'upc_attribute' => function_exists('mra_check_upc_attribute') ? mra_check_upc_attribute($wpid, false) : ''
                ];

                foreach ($meta_keys as $key) {
                    $results[$wpid]['meta'][$key] = get_post_meta($wpid, $key, true);
                }
            }

            return new WP_REST_Response(['data' => $results], 200);
        },
    ]);

    // Batch update post meta
    register_rest_route('batch/v1', '/update-post-meta', [
        'methods' => 'POST',
        'callback' => function (WP_REST_Request $request) {
            $params = $request->get_json_params();
            $updates = $params['updates'] ?? []; // Array of [wpid, field, value]
            $results = [];

            foreach ($updates as $update) {
                $wpid = $update['wpid'] ?? 0;
                $field = $update['field'] ?? '';
                $value = $update['value'] ?? '';
                if ($wpid && $field) {
                    $results[$wpid][$field] = update_post_meta($wpid, $field, $value);
                }
            }

            return new WP_REST_Response(['data' => $results], 200);
        },
    ]);
});
*/
////////////////////////////////////////////////////////////////////////////////

// Register error handlers
set_error_handler('custom_plugin_error_handler');
register_shutdown_function('custom_plugin_shutdown_function');

// Clean up old logs on plugin load
add_action('init', 'cleanup_old_logs');


/***** End of plugin error logging code *****/