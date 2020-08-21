<?php
/**
 * Plugin Name: VueJs App Demo
 * Description: A demo on how to use Vuejs in WordPress with custom API
 */

function vjpt_install() {
    global $wpdb;
    $table_Student = $wpdb->prefix.'vjpt_students'; 

    // Student details
    $sql = "CREATE TABLE IF NOT EXISTS $table_Student (
        id int(11) AUTO_INCREMENT PRIMARY KEY,
        name     varchar(50) NOT NULL,
        age     INT NOT NULL,
        email varchar(50)  not null,
        place     varchar(50) NOT NULL,
        created_at date NOT NULL);";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'vjpt_install');

function vjpt_install_data(){
    global $wpdb;
    $table_Student = $wpdb->prefix.'vjpt_students'; 

    $data = [
        ['name' => 'Tarek, Rahman', 'age' => 22, 'email' => 'tarek.rahman@refin.com', 'place' => 'Nottingham', "created_at" => date("Y-m-d")],
        ['name' => 'Farish, George W', 'age' => 21, 'email' => 'george.farish@refin.com', 'place' => 'London', "created_at" => date("Y-m-d")],
        ['name' => 'Arjun, Menon', 'age' => 43, 'email' => 'arjun.menon@refin.com', 'place' => 'Birmingham', "created_at" => date("Y-m-d")],
        ['name' => 'Charles-Love, Nadege', 'age' => 55, 'email' => 'nadege.charles@refin.com', 'place' => 'Liverpool', "created_at" => date("Y-m-d")],
        ['name' => 'Wood, Claire', 'age' => 37, 'email' => 'claire.wood@refin.com', 'place' => 'Manchestor', "created_at" => date("Y-m-d")],
        ['name' => 'Dowell,  Campbell', 'age' => 28, 'email' => 'campbell.dowell@refin.com', 'place' => 'Sounthampton', "created_at" => date("Y-m-d")],
        ['name' => 'Telenkov, Evgenii', 'age' => 43, 'email' => 'evgenii.telenkov@refin.com', 'place' => 'Derby', "created_at" => date("Y-m-d")],
        ['name' => 'Davidson, Brian', 'age' => 65, 'email' => 'brian.davidson@refin.com', 'place' => 'Licestor', "created_at" => date("Y-m-d")],
        ['name' => 'Henry, Alan', 'age' => 33, 'email' => 'alan.henry@refin.com', 'place' => 'Beeston', "created_at" => date("Y-m-d")],
    ];

    foreach ($data as $stud) {
            $result = $wpdb->insert($table_Student, $stud);
            // print("Result: $result:     Name-". $stud['name'].", age:".$stud['age'].", email:".$stud['email'].", place:".$stud['place']);
            // print("<br>");
    }
}
register_activation_hook(__FILE__, 'vjpt_install_data');

//Register the API routes for the objects of the controller.
add_action( 'rest_api_init', function () {
    require_once(plugin_dir_path(__FILE__).'/class-api-students.php');
    $ctrl = new ClassApiStudents();
    $ctrl->register_routes();
});

function enqueue_vuejs_scripts(){
    wp_enqueue_script('vue', 'https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.js', [], '2.5.17');
    wp_enqueue_script('student-details', plugin_dir_url( __FILE__ ) . 'vueapp.js', [], '1.0', true);
}
add_action('admin_enqueue_scripts', 'enqueue_vuejs_scripts' );

//[students] - checks for shortcode in wordpress and renders this div mount
function handle_shortcode() {
    return '<div id="mount"></div>';
}
add_shortcode('students', 'handle_shortcode'); 

function vueAdminPage() {
  add_menu_page('VeuJs App', 'VeuJs App', 'manage_options' ,__FILE__, 'RenderVueComponent', 'dashicons-forms');
}
add_action('admin_menu', 'vueAdminPage');

function RenderVueComponent(){
    echo handle_shortcode();
}