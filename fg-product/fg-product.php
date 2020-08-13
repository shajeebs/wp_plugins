<?php
/*
* Plugin Name: fg-product 
* Description: This plugin to create custom product list-tables from database using WP_List_Table class.
* Version:     1.1.1
* Author:      shajeeb
* Author URI:  http://nimra-tech.com/
* License:     GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: fgpt
* Domain Path: /languages
*/

defined('ABSPATH') or die( '¡Sin trampas!' );
define("ROWMATERIAL_PRODUCT_TYPE_ID", 3);
define("ROWMATERIAL_CATEGORY_ID", 3);
define("ROWMATERIAL_TAX_CATEGORY_ID", 2);
define("FP_PRODUCT_TYPE_ID", 1);
define("FP_CATEGORY_ID", 2);
define("FP_TAX_CATEGORY_ID", 2);

require plugin_dir_path( __FILE__ ) . 'includes/metabox-product.php';
// require plugin_dir_path( __FILE__ ) . 'includes/make-product.php';
require plugin_dir_path( __FILE__ ) . 'includes/add-product-prop.php';
require plugin_dir_path( __FILE__ ) . 'includes/list-product-prop.php';
require plugin_dir_path( __FILE__ ) . 'includes/inventory-status.php';

function fgpt_fgproduct_styles() {
    // wp_enqueue_style('jquerysctipttop', 'https://www.jqueryscript.net/css/jquerysctipttop.css', array(),  '1.1.1', true);
    // wp_enqueue_style('fontawesome', 'https://use.fontawesome.com/releases/v5.3.1/css/all.css', array(),  '5.3.1', true);
    // wp_enqueue_style('bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/4.1.3/darkly/bootstrap.min.css', array(),  '5.3.1', true);

    wp_enqueue_style('fgproduct-styles', plugins_url('/css/styles.css', __FILE__ ));
}
add_action('admin_enqueue_scripts', 'fgpt_fgproduct_styles');
// add_action('wp_enqueue_scripts', 'fgpt_fgproduct_styles');

function fgpt_fgproduct_scripts() {
    // wp_enqueue_script('jquery-cdn', 'https://code.jquery.com/jquery-3.3.1.slim.min.js', array(),  '3.3.1', true);
    // wp_enqueue_script('popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array('jquery-cdn'),  '1.14.3', true);
    // wp_enqueue_script('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', array(),  '4.1.3', true);
    // wp_enqueue_script('jquery-cdn', 'https://code.jquery.com/jquery-3.3.1.slim.min.js', array(),  '3.3.1', true);
    
    wp_enqueue_script('fgproduct-scripts', plugins_url('/js/script.js', __FILE__ ), false, null, true);
    // wp_enqueue_script('fgproduct-bootstable', plugins_url('/js/bootstable.js', __FILE__ ), false, null, true);
}
add_action('admin_enqueue_scripts', 'fgpt_fgproduct_scripts' );
// add_action('wp_enqueue_scripts', 'fgpt_fgproduct_scripts');

function fgpt_plugin_load_textdomain() {
    load_plugin_textdomain( 'fgpt', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'fgpt_plugin_load_textdomain' );


global $fgpt_db_version;
$fgpt_db_version = '1.1.0'; 

function fgpt_install()
{
    global $wpdb;
    global $fgpt_db_version;

    $table_Product = $wpdb->prefix . 'erp_acct_products'; 
    $table_ProductProp = $wpdb->prefix . 'fgpt_productproperties'; 
    $table_ProductStock = $wpdb->prefix . 'fgpt_productstock'; 

    // Product Properties
    $sql = "CREATE TABLE IF NOT EXISTS $table_ProductProp (
        id int(11) AUTO_INCREMENT PRIMARY KEY,
        parent_prod_id     INT NOT NULL,
        prod_id     INT NOT NULL,
        cost_price decimal(20,2) NOT NULL,
        sale_price	decimal(20,2) NOT NULL,
        quantity INT NOT NULL,
        expiry_date date NOT NULL,
        created_at date NOT NULL,
        CONSTRAINT fk_prd_product_id
        FOREIGN KEY (parent_prod_id) REFERENCES $table_Product(id) ON DELETE CASCADE);";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Stock Details
    $sql = "CREATE TABLE IF NOT EXISTS $table_ProductStock (
        id int(11) AUTO_INCREMENT PRIMARY KEY,
        prod_id     INT NOT NULL,
        quantity INT NOT NULL,
        updated_at date NULL,
        created_at date NOT NULL,
        CONSTRAINT fk_stock_product_id
        FOREIGN KEY (prod_id) REFERENCES $table_Product(id) ON DELETE CASCADE);";

    //require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'fgpt_install');

