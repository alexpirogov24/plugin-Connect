<?php
function mra_import_psql_products_func()
{
    

    $mysqli_read = mra_import_psql_db_connection_read();
    $mysqli_read_write = mra_import_psql_db_connection_read_write();
    if (!$mysqli_read->connect_error or !$mysqli_read_write->connect_error) {

        if (isset($_POST['updprod']) && $_POST['updprod']!='') {
           include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/update-product.php");
        } else {
            $list_vendors_arr = array();
            if(get_option('list_vendors_connect')) {
                $list_vendors_arr = unserialize( get_option('list_vendors_connect') );
            }

            if (!empty($list_vendors_arr)) {

                if(isset($_GET['vendor'])) {
                    $vendor = isset($_GET['vendor']) ? $_GET['vendor'] : '';
                } else {
                    $i=0; foreach ($list_vendors_arr as $value) {
                        if(isset($value)) {
                            $vendor = $value;
                            break;
                        }
                    $i++; }
                }

                // $host = get_option('mra_import_psql_host');
                // $port = get_option('mra_import_psql_port');
                // $dbname = get_option('mra_import_psql_dbname');
                // $user = get_option('mra_import_psql_user');
                // $password = get_option('mra_import_psql_password');

                // $mysqli = new mysqli($host, $user, $password, $dbname, $port);

                $result_vendor = $mysqli_read->query("SELECT * FROM vendors WHERE vendor='".$vendor."'");
                $row_vendor = $result_vendor->fetch_assoc();

                $count_view_products = 100;
        		if(get_option('count_view_products')) {
        			if (isset($_GET['count_view_products'])) {
        				$count_view_products = isset($_GET['count_view_products']) ? $_GET['count_view_products'] : '';
        			} else {
        				$count_view_products = get_option('count_view_products');
        			}			
        		} else {
        			if (isset($_GET['count_view_products'])) {
        				$count_view_products = isset($_GET['count_view_products']) ? $_GET['count_view_products'] : '';
        			}
        		}

                $add_sql = '';
                $filter_arr = array();
                $search_arr = array();

                if(get_option('filter_product')) {
                    if (isset($_GET['filter']) && $_GET['filter']=='send') {
                        $filter_arr['has_map'] = isset($_GET['has_map']) ? $_GET['has_map'] : '';
                        $filter_arr['has_image'] = isset($_GET['has_image']) ? $_GET['has_image'] : '';
                        $filter_arr['curr_in_stock'] = isset($_GET['curr_in_stock']) ? $_GET['curr_in_stock'] : '';
                        $filter_arr['category'] = isset($_GET['category']) ? $_GET['category'] : '';
                        $filter_arr['manufacturer'] = isset($_GET['manufacturer']) ? $_GET['manufacturer'] : '';
                        update_option( 'filter_product', serialize($filter_arr) );
                        if(isset($filter_arr['has_map']) && $filter_arr['has_map']=="true")
                            $add_sql .= ' AND mc.MAP != 0';
                        if(isset($filter_arr['has_image']) && $filter_arr['has_image']=="true")
                            $add_sql .= ' AND (ium.s3_URL != "s3://mwm-vendor-images/0.jpg" OR ium.s3_URL != "")';
                        if(isset($filter_arr['curr_in_stock']) && $filter_arr['curr_in_stock']=="true")
                            $add_sql .= ' AND im.quantity > 0';
                        if(isset($filter_arr['category']) && $filter_arr['category']!="not_selected" && $filter_arr['category']!=NULL && $filter_arr['category']!='') {
                            $add_sql .= ' AND (cum.category_id = "'.$filter_arr['category'].'" OR c.parent_id = "'.$filter_arr['category'].'")'; // $add_sql .= ' AND cum.category_id = "'.$filter_arr['category'].'"';
                        }
                        if(isset($filter_arr['manufacturer']) && $filter_arr['manufacturer']!="not_selected")
                            $add_sql .= ' AND mc.Manufacturer = "'.$filter_arr['manufacturer'].'"';


                    } elseif(isset($_GET['filter']) && $_GET['filter']=='reset') {
                        delete_option('filter_product');
                    } else {
                        $filter_arr = unserialize(get_option('filter_product'));
                        if(isset($filter_arr['has_map']) && $filter_arr['has_map']=="true")
                            $add_sql .= ' AND mc.MAP != 0';
                        if(isset($filter_arr['has_image']) && $filter_arr['has_image']=="true")
                            $add_sql .= ' AND (ium.s3_URL != "s3://mwm-vendor-images/0.jpg" OR ium.s3_URL != "")';
                        if(isset($filter_arr['curr_in_stock']) && $filter_arr['curr_in_stock']=="true")
                            $add_sql .= ' AND im.quantity > 0';
                        if(isset($filter_arr['category']) && $filter_arr['category']!="not_selected" && $filter_arr['category']!=NULL && $filter_arr['category']!='') {
                            $add_sql .= ' AND (cum.category_id = "'.$filter_arr['category'].'" OR c.parent_id = "'.$filter_arr['category'].'")'; // $add_sql .= ' AND cum.category_id = "'.$filter_arr['category'].'"';
                        }
                        if(isset($filter_arr['manufacturer']) && $filter_arr['manufacturer']!="not_selected")
                            $add_sql .= ' AND mc.Manufacturer = "'.$filter_arr['manufacturer'].'"';
                    }
                } else {
                    if (isset($_GET['filter']) && $_GET['filter']=='send') {
                        $filter_arr['has_map'] = isset($_GET['has_map']) ? $_GET['has_map'] : '';
                        $filter_arr['has_image'] = isset($_GET['has_image']) ? $_GET['has_image'] : '';
                        $filter_arr['curr_in_stock'] = isset($_GET['curr_in_stock']) ? $_GET['curr_in_stock'] : '';
                        $filter_arr['category'] = isset($_GET['category']) ? $_GET['category'] : '';
                        $filter_arr['manufacturer'] = isset($_GET['manufacturer']) ? $_GET['manufacturer'] : '';
                        add_option( 'filter_product', serialize($filter_arr) );
                        if(isset($filter_arr['has_map']) && $filter_arr['has_map']=="true")
                            $add_sql .= ' AND mc.MAP != 0';
                        if(isset($filter_arr['has_image']) && $filter_arr['has_image']=="true")
                            $add_sql .= ' AND (ium.s3_URL != "s3://mwm-vendor-images/0.jpg" OR ium.s3_URL != "")';
                        if(isset($filter_arr['curr_in_stock']) && $filter_arr['curr_in_stock']=="true")
                            $add_sql .= ' AND im.quantity > 0';
                        if(isset($filter_arr['category']) && $filter_arr['category']!="not_selected" && $filter_arr['category']!=NULL && $filter_arr['category']!='') {
                            $add_sql .= ' AND (cum.category_id = "'.$filter_arr['category'].'" OR c.parent_id = "'.$filter_arr['category'].'")'; // $add_sql .= ' AND cum.category_id = "'.$filter_arr['category'].'"';
                        }
                        if(isset($filter_arr['manufacturer']) && $filter_arr['manufacturer']!="not_selected")
                            $add_sql .= ' AND mc.Manufacturer = "'.$filter_arr['manufacturer'].'"';
                    }
                }

                $mra_import_psql_enable_ffl = get_option('mra_import_psql_enable_ffl');
                if ($mra_import_psql_enable_ffl != 'on')
                    $add_sql .= ' AND mc.Firearm = 0';

                $mra_import_psql_enable_nfa_products = get_option('mra_import_psql_enable_nfa_products');
                if ($mra_import_psql_enable_nfa_products != 'on')
                    $add_sql .= ' AND (cum.category_id != "188" OR c.parent_id != "188")';

                if(get_option('search_product')) {
                    if (isset($_GET['search']) && $_GET['search']=='send') {
                        $search_arr['title'] = isset($_GET['search_title']) ? $_GET['search_title'] : '';
                        $search_arr['upc'] = isset($_GET['search_upc']) ? $_GET['search_upc'] : '';                
                        update_option( 'search_product', serialize($search_arr) );
                        if(isset($search_arr['upc']) && $search_arr['upc']!='')
                            $add_sql .= ' AND mc.UPC LIKE \''.$search_arr['upc'].'\'';
                        if(isset($search_arr['title']) && $search_arr['title']!='')
                            $add_sql .= ' AND mc.Title LIKE "%'.$search_arr['title'].'%"';
                    } elseif(isset($_GET['search']) && $_GET['search']=='reset') {
                        delete_option('search_product');
                    } else {
                        $search_arr = unserialize(get_option('search_product'));
                        if(isset($search_arr['upc']) && $search_arr['upc']!='')
                            $add_sql .= ' AND mc.UPC LIKE \''.$search_arr['upc'].'\'';
                        if(isset($search_arr['title']) && $search_arr['title']!='')
                            $add_sql .= ' AND mc.Title LIKE "%'.$search_arr['title'].'%"';
                    }
                } else {
                    if (isset($_GET['search']) && $_GET['search']=='send') {
                        $search_arr['title'] = isset($_GET['search_title']) ? $_GET['search_title'] : '';
                        $search_arr['upc'] = isset($_GET['search_upc']) ? $_GET['search_upc'] : '';
                        add_option( 'search_product', serialize($search_arr) );
                        if(isset($search_arr['upc']) && $search_arr['upc']!='')
                            $add_sql .= ' AND mc.UPC LIKE \''.$search_arr['upc'].'\'';
                        if(isset($search_arr['title']) && $search_arr['title']!='')
                            $add_sql .= ' AND mc.Title LIKE "%'.$search_arr['title'].'%"';
                    }
                }

                if(isset($_GET['beginning_page'])) {
                	$beginning_page = $_GET['beginning_page']+1;
                    $pages = "LIMIT ".$beginning_page.", ".$count_view_products;
                } else {
                    $pages = "LIMIT 0,".$count_view_products;
                }

                /* 
                $mra_psql_products = new WP_Query;
                $products_exclude = $mra_psql_products->query( [
                    'posts_per_page' => -1,
                    'post_type' => 'product',
                    'tax_query' => array(
                        'relation' => 'OR',
                        array(
                            'taxonomy' => 'pa_upc',
                            'operator' => 'EXISTS',
                        )
                    )
                ] );

                $exclude_upc_sql = "";
                if ($products_exclude) {
                    $exclude_upc = '';
                    $e = 0;
                    foreach ($products_exclude as $key => $product_exc) {
                        $term_list_exc = wp_get_post_terms( $product_exc->ID, 'pa_upc', array( 'fields' => 'names' ) );
                        $upc_product_exc = $term_list_exc[0];
                        if ($e==0) 
                            $exclude_upc .= "'".$upc_product_exc."'";
                        else
                            $exclude_upc .= ", '".$upc_product_exc."'";
                        $e++;
                    }
                    $exclude_upc_sql = " AND master_catalog.UPC NOT IN (".$exclude_upc.")";
                }

                // var_dump($exclude_upc_sql);

                // die();

                $result = $mysqli_read->query("SELECT * FROM master_catalog, cost_master, inventory_master, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=cost_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND inventory_master.vendorid=".$row_vendor['id'].$add_sql.$exclude_upc_sql." ".$pages);
                */

                // $result = $mysqli_read->query("SELECT * FROM master_catalog, cost_master, inventory_master, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=cost_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND NOT EXISTS (SELECT upc FROM added_products WHERE site_id=3) AND inventory_master.vendorid=".$row_vendor['id'].$add_sql." ".$pages);

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

                 

                if($row_vendor['id']!="5") {

                    // $result = $mysqli_read->query("SELECT *
                    // FROM master_catalog mc 
                    //     LEFT JOIN cost_master cm 
                    //         ON mc.UPC = cm.upc 
                    //     LEFT JOIN inventory_master im 
                    //         ON mc.UPC = im.upc
                    //     LEFT JOIN category_upc_mapping cum 
                    //         ON mc.UPC = cum.upc
                    //     LEFT JOIN image_upc_mapping ium 
                    //         ON mc.UPC = ium.UPC
                    // WHERE 
                    //     cm.vendorid = ".$row_vendor['id']."
                    //     AND im.vendorid = ".$row_vendor['id']."
                    //     AND ium.vendor = '".$row_vendor['vendor']."'".$add_sql."
                    //     AND NOT EXISTS (
                    //         SELECT upc 
                    //             FROM added_products ap 
                    //                 WHERE cm.upc = ap.upc
                    //                     AND ap.site_id = ".$site_id_db."
                    //     )
                    // ".$pages);

                    if (isset($_GET['search'])) {
                        if($_GET['search']=='send') {
                            $sql_vend_is_not_nul = "";
                        } else {
                            $sql_vend_is_not_nul = "AND (ium.vendor = '".$row_vendor['vendor']."' OR ium.vendor IS NOT NULL)";
                        }
                    } else {
                        $sql_vend_is_not_nul = "AND (ium.vendor = '".$row_vendor['vendor']."' OR ium.vendor IS NOT NULL)";
                    }

                    if (get_option('mra_import_psql_hide_out_of_catalog_connect') == 'on') {
                        $cost_quantity = " AND cm.cost != 0 AND im.quantity != 0";
                    } else {
                        $cost_quantity = "";
                    }

                    $result = $mysqli_read->query("SELECT 
                        mc.*, 
                        cm.*, 
                        im.*, 
                        cum.*, 
                        MAX(CASE 
                            WHEN ium.vendor = '".$row_vendor['vendor']."' THEN ium.vendor 
                            ELSE COALESCE(ium.vendor, 'default_non_null_vendor')
                        END) AS vendor
                    FROM master_catalog mc
                    LEFT JOIN cost_master cm ON mc.UPC = cm.upc
                    LEFT JOIN inventory_master im ON mc.UPC = im.upc
                    LEFT JOIN category_upc_mapping cum ON mc.UPC = cum.upc
                    LEFT JOIN image_upc_mapping ium ON mc.UPC = ium.`UPC`
                    LEFT JOIN categories c ON cum.category_id = c.id 
                    WHERE cm.vendorid = ".$row_vendor['id'].$cost_quantity."
                      AND im.vendorid = ".$row_vendor['id'].$add_sql."
                    ".$sql_vend_is_not_nul."
                      AND NOT EXISTS (
                        SELECT upc
                        FROM added_products ap
                        WHERE cm.upc = ap.upc
                          AND ap.site_id = ".$site_id_db."
                      )
                    GROUP BY mc.UPC, cm.upc, im.upc, cum.upc
                    ".$pages);

                    // var_dump("SELECT 
                    //     mc.*, 
                    //     cm.*, 
                    //     im.*, 
                    //     cum.*, 
                    //     MAX(CASE 
                    //         WHEN ium.vendor = '".$row_vendor['vendor']."' THEN ium.vendor 
                    //         ELSE COALESCE(ium.vendor, 'default_non_null_vendor')
                    //     END) AS vendor
                    // FROM master_catalog mc
                    // LEFT JOIN cost_master cm ON mc.UPC = cm.upc
                    // LEFT JOIN inventory_master im ON mc.UPC = im.upc
                    // LEFT JOIN category_upc_mapping cum ON mc.UPC = cum.upc
                    // LEFT JOIN image_upc_mapping ium ON mc.UPC = ium.`UPC`
                    // LEFT JOIN categories c ON cum.category_id = c.id 
                    // WHERE cm.vendorid = ".$row_vendor['id'].$cost_quantity."
                    //   AND im.vendorid = ".$row_vendor['id'].$add_sql."
                    // ".$sql_vend_is_not_nul."
                    //   AND NOT EXISTS (
                    //     SELECT upc
                    //     FROM added_products ap
                    //     WHERE cm.upc = ap.upc
                    //       AND ap.site_id = ".$site_id_db."
                    //   )
                    // GROUP BY mc.UPC, cm.upc, im.upc, cum.upc
                    // ".$pages);

                } else {

                    // $result = $mysqli_read->query("SELECT *
                    // FROM master_catalog mc 
                    //     LEFT JOIN cost_master cm 
                    //         ON mc.UPC = cm.upc 
                    //     LEFT JOIN inventory_master im 
                    //         ON mc.UPC = im.upc
                    //     LEFT JOIN category_upc_mapping cum 
                    //         ON mc.UPC = cum.upc 
                    //     LEFT JOIN (
                    //         SELECT * 
                    //         FROM image_upc_mapping 
                    //         GROUP BY UPC
                    //     ) ium ON ium.UPC = mc.UPC
                    // WHERE cm.vendorid = 5 
                    //     AND im.vendorid = 5".$add_sql."
                    //     AND NOT EXISTS ( 
                    //         SELECT upc FROM added_products ap WHERE cm.upc = ap.upc AND ap.site_id = ".$site_id_db." 
                    //     )
                    // ".$pages);

                    if (get_option('mra_import_psql_hide_out_of_catalog_connect') == 'on') {
                        $cost_quantity2 = " AND cm.cost != 0 AND im.quantity != 0";
                    } else {
                        $cost_quantity2 = "";
                    }

                    $result = $mysqli_read->query("SELECT 
                        mc.*, 
                        cm.*, 
                        im.*, 
                        cum.*, 
                        MAX(CASE 
                            WHEN ium.vendor = 'davidsons' THEN ium.vendor 
                            ELSE COALESCE(ium.vendor, 'default_non_null_vendor')
                        END) AS vendor
                    FROM master_catalog mc
                    LEFT JOIN cost_master cm ON mc.UPC = cm.upc
                    LEFT JOIN inventory_master im ON mc.UPC = im.upc
                    LEFT JOIN category_upc_mapping cum ON mc.UPC = cum.upc
                    LEFT JOIN image_upc_mapping ium ON mc.UPC = ium.`UPC`
                    LEFT JOIN categories c ON cum.category_id = c.id
                    WHERE cm.vendorid = 5".$cost_quantity2."
                      AND im.vendorid = 5".$add_sql."
                      AND (ium.vendor = 'davidsons' OR ium.vendor IS NOT NULL)
                      AND NOT EXISTS (
                        SELECT upc
                        FROM added_products ap
                        WHERE cm.upc = ap.upc
                          AND ap.site_id = ".$site_id_db."
                      )
                    GROUP BY mc.UPC, cm.upc, im.upc, cum.upc
                    ".$pages);
                }

                

                // AND ium.vendor = '".$row_vendor['vendor']."'


                // var_dump("SELECT *
                // FROM master_catalog mc 
                //     LEFT JOIN cost_master cm 
                //         ON mc.UPC = cm.upc 
                //     LEFT JOIN inventory_master im 
                //         ON mc.UPC = im.upc 
                //     LEFT JOIN image_upc_mapping ium 
                //         ON mc.UPC = ium.UPC 
                // WHERE 
                //     cm.vendorid = ".$row_vendor['id']."
                //     AND im.vendorid = ".$row_vendor['id']."
                //     AND ium.vendor = '".$row_vendor['vendor']."'".$add_sql."
                //     AND NOT EXISTS (
                //         SELECT upc 
                //             FROM added_products ap 
                //                 WHERE cm.upc = ap.upc
                //                     AND ap.site_id = ".$site_id_db."
                //     )
                // ".$pages);

                // var_dump("SELECT *
                // FROM master_catalog mc 
                //     LEFT JOIN cost_master cm 
                //         ON mc.UPC = cm.upc 
                //     LEFT JOIN inventory_master im 
                //         ON mc.UPC = im.upc 
                //     LEFT JOIN image_upc_mapping ium 
                //         ON mc.UPC = ium.UPC 
                // WHERE 
                //     cm.vendorid = ".$row_vendor['id']."
                //     AND im.vendorid = ".$row_vendor['id']."
                //     AND ium.vendor = '".$row_vendor['vendor']."'".$add_sql."
                //     AND NOT EXISTS (
                //         SELECT upc 
                //             FROM added_products ap 
                //                 WHERE cm.upc = ap.upc
                //                     AND ap.site_id = 3
                //     )
                // ".$pages);

                // var_dump("SELECT * FROM master_catalog, cost_master, inventory_master, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=cost_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND NOT EXISTS (SELECT upc FROM added_products WHERE site_id=3) AND inventory_master.vendorid=".$row_vendor['id'].$add_sql." ".$pages); 

                // die();

                
                if ($result) {
                    $row_products = $result->fetch_all(MYSQLI_ASSOC);
                } 




                // var_dump("SELECT * FROM master_catalog, inventory_master WHERE master_catalog.UPC=inventory_master.UPC AND inventory_master.vendorid=".$row_vendor['id']." ".$pages);

                // $count_products = $result_count->fetch_assoc();
                // $count_products = intval($count_products['COUNT(*)']);

                // var_dump($count_products);
                // echo "<br>";
                // $count_page = round($count_products/100);
                // var_dump($count_page);
                // var_dump($row_products);

                

            } else {

            }
            ?>
            <style type="text/css">
                .wrap h3 {
                    float: left;
                    display: table;
                    margin: 35px 0px 0px;
                }
            	.img_product {
            		width: 100%;
            		max-width: 120px;
            		max-height: 120px;
            	}
                .top_menu_vendors {
                    padding: 0;
                    margin: 20px 0 0 0;
                    width: 100%;
                    display: table;
                }
                    .top_menu_vendors .elem {                
                        display: block;
                        float: left;
                    }
                        .top_menu_vendors .elem .link_vendor {
                            padding: 7px 15px;
                            border-top: 1px solid #e1e1e1;
                            border-bottom: 1px solid #e1e1e1;
                            border-left: 1px solid #e1e1e1;
                            border-right: 1px solid #e1e1e1;
                            border-top-left-radius: 3px;
                            border-top-right-radius: 3px;
                            text-transform: uppercase;
                            background-color: #f2f2f4;
                            color: #000;
                            text-decoration: none;
                        }

                        .top_menu_vendors .elem .link_vendor.active {
                            background-color: #f1cb40;
                        }
                
                .block_btns_update {
                    display: table;
                    width: 100%;
                }
                .btns_update_product {
                    display: table;
                    float: right;
                    margin-bottom: 10px;
                }
                    .btns_update_product .text_btns_update {
                        float: left;
                        margin-right: 5px;
                        font-size: 16px;
                        line-height: 32px;
                    }
                    .btns_update_product .btn {
                        float: left;
                        display: block;
                        padding: 7px 15px;
                        margin: 0 3px;
                        border: 1px solid #e1e1e1;
                        border-radius: 3px;
                        background-color: #f2f2f4;
                        color: #000;
                        text-decoration: none;
                        cursor: pointer;
                    }
                    .btns_update_product .btn:hover {
                        background-color: #e7e7e9;
                    }

                ul.select2-results__options {
                    height: 350px;
                    overflow: scroll;
                }

            </style>
            <div class='wrap'>
                <!-- <h1>Products</h1> -->
                <?php if (!empty($list_vendors_arr)) { ?>
                
                    <ul class="top_menu_vendors">
                    <?php foreach ($list_vendors_arr as $key => $one_vendor) { 
                        if ($one_vendor == $vendor)
                            $active = "active";
                        else
                            $active = "";

                        // $mra_import_psql_enable_rsr = get_option('mra_import_psql_enable_rsr');
                        
                        // if($one_vendor!='rsr') { ?>

                        <li class="elem"><a href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $one_vendor; ?>&filter=reset&search=reset" class="link_vendor <?= $active ?>"><?= $one_vendor ?></a></li>

                        <?php /*} else { 
                            if ($mra_import_psql_enable_rsr == 'on') { ?>

                           <li class="elem"><a href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $one_vendor; ?>&filter=reset&search=reset" class="link_vendor <?= $active ?>"><?= $one_vendor ?></a></li>     

                        <?php }
                        } */
                    } ?>
                    </ul>

                    <?php if ($result) { ?>
                        <h3><span style="text-transform: uppercase;"><?= $vendor ?></span> Catalog</h3>
                        <?php include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/pagenav.php"); ?>
                        <?php include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/filter.php"); ?>
                        <div class="block_btns_update">
                            <div class="btns_update_product">
                                <div class="text_btns_update">Action for marked products:</div>
                                <div class="btn update_product" data-action="update">Add</div>
                                <!-- <div class="btn add_product" data-action="add">Add</div>
                                <div class="btn delete_product" data-action="delete">Delete</div> -->
                            </div>
                        </div>
                        <table class="acquisitions_list_table">
                            <thead>                
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>UPC</th>
                                    <th>Quantity</th>
                                    <th>Cost</th>
                                    <th>Category</th>
                                    <th>Manufacturer</th>
                                    <th>MAP</th>
                                    <th>Weight</th>
                                    <?php if ($mra_import_psql_enable_ffl == 'on') { ?>
                                    <th>Firearm</th>
                                    <?php } ?>
                                    <th>Model</th>
                                    <th><input type="checkbox" class="all_checked" name="all_checked"></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(isset($_GET['beginning_page']))
                                $i = $_GET['beginning_page']+1;
                            else
                                $i = 1; 
                            foreach ($row_products as $key => $product) {

                                // $category = '';
                                // $result_category = $mysqli_read->query("SELECT cat.id, cat.name, cat.parent_id
                                // FROM category_upc_mapping cam
                                // INNER JOIN categories cat ON cam.category_id = cat.id
                                // WHERE cam.upc = ".$product['UPC']);
                                // if ($result_category->num_rows!=0) {
                                //     $rows_category = $result_category->fetch_assoc();
                                //     $category = $rows_category['name'];
                                // }





                                // $img = '';
                                // $result_img = $mysqli_read->query("SELECT *
                                // FROM image_upc_mapping
                                // WHERE UPC = ".$product['UPC']);
                                // if ($result_img->num_rows!=0) {
                                //     $rows_img = $result_img->fetch_assoc();
                                //     $img = $rows_img['Image Name'];
                                // }




                                // $args = array(
                                //     'post_type' => array('product'),
                                //     'tax_query' => array(
                                //         'relation' => 'OR',
                                //         array(
                                //             'taxonomy' => 'pa_upc',
                                //             'field' => 'name',
                                //             'terms' => $product['UPC'],
                                //             'operator' => 'IN',
                                //         )
                                //     )
                                // );
                                // $query = new WP_Query($args);
                                // // var_dump($query->posts);

                                // if (empty($query->posts)) {
                                 ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <?php /* ?><td><img class="img_product" src="<?= 'https://dme5m5gvjikvl.cloudfront.net/'.$img  ?>" /></td><?php */ ?>
                                    <td style="text-align: center;"><img class="img_product" data-upc="<?= $product['UPC'] ?>" src="<?= MRA_IMPORT_PSQL_URL ?>img/loader.gif" /></td>
                                    <td><?= $product['Title'] ?></td>
                                    <td><?= $product['UPC'] ?></td>
                                    <td><?= $product['quantity'] ?></td>
                                    <td><?= $product['cost'] ?></td>
                                    <td><?= $product['Category'] ?></td>
                                    <td><?= $product['Manufacturer'] ?></td>
                                    <td><?= $product['MAP'] ?></td>
                                    <td><?= $product['Weight'] ?></td>
                                    <?php if ($mra_import_psql_enable_ffl == 'on') { ?>
                                    <td><?php if($product['Firearm']==1) echo "yes"; else echo "no"; ?></td>
                                    <?php } ?>
                                    <td><?= $product['Model'] ?></td>
                                    <td><input type="checkbox" class="product product_<?= $product['UPC'] ?>" name="products[<?= $product['UPC'] ?>]" data-id="<?= $product['UPC'] ?>"></td>
                                </tr> 
                               
                                <?php $i++; //} 
                             } ?>
                            </tbody>

                        </table>
                        <?php include(MRA_IMPORT_PSQL_DIR . "admin/inc-settings/pagenav.php"); ?>
                    <?php } else { ?>
                        <p>No values were found for the vendor "<?= $vendor ?>".</p>
                    <?php }
                } else { ?>
                    <p>No vendor is connected. Connect 1 or more vendors.</p>
                <?php } ?>
            </div>
            <script type="text/javascript">
            jQuery(document).ready( function( $ ){
                $(".acquisitions_list_table .all_checked").click(function(){
                    if($(this).is(":checked")) { 
                        $(".acquisitions_list_table .product").prop('checked', true);
                    } else {
                        $(".acquisitions_list_table .product").prop('checked', false);
                    }
                });

                $(".block_btns_update .btn").click(function(){
                    var upd_btn_prod = $(this).attr("data-action"),
                        products = new Array(),
                        i = 0;

                    $('.acquisitions_list_table input.product').each(function (index, element) {
                        if($(element).is(":checked")) {
                            products[i] = $(element).attr("data-id");
                            i++;
                        }
                    });

                    $.redirect('/wp-admin/admin.php?page=mra_import_psql_products', {'updprod': upd_btn_prod, 'products': products});
                });

                $('.select_category').select2();
                $('.select_manufacture').select2();

            } );
            </script>
        <?php }


    } else {
        echo '<p style="color:#f00;"><strong>Database connection error.</strong></p>';
        if($mysqli_read->connect_error)
            echo '<p>'.$mysqli_read->connect_error.'</p>';
        if($mysqli_read_write->connect_error)
            echo '<p>'.$mysqli_read_write->connect_error.'</p>';
    }

    if(!$mysqli_read->connect_error)
        $mysqli_read->close();
    if(!$mysqli_read_write->connect_error)
        $mysqli_read_write->close();
}

function ali_enqueue_admin_scripts($hook) {
    wp_enqueue_script('ali-lazy-load', MRA_IMPORT_PSQL_URL.'js/lazy-load.js', array('jquery'), null, true);
    wp_localize_script('ali-lazy-load', 'ali_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('admin_enqueue_scripts', 'ali_enqueue_admin_scripts');


/*
function ali_load_images() {
    $mysqli_read = mra_import_psql_db_connection_read();
    if (!$mysqli_read->connect_error) {
        $product_upcs = isset($_POST['product_upcs']) ? $_POST['product_upcs'] : array();
        $images = array();
        
        foreach ($product_upcs as $upc) {
            $img = MRA_IMPORT_PSQL_URL . 'img/noimage.png';
            $result_img = $mysqli_read->query("SELECT *
            FROM image_upc_mapping
            WHERE UPC = ".$upc);
            if ($result_img->num_rows!=0) {
                $rows_img = $result_img->fetch_assoc();
                $img = $rows_img['Image Name'];
            }
            //$result = $wpdb->get_row($wpdb->prepare("SELECT `Image Name` FROM image_upc_mapping WHERE UPC = %s", $upc));
            $images[$upc] = 'https://dme5m5gvjikvl.cloudfront.net/'.$img;
        }

        wp_send_json_success($images);
    }
}
add_action('wp_ajax_ali_load_images', 'ali_load_images');
add_action('wp_ajax_nopriv_ali_load_images', 'ali_load_images');


function ali_admin_inline_script() {
    echo "<script>
    jQuery(document).ready(function($) {
        let productUPCs = [];
        $('.acquisitions_list_table tr .img_product').each(function() {
            productUPCs.push($(this).data('upc'));
        });
        
        if (productUPCs.length > 0) {
            $.post(ali_ajax.ajax_url, {
                action: 'ali_load_images',
                product_upcs: productUPCs
            }, function(response) {
                if (response.success) {
                    $('.acquisitions_list_table tr .img_product').each(function() {
                        let productUPC = $(this).data('upc');
                        if (response.data[productUPC]) {
                            $(this).attr('src', response.data[productUPC]);
                        }
                    });
                }
            });
        }
    });
    </script>";
}
add_action('admin_footer', 'ali_admin_inline_script', 20); */

function ali_load_image() {
    $mysqli_read = mra_import_psql_db_connection_read();
    $upc = isset($_POST['upc']) ? $_POST['upc'] : '';
    
    if (!$upc) {
        wp_send_json_error(['message' => 'UPC not provided']);
    }

    $img = MRA_IMPORT_PSQL_URL . 'img/noimage.png';
    $result_img = $mysqli_read->query("SELECT *
    FROM image_upc_mapping
    WHERE UPC = ".$upc);
    if ($result_img->num_rows!=0) {
        $rows_img = $result_img->fetch_assoc();
        $img = $rows_img['Image Name'];
    }
    //$result = $wpdb->get_row($wpdb->prepare("SELECT `Image Name` FROM image_upc_mapping WHERE UPC = %s", $upc));
    // $images[$upc] = 'https://dme5m5gvjikvl.cloudfront.net/'.$img;

    // $result = $wpdb->get_row($wpdb->prepare("SELECT `Image Name` FROM image_upc_mapping WHERE UPC = %s", $upc));
    $image_url = 'https://dme5m5gvjikvl.cloudfront.net/'.$img;

    if(!$mysqli_read->connect_error)
        $mysqli_read->close();
    
    wp_send_json_success(['upc' => $upc, 'image_url' => $image_url]);
}
add_action('wp_ajax_ali_load_image', 'ali_load_image');


function ali_admin_inline_script() {
    echo "<script>
    jQuery(document).ready(function($) {
        let images = $('.acquisitions_list_table .img_product');
        function loadNextImage(index) {
            if (index >= images.length) return;
            let img = $(images[index]);
            let upc = img.data('upc');
            
            $.post(ali_ajax.ajax_url, {
                action: 'ali_load_image',
                upc: upc
            }, function(response) {
                if (response.success) {
                    img.attr('src', response.data.image_url);
                }
                loadNextImage(index + 1);
            });
        }
        loadNextImage(0);
    });
    </script>";
}
add_action('admin_footer', 'ali_admin_inline_script', 20);
