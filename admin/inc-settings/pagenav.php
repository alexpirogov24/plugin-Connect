<?php
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

if(isset($_GET['page_num'])) $main_page_num = isset($_GET['page_num']) ? $_GET['page_num'] : ''; else $main_page_num = 1;

$count_view_products = 100;
if(get_option('count_view_products')) {
	if (isset($_GET['count_view_products'])) {
		update_option( 'count_view_products', $_GET['count_view_products'] );		
	}
	$count_view_products = get_option('count_view_products');
} else {
	if (isset($_GET['count_view_products'])) {
		add_option( 'count_view_products', $_GET['count_view_products'] );
		$count_view_products = get_option('count_view_products');
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
        if(isset($filter_arr['category']) && $filter_arr['category']!="not_selected")
            $add_sql .= ' AND (cum.category_id = "'.$filter_arr['category'].'" OR c.parent_id = "'.$filter_arr['category'].'")'; // $add_sql .= ' AND cum.category_id = "'.$filter_arr['category'].'"';
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
        if(isset($filter_arr['category']) && $filter_arr['category']!="not_selected")                            
            $add_sql .= ' AND (cum.category_id = "'.$filter_arr['category'].'" OR c.parent_id = "'.$filter_arr['category'].'")'; // $add_sql .= ' AND cum.category_id = "'.$filter_arr['category'].'"';
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
        if(isset($filter_arr['category']) && $filter_arr['category']!="not_selected")
            $add_sql .= ' AND (cum.category_id = "'.$filter_arr['category'].'" OR c.parent_id = "'.$filter_arr['category'].'")'; // $add_sql .= ' AND cum.category_id = "'.$filter_arr['category'].'"';
        if(isset($filter_arr['manufacturer']) && $filter_arr['manufacturer']!="not_selected")
            $add_sql .= ' AND mc.Manufacturer = "'.$filter_arr['manufacturer'].'"';
    }
}

if(get_option('search_product')) {
    if (isset($_GET['search']) && $_GET['search']=='send') {
        $search_arr['title'] = isset($_GET['search_title']) ? $_GET['search_title'] : '';
        $search_arr['upc'] = isset($_GET['search_upc']) ? $_GET['search_upc'] : '';               
        update_option( 'search_product', serialize($search_arr) );
        if(isset($search_arr['upc']) && $search_arr['upc']!='')
            $add_sql .= ' AND mc.UPC LIKE "%'.$search_arr['upc'].'%"';
        if(isset($search_arr['title']) && $search_arr['title']!='')
            $add_sql .= ' AND mc.Title LIKE "%'.$search_arr['title'].'%"';
    } elseif(isset($_GET['search']) && $_GET['search']=='reset') {
        delete_option('search_product');
    } else {
        $search_arr = unserialize(get_option('search_product'));
        if(isset($search_arr['upc']) && $search_arr['upc']!='')
            $add_sql .= ' AND mc.UPC LIKE "%'.$search_arr['upc'].'%"';
        if(isset($search_arr['title']) && $search_arr['title']!='')
            $add_sql .= ' AND mc.Title LIKE "%'.$search_arr['title'].'%"';
    }
} else {
    if (isset($_GET['search']) && $_GET['search']=='send') {
        $search_arr['title'] = isset($_GET['search_title']) ? $_GET['search_title'] : '';
        $search_arr['upc'] = isset($_GET['search_upc']) ? $_GET['search_upc'] : '';
        add_option( 'search_product', serialize($search_arr) );
        if(isset($search_arr['upc']) && $search_arr['upc']!='')
            $add_sql .= ' AND mc.UPC LIKE "%'.$search_arr['upc'].'%"';
        if(isset($search_arr['title']) && $search_arr['title']!='')
            $add_sql .= ' AND mc.Title LIKE "%'.$search_arr['title'].'%"';
    }
}

$mra_import_psql_enable_ffl = get_option('mra_import_psql_enable_ffl');
if ($mra_import_psql_enable_ffl != 'on')
    $add_sql .= ' AND mc.Firearm = 0';

$result_vendor = $mysqli_read->query("SELECT * FROM vendors WHERE vendor='".$vendor."'");
$row_vendor = $result_vendor->fetch_assoc();

// $mra_psql_products = new WP_Query;
// $products_exclude = $mra_psql_products->query( [
//     'posts_per_page' => -1,
//     'post_type' => 'product',
//     'tax_query' => array(
//         'relation' => 'OR',
//         array(
//             'taxonomy' => 'pa_upc',
//             'operator' => 'EXISTS',
//         )
//     )
// ] );

