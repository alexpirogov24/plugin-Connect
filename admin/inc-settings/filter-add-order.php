<?php
function mra_import_psql_add_order_func( $order_id ){
	$mysqli_read = mra_import_psql_db_connection_read();
    $mysqli_read_write = mra_import_psql_db_connection_read_write();
    
	// require (ABSPATH."wp-login.php");

	
	// $meta = get_post_meta($order_id);
	// print_r($order);
	// print_r( $meta );

    if (isset($_POST['type_query'])) $type_query = $_POST['type_query']; else $type_query = false;
	

    if (!$mysqli_read->connect_error or !$mysqli_read_write->connect_error) {
    	global $wpdb;

    	// $host = get_option('mra_import_psql_host');
     //    $port = get_option('mra_import_psql_port');
     //    $dbname = get_option('mra_import_psql_dbname');
     //    $user = get_option('mra_import_psql_user');
     //    $password = get_option('mra_import_psql_password');

     //    $mysqli = new mysqli($host, $user, $password, $dbname, $port);

        $name_site = get_bloginfo('name');
        $homepage_link = get_bloginfo('url');

        $result_site = $mysqli_read_write->query("SELECT * FROM sites WHERE name='".$name_site."' AND homepage_link='".$homepage_link."'");
        // var_dump($result_site);
	    if ($result_site->num_rows!=0) {
	        $rows_site = $result_site->fetch_assoc();
	        $site_id_db = $rows_site['id'];
	    } else {
	    	$result_site = $mysqli_read_write->query("INSERT INTO sites VALUES (NULL, '$name_site', '$homepage_link')");
	    	$site_id_db = $mysqli_read_write->insert_id;
	    }


	    $list_vendors_arr = array();
		if(get_option('list_vendors_connect')) {
		    $list_vendors_arr = unserialize( get_option('list_vendors_connect') );
		}
		if (!empty($list_vendors_arr)) {

			$list_vendors_access = array();
			if(get_option('list_vendors_access')) {
				$list_vendors_access = unserialize( get_option('list_vendors_access') );
			}

			$vendor_id = NULL;
			foreach ($list_vendors_access as $vendor => $vendors_access) {
				$result_vendor = $mysqli_read->query("SELECT * FROM vendors WHERE vendor='".$vendor."'");
                $row_vendor = $result_vendor->fetch_assoc();
                $vendor_id = $row_vendor['id'];
                foreach ($vendors_access as $method_slug => $access) {
                	$method_title = '';
                	if($method_slug == 'storeaccess') $method_title = "Ship to store Accessory";
                	elseif ($method_slug == 'dropaccess') $method_title = "Drop Ship Accessory";
                	elseif ($method_slug == 'storefirearm') $method_title = "Ship to store Firearm";
                	elseif ($method_slug == 'dropfirearm') $method_title = "Drop Ship Firearm";

                	foreach ($access as $field => $value) {
                		$result_access = $mysqli_read_write->query("SELECT * FROM site_accounts WHERE site_id='".$site_id_db."' AND vendor_id='".$vendor_id."' AND method_slug='".$method_slug."' AND field='".$field."'");
                		$row_access = $result_access->fetch_assoc();

					    if ($result_access->num_rows!=0) {
					    	if($row_access['value']!=$value) {
					    		$mysqli_read_write->query("UPDATE site_accounts SET value='".$value."' WHERE id=".$row_access['id']);
					    	}
				    	} else {
	                		$result_access = $mysqli_read_write->query("INSERT INTO site_accounts VALUES (NULL, '$site_id_db', '$vendor_id', '$vendor', '$method_slug', '$method_title', '$field', '$value')");
		    				$access_id_db = $mysqli_read_write->insert_id;
	    				}               		
                	}
                	
                }
			}

		}


  		// $order = new WC_Order($order_id);
		// $data = $order->get_data();

		// var_dump($data);

		$order = get_post($order_id);

		$id_wp = $order_id;
		// $parent_id = $data['parent_id'];
		$status = '';
		// $currency = $data['currency'];
		// $version = $data['version'];
		// echo $data['payment_method'];
		// echo $data['payment_method_title'];
		// echo $data['payment_method'];
		// echo $data['payment_method'];
		// $date_created_date = get_post_meta( $order_id, 'post_date', true );
		$date_created_date = $order->post_date;

		$date_modified_date = $order->post_modified;
		// $date_created_timestamp = $data['date_created']->getTimestamp();
		// $date_modified_timestamp = $data['date_modified']->getTimestamp();
		// echo $data['discount_total'];
		// echo $data['discount_tax'];
		$order_total = get_post_meta( $order_id, '_order_total', true );
		// echo $data['shipping_tax'];
		// echo $data['cart_tax'];
		$order_tax = get_post_meta( $order_id, '_order_tax', true );
		// echo $data['customer_id'];
		
		if($type_query) { 
			if ($type_query == 'insert') {
				$billing_first_name = get_post_meta( $order_id, '_billing_first_name', true );
				$billing_last_name = get_post_meta( $order_id, '_billing_last_name', true );
				$billing_company = get_post_meta( $order_id, '_billing_company', true );
				$billing_address_1 = get_post_meta( $order_id, '_billing_address_1', true );
				$billing_address_2 = get_post_meta( $order_id, '_billing_address_2', true );
				$billing_city = get_post_meta( $order_id, '_billing_city', true );
				$billing_state = get_post_meta( $order_id, '_billing_state', true );
				$billing_postcode = get_post_meta( $order_id, '_billing_postcode', true );
				$billing_country = get_post_meta( $order_id, '_billing_country', true );
				$billing_email = get_post_meta( $order_id, '_billing_email', true );
				$billing_phone = get_post_meta( $order_id, '_billing_phone', true );

				$shipping_first_name = get_post_meta( $order_id, '_shipping_first_name', true );
				$shipping_last_name = get_post_meta( $order_id, '_shipping_last_name', true );
				$shipping_company = get_post_meta( $order_id, '_shipping_company', true );
				$shipping_address_1 = get_post_meta( $order_id, '_shipping_address_1', true );
				$shipping_address_2 = get_post_meta( $order_id, '_shipping_address_2', true );
				$shipping_city = get_post_meta( $order_id, '_shipping_city', true );
				$shipping_state = get_post_meta( $order_id, '_shipping_state', true );
				$shipping_postcode = get_post_meta( $order_id, '_shipping_postcode', true );
				$shipping_country = get_post_meta( $order_id, '_shipping_country', true );

				if(get_post_meta( $order_id, '_shipping_fflno', true ))
					$shipping_fflno = get_post_meta( $order_id, '_shipping_fflno', true );
				else
					$shipping_fflno = '';

				if(get_post_meta( $order_id, '_shipping_fflno', true ))
					$shipping_fflexp = get_post_meta( $order_id, '_shipping_fflexp', true );
				else
					$shipping_fflexp = '';


				$result = $mysqli_read_write->query("INSERT INTO orders VALUES (
					NULL,
					'$site_id_db',
					'$id_wp',
					'$status',
					'$date_created_date',
					'$date_modified_date',
					'$billing_first_name',
					'$billing_last_name',
					'$billing_company',
					'$billing_address_1',
					'$billing_address_2',
					'$billing_city',
					'$billing_state',
					'$billing_postcode',
					'$billing_country',
					'$billing_email',
					'$billing_phone',
					'$shipping_first_name',
					'$shipping_last_name',
					'$shipping_company',
					'$shipping_address_1',
					'$shipping_address_2',
					'$shipping_city',
					'$shipping_state',
					'$shipping_postcode',
					'$shipping_country',
					'$order_total',
					'$order_tax',
					'$shipping_fflno',
					'$shipping_fflexp'
				)");

				// $result = $mysqli_read_write->query("INSERT INTO orders VALUES (
				// 	NULL,
				// 	'".$id_wp."',
				// 	'".$status."',
				// 	'".$date_created_date."',
				// 	'".$date_modified_date."',
				// 	'".$billing_first_name."',
				// 	'".$billing_last_name."',
				// 	'".$billing_company."',
				// 	'".$billing_address_1."',
				// 	'".$billing_address_2."',
				// 	'".$billing_city."',
				// 	'".$billing_state."',
				// 	'".$billing_postcode."',
				// 	'".$billing_country."',
				// 	'".$billing_email."',
				// 	'".$billing_phone."',
				// 	'".$shipping_first_name."',
				// 	'".$shipping_last_name."',
				// 	'".$shipping_company."',
				// 	'".$shipping_address_1."',
				// 	'".$shipping_address_2."',
				// 	'".$shipping_city."',
				// 	'".$shipping_state."',
				// 	'".$shipping_postcode."',
				// 	'".$shipping_country."',
				// 	'".$order_total."',
				// 	'".$order_tax."',
				// 	'".$shipping_fflno."',
				// 	'".$shipping_fflexp."'
				// )");

				$insert_id_db = $mysqli_read_write->insert_id;

				$vendor = '';

				$items_order = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."woocommerce_order_items WHERE order_item_type = 'line_item' AND order_id = ".$order_id);

				$list_items_db = array();
				$p = 0;
				foreach( $items_order as $item ){
					$item_array = array();
					$item_list_meta = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."woocommerce_order_itemmeta WHERE order_item_id = ".$item->order_item_id);
					foreach ($item_list_meta as $key => $meta) {
						$item_array[$meta->meta_key] = $meta->meta_value;
					}

					// var_dump($item_array);

					$product_id = $item_array['_product_id'];
					$qty = $item_array['_qty'];
					$subtotal = $item_array['_line_subtotal'];
					$total = $item_array['_line_total'];
					$row_created_at = date("Y-m-d H:i:s");

					$product = wc_get_product( $product_id );
					$upc = $product->get_attribute("pa_upc");
					$weight = get_post_meta( $product_id, '_weight', true );

					$result_item = $mysqli_read_write->query("INSERT INTO order_products (site_id, id_order, id_order_wp, status, name, upc, product_id_wp, qty, weight, subtotal, total, row_created_at ) VALUES (
						'$site_id_db',
						'$insert_id_db',
						'$order_id',
						'wp_submitted',
						'$item->order_item_name',
						'$upc',
						'$product_id',
						'$qty',
						'$weight',
						'$subtotal',
						'$total',
						'$row_created_at'
					)");

					$list_items_db[$p]['id_db'] = $mysqli_read_write->insert_id;

					$result_order_products = $mysqli_read_write->query("SELECT * FROM order_products WHERE id='".$list_items_db[$p]['id_db']."'");
		            $order_product = $result_order_products->fetch_assoc();

		            $list_items_db[$p]['id_prod_wp_id'] = $order_product['product_id_wp'];			

					// var_dump("INSERT INTO order_products VALUES (
					// 	NULL,
					// 	'".$insert_id_db."',
					// 	'".$order_id."',
					// 	'".$item->order_item_name."',
					// 	'".$item_array['_product_id']."',
					// 	'".$vendor."',
					// 	'".$item_array['_qty']."',
					// 	'".$item_array['_line_subtotal']."',
					// 	'".$item_array['_line_total']."'
					// )");

					$p++;
				}

			}	
		}

		if (isset($_POST['list_vendor'])) $list_vendor = $_POST['list_vendor']; else $list_vendor = false;
		if (isset($_POST['list_vendor_ids'])) $list_vendor_ids = $_POST['list_vendor_ids']; else $list_vendor_ids = false;
		if (isset($_POST['select_ckek'])) $select_ckek = $_POST['select_ckek']; else $select_ckek = false;
		if (isset($_POST['select_ckek_ids'])) $select_ckek_ids = $_POST['select_ckek_ids']; else $select_ckek_ids = false;
		

		// var_dump($list_vendor);
		// var_dump($list_vendor_ids);
		// var_dump($select_ckek);

		$a = 0;

		if($type_query) {
			if($type_query=='update') {
				$name_site_upd = get_bloginfo('name');
		        $homepage_link_up = get_bloginfo('url');

		        $site_id_db_up = NULL;
		        $result_site_upd = $mysqli_read_write->query("SELECT * FROM sites WHERE name='".$name_site_upd."' AND homepage_link='".$homepage_link_up."'");
		        
			    if ($result_site_upd->num_rows!=0) {
			        $rows_site_upd = $result_site_upd->fetch_assoc();
			        $site_id_db_up = $rows_site_upd['id'];
			    }

			    $result_site_order_upd = $mysqli_read_write->query("SELECT * FROM orders WHERE site_id='".$site_id_db_up."' AND id_wp='".$order_id."'");

			    if ($result_site_order_upd->num_rows!=0) {

			    	$rows_site_order_upd = $result_site_order_upd->fetch_assoc();
			    	$result_order_product_upd = $mysqli_read_write->query("SELECT * FROM order_product_qty_vendor WHERE order_id='".$rows_site_order_upd['id']."' AND order_id_wp='".$order_id."'");

			    	if ($result_order_product_upd->num_rows!=0) {
			    		$arr_order_product_qty_vendor = $result_order_product_upd->fetch_all(MYSQLI_ASSOC);
			    	}
			    }
			}
		}

		$i_arr = 0;
		while(isset($list_vendor[$a])) {

			$id_prod_ord_db = 0;

			foreach ($list_items_db as $key => $item) {
				if ($list_vendor_ids[$a]==$item['id_prod_wp_id']) {
					$id_prod_ord_db = $item['id_db'];

					break;
				}
			}

			if($type_query) {
				if($type_query=='insert') {
					$result_item = $mysqli_read_write->query("INSERT INTO order_product_qty_vendor VALUES (
						NULL,
						'$insert_id_db',
						'$id_prod_ord_db',
						'$order_id',
						'$list_vendor_ids[$a]',
						'$list_vendor[$a]',
						'$select_ckek[$a]'
					)");
				} elseif($type_query=='update') {
					if(isset($arr_order_product_qty_vendor)) {
						$result_item = $mysqli_read_write->query("UPDATE order_product_qty_vendor
						    SET
						        vendor_id = '".$list_vendor[$a]."',
						        method = '".$select_ckek[$a]."'
						    WHERE id = '".$arr_order_product_qty_vendor[$i_arr]['id']."'
						");
					}
				}

			} else {
				$result_item = $mysqli_read_write->query("INSERT INTO order_product_qty_vendor VALUES (
					NULL,
					'$insert_id_db',
					'$id_prod_ord_db',
					'$order_id',
					'$list_vendor_ids[$a]',
					'$list_vendor[$a]',
					'$select_ckek[$a]'
				)");	
			}

			
			
		$a++; $i_arr++; }


		return 'Order number '.$order_id.' has been added sent to the postrgess database. DB id: '.$insert_id_db;

		
	} else {
		return 'There was an error in adding order number '.$order_id.'.';
	}
	if(!$mysqli_read->connect_error)
        $mysqli_read->close();
    if(!$mysqli_read_write->connect_error)
        $mysqli_read_write->close();
}

