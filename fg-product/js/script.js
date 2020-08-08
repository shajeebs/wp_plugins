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
                var datafilter = jsonData.filter(el => el.id ==jQuery("#productName").val())[0];
                selectionList.push(datafilter);
                var markup = "<tr><td><input type='checkbox' name='pid' value='" + datafilter.id + "'/><input type='hidden' name='productIds[]' value='"+ datafilter.id +"' /></td><td>" + datafilter.name + "</td><td>" + datafilter.cost_price + "</td><td>" + datafilter.sale_price + "</td><td>" + datafilter.expiry_date + "</td></tr>";
                jQuery("table tbody").append(markup);
                UpdateCost();
            });
        }
        
        // Find and remove selected table rows
        jQuery(".delete-row").click(function(){
            jQuery("table tbody").find('input[name="record"]').each(function(){
            	if(jQuery(this).is(":checked")){
                    jQuery(this).parents("tr").remove();
                    UpdateCost();
                }
            });
        });

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
                        var markup = "<tr><td>" + prd.name + "</td><td>" + prd.product_type_name 
                        + "</td><td>" + prd.stock + "</td><td>" + prd.sale_price + "</td></tr>";
                        jQuery("#tbProdTypes tbody").append(markup);
                    });
                });
            }
        });
        // END - inventory-status.php 
    });    