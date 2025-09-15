<style type="text/css">
    .update_product_content h3 {
        padding-top: 15px;
    }
    .update_product_content .success_text {
        color: #4CAF50;
        font-weight: 700;
    }
    .update_product_content .error_text {
        color: #ff4910;
        font-weight: 700;
    }
    .update_product_content .btn {
        display: block;
        float: left;
        margin-right: 20px;
        padding: 5px 10px;
        border: 1px solid #e1e1e1;
        border-radius: 3px;
        text-decoration: none;
        color: #000;
        background: #f2f2f4;
    }
    .update_product_content .btn:hover {
        background-color: #e5e5e5;
    }
    #list_upc_product {
        display: none;
    }
       #load_image .load {
            width: 25px;
            height: 25px;
       }
</style>
<div class="update_product_content">
<?php
if ( ! function_exists( 'wp_handle_upload' ) )
    require_once( ABSPATH . 'wp-admin/includes/file.php' );

$attributes_a = wc_get_attribute_taxonomies();
$upc = false;
$brand = false;

// var_dump($attributes_a);
foreach ($attributes_a as $key => $attribute_a) {
    if($attribute_a->attribute_name == "upc") {
        $upc = true;
    }
    if($attribute_a->attribute_name == "brand") {
        $brand = true;
    }
}
if (!$upc) {
    $args_a = array(
        'slug'    => 'pa_upc',
        'name'   => sanitize_title('upc'),
        'type'    => 'select',
        'orderby' => 'menu_order',
        'has_archives'  => false,
    );
    $result_a = wc_create_attribute( $args_a );
}

if (!$brand) {
    $args_b = array(
        'slug'    => 'pa_brand',
        'name'   => sanitize_title('brand'),
        'type'    => 'select',
        'orderby' => 'menu_order',
        'has_archives'  => false,
    );
    $result_b = wc_create_attribute( $args_b );
}

$file = & $_FILES['mra_import_csv'];
$overrides = [ 'test_form' => false ];
$movefile = wp_handle_upload( $file, $overrides );

$products_arr = array();

if ( $movefile && empty($movefile['error']) ) {
    echo "The file was successfully uploaded.\n";

    $csv = file_get_contents($movefile['file']);
    $rows = explode(PHP_EOL, $csv);

    $w=0;
    foreach ($rows as $row) {
      if($w!=0) $products_arr[] = $row;
    $w++; }

} elseif($_POST['updprod']=="update") {
    $products_arr = $_POST['products'];

}
    
