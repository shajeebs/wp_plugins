<?php
function fgpt_inventorystatus_page_handler()
{
    $productListTable = new Product_List_Table();
    $productTypes = $productListTable->getProductTypes();
 ?>
 <div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Inventory Status', 'fgpt')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=products');?>"><?php _e('Back to list', 'fgpt')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>
        <div  class="postbox"> 
            <div class="rowLayout">
                <p> Display the details about Inventory Stock </p>
                <label for="productTypes"><?php _e('Select Product Type:', 'fgpt')?></label><br>
                <select id="productTypes">
                <option value="">--SELECT PRODUCT TYPE--</option>
                <?php foreach ($productTypes as $prodType) { ?>
                    <option value="<?php echo $prodType['id']; ?>"><?php echo $prodType['name']; ?></option>
                <?php } ?>
                </select>
                <div class="load-state">
                    <table id="tbProdTypes" class="wp-list-table widefat fixed striped products tableStyle">
                        <thead><tr><th>Product</th><th>Type</th><th>Stock</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            
        </div>
</div>
<?php
}

