<?php
function mra_import_psql_logs_cron_func()
{ ?>
<style type="text/css">
	#log_file_content {
		background-color: #f5f5f5;
	    border: 1px solid #c6c6c6;
	    border-radius: 4px;
	    padding: 20px;
	    color: #616161;
	    line-height: 25px;
	    margin: 20px 20px 0 0;
	}
</style>
<h1>Make Ready Connect Logs cron</h1>
<?php

	// // var_dump(unserialize(get_post_meta( 87288, '_wc_pos_outlet_stock', true )));
	// // var_dump($stock = get_post_meta( 100016, '_stock', true ));

	// $wc_pos_outlet_stock = get_post_meta( 100016, '_wc_pos_outlet_stock', true );
	// $wc_pos_outlet_stock = unserialize($wc_pos_outlet_stock);

	

	// $outlet_stock_val = 0;
 //    foreach ($wc_pos_outlet_stock as $key => $value) {
 //    	$post = get_post($key);
 //    	if ($post && $post->post_type === 'pos_outlet') {
	//     	$outlet_stock_val = $outlet_stock_val+intval($value);	    	
	//     } else {
	//     	unset($wc_pos_outlet_stock[$key]);
	//     }
 //    }
 //    update_post_meta( 100016, '_wc_pos_outlet_stock', serialize($wc_pos_outlet_stock) );
 //    // var_dump($outlet_stock_val);

	$date_text = date("d_m_y");
	$log_file = file_get_contents(MRA_IMPORT_PSQL_DIR."logs/log_cron_update_product_data_".$date_text.".txt");
	
	$log_file = explode("\r\n", $log_file);

	echo '<div id="log_file_content">';
	foreach ($log_file as $key => $row) {
		echo $row.'<br>';
	}		
	echo '</div>';

}