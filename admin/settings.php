<?php
// no outside access
if (!defined('WPINC')) die('No access outside of wordpress.');

add_action('admin_menu', 'mra_import_psql_settings_function');
function mra_import_psql_settings_function()
{
    add_menu_page(
        'Make Ready Connect Settings',
        'Make Ready Connect',
        'manage_options',
        'mr_connect',
        'mra_import_psql_vendors_func',
    );

    add_submenu_page( 
        'mr_connect',
        'Make Ready Connect product import csv',
        'Import csv',
        'manage_options',
        'mra_import_psql_product_import_csv',
        'mra_import_psql_products_import_csv_func'
    );

    add_submenu_page( 
        'mr_connect',
        'Make Ready Connect hidden settings',
        'Hidden settings',
        'manage_options',
        'mra_import_psql_hidden_settings',
        'mra_import_psql_hidden_settings_func'
    );

    add_submenu_page( 
        'mr_connect',
        'Connect Settings',
        'Connect Settings',
        'manage_options',
        'mra_import_psql',
        'mra_import_psql_settings_func'
    );

    add_submenu_page( 
        'mr_connect',
        'Integrated Vendors',
        'Vendors',
        'manage_options',
        'mr_connect',
        'mra_import_psql_vendors_func'
    );

    

    add_submenu_page( 
        'mr_connect',
        'Make Ready Connect products',
        'Product Catalogs',
        'manage_options',
        'mra_import_psql_products',
        'mra_import_psql_products_func'
    );

    add_submenu_page( 
        'mr_connect',
        'Markup Settings',
        'Markup Settings',
        'manage_options',
        'mra_import_psql_price_edit',
        'mra_import_psql_price_edit_func'
    );

    add_submenu_page( 
        'mr_connect',
        'Make Ready Connect Vendor access',
        'Vendor access',
        'manage_options',
        'mra_import_psql_vendor_access',
        'mra_import_psql_vendor_access_func'
    );

    add_submenu_page( 
        'mr_connect',
        'Make Ready Connect orders list',
        'Orders list',
        'manage_options',
        'mra_import_psql_orders_list',
        'mra_import_psql_orders_list_func'
    );

    add_submenu_page( 
        'mr_connect',
        'Make Ready Connect Logs cron',
        'Logs cron',
        'manage_options',
        'mra_import_psql_logs_cron',
        'mra_import_psql_logs_cron_func'
    );

    
}



function mra_import_psql_settings_func()
{
?>
    <style type="text/css">
    </style>
    <div class='wrap'>
        <h1>Make Ready Connect Settings</h1>
        <form action='options.php' method='post'>
            <?php
            settings_fields('mra_import_psql_options_fields');
            do_settings_sections('mra_import_psql_options');
            ?>
            <input type='submit' name='Submit' class='button button-primary' value='Save Changes'>
        </form>
    </div>
    <script type="text/javascript">
        jQuery(document).ready( function( $ ){
            $('.form-table tbody tr:nth-child(5)').after('<tr><th scope="row">--------------</th><td>--------------------------------------------</td></tr>');
        } );
    </script>   
    <?php
    

    $mysqli_read = mra_import_psql_db_connection_read();
    $mysqli_read_write = mra_import_psql_db_connection_read_write();
    if (!$mysqli_read->connect_error or !$mysqli_read_write->connect_error) {
        echo '<p style="color:#00e84d;">Connection to the database is successful</p>';
    } else {
        echo '<p style="color:#f00;"><strong>Database connection error.</strong></p>';
        if($mysqli_read->connect_error)
            echo '<p>'.$mysqli_read->connect_error.'</p>';
        if($mysqli_read_write->connect_error)
            echo '<p>'.$mysqli_read->connect_error.'</p>';
    }

    if(!$mysqli_read->connect_error)
        $mysqli_read->close();
    if(!$mysqli_read_write->connect_error)
        $mysqli_read_write->close();

}

