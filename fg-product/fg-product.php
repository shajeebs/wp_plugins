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

defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

require plugin_dir_path( __FILE__ ) . 'includes/metabox-product.php';

function fgpt_custom_admin_styles() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
}
add_action('admin_enqueue_scripts', 'fgpt_custom_admin_styles');


function fgpt_plugin_load_textdomain() {
    load_plugin_textdomain( 'fgpt', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'fgpt_plugin_load_textdomain' );


global $fgpt_db_version;
$fgpt_db_version = '1.1.0'; 


/*function fgpt_install()
{
    global $wpdb;
    global $fgpt_db_version;

    $table_name = $wpdb->prefix . 'erp_acct_products'; 


    $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL AUTO_INCREMENT,
      name VARCHAR (50) NOT NULL,
      lastname VARCHAR (100) NOT NULL,
      email VARCHAR(100) NOT NULL,
      phone VARCHAR(15) NULL,
      company VARCHAR(100) NULL,
      web VARCHAR(100) NULL,  
      two_email VARCHAR(100) NULL,   
      two_phone VARCHAR(15) NULL,  
      job VARCHAR(100) NULL,
      address VARCHAR (250) NULL,
      notes VARCHAR (250) NULL,
      PRIMARY KEY  (id)
    );";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('fgpt_db_version', $fgpt_db_version);

    $installed_ver = get_option('fgpt_db_version');
    if ($installed_ver != $fgpt_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
          id int(11) NOT NULL AUTO_INCREMENT,
          name VARCHAR (50) NOT NULL,
          lastname VARCHAR (100) NOT NULL,
          email VARCHAR(100) NOT NULL,
          phone VARCHAR(15) NULL,
          company VARCHAR(100) NULL,
          web VARCHAR(100) NULL,  
          two_email VARCHAR(100) NULL,   
          two_phone VARCHAR(15) NULL,  
          job VARCHAR(100) NULL,          
          address VARCHAR (250) NULL,
          notes VARCHAR (250) NULL,
          PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('fgpt_db_version', $fgpt_db_version);
    }
}

register_activation_hook(__FILE__, 'fgpt_install');


function fgpt_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'erp_acct_products'; 

}

register_activation_hook(__FILE__, 'fgpt_install_data');


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
            'product_type_id'  => __('Product Type', 'fgpt'),
            'category_id'     => __('Category', 'fgpt'),
            'tax_cat_id'     => __('Tax Category', 'fgpt'),
            'vendor'   => __('Vendor', 'fgpt'),
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
            'product_type_id'  => array('product_type_id', true),
            'category_id'     => array('category_id', true),
            'tax_cat_id'     => array('tax_cat_id', true),
            'vendor'   => array('vendor', true),
            'cost_price'       => array('cost_price', true),  
            'sale_price' => array('sale_price', true),   
            'created_at' => array('created_at', true),  
            //'job'       => array('job', true),
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
        $table_name = $wpdb->prefix . 'erp_acct_products'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
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
        $table_name = $wpdb->prefix . 'erp_acct_products'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);
        // require_once(ABSPATH . 'wp-content\plugins\erp\modules\accounting\includes\functions\products.php');
        // $this->items = erp_acct_get_all_products();
        // require_once(ABSPATH . 'wp-content\plugins\erp\modules\accounting\api\class-rest-api-products.php');
        // $this->items = get_inventory_products();
        

        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function fgpt_admin_menu()
{
    add_menu_page(__('Products', 'fgpt'), __('Products', 'fgpt'), 'activate_plugins', 'products', 'fgpt_products_page_handler');
    add_submenu_page('products', __('Products', 'fgpt'), __('Products', 'fgpt'), 'activate_plugins', 'products', 'fgpt_products_page_handler');
   
    add_submenu_page('products', __('Add new', 'fgpt'), __('Add new', 'fgpt'), 'activate_plugins', 'products_form', 'fgpt_products_form_page_handler');
}

add_action('admin_menu', 'fgpt_admin_menu');


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