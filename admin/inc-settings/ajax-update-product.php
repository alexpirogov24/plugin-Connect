<?php
function save_wc_custom_attributes($post_id, $custom_attributes) {
    $i = 0;
    foreach ($custom_attributes as $name => $value) {
        wp_set_object_terms($post_id, $value, $name, true);
        $product_attributes[$i] = array(
            'name' => $name,
            'value' => $value,
            'is_visible' => 1,
            'is_variation' => 0,
            'is_taxonomy' => 1
        );
        $i++;
    }
    update_post_meta($post_id, '_product_attributes', $product_attributes);
}

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
function add_external_image_to_product($post_id, $external_image_url) {
    // Check if the plugin is active
    if ( is_plugin_active( 'external-images/external-images.php' ) ) {
        // Check if the post type is a product
        if (get_post_type($post_id) == 'product') {
            // Add meta-data with the external image URL
            update_post_meta($post_id, '_external_image_url', esc_url($external_image_url));
            
            // Set the external image as the post thumbnail
            if (function_exists('set_external_image_as_thumbnail')) {
                set_external_image_as_thumbnail($post_id, $external_image_url);
                return true;
            } else {
                // Return false if the function is not found
                return false;
            }
        } else {
            // Return false if the post type is not a product
            return false;
        }
    } else {
        // Return false if the plugin is not active
        return false;
    }
}

function upload_image_wordpress($post_id, $url_img) {
    $image_url = $url_img;
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents( $image_url );
    $filename = basename( $image_url );
    if ( wp_mkdir_p( $upload_dir['path'] ) ) {
      $file = $upload_dir['path'] . '/' . $filename;
    }
    else {
      $file = $upload_dir['basedir'] . '/' . $filename;
    }
    file_put_contents( $file, $image_data );
    $wp_filetype = wp_check_filetype( $filename, null );

    $attachment = array(
      'post_mime_type' => $wp_filetype['type'],
      'post_title' => sanitize_file_name( $filename ),
      'post_content' => '',
      'post_status' => 'inherit'
    );

    global $wpdb;
    $row = $wpdb->get_row( "SELECT * FROM $wpdb->postmeta WHERE `meta_key` LIKE '_wp_attached_file' AND `meta_value` LIKE '%".$filename."%'" );
    //SELECT * FROM `wp_postmeta` WHERE `meta_key` LIKE '_wp_attached_file' AND `meta_value` LIKE '%kissclipart-pistol-in-hand-png-clipart-revolver-firearm-clip-a-9cb7e26bc0a7bd6a-removebg-preview%' 
    if($row==NULL) {
        $attach_id = wp_insert_attachment( $attachment, $file );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        $attach_data['attach_id'] = $attach_id;
        wp_update_attachment_metadata( $attach_id, $attach_data );
    }

    set_post_thumbnail($post_id, $attach_id);

    return $attach_data;
}