add_action('admin_init', 'mra_import_psql_admin_init');
function mra_import_psql_admin_init()
{
    add_settings_section(
        'default',
        '',
        '',
        'mra_import_psql_options'
    );

    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_host_read',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_port_read',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_dbname_read',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_user_read',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_password_read',
        array(
            'type' => 'string',
        )
    );



    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_host_read_write',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_port_read_write',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_dbname_read_write',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_user_read_write',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_options_fields',
        'mra_import_psql_password_read_write',
        array(
            'type' => 'string',
        )
    );



    add_settings_field(
        'mra_import_psql_host_read',
        'Host (read-only)',
        'mra_import_psql_host_read_input',
        'mra_import_psql_options',
    );

    add_settings_field(
        'mra_import_psql_port_read',
        'Port (read-only)',
        'mra_import_psql_port_read_input',
        'mra_import_psql_options',
    );

    add_settings_field(
        'mra_import_psql_dbname_read',
        'DB name (read-only)',
        'mra_import_psql_dbname_read_input',
        'mra_import_psql_options',
    );

    add_settings_field(
        'mra_import_psql_user_read',
        'User (read-only)',
        'mra_import_psql_user_read_input',
        'mra_import_psql_options',
    );

    add_settings_field(
        'mra_import_psql_password_read',
        'Password (read-only)',
        'mra_import_psql_password_read_input',
        'mra_import_psql_options',
    );


    add_settings_field(
        'mra_import_psql_host_read_write',
        'Host (read/write)',
        'mra_import_psql_host_read_write_input',
        'mra_import_psql_options',
    );

    add_settings_field(
        'mra_import_psql_port_read_write',
        'Port (read/write)',
        'mra_import_psql_port_read_write_input',
        'mra_import_psql_options',
    );

    add_settings_field(
        'mra_import_psql_dbname_read_write',
        'DB name (read/write)',
        'mra_import_psql_dbname_read_write_input',
        'mra_import_psql_options',
    );

    add_settings_field(
        'mra_import_psql_user_read_write',
        'User (read/write)',
        'mra_import_psql_user_read_write_input',
        'mra_import_psql_options',
    );

    add_settings_field(
        'mra_import_psql_password_read_write',
        'Password (read/write)',
        'mra_import_psql_password_read_write_input',
        'mra_import_psql_options',
    );
}

function mra_import_psql_host_read_input()
{
    $host = get_option('mra_import_psql_host_read');
    echo "<input id='mra_import_psql_host_read' name='mra_import_psql_host_read' type='text' value='".$host."' class='regular-text'>";
}

function mra_import_psql_port_read_input()
{
    $port = get_option('mra_import_psql_port_read');
    echo "<input id='mra_import_psql_port_read' name='mra_import_psql_port_read' type='text' value='".$port."' class='regular-text'>";
}

function mra_import_psql_dbname_read_input()
{
    $dbname = get_option('mra_import_psql_dbname_read');
    echo "<input id='mra_import_psql_dbname_read' name='mra_import_psql_dbname_read' type='text' value='".$dbname."' class='regular-text'>";
}

function mra_import_psql_user_read_input()
{
    $user = get_option('mra_import_psql_user_read');
    echo "<input id='mra_import_psql_user_read' name='mra_import_psql_user_read' type='text' value='".$user."' class='regular-text'>";
}

function mra_import_psql_password_read_input()
{
    $password = get_option('mra_import_psql_password_read');
    echo "<input id='mra_import_psql_password_read' name='mra_import_psql_password_read' type='password' value='".$password."' class='regular-text'>";
}


function mra_import_psql_host_read_write_input()
{
    $host = get_option('mra_import_psql_host_read_write');
    echo "<input id='mra_import_psql_host_read_write' name='mra_import_psql_host_read_write' type='text' value='".$host."' class='regular-text'>";
}

function mra_import_psql_port_read_write_input()
{
    $port = get_option('mra_import_psql_port_read_write');
    echo "<input id='mra_import_psql_port_read_write' name='mra_import_psql_port_read_write' type='text' value='".$port."' class='regular-text'>";
}

