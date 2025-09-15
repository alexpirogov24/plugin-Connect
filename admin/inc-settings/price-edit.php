<?php
function mra_import_psql_price_edit_func() {

    if (isset($_GET['price_edit']) && $_GET['price_edit']=='yes') {
        include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/update-price.php");
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
            <h1>Markup Settings</h1>
            <?php $mra_import_psql_select = get_option('mra_import_psql_select'); 
            if ($mra_import_psql_select) {
                $class = '';
                $display = 'none';
            } else {
                $class = 'no_active';
                $display = 'block';
            } ?>
            <a id="update_price_btn" class="<?= $class; ?>" href="/wp-admin/admin.php?page=mra_import_psql_price_edit&price_edit=yes">Update price</a>
            <p class="update_price_btn_text" style="display: <?= $display; ?>;">For the button to be active, you must set the values and click Save Changes.</p>
            <form action='options.php' method='post'>
                <?php
                settings_fields('mra_import_psql_price_edit_fields');
                do_settings_sections('mra_import_psql_price_edit_options');
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
        } ?>

        <script type="text/javascript">
        jQuery(document).ready( function( $ ){
            $("input[type='radio']" ).change(function() {
                $('#update_price_btn').attr('class', 'no_active');
                $('.update_price_btn_text').show();
            });
            $('.mra_import_psql_input').on('input keyup', function(e) {
                $('#update_price_btn').attr('class', 'no_active');
                $('.update_price_btn_text').show();
            });
        });
        </script>

        <?php

        if(!$mysqli_read->connect_error)
            $mysqli_read->close();
        if(!$mysqli_read_write->connect_error)
            $mysqli_read_write->close();

    }

}

add_action('admin_init', 'mra_import_psql_price_edit_init');
function mra_import_psql_price_edit_init()
{
    add_settings_section(
        'default',
        '',
        '',
        'mra_import_psql_price_edit_options'
    );

    register_setting(
        'mra_import_psql_price_edit_fields',
        'mra_import_psql_enable_auto_price_edit',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_price_edit_fields',
        'mra_import_psql_select',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_price_edit_fields',
        'mra_import_psql_percent',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_price_edit_fields',
        'mra_import_psql_dollar',
        array(
            'type' => 'string',
        )
    );

    register_setting(
        'mra_import_psql_price_edit_fields',
        'mra_import_psql_hide_out_of_stock',
        array(
            'type' => 'string',
        )
    );


    add_settings_field(
        'mra_import_psql_enable_auto_price_edit',
        'Enable automatic price update',
        'mra_import_psql_enable_auto_price_edit_input',
        'mra_import_psql_price_edit_options',
    );

    add_settings_field(
        'mra_import_psql_select',
        'Select price edit',
        'mra_import_psql_select_func',
        'mra_import_psql_price_edit_options',
    );

    add_settings_field(
        'mra_import_psql_percent',
        'Markup Percent (%)',
        'mra_import_psql_percent_input',
        'mra_import_psql_price_edit_options',
    );

    add_settings_field(
        'mra_import_psql_dollar',
        'Markup Values ($)',
        'mra_import_psql_dollar_input',
        'mra_import_psql_price_edit_options',
    );

    add_settings_field(
        'mra_import_psql_hide_out_of_stock',
        'Hide out of stock items from the catalog',
        'mra_import_psql_hide_out_of_stock_input',
        'mra_import_psql_price_edit_options',
    );
}

function mra_import_psql_enable_auto_price_edit_input()
{
    $mra_import_psql_enable_auto_price_edit = get_option('mra_import_psql_enable_auto_price_edit');
    if ($mra_import_psql_enable_auto_price_edit == 'on')
        $checked = 'checked';
    else
        $checked = '';

    echo '<input type="checkbox" id="mra_import_psql_enable_auto_price_edit" name="mra_import_psql_enable_auto_price_edit" '.$checked.'>';
}

function mra_import_psql_select_func()
{

    $mra_import_psql_select = get_option('mra_import_psql_select');
    if(!$mra_import_psql_select){
        $noextracharge = '';
        $percent = '';
        $value = '';
        $map = '';
    }

    if($mra_import_psql_select=="noextracharge") $noextracharge = 'checked'; else $noextracharge = '';
    if($mra_import_psql_select=="percent") $percent = 'checked'; else $percent = '';
    if($mra_import_psql_select=="value") $value = 'checked'; else $value = '';
    if($mra_import_psql_select=="map_percent") $map_percent = 'checked'; else $map_percent = '';
    if($mra_import_psql_select=="map_value") $map_value = 'checked'; else $map_value = '';

    // <!--<div class="radio_select">
    //     <input type="radio" id="mra_import_psql_select1" name="mra_import_psql_select" value="noextracharge" '.$noextracharge.' />
    //     <label for="mra_import_psql_select1">No extra charge</label>
    // </div>-->

    echo '
    <div class="radio_select">
        <input type="radio" id="mra_import_psql_select2" name="mra_import_psql_select" value="percent" '.$percent.' />
        <label for="mra_import_psql_select2">Only a percentage (%)</label>
    </div>
    <div class="radio_select">
        <input type="radio" id="mra_import_psql_select3" name="mra_import_psql_select" value="value" '.$value.' />
        <label for="mra_import_psql_select3">Only value ($)</label>
    </div>
    <div class="radio_select">
        <input type="radio" id="mra_import_psql_select4" name="mra_import_psql_select" value="map_percent" '.$map_percent.' />
        <label for="mra_import_psql_select4">MAP if specified by the product. The default is percent (%). Only triggers when the percent field is full.</label>
    </div>
    <div class="radio_select">
        <input type="radio" id="mra_import_psql_select5" name="mra_import_psql_select" value="map_value" '.$map_value.' />
        <label for="mra_import_psql_select5">MAP if specified by the product. By default, a fixed markup ($) is used. Triggers only when the value field is filled.</label>
    </div>';


    // <select id="mra_import_psql_select" name="mra_import_psql_select">
    //   <option value="noextracharge" '.$noextracharge.'>No extra charge</option>
    //   <option value="percent" '.$percent.'>Markup percent</option>
    //   <option value="value" '.$value.'>Markup value</option>
    //   <option value="map" '.$map.'>Use map</option> 
    // </select>';
}

function mra_import_psql_percent_input()
{

    $mra_import_psql_percent = get_option('mra_import_psql_percent');
    if(!$mra_import_psql_percent)
        $mra_import_psql_percent = ''; 

    echo '<input type="text" id="mra_import_psql_percent" class="mra_import_psql_input" name="mra_import_psql_percent" value="'.$mra_import_psql_percent.'">';
}

function mra_import_psql_dollar_input()
{

    $mra_import_psql_dollar = get_option('mra_import_psql_dollar');
    if(!$mra_import_psql_dollar)
        $mra_import_psql_dollar = '';

    echo '<input type="text" id="mra_import_psql_dollar" class="mra_import_psql_input" name="mra_import_psql_dollar" value="'.$mra_import_psql_dollar.'">';
}


function mra_import_psql_hide_out_of_stock_input()
{

    $mra_import_psql_hide_out_of_stock = get_option('mra_import_psql_hide_out_of_stock');
    if ($mra_import_psql_hide_out_of_stock == 'on')
        $checked = 'checked';
    else
        $checked = '';

    echo '<input type="checkbox" id="mra_import_psql_hide_out_of_stock" name="mra_import_psql_hide_out_of_stock" '.$checked.'>';
}