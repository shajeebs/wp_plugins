<?php
function fgpt_products_page_handler()
{
    global $wpdb;

    $table = new Product_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'fgpt'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Products', 'fgpt')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=products_form');?>"><?php _e('Add new', 'fgpt')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="products-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}


function fgpt_products_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'erp_acct_products'; 

    $message = '';
    $notice = '';

    $default = array(
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


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = fgpt_validate_product($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('Item was successfully saved', 'fgpt');
                } else {
                    $notice = __('There was an error while saving item', 'fgpt');
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
        
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'fgpt');
            }
        }
    }

    
    add_meta_box('products_form_meta_box', __('Product data', 'fgpt'), 'fgpt_products_form_meta_box_handler', 'product', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Product', 'fgpt')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=products');?>"><?php _e('back to list', 'fgpt')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('product', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'fgpt')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

function fgpt_products_form_meta_box_handler($item)
{
    $productListTable = new Product_List_Table();
    $dropdownData = $productListTable->getData();
    ?>
<tbody >
		
	<div class="formdatabc">		
		
    <form >
		<div class="form2bc">
        <p>			
		    <label for="name"><?php _e('Product Name:', 'fgpt')?></label>
		<br>	
            <input id="name" name="name" type="text" value="<?php echo esc_attr($item['name'])?>" required>
		</p><p>	
            <label for="product_type_id"><?php _e('Product Type:', 'fgpt')?></label>
		<br>
            <?php $preItem = esc_attr($item['product_type_id']);
             echo '<select name="product_type_id" name="product_type_id">';
            foreach($dropdownData['prodTypes'] as $prodType){ 
                $id = $prodType->id;
                $prod = $prodType->name;
                $selected = ($id == $preItem) ? 'selected=selected' : '';
                echo "<option value='$id' $selected>$prod</option>";
            } echo '</select>';?>
        </p>
		</div>	
		<div class="form2bc">
			<p>
            <label for="category_id"><?php _e('Category:', 'fgpt')?></label> 
		<br>	
            <?php $preItem = esc_attr($item['category_id']);
                echo '<select name="category_id" name="category_id">';
                foreach($dropdownData['prodCats'] as $prodCat){ 
                    $id = $prodCat['id'];
                    $prod = $prodCat['name'];
                    $selected = ($id == $preItem) ? 'selected=selected' : '';
                    echo "<option value='$id' $selected>$prod</option>";
                } echo '</select>'; 
                ?>
        </p><p>	  
            <label for="tax_cat_id"><?php _e('Tax Category:', 'fgpt')?></label> 
		<br>
            <?php $preItem = esc_attr($item['tax_cat_id']);
            //print "Value - $preItem";
            echo '<select name="tax_cat_id" name="tax_cat_id">';
            foreach($dropdownData['taxCats'] as $taxCat){ 
                $id = $taxCat['id'];
                $tx = $taxCat['name'];
                $selected = ($id == $preItem) ? 'selected=selected' : '';
                echo "<option value='$id' $selected>$tx</option>";
            } echo '</select>';?>
		</p>
        <p>
            <label for="vendor"><?php _e('Vendor:', 'fgpt')?></label> 
		    <br>	
            <?php $preItem = esc_attr($item['vendor']);
             echo '<select name="vendor" name="vendor">';
            foreach($dropdownData['vendors'] as $vendor){ 
                $id = $vendor->id;
                $vnd = $vendor->first_name.' '.$vendor->last_name;
                $selected = ($id == $preItem) ? 'selected=selected' : '';
                echo "<option value='$id' $selected>$vnd</option>";
            } echo '</select>';
            ?>
        </p>
		</div>
		<div class="form3bc">
		<p>
            <label for="cost_price"><?php _e('Cost Price:', 'fgpt')?></label> 
		<br>	
            <input id="cost_price" name="cost_price" type="number" value="<?php echo esc_attr($item['cost_price'])?>" required>
        </p><p>	  
            <label for="sale_price"><?php _e('Sale Price:', 'fgpt')?></label> 
		<br>
			<input id="sale_price" name="sale_price" type="number" value="<?php echo esc_attr($item['sale_price'])?>" required>
		</p><p>	  
            <label for="created_at"><?php _e('Created On:', 'fgpt')?></label> 
		<br>
			<input id="created_at" name="created_at" type="date" value="<?php echo esc_attr($item['created_at'])?>">
		</p>		
		</div>	
		</form>
		</div>
</tbody>
<?php
}
