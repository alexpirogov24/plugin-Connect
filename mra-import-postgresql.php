<?php
/*
Plugin Name: Make Ready Connect
Description: Make Ready Connect
Version: 1.6.4
Author: MRA
*/

// no outside access
if (!defined('WPINC')) die('No access outside of wordpress.');

define('MRA_IMPORT_PSQL_NAME', 'Make Ready Connect');
define('MRA_IMPORT_PSQL_VERSION', '1.6.4');
define('MRA_IMPORT_PSQL_DIR', plugin_dir_path(__FILE__));
define('MRA_IMPORT_PSQL_URL', plugins_url('/', __FILE__));
define('MRA_IMPORT_PSQL_BASENAME', plugin_basename(__FILE__));


foreach (glob(MRA_IMPORT_PSQL_DIR . "includes/*.php") as $filename) {
    include($filename);
}

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

// Register error handlers
set_error_handler('custom_plugin_error_handler');
register_shutdown_function('custom_plugin_shutdown_function');

// Clean up old logs on plugin load
add_action('init', 'cleanup_old_logs');


/***** End of plugin error logging code *****/