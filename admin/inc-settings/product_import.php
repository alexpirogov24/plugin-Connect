<?php
function mra_import_psql_products_import_csv_func() {
	if(isset($_POST['fileup_nonce'])) {
		if(wp_verify_nonce( $_POST['fileup_nonce'], 'mra_import_csv' ) ){

			include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/update-product.php");

			// if ( ! function_exists( 'wp_handle_upload' ) )
			// 	require_once( ABSPATH . 'wp-admin/includes/file.php' );

			// $file = & $_FILES['mra_import_csv'];

			// $overrides = [ 'test_form' => false ];

			// $movefile = wp_handle_upload( $file, $overrides );

			// if ( $movefile && empty($movefile['error']) ) {
			// 	echo "The file was successfully uploaded.\n";
			// 	// var_dump( $movefile );


			// } else {
			// 	echo "Possible attacks when downloading a file!\n";
			// }

		}
		
	} else { ?>
		<a href="<?= MRA_IMPORT_PSQL_URL."/files/example_csv_file_upc.csv"; ?>">Example of uploading a csv file</a>
		<h4>Attach a csv file:</h4>
		<form id="mra_import_csv_form" enctype="multipart/form-data" action="/wp-admin/admin.php?page=mra_import_psql_product_import_csv" method="POST">
			<?php wp_nonce_field( 'mra_import_csv', 'fileup_nonce' ); ?>
			<input name="mra_import_csv" type="file" />
			<input type="submit" value="Import file" />
		</form>
	<?php }
}