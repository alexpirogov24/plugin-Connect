<?php

function add_tags_based_on_pos_outlet($product_id) {
    // // Get the full list of POS outlets from the option
    $pos_outlets = get_option('added_vendor_list');
    if (empty($pos_outlets)) {
        return false; // No POS outlets available
    }

    // Decode the serialized data from the database
    $pos_outlets = maybe_unserialize($pos_outlets);
    if (!is_array($pos_outlets)) {
        return false; // Data format is invalid
    }

    // Get the _wc_pos_outlet_stock meta for the product
    $outlet_stock_meta = get_post_meta($product_id, '_wc_pos_outlet_stock', true);
    if (empty($outlet_stock_meta)) {
        return false; // No outlet stock meta available
    }

    // Deserialize the stock meta to get outlet IDs
    $outlet_stock = maybe_unserialize($outlet_stock_meta);
    if (!is_array($outlet_stock)) {
        return false; // Data format is invalid
    }

    // Retrieve existing tags for the product
    $existing_tags = wp_get_post_terms($product_id, 'product_tag', ['fields' => 'names']);
    $existing_tags = is_array($existing_tags) ? $existing_tags : [];

    $new_tags = []; // To collect new tags to be added
    $tags_added = false; // Track if any tags are added

    // Loop through POS outlets and match with stock
    foreach ($pos_outlets as $outlet) {
        if (!empty($outlet['wpid']) && isset($outlet_stock[$outlet['wpid']])) {
            // Check if the tag (outlet name) already exists
            if (!in_array($outlet['name'], $existing_tags)) {
                $new_tags[] = $outlet['name']; // Add new tag to the list
                $tags_added = true; // Set the flag to true
            }
        }
    }

    // If there are new tags, add them to the product
    if (!empty($new_tags)) {
        wp_set_post_terms($product_id, $new_tags, 'product_tag', true); // Append tags
    }

    return $tags_added; // Return true if at least one tag was added, false otherwise
}


function check_product_attributes($product_id) {
    // Получаем объект продукта по ID
    $product = wc_get_product($product_id);
    if (!$product) {
        return false; // Продукт не найден
    }

    // Получаем все атрибуты продукта
    $attributes = $product->get_attributes();
    
    // Проверка на наличие атрибутов
    if (empty($attributes)) {
        return false;
    }

    // Флаг для проверки обязательного атрибута pa_upc
    $has_upc = false;

    // Проверка каждого атрибута
    foreach ($attributes as $attribute) {
        // Пропускаем, если атрибут не заполнен
        if (!$attribute->get_options()) {
            return false;
        }

        // Проверяем наличие атрибута pa_upc
        if ($attribute->get_name() === 'pa_upc') {
            $has_upc = true;
            // Проверка, что значение атрибута pa_upc не пустое
            if (empty($attribute->get_options())) {
                return false;
            }
        }
    }

    // Возвращаем false, если pa_upc не найден
    return $has_upc;
}

function save_wc_custom_attributes1111($post_id, $custom_attributes) {
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
}

function get_single_pos_outlet_id_by_title( $title ) {
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

    return $result;
}

function mra_check_upc_attribute( $product_id ) {
    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        return false;
    }
    $upc = $product->get_attribute("pa_upc");
    return ! empty( $upc );
}

function mra_check_and_add_tag( $product_id ) {
    $tag_name = 'personal product';

    $current_tags = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'names' ) );
    if ( in_array( $tag_name, $current_tags, true ) ) {
        return false;
    }

    $tag = term_exists( $tag_name, 'product_tag' );
    if ( ! $tag ) {
        $tag = wp_insert_term( $tag_name, 'product_tag' );
    }

    if ( ! is_wp_error( $tag ) ) {
        wp_set_post_terms( $product_id, $tag_name, 'product_tag', true );
        return true;
    }

    return false;
}

function is_personal_product($product_id) {

    // Check by name
    if (has_term('personal product', 'product_tag', $product_id)) {
        return true;
    }

    // Check by slug
    if (has_term('personal-product', 'product_tag', $product_id)) {
        return true;
    }

    // If neither match
    return false;
}

function check_and_update_product_status($product_id) {
    if (!function_exists('wc_get_product')) {
        return false;
    }
    
    $product = wc_get_product($product_id);
    if (!$product) {
        return false;
    }

    if (is_personal_product($product_id)) {
        return false;
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
        return 'draft';
    } elseif (($current_status === 'draft' && $price > 0)) {
        wp_update_post([
            'ID' => $product_id,
            'post_status' => 'publish'
        ]);
        return 'publish';
    }

    return false;
}

function filter_outlets_by_pos($outlets) {
    foreach ($outlets as $key => $outlet) {
        if (!pos_outlet_exists($outlet['wpid'])) {
            unset($outlets[$key]);
        }
    }
    return array_values($outlets);
}

function pos_outlet_exists($wpid) {
    return get_post_status($wpid) !== false;
}

function has_duplicate_names($outlets) {
    $names = array_column($outlets, 'name');
    return count($names) !== count(array_unique($names));
}