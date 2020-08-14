# wp_plugins
word press custom plugins

    require_once(ABSPATH . 'wp-content\plugins\erp\modules\accounting\includes\functions\products.php');
    $returnArray['prodTypes'] = erp_acct_get_product_types();

    require_once(ABSPATH . 'wp-content\plugins\erp\modules\accounting\includes\functions\product-cats.php');
    $returnArray['prodCats'] = erp_acct_get_all_product_cats();

    require_once(ABSPATH . 'wp-content\plugins\erp\modules\accounting\includes\functions\tax-cats.php');
    $returnArray['taxCats'] = erp_acct_get_all_tax_cats();

    require_once(ABSPATH . 'wp-content\plugins\erp\modules\accounting\includes\functions\people.php');
    $returnArray['vendors'] = erp_acct_get_accounting_people(
        [ 'type' => 'vendor' ]
    );


    GetProducts change added Stock, Product Type=3 and Quantity > 0(Sales):
    --------------------------------------------------------------------
    http://localhost/wordpressv1/wp-json/erp/v1/accounting/v1/products?number=-1
    api\class-rest-api-products.php
    \functions\products.php
    erp_acct_get_all_products()
    39: (product.product_type_id<>3) AND (stock.quantity > 0)  added in query

    Sales Rest API
    -----------------
    api\class-rest-api-invoices.php
    \functions\invoices.php
    erp_acct_insert_invoice_details_and_tax
     ----------------------------------------
    291: update stock added

    Purchase Rest API
    -----------------
    http://localhost/wordpressv1/wp-json/erp/v1/accounting/v1/purchases
    class-rest-api-purchases.php: create_purchase()
    erp_acct_insert_purchase()
    erp\modules\accounting\includes\functions\purchases.php
    table: erp_acct_purchase_details
    functions\purchases.php : erp_acct_insert_purchase()
    195: Update stock

    Vendor Products (Purchases)
    --------------
    http://localhost/wordpressv1/wp-json/erp/v1/accounting/v1/vendors/8/products?number=-1
    api\class-rest-api-vendors.php
    get_vendor_products()
    erp_acct_get_vendor_products()
    319: Query updated to get only Raw Material products