<?php
function fgpt_inventorystatus_page_handler()
{
    $productListTable = new Product_List_Table();
    $productTypes = $productListTable->getProductTypes();
 ?>

    <form id="form" method="POST" >
        <p> Display the details about Inventory Stock </p>
        <label for="productTypes"><?php _e('Select Product Type:', 'fgpt')?></label><br>
        <select id="productTypes">
        <option value="">--SELECT PRODUCT TYPE--</option>
        <?php foreach ($productTypes as $prodType) { ?>
            <option value="<?php echo $prodType['id']; ?>"><?php echo $prodType['name']; ?></option>
        <?php } ?>
        </select>
        <div class="load-state">
            <table id="tbProdTypes" class="wp-list-table widefat fixed striped products">
                <thead><tr><th>Product</th><th>Type</th><th>Stock</th><th>Status</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </form>
</div>
<?php
}

