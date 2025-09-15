<?php
if(!empty($_GET['vendor'])) {
    $vendor = $_GET['vendor'];
} else {
    $i=0; foreach ($list_vendors_arr as $value) {
        if(isset($value)) {
            $vendor = $value;
            break;
        }
    $i++; }
}
$url = "/wp-admin/admin.php?page=mra_import_psql_products&vendor=".$vendor;

$search_arr = array();
if(get_option('search_product')) {
    $search_arr = unserialize(get_option('search_product'));
}

$result_vendor = $mysqli_read->query("SELECT * FROM vendors WHERE vendor='".$vendor."'");
$row_vendor = $result_vendor->fetch_assoc();

$add_sql_manuf = '';
$add_sql_cat = '';
$filter_arr = array();
if(get_option('filter_product')) {
    $filter_arr = unserialize(get_option('filter_product'));
}

$ffl_sql = '';
$mra_import_psql_enable_ffl = get_option('mra_import_psql_enable_ffl');
if ($mra_import_psql_enable_ffl != 'on')
    $ffl_sql .= ' AND mc.Firearm = 0';

if(isset($filter_arr['category']) && $filter_arr['category']!="0") {
    $add_sql_cat .= ' AND cum.category_id = "'.$filter_arr['category'].'"  OR c.parent_id = "'.$filter_arr['category'].'"'.$ffl_sql;
}
if(isset($filter_arr['manufacturer']) && $filter_arr['manufacturer']!="not_selected") {
    $add_sql_manuf .= ' AND mc.Manufacturer = "'.$filter_arr['manufacturer'].'"'.$ffl_sql;
}


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

// $result_cats = $mysqli->query("SELECT DISTINCT master_catalog.Category FROM master_catalog, cost_master, inventory_master, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=cost_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND inventory_master.vendorid=".$row_vendor['id'].$add_sql_manuf." ORDER BY master_catalog.Category");

// $result_cats = $mysqli->query("SELECT DISTINCT mc.Category
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
//     AND ium.vendor = '".$row_vendor['vendor']."'".$add_sql_manuf."
//     AND NOT EXISTS (
//         SELECT upc 
//             FROM added_products ap 
//                 WHERE cm.upc = ap.upc
//                     AND ap.site_id = ".$site_id_db."
//     )
// ORDER BY mc.Category");




// $result_manuf = $mysqli->query("SELECT DISTINCT master_catalog.Manufacturer FROM master_catalog, cost_master, inventory_master, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=cost_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND inventory_master.vendorid=".$row_vendor['id'].$add_sql_cat." ORDER BY master_catalog.Manufacturer");

