<?php

if ( ! function_exists( 'wp_handle_upload' ) )
    require_once( ABSPATH . 'wp-admin/includes/file.php' );

$file = & $_FILES['mra_import_csv'];

$overrides = [ 'test_form' => false ];

$movefile = wp_handle_upload( $file, $overrides );

if ( $movefile && empty($movefile['error']) ) {
    global $wpdb;

    echo "The file was successfully uploaded.\n";

    $data_arr = array();

    $delimiter = ';';

    $csv = file_get_contents($movefile['file']);
    $rows = explode(PHP_EOL, $csv);

    foreach ($rows as $row)
    {
      $data_arr[] = explode($delimiter, $row);
    }

    // $host = get_option('mra_import_psql_host');
    // $port = get_option('mra_import_psql_port');
    // $dbname = get_option('mra_import_psql_dbname');
    // $user = get_option('mra_import_psql_user');
    // $password = get_option('mra_import_psql_password');
    // $mysqli_product = new mysqli($host, $user, $password, $dbname, $port); ?>

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
    echo "<h3>Add products</h3>";
    echo '<div id="list_upc_product">';
        echo '<div id="count_products">'.count($data_arr).'</div>';

    foreach ($data_arr as $key => $product_csv) {
        if($key!=0) {
            // echo '<div class="one_upc" data-num="'.$key.'" 
            // data-upc="'.$product_csv[0].'" 
            // data-title="'.str_replace("\"", "", $product_csv[1]).'" 
            // data-image="'.$product_csv[2].'" 
            // data-quantity="'.$product_csv[3].'" 
            // data-cost="'.$product_csv[4].'" 
            // data-category="'.$product_csv[5].'" 
            // data-description="'.str_replace("\"", "", $product_csv[6]).'" 
            // data-manufacturer="'.$product_csv[7].'" 
            // data-map="'.$product_csv[8].'" 
            // data-weight="'.$product_csv[9].'" 
            // data-length="'.$product_csv[10].'" 
            // data-width="'.$product_csv[11].'" 
            // data-height="'.$product_csv[12].'" 
            // data-model="'.$product_csv[13].'" 
            // data-firearm="'.$product_csv[14].'" 
            // >'.$product_csv[0].'</div>';
            
        
        // $product[0] = upc
        // $product[1] = title
        // $product[2] = image
        // $product[3] = quantity
        // $product[4] = cost
        // $product[5] = category
        // $product[6] = description    
        // $product[7] = manufacturer
        // $product[8] = map
        // $product[9] = weight
        // $product[10] = length
        // $product[11] = width
        // $product[12] = height
        // $product[13] = model
        // $product[14] = firearm    


        // var_dump($product_upc);
        // $result_product = $mysqli_product->query("SELECT * FROM master_catalog, cost_master, inventory_master, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=cost_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND master_catalog.UPC=".$product_upc);
        // $row_product = $result_product->fetch_assoc();
        
        // $product = get_product(96107);
        // var_dump($product);

            
        }
    }
    echo '</div>';
    echo '<div id="result_update"></div>';
    echo '<div id="load_image"><img class="load" src="'.MRA_IMPORT_PSQL_URL.'img/loading.gif"></div>';
    echo '<p><strong>* It is required you keep this page open until adding products shows complete.</br>
    If you close this page before it shows complete  it will end the adding process on the current product its on.</strong></p>';
    echo '<p><a href="/wp-admin/admin.php?page=mra_import_psql_products" class="btn">Go to the list of plugin products</a> <a href="/wp-admin/edit.php?post_type=product" class="btn">Go to the list of woocommerce products</a></p>';

    unlink($movefile['file']); ?>
    <script type="text/javascript">
    jQuery(document).ready( function( $ ){
        var count_el = $('.one_upc').length,
            a = 1;
        $('#list_upc_product .one_upc').each(function (index, element) {

            setTimeout(function() {
                var data = {
                    action: 'mrapgupdatecsv',
                    count_product: $("#count_products").html(),
                    num_element: $(element).attr("data-num"),
                    product_upc: $(element).attr("data-upc"),
                    product_title: $(element).attr("data-title"),
                    product_image: $(element).attr("data-image"),
                    product_quantity: $(element).attr("data-quantity"),
                    product_cost: $(element).attr("data-cost"),
                    product_category: $(element).attr("data-category"),
                    product_description: $(element).attr("data-description"),
                    product_manufacturer: $(element).attr("data-manufacturer"),
                    product_map: $(element).attr("data-map"),
                    product_weight: $(element).attr("data-weight"),
                    product_length: $(element).attr("data-length"),
                    product_width: $(element).attr("data-width"),
                    product_height: $(element).attr("data-height"),
                    product_model: $(element).attr("data-model"),
                    product_firearm: $(element).attr("data-firearm"),
                };

                $.post( ajaxurl, data, function( response ){
                    $("#result_update").append( '<p>'+a+'. '+response+'</p>' );
                    var ind = index+1;
                    if(ind == count_el) {
                        $("#load_image").html('<p style="color: #4CAF50; font-weight: 700;">Product loading is complete</p>');
                    }
                    a++;
                } );

            }, 700*index);
        });
    } );
    </script>
    <?php
} else {
    echo "Possible attacks when downloading a file!\n";
}





// elseif ($_POST['updprod']=="add") {
//     echo "<h3>Update products</h3>";
      
// } elseif ($_POST['updprod']=="delete") {
//     echo "<h3>Delete products</h3>";
// }



?>
</div>