<?php

function fgpt_listProductProp_FormPage_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'erp_acct_products'; 
    $table_name1 = $wpdb->prefix.'fgpt_productproperties';

    $message = '';
    $notice = '';

    $defaultProd = array(
        'id'        => 0, 
        'name'      => '',
        'product_type_id'   => 1,
        'category_id'       => 1,
        'tax_cat_id'        => 1,
        'vendor'            => 1,
        'cost_price'        => '',  
        'sale_price'        => '',   
        'created_at'        => date("Y-m-d"),
    );

    if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        $item = shortcode_atts($defaultProd, $_REQUEST);     
        $item_valid = fgpt_validate_product($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                //$message = __('Success..!!', 'fgpt');
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Product was successfully saved', 'fgpt');

                $projProp = array();
                $productListTable = new Product_List_Table();
                $jsonData = $productListTable->getRowMaterials();
                //print("jsonData Array <br>");
                $jsonData = json_decode($jsonData, true);
                foreach ($_REQUEST['productIds'] as $pid) {
                    foreach ($jsonData as $key => $jsonVal) {
                        if($jsonVal['id'] == $pid){
                            $result = $wpdb->insert($table_name1, array(
                                "parent_prod_id" => $item['id'],
                                "prod_id" => $jsonVal['id'],
                                "cost_price" => $jsonVal['cost_price'],
                                "sale_price" => $jsonVal['sale_price'],
                                "expiry_date" => $jsonVal['expiry_date'],
                                "created_at" => date("Y-m-d")
                            ));
                        }
                    }
                    
                }
                if ($result) 
                        $message = __('Product and properties was successfully saved', 'fgpt');
                    else 
                        $notice = __('There was an error while saving Product properties', 'fgpt');
                } else {
                    $notice = __('There was an error while saving Product', 'fgpt');
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'fgpt');
                } else {
                    $notice = __('There was an error while updating item', 'fgpt');
                }
            }
        } else {
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $defaultProd;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $defaultProd;
                $notice = __('Item not found', 'fgpt');
            }
        }
    }
    $productListTable = new Product_List_Table();
    $finishedProducts = $productListTable->getFinishedProducts();

    // echo "<input type='hidden' id='rowMaterials' value='$jsonData'>";
    // add_meta_box('productPropForm_metaBox', __('Displays product properties.', 'fgpt'), 'fgpt_ListProductProp_Handler', 'list_product_properties', 'normal', 'default');
    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Make Product', 'fgpt')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=products');?>"><?php _e('Back to list', 'fgpt')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST" >
        <input type="hidden" name="action" value="list_product_properties" />

        <select id="productProps">
        <option value="">--SELECT PRODUCT--</option>
        <?php foreach ($finishedProducts as $prod) { ?>
            <option value="<?php echo $prod['id']; ?>"><?php echo $prod['name']; ?></option>
        <?php } ?>
        </select>
        <div class="load-state">
            <table id="tbProdProps" class="wp-list-table widefat fixed striped products">
                <thead><tr><th>Row Material</th><th>Cost Price</th><th>Sale Price</th><th>Expiry Date</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <!--<div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('list_product_properties', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'fgpt')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>-->
    </form>
</div>
<?php
}