// $exclude_upc_sql = "";
// if ($products_exclude) {
//     $exclude_upc = '';
//     $e = 0;
//     foreach ($products_exclude as $key => $product_exc) {
//         $term_list_exc = wp_get_post_terms( $product_exc->ID, 'pa_upc', array( 'fields' => 'names' ) );
//         $upc_product_exc = $term_list_exc[0];
//         if ($e==0) 
//             $exclude_upc .= "'".$upc_product_exc."'";
//         else
//             $exclude_upc .= ", '".$upc_product_exc."'";
//         $e++;
//     }
//     $exclude_upc_sql = " AND master_catalog.UPC NOT IN (".$exclude_upc.")";
// }

$count_page = 0;
// $result_count = $mysqli->query("SELECT COUNT(*) FROM master_catalog, inventory_master, image_upc_mapping  WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=image_upc_mapping.UPC AND inventory_master.vendorid=".$row_vendor['id'].$add_sql.$exclude_upc_sql);

// var_dump("SELECT COUNT(*) FROM master_catalog, inventory_master WHERE master_catalog.UPC=inventory_master.UPC AND inventory_master.vendorid=".$row_vendor['id'].$add_sql.$exclude_upc_sql);

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

$count_query = "
    SELECT 
        COUNT(*) AS total_count
    FROM (
        SELECT 
            mc.UPC
        FROM 
            master_catalog mc
        LEFT JOIN 
            cost_master cm ON mc.UPC = cm.upc
        LEFT JOIN 
            inventory_master im ON mc.UPC = im.upc
        LEFT JOIN 
            category_upc_mapping cum ON mc.UPC = cum.upc
        LEFT JOIN 
            image_upc_mapping ium ON mc.UPC = ium.`UPC`
        LEFT JOIN 
            categories c ON cum.category_id = c.id 
        WHERE 
            cm.vendorid = ".$row_vendor['id']."
            AND cm.cost != 0
            AND im.vendorid = ".$row_vendor['id'].$add_sql."
            AND im.quantity != 0
            AND (ium.vendor = '".$row_vendor['vendor']."' OR ium.vendor IS NOT NULL)
            AND NOT EXISTS (
                SELECT upc
                FROM added_products ap
                WHERE cm.upc = ap.upc
                  AND ap.site_id = ".$site_id_db."
            )
        GROUP BY mc.UPC, cm.upc, im.upc, cum.upc
    ) AS subquery
";

$result_count = $mysqli_read->query($count_query);

if($result_count) {
    $count_products = $result_count->fetch_assoc()['total_count'];
    $count_products = intval($count_products);
    $count_page = round($count_products/$count_view_products);
}

// var_dump($count_products);

$count_arr = array();
$count_arr[0] = 10;
$count_arr[1] = 25;
$count_arr[2] = 50;
$count_arr[3] = 100;
$count_arr[4] = 250;
$count_arr[5] = 500;

?>
<style type="text/css">
	.page_nav {
		margin: 20px 0;
		background-color: #fbfbfb;
		padding: 8px 12px;
		display: table;
        float: right;
        border: 1px solid #dbdbdb;
        border-radius: 3px;
	}
	.page_nav p {
		float: left;
		margin-right: 5px;
	    line-height: 4px;
	}
	#page_nav_select {
		float: left;
		margin-right: 40px;
	}
	#page_nav_count {
		float: left;
		margin-right: 15px;
	}
    .page_nav_ul {
        padding: 0;
        margin: 0;
        display: table;
        float: left;
    }
    .page_nav_ul li {
        display: block;
        float: left;
        margin: 0 4px;
    }
    .page_nav_ul li a {
    	display: block;
    	padding: 5px;
    	border: 1px solid #dfdfdf;
    	border-radius: 3px;
    	background-color: #ffffff;
    	text-decoration: none;
    	color: #000;
    }
    .page_nav_ul li a.active {
        pointer-events: none;
        background-color: #fff4cc;
    }
