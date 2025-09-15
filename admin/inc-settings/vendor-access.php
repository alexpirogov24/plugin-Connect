<?php
function mra_import_psql_vendor_access_func() {
	$mysqli_read = mra_import_psql_db_connection_read();
    $mysqli_read_write = mra_import_psql_db_connection_read_write();

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
				    		// var_dump($row_access['id']);
				    		$mysqli_read_write->query("UPDATE site_accounts SET value='".$value."' WHERE id=".$row_access['id']);
				    	}
			    	} else {
		    			// var_dump(456);
                		$result_access = $mysqli_read_write->query("INSERT INTO site_accounts VALUES (NULL, '$site_id_db', '$vendor_id', '$vendor', '$method_slug', '$method_title', '$field', '$value')");
	    				$access_id_db = $mysqli_read_write->insert_id;
    				}               		
            	}
            	
            }
		}

	}
?>
	<style type="text/css">
		span.name_vendor {
			text-transform: uppercase;
		}
		#block_vendor_access th {
		    padding: 4px 9px;
		    font-weight: 700;
		    border: 1px solid #eeee;
		    font-size: 14px;
		    text-align: center;
		}
		#block_vendor_access td {
		    padding: 4px 9px;
		    font-weight: 400;
		    border: 1px solid #eeee;
		    font-size: 14px;
		    text-align: center;
		}
		#block_vendor_access .btn_edit, .btns_save_changes a, .btns_save_changes  .save_changes {
		    display: block;
		    padding: 4px 9px;
		    border: 1px solid #e1e1e1;
		    border-radius: 3px;
		    background-color: #f2f2f4;
		    color: #000;
		    text-decoration: none;
		    cursor: pointer;
	    }
	    #block_vendor_access .btn_edit:hover, .btns_save_changes a:hover, .btns_save_changes  .save_changes:hover {
		    background-color: #e7e7e9;
		}
		.btns_save_changes {
			clear: both;
		}
		.btns_save_changes a, .btns_save_changes .save_changes {
			float: left;
			margin-right: 15px;
		}

		.vendor_access_block {
			border: 1px solid #c7c7c7;
		    border-radius: 4px;
		    padding: 8px 18px 12px;
		    display: table;
		    float: left;
		}
		.vendor_access_block:nth-child(1) {
			margin: 0 20px 20px 0;
		}
		.vendor_access_block:nth-child(2) {
			margin: 0 0 20px 0;
		}
		.vendor_access_block:nth-child(3) {
			clear: both;
			margin: 0 20px 20px 0;
		}
		.vendor_access_block:nth-child(4) {
			margin: 0 0 20px 0;
		}
			.vendor_access_block .title_block {
				width: 100%;
				font-size: 16px;
				font-weight: 700;
				padding: 5px 0;
			}
			.vendor_access_block .input_block {
				width: 100%;
			    margin-top: 0px;
			    padding: 5px 0;
			}
				.vendor_access_block .input_block .name {
					width: 100%;					
				}
				.vendor_access_block .input_block .input {
					margin-top: 3px;
					width: 100%;					
				}
	</style>
	<?php
	$mysqli_read = mra_import_psql_db_connection_read();
    if (!$mysqli_read->connect_error) {

		$list_vendors_arr = array();
		if(get_option('list_vendors_connect')) {
		    $list_vendors_arr = unserialize( get_option('list_vendors_connect') );
		}
		if (!empty($list_vendors_arr)) {

			$list_vendors_access = array();
			if(get_option('list_vendors_access')) {
				$list_vendors_access = unserialize( get_option('list_vendors_access') );
			}

			if(!isset($list_vendors_access[$_GET['vendor']]['storeaccess']['login'])) $list_vendors_access[$_GET['vendor']]['storeaccess']['login'] = '';
			if(!isset($list_vendors_access[$_GET['vendor']]['storeaccess']['pass'])) $list_vendors_access[$_GET['vendor']]['storeaccess']['pass'] = '';

			if(!isset($list_vendors_access[$_GET['vendor']]['dropaccess']['login'])) $list_vendors_access[$_GET['vendor']]['dropaccess']['login'] = '';
			if(!isset($list_vendors_access[$_GET['vendor']]['dropaccess']['pass'])) $list_vendors_access[$_GET['vendor']]['dropaccess']['pass'] = '';

			if(!isset($list_vendors_access[$_GET['vendor']]['storefirearm']['login'])) $list_vendors_access[$_GET['vendor']]['storefirearm']['login'] = '';
			if(!isset($list_vendors_access[$_GET['vendor']]['storefirearm']['pass'])) $list_vendors_access[$_GET['vendor']]['storefirearm']['pass'] = '';

			if(!isset($list_vendors_access[$_GET['vendor']]['dropfirearm']['login'])) $list_vendors_access[$_GET['vendor']]['dropfirearm']['login'] = '';
			if(!isset($list_vendors_access[$_GET['vendor']]['dropfirearm']['pass'])) $list_vendors_access[$_GET['vendor']]['dropfirearm']['pass'] = '';


			if (isset($_GET['edit_vendor_access']) && $_GET['edit_vendor_access']=="edit" && isset($_GET['vendor'])) {

				$mra_import_psql_enable_ffl = get_option('mra_import_psql_enable_ffl');	

				echo '<h1>Editing of the <span class="name_vendor">"'.$_GET['vendor'].'"</span> vendor</h1>';

				echo '<div class="list_vendor_access_block">
					<div class="vendor_access_block">
						<div class="title_block">Ship to store Accessory</div>
						<div class="input_block">
							<div class="name">FTP Login:</div>
							<div class="input"><input type="text" class="store_accessory_login" name="store_accessory_login" value="'.$list_vendors_access[$_GET['vendor']]['storeaccess']['login'].'" /></div>
						</div>
						<div class="input_block">
							<div class="name">FTP Pass:</div>
							<div class="input"><input type="text" class="store_accessory_pass" name="store_accessory_pass" value="'.$list_vendors_access[$_GET['vendor']]['storeaccess']['pass'].'" /></div>
						</div>
					</div>'; 

					if ($mra_import_psql_enable_ffl == 'on') {
					echo '<div class="vendor_access_block">
						<div class="title_block">Ship to store Firearm</div>
						<div class="input_block">
							<div class="name">FTP Login:</div>
							<div class="input"><input type="text" class="store_firearm_login" name="store_firearm_login" value="'.$list_vendors_access[$_GET['vendor']]['storefirearm']['login'].'" /></div>
						</div>
						<div class="input_block">
							<div class="name">FTP Pass:</div>
							<div class="input"><input type="text" class="store_firearm_pass" name="store_firearm_pass" value="'.$list_vendors_access[$_GET['vendor']]['storefirearm']['pass'].'" /></div>
						</div>
					</div>';
					}

					echo '<div class="vendor_access_block">
						<div class="title_block">Drop Ship Accessory</div>
						<div class="input_block">
							<div class="name">FTP Login:</div>
							<div class="input"><input type="text" class="drop_accessory_login" name="drop_accessory_login" value="'.$list_vendors_access[$_GET['vendor']]['dropaccess']['login'].'" /></div>
						</div>
						<div class="input_block">
							<div class="name">FTP Pass:</div>
							<div class="input"><input type="text" class="drop_accessory_pass" name="drop_accessory_pass" value="'.$list_vendors_access[$_GET['vendor']]['dropaccess']['pass'].'" /></div>
						</div>
					</div>';

					if ($mra_import_psql_enable_ffl == 'on') {
					echo '<div class="vendor_access_block">
						<div class="title_block">Drop Ship Firearm</div>
						<div class="input_block">
							<div class="name">FTP Login:</div>
							<div class="input"><input type="text" class="drop_firearm_login" name="drop_firearm_login" value="'.$list_vendors_access[$_GET['vendor']]['dropfirearm']['login'].'" /></div>
						</div>
						<div class="input_block">
							<div class="name">FTP Pass:</div>
							<div class="input"><input type="text" class="drop_firearm_pass" name="drop_firearm_pass" value="'.$list_vendors_access[$_GET['vendor']]['dropfirearm']['pass'].'" /></div>
						</div>
					</div>';
					}

				echo '</div>';

				echo '<div class="btns_save_changes">
					<a href="/wp-admin/admin.php?page=mra_import_psql_vendor_access">Back to list of accounts</a>
					<div class="save_changes" href="/wp-admin/admin.php?page=mra_import_psql_vendor_access&edit_vendor_access=update&vendor='.$_GET['vendor'].'">Save changes</div>
				</div>'; ?>

				<script type="text/javascript">
		            jQuery(document).ready( function( $ ){
		            	$(".btns_save_changes .save_changes").click(function(){
		                    var store_accessory_login = $('.vendor_access_block .store_accessory_login').val(),
		                    	store_accessory_pass = $('.vendor_access_block .store_accessory_pass').val(),
		                    	store_firearm_login = $('.vendor_access_block .store_firearm_login').val(),
		                    	store_firearm_pass = $('.vendor_access_block .store_firearm_pass').val(),
		                    	drop_accessory_login = $('.vendor_access_block .drop_accessory_login').val(),
		                    	drop_accessory_pass = $('.vendor_access_block .drop_accessory_pass').val(),
		                    	drop_firearm_login = $('.vendor_access_block .drop_firearm_login').val(),
		                    	drop_firearm_pass = $('.vendor_access_block .drop_firearm_pass').val();


		                    <?php if ($mra_import_psql_enable_ffl == 'on') { ?>
		                    $.redirect('/wp-admin/admin.php?page=mra_import_psql_vendor_access&edit_vendor_access=update&vendor=<?= $_GET['vendor'] ?>', {'store_accessory_login': store_accessory_login, 'store_accessory_pass': store_accessory_pass, 'store_firearm_login': store_firearm_login, 'store_firearm_pass': store_firearm_pass, 'drop_accessory_login': drop_accessory_login, 'drop_accessory_pass': drop_accessory_pass, 'drop_firearm_login': drop_firearm_login, 'drop_firearm_pass': drop_firearm_pass});
		                	<?php } else { ?>
	                		$.redirect('/wp-admin/admin.php?page=mra_import_psql_vendor_access&edit_vendor_access=update&vendor=<?= $_GET['vendor'] ?>', {'store_accessory_login': store_accessory_login, 'store_accessory_pass': store_accessory_pass, 'drop_accessory_login': drop_accessory_login, 'drop_accessory_pass': drop_accessory_pass});
	                		<?php } ?>
		                });
		            } );
		        </script>

			<?php } elseif (isset($_GET['edit_vendor_access']) && $_GET['edit_vendor_access']=="update") {

				echo '<h1><span class="name_vendor">"'.$_GET['vendor'].'"</span> Vendor Update</h1>';

				$mra_import_psql_enable_ffl = get_option('mra_import_psql_enable_ffl');

				$list_vendors_access = array();
				if(get_option('list_vendors_access')) {
					$list_vendors_access = unserialize( get_option('list_vendors_access') );
				}

				if(isset($_POST['store_accessory_login'])) $list_vendors_access[$_GET['vendor']]['storeaccess']['login'] = $_POST['store_accessory_login']; else $list_vendors_access[$_GET['vendor']]['storeaccess']['login'] = '';
				if(isset($_POST['store_accessory_pass'])) $list_vendors_access[$_GET['vendor']]['storeaccess']['pass'] = $_POST['store_accessory_pass']; else $list_vendors_access[$_GET['vendor']]['storeaccess']['pass'] = '';
			
				if ($mra_import_psql_enable_ffl == 'on') {				
					if(isset($_POST['store_firearm_login'])) $list_vendors_access[$_GET['vendor']]['storefirearm']['login'] = $_POST['store_firearm_login']; else $list_vendors_access[$_GET['vendor']]['storefirearm']['login'] = '';
					if(isset($_POST['store_firearm_pass'])) $list_vendors_access[$_GET['vendor']]['storefirearm']['pass'] = $_POST['store_firearm_pass']; else $list_vendors_access[$_GET['vendor']]['storefirearm']['pass'] = '';
				}
				
				if(isset($_POST['drop_accessory_login'])) $list_vendors_access[$_GET['vendor']]['dropaccess']['login'] = $_POST['drop_accessory_login']; else $list_vendors_access[$_GET['vendor']]['dropaccess']['login'] = '';
				if(isset($_POST['drop_accessory_pass'])) $list_vendors_access[$_GET['vendor']]['dropaccess']['pass'] = $_POST['drop_accessory_pass']; else $list_vendors_access[$_GET['vendor']]['dropaccess']['pass'] = '';

				if ($mra_import_psql_enable_ffl == 'on') {
					if(isset($_POST['drop_firearm_login'])) $list_vendors_access[$_GET['vendor']]['dropfirearm']['login'] = $_POST['drop_firearm_login']; else $list_vendors_access[$_GET['vendor']]['dropfirearm']['login'] = '';
					if(isset($_POST['drop_firearm_pass'])) $list_vendors_access[$_GET['vendor']]['dropfirearm']['pass'] = $_POST['drop_firearm_pass']; else $list_vendors_access[$_GET['vendor']]['dropfirearm']['pass'] = '';
				}

				$list_vendors_access_serialize = serialize($list_vendors_access);

                $result = update_option( 'list_vendors_access', $list_vendors_access_serialize );


				echo '<p style="color:#00be20;"><span class="name_vendor">"'.$_GET['vendor'].'"</span> vendor accounts have been changed</p>';

				echo '<div class="btns_save_changes">
					<a href="/wp-admin/admin.php?page=mra_import_psql_vendor_access&edit_vendor_access=edit&vendor='.$_GET['vendor'].'">Go back to edit vendor</a>
					<a href="/wp-admin/admin.php?page=mra_import_psql_vendor_access">Go to list of vendors</a>
				</div>';

			} else {

				echo '<h1>Vendor access</h1>';

				$mra_import_psql_enable_ffl = get_option('mra_import_psql_enable_ffl');			    

				if ($list_vendors_arr) {
					echo '<table id="block_vendor_access">
						<thead>                
			                <tr>
			                    <th>Vendor</th>
			                    <th>Ship to store Accessory</th>';
			                    
			                    if ($mra_import_psql_enable_ffl == 'on') {
			                    echo '<th>Ship to store Firearm</th>';
			                    }

			                    echo '<th>Drop Ship Accessory</th>';
			                    
			                    if ($mra_import_psql_enable_ffl == 'on') {
			                    echo '<th>Drop Ship Firearm</th>';
			                    }

			                    echo '<th>&nbsp;</th>
			                </tr>
			            </thead>
			            <tbody>';

			            $list_vendors_access = array();
						if(get_option('list_vendors_access')) {
							$list_vendors_access = unserialize( get_option('list_vendors_access') );
						}
						// var_dump($list_vendors_access);
						foreach( $list_vendors_arr as $vendor ){

							$not_filled_login_text = '<span style="color:#a80000;">Login is not filled</span>';
							$not_filled_pass_text = '<span style="color:#a80000;">Password not filled</span>';

							if(!isset($list_vendors_access[$vendor]['storeaccess']['login']) || $list_vendors_access[$vendor]['storeaccess']['login'] =='') $list_vendors_access[$vendor]['storeaccess']['login'] = $not_filled_login_text;
							if(!isset($list_vendors_access[$vendor]['storeaccess']['pass']) || $list_vendors_access[$vendor]['storeaccess']['pass'] =='') $list_vendors_access[$vendor]['storeaccess']['pass'] = $not_filled_pass_text;

							if ($mra_import_psql_enable_ffl == 'on') {
								if(!isset($list_vendors_access[$vendor]['dropaccess']['login']) || $list_vendors_access[$vendor]['dropaccess']['login'] =='') $list_vendors_access[$vendor]['dropaccess']['login'] = $not_filled_login_text;
								if(!isset($list_vendors_access[$vendor]['dropaccess']['pass']) || $list_vendors_access[$vendor]['dropaccess']['pass'] =='') $list_vendors_access[$vendor]['dropaccess']['pass'] = $not_filled_pass_text;
							}

							if(!isset($list_vendors_access[$vendor]['storefirearm']['login']) || $list_vendors_access[$vendor]['storefirearm']['login']=='') $list_vendors_access[$vendor]['storefirearm']['login'] = $not_filled_login_text;
							if(!isset($list_vendors_access[$vendor]['storefirearm']['pass']) || $list_vendors_access[$vendor]['storefirearm']['pass']=='') $list_vendors_access[$vendor]['storefirearm']['pass'] = $not_filled_pass_text;

							if ($mra_import_psql_enable_ffl == 'on') {
								if(!isset($list_vendors_access[$vendor]['dropfirearm']['login']) || $list_vendors_access[$vendor]['dropfirearm']['login']=='') $list_vendors_access[$vendor]['dropfirearm']['login'] = $not_filled_login_text;
								if(!isset($list_vendors_access[$vendor]['dropfirearm']['pass']) || $list_vendors_access[$vendor]['dropfirearm']['pass']=='') $list_vendors_access[$vendor]['dropfirearm']['pass'] = $not_filled_pass_text;
							}

							echo '<tr>
			                    <td><span class="name_vendor">'.$vendor.'</span></td>
			                    <td><p><strong>ftp login:</strong> '.$list_vendors_access[$vendor]['storeaccess']['login'].'</p><p><strong>ftp pass:</strong> '.$list_vendors_access[$vendor]['storeaccess']['pass'].'</p></td>';

			                    if ($mra_import_psql_enable_ffl == 'on') {
			                    echo '<td><p><strong>ftp login:</strong> '.$list_vendors_access[$vendor]['storefirearm']['login'].'</p><p><strong>ftp pass:</strong> '.$list_vendors_access[$vendor]['storefirearm']['pass'].'</p></td>';
			                	}

			                    echo '<td><p><strong>ftp login:</strong> '.$list_vendors_access[$vendor]['dropaccess']['login'].'</p><p><strong>ftp pass:</strong> '.$list_vendors_access[$vendor]['dropaccess']['pass'].'</p></td>';

			                    if ($mra_import_psql_enable_ffl == 'on') {
			                    echo '<td><p><strong>ftp login:</strong> '.$list_vendors_access[$vendor]['dropfirearm']['login'].'</p><p><strong>ftp pass:</strong> '.$list_vendors_access[$vendor]['dropfirearm']['pass'].'</p></td>';
			                	}

			                    echo '<td><a class="btn_edit" href="/wp-admin/admin.php?page=mra_import_psql_vendor_access&edit_vendor_access=edit&vendor='.$vendor.'">Edit</a></td>
		            	  	<tr>';
						}

					echo '</tbody>
					</table>';	
					wp_reset_postdata();
				}

			}

		} else {
			echo '<p>No vendor is connected. Connect 1 or more vendors.</p>';
		}

	} else {
        echo '<p style="color:#f00;"><strong>Database connection error.</strong></p>';
        echo '<p>'.$mysqli_read->connect_error.'</p>';
    }
}