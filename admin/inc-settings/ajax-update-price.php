<?php
add_action( 'wp_ajax_mraupdateprice', 'mraupdateprice_func' );
function mraupdateprice_func(){
	$product_id = $_POST['product_id'];
    $num_element = intval($_POST['num_element'])+1;
	

	$cog_cost = get_post_meta( $product_id, '_wc_cog_cost', true );
	$map = get_post_meta( $product_id, '_minimum_advertised_price', true );

	$mra_import_psql_select = get_option('mra_import_psql_select');
	$percent = get_option('mra_import_psql_percent');
	$dollar = get_option('mra_import_psql_dollar');
    $price = '';

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

    update_post_meta( $product_id, '_regular_price', $price );
    update_post_meta( $product_id, '_price', $price );

    if ($price!='')
    	echo 'edit price product "'.get_the_title($product_id).'" ('.$product_id.') = '.$price;
    else
    	echo 'The price of product '.get_the_title($product_id).'" ('.$product_id.') was not filled because of incorrect markup settings';

    wp_die();
}