add_filter( 'mra_import_psql_add_order_filter', 'mra_import_psql_add_order_func' );


add_action( 'wp_ajax_sendorderdbpostgress', 'sendorderdbpostgress_func' );
function sendorderdbpostgress_func(){
	$order_id = intval($_POST['order_id']);

	$return_order = apply_filters( 'mra_import_psql_add_order_filter', $order_id );

	echo $return_order;

	// $return_order = mra_import_psql_add_order_func($orderid);

}

add_action( 'woocommerce_after_order_itemmeta', 'new_code_item_filter_order', 5, 30 );
function new_code_item_filter_order( $item_id, $item, $product ) {
	// var_dump($item);

	$mysqli_read = mra_import_psql_db_connection_read();
    $mysqli_read_write = mra_import_psql_db_connection_read_write();

	$order_id = $item->get_order_id();

	if ( ! empty( $product ) && isset( $product ) ) {
		$list_vendors_arr = array();
	    if(get_option('list_vendors_connect')) {
	        $list_vendors_arr = unserialize( get_option('list_vendors_connect') );
	    }

	    $added_vendor_list = array();
	    if(get_option('added_vendor_list')) {
	        $added_vendor_list = unserialize( get_option('added_vendor_list') );
	    }

	    // var_dump($added_vendor_list);
	    

        $id_product = $product->get_id();
        $qty = $item->get_quantity();

        

        $cost_arr = get_post_meta( $id_product, '_wc_pos_outlet_cost', true );
        if ($cost_arr && !is_array($cost_arr))
        	$cost_arr = unserialize($cost_arr);

        $stock_arr = get_post_meta( $id_product, '_wc_pos_outlet_stock', true );
        if ($stock_arr && !is_array($stock_arr))
        	$stock_arr = unserialize($stock_arr);
        
        if(!empty($cost_arr)) {
	        foreach ($cost_arr as $key => $cost) {
	        	if($stock_arr[$key]==0)
	        		unset($cost_arr[$key]);
	        }
	    }

	    if(!empty($added_vendor_list)) {
	    	$arr_vendor = array();
	    	$n = 0;
	    	$isset_p_i = false;
	    	foreach ($added_vendor_list as $key => $vendor) {
	    		foreach ($cost_arr as $key1 => $cost) {
	    			$outlet = get_post($key1);
	    			$slug_outlet = $outlet->post_name;
	    			if($vendor['wpid']==$key1 ) {
	    				$arr_vendor[$n]['idbd'] = $vendor['dbid'];
	    				$arr_vendor[$n]['namevendor'] = $vendor['name'];
	    				$arr_vendor[$n]['cost'] = $cost;
	    				$arr_vendor[$n]['stock'] = $stock_arr[$key1];
		    			$n++;
		    		} elseif ($slug_outlet=='personal-inventory' && !$isset_p_i) {
		    			$isset_p_i = true;
		    			$arr_vendor[$n]['idbd'] = '0';
	    				$arr_vendor[$n]['namevendor'] = $outlet->post_title;
	    				$arr_vendor[$n]['cost'] = $cost;
	    				$arr_vendor[$n]['stock'] = $stock_arr[$key1];
	    				$n++;
		    		}
	    		}	    		
	    	}
	    }

	    // var_dump($arr_vendor);
		$row_order_product = NULL;
	    $result_order_product = $mysqli_read_write->query("SELECT * FROM order_product_qty_vendor WHERE order_id_wp='".$order_id."' AND id_product_wp='".$id_product."'");
		if ($result_order_product->num_rows!=0) {
	    	$row_order_product = $result_order_product->fetch_all(MYSQLI_ASSOC);
	    }

	    // var_dump($row_order_product);

	    ?>

        <div class="block_select_vendor"><p>Select a vendor:</p>
        	<?php $i=0;
            while ($i<$qty) { ?>
        	<div class="one_select_ckek">
	    		<div class="select"><select class="select_list_vendor" id="select_list_vendor_<?= $id_product ?>" data-id="<?= $id_product ?>" name="select_list_vendor_<?= $id_product ?>">
	    			<option value="none">------</option>
	    			<?php if (!empty($cost_arr)) { foreach ($arr_vendor as $key => $vendor) {
	    				if(isset($row_order_product[$i])) {
	    					if ($row_order_product[$i]['vendor_id'] == $vendor['idbd']) {
	    						$selected = 'selected';
	    					} else {
	    						$selected = '';
	    					}
	    				} else {
	    					$selected = '';
	    				}
	    				$outlet = get_post($key); ?>
	    			<option value="<?= $vendor['idbd'] ?>" <?= $selected ?>><?= $vendor['namevendor'] ?> ($<?= $vendor['cost'] ?>) (qty: <?= $vendor['stock'] ?>)</option>
	    			<?php } } ?>        			
	    		</select></div>
	    		<div class="chek">
	    			<?php if(isset($row_order_product[$i])) {
	    				$method = $row_order_product[$i]['method'];
	    			} else {
    					$method = '';
    				} ?>
	    			<?php if (has_term( 'ffl_firearm', 'product_tag', $id_product )){ ?>
	    				<div class="block_radio firearm">
					        <input type="radio" class="type_access" data-id="<?= $id_product ?>" id="ship_store_<?= $i ?>_<?= $id_product ?>" name="type_access_<?= $i ?>_<?= $id_product ?>" value="ship_store_firearm" <?php if($method=='ship_store_firearm') echo 'checked'; ?> />
					        <label for="ship_store_<?= $i ?>_<?= $id_product ?>">Ship to store</label>
					    </div>
					    <div class="block_radio firearm">
					        <input type="radio" class="type_access" data-id="<?= $id_product ?>" id="drop_ship_<?= $i ?>_<?= $id_product ?>" name="type_access_<?= $i ?>_<?= $id_product ?>" value="drop_ship_firearm" <?php if($method=='drop_ship_firearm') echo 'checked'; ?> />
					        <label for="drop_ship_<?= $i ?>_<?= $id_product ?>">Drop Ship</label>
					    </div>
					<?php } else { ?>
						<div class="block_radio accessory">
					        <input type="radio" class="type_access" data-id="<?= $id_product ?>" id="ship_store_<?= $i ?>_<?= $id_product ?>" name="type_access_<?= $i ?>_<?= $id_product ?>" value="ship_store_accessory" <?php if($method=='ship_store_accessory') echo 'checked'; ?> />
					        <label for="ship_store_<?= $i ?>_<?= $id_product ?>">Ship to store</label>
					    </div>
					    <div class="block_radio accessory">
					        <input type="radio" class="type_access" data-id="<?= $id_product ?>" id="drop_ship_<?= $i ?>_<?= $id_product ?>" name="type_access_<?= $i ?>_<?= $id_product ?>" value="drop_ship_accessory" <?php if($method=='drop_ship_accessory') echo 'checked'; ?> />
					        <label for="drop_ship_<?= $i ?>_<?= $id_product ?>">Drop Ship</label>
					    </div>
					<?php } ?>
				</div>
			</div>

			<?php $i++; } ?>

		</div>

    <?php //} else { 

    	$name_site = get_bloginfo('name');
        $homepage_link = get_bloginfo('url');

        $site_id_db = NULL;
        $result_site = $mysqli_read_write->query("SELECT * FROM sites WHERE name='".$name_site."' AND homepage_link='".$homepage_link."'");
        // var_dump($result_site);
	    if ($result_site->num_rows!=0) {
	        $rows_site = $result_site->fetch_assoc();
	        $site_id_db = $rows_site['id'];
	    }

	    $update = false;
	    $result_site_order = $mysqli_read_write->query("SELECT * FROM orders WHERE site_id='".$site_id_db."' AND id_wp='".$order_id."'");

	    if ($result_site_order->num_rows!=0) {

	    	$rows_site_order = $result_site_order->fetch_assoc();
	    	$result_order_product = $mysqli_read_write->query("SELECT * FROM order_product_qty_vendor WHERE order_id='".$rows_site_order['id']."' AND order_id_wp='".$order_id."'");

	    	if ($result_order_product->num_rows!=0)
	    		$update = true;
	    }

	    if ($update) {
	    	echo '<div class="hide_vendor_update_true" style="display:none !important;"></div>';
	    }

    } 

    	/* if ($update) { ?>
    		<script type="text/javascript">
	        jQuery(($) => {
        	  		$('#woocommerce-order-items .wc-order-totals-items').prepend('<div class="block_send_vendors_order" style="float:left;"><input type="hidden" id="type_query_send_order_bd" name="type_query" value="update"><input type="hidden" id="id_order_bd" name="id_order_bd" value="<?= $row_order_product[$i]['id'] ?>"><div id="btn_send_order_bd" class="hide">Update Order</div><div id="result_send_order"></div></div>');
	        });
        </script>

		<?php } else { ?>
			<script type="text/javascript">
	         jQuery(($) => {
	         	$('#woocommerce-order-items .wc-order-totals-items').prepend('<div class="block_send_vendors_order" style="float:left;"><input type="hidden" id="type_query_send_order_bd" name="type_query" value="insert"><input type="hidden" id="id_order_bd" name="id_order_bd" value=""><div id="btn_send_order_bd" class="hide">Submit Order</div><div id="result_send_order"></div></div>');
        		});
        </script>
			
		<?php } */

    if(!$mysqli_read->connect_error)
        $mysqli_read->close();
    if(!$mysqli_read_write->connect_error)
        $mysqli_read_write->close();    
}



