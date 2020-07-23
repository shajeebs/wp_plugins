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

    // $default = array(
    //     'id' => 0,
    //     'name'      => '',
    //     'lastname'  => '',
    //     'email'     => '',
    //     'phone'     => null,
    //     'company'   => '',
    //     'web'       => '',  
    //     'two_email' => '',   
    //     'two_phone' => '',
    //     'job'       => '',        
    //     'address'   => '',
    //     'notes'     => '',
    // );
    $default = array(
        'id'        => 0, 
        'name'      => '',
        'product_type_id'   => 1,
        'category_id'       => 1,
        'tax_cat_id'        => 1,
        'vendor'            => 1,
        'cost_price'        => 0,  
        'sale_price'        => 0,   
        'created_at'        => '',
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
    ?>
<tbody >
		
	<div class="formdatabc">		
		
    <form >
		<div class="form2bc">
        <p>			
		    <label for="name"><?php _e('Name:', 'fgpt')?></label>
		<br>	
            <input id="name" name="name" type="text" value="<?php echo esc_attr($item['name'])?>"
                    required>
		</p><p>	
            <label for="lastname"><?php _e('Last Name:', 'fgpt')?></label>
		<br>
		    <input id="lastname" name="lastname" type="text" value="<?php echo esc_attr($item['lastname'])?>"
                    required>
        </p>
		</div>	
		<div class="form2bc">
			<p>
            <label for="email"><?php _e('E-Mail:', 'fgpt')?></label> 
		<br>	
            <input id="email" name="email" type="email" value="<?php echo esc_attr($item['email'])?>"
                   required>
        </p><p>	  
            <label for="phone"><?php _e('Phone:', 'fgpt')?></label> 
		<br>
			<input id="phone" name="phone" type="tel" value="<?php echo esc_attr($item['phone'])?>">
		</p>
		</div>
		<div class="form2bc">
			<p>
            <label for="company"><?php _e('Company:', 'fgpt')?></label> 
		<br>	
            <input id="company" name="company" type="text" value="<?php echo esc_attr($item['company'])?>">
        </p><p>	  
            <label for="web"><?php _e('Web:', 'fgpt')?></label> 
		<br>
			<input id="web" name="web" type="text" value="<?php echo esc_attr($item['web'])?>">
		</p>
		</div>	
		<div class="form3bc">
		<p>
            <label for="email"><?php _e('E-Mail:', 'fgpt')?></label> 
		<br>	
            <input id="email" name="two_email" type="email" value="<?php echo esc_attr($item['two_email'])?>">
        </p><p>	  
            <label for="phone"><?php _e('Phone:', 'fgpt')?></label> 
		<br>
			<input id="phone" name="two_phone" type="tel" value="<?php echo esc_attr($item['two_phone'])?>">
		</p><p>	  
            <label for="job"><?php _e('Job Title:', 'fgpt')?></label> 
		<br>
			<input id="job" name="job" type="text" value="<?php echo esc_attr($item['job'])?>">
		</p>		
		</div>	
		<div>		
			<p>
		    <label for="address"><?php _e('Address:', 'fgpt')?></label> 
		<br>
            <textarea id="addressbc" name="address" cols="100" rows="3" maxlength="240"><?php echo esc_attr($item['address'])?></textarea>
		</p><p>  
            <label for="notes"><?php _e('Notes:', 'fgpt')?></label>
		<br>
            <textarea id="notesbc" name="notes" cols="100" rows="3" maxlength="240"><?php echo esc_attr($item['notes'])?></textarea>
		</p>
		</div>	
		</form>
		</div>
</tbody>
<?php
}
