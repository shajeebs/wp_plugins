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