add_action('admin_enqueue_scripts', 'update_outlet_cost_on_product_edit');

function update_outlet_cost_on_product_edit($hook) {
	if ($hook == 'post.php' || $hook == 'post-new.php') {
        global $post;

        // Check if current post is a product
        if (get_post_type($post) == 'product') {
        	if(get_post_meta( $post->ID, '_wc_pos_outlet_cost') == false) {

       //  		$host = get_option('mra_import_psql_host');
			    // $port = get_option('mra_import_psql_port');
			    // $dbname = get_option('mra_import_psql_dbname');
			    // $user = get_option('mra_import_psql_user');
			    // $password = get_option('mra_import_psql_password');

			    // $mysqli = new mysqli($host, $user, $password, $dbname, $port);

			    $mysqli_read = mra_import_psql_db_connection_read();

			    $product = wc_get_product( $post->ID );
				$upc = $product->get_attribute("pa_upc");

				$outlet_stock = get_post_meta( $post->ID, '_wc_pos_outlet_stock', true );
		        if ($outlet_stock && !is_array($outlet_stock))
		        	$outlet_stock = unserialize($outlet_stock);

		        $result_cost = $mysqli_read->query("SELECT * FROM cost_master WHERE upc=".$upc);
			    if ($result_cost) {
			        $rows_product_cost = $result_cost->fetch_all(MYSQLI_ASSOC);
			    }
		        
		        $outlet_cost = array();
		        $outlet_cost_stock = array();
		        $cost = 0;
		        $cost_stok_id = '';
		        if (get_option('added_vendor_list')) {
		            $rows_product_cost = is_array($rows_product_cost) ? $rows_product_cost : [];
						foreach ($rows_product_cost as $key => $product_cost_db) {
		                $added_vendor_meta_arr = unserialize( get_option('added_vendor_list') );
		                $cost_db = floatval($product_cost_db['cost']);
		                foreach ($added_vendor_meta_arr as $key => $vendor_wp) {
		                    if($product_cost_db['vendorid']==$vendor_wp['dbid']) {
		                        $outlet_cost[$vendor_wp['wpid']] = $cost_db;
		                        $outlet_cost_stock[$vendor_wp['wpid']] = $cost_db;
		                        if($outlet_stock[$vendor_wp['wpid']] != 0) {
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
		                    }

		                    if($cost_stok_id!='') {
		                        unset($outlet_cost_stock[$cost_stok_id]);
		                    }    
		                }
		            }
		            $outlet_cost_serialize = serialize($outlet_cost);
		            if ($outlet_cost_stock)
		                $cost = min($outlet_cost_stock);
		            
		            update_post_meta( $post->ID, '_wc_pos_outlet_cost', $outlet_cost_serialize );
		        }

		        if(!$mysqli_read->connect_error)
			        $mysqli_read->close();
        	}
        }
    }
}




add_action('woocommerce_new_order', 'set_outlet_cost_meta_for_order_items', 10, 1);

function set_outlet_cost_meta_for_order_items($order_id) {
    // Get the order object
    $order = wc_get_order($order_id);

    // Loop through order items
    foreach ($order->get_items() as $item_id => $item) {
        // Get the product ID
        $product_id = $item->get_product_id();

        // Define the value for _wc_pos_outlet_cost meta
        $outlet_cost_value = '121213123123123';

        // Update or add the post meta
        add_post_meta($product_id, '_wc_pos_outlet_cost', $outlet_cost_value);
    }
}

function custom_order_meta_box() {
    add_meta_box(
        'tracking_data_meta_box',
        'Tracking data',
        'tracking_data_meta_box_content',
        'shop_order',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'custom_order_meta_box');

// add_action( 'woocommerce_admin_order_data_after_order_details', 'add_tracking_data_meta_field' );
function tracking_data_meta_box_content( $order ){

	$order_id = $order->ID;

	// $host = get_option('mra_import_psql_host');
 //    $port = get_option('mra_import_psql_port');
 //    $dbname = get_option('mra_import_psql_dbname');
 //    $user = get_option('mra_import_psql_user');
 //    $password = get_option('mra_import_psql_password');

 //    $mysqli = new mysqli($host, $user, $password, $dbname, $port);

	$mysqli_read_write = mra_import_psql_db_connection_read_write();

    $name_site = get_bloginfo('name');
    $homepage_link = get_bloginfo('url');

    $result_site = $mysqli_read_write->query("SELECT * FROM sites WHERE name='".$name_site."' AND homepage_link='".$homepage_link."'");

    if ($result_site->num_rows!=0) {
        $rows_site = $result_site->fetch_assoc();
        $site_id_db = $rows_site['id'];
    } else {
    	$result_site = $mysqli_read_write->query("INSERT INTO sites VALUES (NULL, '$name_site', '$homepage_link')");
    	$site_id_db = $mysqli_read_write->insert_id;
    }

	$result_order_products = $mysqli_read_write->query("SELECT * FROM order_products WHERE site_id='".$site_id_db."' AND id_order_wp='".$order_id."'");

	if ($result_order_products->num_rows!=0) {

		$rows_order_products = $result_order_products->fetch_assoc();
        $id_order_vendor = $rows_order_products['id_order_vendor'];
	    $po_number = $rows_order_products['po_number'];
	    $status = $rows_order_products['status'];
	    $shipping_service = $rows_order_products['shipping_service'];
	    $tracking_number = $rows_order_products['tracking_number'];
	    $row_updated_at = $rows_order_products['row_updated_at'];

	    $tracking_data = array(
	        'id_order_vendor'   => $id_order_vendor,
	        'po_number'          => $po_number,
	        'status'             => $status,
	        'shipping_service'   => $shipping_service,
	        'tracking_number'    => $tracking_number,
	        'row_updated_at'        => $row_updated_at
	    );

	    // update_post_meta( $order_id, 'tracking_data', $tracking_data );

	    // echo '<div class="order_data_column">';
	    // echo '<h4>' . __( 'Tracking data', 'woocommerce' ) . '</h4>';
	    echo '<ul>';

	    foreach ( $tracking_data as $key => $value ) {
	        echo '<li><strong>' . esc_html( $key ) . ':</strong> ' . esc_html( $value ) . '</li>';
	    }

	    echo '</ul>';
	    // echo '</div>';
	} else {
		// echo '<div class="order_data_column">';
	    // echo '<h4>' . __( 'Tracking data', 'woocommerce' ) . '</h4>';
	    echo '<p>Tracking data on this order is missing.</p>';
	    // echo '</div>';
	}

    if(!$mysqli_read_write->connect_error)
        $mysqli_read_write->close();
}


add_action('admin_footer', 'add_btn_send_order');
function add_btn_send_order() { 
	global $pagenow, $post;
   if ($pagenow == 'post.php' || $pagenow == 'admin.php') { ?>
	<style type="text/css">
	    #btn_send_order_bd {
	    	padding: 5px 10px;
	    	color: #fff;
	    	border-radius: 3px;
	    	/*float: right;*/
	    	display: table;   	
	    }
	    #btn_send_order_bd.hide {
	    	background-color: #d9d9d9;
	    	cursor: auto;
	    }
	    #btn_send_order_bd.show {
	    	background-color: #0cb4ef;
	    	cursor: pointer; 
	    }
	    #result_send_order {
	    	clear: both;
	    	padding: 5px 10px;
	    	color: #c5b2bd;
	    }
	    .block_select_vendor {
	    	background-color: #f9f9f9;
		    border: 1px solid #e8e8e8;
		    border-radius: 3px;
		    padding: 10px;
		    margin-top: 12px;
		    display: table;
	    }
	    .one_select_ckek {
    	    display: table;
		    margin: 3px 0;
		    border: 1px solid #e9e9e9;
		    padding: 2px 5px;
		    border-radius: 3px;
	    }
	    .block_select_vendor p {
	    	margin: 2px 0;
	    	font-weight: 700;
	    }
	    .block_select_vendor .select {
	    	float: left;
	    	margin-right: 4px;
	    	max-width: 100px;
	    	padding-top: 8px;
	    	padding-right: 5px;
	    }
	    	.block_select_vendor .select select{
    		    min-width: 90px;
	    	}
	    .block_select_vendor .chek {
	    	float: right;
	    	margin-top: 5px;
	    }
	    .wc-order-item-serial-number + .block_select_vendor .chek .accessory {display: none;}
	    .wc-order-item-serial-number + .block_select_vendor .chek .firearm {display: block;}

	    .edit + .block_select_vendor .chek .firearm {display: none;}
	    .edit + .block_select_vendor .chek .accessory {display: block;}

	</style>
	<?php $list_vendors_arr = array();
    if(get_option('list_vendors_connect')) {
        $list_vendors_arr = unserialize( get_option('list_vendors_connect') );
    } ?>
    <script type="text/javascript">
        jQuery(($) => {

        	if ($('.hide_vendor_update_true').length > 0) {
			   $('#woocommerce-order-items .wc-order-totals-items').prepend('<div class="block_send_vendors_order"><input type="hidden" id="type_query_send_order_bd" name="type_query" value="update"><input type="hidden" id="id_order_bd" name="id_order_bd" value=""><div id="btn_send_order_bd" class="hide">Update Order</div><div id="result_send_order" style="text-align:left;"></div></div>');
			} else {
			    $('#woocommerce-order-items .wc-order-totals-items').prepend('<div class="block_send_vendors_order"><input type="hidden" id="type_query_send_order_bd" name="type_query" value="insert"><input type="hidden" id="id_order_bd" name="id_order_bd" value=""><div id="btn_send_order_bd" class="hide">Submit Order</div><div id="result_send_order" style="text-align:left;"></div></div>');
			}
        	

        	/* var id_product = $('#id_product_block_serial_number').val();
        	$('#order_line_items .item .name').append('<div class="block_select_vendor"><p>Select a vendor:</p>'+
        		'<div class="select"><select id="select_list_vendor_'+id_product+'" name="select_list_vendor_'+id_product+'">'+
        			<?php if (!empty($list_vendors_arr)) { foreach ($list_vendors_arr as $key => $vendor) { ?>
        			'<option value="<?= $vendor ?>"><?= $vendor ?></option>'+
        			<?php } } ?>        			
        		'</select></div>'+
        		'<div class="chek">'+
        			'<div class="accessory">'+
				        '<input type="radio" id="store_accessory" name="type_access" />'+
				        '<label for="store_accessory">Ship to store Accessory</label>'+
				    '</div>'+
				    '<div class="accessory">'+
				        '<input type="radio" id="drop_accessory" name="type_access" />'+
				        '<label for="drop_accessory">Drop Ship Accessory</label>'+
				    '</div>'+
				    '<div class="firearm">'+
				        '<input type="radio" id="store_firearm" name="type_access" />'+
				        '<label for="store_firearm">Ship to store Firearm</label>'+
				    '</div>'+
				    '<div class="firearm">'+
				        '<input type="radio" id="drop_firearm" name="type_access" />'+
				        '<label for="drop_firearm">Drop Ship Firearm</label>'+
				    '</div>'+
    			'</div>'+
    		'</div>'); */

			/* $('.one_select_ckek .type_access').change(function() {
				var result_chek = false;
	    		$('.one_select_ckek .chek').each(function() {

	    			if($(this).find('.type_access:checked').length==0) {
	    				result_chek = false;
	    				return false;
	    			} else {
	    				result_chek = true;
	    			}

	    		});

	    		var result_select = false;
	    		$('.one_select_ckek .select').each(function() {


	    			if($(this).find('.select_list_vendor').val()=='none') {
	    				result_select = false;
	    				return false;
	    			} else {
	    				result_select = true;
	    			}

	    		});

	    		if(result_chek && result_select) {
	    			$('#btn_send_order_bd').removeClass('hide');
	    			$('#btn_send_order_bd').addClass('show');
	    			$('#result_send_order').html('');
	    		} else {
	    			$('#btn_send_order_bd').removeClass('show');
	    			$('#btn_send_order_bd').addClass('hide');
	    		}
			}); */

			$('.one_select_ckek .type_access').change(function() {
			    var result_chek = true;
			    $('.one_select_ckek .chek').each(function() {
			        if ($(this).find('.type_access:checked').length == 0) {
			            result_chek = false;
			            return false; // Прекращаем цикл each
			        }
			    });

			    var result_select = true;
			    $('.one_select_ckek .select').each(function() {
			        if ($(this).find('.select_list_vendor').val() == 'none') {
			            result_select = false;
			            return false; // Прекращаем цикл each
			        }
			    });

			    if (result_chek && result_select) {
			        $('#btn_send_order_bd').removeClass('hide').addClass('show');
			        $('#result_send_order').html('');
			    } else {
			        $('#btn_send_order_bd').removeClass('show').addClass('hide');
			    }
			});



			$('.one_select_ckek .select_list_vendor').change(function() {
				// console.log();

				var result_chek = false;
	    		$('.one_select_ckek .chek').each(function() {

	    			if($(this).find('.type_access:checked').length==0) {
	    				result_chek = false;
	    				return false;
	    			} else {
	    				result_chek = true;
	    			}

	    		});

	    		var result_select = false;
	    		$('.one_select_ckek .select').each(function() {


	    			if($(this).find('.select_list_vendor').val()=='none') {
	    				result_select = false;
	    				return false;
	    			} else {
	    				result_select = true;
	    			}

	    		});

	    		if(result_chek && result_select) {
	    			$('#btn_send_order_bd').removeClass('hide');
	    			$('#btn_send_order_bd').addClass('show');
	    			$('#result_send_order').html('');
	    			// console.log('class: show');
	    		} else {
	    			$('#btn_send_order_bd').removeClass('show');
	    			$('#btn_send_order_bd').addClass('hide');
	    			// console.log('class: hide');
	    		}

	    		// if($(this).val()==0) {

	    		// }

			});
        	

			// $('#order_shipping_line_items').append('<tr><td></td><td><div id="btn_send_order_bd">Send Oreder DB Postgress</div><div id="result_send_order"></div></td></tr>');

            $(document).on('click', '#btn_send_order_bd', function() {

            	var select_list_vendor = new Array(),
            		select_list_vendor_ids = new Array(),
            		one_select_ckek = new Array(),
            		one_select_ckek_ids = new Array(),
            		i = 0,
            		a = 0;

            	$('.one_select_ckek .select').each(function() {
            		select_list_vendor_ids[i] = $(this).find('.select_list_vendor').attr('data-id');
            		select_list_vendor[i] = $(this).find('.select_list_vendor').val();
            		i++;
            	});

            	$('.one_select_ckek .chek').each(function() {
            		one_select_ckek_ids[a] = $(this).find('.type_access:checked').attr('data-id');
            		one_select_ckek[a] = $(this).find('.type_access:checked').val();
            		a++;
            	});

            	if($('#btn_send_order_bd').hasClass('hide')) {
            		$('#result_send_order').html('<p style="color:#f00;">Not all data is populated to submit the order to the postrgess database.</p>');
            	} else {
	            	var data = {
	                    action: 'sendorderdbpostgress',
	                    order_id: $("#post_ID").val(),
	                    list_vendor: select_list_vendor,
	                    list_vendor_ids: select_list_vendor_ids,
	                    select_ckek: one_select_ckek,
	                    select_ckek_ids: one_select_ckek_ids,
	                    type_query: $('#type_query_send_order_bd').val()
	                    // id_order_bd: $('#id_order_bd')
	                };

	                $.post( ajaxurl, data, function( response ){
	                    $("#result_send_order").append( '<p>'+response+'</p>' );
	                } );
                }
            });
		});
    </script>
<?php } } 