<?php
function save_wc_custom_attributes_import_csv($post_id, $custom_attributes) {
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

function upload_image_wordpress_import_csv($post_id, $url_img) {
    $image_url = $url_img;
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents( $image_url );
    $filename = basename( $image_url );
    if ( wp_mkdir_p( $upload_dir['path'] ) ) {
      $file = $upload_dir['path'] . '/' . $filename;
    }
    else {
      $file = $upload_dir['basedir'] . '/' . $filename;
    }
    file_put_contents( $file, $image_data );
    $wp_filetype = wp_check_filetype( $filename, null );

    $attachment = array(
      'post_mime_type' => $wp_filetype['type'],
      'post_title' => sanitize_file_name( $filename ),
      'post_content' => '',
      'post_status' => 'inherit'
    );

    global $wpdb;
    $row = $wpdb->get_row( "SELECT * FROM $wpdb->postmeta WHERE `meta_key` LIKE '_wp_attached_file' AND `meta_value` LIKE '%".$filename."%'" );
    //SELECT * FROM `wp_postmeta` WHERE `meta_key` LIKE '_wp_attached_file' AND `meta_value` LIKE '%kissclipart-pistol-in-hand-png-clipart-revolver-firearm-clip-a-9cb7e26bc0a7bd6a-removebg-preview%'
    if($row==NULL) {
        $attach_id = wp_insert_attachment( $attachment, $file );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        $attach_data['attach_id'] = $attach_id;
        wp_update_attachment_metadata( $attach_id, $attach_data );
    }

    set_post_thumbnail($post_id, $attach_id);

    return $attach_data;
}


add_action( 'wp_ajax_mrapgupdatecsv', 'mrapgupdatecsv_func' );
function mrapgupdatecsv_func(){

	$product_csv = $_POST['product_csv'];
    $count_product_csv = intval($_POST['count_product_csv']);
    $num_element_csv = intval($_POST['num_element_csv'])+1;

    $product_upc = $_POST['product_upc'];
    $product_title = $_POST['product_title'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['product_quantity'];
    $product_cost = $_POST['product_cost'];
    $product_category = $_POST['product_category'];
    $product_description = $_POST['product_description'];
    $product_manufacturer = $_POST['product_manufacturer'];
    $product_map = $_POST['product_map'];
    $product_weight = $_POST['product_weight'];
    $product_length = $_POST['product_length'];
    $product_width = $_POST['product_width'];
    $product_height = $_POST['product_height'];
    $product_model = $_POST['product_model'];
    $product_firearm = $_POST['product_firearm'];

    global $wpdb;

	$term = term_exists( $product_category, 'product_cat' );
	if($term) {
	    $cat_id = $term['term_taxonomy_id'];
	} else {
	    $cat_insert_res = wp_insert_term( $product_category, 'product_cat');
	    $cat_id = $cat_insert_res['term_taxonomy_id'];
	}

	$args = array(
	    'post_type' => array('product'),
	    'tax_query' => array(
	        'relation' => 'OR',
	        array(
	            'taxonomy' => 'pa_upc',
	            'field' => 'name',
	            'terms' => $product_upc,
	            'operator' => 'IN',
	        )
	    )
	);
	$query = new WP_Query($args);
	// var_dump($query->posts);

	if (empty($query->posts)) {
	    // $url_name = str_replace('s3://mwm-vendor-images/', '', $product_csv[2]);
	    // $url_img = 'https://mwm-vendor-images.s3.us-east-2.amazonaws.com/'.str_replace('s3://mwm-vendor-images/', '', $product_csv[2]);
	    $url_img = $product_image;
	    $img_title = str_replace('https://dme5m5gvjikvl.cloudfront.net/', '', $product_image);
	    $img_select = $wpdb->get_row( "SELECT * FROM $wpdb->posts WHERE `post_title` LIKE '$img_title' AND `post_mime_type` LIKE 'image/jpeg'" );
	    
	    $post_data = [
	        'post_title'    => $product_title,
	        'post_content'  => $product_description,
	        'post_status'   => 'publish',
	        'post_type'     => 'product',
	    ];
	    // var_dump($post_data);
	    $post_id = wp_insert_post(  wp_slash( $post_data ) );
	    // var_dump($post_id."<br>");
	    $product = wc_get_product( $post_id );

	    if(!has_post_thumbnail( $post_id )) {
	        if($img_select) {
	            set_post_thumbnail( $post_id, $img_select->ID );
	        } else {
	            upload_image_wordpress_import_csv($post_id, $url_img);
	        }
	    }

	    $cat_id = array_map('intval', array($cat_id) );
	    wp_set_object_terms( $post_id, $cat_id, 'product_cat' );


	    if($product_firearm==1) {
	        $product_tag = get_term_by( 'name', 'FFL_Firearm', 'product_tag');
	        $tag_id = $product_tag->term_id;
	        $tag_id = array_map('intval', array($tag_id) );
	        wp_set_object_terms( $post_id, $tag_id, 'product_tag');
	    }

	    add_post_meta( $post_id, 'mra_pgsql_product', 'yes' );
	    
	    wp_set_object_terms( $post_id, 'simple', 'product_type' );
	    update_post_meta( $post_id, '_visibility', 'visible' );
	    update_post_meta( $post_id, '_sku', $product_upc );
	    
	    if(intval($product_quantity)>0) $stock_status = 'instock'; else $stock_status = 'outofstock';
	    update_post_meta( $post_id, '_stock_status', $stock_status);
	    update_post_meta( $post_id, '_manage_stock', 'yes' );
	    update_post_meta( $post_id, '_stock', intval($product_quantity) );
	    // update_post_meta( $post_id, '_regular_price', $row_product['cost'] );
	    update_post_meta( $post_id, '_wc_cog_cost', $product_cost );
	    update_post_meta( $post_id, '_minimum_advertised_price', $product_map );
	    update_post_meta( $post_id, '_price', $product_cost );

	    update_post_meta( $post_id, '_weight', $product_weight );
	    update_post_meta( $post_id, '_length', $product_length );
	    update_post_meta( $post_id, '_width', $product_width );
	    update_post_meta( $post_id, '_height', $product_height );

	    // update_post_meta( $post_id, 'total_sales', '0' );
	    // update_post_meta( $post_id, '_downloadable', 'no' );
	    // update_post_meta( $post_id, '_virtual', 'yes' );            
	    // update_post_meta( $post_id, '_sale_price', '' );
	    // update_post_meta( $post_id, '_purchase_note', '' );
	    // update_post_meta( $post_id, '_featured', 'no' );
	    // update_post_meta( $post_id, '_sale_price_dates_from', '' );
	    // update_post_meta( $post_id, '_sale_price_dates_to', '' );
	    // update_post_meta( $post_id, '_sold_individually', '' );
	    // update_post_meta( $post_id, '_backorders', 'no' );

	    $custom_attributes = array();
	    $custom_attributes['pa_upc'] = $product_upc;
	    if(isset($product_manufacturer) && $product_manufacturer!='')
	        $custom_attributes['pa_brand'] = $product_manufacturer;
	    // if(isset($row_product['Model']) && $row_product['Model']!='')
	    //     $custom_attributes['pa_brand'] = $row_product['Model'];

	    if(!empty($custom_attributes))
	        save_wc_custom_attributes_import_csv($post_id, $custom_attributes);


	    if($product) {
	        echo 'Product '.$product_title.' ('.$product_upc.') was successfully <span class="success_text">added</span>';
	        // echo '<p><a href="/wp-admin/admin.php?page=mra_import_psql_products" class="btn">Go to the list of plugin products</a> <a href="/wp-admin/edit.php?post_type=product" class="btn">Go to the list of woocommerce products</a></p>';
	    }
	    else {
	        echo '<span class="error_text">Addition error:</span> product '.$product_title.' ('.$product_upc.') has not been added';
	    }
	    
	} else {
	    $product_id = $query->posts[0]->ID;
	    $product_title = $query->posts[0]->post_title;
	    echo 'The product with upc value '.$product_upc.' already exists in woocommerce and therefore was <span class="error_text">not added.</span> ID: '.$product_id.'; Title: '.$product_title;
	}

	wp_die();
}