function mra_import_psql_dbname_read_write_input()
{
    $dbname = get_option('mra_import_psql_dbname_read_write');
    echo "<input id='mra_import_psql_dbname_read_write' name='mra_import_psql_dbname_read_write' type='text' value='".$dbname."' class='regular-text'>";
}

function mra_import_psql_user_read_write_input()
{
    $user = get_option('mra_import_psql_user_read_write');
    echo "<input id='mra_import_psql_user_read_write' name='mra_import_psql_user_read_write' type='text' value='".$user."' class='regular-text'>";
}

function mra_import_psql_password_read_write_input()
{
    $password = get_option('mra_import_psql_password_read_write');
    echo "<input id='mra_import_psql_password_read_write' name='mra_import_psql_password_read_write' type='password' value='".$password."' class='regular-text'>";
}


function mra_import_psql_admin_style() {
    wp_enqueue_style( 'mra-import-psql-admin-css', MRA_IMPORT_PSQL_URL.'css/admin-style.css', array(), '1.0' );
    wp_enqueue_style( 'mra-import-psql-admin-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '1.0' );
    wp_enqueue_script( 'mra-import-psql-jquery-redirect-js', MRA_IMPORT_PSQL_URL.'js/jquery.redirect.js', array(), '1.0', true );
    wp_enqueue_script( 'mra-import-psql-script-js', MRA_IMPORT_PSQL_URL.'js/script.js', array(), '1.0', true );
    wp_enqueue_script( 'mra-import-psql-select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', '1.0', true);
}
add_action( 'admin_enqueue_scripts', 'mra_import_psql_admin_style', 999);

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/filter-add-order.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/product_import.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/vendors.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/products.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/price-edit.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/hidden_settings.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/ajax-update-product.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/ajax-update-price.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/ajax-update-product-import-csv.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/ajax-update-img.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/vendor-access.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/orders-list.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/logs-cron.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/mra-outlets.php");

include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/mra-outlets-metabox-product.php");

function is_pos_outlet_post($post_id) {
    $post = get_post($post_id);
    if ($post && $post->post_type === 'pos_outlet') {
        return true;
    }
    return false;
}

function mra_import_psql_db_connection_read() {
    
    $host = get_option('mra_import_psql_host_read');
    $port = get_option('mra_import_psql_port_read');
    $dbname = get_option('mra_import_psql_dbname_read');
    $user = get_option('mra_import_psql_user_read');
    $password = get_option('mra_import_psql_password_read');
    $mysqli = new mysqli($host, $user, $password, $dbname, $port);

    return $mysqli;
}

function mra_import_psql_db_connection_read_write() {
    
    $host = get_option('mra_import_psql_host_read_write');
    $port = get_option('mra_import_psql_port_read_write');
    $dbname = get_option('mra_import_psql_dbname_read_write');
    $user = get_option('mra_import_psql_user_read_write');
    $password = get_option('mra_import_psql_password_read_write');
    $mysqli = new mysqli($host, $user, $password, $dbname, $port);

    return $mysqli;
}

function buildCategoryTree($parentId, $categoriesTree) {
    $categoryTree = [];

    // Если для данного родительского id есть дочерние категории
    if (isset($categoriesTree[$parentId])) {
        // Проходим по всем дочерним категориям
        foreach ($categoriesTree[$parentId] as $categoryId => $category) {
            // Рекурсивно строим дерево для текущей дочерней категории
            $category['children'] = buildCategoryTree($categoryId, $categoriesTree);
            // Добавляем текущую дочернюю категорию в дерево
            $categoryTree[] = $category;
        }
    }

    return $categoryTree;
}

function mra_import_psql_db_arr_category() {

    $array_category = array();
    $child_cats = array();
    $child2_cats = array();
    $child3_cats = array();
    $child4_cats = array();
    $mysqli_read = mra_import_psql_db_connection_read();

    $result_categories = $mysqli_read->query("SELECT id, name, parent_id FROM categories");
    if ($result_categories) {
        $categories = $result_categories->fetch_all(MYSQLI_ASSOC);
        
        $categoriesTree = [];

        // Группировка категорий по родительским id
        foreach ($categories as $category) {
            $parentId = $category['parent_id'];
            $categoryId = $category['id'];

            // Если родительского элемента еще нет в массиве, создаем его
            if (!isset($categoriesTree[$parentId])) {
                $categoriesTree[$parentId] = [];
            }

            // Добавляем текущий элемент как дочерний к родительскому элементу
            $categoriesTree[$parentId][$categoryId] = $category;
        }

        // Получение дерева категорий, начиная с корневых элементов (у которых parent_id = NULL)
        $array_category = buildCategoryTree(NULL, $categoriesTree);
    }

    $mysqli_read->close();

    return $array_category;
}

add_action('before_delete_post', 'remove_pos_cost_meta');
function remove_pos_cost_meta($post_id) {
    if (get_post_type($post_id) === 'product') {

        $mysqli_read_write = mra_import_psql_db_connection_read_write();

        $name_site = get_bloginfo('name');
        $homepage_link = get_bloginfo('url');

        $result_site = $mysqli_read_write->query("SELECT * FROM sites WHERE name='".$name_site."' AND homepage_link='".$homepage_link."'");
        if ($result_site->num_rows!=0) {
            $rows_site = $result_site->fetch_assoc();
            $site_id_db = $rows_site['id'];
        } else {
            $result_site = $mysqli_read_write->query("INSERT INTO sites VALUES (NULL, '$name_site', '$homepage_link')");
            $site_id_db = $mysqli_read_write->insert_id;
        }

        $product = wc_get_product($post_id);
        $upc = $product->get_attribute('upc');

        $result_delete = $mysqli_read_write->query("DELETE FROM added_products WHERE site_id=".$site_id_db." AND upc = ".$upc);
        

        $mysqli_read_write->close();
        // delete_post_meta($post_id, 'pos_cost');
    }
}

add_action('admin_footer', 'enqueue_custom_admin_script');

function enqueue_custom_admin_script() {
    // Check if we are on the edit or new product page
    global $pagenow, $post_type, $post;;
    $mra_import_psql_enable_rsr = get_option('mra_import_psql_enable_rsr');
    $mra_import_psql_enable_default_outlet = get_option('mra_import_psql_enable_default_outlet');
    if ( ($pagenow === 'post.php' || $pagenow === 'post-new.php') && $post_type === 'product' ) {

            $product_id = $post->ID;
            $stock = 100;
            $post_id_excluded_rsr = get_single_pos_outlet_id_by_title( 'rsr' );
            $post_id_excluded_default_outlet = get_single_pos_outlet_id_by_title( 'Default Outlet' );
            $wc_pos_outlet_stock = get_post_meta( $product_id, '_wc_pos_outlet_stock', true );
            $wc_pos_outlet_cost = get_post_meta( $product_id, '_wc_pos_outlet_cost', true );
        
            if (is_array($wc_pos_outlet_stock)) {
                $array_wc_pos_outlet_stock = $wc_pos_outlet_stock;
            } else {
                $array_wc_pos_outlet_stock = unserialize($wc_pos_outlet_stock);
            }

            if (is_array($wc_pos_outlet_cost)) {
                $array_wc_pos_outlet_cost = $wc_pos_outlet_cost;
            } else {
                $array_wc_pos_outlet_cost = unserialize($wc_pos_outlet_cost);
            }

            $array_wc_pos_outlet_stock_excluded = array();
            $array_wc_pos_outlet_cost_excluded = array();

            if ($mra_import_psql_enable_rsr != 'on' && has_term('ffl_firearm', 'product_tag', $product_id)) {    
                if (isset($array_wc_pos_outlet_stock[$post_id_excluded_rsr])) {
                    $array_wc_pos_outlet_stock_excluded[$post_id_excluded_rsr] = $array_wc_pos_outlet_stock[$post_id_excluded_rsr];
                    unset($array_wc_pos_outlet_stock[$post_id_excluded_rsr]);
                }
            }

            if ($mra_import_psql_enable_default_outlet != 'on' && has_term('ffl_firearm', 'product_tag', $product_id)) {    
                if (isset($array_wc_pos_outlet_stock[$post_id_excluded_default_outlet])) {
                    $array_wc_pos_outlet_stock_excluded[$post_id_excluded_default_outlet] = $array_wc_pos_outlet_stock[$post_id_excluded_default_outlet];
                    unset($array_wc_pos_outlet_stock[$post_id_excluded_default_outlet]);
                }
            }
            

            if(empty($array_wc_pos_outlet_stock)) {
                $stock = 0;
            }

            // var_dump($stock);

        // $wc_pos_outlet_stock = get_post_meta( 110986, '_wc_pos_outlet_stock', true );
        // var_dump($wc_pos_outlet_stock);
        /* if ($mra_import_psql_enable_default_outlet != 'on' && $mra_import_psql_enable_rsr != 'on' && has_term('ffl_firearm', 'product_tag', $product_id)) {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Set a delay for executing the code
                setTimeout(function() {
                    // Hide the <p> element containing a <label> with text "RSR"
                    $('#wc_pos_outlet_stock_container .form-field').each(function() {
                        if ($(this).find('label').text().trim() === 'RSR') {
                            $(this).hide();
                        }
                    });

                    var totalStock = 0;

                    // Выбор всех элементов input с атрибутом name, начинающимся на "wc_pos_outlet_stock_"
                    $('input[name^="wc_pos_outlet_stock_"]').not(function() {
                        // Исключение тех, у которых родительский <p> элемент имеет display: none;
                        return $(this).closest('p').css('display') === 'none';
                    }).each(function() {
                        // Суммирование значений input
                        totalStock += parseFloat($(this).val()) || 0;
                    });

                    // Запись суммы в input с name="_stock"
                    $('input[name="_stock"]').val(totalStock);

                    $('li.select2-selection__choice[title="RSR"]').hide();
                    
                }, 3000); // Delay of 3 seconds
            });
            </script>
            <?php
        } */

        /* if ($mra_import_psql_enable_default_outlet != 'on' && has_term('ffl_firearm', 'product_tag', $product_id)) {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Set a delay for executing the code
                setTimeout(function() {
                    // Hide the <p> element containing a <label> with text "Default Outlet"
                    $('#wc_pos_outlet_stock_container .form-field').each(function() {
                        if ($(this).find('label').text().trim() === 'Default Outlet') {
                            $(this).hide();
                        }
                    });

                    var totalStock = 0;

                    // Выбор всех элементов input с атрибутом name, начинающимся на "wc_pos_outlet_stock_"
                    $('input[name^="wc_pos_outlet_stock_"]').not(function() {
                        // Исключение тех, у которых родительский <p> элемент имеет display: none;
                        return $(this).closest('p').css('display') === 'none';
                    }).each(function() {
                        // Суммирование значений input
                        totalStock += parseFloat($(this).val()) || 0;
                    });

                    // Запись суммы в input с name="_stock"
                    $('input[name="_stock"]').val(totalStock);

                    $('li.select2-selection__choice[title="Default Outlet"]').hide();
                    
                }, 3000); // Delay of 3 seconds
            });
            </script>
            <?php
        } */
    }
}

function custom_hide_product_tags_script() {
    if (is_product()) { // Only load on single product pages
        wp_enqueue_script('hide-tags-script', MRA_IMPORT_PSQL_URL . 'js/hide-tags.js', [], null, true);
        wp_enqueue_style('custom-product-styles', MRA_IMPORT_PSQL_URL . 'css/custom-product-styles.css');
    }
}
add_action('wp_enqueue_scripts', 'custom_hide_product_tags_script');