$result_manuf = $mysqli_read->query("SELECT DISTINCT 
    mc.Manufacturer 
FROM 
    master_catalog mc
LEFT JOIN 
    cost_master cm ON mc.UPC = cm.upc
LEFT JOIN 
    inventory_master im ON mc.UPC = im.upc
LEFT JOIN 
    category_upc_mapping cum ON mc.UPC = cum.upc
LEFT JOIN 
    image_upc_mapping ium ON mc.UPC = ium.UPC
LEFT JOIN 
    categories c ON cum.category_id = c.id
WHERE 
    cm.vendorid = ".$row_vendor['id']."
    AND cm.cost != 0
    AND im.vendorid = ".$row_vendor['id']."
    AND im.quantity != 0
    AND ium.vendor = '".$row_vendor['vendor']."'".$add_sql_cat.$add_sql_manuf."

    AND NOT EXISTS (
        SELECT upc 
            FROM added_products ap 
                WHERE cm.upc = ap.upc
                    AND ap.site_id = ".$site_id_db."
    )
ORDER BY mc.Manufacturer");

// var_dump("SELECT DISTINCT mc.Manufacturer
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
//     AND ium.vendor = '".$row_vendor['vendor']."'".$add_sql_cat.$add_sql_manuf."
//     AND NOT EXISTS (
//         SELECT upc 
//             FROM added_products ap 
//                 WHERE cm.upc = ap.upc
//                     AND ap.site_id = ".$site_id_db."
//     )
// ORDER BY mc.Manufacturer");



// var_dump("SELECT DISTINCT master_catalog.Category FROM master_catalog, inventory_master, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND inventory_master.vendorid=".$row_vendor['id']);
        
// if ($result_cats) {
//     $row_cats = $result_cats->fetch_all(MYSQLI_ASSOC);
// }

if ($result_manuf) {
    $row_manuf = $result_manuf->fetch_all(MYSQLI_ASSOC);
}

function renderCategoryOptions($categories, $selectedId = null, $prefix = '') {
    $html = '';

    foreach ($categories as $category) {
        $isSelected = $category['id'] == $selectedId ? ' selected' : '';
        $html .= '<option value="' . $category['id'] . '" data-name="' . htmlspecialchars($category['name']) . '"' . $isSelected . '>' . $prefix . htmlspecialchars($category['name']) . '</option>';

        if (!empty($category['children'])) {
            $html .= renderCategoryOptions($category['children'], $selectedId, $prefix . '- ');
        }
    }

    return $html;
}


?>
<style type="text/css">
    #mra_import_psql_filter {
        display: table;
        width: 100%;
        margin: 15px 0;
        background-color: #fbfbfb;
        padding: 8px 12px;
        border: 1px solid #dbdbdb;
        border-radius: 3px;
    }
    #mra_import_psql_filter .filter_checkbox {
        padding: 5px;
        margin-right: 7px;
    }
    #mra_import_psql_filter .filter_checkbox input {
        margin-top: 0px;
        margin-right: 1px;
    }
    #mra_import_psql_filter select {
        max-width: 120px;
    }
    #mra_import_psql_filter .button {
        border: 1px solid #d1d1d1;
    }
    #mra_import_psql_filter .filter_block {
        float: left;
    }
    #mra_import_psql_filter .search_block {
        float: right;
    }
    #mra_import_psql_filter .search_block .search_input {
        max-width: 150px;
    }
    #mra_import_psql_filter .filter_checkbox {
        float: left;
    }
    #select2-filter_select_category-results .select2-results__option:hover, #select2-filter_select_manufacturer-results .select2-results__option:hover{
        background-color: #f2f2f2;
    }
    #select2-filter_select_category-results .select2-results__option--selected, #select2-filter_select_manufacturer-results .select2-results__option--selected {
        font-weight: 700;
        background-color: #f2f2f2;
    }
    .select_custom {display: none;}
    .select2-selection__rendered {
        background: #fff url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E) no-repeat right 5px top 55%;
        background-size: 16px 16px;
        display: inline-block;
        background-color: #fff;
        padding: 5px 30px 5px 12px;
        border-radius: 3px;
        border: 1px solid #d8d8d8;
    }
    .select2-container.select2-container--open {
        max-width: 176px;
        max-height: 405px;
        overflow: scroll;
        background-color: rgb(255, 255, 255);
        border: 1px solid rgb(174, 174, 174);
    }
    .select2-results__option {
        cursor: default;
        padding: 0px 10px;
    }
    #filter_select_category, #filter_select_manufacturer {
        width: initial !important;
        clip: initial !important;
        -webkit-clip-path: initial !important;
        clip-path: initial !important;
        position: relative !important;
        padding: 3px 10px 5px !important;
        height: 30px !important;
        line-height: 25px !important;
    }
    #filter_select_category+.select2-container, #filter_select_manufacturer+.select2-container {
        display: none !important;
    }

