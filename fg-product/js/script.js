jQuery(document).ready(function(){
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
            var markup = "<tr><td><input type='checkbox' name='record' value='" + datafilter.id + "'></td><td>" + datafilter.name + "</td><td>" + datafilter.cost_price + "</td><td>" + datafilter.sale_price + "</td><td>" + datafilter.expiry_date + "</td></tr>";
            jQuery("table tbody").append(markup);
            UpdateCost();
        });
        
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
    });    