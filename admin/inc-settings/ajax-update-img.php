<?php

add_action( 'wp_ajax_mraupdateimage', 'mraupdateimage_func' );
function mraupdateimage_func(){
	$mysqli_read_product_1 = mra_import_psql_db_connection_read();	
	$product_id = $_POST['product_id'];
	$term_list = wp_get_post_terms( $product_id, 'pa_upc', array( 'fields' => 'names' ) );
	$upc_product = $term_list[0];
	// $upc_product = get_post_meta($product_id, 'pa_upc', true);
	// var_dump($upc_product);

    $img = '';
    $result_img = $mysqli_read_product_1->query("SELECT *
    FROM image_upc_mapping
    WHERE UPC = ".$upc_product);

    if ($result_img->num_rows!=0) {
        $rows_img = $result_img->fetch_assoc();
        $img = $rows_img['s3_URL'];
    }
    // var_dump($result_img);

    $url_title = str_replace('s3://mwm-vendor-images/', '', $img);
    $url_img = 'https://dme5m5gvjikvl.cloudfront.net/'.$url_title;
    // var_dump($url_img);

    update_post_meta($product_id, '_external_image_url', $url_img);
    $result = get_post_meta($product_id, '_external_image_url', true);
    if (empty($result)) {
        echo '<span class="error_text">The external_image is empty and it failed to be added at product: (wpid: '.$product_id.', upc: '.$upc_product.' )</span>';    	
    } else {
    	echo '<span class="success_text">external_image has already been added and exists at the product: (wpid: '.$product_id.', upc: '.$upc_product.' )</span>';
    }

    wp_die();
}