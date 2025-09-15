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
            #markup_rules_table select.rule-name {
                max-width: 100%;
            }
            #exclusions_table select.ex-name {
                max-width: 100%;
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

                /* $markup_rules_json = get_option('mra_import_psql_custom_markup_rules');
                $exclusions_json = get_option('mra_import_psql_markup_exclusions');

                $markup_rules = json_decode($markup_rules_json, true);
                $exclusions = json_decode($exclusions_json, true);
                ?>

                <h2>Custom Markup Rules</h2>
                <table class="wp-list-table widefat fixed striped" id="markup_rules_table">
                    <thead>
                        <tr>
                            <th>Type</th><th>Name</th><th>Mode</th><th>Value</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($markup_rules) && is_array($markup_rules)): ?>
                            <?php foreach ($markup_rules as $rule): ?>
                                <tr>
                                    <td><?= esc_html($rule['type']) ?></td>
                                    <td><?= esc_html($rule['name']) ?></td>
                                    <td><?= esc_html($rule['mode']) ?></td>
                                    <td><?= esc_html($rule['value']) ?></td>
                                    <td><button type="button" class="button remove-rule">Remove</button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button type="button" class="button" id="add_rule_btn">Add Rule</button>
                <input type="hidden" name="mra_import_psql_custom_markup_rules" id="mra_import_psql_custom_markup_rules" value="<?= esc_attr($markup_rules_json) ?>">

                <h2>Exclusions</h2>
                <table class="wp-list-table widefat fixed striped" id="exclusions_table">
                    <thead>
                        <tr>
                            <th>Type</th><th>Name</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($exclusions) && is_array($exclusions)): ?>
                            <?php foreach ($exclusions as $excl): ?>
                                <tr>
                                    <td><?= esc_html($excl['type']) ?></td>
                                    <td><?= esc_html($excl['name']) ?></td>
                                    <td><button type="button" class="button remove-exclusion">Remove</button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button type="button" class="button" id="add_exclusion_btn">Add Exclusion</button>
                <input type="hidden" name="mra_import_psql_markup_exclusions" id="mra_import_psql_markup_exclusions" value="<?= esc_attr($exclusions_json) ?>"> <?php */ ?>

                <br><br>
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

            function renderMarkupRules() {
                const rules = JSON.parse($('#mra_import_psql_custom_markup_rules').val() || '[]');
                const $rules = $('#markup_rules_table tbody').empty();

                rules.forEach((rule, index) => {
                    const options = (rule.type === 'brand' ? mra_import_psql_data.brands : mra_import_psql_data.categories)
                        .map(name => `<option value="${name}" ${rule.name === name ? 'selected' : ''}>${name}</option>`).join('');

                    $rules.append(`
                        <tr data-index="${index}">
                            <td>
                                <select class="rule-type">
                                    <option value="brand"${rule.type === 'brand' ? ' selected' : ''}>Brand</option>
                                    <option value="category"${rule.type === 'category' ? ' selected' : ''}>Category</option>
                                </select>
                            </td>
                            <td>
                                <select class="rule-name">${options}</select>
                            </td>
                            <td>
                                <select class="rule-mode">
                                    <option value="percent"${rule.mode === 'percent' ? ' selected' : ''}>%</option>
                                    <option value="value"${rule.mode === 'value' ? ' selected' : ''}>$</option>
                                </select>
                            </td>
                            <td><input type="text" class="rule-value" value="${rule.value || ''}"></td>
                            <td><button type="button" class="button remove-rule">Remove</button></td>
                        </tr>
                    `);
                });

                updateMarkupRulesInput();
            }

            function renderExclusions() {
                const exclusions = JSON.parse($('#mra_import_psql_markup_exclusions').val() || '[]');
                const $ex = $('#exclusions_table tbody').empty();

                exclusions.forEach((ex, index) => {
                    const options = (ex.type === 'brand' ? mra_import_psql_data.brands : mra_import_psql_data.categories)
                        .map(name => `<option value="${name}" ${ex.name === name ? 'selected' : ''}>${name}</option>`).join('');

                    $ex.append(`
                        <tr data-index="${index}">
                            <td>
                                <select class="ex-type">
                                    <option value="brand"${ex.type === 'brand' ? ' selected' : ''}>Brand</option>
                                    <option value="category"${ex.type === 'category' ? ' selected' : ''}>Category</option>
                                </select>
                            </td>
                            <td>
                                <select class="ex-name">${options}</select>
                            </td>
                            <td><button type="button" class="button remove-ex">Remove</button></td>
                        </tr>
                    `);
                });

                updateExclusionsInput();
            }

            function updateMarkupRulesInput() {
                const rules = [];
                $('#markup_rules_table tbody tr').each(function () {
                    rules.push({
                        type: $(this).find('.rule-type').val(),
                        name: $(this).find('.rule-name').val(),
                        mode: $(this).find('.rule-mode').val(),
                        value: $(this).find('.rule-value').val(),
                    });
                });
                $('#mra_import_psql_custom_markup_rules').val(JSON.stringify(rules));
            }

            function updateExclusionsInput() {
                const exclusions = [];
                $('#exclusions_table tbody tr').each(function () {
                    exclusions.push({
                        type: $(this).find('.ex-type').val(),
                        name: $(this).find('.ex-name').val(),
                    });
                });
                $('#mra_import_psql_markup_exclusions').val(JSON.stringify(exclusions));
            }

            // Add buttons
            $('#add_rule_btn').on('click', function () {
                const rules = JSON.parse($('#mra_import_psql_custom_markup_rules').val() || '[]');
                rules.push({ type: 'brand', name: '', mode: 'percent', value: '' });
                $('#mra_import_psql_custom_markup_rules').val(JSON.stringify(rules));
                renderMarkupRules();
            });

            $('#add_exclusion_btn').on('click', function () {
                const exclusions = JSON.parse($('#mra_import_psql_markup_exclusions').val() || '[]');
                exclusions.push({ type: 'brand', name: '' });
                $('#mra_import_psql_markup_exclusions').val(JSON.stringify(exclusions));
                renderExclusions();
            });

            // Input listeners
            $(document).on('change input', '.rule-type, .rule-name, .rule-mode, .rule-value', updateMarkupRulesInput);
            $(document).on('change input', '.ex-type, .ex-name', updateExclusionsInput);

            // Remove buttons
            $(document).on('click', '.remove-rule', function () {
                const index = $(this).closest('tr').data('index');
                const rules = JSON.parse($('#mra_import_psql_custom_markup_rules').val());
                rules.splice(index, 1);
                $('#mra_import_psql_custom_markup_rules').val(JSON.stringify(rules));
                renderMarkupRules();
            });

            $(document).on('click', '.remove-ex', function () {
                const index = $(this).closest('tr').data('index');
                const exclusions = JSON.parse($('#mra_import_psql_markup_exclusions').val());
                exclusions.splice(index, 1);
                $('#mra_import_psql_markup_exclusions').val(JSON.stringify(exclusions));
                renderExclusions();
            });

            renderMarkupRules();

            // Обновление списка названий при смене типа
            function updateRuleNameOptions($row, type, selectedName) {
                const list = type === 'brand' ? mra_import_psql_data.brands : mra_import_psql_data.categories;
                const options = list.map(name => `<option value="${name}" ${name === selectedName ? 'selected' : ''}>${name}</option>`).join('');
                $row.find('.rule-name').html(options);
            }

            function updateExclusionNameOptions($row, type, selectedName) {
                const list = type === 'brand' ? mra_import_psql_data.brands : mra_import_psql_data.categories;
                const options = list.map(name => `<option value="${name}" ${name === selectedName ? 'selected' : ''}>${name}</option>`).join('');
                $row.find('.ex-name').html(options);
            }

            $(document).on('change', '.rule-type', function () {
                const $row = $(this).closest('tr');
                const type = $(this).val();
                updateRuleNameOptions($row, type, '');
                updateMarkupRulesInput();
            });

            $(document).on('change', '.ex-type', function () {
                const $row = $(this).closest('tr');
                const type = $(this).val();
                updateExclusionNameOptions($row, type, '');
                updateExclusionsInput();
            });

            renderExclusions();


             $('form').on('submit', function () {
                updateMarkupRulesInput();
                updateExclusionsInput();
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

    register_setting(
        'mra_import_psql_price_edit_fields',
        'mra_import_psql_custom_markup_rules',
        ['type' => 'string']
    );

    register_setting(
        'mra_import_psql_price_edit_fields',
        'mra_import_psql_markup_exclusions',
        ['type' => 'string']
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
        'Hide out-of-stock items from the frontend catalog',
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





function mra_import_get_all_brands() {
    $brands = get_terms(['taxonomy' => 'pa_brand', 'hide_empty' => false]);
    return array_map(function($term) {
        return $term->name;
    }, $brands);
}

function mra_import_get_all_categories() {
    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
    return array_map(function($term) {
        return $term->name;
    }, $categories);
}

add_action('admin_enqueue_scripts', function() {
    wp_localize_script('jquery', 'mra_import_psql_data', [
        'brands' => mra_import_get_all_brands(),
        'categories' => mra_import_get_all_categories()
    ]);
});