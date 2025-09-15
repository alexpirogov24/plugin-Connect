<?php

function mra_import_psql_vendors_func()
{
    // var_dump(unserialize(get_option('added_vendor_list')));
    // var_dump(unserialize(get_option('list_vendors_connect')));
    $mysqli_read = mra_import_psql_db_connection_read();
    $mysqli_read_write = mra_import_psql_db_connection_read_write();
    if (!$mysqli_read->connect_error or !$mysqli_read_write->connect_error) {

        $data_arr = array();

        $delimiter = ';';

        $csv = file_get_contents(MRA_IMPORT_PSQL_DIR.'files/vendors_list.csv');
        $rows = explode(PHP_EOL, $csv);

        foreach ($rows as $row)
        {
          $data_arr[] = explode($delimiter, $row);
        }

        // $vendor[0] Vendor Name
        // $vendor[1] Logo URL
        // $vendor[2] More Info Button Link
        // $vendor[3] Become a Dealer Button Link
        // $vendor[4] Number of Products
        // $vendor[5] Allows Dropship
        // $vendor[6] Minimum Order
        // $vendor[7] Ships To
        // $vendor[8] Supplier Type
        // $vendor[9] DB id
        // $vendor[10] DB name

        // $host = get_option('mra_import_psql_host');
        // $port = get_option('mra_import_psql_port');
        // $dbname = get_option('mra_import_psql_dbname');
        // $user = get_option('mra_import_psql_user');
        // $password = get_option('mra_import_psql_password');

        // delete_option('added_vendor_list');
        // die();


        if (!empty($_GET['vendor_connect']) && !empty($_GET['status_vendor_connect'])) {
            $outlet_id = '';
            if($_GET['status_vendor_connect']=='integrate') {
                
                $list_vendors_arr = array();
                if(get_option('list_vendors_connect')) {
                    $list_vendors_arr = unserialize( get_option('list_vendors_connect') );
                    if(!in_array($_GET['vendor_connect'], $list_vendors_arr)) {
                        array_push($list_vendors_arr, $_GET['vendor_connect']);
                        $list_vendors = serialize($list_vendors_arr);
                        $result = update_option( 'list_vendors_connect', $list_vendors );
                    }
                } else {
                    $list_vendors_arr[0] = $_GET['vendor_connect'];
                    $list_vendors = serialize($list_vendors_arr);
                    $result = add_option( 'list_vendors_connect', $list_vendors );
                }


                $pos_outlet_list = get_posts( array(
                    'numberposts' => -1,
                    'post_type'   => 'pos_outlet',
                ) );
                foreach( $pos_outlet_list as $post ){
                    setup_postdata( $post );
                    if(strtolower($post->post_title)==$_GET['vendor_connect'] or $post->post_name==$_GET['vendor_connect']) {
                        $outlet_id = $post->ID;
                        break;
                    }
                }
                wp_reset_postdata();

                if($outlet_id == '') {
                    // echo 111111;
                    $added_vendor_arr = array();
                    $one_vendor_csv = array();                    

                    foreach ($data_arr as $key => $vendor) {
                        $name_vendor = trim($vendor[10]);
                        if($name_vendor==trim($_GET['vendor_connect'])) {
                            $one_vendor_csv[0]['name'] = $vendor[0];
                            $one_vendor_csv[0]['dbname'] = $name_vendor;
                            $one_vendor_csv[0]['dbid'] = $vendor[9];
                            break;
                        }
                    }

                    $outlet_post_data = [
                        'post_title'    => $one_vendor_csv[0]['name'],
                        'post_status'   => 'publish',
                        'post_type'     => 'pos_outlet',
                    ];
                    $outlet_post_id = wp_insert_post(  wp_slash( $outlet_post_data ) );
                    $one_vendor_csv[0]['wpid'] = $outlet_post_id;

                    if(get_option('added_vendor_list')) {
                        // echo 222222;
                        $search_vendor = false;
                        $added_vendor_arr = unserialize( get_option('added_vendor_list') );
                        foreach ($added_vendor_arr as $key => $one_vendor) {
                            if ($one_vendor_csv[0]['wpid']==$one_vendor['wpid']) {
                                $search_vendor = true;
                                break;
                            }
                        }
                        if(!$search_vendor) {
                            // echo 2222444555;
                            $added_vendor_arr[] = $one_vendor_csv[0];
                            $serialize_added_vendor_arr = serialize($added_vendor_arr);
                            $result_added_vendor = update_option( 'added_vendor_list', $serialize_added_vendor_arr );
                        }
                    } else {
                        // echo 333333;
                        $added_vendor_arr = $one_vendor_csv;
                        $serialize_added_vendor_arr = serialize($added_vendor_arr);
                        $result_added_vendor = add_option( 'added_vendor_list', $serialize_added_vendor_arr );
                    }
                } else {
                    // echo 44444;
                    $added_vendor_arr = array();
                    $one_vendor_csv = array();                    

                    foreach ($data_arr as $key => $vendor) {
                        $name_vendor = trim($vendor[10]);
                        if($name_vendor==trim($_GET['vendor_connect'])) {
                            $one_vendor_csv[0]['name'] = $vendor[0];
                            $one_vendor_csv[0]['dbname'] = $name_vendor;
                            $one_vendor_csv[0]['dbid'] = $vendor[9];
                            break;
                        }
                    }
                    $one_vendor_csv[0]['wpid'] = $outlet_id;


                    if(get_option('added_vendor_list')) {
                        // echo 555555;
                        $search_vendor = false;
                        $added_vendor_arr = unserialize( get_option('added_vendor_list') );
                        foreach ($added_vendor_arr as $key => $one_vendor) {
                            if ($one_vendor_csv[0]['wpid']==$one_vendor['wpid']) {
                                $search_vendor = true;
                                break;
                            }
                        }
                        if(!$search_vendor) {
                            // echo 55533344;
                            $added_vendor_arr[] = $one_vendor_csv[0];
                            $serialize_added_vendor_arr = serialize($added_vendor_arr);
                            $result_added_vendor = update_option( 'added_vendor_list', $serialize_added_vendor_arr );
                        }
                    } else {
                        // echo 77777;
                        $added_vendor_arr = $one_vendor_csv;
                        $serialize_added_vendor_arr = serialize($added_vendor_arr);
                        $result_added_vendor = add_option( 'added_vendor_list', $serialize_added_vendor_arr );
                    }
                }

            } elseif ($_GET['status_vendor_connect']=='disconnect') {

                if(get_option('list_vendors_connect')) {
                    $list_vendors_arr = unserialize( get_option('list_vendors_connect') );
                    $key = array_search($_GET['vendor_connect'], $list_vendors_arr);
                    unset($list_vendors_arr[$key]);
                    $list_vendors = serialize($list_vendors_arr);
                    $result = update_option( 'list_vendors_connect', $list_vendors );
                }


                // $deleted = wp_delete_post(1);

            }

        }

        // $added_vendor_arr = unserialize( get_option('added_vendor_list') );
        // var_dump($added_vendor_arr);
        
        ?>
        <style type="text/css">
            .list_vendors {

            }
            .list_vendors .vendor {
                display: table;
                width: 95%;
                margin: 10px 0;
                padding: 1.5%;
                border: 1px solid #eee;
                border-radius: 3px;
            }
            .list_vendors .vendor .img {
                min-height: 100px;
                width: 13.5%;
                float: left;
                padding: 20px;
                background-position: center;
                background-size: contain;
                background-repeat: no-repeat;
            }
            .list_vendors .vendor .desc {
                width: 32%;
                float: left;
                padding: 20px;
            }
                .list_vendors .vendor .desc .row {
                    width: 100%;
                }
                .list_vendors .vendor .desc .title {
                    font-size: 20px;
                    margin-bottom: 10px;
                }

            .list_vendors .vendor .btns {
                width: 43.5%;
                float: left;
                padding: 35px 0;
            }
                .list_vendors .vendor .btns a {
                    float: left;
                    display: table;
                    padding: 10px 15px;
                    font-size: 16px;
                    text-transform: uppercase;
                    border: 1px solid #eee;
                    border-radius: 3px;
                    margin: 0 5px;
                    text-decoration: none;
                    color: #000;
                }
                .list_vendors .vendor .btns a.integrate {
                    background-color: #f1cb40;
                }
                .list_vendors .vendor .btns a.disconnect {
                    background-color: #f2763f;
                }

        </style>
        <div class='wrap'>
            <h1>Integrated Vendors</h1>
            <div class="list_vendors">
            <?php $list_vendors_connect_arr = array();
            if(get_option('list_vendors_connect')) {
                $list_vendors_connect_arr = unserialize( get_option('list_vendors_connect') );
            }

            foreach ($data_arr as $key => $vendor) { ?>
                <?php if($key!=0) {             
                $vendor_name_db = trim($vendor[10]);
                ?>
                <div class="vendor">
                    <div class="img" style="background-image: url('<?= $vendor[1] ?>');"></div>
                    <div class="desc">
                        <div class="row title"><?php if($vendor[0] == 'Sportssouth') echo "Sports South"; else echo $vendor[0]; ?></div>
                        <div class="row products"><strong>Number of Products:</strong> <?= $vendor[4] ?></div>
                        <div class="row all_drop"><strong>Allows Dropship:</strong> <?= $vendor[5] ?></div>
                        <div class="row min_order"><strong>Minimum Order:</strong> <?= $vendor[6] ?></div>
                        <div class="row ships_to"><strong>Ships To:</strong> <?= $vendor[7] ?></div>
                        <div class="row s_cat"><strong>Supplier Type:</strong> <?= $vendor[8] ?></div>
                    </div>
                    <div class="btns">
                        <?php if(!empty($list_vendors_connect_arr)) {
                            if (in_array($vendor_name_db, $list_vendors_connect_arr)) {  ?>
                                <a href="/wp-admin/admin.php?page=mr_connect&vendor_connect=<?= $vendor_name_db ?>&status_vendor_connect=disconnect" class="disconnect">Disconnect</a>
                            <?php } else { ?>
                                <a href="/wp-admin/admin.php?page=mr_connect&vendor_connect=<?= $vendor_name_db ?>&status_vendor_connect=integrate" class="integrate">Integrate</a>
                            <?php } ?>
                        <?php } else { ?>
                            <a href="/wp-admin/admin.php?page=mr_connect&vendor_connect=<?= $vendor_name_db ?>&status_vendor_connect=integrate" class="integrate">Integrate</a>
                        <?php } ?>

                        <a href="<?= $vendor[2] ?>" target="_blank" class="more">More info</a>
                        <a href="<?= $vendor[3] ?>" target="_blank" class="visit">Become a Dealer</a>
                    </div>
                </div>
                <?php } ?>
            <?php } ?>
            </div>
        </div>
        <script type="text/javascript">
        jQuery(document).ready( function( $ ){
            
        } );
        </script>   
    <?php } else {
        echo '<p style="color:#f00;"><strong>Database connection error.</strong></p>';
        if($mysqli_read->connect_error)
            echo '<p>'.$mysqli_read->connect_error.'</p>';
        if($mysqli_read_write->connect_error)
            echo '<p>'.$mysqli_read->connect_error.'</p>';
    } 
}

function custom_delete_pos_outlet_post($vendor_name) {
    // Check if GET parameters are present and have the correct values
    if (isset($_GET['vendor_connect']) && isset($_GET['status_vendor_connect']) && $_GET['status_vendor_connect'] === 'disconnect') {
        
        // Sanitize and use the vendor name passed to the function
        $vendor_name = sanitize_text_field($vendor_name);
        
        // Query for 'pos_outlet' posts with a title matching $vendor_name (case insensitive)
        $args = array(
            'post_type' => 'pos_outlet',
            'title' => $vendor_name,
            'post_status' => 'any',
            'posts_per_page' => 1,
        );
        
        $posts = get_posts($args);

        // If a post is found, delete it completely
        if ($posts) {
            foreach ($posts as $post) {
                wp_delete_post($post->ID, true); // Permanently delete the post
            }
        }
    }
}

// Hook the function to the WordPress initialization action
add_action('init', function() {
    if (isset($_GET['vendor_connect'])) {
        custom_delete_pos_outlet_post($_GET['vendor_connect']);
    }
});
