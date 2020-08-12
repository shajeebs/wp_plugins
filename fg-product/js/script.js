jQuery(document).ready(function(){
        if(jQuery('#rowMaterials').length){
            var jsonData = JSON.parse(jQuery("#rowMaterials").val());
            jQuery("#product_type_id").prop("disabled", true);
            var selectionList = [];
            jQuery(jsonData).each(function() {
                jQuery("#productName").append(jQuery("<option />").val(this.id).text(this.name));
            });

            jQuery("#productName").change(function () {
                var datafilter = jsonData.filter(el => el.id == this.value)[0];
                jQuery("#costPrice").val(datafilter.cost_price);
                jQuery("#salePrice").val(datafilter.sale_price);
                jQuery("#expDate").val(datafilter.expiry_date);
            });
            jQuery(".add-row").click(function(){
                var selectedItem = jQuery("#productName").val();
                var datafilter = jsonData.filter(el => el.id == selectedItem)[0];
                selectionList.push(datafilter);
                var markup = "<tr><td><input type='hidden' name='productIds[]' value='"+ datafilter.id +"' /></td><td>" + datafilter.name + "</td><td>" + datafilter.cost_price + "</td><td>" + datafilter.sale_price + "</td><td><input type='number' name='quantities[]' value='1' /></td><td>" + datafilter.expiry_date + "</td><td><a href='#' alt='Delete Row' class='deleterow'>X</a></td></tr>";
                jQuery("table tbody").append(markup);
                jQuery("#productName option[value='" + selectedItem + "']").remove();
                UpdateCost();
            });
        }

        function UpdateCost(){  
            jQuery("#totalCost").val(calculateColumn(2));
            jQuery("#totalSales").val(calculateColumn(3));
        }

        function calculateColumn(index) {
            var total = 0;
            jQuery('table tr').each(function() {
                var value = parseInt(jQuery('td', this).eq(index).text());
                if (!isNaN(value)) {
                    total += value;
                }
            });
            return total;
        }

        // START - list-product-prop.php 
        jQuery("#productProps").change(function(){
            if(this.value != '') {
                var data = {'action': 'get_products_ajax','pid': this.value }
                jQuery.post("admin-ajax.php", data, function(response) {
                    jQuery("#tbProdProps tbody").empty();
                    jQuery.each(JSON.parse(response), function(i, prd) {
                        //alert(item.name);
                        var markup = "<tr><td>" + prd.name + "</td><td>" + prd.cost_price + "</td><td>" + prd.sale_price + "</td><td>" + prd.expiry_date + "</td></tr>";
                        jQuery("#tbProdProps tbody").append(markup);
                    });
                });
            }
        });

        jQuery('#tbProdProps').on('click','tr a',function(e){
            e.preventDefault();
            var pid = jQuery(this).parents('tr').find('input[name="productIds[]"]').val();
            var datafilter = jsonData.filter(el => el.id == pid)[0];
            jQuery("#productName").append(jQuery("<option />").val(datafilter.id).text(datafilter.name));
            jQuery(this).parents('tr').remove();
        });
        // END - list-product-prop.php 


        // START - inventory-status.php 
        jQuery("#productTypes").change(function(){
            if(this.value != '') {
                var data = {'action': 'get_producttypes_ajax','typeid': this.value }
                jQuery.post("admin-ajax.php", data, function(response) {
                    //alert(response);
                    jQuery("#tbProdTypes tbody").empty();
                    jQuery.each(JSON.parse(response), function(i, prd) {
                        //alert(item.name);
                        var stock = parseInt(prd.stock);
                        var stockStatus = (stock > 0 ? "In Stock": "Out Of Stock");
                        var rowStyle = (stock > 0 ? "inStock": "outOfStock");
                        var markup = "<tr id='" + prd.id + "' class='"+rowStyle+"'><td>" + prd.name + "</td><td>" + prd.product_type_name 
                        + "</td><td class='stockTd'>" + prd.stock + "<input type='number' class='stockVal' style='width: 90px; display: none;'></td><td>" + stockStatus 
                        + `</td><td class='stockAction'>
                        <a href='#' alt='Add Stock' class='addStock' >Add Stock</a>
                        <input type="button" id="btnSaveStock" value="Save" onclick="saveStock(this)" style="display: none;">
                        <input type="button" id="btnCancel" value="Cancel" onclick="cancelStock(this)" style="display: none;">
                        </td></tr>`;
                        jQuery("#tbProdTypes tbody").append(markup);
                    });
                });
            }
        });

        jQuery('#tbProdTypes').on('click','tr a',function(e){
            //e.preventDefault();
            jQuery(this).hide();
            jQuery(this).parents('tr').find('#btnSaveStock').show();
            jQuery(this).parents('tr').find('#btnCancel').show();
            jQuery(this).parents('tr').find('.stockVal').show();
        });

        // END - inventory-status.php 
    });    

    saveStock = (btnSave) => {
        var pid = jQuery(btnSave).parents('tr').attr('id');
        var stockInput = jQuery(btnSave).closest('tr').find('.stockVal');
        var stock = stockInput.val();
        alert("Product Id: " + pid + ", Stock updated with the value : " + stock);
        cancelStock(btnSave);
        jQuery(btnSave).closest('tr').find('.stockTd').html(stock);
        jQuery(btnSave).closest('tr').find('.stockTd').append(stockInput);
    }

    cancelStock = (btnCancel) => {
        jQuery(btnCancel).parents('tr').find('.stockVal').hide();
        jQuery(btnCancel).closest('td').find('.addStock').show();
        jQuery(btnCancel).closest('td').find('#btnSaveStock').hide();
        jQuery(btnCancel).closest('td').find('#btnCancel').hide();
    }