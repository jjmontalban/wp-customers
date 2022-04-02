<?php
/**
* Plugin Name: GB Customers
* Description: Plugin to create customer list for Galacticblum SL
* Version:     2.1.3
* Plugin URI:  https://jjmontalban.github.io
* Author:      JJMontalban
* Author URI:  https://jjmontalban.github.io
* License:     GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: gbc
* Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( '' );


//Includes
require plugin_dir_path( __FILE__ ) . 'includes/admin.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-customer.php';
require plugin_dir_path( __FILE__ ) . 'includes/webservice.php';

global $wpdb_db_version;
$wpdb_db_version = '1.0.0'; 

$plugin = plugin_basename( __FILE__ );

//Adding styles
function gbc_custom_styles() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
}
add_action('admin_enqueue_scripts', 'gbc_custom_styles');

//Load plugin translated strings
function gbc_plugin_load_textdomain() {
    load_plugin_textdomain( 'gbc', false, basename( dirname( __FILE__ ) ) . '/lang' ); 
}
add_action( 'plugins_loaded', 'gbc_plugin_load_textdomain' );


function gbc_install()
{
    global $wpdb;
    global $wpdb_db_version;

    $customers = $wpdb->prefix . 'customers'; 

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql = "CREATE TABLE " . $customers . " (
      id_customer int(11) NOT NULL AUTO_INCREMENT,
      name VARCHAR (50) NOT NULL,
      lastname VARCHAR (100) NOT NULL,
      phone1 VARCHAR(15) NOT NULL,
      phone2 VARCHAR(15) NULL,
      company VARCHAR(100) NULL,
      email VARCHAR(100) NOT NULL,     
      cif VARCHAR(100) NULL,
      vat VARCHAR (250) NULL,
      address VARCHAR (100) NOT NULL,
      postcode VARCHAR (100) NOT NULL,
      city VARCHAR(15) NOT NULL,
      state VARCHAR(15) NULL,
      country VARCHAR(100) NULL,
      notes VARCHAR (250) NULL,
      PRIMARY KEY  (id_customer)
    );";
    dbDelta($sql);

    add_option('gbc_db_version', $wpdb_db_version);

    $installed_ver = get_option('gbc_db_version');

    if ($installed_ver != $wpdb_db_version) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $sql = "CREATE TABLE " . $customers . " (
            id_customer int(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR (50) NOT NULL,
            lastname VARCHAR (100) NOT NULL,
            phone1 VARCHAR(15) NOT NULL,
            phone2 VARCHAR(15) NULL,
            company VARCHAR(100) NULL,
            email VARCHAR(100) NOT NULL,     
            cif VARCHAR(100) NULL,
            vat VARCHAR (250) NULL,
            address VARCHAR (100) NOT NULL,
            postcode VARCHAR (100) NOT NULL,
            city VARCHAR(15) NOT NULL,
            state VARCHAR(15) NULL,
            country VARCHAR(100) NULL,
            notes VARCHAR (250) NULL,
            PRIMARY KEY  (id_customer)
        );";
        dbDelta($sql);

        update_option('gbc_db_version', $wpdb_db_version);
    }
}
register_activation_hook(__FILE__, 'gbc_install');


function gbc_install_data()
{
    global $wpdb;
    //$table_name = $wpdb->prefix . 'gbc'; 

}
register_activation_hook(__FILE__, 'gbc_install_data');


function gbc_update_db_check()
{
    global $wpdb_db_version;
    if (get_site_option('gbc_db_version') != $wpdb_db_version) {
        gbc_install();
    }
}
add_action('plugins_loaded', 'gbc_update_db_check');


function gbc_admin_menu()
{
    add_menu_page(__('Customers', 'gbc'), __('Customers', 'gbc'), 'activate_plugins', 'customers', 'gbc_customers_list_page');
    add_submenu_page('customers',' ' , ' ', 'activate_plugins', 'customer_view', 'gbc_customer_view_page');
    add_submenu_page('customers', __('Add new', 'gbc'), __('Add new', 'gbc'), 'activate_plugins', 'customer_form', 'gbc_customer_form_page');
    add_submenu_page('customers', __('Webservice', 'gbc'), __('Presta Webservice', 'gbc'), 'activate_plugins', 'webservice', 'gbc_configuration_page');  
}
add_action('admin_menu', 'gbc_admin_menu');

//Registra una configuración y su retrollamada de limpieza (sanitization).
//register_setting( $grupo_de_opciones, $nombre_de_opcion, $retrollamada_de_limpieza );
function gbc_settings(){		
    register_setting('gbc-config-group', 'gbc_options', 'gbc_sanitize');		
}
add_action('admin_init', 'gbc_settings');

function gbc_sanitize($input){
    return $input;
}

function gbc_languages()
{
    load_plugin_textdomain('gbc', false, dirname(plugin_basename(__FILE__)));
}
add_action('init', 'gbc_languages');