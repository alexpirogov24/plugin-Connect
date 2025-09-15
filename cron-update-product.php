<?php

// Function to check and update product status
// function check_and_update_product_status($product_id) {

//     // Check if this is a WooCommerce product
//     if (get_post_type($product_id) != 'product') {
//         return false;
//     }

//     // Get the current post status
//     $current_status = get_post_status($product_id);

//     // Get the meta values
//     $outlet_cost = get_post_meta($product_id, '_wc_pos_outlet_cost', true);
//     $outlet_stock = get_post_meta($product_id, '_wc_pos_outlet_stock', true);

//     // If both values are empty or do not exist, set the product to draft
//     if (empty($outlet_cost) && empty($outlet_stock)) {
//         if ($current_status != 'draft') {
//             $product_data = array(
//                 'ID' => $product_id,
//                 'post_status' => 'draft'
//             );
//             wp_update_post($product_data);
//             return 'draft';
//         }
//         return false;
//     }

//     // If the product is in draft status but both meta values are filled, set the product to publish
//     if ($current_status == 'draft' && !empty($outlet_cost) && !empty($outlet_stock)) {
//         $product_data = array(
//             'ID' => $product_id,
//             'post_status' => 'publish'
//         );
//         wp_update_post($product_data);
//         return 'publish';
//     }

//     return false;
// }

// wp_unschedule_hook( 'cron_mra_psql_main_interval_event_cheat' ); wp_unschedule_hook( 'cron_mra_psql_event' ); /*

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

add_filter( 'cron_schedules', 'cron_mra_psql_main_interval_func' );
function cron_mra_psql_main_interval_func( $schedules ) {
	$mra_import_psql_cron_time = get_option('mra_import_psql_cron_time');
    if(!$mra_import_psql_cron_time)
        $mra_import_psql_cron_time = 30;
    
	$minutes = $mra_import_psql_cron_time;
	$schedules['mra_psql_main_interval'] = array(
		'interval' => 60 * $minutes,
		'display' => 'Every '.$minutes.' minutes'
	);
	return $schedules;
}

add_filter( 'cron_schedules', 'cron_mra_psql_interim_interval_func' );
function cron_mra_psql_interim_interval_func( $schedules ) {

	$minutes = 3;
	$schedules['mra_psql_interim_interval'] = array(
		'interval' => 60 * $minutes,
		'display' => 'Every '.$minutes.' minutes'
	);
	return $schedules;
}

// add_action( 'wp', 'mra_psql_main_activation' );
// function mra_psql_main_activation() {
// 	if ( ! wp_next_scheduled( 'cron_mra_psql_main_event' ) ) {
// 		wp_schedule_event( time(), 'mra_psql_main_interval', 'cron_mra_psql_main_event' );		
// 	}
// }

// add_action( 'cron_mra_psql_main_event', 'cron_mra_psql_main_event_func' );
// function cron_mra_psql_main_event_func(){

// }

add_action( 'wp', 'mra_psql_activation' );
function mra_psql_activation() {

	if ( ! wp_next_scheduled( 'cron_mra_psql_main_interval_event_cheat' ) ) {
		wp_schedule_event( time(), 'mra_psql_main_interval', 'cron_mra_psql_main_interval_event_cheat' );
	}

}

