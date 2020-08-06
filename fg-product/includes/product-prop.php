<?php

function fgpt_productPropFormPage_handler()
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
                print("jsonData Array <br>");
                $jsonData = json_decode($jsonData, true);
                foreach ($_REQUEST['productIds'] as $pid) {
                    foreach ($jsonData as $key => $jsonVal) {
                        //print("<br>JSON: pid:". $value['id']."  -".$value['name']);
                        if($jsonVal['id'] == $pid){
                            array_push($projProp, array(
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
                print("Final Array <br>");
                print_r($projProp);
                $result = $wpdb->insert($table_name1, $projProp);
                // //$sql = "INSERT INTO $table_name1(product_id, cost_price, sale_price, expiry_date)  SELECT id as product_id, cost_price, sale_price, DATE_ADD(created_at, INTERVAL 10 DAY) as expiry_date FROM $table_name WHERE id in ($prodIds)";
                    //  $sql = "INSERT INTO $table_name1(`product_id`, `cost_price`, `sale_price`, `expiry_date`)  SELECT `id` as `product_id`, `cost_price`, `sale_price`, DATE_ADD(`created_at`, INTERVAL 10 DAY) as `expiry_date`  FROM $table_name WHERE `id` in ($prodIds)";
                    //print($sql);
                // $result = $wpdb->insert($sql);
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
    
    add_meta_box('productPropForm_metaBox', __('Displays product properties.', 'fgpt'), 'fgpt_ProductProp_Handler', 'product_properties', 'normal', 'default');
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
    <!--<form action="<?php echo esc_url(admin_url('admin-post.php' )); ?>" method="POST" >-->
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>" />
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>" />
        <input type="hidden" name="action" value="add_product_properties" />

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('product_properties', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'fgpt')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}



function fgpt_ProductProp_Handler($item)
{ 

    $productListTable = new Product_List_Table();
    $jsonData = $productListTable->getRowMaterials();
    $dropdownData = $productListTable->getData();
    echo "<input type='hidden' id='rowMaterials' value='$jsonData'>";
    ?>
<tbody >
	
        <div class="formdatabc">		
        <div>
            <label for="productName"><?php _e('Row Product:', 'fgpt')?> </label><br>
            <select id="productName" name="productName" />
            <input type="decimal" id="costPrice" placeholder="Cost Price" >
            <input type="decimal" id="salePrice" placeholder="Sale Price">
            <input type="text" id="expDate" placeholder="Expiry Date">
            <input type="button" class="add-row" value="Add Row">
        </div>
            <table id="tbProdProps" class="wp-list-table widefat fixed striped products">
                <thead><tr><th>Select</th><th>Row Material</th><th>Cost Price</th><th>Sale Price</th><th>Expiry Date</th></tr></thead>
                <tbody></tbody>
            </table>
            <button type="button" class="delete-row">Delete Row</button><br>
        </div>
        <div class="form2bc">
        <p>			
		    <label for="name"><?php _e('Product Name:', 'fgpt')?></label><br>	
            <input id="name" name="name" type="text" value="<?php echo esc_attr($item['name'])?>" required>
		</p><p>	
            <label for="product_type_id"><?php _e('Product Type:', 'fgpt')?></label><br>
            <?php $preItem = esc_attr($item['product_type_id']);
            $elem = array_values(array_filter($dropdownData['prodTypes'], function($val){ return($val->id == ROWMATERIAL_PRODUCT_TYPE_ID); }))[0];
            $selected = ($elem->id == $preItem) ? 'selected=selected' : '';
             echo "<select name='product_type_id' name='product_type_id'>
             <option value='{$elem->id}' {$selected}>{$elem->name}</option></select>";
            ?>
        </p>
		</div>	
		<div class="form2bc">
			<p>
            <label for="category_id"><?php _e('Category:', 'fgpt')?></label><br>	
            <?php $preItem = esc_attr($item['category_id']);
            $elem = array_values(array_filter($dropdownData['prodCats'], function($val){ return($val['id'] == ROWMATERIAL_CATEGORY_ID); }))[0];
            $selected = ($elem['id'] == $preItem) ? 'selected=selected' : '';
             echo "<select name='category_id' name='category_id'>
             <option value='{$elem['id']}' {$selected}>{$elem['name']}</option></select>";
                ?>
        </p><p>	  
            <label for="tax_cat_id"><?php _e('Tax Category:', 'fgpt')?></label><br>
            <?php $preItem = esc_attr($item['tax_cat_id']);
            $elem = array_values(array_filter($dropdownData['taxCats'], function($val){ return($val['id'] == ROWMATERIAL_TAX_CATEGORY_ID); }))[0];
            $selected = ($elem['id'] == $preItem) ? 'selected=selected' : '';
             echo "<select name='tax_cat_id' name='tax_cat_id'>
             <option value='{$elem['id']}' {$selected}>{$elem['name']}</option></select>";
            ?>
		</p>
        <p>
            <label for="vendor"><?php _e('Vendor:', 'fgpt')?></label><br>	
            <?php $preItem = esc_attr($item['vendor']);
             echo '<select name="vendor" name="vendor">';
            foreach($dropdownData['vendors'] as $vendor){ 
                $vid = $vendor->id;
                $vnd = $vendor->first_name.' '.$vendor->last_name;
                $selected = ($vid == $preItem) ? 'selected=selected' : '';
                echo "<option value='$vid' $selected>$vnd</option>";
            } echo '</select>';
            ?>
        </p>
		</div>
        <div class="form3bc">
		<p>
            <label for="totalCost"><?php _e('Total Cost:', 'fgpt')?> </label><br>
            <input type="number" id="totalCost" name="cost_price" placeholder="Total Cost Price" required>
        </p><p>	  
            <label for="totalSales"><?php _e('Total Sales:', 'fgpt')?> </label><br>
            <input type="number" id="totalSales" name="sale_price" placeholder="Total Sales Price" required>
        </p><p>	  
            <label for="created_at"><?php _e('Created On:', 'fgpt')?></label><br>
			<input id="created_at" name="created_at" type="date" value="<?php echo esc_attr($item['created_at'])?>">
		</p>		
		</div>	
    <!--</form>-->
</tbody>
<?php
}