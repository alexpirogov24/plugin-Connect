<?php
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function mra_import_psql_hidden_settings_func() { 
    if (isset($_GET['edit_ex_img']) && $_GET['edit_ex_img']=='yes') {
        include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/all-update-ex-img.php");
    } else { ?>

        <style type="text/css">
            #update_price_btn {
                background-color: #32c49d;
                cursor: pointer;
                text-decoration: none;
                color: #fff;
                padding: 2px 14px;
                line-height: 2.15384615;
                font-size: 13px;
                margin: 15px 0 15px;
                border-radius: 4px;
                display: table;
            }
            #update_price_btn:hover {
                background-color: #88d9c3;
            }
            #update_price_btn.no_active {
                pointer-events: none;
                cursor: default;
                background-color: #e1e1e1;
            }
            .radio_select {
                margin: 5px 0;
            }
        </style>
        <div class='wrap'>
            <h1>Make Ready Connect hidden settings</h1>
            <?php
            if (is_plugin_active( 'external-images/external-images.php' )) {
                $class = '';
                $display = 'none';
            } else {
                $class = 'no_active';
                $display = 'block';
            } ?>
            <a id="update_price_btn" class="<?= $class; ?>" href="/wp-admin/admin.php?page=mra_import_psql_hidden_settings&edit_ex_img=yes">Update all image</a>


            <form action='options.php' method='post'>
                <?php
                settings_fields('mra_import_psql_hidden_settins_fields');
                do_settings_sections('mra_import_psql_hidd_sett_options');
                ?>
                <input type='submit' name='Submit' class='button button-primary' value='Save Changes'>
            </form>
        </div>    
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
}

add_action('admin_init', 'mra_import_psql_hidd_sett_init');
function mra_import_psql_hidd_sett_init()
{
    add_settings_section(
        'default',
        '',
        '',
        'mra_import_psql_hidd_sett_options'
    );


    register_setting(
        'mra_import_psql_hidden_settins_fields',
        'mra_import_psql_enable_ffl',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_hidden_settins_fields',
        'mra_import_psql_enable_nfa_products',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_hidden_settins_fields',
        'mra_import_psql_enable_rsr',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_hidden_settins_fields',
        'mra_import_psql_enable_nfa',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_hidden_settins_fields',
        'mra_import_psql_enable_default_outlet',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_hidden_settins_fields',
        'mra_import_psql_enable_attribute_checking',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_hidden_settins_fields',
        'mra_import_psql_hide_out_of_catalog_connect',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_hidden_settins_fields',
        'mra_import_psql_cron_time',
        array(
            'type' => 'string',
        )
    );


    add_settings_field(
        'mra_import_psql_enable_ffl',
        'Enable the display of firearm products',
        'mra_import_psql_enable_ffl_input',
        'mra_import_psql_hidd_sett_options',
    );

    add_settings_field(
        'mra_import_psql_enable_nfa_products',
        'Enable the display of NFA products',
        'mra_import_psql_enable_nfa_products_input',
        'mra_import_psql_hidd_sett_options',
    );

    add_settings_field(
        'mra_import_psql_enable_rsr',
        'Enable RSR firearms',
        'mra_import_psql_enable_rsr_input',
        'mra_import_psql_hidd_sett_options',
    );

    add_settings_field(
        'mra_import_psql_enable_nfa',
        'Enable NFA Items tag',
        'mra_import_psql_enable_nfa_input',
        'mra_import_psql_hidd_sett_options',
    );

    add_settings_field(
        'mra_import_psql_enable_default_outlet',
        'Enable Default Outlet firearms',
        'mra_import_psql_enable_default_outlet_input',
        'mra_import_psql_hidd_sett_options',
    );

    add_settings_field(
        'mra_import_psql_enable_attribute_checking',
        'Enable attribute checking',
        'mra_import_psql_enable_attribute_checking_input',
        'mra_import_psql_hidd_sett_options',
    );

    add_settings_field(
        'mra_import_psql_hide_out_of_catalog_connect',
        'Hide missing items from the Connect product catalog',
        'mra_import_psql_hide_out_of_catalog_connect_input',
        'mra_import_psql_hidd_sett_options',
    );

    add_settings_field(
        'mra_import_psql_cron_time',
        'Cron time (minutes)',
        'mra_import_psql_cron_time_input',
        'mra_import_psql_hidd_sett_options',
    );
}