if(!empty($products_arr)) {
    echo "<h3>Add products1231</h3>";
    echo '<div id="list_upc_product">';
        echo '<div id="count_products">'.count($products_arr).'</div>';
    foreach ($products_arr as $key => $product_upc) {
        echo '<div class="one_upc" data-num="'.$key.'">'.$product_upc.'</div>';
    
    /*
        // var_dump($product_upc);
        $result_product = $mysqli_product->query("SELECT * FROM master_catalog, cost_master, inventory_master, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=cost_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND master_catalog.UPC=".$product_upc);
        $row_product = $result_product->fetch_assoc();
        
        // $product = get_product(96107);
        // var_dump($product);
        // update_post_meta( $order_id, '_shipping_fflno', esc_attr($ffl_no));

        $term = term_exists( $row_product['Category'], 'product_cat' );
        if($term) {
            $cat_id = $term['term_taxonomy_id'];
        } else {
            $cat_insert_res = wp_insert_term( $row_product['Category'], 'product_cat');
            $cat_id = $cat_insert_res['term_taxonomy_id'];
        }

        $args = array(
            'post_type' => array('product'),
            'tax_query' => array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'pa_upc',
                    'field' => 'name',
                    'terms' => $row_product['UPC'],
                    'operator' => 'IN',
                )
            )
        );
        $query = new WP_Query($args);
        // var_dump($query->posts);

        if (empty($query->posts)) {
            // var_dump($row_product);
            $url_title = str_replace('s3://mwm-vendor-images/', '', $row_product['s3_URL']);
            $url_img = 'https://mwm-vendor-images.s3.us-east-2.amazonaws.com/'.$url_title;
            $img_select = $wpdb->get_row( "SELECT * FROM ".$wpdb->posts." WHERE `post_title` LIKE '".$url_title."' AND `post_mime_type` LIKE 'image/jpeg'" );
            
            $post_data = [
                'post_title'    => $row_product['Title'],
                'post_content'  => $row_product['Product Description'],
                'post_status'   => 'publish',
                'post_type'     => 'product',
            ];
            $post_id = wp_insert_post(  wp_slash( $post_data ) );
            $product = wc_get_product( $post_id );

            if(!has_post_thumbnail( $post_id )) {
                if($img_select) {
                    set_post_thumbnail( $post_id, $img_select->ID );
                } else {
                    upload_image_wordpress($post_id, $url_img);
                }
            }

            $cat_id = array_map('intval', array($cat_id) );
            wp_set_object_terms( $post_id, $cat_id, 'product_cat' );

            if($row_product['Firearm']==1) {
                $product_tag = get_term_by( 'name', 'FFL_Firearm', 'product_tag');
                $tag_id = $product_tag->term_id;
                $tag_id = array_map('intval', array($tag_id) );
                wp_set_object_terms( $post_id, $tag_id, 'product_tag');
            }

            add_post_meta( $post_id, 'mra_pgsql_product', 'yes' );
            
            wp_set_object_terms( $post_id, 'simple', 'product_type' );
            update_post_meta( $post_id, '_visibility', 'visible' );
            update_post_meta( $post_id, '_sku', $row_product['UPC'] );
            
            if(intval($row_product['quantity'])>0) $stock_status = 'instock'; else $stock_status = 'outofstock';
            update_post_meta( $post_id, '_stock_status', $stock_status);
            update_post_meta( $post_id, '_manage_stock', 'yes' );
            update_post_meta( $post_id, '_stock', intval($row_product['quantity']) );
            // update_post_meta( $post_id, '_regular_price', $row_product['cost'] );
            update_post_meta( $post_id, '_wc_cog_cost', $row_product['cost'] );
            update_post_meta( $post_id, '_minimum_advertised_price', $row_product['MAP'] );
            update_post_meta( $post_id, '_price', $row_product['cost'] );

            update_post_meta( $post_id, '_weight', $row_product['Weight'] );
            update_post_meta( $post_id, '_length', $row_product['Length'] );
            update_post_meta( $post_id, '_width', $row_product['Width'] );
            update_post_meta( $post_id, '_height', $row_product['Height'] );

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
            $custom_attributes['pa_upc'] = $row_product['UPC'];
            if(isset($row_product['Manufacturer']) && $row_product['Manufacturer']!='')
                $custom_attributes['pa_brand'] = $row_product['Manufacturer'];
            // if(isset($row_product['Model']) && $row_product['Model']!='')
            //     $custom_attributes['pa_brand'] = $row_product['Model'];

            if(!empty($custom_attributes))
                save_wc_custom_attributes($post_id, $custom_attributes);

            if($product) {
                echo '<p>Product '.$row_product['Title'].' ('.$row_product['UPC'].') was successfully <span class="success_text">added</span></p>';                
            }
            else {
                echo '<p><span class="error_text">Addition error:</span> product '.$row_product['Title'].' ('.$row_product['UPC'].') has not been added</p>';
            }
            
        } else {
            $product_id = $query->posts[0]->ID;
            $product_title = $query->posts[0]->post_title;
            echo '<p>The product with upc value '.$row_product['UPC'].' already exists in woocommerce and therefore was <span class="error_text">not added.</span> ID: '.$product_id.'; Title: '.$product_title.'</p>';
        }
    */
    }
    echo '</div>';
    echo '<div id="result_update"></div>';
    echo '<div id="load_image"><img class="load" src="'.MRA_IMPORT_PSQL_URL.'img/loading.gif"></div>';
    echo '<p><strong>* It is required you keep this page open until adding products shows complete.</br>
    If you close this page before it shows complete  it will end the adding process on the current product its on.</strong></p>';
    echo '<p><a href="/wp-admin/admin.php?page=mra_import_psql_products" class="btn">Go to the list of plugin products</a> <a href="/wp-admin/edit.php?post_type=product" class="btn">Go to the list of woocommerce products</a></p>';
    

    ?>
    <script type="text/javascript">
    jQuery(document).ready( function( $ ){
        var count_el = $('.one_upc').length,
            a = 1;
        $('#list_upc_product .one_upc').each(function (index, element) {

            setTimeout(function() {
                var data = {
                    action: 'mrapgupdate',
                    count_product: $("#count_products").html(),
                    num_element: $(element).attr("data-num"),
                    upc_product: $(element).html()
                };

                var data2 = {
                    action: 'mraupdatecategories'
                };

                $.post( ajaxurl, data, function( response ){
                    $("#result_update").append( '<p>'+a+'. '+response+'</p>' );
                    var ind = index+1;
                    if(ind == count_el) {
                        $("#load_image").html('<p style="color: #4CAF50; font-weight: 700;">Product loading is complete</p>');
                        $.post( ajaxurl, data2, function( response2 ){
                            $("#result_update").append( '<p>'+response2+'</p>' );
                        });
                    }
                    a++;
                } );

            }, 1300*index);
        });
    } );
    </script>
    <?php
    
} 
// elseif ($_POST['updprod']=="add") {
//     echo "<h3>Update products</h3>";
      
// } elseif ($_POST['updprod']=="delete") {
//     echo "<h3>Delete products</h3>";
// }



?>
</div>