add_action( 'wp_ajax_mrapgupdate', 'mrapgupdate_func' );
function mrapgupdate_func(){

    $upc_product = trim($_POST['upc_product']);
    $count_product = intval($_POST['count_product']);
    $num_element = intval($_POST['num_element'])+1;
    global $wpdb;
    $mysqli_read_product = mra_import_psql_db_connection_read();
    $mysqli_read_write_product = mra_import_psql_db_connection_read_write();

    // var_dump($upc_product);

    // $result_product = $mysqli_read_product->query("SELECT * FROM master_catalog, cost_master, inventory_master, category_upc_mapping, categories, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=cost_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND image_upc_mapping.UPC=category_upc_mapping.upc AND category_upc_mapping.category_id=categories.id AND master_catalog.UPC=".$upc_product);
    

    $result_product = $mysqli_read_product->query("SELECT *
    FROM master_catalog mc
    LEFT JOIN cost_master cm ON mc.UPC = cm.UPC
    LEFT JOIN inventory_master im ON mc.UPC = im.UPC
    LEFT JOIN category_upc_mapping cum ON mc.UPC = cum.upc
    LEFT JOIN categories c ON cum.category_id = c.id
    LEFT JOIN image_upc_mapping ium ON mc.UPC = ium.UPC
    WHERE mc.UPC = ".$upc_product."
    GROUP BY mc.UPC");

    $row_product = $result_product->fetch_assoc();

    // var_dump("SELECT * FROM master_catalog, cost_master, inventory_master, category_upc_mapping, categories, image_upc_mapping WHERE master_catalog.UPC=inventory_master.UPC AND master_catalog.UPC=cost_master.UPC AND image_upc_mapping.UPC=master_catalog.UPC AND image_upc_mapping.UPC=category_upc_mapping.upc AND category_upc_mapping.category_id=categories.id AND master_catalog.UPC=".$upc_product);

    $result_vendor = $mysqli_read_product->query("SELECT * FROM inventory_master WHERE upc=".$upc_product);
    if ($result_vendor) {
        $rows_product_vendor = $result_vendor->fetch_all(MYSQLI_ASSOC);
    }

    $result_cost = $mysqli_read_product->query("SELECT * FROM cost_master WHERE upc=".$upc_product);
    if ($result_cost) {
        $rows_product_cost = $result_cost->fetch_all(MYSQLI_ASSOC);
    }

    

    $term = term_exists($row_product['name'], 'product_cat');
    if ($term) {
        $cat_id = $term['term_id'];
    } else {
        // var_dump($row_product['name']);
        $cat_insert_res = wp_insert_term($row_product['name'], 'product_cat');
        if (is_wp_error($cat_insert_res)) {
            // Обработка ошибки
            echo 'Error when creating a category: ' . $cat_insert_res->get_error_message();
            return;
        }
        $cat_id = $cat_insert_res['term_id'];
    }

    // Пример экранирования значений для предотвращения SQL-инъекций
    $product_name = $mysqli_read_product->real_escape_string($row_product['name']);
    $result_category = $mysqli_read_product->query("SELECT * FROM categories WHERE name='$product_name'");
    if ($result_category) {
        $rows_product_category = $result_category->fetch_assoc();
    }

    if (isset($rows_product_category['parent_id']) && $rows_product_category['parent_id'] != NULL) {
        $parent_id1 = (int) $rows_product_category['parent_id'];
        $result_category_parent1 = $mysqli_read_product->query("SELECT * FROM categories WHERE id=$parent_id1");
        if ($result_category_parent1) {
            $row_product_category_parent1 = $result_category_parent1->fetch_assoc();

            $term_parent1 = term_exists($row_product_category_parent1['name'], 'product_cat');
            if ($term_parent1) {
                $cat_parent1_id = $term_parent1['term_id'];
            } else {
                $cat_insert_res_parent1 = wp_insert_term($row_product_category_parent1['name'], 'product_cat');
                if (is_wp_error($cat_insert_res_parent1)) {
                    // Обработка ошибки
                    echo 'Error when creating a parent category: ' . $cat_insert_res_parent1->get_error_message();
                    return;
                }
                $cat_parent1_id = $cat_insert_res_parent1['term_id'];
            }

            $result = wp_update_term($cat_id, 'product_cat', array(
                'parent' => $cat_parent1_id
            ));
            if (is_wp_error($result)) {
                // Обработка ошибки
                echo 'Error when updating a category: ' . $result->get_error_message();
                return;
            }

            if (isset($row_product_category_parent1['parent_id']) && $row_product_category_parent1['parent_id'] != NULL) {
                $parent_id2 = (int) $row_product_category_parent1['parent_id'];
                $result_category_parent2 = $mysqli_read_product->query("SELECT * FROM categories WHERE id=$parent_id2");
                if ($result_category_parent2) {
                    $row_product_category_parent2 = $result_category_parent2->fetch_assoc();

                    $term_parent2 = term_exists($row_product_category_parent2['name'], 'product_cat');
                    if ($term_parent2) {
                        $cat_parent2_id = $term_parent2['term_id'];
                    } else {
                        $cat_insert_res_parent2 = wp_insert_term($row_product_category_parent2['name'], 'product_cat');
                        if (is_wp_error($cat_insert_res_parent2)) {
                            // Обработка ошибки
                            echo 'Error when creating a parent category: ' . $cat_insert_res_parent2->get_error_message();
                            return;
                        }
                        $cat_parent2_id = $cat_insert_res_parent2['term_id'];
                    }

                    $result = wp_update_term($cat_parent1_id, 'product_cat', array(
                        'parent' => $cat_parent2_id
                    ));
                    if (is_wp_error($result)) {
                        // Обработка ошибки
                        echo 'Error when updating parent category: ' . $result->get_error_message();
                        return;
                    }

                    if (isset($row_product_category_parent2['parent_id']) && $row_product_category_parent2['parent_id'] != NULL) {
                        $parent_id3 = (int) $row_product_category_parent2['parent_id'];
                        $result_category_parent3 = $mysqli_read_product->query("SELECT * FROM categories WHERE id=$parent_id3");
                        if ($result_category_parent3) {
                            $row_product_category_parent3 = $result_category_parent3->fetch_assoc();

                            $term_parent3 = term_exists($row_product_category_parent3['name'], 'product_cat');
                            if ($term_parent3) {
                                $cat_parent3_id = $term_parent3['term_id'];
                            } else {
                                $cat_insert_res_parent3 = wp_insert_term($row_product_category_parent3['name'], 'product_cat');
                                if (is_wp_error($cat_insert_res_parent3)) {
                                    // Обработка ошибки
                                    echo 'Error when creating a parent category: ' . $cat_insert_res_parent3->get_error_message();
                                    return;
                                }
                                $cat_parent3_id = $cat_insert_res_parent3['term_id'];
                            }

                            $result = wp_update_term($cat_parent2_id, 'product_cat', array(
                                'parent' => $cat_parent3_id
                            ));
                            if (is_wp_error($result)) {
                                // Обработка ошибки
                                echo 'Error when updating parent category: ' . $result->get_error_message();
                                return;
                            }
                        }
                    }
                }
            }
        }
    }


    

    $args = array(
        'post_type' => array('product'),
        'tax_query' => array(
            'relation' => 'OR',
            array(
                'taxonomy' => 'pa_upc',
                'field' => 'name',
                'terms' => $upc_product,
                'operator' => 'IN',
            )
        )
    );
    $query = new WP_Query($args);
    // var_dump($query->posts);

    if (empty($query->posts)) {
        $img = '';
        $result_img = $mysqli_read_product->query("SELECT *
        FROM image_upc_mapping
        WHERE UPC = ".$upc_product);
        if ($result_img->num_rows!=0) {
            $rows_img = $result_img->fetch_assoc();
            $img = $rows_img['s3_URL'];
        }


        $url_title = str_replace('s3://mwm-vendor-images/', '', $img);
        $url_img = 'https://mwm-vendor-images.s3.us-east-2.amazonaws.com/'.$url_title;
        $url_img2 = 'https://dme5m5gvjikvl.cloudfront.net/'.$url_title;
        $url_name = str_replace('.', '-', $url_title);
        $img_select = $wpdb->get_row( "SELECT * FROM ".$wpdb->posts." WHERE `post_name` LIKE '".$url_name."' AND `post_mime_type` LIKE 'image/jpeg'" );
        
        $post_data = [
            'post_title'    => $row_product['Title'],
            'post_content'  => $row_product['Product Description'],
            'post_status'   => 'publish',
            'post_type'     => 'product',
        ];
        $post_id = wp_insert_post(  wp_slash( $post_data ) );
        $product = wc_get_product( $post_id );

        if (is_plugin_active( 'external-images/external-images.php' )) {
            $result = add_external_image_to_product($post_id, $url_img2);
            if ($result) {

            } else {
                echo "";
            }

        } else {
            if(!has_post_thumbnail( $post_id )) {
                
                if($img_select) {
                    set_post_thumbnail( $post_id, $img_select->ID );
                } else {
                    upload_image_wordpress($post_id, $url_img);
                }
            }
        }

        // if(get_option('list_vendors_connect')) {
        //     $list_vendors_arr = unserialize( get_option('list_vendors_connect') );
        // }

        // get_post_meta( $id_product, '_wc_pos_outlet_stock', true )
        // update_post_meta( $id_product, '_wc_pos_outlet_stock', $outlet_cost );

        $cat_parent1_id = isset($cat_parent1_id) ? $cat_parent1_id : null;
        $cat_parent2_id = isset($cat_parent2_id) ? $cat_parent2_id : null;
        $cat_parent3_id = isset($cat_parent3_id) ? $cat_parent3_id : null;

        $cat_id = array_map('intval', array($cat_id, $cat_parent1_id, $cat_parent2_id, $cat_parent3_id));

        wp_set_object_terms( $post_id, $cat_id, 'product_cat' );

        if($row_product['Firearm']==1) {
            $product_tag = get_term_by( 'name', 'FFL_Firearm', 'product_tag');
            $tag_id = $product_tag->term_id;
            $tag_id = array_map('intval', array($tag_id) );
            wp_set_object_terms( $post_id, $tag_id, 'product_tag');
        }

        add_post_meta( $post_id, 'mra_pgsql_product', 'yes' );
        
        wp_set_object_terms( $post_id, 'simple', 'product_type' );
        update_post_meta( $post_id, '_visibility', 'visible' );
        update_post_meta( $post_id, '_sku', $upc_product );
        
        update_post_meta( $post_id, '_manage_stock', 'yes' );

        $outlet_stock = array();
        $stock = 0;
        if (get_option('added_vendor_list')) {
            // echo "1111<br>";
            foreach ($rows_product_vendor as $key => $product_vendor_db) {
                // echo "<br>11111 vendorid_db: ";
                // var_dump($product_vendor_db["vendorid"]);
                // echo "<br>";
                $added_vendor_meta_arr = unserialize( get_option('added_vendor_list') );
                foreach ($added_vendor_meta_arr as $key => $vendor) {
                    // echo "222222 dbid_wp: ";
                    // var_dump($vendor['dbid']);
                    // echo "<br>";
                    if($product_vendor_db['vendorid']==$vendor['dbid']) {
                        // echo "3333 stock_db: ";
                        $outlet_stock[$vendor['wpid']] = $product_vendor_db['quantity'];
                        $stock = $stock + $product_vendor_db['quantity'];
                        // var_dump($stock);
                        // echo "<br>";
                    }
                }
            }
            // var_dump($outlet_stock);
            $outlet_stock_serialize = serialize($outlet_stock);
            update_post_meta( $post_id, '_wc_pos_outlet_stock', $outlet_stock_serialize );
        } else {
            // echo "2222<br>";
            $stock = $row_product['quantity'];            
        }        
        update_post_meta( $post_id, '_stock', $stock );
        if($stock>0) $stock_status = 'instock'; else $stock_status = 'outofstock';
        update_post_meta( $post_id, '_stock_status', $stock_status);

        if(!$outlet_stock)
            $outlet_stock = get_post_meta( $post_id, '_wc_pos_outlet_stock');
        
        $outlet_cost = array();
        $outlet_cost_stock = array();
        $cost = 0;
        $cost_stok_id = '';
        if (get_option('added_vendor_list')) {
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
            
            update_post_meta( $post_id, '_wc_pos_outlet_cost', $outlet_cost_serialize );
        }

        update_post_meta( $post_id, '_wc_cog_cost', $cost );
        update_post_meta( $post_id, '_minimum_advertised_price', $row_product['MAP'] );
        
        $cog_cost = get_post_meta( $post_id, '_wc_cog_cost', true );
        $map = get_post_meta( $post_id, '_minimum_advertised_price', true );
        $mra_import_psql_select = get_option('mra_import_psql_select');
        $percent = get_option('mra_import_psql_percent');
        $dollar = get_option('mra_import_psql_dollar');

        if ($mra_import_psql_select=="percent") {
            $percent_num = $cog_cost * ($percent/100);
            $cost = $cog_cost + $percent_num;
        } elseif ($mra_import_psql_select=="value") {
            $cost = $cog_cost + $dollar;
        } elseif ($mra_import_psql_select=="map_percent" && $percent!='') {
            if ($map && $map!=0 && $map!='') {
                $cost = $map;
            } else {
                $percent_num = $cog_cost * ($percent/100);
                $cost = $cog_cost + $percent_num;
            }
        } elseif ($mra_import_psql_select=="map_value" && $dollar!='') {
            if ($map && $map!=0 && $map!='') {
                $cost = $map;
            } else {
                $cost = $cog_cost + $dollar;
            }
        }
        
        update_post_meta( $post_id, '_regular_price', $cost );
        update_post_meta( $post_id, '_price', $cost );


        // update_post_meta( $post_id, '_regular_price', $row_product['cost'] );
        
        update_post_meta( $post_id, '_weight', $row_product['Weight'] );
        update_post_meta( $post_id, '_length', $row_product['Length'] );
        update_post_meta( $post_id, '_width', $row_product['Width'] );
        update_post_meta( $post_id, '_height', $row_product['Height'] );

        // update_post_meta( $post_id, 'total_sales', '0' );
        // update_post_meta( $post_id, '_downloadable', 'no' );
        // update_post_meta( $post_id, '_virtual', 'yes' );            
        // update_post_meta( $post_id, '_sale_price', '' );
        // update_post_meta( $post_id, '_purchase_note', '' );
        // update_post_meta( $post_id, '_featured', 'no' );
        // update_post_meta( $post_id, '_sale_price_dates_from', '' );
        // update_post_meta( $post_id, '_sale_price_dates_to', '' );
        // update_post_meta( $post_id, '_sold_individually', '' );
        // update_post_meta( $post_id, '_backorders', 'no' );

        $attributes_a = wc_get_attribute_taxonomies();
        $slugs_a = wp_list_pluck( $attributes_a, 'attribute_name' );
        if ( ! in_array( 'pa_upc', $slugs_a ) ) {
            $args_a = array(
                'slug'    => 'pa_upc',
                'name'   => sanitize_title('upc'),
                'type'    => 'select',
                'orderby' => 'menu_order',
                'has_archives'  => false,
            );
            $result_a = wc_create_attribute( $args_a );
        }

        if ( ! in_array( 'pa_brand', $slugs_a ) ) {
            $args_a = array(
                'slug'    => 'pa_brand',
                'name'   => sanitize_title('brand'),
                'type'    => 'select',
                'orderby' => 'menu_order',
                'has_archives'  => false,
            );
            $result_a = wc_create_attribute( $args_a );
        }

        $custom_attributes = array();
        $custom_attributes['pa_upc'] = $upc_product;
        if(isset($row_product['Manufacturer']) && $row_product['Manufacturer']!='')
            $custom_attributes['pa_brand'] = $row_product['Manufacturer'];
        // if(isset($row_product['Model']) && $row_product['Model']!='')
        //     $custom_attributes['pa_brand'] = $row_product['Model'];


        $attrs_master = $mysqli_read_product->query("SELECT DISTINCT name FROM attributes_master ORDER BY name");
        if ($attrs_master) {
            $row_attrs = $attrs_master->fetch_all(MYSQLI_ASSOC);

            foreach ($row_attrs as $key => $attr) {
                $name = strtolower($attr['name']);
                $name = str_replace(' ', '_', $name);
                $name = str_replace('/', '_or_', $name);

                if ( ! in_array( 'pa_'.$name, $slugs_a ) ) {
                    $args_ats = array(
                        'slug'    => 'pa_'.$name,
                        'name'   => sanitize_title($name),
                        'type'    => 'select',
                        'orderby' => 'menu_order',
                        'has_archives'  => false,
                    );
                    $result_ats = wc_create_attribute( $args_ats );
                }
            }
        }

        $result_attrs_product = $mysqli_read_product->query("SELECT * FROM attributes_master WHERE upc=".$upc_product);
        if ($result_attrs_product) {
            $row_attrs_product = $result_attrs_product->fetch_all(MYSQLI_ASSOC);
            foreach ($row_attrs_product as $key => $attr_product) {
                $name_prod = strtolower($attr_product['name']);
                $name_prod = str_replace(' ', '_', $name_prod);
                $name_prod = str_replace('/', '_or_', $name_prod);

                $custom_attributes['pa_'.$name_prod] = $attr_product['value'];
            }
        }
        

        

        if(!empty($custom_attributes))
            save_wc_custom_attributes($post_id, $custom_attributes);

        if($product) {
            echo 'Product '.$row_product['Title'].' ('.$upc_product.') was successfully <span class="success_text">added</span>';

            $name_site = get_bloginfo('name');
            $homepage_link = get_bloginfo('url');
            $upc_product = $upc_product;

            $result_site = $mysqli_read_write_product->query("SELECT * FROM sites WHERE name='".$name_site."' AND homepage_link='".$homepage_link."'");
            if ($result_site->num_rows!=0) {
                $rows_site = $result_site->fetch_assoc();
                $site_id_db = $rows_site['id'];
            } else {
                $result_site = $mysqli_read_write_product->query("INSERT INTO sites VALUES (NULL, '$name_site', '$homepage_link')");
                $site_id_db = $mysqli_read_write_product->insert_id;
            }

            $result_site_product = $mysqli_read_write_product->query("SELECT * FROM added_products WHERE site_id=".$site_id_db." AND upc=".$upc_product);
            // var_dump($result_site_product); 
            if($result_site_product->num_rows==0) {
                $result_site_product = $mysqli_read_write_product->query("INSERT IGNORE INTO added_products (site_id, upc) VALUES ($site_id_db, $upc_product)");
            }          
        }
        else {
            echo '<span class="error_text">Addition error:</span> product '.$row_product['Title'].' ('.$upc_product.') has not been added';
        }


        
    } else {
        $product_id = $query->posts[0]->ID;
        $product_title = $query->posts[0]->post_title;
        echo 'The product with upc value '.$upc_product.' already exists in woocommerce and therefore was <span class="error_text">not added.</span> ID: '.$product_id.'; Title: '.$product_title;
    }

    if(!$mysqli_read_product->connect_error)
        $mysqli_read_product->close();
    if(!$mysqli_read_write_product->connect_error)
        $mysqli_read_write_product->close();
    
    wp_die();
}



/*
// Функция для получения всех категорий WooCommerce
function get_all_woocommerce_categories() {
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
    ));
    return $categories;
}

// Функция для поиска категории по названию
function find_category_by_name($categories, $name) {
    foreach ($categories as $category) {
        if ($category->name == $name) {
            return $category;
        }
    }
    return null;
}

// Функция для создания категории, если она не существует
function create_or_update_category($category_name, $parent_id = 0) {
    $term = term_exists($category_name, 'product_cat', $parent_id);

    if ($term === 0 || $term === null) {
        // Категория не существует, создаем новую
        $term = wp_insert_term(
            $category_name,
            'product_cat',
            array(
                'parent' => $parent_id,
                'slug'   => sanitize_title($category_name)
            )
        );

        if (is_wp_error($term)) {
            // Обработка ошибок при создании категории
            echo 'Ошибка при создании категории: ' . $term->get_error_message();
            return null;
        }

        return $term['term_id'];
    } else {
        // Категория существует, возвращаем её ID
        return $term['term_id'];
    }
}

// Функция для поиска категории в иерархии массива по названию
function find_category_in_tree($categories_tree, $category_name) {
    foreach ($categories_tree as $category) {
        if ($category['name'] == $category_name) {
            return $category;
        }
        if (!empty($category['children'])) {
            $result = find_category_in_tree($category['children'], $category_name);
            if ($result) {
                return $result;
            }
        }
    }
    return null;
}

// Функция для обновления иерархии категорий WooCommerce на основе массива
function update_woocommerce_categories_hierarchy($woocommerce_categories, $categories_tree, $parent_id = 0) {
    foreach ($woocommerce_categories as $woocommerce_category) {
        $category_name = $woocommerce_category->name;
        $tree_category = find_category_in_tree($categories_tree, $category_name);

        if ($tree_category) {
            $current_parent_id = $woocommerce_category->parent;

            if ($tree_category['parent_id'] !== null) {
                $parent_category = find_category_in_tree($categories_tree, $tree_category['parent_id']);
                if ($parent_category) {
                    $parent_id = create_or_update_category($parent_category['name'], 0);
                }
            } else {
                $parent_id = 0;
            }

            // Обновляем родительскую категорию, если необходимо
            if ($current_parent_id != $parent_id) {
                wp_update_term($woocommerce_category->term_id, 'product_cat', array('parent' => $parent_id));
            }
        }
    }
}

add_action( 'wp_ajax_mraupdatecategories', 'mraupdatecategories_func' );
function mraupdatecategories_func(){

    $woocommerce_categories = get_all_woocommerce_categories();
    update_woocommerce_categories_hierarchy($woocommerce_categories, mra_import_psql_db_arr_category());
    echo "Categories have been updated or collated!";
    wp_die();
} */