function mra_import_psql_enable_ffl_input()
{
    $mra_import_psql_enable_ffl = get_option('mra_import_psql_enable_ffl');
    if ($mra_import_psql_enable_ffl == 'on')
        $checked = 'checked';
    else
        $checked = '';

    echo '<input type="checkbox" id="mra_import_psql_enable_ffl" name="mra_import_psql_enable_ffl" '.$checked.'>';
}

function mra_import_psql_enable_nfa_products_input()
{
    $mra_import_psql_enable_nfa_products = get_option('mra_import_psql_enable_nfa_products');
    if ($mra_import_psql_enable_nfa_products == 'on')
        $checked = 'checked';
    else
        $checked = '';

    echo '<input type="checkbox" id="mra_import_psql_enable_nfa_products" name="mra_import_psql_enable_nfa_products" '.$checked.'>';
}

function mra_import_psql_enable_rsr_input()
{
    $mra_import_psql_enable_rsr = get_option('mra_import_psql_enable_rsr');
    if ($mra_import_psql_enable_rsr == 'on')
        $checked = 'checked';
    else
        $checked = '';

    echo '<input type="checkbox" id="mra_import_psql_enable_rsr" name="mra_import_psql_enable_rsr" '.$checked.'>';
}

function mra_import_psql_enable_nfa_input()
{
    $mra_import_psql_enable_nfa = get_option('mra_import_psql_enable_nfa');
    if ($mra_import_psql_enable_nfa == 'on')
        $checked = 'checked';
    else
        $checked = '';

    echo '<input type="checkbox" id="mra_import_psql_enable_nfa" name="mra_import_psql_enable_nfa" '.$checked.'>';
}

function mra_import_psql_enable_default_outlet_input()
{
    $mra_import_psql_enable_default_outlet = get_option('mra_import_psql_enable_default_outlet');
    if ($mra_import_psql_enable_default_outlet == 'on')
        $checked = 'checked';
    else
        $checked = '';

    echo '<input type="checkbox" id="mra_import_psql_enable_default_outlet" name="mra_import_psql_enable_default_outlet" '.$checked.'>';
}

function mra_import_psql_enable_attribute_checking_input()
{
    $mra_import_psql_enable_attribute_checking = get_option('mra_import_psql_enable_attribute_checking');
    if ($mra_import_psql_enable_attribute_checking == 'on')
        $checked = 'checked';
    else
        $checked = '';

    echo '<input type="checkbox" id="mra_import_psql_enable_attribute_checking" name="mra_import_psql_enable_attribute_checking" '.$checked.'>';
}

function mra_import_psql_hide_out_of_catalog_connect_input()
{
    $mra_import_psql_hide_out_of_catalog_connect = get_option('mra_import_psql_hide_out_of_catalog_connect');
    if ($mra_import_psql_hide_out_of_catalog_connect == 'on')
        $checked = 'checked';
    else
        $checked = '';

    echo '<input type="checkbox" id="mra_import_psql_hide_out_of_catalog_connect" name="mra_import_psql_hide_out_of_catalog_connect" '.$checked.'>';
}

function mra_import_psql_cron_time_input()
{

    $mra_import_psql_cron_time = get_option('mra_import_psql_cron_time');
    if(!$mra_import_psql_cron_time)
        $mra_import_psql_cron_time = '';

    echo '<input type="text" id="mra_import_psql_cron_time" name="mra_import_psql_cron_time" value="'.$mra_import_psql_cron_time.'">';
}