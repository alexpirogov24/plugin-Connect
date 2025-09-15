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
if ($_GET['price_edit']=='yes') {

    $mra_psql_products = new WP_Query;
    $products = $mra_psql_products->query( [
        'posts_per_page' => -1,
        // 'posts_per_page' => $cron_update_product_data['number'],
        // 'offset' => $cron_update_product_data['offset'],
        'post_type' => 'product',
        'tax_query' => array(
            'relation' => 'OR',
            // array(
            //         'taxonomy' => 'pa_upc',
            //         'field' => 'name',
            //         'terms' => array('23614740889', '602686422420', '681565230707'),
            //         'operator' => 'IN',
            //     )
            array(
                'taxonomy' => 'pa_upc',
                'operator' => 'EXISTS',
            )
        )
    ] );

    // foreach( $products as $key => $product ) {


    //     $cog_cost = get_post_meta( $product->ID, '_wc_cog_cost', true );
    //     $percent_num = $cog_cost * ($percent/100);
    //     $price = $cog_cost + $percent_num;
    //     update_post_meta( $product->ID, '_regular_price', $price );
    //     update_post_meta( $product->ID, '_price', $price );

    //     echo '<p>'.$key.') edit price product "'.$product->post_title.' ('.$product->ID.')" = '.$price.'</p>';

    // }

    echo "<h3>Edit product price</h3>";
    echo '<div id="list_upc_product">';
        echo '<div id="count_products">'.count($products).'</div>';
    foreach( $products as $key => $product ) {
        echo '<div class="product" data-num="'.$key.'">'.$product->ID.'</div>';
    }
    echo '</div>';
    echo '<p><strong>* It is required you keep this page open until adding products shows complete.</br>
    If you close this page before it shows complete  it will end the adding process on the current product its on.</strong></p>';
    echo '<div id="result_update"></div>';
    echo '<div id="load_image"><img class="load" src="'.MRA_IMPORT_PSQL_URL.'img/loading.gif"></div>';
    echo '<p><strong>* It is required you keep this page open until adding products shows complete.</br>
    If you close this page before it shows complete  it will end the adding process on the current product its on.</strong></p>';
    echo '<p><a href="/wp-admin/edit.php?post_type=product" class="btn">Go to the list of woocommerce products</a></p>';
    

    ?>
    <script type="text/javascript">
    jQuery(document).ready( function( $ ){
        var count_el = $('.product').length,
            a = 1;
        $('#list_upc_product .product').each(function (index, element) {

            setTimeout(function() {
                var data = {
                    action: 'mraupdateprice',
                    num_element: $(element).attr("data-num"),
                    product_id: $(element).html()
                };

                $.post( ajaxurl, data, function( response ){
                    $("#result_update").append( '<p>'+a+') '+response+'</p>' );
                    var ind = index+1;
                    if(ind == count_el) {
                        $("#load_image").html('<p style="color: #4CAF50; font-weight: 700;">Product loading is complete</p>');
                    }
                    a++;
                } );

            }, 50*index);
        });
    } );
    </script>
    <?php
    
}



?>
</div>