add_action( 'cron_mra_psql_main_interval_event_cheat', 'mra_psql_main_interval_activation' );
function mra_psql_main_interval_activation() {
	if ( ! wp_next_scheduled( 'cron_mra_psql_event' ) ) {

		$date_text = date("d_m_y");
		if(!file_exists(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt"))
			file_put_contents(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt", '');
		$log_file = fopen(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt", "a");
		$text_log = date("Y-m-d H:i:s").": RUNNING THE MAIN CRON. \r\n";
		$result_fwrite = fwrite($log_file, $text_log);
		fclose($log_file);

		wp_schedule_event( time(), 'mra_psql_interim_interval', 'cron_mra_psql_event' );
	}
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


add_action( 'cron_mra_psql_event', 'cron_mra_psql_event_func' );
function cron_mra_psql_event_func(){

    
	// delete_option('cron_update_product_data');

	$mysqli_read_product = mra_import_psql_db_connection_read();
	$mysqli_read_write_product = mra_import_psql_db_connection_read_write();

	$date_text = date("d_m_y");
	if(!file_exists(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt"))
		file_put_contents(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt", '');
	$log_file = fopen(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt", "a");

	$text_log = date("Y-m-d H:i:s").": Running an INTERMEDIATE CRON.\r\n";
	$result_fwrite = fwrite($log_file, $text_log);

	$cron_update_product_data = array();
	if (get_option('cron_update_product_data')) {
		$cron_update_product_data = unserialize( get_option('cron_update_product_data') );
	} else {
		$cron_update_product_data['status'] = 'active';
		$cron_update_product_data['number'] = 500;
		$cron_update_product_data['offset'] = 0;
		$cron_update_product_data['count_items'] = 0;
        add_option( 'cron_update_product_data', serialize($cron_update_product_data));

        if ($cron_update_product_data['offset']==0) {
			$text_log = "------------- start --------------- \r\n";
			$text_log .= date("Y-m-d H:i:s").": The cron is running. The process of checking the quantity and value of goods has started. \r\n";
			$result_fwrite = fwrite($log_file, $text_log);
		}
	}

	// $text_log = date("Y-m-d H:i:s").": Offset products:".$cron_update_product_data['offset'].". Count items:".$cron_update_product_data['count_items']." \r\n";
	// $result_fwrite = fwrite($log_file, $text_log);

	

	try{ 

		$check_added_vendor_list = unserialize(get_option('added_vendor_list'));
		$check_list_vendors_connect = unserialize(get_option('list_vendors_connect'));

		// $text_log = date("Y-m-d H:i:s").": 010101 YES. \r\n";
		// $result_fwrite = fwrite($log_file, $text_log);



        $start_time111 = microtime(true);
		if(count($check_added_vendor_list) === 0) {
			// $text_log = date("Y-m-d H:i:s").": 111111 YES.\r\n";
			// $result_fwrite = fwrite($log_file, $text_log);
			if(!empty($check_list_vendors_connect)) {
				// $text_log = date("Y-m-d H:i:s").": 222222 YES.\r\n";
				// $result_fwrite = fwrite($log_file, $text_log);
				$i = 0;
				foreach ($check_list_vendors_connect as $key => $value) {
					$check_added_vendor_list[$i]['dbname'] = $value;
					$check_added_vendor_list[$i]['name'] = $value;
					$check_added_vendor_list[$i]['wpid'] = get_single_pos_outlet_id_by_title($value);
					$result_vendor = $mysqli_read_product->query("SELECT * FROM vendors WHERE vendor='".$value."'");
		    		$row_vendor = $result_vendor->fetch_assoc();
		    		$check_added_vendor_list[$i]['dbid'] = $row_vendor['id'];	    		
				$i++; }

				// $text_log = date("Y-m-d H:i:s").": qqqwwweee1111222333.\r\n";
				// $text_log .= date("Y-m-d H:i:s").": ".print_r($check_added_vendor_list, true)." \r\n";
				// $result_fwrite = fwrite($log_file, $text_log);
				

				$serialize_check_added_vendor_list = serialize($check_added_vendor_list);
	            $result_added_vendor = update_option( 'added_vendor_list', $serialize_check_added_vendor_list );
			}
		}
        $end_time111 = microtime(true);
        $execution_time111 = $end_time111 - $start_time111;
        // $text_log .= date("Y-m-d H:i:s").": Block execution time 111 {$execution_time111} seconds \r\n";
        // $result_fwrite = fwrite($log_file, $text_log);

		if (has_duplicate_names($check_added_vendor_list)) {
		    $check_added_vendor_list = filter_outlets_by_pos($check_added_vendor_list);
		    $serialize_check_added_vendor_list = serialize($check_added_vendor_list);
            $result_added_vendor = update_option( 'added_vendor_list', $serialize_check_added_vendor_list );

		    $text_log .= date("Y-m-d H:i:s").": The added_vendor_list array has been updated and duplicates have been removed from it. \r\n";
			$result_fwrite = fwrite($log_file, $text_log);
		}

		// $result_fwrite = fwrite($log_file, $text_log);

		global $wpdb;
		// $query = "
		//     SELECT COUNT(*) 
		//     FROM {$wpdb->posts} AS p 
		//     INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
		//     INNER JOIN {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id
		//     INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
		//     INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
		//     WHERE p.post_type = 'product' 
		//     AND tt.taxonomy = 'pa_upc' 
		//     AND pm.meta_key = '_product_attributes'
		//     AND pm.meta_value LIKE '%pa_upc%'
		// ";
		// $cron_update_product_data['count_items'] = $wpdb->get_var($query);


        $start_time222 = microtime(true);
		$query = "
		    SELECT COUNT(*) 
		    FROM {$wpdb->posts} AS p
		    WHERE p.post_type = 'product'
		";
		$cron_update_product_data['count_items'] = $wpdb->get_var($query);


		// $text_log = date("Y-m-d H:i:s").": count1111 ".print_r($cron_update_product_data['count_items'], true)." \r\n";
		// $result_fwrite = fwrite($log_file, $text_log);


		// $query = "
		//     SELECT p.ID, p.post_title
		//     FROM {$wpdb->posts} AS p 
		//     INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
		//     INNER JOIN {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id
		//     INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
		//     INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
		//     WHERE p.post_type = 'product' 
		//     AND tt.taxonomy = 'pa_upc' 
		//     AND pm.meta_key = '_product_attributes'
		//     AND pm.meta_value LIKE '%pa_upc%'
		//     ORDER BY p.ID ASC
		// 	LIMIT ".$cron_update_product_data['number']." OFFSET ".$cron_update_product_data['offset']."
		// ";

		$query = "
		    SELECT p.ID, p.post_title, pm.meta_value AS sku
		    FROM {$wpdb->posts} AS p
		    LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id AND pm.meta_key = '_sku'
		    WHERE p.post_type = 'product'
		    ORDER BY p.ID ASC
		    LIMIT {$cron_update_product_data['number']} OFFSET {$cron_update_product_data['offset']}
		";

		$products = $wpdb->get_results($query);

        $end_time222 = microtime(true);
        $execution_time222 = $end_time222 - $start_time222;
        // $text_log .= date("Y-m-d H:i:s").": Block execution time 222 {$execution_time222} seconds \r\n";
        // $result_fwrite = fwrite($log_file, $text_log);
		

		// $text_log = date("Y-m-d H:i:s").": Count products: ".count($products)." \r\n";
		// $result_fwrite = fwrite($log_file, $text_log);

		

		// $mysqli_product = mra_import_psql_db_connection();
		// $text_log = date("Y-m-d H:i:s").": ".print_r($mysqli_product, true)." \r\n";
		// $result_fwrite = fwrite($log_file, $text_log);

		$i = $cron_update_product_data['offset'];

		$mra_import_psql_enable_rsr = get_option('mra_import_psql_enable_rsr');
		$mra_import_psql_enable_default_outlet = get_option('mra_import_psql_enable_default_outlet');

		foreach( $products as $key => $product ) {

			if ( mra_check_upc_attribute( $product->ID ) ) {


                $start_time333 = microtime(true);

				$post_id_excluded_rsr = get_single_pos_outlet_id_by_title( 'rsr' );
				$post_id_excluded_default_outlet = get_single_pos_outlet_id_by_title( 'Default Outlet' );
				$wc_pos_outlet_stock = get_post_meta( $product->ID, '_wc_pos_outlet_stock', true );
				$wc_pos_outlet_cost = get_post_meta( $product->ID, '_wc_pos_outlet_cost', true );
			
				if (is_array($wc_pos_outlet_stock)) {
					$array_wc_pos_outlet_stock_old = $wc_pos_outlet_stock;
			        $array_wc_pos_outlet_stock = $wc_pos_outlet_stock;
			    } else {
			    	$array_wc_pos_outlet_stock_old = unserialize($wc_pos_outlet_stock);
			        $array_wc_pos_outlet_stock = unserialize($wc_pos_outlet_stock);
			    }

			    if (is_array($wc_pos_outlet_cost)) {
			        $array_wc_pos_outlet_cost = $wc_pos_outlet_cost;
			    } else {
			        $array_wc_pos_outlet_cost = unserialize($wc_pos_outlet_cost);
			    }

			    $array_wc_pos_outlet_stock_excluded = array();
				$array_wc_pos_outlet_cost_excluded = array();

				if ($mra_import_psql_enable_rsr != 'on' && has_term('ffl_firearm', 'product_tag', $product->ID)) {
					if (is_array($array_wc_pos_outlet_stock)) {    
					    if (isset($array_wc_pos_outlet_stock[$post_id_excluded_rsr])) {
					        $array_wc_pos_outlet_stock_excluded[$post_id_excluded_rsr] = $array_wc_pos_outlet_stock[$post_id_excluded_rsr];
					        unset($array_wc_pos_outlet_stock[$post_id_excluded_rsr]);
					    }
					}
				    
				    if (is_array($array_wc_pos_outlet_cost)) {
					    if (isset($array_wc_pos_outlet_cost[$post_id_excluded_rsr])) {
					        $array_wc_pos_outlet_cost_excluded[$post_id_excluded_rsr] = $array_wc_pos_outlet_cost[$post_id_excluded_rsr];
					        unset($array_wc_pos_outlet_cost[$post_id_excluded_rsr]);
					    }
				    }
			    }

			    if ($mra_import_psql_enable_default_outlet != 'on' && has_term('ffl_firearm', 'product_tag', $product->ID)) {
			    	if (is_array($array_wc_pos_outlet_stock)) {   
					    if (isset($array_wc_pos_outlet_stock[$post_id_excluded_default_outlet])) {
					        $array_wc_pos_outlet_stock_excluded[$post_id_excluded_default_outlet] = $array_wc_pos_outlet_stock[$post_id_excluded_default_outlet];
					        unset($array_wc_pos_outlet_stock[$post_id_excluded_default_outlet]);
					    }
					}
				    
				    if (is_array($array_wc_pos_outlet_cost)) {
					    if (isset($array_wc_pos_outlet_cost[$post_id_excluded_default_outlet])) {
					        $array_wc_pos_outlet_cost_excluded[$post_id_excluded_default_outlet] = $array_wc_pos_outlet_cost[$post_id_excluded_default_outlet];
					        unset($array_wc_pos_outlet_cost[$post_id_excluded_default_outlet]);
					    }
			    	}
			    }

                $end_time333 = microtime(true);
                $execution_time333 = $end_time333 - $start_time333;
                // $text_log .= date("Y-m-d H:i:s").": Block execution time 333 {$execution_time333} seconds \r\n";
                // $result_fwrite = fwrite($log_file, $text_log);

				// $text_log = date("Y-m-d H:i:s").": ".$product->post_title." (".$product->ID.") \r\n";
				// $result_fwrite = fwrite($log_file, $text_log);


				// $text_log = date("Y-m-d H:i:s").": 1111111 \r\n";
				// $result_fwrite = fwrite($log_file, $text_log);

				// $text_log = date("Y-m-d H:i:s").": ".$product->post_title." (".$product->ID.") \r\n";
				// $result_fwrite = fwrite($log_file, $text_log);

				$product_wc = wc_get_product( $product->ID );
				$upc_product = $product_wc->get_attribute("pa_upc");

				// $text_log = date("Y-m-d H:i:s").": qwqwqwqwqwqwqwwq ".print_r($rows_product_vendor, true)." \r\n";
				// $result_fwrite = fwrite($log_file, $text_log);

				// $term_list = wp_get_post_terms( $product->ID, 'pa_upc', array( 'fields' => 'names' ) );
				// $upc_product = $term_list[0];

				$term_list = wp_get_post_terms( $product->ID, 'pa_upc', array( 'fields' => 'names' ) );

				// Проверяем, если 'pa_upc' заполнен
				if ( !empty( $term_list ) && !empty( $term_list[0] ) ) {
				    $upc_product = $term_list[0];
				} else {
				    // Если 'pa_upc' отсутствует или пуст, используем SKU
				    $upc_product = $product->sku;
				}

				$result_product = $mysqli_read_product->query("SELECT * FROM master_catalog WHERE UPC=".$upc_product);
				if($result_product!=false) {

                    $start_time444 = microtime(true);

		    		$row_product = $result_product->fetch_assoc();

		    		$custom_attributes = array();
			        $custom_attributes['pa_upc'] = $upc_product;
			        if(isset($row_product['Manufacturer']) && $row_product['Manufacturer']!='')
			            $custom_attributes['pa_brand'] = $row_product['Manufacturer'];


		    		if ($mra_import_psql_enable_rsr != 'on' && has_term('ffl_firearm', 'product_tag', $product->ID)) {
						$result_vendor = $mysqli_read_product->query("SELECT * FROM inventory_master WHERE upc=".$upc_product." AND vendorid!=1");
					} else {
						$result_vendor = $mysqli_read_product->query("SELECT * FROM inventory_master WHERE upc=".$upc_product);
					}
				    if ($result_vendor) {
				        $rows_product_vendor = $result_vendor->fetch_all(MYSQLI_ASSOC);
				    }

				    if ($mra_import_psql_enable_rsr != 'on' && has_term('ffl_firearm', 'product_tag', $product->ID)) {
				    	$result_cost = $mysqli_read_product->query("SELECT * FROM cost_master WHERE upc=".$upc_product." AND vendorid!=1");
				    } else {
				    	$result_cost = $mysqli_read_product->query("SELECT * FROM cost_master WHERE upc=".$upc_product);
				    }
				    
				    if ($result_cost) {
				        $rows_product_cost = $result_cost->fetch_all(MYSQLI_ASSOC);
				    }

				 //    $text_log = date("Y-m-d H:i:s").": 22222222 \r\n";
				 // //    $text_log .= date("Y-m-d H:i:s").": ".print_r($rows_product_vendor, true)." \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

					// $text_log = date("Y-m-d H:i:s").": ".$product->post_title." (".$product->ID.") \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);



				    // start. Creating two arrays to check which products need to overwrite quantities in which products.
				    $added_vendor_meta_arr_wp = array();
				    $added_vendor_meta_arr_wp = unserialize( get_option('added_vendor_list') );

				 //    $text_log = date("Y-m-d H:i:s").": ".print_r(get_option('added_vendor_list'), true)." \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

				    $db_stock_arr = array();
				    $a = 0;
					foreach ($rows_product_vendor as $key => $value) {
						$vendorid_wp = '';
						foreach ($added_vendor_meta_arr_wp as $k => $val) {
							if ($val['dbid']==$value['vendorid']) {
								$vendorid_wp = $val['dbid'];
								break;
							}
						}
						if ($vendorid_wp != '') {
							$db_stock_arr[$a]['vendorid'] = $vendorid_wp;
							$db_stock_arr[$a]['stock'] = $value['quantity'];
						$a++; }
					}

					// $text_log = date("Y-m-d H:i:s").": 3333333 \r\n";
					// // $text_log .= date("Y-m-d H:i:s").": ".print_r($db_stock_arr, true)." \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

					// $text_log = date("Y-m-d H:i:s").": ".$product->post_title." (".$product->ID.") \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

					$outlet_stock_wp = array();

					// $text_log = date("Y-m-d H:i:s").": ".print_r($array_wc_pos_outlet_stock, true)." \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

					$isset_p_i_stock = array();
					$isset_p_i_stock['isset'] = false;
					if (is_array($array_wc_pos_outlet_stock)) {
						$outlet_stock_wp = $array_wc_pos_outlet_stock;
						if(is_array($outlet_stock_wp)) {
							foreach ($outlet_stock_wp as $key => $value) {
								$outlet = get_post($key);
			    				if ($outlet && isset($outlet->post_name)) {
                                    $slug_outlet = $outlet->post_name;
                                    if ($slug_outlet == 'personal-inventory') {
                                        $isset_p_i_stock['isset'] = true;
                                        $isset_p_i_stock['id'] = $key;
                                        $isset_p_i_stock['stock'] = $value;
                                    }
                                }
							}
						}
					}
					else {
						$outlet_stock_wp = unserialize($array_wc_pos_outlet_stock);
						if(is_array($outlet_stock_wp)) {
							foreach ($outlet_stock_wp as $key => $value) {
								$outlet = get_post($key);
			    				$slug_outlet = $outlet->post_name;
			    				if($slug_outlet=='personal-inventory') {
			    					$isset_p_i_stock['isset'] = true;
			    					$isset_p_i_stock['id'] = $key;
			    					$isset_p_i_stock['stock'] = $value;
			    				}
							}
						}
					}
					// $text_log = date("Y-m-d H:i:s").": ".print_r($outlet_stock_wp, true)." \r\n";
					
					$db_stock_arr_wp = array();
				    $b = 0;
					foreach ($outlet_stock_wp as $key => $value) {
						foreach ($added_vendor_meta_arr_wp as $k => $val) {
							if ($val['wpid']==$key) {
								$vendorid_wp = $val['dbid'];
								break;
							}
						}
						$db_stock_arr_wp[$b]['vendorid'] = $vendorid_wp;
						$db_stock_arr_wp[$b]['stock'] = $value;
					$b++; }

                    $end_time444 = microtime(true);
                    $execution_time444 = $end_time444 - $start_time444;
                    // $text_log .= date("Y-m-d H:i:s").": Block execution time 444 {$execution_time444} seconds \r\n";
                    // $result_fwrite = fwrite($log_file, $text_log);


					// $text_log = date("Y-m-d H:i:s").": 444444 \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

					// $text_log = date("Y-m-d H:i:s").": ".$product->post_title." (".$product->ID.") \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

					// $text_log .= date("Y-m-d H:i:s").": ".print_r($db_stock_arr, true)." \r\n";
					// $text_log .= date("Y-m-d H:i:s").": ".print_r($db_stock_arr_wp, true)." \r\n";
					// if($db_stock_arr != $db_stock_arr_wp) {
					// 	$text_log .= date("Y-m-d H:i:s").": db_stock_arr != db_stock_arr_wp \r\n";
					// } else {
					// 	$text_log .= date("Y-m-d H:i:s").": db_stock_arr == db_stock_arr_wp \r\n";
					// }
					// $result_fwrite = fwrite($log_file, $text_log);

					// end. Creating two arrays to check which products need to overwrite quantities in which products.

                    $start_time555 = microtime(true);

                    // new code 555

                    if ($db_stock_arr != $db_stock_arr_wp) {

                        $outlet_stock = [];
                        $stock = 0;

                        $added_vendor_list_option = get_option('added_vendor_list');
                        $added_vendor_meta_arr = $added_vendor_list_option ? @unserialize($added_vendor_list_option) : [];

                        if (!empty($added_vendor_meta_arr)) {
                            $iss = false;

                            foreach ($rows_product_vendor as $product_vendor_db) {
                                foreach ($added_vendor_meta_arr as $vendor) {
                                    if ($product_vendor_db['vendorid'] == $vendor['dbid']) {
                                        $outlet_stock[$vendor['wpid']] = $product_vendor_db['quantity'];
                                        $stock += $product_vendor_db['quantity'];
                                    } elseif (!empty($isset_p_i_stock['isset']) && !$iss) {
                                        $outlet_stock[$isset_p_i_stock['id']] = $isset_p_i_stock['stock'];
                                        $stock += $isset_p_i_stock['stock'];
                                        $iss = true;
                                    }
                                }
                            }

                            $outlet_stock_serialize = serialize($outlet_stock + $array_wc_pos_outlet_stock_excluded);
                            update_post_meta($product->ID, '_wc_pos_outlet_stock', $outlet_stock_serialize);

                        } else {
                            $stock = $row_product['quantity'];
                        }

                        update_post_meta($product->ID, '_stock', $stock);
                        $stock_status = ($stock > 0) ? 'instock' : 'outofstock';
                        update_post_meta($product->ID, '_stock_status', $stock_status);

                        $text_log = date("Y-m-d H:i:s") . ": Outlets number of product {$product->post_title} ({$product->ID}) updated.\r\n";
                        fwrite($log_file, $text_log);
                    }

                    // end new code 555

                    /* old code 555
					// If a product is found in which the quantity is different, the code for overwriting the quantity data follows.
					if($db_stock_arr != $db_stock_arr_wp) {

						// $text_log .= date("Y-m-d H:i:s").": ".print_r($db_stock_arr, true)." \r\n";
						// $text_log .= date("Y-m-d H:i:s").": ".print_r($db_stock_arr_wp, true)." \r\n";	
						// $result_fwrite = fwrite($log_file, $text_log);

					    // $text_log = date("Y-m-d H:i:s").": begin Outlets number updated \r\n";
					    $outlet_stock = array();
					    $stock = 0;
					    // $text_log .= date("Y-m-d H:i:s").": 121212121212 ".print_r(get_option('added_vendor_list'), true)." \r\n";
					    if (get_option('added_vendor_list')) {
					    	$iss = false;
					        foreach ($rows_product_vendor as $key => $product_vendor_db) {
					            $added_vendor_meta_arr = unserialize( get_option('added_vendor_list') );
					            // $text_log .= date("Y-m-d H:i:s").": 1111111 ".print_r($added_vendor_meta_arr, true)." \r\n";
					            foreach ($added_vendor_meta_arr as $key => $vendor) {
					                if($product_vendor_db['vendorid']==$vendor['dbid']) {
					                    $outlet_stock[$vendor['wpid']] = $product_vendor_db['quantity'];
					                    $stock = $stock + $product_vendor_db['quantity'];
					                } 
					                elseif($isset_p_i_stock['isset']) {
					                	if (!$iss) {
						                	$outlet_stock[$isset_p_i_stock['id']] = $isset_p_i_stock['stock'];
						                	$stock = $stock + $isset_p_i_stock['stock'];
						                	$iss = true;
					                	}
					                }
					            }
					        }
					        $outlet_stock_serialize = serialize($outlet_stock+$array_wc_pos_outlet_stock_excluded);
					        update_post_meta( $product->ID, '_wc_pos_outlet_stock', $outlet_stock_serialize );
					        // $array_wc_pos_outlet_stock = get_post_meta( $product->ID, '_wc_pos_outlet_stock', true );

					    } else {
					        $stock = $row_product['quantity'];            
					    }
					    
					    update_post_meta( $product->ID, '_stock', $stock );
					    if($stock>0) $stock_status = 'instock'; else $stock_status = 'outofstock';
					    update_post_meta( $product->ID, '_stock_status', $stock_status);

					    // $text_log .= date("Y-m-d H:i:s").": ".print_r($outlet_stock, true)." \r\n";
					    $text_log = date("Y-m-d H:i:s").": Outlets number of product ".$product->post_title." (".$product->ID.") updated. \r\n";
						$result_fwrite = fwrite($log_file, $text_log);
					}
                    end old code 555 */

                    $end_time555 = microtime(true);
                    $execution_time555 = $end_time555 - $start_time555;
                    // $text_log .= date("Y-m-d H:i:s").": Block execution time 555 {$execution_time555} seconds \r\n";
                    // $result_fwrite = fwrite($log_file, $text_log);

					// $text_log = date("Y-m-d H:i:s").": ".$product->post_title." (".$product->ID.") \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

					// $text_log = date("Y-m-d H:i:s").": 5555555 \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

				    $mra_import_psql_enable_auto_price_edit = get_option('mra_import_psql_enable_auto_price_edit');
				    if($mra_import_psql_enable_auto_price_edit == 'on') {

                        $start_time616 = microtime(true);

                        // new code 616

                        // Кэшируем список вендоров
                        $added_vendor_list_option = get_option('added_vendor_list');
                        $added_vendor_meta_arr_wp = $added_vendor_list_option ? @unserialize($added_vendor_list_option) : [];

                        // Создаём мапы: dbid → wpid и wpid → dbid
                        $vendor_dbid_to_wpid = [];
                        $vendor_wpid_to_dbid = [];
                        foreach ($added_vendor_meta_arr_wp as $vendor) {
                            if (!empty($vendor['dbid']) && !empty($vendor['wpid'])) {
                                $vendor_dbid_to_wpid[$vendor['dbid']] = $vendor['wpid'];
                                $vendor_wpid_to_dbid[$vendor['wpid']] = $vendor['dbid'];
                            }
                        }

                        // Создание массива db_cost_arr из базы
                        $db_cost_arr = [];
                        foreach ($rows_product_cost as $value) {
                            $vendorid = $value['vendorid'];
                            if (isset($vendor_dbid_to_wpid[$vendorid])) {
                                $db_cost_arr[] = [
                                    'vendorid' => $vendorid,
                                    'cost'     => $value['cost'],
                                ];
                            }
                        }

                        // Проверка наличия personal-inventory
                        $isset_p_i_cost = [
                            'isset' => false,
                        ];
                        $outlet_cost_wp = [];

                        if (is_array($wc_pos_outlet_cost)) {
                            $outlet_cost_wp = $wc_pos_outlet_cost;
                        } else {
                            $unserialized = @unserialize($wc_pos_outlet_cost);
                            if (is_array($unserialized)) {
                                $outlet_cost_wp = $unserialized;
                            }
                        }

                        if (is_array($outlet_cost_wp)) {
                            foreach ($outlet_cost_wp as $key => $value) {
                                $outlet = get_post($key);
                                if ($outlet && $outlet->post_name === 'personal-inventory') {
                                    $isset_p_i_cost = [
                                        'isset' => true,
                                        'id'    => $key,
                                        'cost'  => $value,
                                    ];
                                    break;
                                }
                            }
                        }

                        // Создание массива db_cost_arr_wp из outlet_cost_wp
                        $db_cost_arr_wp = [];
                        foreach ($outlet_cost_wp as $key => $value) {
                            $vendorid = $vendor_wpid_to_dbid[$key] ?? null;
                            if ($vendorid !== null) {
                                $db_cost_arr_wp[] = [
                                    'vendorid' => $vendorid,
                                    'cost'     => $value,
                                ];
                            }
                        }

                        // Обновление поля MAP
                        if (!empty($row_product) && isset($row_product['MAP'])) {
                            $current_map = get_post_meta($product->ID, '_minimum_advertised_price', true);
                            if ($current_map != $row_product['MAP']) {
                                update_post_meta($product->ID, '_minimum_advertised_price', $row_product['MAP']);
                                $text_log = date("Y-m-d H:i:s") . ": MAP of product {$product->post_title} ({$product->ID}) updated. ({$row_product['MAP']}) \r\n";
                                fwrite($log_file, $text_log);
                            }
                        } else {
                            $text_log = date("Y-m-d H:i:s") . ": Warning: Missing 'MAP' key in row_product for product " . ($product->post_title ?? 'Unknown') . " (" . ($product->ID ?? 'Unknown') . ")\r\n";
                            fwrite($log_file, $text_log);
                        }


                        // end new code 616

                        /* old code 616
						// start. Creating two arrays to recheck the items in which to change the cost.
						$added_vendor_meta_arr_wp = unserialize( get_option('added_vendor_list') );
						$db_cost_arr = array();
					    $a = 0;
						foreach ($rows_product_cost as $key => $value) {
							$vendorid_wp = '';
							foreach ($added_vendor_meta_arr_wp as $k => $val) {
								if ($val['dbid']==$value['vendorid']) {
									$vendorid_wp = $val['dbid'];
									break;
								}
							}
							if ($vendorid_wp != '') {
								$db_cost_arr[$a]['vendorid'] = $vendorid_wp;
								$db_cost_arr[$a]['cost'] = $value['cost'];
							$a++; }
						}

						// $text_log = date("Y-m-d H:i:s").": 66666666 \r\n";
						// $result_fwrite = fwrite($log_file, $text_log);

						$isset_p_i_cost = array();
						$isset_p_i_cost['isset'] = false;
						$outlet_cost_wp = array();
						if(is_array($wc_pos_outlet_cost)) {
							$outlet_cost_wp = $wc_pos_outlet_cost;

							if(is_array($outlet_cost_wp)) {
								foreach ($outlet_cost_wp as $key => $value) {
									$outlet = get_post($key);
				    				$slug_outlet = $outlet->post_name;
				    				if($slug_outlet=='personal-inventory') {
				    					$isset_p_i_cost['isset'] = true;
				    					$isset_p_i_cost['id'] = $key;
				    					$isset_p_i_cost['cost'] = $value;
				    				}
								}
							}
						}
						else {
							$outlet_cost_wp = unserialize($wc_pos_outlet_cost);

							if(is_array($outlet_cost_wp)) {
								foreach ($outlet_cost_wp as $key => $value) {
									$outlet = get_post($key);
				    				$slug_outlet = $outlet->post_name;
				    				if($slug_outlet=='personal-inventory') {
				    					$isset_p_i_cost['isset'] = true;
				    					$isset_p_i_cost['id'] = $key;
				    					$isset_p_i_cost['cost'] = $value;
				    				}
								}
							}
						}
						// $text_log = date("Y-m-d H:i:s").": ".print_r($outlet_stock_wp, true)." \r\n";			
						$db_cost_arr_wp = array();
					    $b = 0;
						foreach ($outlet_cost_wp as $key => $value) {
							foreach ($added_vendor_meta_arr_wp as $k => $val) {
								if ($val['wpid']==$key) {
									$vendorid_wp = $val['dbid'];
									break;
								}
							}
							$db_cost_arr_wp[$b]['vendorid'] = $vendorid_wp;
							$db_cost_arr_wp[$b]['cost'] = $value;
						$b++; }

						// $text_log = date("Y-m-d H:i:s").": 77777777 \r\n";
						// $result_fwrite = fwrite($log_file, $text_log);

						// $text_log .= date("Y-m-d H:i:s").": ".print_r($db_cost_arr, true)." \r\n"; 
						// $text_log .= date("Y-m-d H:i:s").": ".print_r($db_cost_arr_wp, true)." \r\n";
						// if($db_cost_arr != $db_cost_arr_wp) {
						// 	$text_log .= date("Y-m-d H:i:s").": db_cost_arr != db_cost_arr_wp \r\n";
						// } else {
						// 	$text_log .= date("Y-m-d H:i:s").": db_cost_arr == db_cost_arr_wp \r\n";
						// }
						// $result_fwrite = fwrite($log_file, $text_log);

						// end. Creating two arrays to recheck the items in which to change the cost.

						// if(get_post_meta( $product->ID, '_minimum_advertised_price', true ) != $row_product['MAP']) {
						// 	update_post_meta( $product->ID, '_minimum_advertised_price', $row_product['MAP'] );
						// 	$text_log = date("Y-m-d H:i:s").": MAP of product ".$product->post_title." (".$product->ID.") updated. (".$row_product['MAP'].") \r\n";
						// 	$result_fwrite = fwrite($log_file, $text_log);
						// }

                        if (!empty($row_product) && isset($row_product['MAP'])) {
                            if (get_post_meta($product->ID, '_minimum_advertised_price', true) != $row_product['MAP']) {
                                update_post_meta($product->ID, '_minimum_advertised_price', $row_product['MAP']);
                                $text_log = date("Y-m-d H:i:s") . ": MAP of product " . $product->post_title . " (" . $product->ID . ") updated. (" . $row_product['MAP'] . ") \r\n";
                                $result_fwrite = fwrite($log_file, $text_log);
                            }
                        } else {
                            $text_log = date("Y-m-d H:i:s") . ": Warning: Missing 'MAP' key in row_product for product " . ($product->post_title ?? 'Unknown') . " (" . ($product->ID ?? 'Unknown') . ")\r\n";
                            fwrite($log_file, $text_log);
                        }

                        end old code 616 */

                        $end_time616 = microtime(true);
                        $execution_time616 = $end_time616 - $start_time616;
                        // $text_log .= date("Y-m-d H:i:s").": Block execution time 616 {$execution_time616} seconds \r\n";
                        // $result_fwrite = fwrite($log_file, $text_log);




                        $start_time777 = microtime(true);

                        // new code

                        $mra_import_psql_select = get_option('mra_import_psql_select');
                        $percent = get_option('mra_import_psql_percent');
                        $dollar = get_option('mra_import_psql_dollar');

                        $meta = get_post_meta($product->ID);
                        $p_regular_price = $meta['_regular_price'][0] ?? 0;
                        $p_price = $meta['_price'][0] ?? 0;
                        $cog_cost = $meta['_wc_cog_cost'][0] ?? 0;
                        $map = $meta['_minimum_advertised_price'][0] ?? 0;

                        if ($cog_cost === '') $cog_cost = 0;

                        if (!empty($wc_pos_outlet_cost)) {
                            $outlet_cost_wp = is_array($wc_pos_outlet_cost) ? $wc_pos_outlet_cost : @unserialize($wc_pos_outlet_cost);

                            if (
                                is_array($outlet_cost_wp)
                                && count($outlet_cost_wp) === 1
                                && !empty($isset_p_i_cost['isset'])
                                && $isset_p_i_cost['isset'] === true
                            ) {
                                $cog_cost = $isset_p_i_cost['cost'];
                                update_post_meta($product->ID, '_wc_cog_cost', $cog_cost);
                            }
                        }

                        switch ($mra_import_psql_select) {
                            case 'percent':
                                $price = $cog_cost + ($cog_cost * ($percent / 100));
                                break;

                            case 'value':
                                $price = $cog_cost + $dollar;
                                break;

                            case 'map_percent':
                                $price = ($map && $map != 0 && $map !== '') 
                                    ? $map 
                                    : $cog_cost + ($cog_cost * ($percent / 100));
                                break;

                            case 'map_value':
                                $price = ($map && $map != 0 && $map !== '') 
                                    ? $map 
                                    : $cog_cost + $dollar;
                                break;

                            default:
                                $price = $p_price; // fallback
                                break;
                        }


                        // end new code

                        /* old code
						$p_regular_price = get_post_meta( $product->ID, '_regular_price', true );
						$p_price = get_post_meta( $product->ID, '_price', true );

						$mra_import_psql_select = get_option('mra_import_psql_select');
					    if ($mra_import_psql_select) {
						    $cog_cost = get_post_meta( $product->ID, '_wc_cog_cost', true );
						    if($cog_cost=='')
						    	$cog_cost = 0;
							$map = get_post_meta( $product->ID, '_minimum_advertised_price', true );
							
							$percent = get_option('mra_import_psql_percent');
							$dollar = get_option('mra_import_psql_dollar');
						    if(get_post_meta( $product->ID, '_price', true ))
						    	$price = get_post_meta( $product->ID, '_price', true );
				    		else
				    			$price = 0;

				    		if($wc_pos_outlet_cost) {
							
					    		if (is_array($wc_pos_outlet_cost))
					    			$outlet_cost_wp = $wc_pos_outlet_cost;
				    			else
					    			$outlet_cost_wp = unserialize($wc_pos_outlet_cost);

					   //  		$text_log .= date("Y-m-d H:i:s").": ".print_r($outlet_cost_wp, true)." \r\n";
					   //  		$text_log .= date("Y-m-d H:i:s").": ".print_r(count($outlet_cost_wp), true)." \r\n";
					   //  		$text_log .= date("Y-m-d H:i:s").": ".print_r($isset_p_i_cost, true)." \r\n";
								// $result_fwrite = fwrite($log_file, $text_log);


						    	if(is_array($outlet_cost_wp) && count($outlet_cost_wp)==1 && $isset_p_i_cost['isset']==true) {
						    		$cog_cost = $isset_p_i_cost['cost'];
						    		// $text_log = date("Y-m-d H:i:s").": Since the product ".$product->post_title." (".$product->ID.") has only personal inventory, it is its value that is assigned to price. \r\n";
									// $result_fwrite = fwrite($log_file, $text_log);

									update_post_meta( $product->ID, '_wc_cog_cost', $cog_cost );
						    	}
						    }

						    if ($mra_import_psql_select=="percent") {
						    	$percent_num = $cog_cost * ($percent/100);
							    $price = $cog_cost + $percent_num;
						    } elseif ($mra_import_psql_select=="value") {
						    	$price = $cog_cost + $dollar;
						    } elseif ($mra_import_psql_select=="map_percent" && $percent!='') {
						    	if ($map && $map!=0 && $map!='') {
									$price = $map;
						    	} else {
						    		$percent_num = $cog_cost * ($percent/100);
							    	$price = $cog_cost + $percent_num;
						    	}
						    } elseif ($mra_import_psql_select=="map_value" && $dollar!='') {
						    	if ($map && $map!=0 && $map!='') {
									$price = $map;
						    	} else {
						    		$price = $cog_cost + $dollar;
						    	}
						    }
					    }
                        end old code */

                        $end_time777 = microtime(true);
                        $execution_time777 = $end_time777 - $start_time777;
                        // $text_log .= date("Y-m-d H:i:s").": Block execution time 777 {$execution_time777} seconds \r\n";
                        // $result_fwrite = fwrite($log_file, $text_log);

					 //    $text_log = date("Y-m-d H:i:s").": 11111 Cost 2222 \r\n";
					 //    $text_log .= date("Y-m-d H:i:s").": ".print_r($db_cost_arr, true)." \r\n"; 
						// $text_log .= date("Y-m-d H:i:s").": ".print_r($db_cost_arr_wp, true)." \r\n";
						// $text_log .= date("Y-m-d H:i:s").": ".print_r($p_regular_price, true)." \r\n";
						// $text_log .= date("Y-m-d H:i:s").": ".print_r($p_price, true)." \r\n";
						// $text_log .= date("Y-m-d H:i:s").": ".print_r($price, true)." \r\n";
						// $result_fwrite = fwrite($log_file, $text_log);


                        $start_time888 = microtime(true);


                        // new code 888

                        // Check if cost data or price values differ — if so, update them
                        if (
                            ($db_cost_arr != $db_cost_arr_wp) || 
                            ($p_regular_price == 0) || 
                            ($p_price == 0) || 
                            ($price != $p_regular_price)
                        ) {
                            // Unserialize outlet stock if needed
                            $outlet_stock_c = is_array($array_wc_pos_outlet_stock) 
                                ? $array_wc_pos_outlet_stock 
                                : unserialize($array_wc_pos_outlet_stock);

                            $iss2 = false;
                            $outlet_cost = [];
                            $cost = 0;
                            $cost_stok_id = '';

                            $added_vendor_meta_arr = get_option('added_vendor_list') 
                                ? unserialize(get_option('added_vendor_list')) 
                                : [];

                            if (!empty($added_vendor_meta_arr)) {
                                foreach ($rows_product_cost as $product_cost_db) {
                                    $cost_db = floatval($product_cost_db['cost']);
                                    foreach ($added_vendor_meta_arr as $vendor_wp) {
                                        if ($product_cost_db['vendorid'] == $vendor_wp['dbid']) {
                                            $outlet_cost[$vendor_wp['wpid']] = $cost_db;

                                            if (!empty($outlet_stock_c[$vendor_wp['wpid']])) {
                                                if ($cost_db != 0) {
                                                    $cost = ($cost == 0 || $cost_db < $cost) ? $cost_db : $cost;
                                                }
                                            } else {
                                                $cost_stok_id = $vendor_wp['wpid'];
                                            }
                                        } elseif ($isset_p_i_cost['isset'] && !$iss2) {
                                            $outlet_cost[$isset_p_i_cost['id']] = $isset_p_i_cost['cost'];
                                            $iss2 = true;
                                        }
                                    }
                                }

                                // Exclude cost for stock ID if needed
                                if ($cost_stok_id != '') {
                                    unset($outlet_cost[$cost_stok_id]);
                                    if (!empty($outlet_cost)) {
                                        $cost = min($outlet_cost);
                                    }
                                }

                                // Merge outlet cost with excluded and save
                                $merged_cost = $outlet_cost + $array_wc_pos_outlet_cost_excluded;
                                update_post_meta($product->ID, '_wc_pos_outlet_cost', serialize($merged_cost));
                            }

                            // Update core COG and MAP values
                            update_post_meta($product->ID, '_wc_cog_cost', $cost);
                            if (!empty($row_product['MAP'])) {
                                update_post_meta($product->ID, '_minimum_advertised_price', $row_product['MAP']);
                            }

                            // Recalculate price based on method (percent, value, map)
                            $price_method = get_option('mra_import_psql_select');
                            if ($price_method) {
                                $cog_cost = floatval(get_post_meta($product->ID, '_wc_cog_cost', true)) ?: 0;
                                $map = floatval(get_post_meta($product->ID, '_minimum_advertised_price', true)) ?: 0;
                                $percent = floatval(get_option('mra_import_psql_percent'));
                                $dollar = floatval(get_option('mra_import_psql_dollar'));
                                $price = floatval(get_post_meta($product->ID, '_price', true)) ?: 0;

                                // If product only has personal inventory, use its value as COG
                                $wc_pos_outlet_cost = get_post_meta($product->ID, '_wc_pos_outlet_cost', true);
                                $outlet_cost_wp = is_array($wc_pos_outlet_cost) 
                                    ? $wc_pos_outlet_cost 
                                    : unserialize($wc_pos_outlet_cost);

                                if (
                                    is_array($outlet_cost_wp) && 
                                    count($outlet_cost_wp) === 1 && 
                                    $isset_p_i_cost['isset']
                                ) {
                                    $cog_cost = $isset_p_i_cost['cost'];
                                    update_post_meta($product->ID, '_wc_cog_cost', $cog_cost);

                                    $text_log = date("Y-m-d H:i:s") . ": Since the product {$product->post_title} ({$product->ID}) has only personal inventory, it is its value that is assigned to price.\r\n";
                                    fwrite($log_file, $text_log);
                                }

                                // Price calculation logic
                                switch ($price_method) {
                                    case 'percent':
                                        $price = $cog_cost + ($cog_cost * ($percent / 100));
                                        break;
                                    case 'value':
                                        $price = $cog_cost + $dollar;
                                        break;
                                    case 'map_percent':
                                        $price = $map > 0 ? $map : ($cog_cost + ($cog_cost * ($percent / 100)));
                                        break;
                                    case 'map_value':
                                        $price = $map > 0 ? $map : ($cog_cost + $dollar);
                                        break;
                                }
                            }

                            // Save final prices
                            if ($price > 0) {
                                update_post_meta($product->ID, '_custom_regular_price', $price);
                            } else {
                                $price = get_post_meta($product->ID, '_custom_regular_price', true);
                            }

                            update_post_meta($product->ID, '_regular_price', $price);
                            update_post_meta($product->ID, '_price', $price);

                            // Final log entry
                            $text_log = date("Y-m-d H:i:s") . ": Outlets cost of product {$product->post_title} ({$product->ID}) updated.\r\n";
                            fwrite($log_file, $text_log);
                        }

                        // end new code 888


                        /* old code 888

						// Next is the code for overwriting the cost of goods in case the data differs.
						if(($db_cost_arr != $db_cost_arr_wp) or ($p_regular_price == 0) or ($p_price == 0) or ($price != $p_regular_price)) {

							// $text_log = date("Y-m-d H:i:s").": ".print_r($db_cost_arr, true)." \r\n"; 
							// $text_log .= date("Y-m-d H:i:s").": ".print_r($db_cost_arr_wp, true)." \r\n";
							// $result_fwrite = fwrite($log_file, $text_log);

						    // $text_log = date("Y-m-d H:i:s").": begin Outlets cost updated \r\n";
							if (is_array($array_wc_pos_outlet_stock))
								$outlet_stock_c = $array_wc_pos_outlet_stock;
							else
								$outlet_stock_c = unserialize($array_wc_pos_outlet_stock);


							$iss2 = false;
						    $outlet_cost = array();
						    $cost = 0;
						    $cost_stok_id = '';
						    // $text_log .= date("Y-m-d H:i:s").": 23232323232323 ".print_r(get_option('added_vendor_list'), true)." \r\n";
						    if (get_option('added_vendor_list')) {
						        foreach ($rows_product_cost as $key => $product_cost_db) {
						            $added_vendor_meta_arr = unserialize( get_option('added_vendor_list') );
						            // $text_log .= date("Y-m-d H:i:s").": 222222 ".print_r($added_vendor_meta_arr, true)." \r\n";
						            $cost_db = floatval($product_cost_db['cost']);
						            foreach ($added_vendor_meta_arr as $key => $vendor_wp) {
						                if($product_cost_db['vendorid']==$vendor_wp['dbid']) {
						                    $outlet_cost[$vendor_wp['wpid']] = $cost_db;
						                    if (isset($outlet_stock_c[$vendor_wp['wpid']]) && $outlet_stock_c[$vendor_wp['wpid']] != 0) {
							                    if ($cost_db!=0) {
							                        if ($cost==0){
							                            $cost = $cost_db;
							                        } elseif ($cost!=0 && $cost_db<$cost) {
							                            $cost = $cost_db;
							                        }
							                    } 
						                    } else {
						                    	$cost_stok_id = $vendor_wp['wpid'];
						                    }                       
						                } elseif($isset_p_i_cost['isset']) {
						                	if (!$iss2) {
							                	$outlet_cost[$isset_p_i_cost['id']] = $isset_p_i_cost['cost'];
							                	$iss2 = true;
						                	}
						                }
						            }
						        }
						        $outlet_cost_serialize = serialize($outlet_cost+$array_wc_pos_outlet_cost_excluded);
						        if($cost_stok_id!='') {
					                unset($outlet_cost[$cost_stok_id]);
					                if($outlet_cost)
					                	$cost = min($outlet_cost);
					            }
						        update_post_meta( $product->ID, '_wc_pos_outlet_cost', $outlet_cost_serialize );
						        $wc_pos_outlet_cost = get_post_meta( $product->ID, '_wc_pos_outlet_cost', true );

						    }
						    update_post_meta( $product->ID, '_wc_cog_cost', $cost );
						    if (!empty($row_product) && isset($row_product['MAP'])) {
                                update_post_meta($product->ID, '_minimum_advertised_price', $row_product['MAP']);
                            }



						    $plus_one = false;
						    $mra_import_psql_select = get_option('mra_import_psql_select');
						    if ($mra_import_psql_select) {
							    $cog_cost = get_post_meta( $product->ID, '_wc_cog_cost', true );
							    if($cog_cost=='')
							    	$cog_cost = 0;
								$map = get_post_meta( $product->ID, '_minimum_advertised_price', true );
								
								$percent = get_option('mra_import_psql_percent');
								$dollar = get_option('mra_import_psql_dollar');
							    if(get_post_meta( $product->ID, '_price', true ))
							    	$price = get_post_meta( $product->ID, '_price', true );
					    		else
					    			$price = 0;

					    		if($wc_pos_outlet_cost) { 
						    		if (is_array($wc_pos_outlet_cost))
						    			$outlet_cost_wp = $wc_pos_outlet_cost; 
					    			else
						    			$outlet_cost_wp = unserialize($wc_pos_outlet_cost);
							    	if(is_array($outlet_cost_wp) && count($outlet_cost_wp)==1 && $isset_p_i_cost['isset']==true) {
							    		$cog_cost = $isset_p_i_cost['cost'];
							    		$text_log = date("Y-m-d H:i:s").": Since the product ".$product->post_title." (".$product->ID.") has only personal inventory, it is its value that is assigned to price. \r\n";
										$result_fwrite = fwrite($log_file, $text_log);
										update_post_meta( $product->ID, '_wc_cog_cost', $cog_cost );
							    	}
						    	}

							    if ($mra_import_psql_select=="percent") {
							    	$percent_num = $cog_cost * ($percent/100);
								    $price = $cog_cost + $percent_num;
							    } elseif ($mra_import_psql_select=="value") {
							    	$price = $cog_cost + $dollar;
							    } elseif ($mra_import_psql_select=="map_percent" && $percent!='') {
							    	if ($map && $map!=0 && $map!='') {
							    		// $plus_one = true;
										// $price = $map+1;
										$price = $map;
							    	} else {
							    		$percent_num = $cog_cost * ($percent/100);
								    	$price = $cog_cost + $percent_num;
							    	}
							    } elseif ($mra_import_psql_select=="map_value" && $dollar!='') {
							    	if ($map && $map!=0 && $map!='') {
							    		// $plus_one = true;
										// $price = $map+1;
										$price = $map;
							    	} else {
							    		$price = $cog_cost + $dollar;
							    	}
							    }
						    }

						  //   if($plus_one) {
						  //   	$text_log = date("Y-m-d H:i:s").": Price changed based on MAP + 1 ".$product->post_title." (".$product->ID.") updated. \r\n";
								// $result_fwrite = fwrite($log_file, $text_log);
						  //   }

						    if($price != 0) {
						    	update_post_meta($product->ID, '_custom_regular_price', $price);
						    } else {
						    	$price = get_post_meta($product->ID, '_custom_regular_price', true);
						    }
						    update_post_meta( $product->ID, '_regular_price', $price );
						    update_post_meta( $product->ID, '_price', $price );
						    
						    // $text_log .= date("Y-m-d H:i:s").": ".print_r($outlet_cost, true)." \r\n";
						    $text_log = date("Y-m-d H:i:s").": Outlets cost of product ".$product->post_title." (".$product->ID.") updated. \r\n";
							$result_fwrite = fwrite($log_file, $text_log);
						}

                        end old code 888 */

						$end_time888 = microtime(true);
                        $execution_time888 = $end_time888 - $start_time888;
                        // $text_log .= date("Y-m-d H:i:s").": Block execution time 888 {$execution_time888} seconds \r\n";
                        // $result_fwrite = fwrite($log_file, $text_log);

					}
					// $text_log = date("Y-m-d H:i:s").": 8888888 \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);


                    $start_time999 = microtime(true);

					$name_site = get_bloginfo('name');
		            $homepage_link = get_bloginfo('url');

		            $result_site = $mysqli_read_write_product->query("SELECT * FROM sites WHERE name='".$name_site."' AND homepage_link='".$homepage_link."'");
		            if ($result_site->num_rows!=0) {
		                $rows_site = $result_site->fetch_assoc();
		                $site_id_db = $rows_site['id'];
		            } else {
		                $result_site = $mysqli_read_write_product->query("INSERT INTO sites VALUES (NULL, '$name_site', '$homepage_link')");
		                $site_id_db = $mysqli_read_write_product->insert_id;
		            }

		            $result_site_product = $mysqli_read_write_product->query("SELECT * FROM added_products WHERE site_id=".$site_id_db." AND upc=".$upc_product);
		            // var_dump($result_site_product); 
		            if($result_site_product->num_rows==0) {
		                $result_site_product = $mysqli_read_write_product->query("INSERT IGNORE INTO added_products (site_id, upc) VALUES ($site_id_db, $upc_product)");
		                $text_log = date("Y-m-d H:i:s").": Product ".$product->post_title." (".$product->ID.") was written to the \"added_products\" table in the database. \r\n";
						$result_fwrite = fwrite($log_file, $text_log);
		            }
					
					// $i++;
					// $text_log = date("Y-m-d H:i:s").": ".$i.") ".$product->post_title." (".$product->ID.") \r\n";
					// $result_fwrite = fwrite($log_file, $text_log);

	            	$mra_import_psql_enable_attribute_checking = get_option('mra_import_psql_enable_attribute_checking');
	            	if ($mra_import_psql_enable_attribute_checking == 'on') {
						if (check_product_attributes($product->ID)) {
							// $text_log = date("Y-m-d H:i:s").": check_product_attributes TRUE \r\n";
							// $result_fwrite = fwrite($log_file, $text_log);
						} else {
							// $text_log = date("Y-m-d H:i:s").": check_product_attributes FALSE \r\n";
							// $result_fwrite = fwrite($log_file, $text_log);
							$result_attrs_product = $mysqli_read_product->query("SELECT * FROM attributes_master WHERE upc=".$upc_product);
						    if ($result_attrs_product) {
						        $row_attrs_product = $result_attrs_product->fetch_all(MYSQLI_ASSOC);
						        foreach ($row_attrs_product as $key => $attr_product) {
						            $name_prod = strtolower($attr_product['name']);
						            $name_prod = str_replace(' ', '_', $name_prod);
						            $name_prod = str_replace('/', '_or_', $name_prod);

						            $custom_attributes['pa_'.$name_prod] = $attr_product['value'];
						        }
						    }

						    if(!empty($custom_attributes))
						        save_wc_custom_attributes1111($product->ID, $custom_attributes);

						    $text_log = date("Y-m-d H:i:s").": This product with WP id ".$product->ID." (upc: ".$upc_product.") has updated attributes due to missing or incomplete attributes. \r\n";
						    $result_fwrite = fwrite($log_file, $text_log);
						}
					}

                    $end_time999 = microtime(true);
                    $execution_time999 = $end_time999 - $start_time999;
                    // $text_log .= date("Y-m-d H:i:s").": Block execution time 999 {$execution_time999} seconds \r\n";
                    // $result_fwrite = fwrite($log_file, $text_log);
				}


				// $draft_status_product = check_and_update_product_status($product->ID); 
				// $text_log = date("Y-m-d H:i:s").": ".$i.") ".print_r($draft_status_product)." \r\n";
				// $result_fwrite = fwrite($log_file, $text_log);
				// if ($draft_status_product != false) {
				// 	if ($draft_status_product == "draft") {
				// 		$text_log = date("Y-m-d H:i:s").": Product ".$product->post_title." (".$product->ID.") has been moved to draft status as it has no inventory in vendors. \r\n";
				// 		$result_fwrite = fwrite($log_file, $text_log);
				// 	} elseif ($draft_status_product == "publish") {
				// 		$text_log = date("Y-m-d H:i:s").": Product ".$product->post_title." (".$product->ID.") has been moved to publish status as it has no inventory in vendors. \r\n";
				// 		$result_fwrite = fwrite($log_file, $text_log);
				// 	}

				// }

                $start_time1212 = microtime(true);

				$draft_status_product = '';
				$current_status = get_post_status($product->ID);
				// $text_log = date("Y-m-d H:i:s").": status product ".$product->ID."  ".print_r($current_status, true)." \r\n";
				// $result_fwrite = fwrite($log_file, $text_log);

				// $outlet_cost_st = $wc_pos_outlet_cost;
	   //  		$outlet_stock_st = $array_wc_pos_outlet_stock;

	    		if(is_array($wc_pos_outlet_cost)) {
					$outlet_cost_st = $wc_pos_outlet_cost;
				} else {
					$outlet_cost_st = unserialize($wc_pos_outlet_cost);
				}

				if(is_array($array_wc_pos_outlet_stock)) {
					$outlet_stock_st = $array_wc_pos_outlet_stock;
				} else {
					$outlet_stock_st = unserialize($array_wc_pos_outlet_stock);
				}

	   //  		$text_log = date("Y-m-d H:i:s").": outlet_cost ".print_r(empty($outlet_cost_st), true)." \r\n";
	   //  		$text_log .= date("Y-m-d H:i:s").": outlet_stock ".print_r(empty($outlet_stock_st), true)." \r\n";
				// $result_fwrite = fwrite($log_file, $text_log);

				$stock_st = get_post_meta( $product->ID, '_stock', true );
		    	$price_st = get_post_meta( $product->ID, '_price', true );

                /*
	    		if ((empty($outlet_cost_st) && empty($outlet_stock_st)) || !is_personal_product($product->ID)) {
	    			if($stock_st==0 || $price_st==0) {
				        if ($current_status != 'draft') {
				            $product_data = array(
				                'ID' => $product->ID,
				                'post_status' => 'draft'
				            );
				            wp_update_post($product_data);
				            $draft_status_product = 'draft';
				        }
			        }
			    }
                */

			    // if ($current_status == 'draft' && !empty($outlet_cost_st) && !empty($outlet_stock_st)) {
			    //     $product_data = array(
			    //         'ID' => $product->ID,
			    //         'post_status' => 'publish'
			    //     );
			    //     wp_update_post($product_data);
			    //     $draft_status_product = 'publish';
			    // } elseif ($current_status == 'draft' && empty($outlet_cost_st) && empty($outlet_stock_st)) {
			    // 	if($stock_st!=0 || $price_st!=0) {
			    // 		$product_data = array(
				   //          'ID' => $product->ID,
				   //          'post_status' => 'publish'
				   //      );
				   //      wp_update_post($product_data);
				   //      $draft_status_product = 'publish';
			    // 	}
			    // }

			 //    if ($current_status == 'draft' && !empty($outlet_cost_st) && !empty($outlet_stock_st)) {
			 //        $product_data = array(
			 //            'ID' => $product->ID,
			 //            'post_status' => 'publish'
			 //        );
			 //        wp_update_post($product_data);
			 //        $draft_status_product = 'publish';
			 //    }

			 //    if ($draft_status_product != '') {
				// 	if ($draft_status_product == "draft") {
				// 		$text_log = date("Y-m-d H:i:s").": Product ".$product->post_title." (".$product->ID.") has been moved to draft status as it has no inventory in vendors. \r\n";
				// 		$result_fwrite = fwrite($log_file, $text_log);
				// 	} elseif ($draft_status_product == "publish") {
				// 		$text_log = date("Y-m-d H:i:s").": Product ".$product->post_title." (".$product->ID.") has been moved to publish status as it has no inventory in vendors. \r\n";
				// 		$result_fwrite = fwrite($log_file, $text_log);
				// 	}
				// }

                $end_time1212 = microtime(true);
                $execution_time1212 = $end_time1212 - $start_time1212;
                // $text_log .= date("Y-m-d H:i:s").": Block execution time 1212 {$execution_time1212} seconds \r\n";
                // $result_fwrite = fwrite($log_file, $text_log);


                $start_time2323 = microtime(true);

				if ($mra_import_psql_enable_rsr != 'on' && has_term('ffl_firearm', 'product_tag', $product->ID)) {
					$stock = get_post_meta( $product->ID, '_stock', true );
					if(empty($array_wc_pos_outlet_stock)) {
						if(!empty($array_wc_pos_outlet_stock_old)) {
				    		$stock = 0;
			    		}
				    }       
				    update_post_meta( $product->ID, '_stock', $stock );
				    if($stock>0) $stock_status = 'instock'; else $stock_status = 'outofstock';
				    update_post_meta( $product->ID, '_stock_status', $stock_status);
			    }

			    if ($mra_import_psql_enable_default_outlet != 'on' && has_term('ffl_firearm', 'product_tag', $product->ID)) {
					$stock = get_post_meta( $product->ID, '_stock', true );
					if(empty($array_wc_pos_outlet_stock)) {
						if(!empty($array_wc_pos_outlet_stock_old)) {
				    		$stock = 0;
			    		}
				    }       
				    update_post_meta( $product->ID, '_stock', $stock );
				    if($stock>0) $stock_status = 'instock'; else $stock_status = 'outofstock';
				    update_post_meta( $product->ID, '_stock_status', $stock_status);
			    }

			    $add_tags = add_tags_based_on_pos_outlet($product->ID);
			    if ($add_tags) {
			    	$text_log = date("Y-m-d H:i:s").": New tags have been added to the  ".$product->post_title." (".$product->ID.") product. \r\n";
					$result_fwrite = fwrite($log_file, $text_log);
			    }
                $end_time2323 = microtime(true);
                $execution_time2323 = $end_time2323 - $start_time2323;
                // $text_log .= date("Y-m-d H:i:s").": Block execution time 2323 {$execution_time2323} seconds \r\n";
                // $result_fwrite = fwrite($log_file, $text_log);

			} else {
				$tag_added = mra_check_and_add_tag($product->ID);
				if ( $tag_added ) {
				    $text_log = date("Y-m-d H:i:s").": The tag “personal product” has been added to product  ".$product->post_title." (".$product->ID."). \r\n";
					$result_fwrite = fwrite($log_file, $text_log);
				}
			}

			$current_status_old = get_post_status($product->ID);
			$res_func = check_and_update_product_status($product->ID);
			$current_status_new = get_post_status($product->ID);

			if($current_status_old != $current_status_new) {
				if ($current_status_new === 'draft') {
					$text_log = date("Y-m-d H:i:s").": The status has been changed to DRAFT for product  ".$product->post_title." (".$product->ID."). \r\n";
					$result_fwrite = fwrite($log_file, $text_log);
				} else {
					$text_log = date("Y-m-d H:i:s").": The status has been changed to PUBLISH for product  ".$product->post_title." (".$product->ID."). \r\n";
					$result_fwrite = fwrite($log_file, $text_log);
				}
			}

		}




		$cron_update_product_data['offset'] = $cron_update_product_data['offset']+$cron_update_product_data['number'];
		$text_log = date("Y-m-d H:i:s").": offset:".$cron_update_product_data['offset']." and count_items ".$cron_update_product_data['count_items']." \r\n";
		$result_fwrite = fwrite($log_file, $text_log);
		if($cron_update_product_data['offset'] >= $cron_update_product_data['count_items']) {
			$cron_update_product_data['status'] = 'deactive';
			$text_log = date("Y-m-d H:i:s").": The process of verifying the quantity and value of goods is complete. \r\n";
		    $text_log .= "------------- end ------------- \r\n";
			$result_fwrite = fwrite($log_file, $text_log);


			wp_unschedule_hook( 'cron_mra_psql_event' );
			delete_option('cron_update_product_data');

			$text_log = date("Y-m-d H:i:s").": INTERMEDIATE CRON action has been stopped. \r\n";
			$result_fwrite = fwrite($log_file, $text_log);

			// $cron_update_product_data['status'] = 'active';
			// $cron_update_product_data['number'] = 300;
			// $cron_update_product_data['offset'] = 0;
			// $cron_update_product_data['count_items'] = 0;
		} else {
			$text_log = date("Y-m-d H:i:s").": Passed ".$cron_update_product_data['offset']." positions \r\n";
			$result_fwrite = fwrite($log_file, $text_log);
			
			// $text_log = date("Y-m-d H:i:s").": ".print_r($cron_update_product_data, true)." \r\n";
			// $result_fwrite = fwrite($log_file, $text_log);

			update_option( 'cron_update_product_data', serialize($cron_update_product_data));
		}

		fclose($log_file);

    } catch ( Exception $e ) {
		// logs:
		$text_log = date("Y-m-d H:i:s").": ".$e->getMessage()." \r\n";
		$result_fwrite = fwrite($log_file, $text_log);
		fclose($log_file);
  	}

  	if(!$mysqli_read_product->connect_error)
        $mysqli_read_product->close();
    if(!$mysqli_read_write_product->connect_error)
        $mysqli_read_write_product->close();

  	
}