function fgpt_install_data(){
    global $wpdb;
    $table_Product = $wpdb->prefix . 'erp_acct_products'; 
    $table_ProductStock = $wpdb->prefix . 'fgpt_productstock';
// INSERT INTO wp_fgpt_productstock(`prod_id`, `quantity`)
// SELECT id, 0 FROM wp_erp_acct_products  
//      WHERE id NOT in (SELECT prod_id FROM wp_fgpt_productstock) 

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_Product 
     WHERE id NOT IN (SELECT `prod_id` FROM $table_ProductStock) 
     AND `product_type_id` in (%d, %d)", ROWMATERIAL_PRODUCT_TYPE_ID, FP_PRODUCT_TYPE_ID), ARRAY_A);

    foreach ($results as $val) {
            $result = $wpdb->insert($table_ProductStock, array(
                "prod_id" => $val['id'],
                "quantity" => 3000,
                "created_at" => date("Y-m-d")
            ));
    }

}
register_activation_hook(__FILE__, 'fgpt_install_data');

function fgpt_admin_menu()
{
    add_menu_page(__('Special Products', 'fgpt'), __('Special Products', 'fgpt'), 'activate_plugins', 'products', 'fgpt_rawmaterials_page_handler');
    add_submenu_page('products', __('Row Materials', 'fgpt'), __('Row Materials', 'fgpt'), 'activate_plugins', 'products', 'fgpt_rawmaterials_page_handler');
    add_submenu_page('products', __('New Raw Material', 'fgpt'), __('New Raw Material', 'fgpt'), 'activate_plugins', 'products_form', 'fgpt_products_form_page_handler');
    // add_submenu_page('products', __('Make Product', 'fgpt'), __('Make Product', 'fgpt'), 'activate_plugins', 'make_product_form', 'fgpt_makeProductFormPage_handler');
    add_submenu_page('products', __('Finished Products', 'fgpt'), __('Finished Products', 'fgpt'), 'activate_plugins', 'list_prop_form', 'fgpt_listProductProp_FormPage_handler');
    add_submenu_page('products', __('Recipes', 'fgpt'), __('Recipes', 'fgpt'), 'activate_plugins', 'product_prop_form', 'fgpt_addProductProp_FormPage_handler');
    add_submenu_page('products', __('Inventory Status', 'fgpt'), __('Inventory Status', 'fgpt'), 'activate_plugins', 'inventory_status', 'fgpt_inventorystatus_page_handler');
}
add_action('admin_menu', 'fgpt_admin_menu');

/*
function fgpt_update_db_check()
{
    global $fgpt_db_version;
    if (get_site_option('fgpt_db_version') != $fgpt_db_version) {
        fgpt_install();
    }
}

add_action('plugins_loaded', 'fgpt_update_db_check');
*/