</style>
<div id="mra_import_psql_filter">
    <div class="filter_block">
        <div class="filter_checkbox block_has_map">
            <?php if(isset($filter_arr['has_map']) && $filter_arr['has_map']=='true') $checked="checked"; else $checked=""; ?>
            <input type="checkbox" id="filter_has_map" name="filter_has_map" <?= $checked ?> />
            <label for="filter_has_map">Has Map</label>
        </div>
        <div class="filter_checkbox block_has_image">
            <?php if(isset($filter_arr['has_image']) && $filter_arr['has_image']=='true') $checked="checked"; else $checked=""; ?>
            <input type="checkbox" id="filter_has_image" name="filter_has_image" <?= $checked ?> />
            <label for="filter_has_image">Has image</label>
        </div>
        <div class="filter_checkbox block_curr_in_stock">
            <?php if(isset($filter_arr['curr_in_stock']) && $filter_arr['curr_in_stock']=='true') $checked="checked"; else $checked=""; ?>
            <input type="checkbox" id="filter_curr_in_stock" name="filter_curr_in_stock" <?= $checked ?> />
            <label for="filter_curr_in_stock">Currently in-Stock</label>
        </div>
        <select id="filter_select_category" class="select_category" name="filter_select_category">
            <option value="not_selected" data-name="not_selected">Сategory</option>

            <?php 
            if (isset($filter_arr['category'])) 
                echo renderCategoryOptions(mra_import_psql_db_arr_category(), $filter_arr['category']);
            else
                echo renderCategoryOptions(mra_import_psql_db_arr_category());

            /* foreach ($row_cats as $key => $cat) {
                if (isset($filter_arr['category']) && $filter_arr['category']==$cat['Category']) $selected = "selected"; else $selected = ""; ?>
                <option value="<?= $cat['Category'] ?>" <?= $selected ?>><?= $cat['Category'] ?></option>
            <?php } */ ?>
        </select>
        <select id="filter_select_manufacturer" class="select_manufacture" data-select-search="true" name="filter_select_manufacturer">
            <option value="not_selected">Manufacturer</option>
            <?php foreach ($row_manuf as $key => $manufacturer) {
                if (isset($filter_arr['manufacturer']) && $filter_arr['manufacturer']==$manufacturer['Manufacturer']) $selected = "selected"; else $selected = ""; ?>
                <option value="<?= $manufacturer['Manufacturer'] ?>" <?= $selected ?>><?= $manufacturer['Manufacturer'] ?></option>
            <?php } ?>
        </select>
        <input type="submit" name="button_filter_send" id="button_filter_send" class="button" data-url="<?= $url ?>" value="Filter">
        <?php if(isset($filter_arr['has_map']) || (isset($_GET['filter']) && $_GET['filter']=="send")) { ?>
        <input type="submit" name="button_filter_reset" id="button_filter_reset" class="button" data-url="<?= $url ?>" value="Reset Filter">
        <?php } ?>
    </div>
    <div class="search_block">
        <?php if(isset($search_arr['title']) && $search_arr['title']!='') $search_title=$search_arr['title']; else $search_title=""; ?>
        <input type="text" class="search_input" id="search_input_title" name="search_input_title" value="<?= $search_title ?>" placeholder="search by title">

        <?php if(isset($search_arr['upc']) && $search_arr['upc']!='') $search_upc=$search_arr['upc']; else $search_upc=""; ?>
        <input type="text" class="search_input" id="search_input_upc" name="search_input_upc" value="<?= $search_upc ?>" placeholder="search by UPC">

        <input type="submit" name="button_search_send" id="button_search_send" class="button" data-url="<?= $url ?>" value="Search">        
        <?php if((isset($search_arr['title']) && $search_arr['title']!='') || (isset($search_arr['upc']) && $search_arr['upc']!='')) { ?>
        <input type="submit" name="button_search_reset" id="button_search_reset" class="button" data-url="<?= $url ?>" value="Reset search">
        <?php } ?>

    </div>
</div>
<script type="text/javascript">
jQuery(document).ready( function( $ ){
    $("#button_filter_send").click(function(){
        if ($('#filter_select_category').val()!='not_selected') {
            var category = '&category='+encodeURIComponent($('#filter_select_category').val());
        }
        else {
            var category = '';
        }
        var manufacturer = encodeURIComponent($('#filter_select_manufacturer').val());
        var url = $(this).attr('data-url')+'&filter=send'+'&has_map='+$('#filter_has_map').prop('checked')+'&has_image='+$('#filter_has_image').prop('checked')+'&curr_in_stock='+$('#filter_curr_in_stock').prop('checked')+category+'&manufacturer='+manufacturer;
        location.href = url;
    });
    $("#button_filter_reset").click(function(){
        var url = $(this).attr('data-url')+'&filter=reset';
        location.href = url;
    });
    $("#button_search_send").click(function(){
        var url = $(this).attr('data-url')+'&search=send'+'&search_upc='+$('#search_input_upc').val()+'&search_title='+$('#search_input_title').val();
        location.href = url;
    });
    $("#button_search_reset").click(function(){
         var url = $(this).attr('data-url')+'&search=reset';
        location.href = url;
    });
} );
</script>