</style>
<div class="page_nav">
	<p>Show:</p>
    <select id="page_nav_count" name="page_nav_count">
    	<?php $c=0; while (!empty($count_arr) && isset($count_arr[$c])) { ?>
    		<option value="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&count_view_products=<?= $count_arr[$c] ?>" <?php if($count_view_products==$count_arr[$c]) echo 'selected'; ?>><?= $count_arr[$c] ?></option>
    	<?php $c++; } ?>
    </select>
    <p>Page:</p>
    <select id="page_nav_select" name="page_nav_select">
    	<?php $a=1; while ($a <= $count_page) { 
    		$beginning_page = $a*$count_view_products;
            $text_link=$a;
            if($text_link==$main_page_num)
                $selected = "selected";
            else
                $selected = ""; ?> 
    	<option value="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= $beginning_page ?>&page_num=<?= $text_link ?>" <?= $selected ?>><?= $text_link ?></option>
    	<?php $a++; } ?>
    </select>
    <ul class="page_nav_ul">
    	<?php if(isset($main_page_num) && $main_page_num!=1) {
    		$page_num = $main_page_num-1;
    		$beginning_page = ($page_num-1)*$count_view_products; ?>
    		<li><a class="back" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= $beginning_page ?>&page_num=<?= $page_num ?>"><</a></li>
    	<?php } ?>

    	<?php if($count_page >= 1) { ?><li><a class="<?php if((!isset($main_page_num) || $main_page_num==1)) echo 'active'; ?>" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= 0*$count_view_products ?>&page_num=<?= 1 ?>">1</a></li><?php } ?>
    	<?php if($count_page >= 2) { ?><li><a class="<?php if($main_page_num==2) echo 'active'; ?>" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= 1*$count_view_products ?>&page_num=<?= 2 ?>">2</a></li><?php } ?>
    	<?php if($count_page >= 3) { ?><li><a class="<?php if($main_page_num==3) echo 'active'; ?>" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= 2*$count_view_products ?>&page_num=<?= 3 ?>">3</a></li><?php } ?>

    	<?php 
    	$begin_pages = 3;
    	$end_pages = $count_page-2;
    	if(isset($main_page_num) && $main_page_num>=$begin_pages && $main_page_num<=$end_pages && $count_page > 6) { 
    		$page_num = $main_page_num;
    		$page_num_before = $page_num-1;
    		$page_num_after = $page_num+1; ?>
    		
    		<?php if($page_num_before>($begin_pages+1) && $count_page >= $page_num_before) { ?>
    		<li><span>...</span></li>
    		<?php } ?>

    		<?php if($page_num_before>=($begin_pages+1) && $count_page >= $page_num_before) { ?>
    		<li><a class="<?php if($main_page_num==$page_num_before) echo 'active'; ?>" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= ($page_num_before-1)*$count_view_products ?>&page_num=<?= $page_num_before ?>"><?= $page_num_before ?></a></li>
    		<?php } ?>

    		<?php if($page_num!=$begin_pages && $page_num!=$end_pages && $count_page >= $page_num) { ?>
    		<li><a class="<?php if($main_page_num==$page_num) echo 'active'; ?>" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= ($page_num-1)*$count_view_products ?>&page_num=<?= $page_num ?>"><?= $page_num ?></a></li>
    		<?php } ?>
    		
    		<?php if($page_num_after<=($end_pages-1) && $count_page >= $page_num_after) { ?>
    		<li><a class="<?php if($main_page_num==$page_num_after) echo 'active'; ?>" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= ($page_num_after-1)*$count_view_products ?>&page_num=<?= $page_num_after ?>"><?= $page_num_after ?></a></li>
    		<?php } ?>

    		<?php if($page_num_after<($end_pages-1) && $count_page >= $page_num_after) { ?>
    		<li><span>...</span></li>
    		<?php } ?>

		<?php } ?>

		<?php $a = $count_page-1;
		if((!isset($main_page_num) || $main_page_num<=2 || $main_page_num>=$a) && $a>9) { ?>
    		<li><span>...</span></li>
		<?php } ?>

    	
        <?php if(($count_page-2) >= 4 && $count_page >=6 ) { ?><li><a class="<?php if($main_page_num==($count_page-2)) echo 'active'; ?>" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= ($count_page-3)*$count_view_products ?>&page_num=<?= $count_page-2 ?>"><?= $count_page-2 ?></a></li><?php } ?>
    	<?php if(($count_page-1) >= 5 && $count_page >=6 ) { ?><li><a class="<?php if($main_page_num==($count_page-1)) echo 'active'; ?>" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= ($count_page-2)*$count_view_products ?>&page_num=<?= $count_page-1 ?>"><?= $count_page-1 ?></a></li><?php } ?>
    	<?php if($count_page >=6 ) { ?><li><a class="<?php if($main_page_num==$count_page) echo 'active'; ?>" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= ($count_page-1)*$count_view_products ?>&page_num=<?= $count_page ?>"><?= $count_page ?></a></li><?php } ?>
        

    	<?php if($main_page_num!=$count_page) { 
    		$page_num = $main_page_num+1;
    		$beginning_page = ($page_num-1)*$count_view_products; ?>
    		<li><a class="up" href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= $beginning_page ?>&page_num=<?= $page_num ?>">></a></li>
    	<?php } ?>

        <?php /* $a=0; while ($a <= $count_page) { 
            $beginning_page = $a*100;
            $ending_page = $beginning_page+100;
            $text_link=$a+1;
            if($text_link==$main_page_num)
                $active = "active";
            else
                $active = ""; ?>
            <li><a href="/wp-admin/admin.php?page=mra_import_psql_products&vendor=<?= $row_vendor['vendor']; ?>&beginning_page=<?= $beginning_page ?>&page_num=<?= $text_link ?>" class="<?= $active ?>"><?= $text_link ?></a></li>
        <?php $a++; } */ ?>
    </ul>
</div>
<script type="text/javascript">
jQuery(document).ready( function( $ ){
    $("#page_nav_select").change(function(){
    	location.href = $(this).val();
    });
    $("#page_nav_count").change(function(){
    	location.href = $(this).val();
    });
} );
</script>