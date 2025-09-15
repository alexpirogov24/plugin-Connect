<?php
function mra_import_psql_orders_list_func() {
	$mysqli_read_write = mra_import_psql_db_connection_read_write();
    if (!$mysqli_read_write->connect_error) {

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

    	$result_order_product = $mysqli_read_write->query("SELECT * FROM order_products WHERE site_id=".$site_id_db);
        if ($result_order_product->num_rows!=0) {
            $row_order_product = $result_order_product->fetch_all(MYSQLI_ASSOC);
        } 


         ?>

        <style type="text/css">
        	
        </style>
        <div class='wrap'>
            <h1>MRA order-product list</h1>
            <?php if ($result_order_product->num_rows!=0) { ?>
            <table class="acquisitions_list_table">
                <thead>                
                    <tr>
                        <th>&nbsp;</th>
                        <th>Id db</th>
                        <th>Order WP</th>
                        <th>Po number</th>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Vendor note</th>
                        <th>Upc</th>
                        <th>Product id_wp</th>
                        <th>Quantity</th>
                        <th>Date of update</th>
                        <th>Date added</th>
                    </tr>
                </thead>
                <tbody>
                	<?php $i = 1; 
                        foreach ($row_order_product as $key => $order_product) { ?>
	                	<tr>
	                   		<td><?= $i ?></td>
	                   		<td><?= $order_product['id']; ?></td>
	                   		<td><a href="/wp-admin/post.php?post=<?= $order_product['id_order_wp']; ?>&action=edit"><?= $order_product['id_order_wp']; ?></a></td>
	                   		<td><?= $order_product['po_number']; ?></td>
	                   		<td><?= $order_product['status']; ?></td>
	                   		<td><?= $order_product['name']; ?></td>
	                   		<td><?= $order_product['vendor_note']; ?></td>
	                   		<td><?= $order_product['upc']; ?></td>
	                   		<td><?= $order_product['product_id_wp']; ?></td>
	                   		<td><?= $order_product['qty']; ?></td>
	                   		<td><?= $order_product['row_updated_at']; ?></td>
	                   		<td><?= $order_product['row_created_at']; ?></td>
	               		</tr>
               		<?php } ?>
            	</tbody>
        	</table>
            <?php } else { ?>
            <p>There are no shipped orders from this site in the Database yet!</p>
            <?php }?>
        </div>

    <?php }
    if(!$mysqli_read_write->connect_error)
        $mysqli_read_write->close();
}