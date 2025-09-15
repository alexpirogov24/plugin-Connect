<?php
// no outside access
if (!defined('WPINC')) die('No access outside of wordpress.');

if (
    !is_plugin_active('woocommerce-point-of-sale/woocommerce-point-of-sale.php') &&
    !is_plugin_active('woocommerce-point-of-sale-modification/woocommerce-point-of-sale-modification.php')
) {

    add_action('admin_footer', function () {
        global $post;

        // Проверяем, что мы на странице редактирования продукта
        if (!$post || 'product' !== $post->post_type) {
            return;
        }

        $mra_import_psql_enable_rsr = get_option('mra_import_psql_enable_rsr');
        $post_id_excluded_rsr = get_single_pos_outlet_id_by_title( 'rsr' );

        // Получаем данные из мета полей
        $stock_data = get_post_meta($post->ID, '_wc_pos_outlet_stock', true);
        $cost_data = get_post_meta($post->ID, '_wc_pos_outlet_cost', true);    

        // Преобразуем сериализованные данные в массив
        $stock_data = maybe_unserialize($stock_data) ?: [];
        $cost_data = maybe_unserialize($cost_data) ?: [];

        $added_vendor_list = unserialize( get_option('added_vendor_list') );

        if($mra_import_psql_enable_rsr != 'on') {
            foreach ($added_vendor_list as $key => $value) {            
                if($value['dbname']=='rsr') {
                    unset($added_vendor_list[$key]);
                    break;
                }            
            }
            unset($stock_data[$post_id_excluded_rsr]);
        }

        // var_dump($stock_data);
        // var_dump($cost_data);

        // var_dump($added_vendor_list);

        // Подготовка данных для JavaScript
        // $inventory_data = [
        //     'stock' => $stock_data,
        //     'cost' => $cost_data,
        //     'outlets' => ['outlet1', 'outlet2', 'outlet3'],
        //     'nonce' => wp_create_nonce('save_outlet_inventory'),
        // ];

        // Код JavaScript
        ?>
        <style type="text/css">
        	.block_mra_product_outlets {
        		padding: 10px 0 20px;
        	}
        	.block_mra_product_outlets h3 {
        		padding: 10px 0;
    		    margin: 0 15px;
    		    border-bottom: 1px solid #cccccc;
        	}
        	.block_mra_product_outlets table {
        		margin: 15px;
        	}
        	.block_mra_product_outlets table th {
        		padding: 9px 10px 11px 0;
        	}
        	.block_mra_product_outlets table td {
        		padding: 6px 8px;
        	}
            .block_mra_product_outlets button.add-outlet {
                float: right;
                margin: 10px 4% 15px;
            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {

                $(window).on('load', function () {
                    // Calculate the total stock
                    let totalStock = 0;

                    // Iterate through all inputs with names starting with 'outlet_stock'
                    $("input[name^='outlet_stock']").each(function () {
                        const stockValue = parseInt($(this).val(), 10); // Parse the value as an integer
                        if (!isNaN(stockValue)) {
                            totalStock += stockValue; // Add to total stock if it's a valid number
                        }
                    });

                    // Set the total stock value in the input with class 'wc_input_stock'
                    $(".wc_input_stock").val(totalStock);
                    console.log("Total stock calculated and updated:", totalStock); // Debugging log
                });

                // Создаем таблицу
                let tableHTML = `
                <div class="block_mra_product_outlets">
                    <h3>Mra outlets:</h3>
                    <table class="form-table" id="mra-outlet-table">
                        <thead>
                            <tr>
                                <th>Title Outlet</th>
                                <th>Stock</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                <?php foreach ($stock_data as $key => $stock) {
                    if (is_pos_outlet_post($key)) { ?>
                    tableHTML += `
                        <tr data-outlet-id="<?= $key ?>">
                            <td>
                                <select name="outlet_title[<?= $key ?>]">
                                    <?php $id_p_i = get_single_pos_outlet_id_by_title("personal inventory");
                                    $isset_p_i = false;
                                    foreach ($added_vendor_list as $vendor) { ?>
                                    <option value="<?= $vendor['wpid'] ?>" <?php if($key==$vendor['wpid']) echo 'selected'; ?>>
                                        <?= $vendor['name'] ?>
                                    </option>
                                    <?php if($id_p_i == $vendor['wpid']) $isset_p_i = true; ?>
                                    <?php } ?>

                                    <?php if($isset_p_i==false) { ?>
                                    <option value="<?= $id_p_i ?>" <?php if($key==$id_p_i) echo 'selected'; ?>>personal inventory</option>
                                    <?php } ?>

                                </select>
                            </td>
                            <td><input type="text" name="outlet_stock[<?= $key ?>]" value="<?= $stock ?>" /></td>
                            <?php if (isset($cost_data[$key])) { ?>
                            <td><input type="text" name="outlet_cost[<?= $key ?>]" value="<?= $cost_data[$key] ?>" /></td>
                            <?php } else { ?>
                            <td><input type="text" name="outlet_cost[<?= $key ?>]" value="" /></td>
                            <?php } ?>
                            <td><button type="button" class="remove-outlet button">Remove</button></td>
                        </tr>
                    `;
                    <?php }
                } ?>

                tableHTML += `
                        </tbody>
                    </table>
                    <button type="button" class="add-outlet button">Add New</button>
                </div>`;

                // Добавляем таблицу внутрь блока #inventory_product_data
                $('#inventory_product_data').append(tableHTML);

                // Обработчик для добавления новой строки
                $('.add-outlet').on('click', function () {
                    const newRowHTML = `
                    <tr>
                        <?php $id_p_i = get_single_pos_outlet_id_by_title("personal inventory"); ?>
                        <td>
                            <select name="outlet_title[<?= $id_p_i ?>]">
                                <?php foreach ($added_vendor_list as $vendor) { ?>
                                <option value="<?= $vendor['wpid'] ?>"><?= $vendor['name'] ?></option>
                                <?php } ?>
                                <option value="<?= $id_p_i ?>">personal inventory</option>
                            </select>
                        </td>
                        <td><input type="text" name="outlet_stock[<?= $id_p_i ?>]" value="" /></td>
                        <td><input type="text" name="outlet_cost[<?= $id_p_i ?>]" value="" /></td>
                        <td><button type="button" class="remove-outlet button">Remove</button></td>
                    </tr>`;
                    $('#mra-outlet-table tbody').append(newRowHTML);
                });

                // Обработчик для удаления строки
                $(document).on('click', '.remove-outlet', function () {
                    $(this).closest('tr').remove();
                });
            });
        </script>
        <?php
    });

    // Сохраняем данные из таблицы
    /* add_action('woocommerce_process_product_meta', function ($post_id) {
        // Проверяем nonce
        if (!isset($_POST['outlet_inventory_nonce']) || !wp_verify_nonce($_POST['outlet_inventory_nonce'], 'save_outlet_inventory')) {
            return;
        }

        // Получаем данные из POST
        $outlet_stock = isset($_POST['outlet_stock']) ? array_map('sanitize_text_field', $_POST['outlet_stock']) : [];
        $outlet_cost = isset($_POST['outlet_cost']) ? array_map('sanitize_text_field', $_POST['outlet_cost']) : [];

        // Сохраняем в мета поля
        update_post_meta($post_id, '_wc_pos_outlet_stock', maybe_serialize($outlet_stock));
        update_post_meta($post_id, '_wc_pos_outlet_cost', maybe_serialize($outlet_cost));
    });
    */

    
    add_action('save_post', function ($post_id) {
        // Проверяем тип поста
        if (get_post_type($post_id) !== 'product') {
            return;
        }

        // Проверяем, отправлена ли форма
        if (!isset($_POST['outlet_stock'], $_POST['outlet_cost'])) {
            return;
        }

        $stocks = $_POST['outlet_stock'];
        $costs = $_POST['outlet_cost'];

        // Инициализируем массивы для сохранения
        $stock_data = [];
        $cost_data = [];

        // Формируем данные для мета полей
        foreach ($stocks as $key => $stock) {
            $stock_value = isset($stock) ? sanitize_text_field($stock) : '';
            $cost_value = isset($costs[$key]) ? sanitize_text_field($costs[$key]) : '';

            $stock_data[$key] = $stock_value;
            $cost_data[$key] = $cost_value;
        }

        // Сохраняем данные в мета поля
        update_post_meta($post_id, '_wc_pos_outlet_stock', maybe_serialize($stock_data));
        update_post_meta($post_id, '_wc_pos_outlet_cost', maybe_serialize($cost_data));
    });

} else {
    error_log('One of the “woocommerce-point-of-sale” and “woocommerce-point-of-sale-modification” plugins is active and the Connect plugin cannot work correctly.');
}