if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Product_List_Table extends WP_List_Table
 { 
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'product',
            'plural'   => 'products',
        ));
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_phone($item)
    {
        return '<em>' . $item['phone'] . '</em>';
    }


    function column_name($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="?page=products_form&id=%s">%s</a>', $item['id'], __('Edit', 'fgpt')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'fgpt')),
        );

        return sprintf('%s %s',
            $item['name'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'name'      => __('Product', 'fgpt'),
            'product_type_name'  => __('Product Type', 'fgpt'),
            'stock'     => __('Stock', 'fgpt'),
            // 'cat_name'     => __('Category', 'fgpt'),
            // //'tax_cat_id'     => __('Tax Category', 'fgpt'),
            // 'vendor_name'   => __('Vendor', 'fgpt'),
            'cost_price'       => __('Cost Price', 'fgpt'),  
            'sale_price' => __('Sale Price', 'fgpt'),   
            'created_at'       => __('Created On', 'fgpt'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'name'      => array('name', true),
            'product_type_name'  => array('product_type_name', true),
            'stock'     => array('stock', true),
            // 'cat_name'     => array('cat_name', true),
            // //'tax_cat_id'     => array('tax_cat_id', true),
            // 'vendor_name'   => array('vendor_name', true),
            'cost_price'       => array('cost_price', true),  
            'sale_price' => array('sale_price', true),   
            'created_at' => array('created_at', true),  
        );

        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_Product = $wpdb->prefix . 'erp_acct_products'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_Product WHERE id IN($ids)");
            }
        }
    }

   function getJsonData($url){
        $request = new WP_REST_Request( 'GET', $url);
        $request->set_query_params( [ 'per_page' => 12 ] );
        $response = rest_do_request( $request );
        $server = rest_get_server();
        $data = $server->response_to_data( $response, false );
        $json = wp_json_encode( $data );
        return $json;
    }

    function getRowMaterials(){
        $per_page = 10;
        $paged = 0;
        global $wpdb;
        $rowMaterials = $wpdb->get_results($wpdb->prepare("SELECT id, name, cost_price, 
            sale_price, created_at, DATE_ADD(created_at, INTERVAL 10 DAY) as expiry_date 
            FROM wp_erp_acct_products where product_type_id=3 
            LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        $jsonVal =json_encode($rowMaterials);
        return $jsonVal;
    }

    // START - list-product-prop.php 
    function getFinishedProducts(){
        $per_page = 10;
        $paged = 0;
        global $wpdb;
        $products = $wpdb->get_results($wpdb->prepare("SELECT p.id, p.name, count(*) 
            FROM wp_erp_acct_products as p 
            INNER JOIN wp_fgpt_productproperties pp on p.id = pp.parent_prod_id
            GROUP BY p.id, p.name HAVING count(*) > 0 
            LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        return $products;
    }// END - list-product-prop.php 

    // START - inventory-status.php 
    function getProductTypes(){
        global $wpdb;
        $productTypes = $wpdb->get_results($wpdb->prepare("SELECT `id`, `name` FROM `wp_erp_acct_product_types`LIMIT %d OFFSET %d", 10, 0), ARRAY_A);
        return $productTypes;
    }// END - inventory-status.php 

    function getData(){
        $returnArray = [
            'prodTypes' => [],
            'prodCats' => [],
            'taxCats'  => [],
            'vendors'  => [],
        ];

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
        //print_r($returnArray);
        return $returnArray;
    }

    function prepare_items()
    {
        global $wpdb;
        $table_Product = $wpdb->prefix . 'erp_acct_products'; 
        $per_page = 10; 
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_Product");
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';
      
        $this->getItems(ROWMATERIAL_PRODUCT_TYPE_ID, $orderby, $order, $per_page, $paged);

        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }

    function getItems($productType = ROWMATERIAL_PRODUCT_TYPE_ID, $orderby="name", $order="ASC", $per_page="100", $paged="0")
    {
        global $wpdb;
        $table_Product = $wpdb->prefix . 'erp_acct_products'; 
        $sql = $wpdb->prepare("SELECT product.id, product.name, 
        product.product_type_id, stock.quantity as stock, product.cost_price, product.sale_price, product.tax_cat_id, 
        people.id AS vendor, CONCAT(people.first_name, ' ', people.last_name) AS vendor_name, 
        cat.id AS category_id, cat.name AS cat_name, product_type.name AS product_type_name, 
        product.created_at
        FROM wp_erp_acct_products AS product 
        LEFT JOIN wp_erp_peoples AS people ON product.vendor = people.id 
        LEFT JOIN wp_erp_acct_product_categories AS cat ON product.category_id = cat.id 
        LEFT JOIN wp_erp_acct_product_types AS product_type ON product.product_type_id = product_type.id 
        LEFT JOIN wp_fgpt_productstock AS stock ON stock.prod_id = product.id 
        WHERE product.product_type_id = %d
        ORDER BY $orderby $order 
        LIMIT %d OFFSET %d", $productType, $per_page, $paged);
        $items = $wpdb->get_results($sql, ARRAY_A);
        $this->items = $items;
        return $items;
    }

    function getItem($id){
        global $wpdb;
        $item = $wpdb->get_row($wpdb->prepare("SELECT *, s.quantity as stock_quantity 
        FROM wp_erp_acct_products as p 
        INNER JOIN wp_fgpt_productstock s on p.id=s.prod_id 
        WHERE p.id=%d", $id), ARRAY_A);
        return $item;
    }

    function saveItem($item, &$message, &$notice){
        global $wpdb;
        $table_product = $wpdb->prefix.'erp_acct_products'; 
        $table_productstock = $wpdb->prefix.'fgpt_productstock'; 
        if ($item['id'] == 0) {
            $wpdb->query('START TRANSACTION');
            $insertItem = $item;
            unset($insertItem['stock_quantity']);
            $result = $wpdb->insert($table_product, $insertItem);
            $item['id'] = $wpdb->insert_id;
            $stockResult = $wpdb->insert($table_productstock, array('prod_id' => $item['id'], 
                                                                    'quantity'=> $item['stock_quantity'], 
                                                                    'created_at' => date("Y-m-d")));
            if($result || $stockResult) {
                $wpdb->query('COMMIT'); // if you come here then well done
                $message = __('Item was successfully saved', 'fgpt');
            }
            else {
                $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
                $notice = __('There was an error while saving item', 'fgpt');
            }
        } 
        else {
            $updateItem = $item;
            unset($updateItem['stock_quantity']);
            print_r($item);
            print("<br>");
            $wpdb->query('START TRANSACTION');
            $updateProductResult = $wpdb->update($table_product, $updateItem, array('id' => $updateItem['id']));
            print("<br> Inserted prop: $updateProductResult <br>");
            print("<br> Inserted ID: ".$item['id']." <br>");
            $updateStockResult = $wpdb->update("wp_fgpt_productstock", 
                                            array('quantity'=> $item['stock_quantity'],
                                                  'updated_at' => date("Y-m-d")),
                                            array('prod_id'=>$item['id']));
            print("<br> Updated stock: $updateStockResult <br>");
            if($updateProductResult || $updateStockResult) {
                $wpdb->query('COMMIT'); // if you come here then well done
                $message = __('Item was successfully updated', 'fgpt');
            }
            else {
                $wpdb->query('ROLLBACK'); // something went wrong, Rollback
                $notice = __('There was an error while updating item', 'fgpt');
            }
        }
    }

    function saveItemProperties($item, $prodPropQuantities, &$message, &$notice){
            global $wpdb;
            $table_product = $wpdb->prefix.'erp_acct_products'; 
            $table_productstock = $wpdb->prefix.'fgpt_productstock'; 
            $table_ProductPropeties = $wpdb->prefix.'fgpt_productproperties';
            if ($item['id'] == 0) {
            //$message = __('Success..!!', 'fgpt');
            $wpdb->query('START TRANSACTION');
            $insertItem = $item;
            unset($insertItem['stock_quantity']);
            $resultProd = $wpdb->insert($table_product, $insertItem);
            $item['id'] = $wpdb->insert_id;
            print("<br> Added new Product Pid: ".$item['id'].": Name:".$item['name'].", Res:$resultProd <br>");
            if ($resultProd) {
                //$message = __('Product was successfully saved', 'fgpt');

                $projProp = array();
                //$productListTable = new Product_List_Table();
                $jsonRowMaterials = $this->getRowMaterials();
                //print("jsonRowMaterials Array <br>");
                $jsonRowMaterials = json_decode($jsonRowMaterials, true);
                foreach ($prodPropQuantities as $pid => $qty) {
                    foreach ($jsonRowMaterials as $key => $jsonVal) {
                        //print("<br>JSON: pid:". $jsonVal['id']."  -".$jsonVal['name']);
                        if($jsonVal['id'] == $pid){
                            $stockQuantity = $wpdb->get_var("SELECT quantity FROM wp_fgpt_productstock WHERE prod_id = ".$jsonVal['id']);
                            print("Product properties current Stock: Pid: $pid: Qty:$stockQuantity <br>");
                            $stockQuantity = $stockQuantity - $qty;
                            $resultProdProp = $wpdb->insert($table_ProductPropeties, array(
                                "parent_prod_id" => $item['id'],
                                "prod_id" => $jsonVal['id'],
                                "cost_price" => $jsonVal['cost_price'],
                                "sale_price" => $jsonVal['sale_price'],
                                "quantity" => $qty,
                                "expiry_date" => $jsonVal['expiry_date'],
                                "created_at" => date("Y-m-d")
                            ));
                            print("<br> Added new Product Properties: $wpdb->insert_id: Name:".$jsonVal['name'].", Res:$resultProd <br>");
                            $resultPropStock = $wpdb->update($table_productstock, array('quantity'=> $stockQuantity), array('prod_id'=>$pid));
                            print("<br> Updated product properties stock Pid: $pid: Qty:$stockQuantity, Res:$resultPropStock <br>");
                        }
                    }
                }
                //New Product Stock Added
                $resultProdStock = $wpdb->insert($table_productstock, array('prod_id' => $item['id'], 
                                                                    'quantity'=> $item['stock_quantity'], 
                                                                    'created_at' => date("Y-m-d")));

                print("<br> New product ptock added: $resultProdStock <br>");
                if($resultProd && $resultProdStock && $resultProdProp && $resultPropStock) {
                    $wpdb->query('COMMIT'); // if you come here then well done
                    $message = __('Product and properties was successfully saved', 'fgpt');
                }
                else {
                    $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
                    $notice = __('There was an error while saving Product properties', 'fgpt');
                }
            } 
            else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('Item was successfully updated', 'fgpt');
                } else {
                    $notice = __('There was an error while updating item', 'fgpt');
                }
            }
        }
    }

}//class Product_List_Table END

function fgpt_validate_product($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Name is required', 'fgpt');
    //if (empty($item['cost_price'])) $messages[] = __('Last Name is required', 'fgpt');
    //if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('E-Mail is in wrong format', 'fgpt');
    //if(!empty($item['phone']) && !absint(intval($item['phone'])))  $messages[] = __('Phone can not be less than zero');
    if(!empty($item['cost_price']) && floatval($item['cost_price']) == 0 && !preg_match('/[0-9]+/', $item['cost_price'])) $messages[] = __('Pleaes provide valid Cost Price');
    if(!empty($item['sale_price']) && floatval($item['sale_price']) == 0 && !preg_match('/[0-9]+/', $item['sale_price'])) $messages[] = __('Pleaes provide valid Sale Price');
    

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function fgpt_languages()
{
    load_plugin_textdomain('fgpt', false, dirname(plugin_basename(__FILE__)));
}
add_action('init', 'fgpt_languages');


// hide update notifications
function remove_core_updates(){
    global $wp_version;return(object) array('last_checked'=> time(),'version_checked'=> $wp_version,);
}
add_filter('pre_site_transient_update_core','remove_core_updates'); //hide updates for WordPress itself
add_filter('pre_site_transient_update_plugins','remove_core_updates'); //hide updates for all plugins
add_filter('pre_site_transient_update_themes','remove_core_updates'); //hide updates for all themes

// START - list-product-prop.php 
function getProducts_AjaxCallback() {
    $pid = $_POST['pid'];
    global $wpdb;
    $results = $wpdb->get_results($wpdb->prepare("SELECT pprod.id, pprod.name, pp.cost_price, pp.sale_price, pp.expiry_date
        FROM wp_erp_acct_products as p 
        INNER JOIN wp_fgpt_productproperties pp on p.id = pp.parent_prod_id
        INNER JOIN wp_erp_acct_products pprod on pprod.id = pp.prod_id
        WHERE p.id = %d", $pid));
    echo json_encode($results);
    wp_die();
}
add_action('wp_ajax_get_products_ajax', 'getProducts_AjaxCallback');
add_action('wp_ajax_nopriv_get_products_ajax', 'getProducts_AjaxCallback');

// END - list-product-prop.php 

// START - inventory-status.php 
function getProductTypes_AjaxCallback() {
    $typeId = $_POST['typeid'];
    $table = new Product_List_Table();
    $results = $table->getItems($typeId);
    echo json_encode($results);
    wp_die();
}
add_action('wp_ajax_get_producttypes_ajax', 'getProductTypes_AjaxCallback');
add_action('wp_ajax_nopriv_get_producttypes_ajax', 'getProductTypes_AjaxCallback');

function updateStock_AjaxCallback() {
    $pid = $_POST['pid'];
    $qty = $_POST['qty'];   
    global $wpdb;
    $updateStockResult = $wpdb->update("wp_fgpt_productstock", 
                                        array('quantity' => $qty,
                                        'updated_at' => date("Y-m-d")),
                                        array('prod_id' => $pid));

    $table = new Product_List_Table();
    $result = $table->getItem($pid);
    //print_r($result);
    $updateStockResult = array('pid' => $pid,
                'qty'  => $result['stock_quantity'],
                'date'  => date("Y-m-d"),
                'rowsaffected' => $updateStockResult
                );
    echo json_encode($updateStockResult);
    wp_die();
}
add_action('wp_ajax_update_stock_ajax', 'updateStock_AjaxCallback');
add_action('wp_ajax_nopriv_update_stock_ajax', 'updateStock_AjaxCallback');

// END - list-product-prop.php 