<?php

/**
 * Plugin Name: GetEDGE API for WooCommerce
 * Plugin URI: https://getedge.com.au/
 * Description: Search company name availability and register companies with ASIC, using GetEDGE API.
 * Version: 2.8.2
 * Author: Southern Cross Global Consulting Pty Ltd
 * Author URI: https://www.scglobal.com.au/
 * Text Domain: scgcge
 * Domain Path: languages
 *
 * @package SCGC GetEDGE API
 * @category Core
 * @author SCGlobal
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
require __DIR__ . '/vendor/autoload.php';
/**
 * Basic plugin definitions
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
if (!defined('WP_SCGC_GE_VERSION')) {
    define('WP_SCGC_GE_VERSION', '2.8.1'); //version of plugin
}
if (!defined('WP_SCGC_GE_DIR')) {
    define('WP_SCGC_GE_DIR', dirname(__FILE__)); // plugin dir
}
if (!defined('WP_SCGC_GE_TEXT_DOMAIN')) {
    define('WP_SCGC_GE_TEXT_DOMAIN', 'scgcge'); // text domain for languages
}
if (!defined('WP_SCGC_GE_ADMIN')) {
    define('WP_SCGC_GE_ADMIN', WP_SCGC_GE_DIR . '/includes/admin'); // plugin admin dir
}
if (!defined('WP_SCGC_GE_LIB')) {
    define('WP_SCGC_GE_LIB', WP_SCGC_GE_DIR . '/includes/lib'); // plugin admin dir
}
if (!defined('WP_SCGC_GE_TEMPLATES')) {
    define('WP_SCGC_GE_TEMPLATES', WP_SCGC_GE_DIR . '/includes/templates'); // plugin admin dir
}
if (!defined('WP_SCGC_GE_PLUGIN_URL')) {
    define('WP_SCGC_GE_PLUGIN_URL', plugin_dir_url(__FILE__)); // plugin url
}

if (!defined('wpscgcgelevel')) {
    define('wpscgcgelevel', 'manage_woocommerce'); // this is capability in plugin
}
if (!defined('WP_SCGC_GE_PLUGIN_BASENAME')) {
    define('WP_SCGC_GE_PLUGIN_BASENAME', basename(WP_SCGC_GE_DIR)); //Plugin base name
}
if (!defined('WP_SCGC_GE_REQUESTS_POST_TYPE')) {
    define('WP_SCGC_GE_REQUESTS_POST_TYPE', 'wpscgcgerequests'); //social posting logs post type
}
if (!defined('WP_SCGC_GE_META_PREFIX')) {
    define('WP_SCGC_GE_META_PREFIX', '_wpscgc_'); //metabox prefix
}

if (!defined('WP_SCGC_ASIC_COMPANIES_TABLE')) {
    define('WP_SCGC_ASIC_COMPANIES_TABLE', $wpdb->prefix . 'asic_companies'); //Asic Companies Table Name
}
if (!defined('WP_SCGC_ASIC_ENTITIES_TABLE')) {
    define('WP_SCGC_ASIC_ENTITIES_TABLE', $wpdb->prefix . 'asic_entities'); //Asic Entities Table Name
}
if (!defined('WP_SCGC_ASIC_ENTITY_SHARES_TABLE')) {
    define('WP_SCGC_ASIC_ENTITY_SHARES_TABLE', $wpdb->prefix . 'asic_entity_shares'); //Asic Entities Table Name
}


function auto_update_specific_plugins($update, $item)
{
    $plugins = array(
        'wp-getedge-ecr',
    );
    if (in_array($item->slug, $plugins)) {
        return true;
    } else {
        return $update;
    }
}
add_filter('auto_update_plugin', 'auto_update_specific_plugins', 10, 2);


$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/getedgeapi/wp-getedge-ecr',
    __FILE__,
    'wp-getedge-ecr'
);
$myUpdateChecker->setBranch('main');


/**
 * Load Text Domain
 *
 * This gets the plugin ready for translation.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_load_textdomain()
{
    // Set filter for plugin's languages directory
    $wp_scgcge_lang_dir = dirname(plugin_basename(__FILE__)) . '/languages/';
    $wp_scgcge_lang_dir = apply_filters('wp_scgcge_languages_directory', $wp_scgcge_lang_dir);

    // Traditional WordPress plugin locale filter
    $locale = apply_filters('plugin_locale', get_locale(), 'scgcge');
    $mofile = sprintf('%1$s-%2$s.mo', 'wpdws', $locale);

    // Setup paths to current locale file
    $mofile_local = $wp_scgcge_lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/' . WP_SCGC_GE_PLUGIN_BASENAME . '/' . $mofile;

    if (file_exists($mofile_global)) { // Look in global /wp-content/languages/wp-settings-widget folder
        load_textdomain('scgcge', $mofile_global);
    } elseif (file_exists($mofile_local)) { // Look in local /wp-content/plugins/wp-settings-widget/languages/ folder
        load_textdomain('scgcge', $mofile_local);
    } else { // Load the default language files
        load_plugin_textdomain('scgcge', false, $wp_scgcge_lang_dir);
    }
}
/**
 * Activation hook
 *
 * Register plugin activation hook.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

register_activation_hook(__FILE__, 'wp_scgcge_install');

/**
 * Deactivation hook
 *
 * Register plugin deactivation hook.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

register_deactivation_hook(__FILE__, 'wp_scgcge_uninstall');

/**
 * Plugin Setup Activation hook call back
 *
 * Initial setup of the plugin setting default options
 * and database tables creations.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

function wp_scgcge_install()
{
    global $wpdb;

    // create Asic Company and Entity Database table
    wp_scgcge_create_table();

    //if plugin is first time going to activated then set all default options
    $wp_scgcge_options = get_option('wp_scgcge_options');

    if (empty($wp_scgcge_options)) {
        wp_scgcge_default_settings(); // set default settings
    }
    flush_rewrite_rules();
}

/**
 * Plugin Setup (On Deactivation)
 *
 * Does the drop tables in the database and
 * delete  plugin options.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

function wp_scgcge_uninstall()
{
    global $wpdb;
}

/**
 * Plugin default settings
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

function wp_scgcge_default_settings()
{
    global $wp_scgcge_options;

    $wp_scgcge_options = [
        'ge_api_key' => '',
        'docs_api_key' => '',
        'page_id' => '',
        'ge_coy_reg_fee_id' => '',
        'ge_asic_fee_id' => '',
        'slack_webhook_url' => '',
        'ge_url' => 'https://getedge.com.au/api/v1',
        'test_transmission' => 'true',
        'xero_account_code_sales' => '',
        'xero_account_code_asic' => '',
        'xero_account_code_sales_coy_reg' => '',
        'xero_asic_contact_id' => '',
        'google_map_api_key' => '',
        'xero_prefix' => '',
        'consumer_key' => '',
        'consumer_secret' => '',
        'public_cert_file' => '',
        'private_key_file' => '',
        'hex_accent_bg' => '#0665d0',
        'hex_accent_colour' => '#ffffff',
        'hex_hover_bg' => '#343a40',
        'hex_hover_colour' => '#ffffff',
        'branding' => 'NO',
        'agt_number' => '',
        'agt_name' => '',
        'agt_company' => '',
        'agt_phone' => '',
        'agt_email' => '',
        'agt_street' => '',
        'agt_suburb' => '',
        'agt_state' => '',
        'agt_postcode' => '',
    ];

    // apply filters for default settings
    $wp_scgcge_options = apply_filters('wp_scgcge_default_settings', $wp_scgcge_options);

    update_option('wp_scgcge_options', $wp_scgcge_options);
}

/**
 * Create the table.
 *
 * Creates asic database table.
 *
 * @access  public
 * @since   2.3.0
 */
function wp_scgcge_create_table()
{
    global $wpdb, $entities_table_name;

    //Old table names
    $companies_table_name = 'asic_companies';
    $entities_table_name = 'asic_entities';

    // Required file for DB Delta query
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Replace Old table asic_companies.
    // if ($wpdb->get_var("show tables like '$companies_table_name' ") == $companies_table_name) {
    //     $rename_query = sprintf(
    //         'RENAME TABLE `%s` TO `%s`;',
    //         $companies_table_name,
    //         WP_SCGC_ASIC_COMPANIES_TABLE
    //     );

    //     if (false === $wpdb->query($rename_query)) {
    //         throw new Exception('MySQL error: ' . $wpdb->last_error);
    //     }
    // }
    // // Replace Old table asic_entities.
    // if ($wpdb->get_var("show tables like '$entities_table_name' ") == $entities_table_name) {
    //     $rename_query = sprintf(
    //         'RENAME TABLE `%s` TO `%s`;',
    //         $entities_table_name,
    //         WP_SCGC_ASIC_ENTITIES_TABLE
    //     );

    //     if (false === $wpdb->query($rename_query)) {
    //         throw new Exception('MySQL error: ' . $wpdb->last_error);
    //     }
    // }
    $charset_collate = $wpdb->get_charset_collate();

    $asicCompaniesSql = 'CREATE TABLE ' . WP_SCGC_ASIC_COMPANIES_TABLE . ' (
        id INT(11) NOT NULL AUTO_INCREMENT,
        company_name_full VARCHAR(200) DEFAULT NULL,
        company_name VARCHAR(200) DEFAULT NULL,
        legal_elements VARCHAR(200) DEFAULT NULL,
        acn_only VARCHAR(3) DEFAULT NULL,
        search_result VARCHAR(10) DEFAULT NULL,
        jurisdiction VARCHAR(4) DEFAULT NULL,
        company_class VARCHAR(4) DEFAULT NULL,
        company_subclass VARCHAR(4) DEFAULT NULL,
        company_type VARCHAR(4) DEFAULT NULL,
        holding_company VARCHAR(3) DEFAULT NULL,
        holding_name VARCHAR(200) DEFAULT NULL,
        holding_country VARCHAR(30) DEFAULT NULL,
        holding_acn VARCHAR(9) DEFAULT NULL,
        holding_abn VARCHAR(11) DEFAULT NULL,
        bn_when VARCHAR(6) DEFAULT NULL,
        bn_abn VARCHAR(11) DEFAULT NULL,
        bn_state VARCHAR(4) DEFAULT NULL,
        bn_number VARCHAR(10) DEFAULT NULL,
        ro_care VARCHAR(50) DEFAULT NULL,
        ro_line2 VARCHAR(50) DEFAULT NULL,
        ro_street VARCHAR(52) DEFAULT NULL,
        ro_suburb VARCHAR(30) DEFAULT NULL,
        ro_state VARCHAR(4) DEFAULT NULL,
        ro_postcode VARCHAR(4) DEFAULT NULL,
        ro_occupy VARCHAR(3) DEFAULT NULL,
        ro_occupier VARCHAR(200) DEFAULT NULL,
        ro_same VARCHAR(3) DEFAULT NULL,
        ppb_care VARCHAR(50) DEFAULT NULL,
        ppb_line2 VARCHAR(50) DEFAULT NULL,
        ppb_street VARCHAR(52) DEFAULT NULL,
        ppb_suburb VARCHAR(30) DEFAULT NULL,
        ppb_state VARCHAR(4) DEFAULT NULL,
        ppb_postcode VARCHAR(4) DEFAULT NULL,
        agree VARCHAR(1) DEFAULT NULL,
        applicant INT(11) DEFAULT NULL,
        applicant_first_name VARCHAR(20) DEFAULT NULL,
        applicant_middle_name VARCHAR(20) DEFAULT NULL,
        applicant_last_name VARCHAR(30) DEFAULT NULL,
        applicant_line2 VARCHAR(50) DEFAULT NULL,
        applicant_street VARCHAR(52) DEFAULT NULL,
        applicant_suburb VARCHAR(30) DEFAULT NULL,
        applicant_state VARCHAR(4) DEFAULT NULL,
        applicant_postcode VARCHAR(4) DEFAULT NULL,
        reg_abn VARCHAR(1) DEFAULT NULL,
        reg_tfn VARCHAR(1) DEFAULT NULL,
        reg_gst VARCHAR(1) DEFAULT NULL,
        reg_payg VARCHAR(1) DEFAULT NULL,
        reg_ftc VARCHAR(1) DEFAULT NULL,
        reg_lct VARCHAR(1) DEFAULT NULL,
        reg_wet VARCHAR(1) DEFAULT NULL,
        reg_fbt VARCHAR(1) DEFAULT NULL,
        acn VARCHAR(9) DEFAULT NULL,
        abn VARCHAR(11) DEFAULT NULL,
        reg_date date DEFAULT NULL,
        user_id INT(11) DEFAULT NULL,
        status VARCHAR(20) DEFAULT NULL,
        order_id INT(11) DEFAULT NULL,
        edge_id INT(11) DEFAULT NULL,
        code VARCHAR(50) DEFAULT NULL,
        session_id VARCHAR(50) DEFAULT NULL,
        document VARCHAR(15) DEFAULT NULL,
        PRIMARY KEY  (id)
        ) ' . $charset_collate . ';';

    dbDelta($asicCompaniesSql);

    $asicEntitiesSql = 'CREATE TABLE ' . WP_SCGC_ASIC_ENTITIES_TABLE . ' (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `entity_type` VARCHAR(3) DEFAULT NULL,
                `entity_role_dir` VARCHAR(1) DEFAULT NULL,
                `entity_role_sec` VARCHAR(1) DEFAULT NULL,
                `entity_role_sha` VARCHAR(1) DEFAULT NULL,
                `entity_role_pub` VARCHAR(1) DEFAULT NULL,
                `entity_first_name` VARCHAR(20) DEFAULT NULL,
                `entity_middle_name1` VARCHAR(20) DEFAULT NULL,
                `entity_middle_name2` VARCHAR(20) DEFAULT NULL,
                `entity_last_name` VARCHAR(30) DEFAULT NULL,
                `entity_former` VARCHAR(1) DEFAULT NULL,
                `entity_former_first_name` VARCHAR(20) DEFAULT NULL,
                `entity_former_middle_name1` VARCHAR(20) DEFAULT NULL,
                `entity_former_middle_name2` VARCHAR(20) DEFAULT NULL,
                `entity_former_last_name` VARCHAR(30) DEFAULT NULL,
                `entity_birth_date` date DEFAULT NULL,
                `entity_birth_country` VARCHAR(30) DEFAULT NULL,
                `entity_birth_state` VARCHAR(4) DEFAULT NULL,
                `entity_birth_suburb` VARCHAR(30) DEFAULT NULL,
                `entity_phone` VARCHAR(15) DEFAULT NULL,
                `entity_email` VARCHAR(200) DEFAULT NULL,
                `entity_company_name` VARCHAR(200) DEFAULT NULL,
                `entity_company_country` VARCHAR(30) DEFAULT NULL,
                `entity_company_acn` VARCHAR(9) DEFAULT NULL,
                `address_care` VARCHAR(50) DEFAULT NULL,
                `address_line2` VARCHAR(50) DEFAULT NULL,
                `address_street` VARCHAR(52) DEFAULT NULL,
                `address_suburb` VARCHAR(30) DEFAULT NULL,
                `address_state` VARCHAR(4) DEFAULT NULL,
                `address_postcode` VARCHAR(15) DEFAULT NULL,
                `address_country` VARCHAR(30) DEFAULT NULL,
                `user_id` INT(11) DEFAULT NULL,
                `session_id` VARCHAR(50) DEFAULT NULL,
                `code` VARCHAR(50) DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';

    dbDelta($asicEntitiesSql);

    //Create table for asic shares.
    $asicEntitySharesSql = 'CREATE TABLE `' . WP_SCGC_ASIC_ENTITY_SHARES_TABLE . '` (
    						  `id` INT(11) NOT NULL AUTO_INCREMENT,
    						  `entity_id` INT(11) UNSIGNED NOT NULL,
    						  `share_class` VARCHAR(3) DEFAULT NULL,
    						  `share_number` INT(11) DEFAULT NULL,
    						  `share_paid` decimal(12,2) DEFAULT NULL,
    						  `share_paid_total` decimal(12,2) DEFAULT NULL,
    						  `share_unpaid` decimal(12,2) DEFAULT NULL,
    						  `share_unpaid_total` decimal(12,2) DEFAULT NULL,
    						  `share_beneficial` VARCHAR(1) DEFAULT NULL,
    						  `share_beneficiary` VARCHAR(200) DEFAULT NULL,
    						  `user_id` INT(11) DEFAULT NULL,
    						  `session_id` VARCHAR(50) DEFAULT NULL,
    						  `code` VARCHAR(50) DEFAULT NULL,
    						  PRIMARY KEY (`id`),
    						  FOREIGN KEY  (`entity_id`) REFERENCES `' . WP_SCGC_ASIC_ENTITIES_TABLE . '`(`id`)
    						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';

    dbDelta($asicEntitySharesSql);

    // $GetShareEntityData = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '" . WP_SCGC_ASIC_ENTITIES_TABLE . "' AND column_name = 'share_class'");

    // if (!empty($GetShareEntityData)) {
    //     $maigrateAsicEntitySharesSql = 'INSERT INTO `' . WP_SCGC_ASIC_ENTITY_SHARES_TABLE . '`(
    // 									entity_id,share_class,share_number,share_paid,share_paid_total,share_unpaid,share_unpaid_total,share_beneficial,share_beneficiary,user_id,session_id,code) SELECT id,share_class,share_number,share_paid,share_paid_total,share_unpaid,share_unpaid_total,share_beneficial,share_beneficiary,user_id,session_id,code FROM `' . WP_SCGC_ASIC_ENTITIES_TABLE . '`';
    //     $wpdb->query($maigrateAsicEntitySharesSql);

    //     $DropAsicEntitySql = 'ALTER TABLE `' . WP_SCGC_ASIC_ENTITIES_TABLE . '` DROP share_class, DROP share_number, DROP share_paid, DROP share_paid_total, DROP share_unpaid, DROP share_unpaid_total, DROP share_beneficial, DROP share_beneficiary';
    //     $wpdb->query($DropAsicEntitySql);
    // }
}

/**
 * Load Plugin
 *
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_plugin_loaded()
{
    global $wp_scgcge_options;

    $wp_scgcge_options = get_option('wp_scgcge_options');
    // load first plugin text domain
    wp_scgcge_load_textdomain();
}
//add action to load plugin
add_action('plugins_loaded', 'wp_scgcge_plugin_loaded');

/**
 * init Add Action
 *
 * Handles to Session Start
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_sessionset()
{
    if (!session_id()) {
        @session_start();
    }
}
//session set
add_action('init', 'wp_scgcge_sessionset', 15);

/**
 * Wp Logout Plugin
 *
 * if Wp Logout than Sesion Destroy
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_force_out()
{
    session_destroy();
}
add_action('wp_logout', 'wp_scgcge_force_out');

/**
 * WP Login Plugin
 *
 * if Wp Login than get Session And update user
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_wp_login($user_login, $user)
{
    global $wpdb;
    if (wp_scgcge_getSession()) {
        $code = wp_scgcge_getSession();
        $userID = $user->ID;
        $result = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'user_id' => $userID,
        ], ['code' => $code]);
    }
}
add_action('wp_login', 'wp_scgcge_wp_login', 10, 2);

/**
 * WP Register Plugin
 *
 * if Wp Register than get Session And update user
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_user_register($user_id)
{
    global $wpdb;
    if (wp_scgcge_getSession()) {
        $code = wp_scgcge_getSession();
        $result = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'user_id' => $user_id,
        ], ['code' => $code]);
    }
}
add_action('user_register', 'wp_scgcge_user_register', 10, 1);

/**
 * Write Log Debugging
 *
 * if debugging true than Print log
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
if (!function_exists('write_log')) {
    function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}

/**
 * Add plugin action links
 *
 * Adds a settings, support and docs link to the plugin list.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_add_settings_link($links)
{
    $plugin_links = [
        '<a href="' . add_query_arg(['page' => 'wp-scgcge-settings'], admin_url('admin.php')) . '">' . __('Settings', 'scgcge') . '</a>'
    ];

    return array_merge($plugin_links, $links);
}

//add plugin settings, support and docs link to plugin listing page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_scgcge_add_settings_link');

/**
 * Initialize all global variables
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

global $wp_scgcge_options, $wp_scgcge_model, $wp_scgcge_scripts, $wp_scgcge_public, $wp_scgcge_admin;

/**
 * Includes
 *
 * Includes all the needed files for our plugin
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

//includes model class file
require_once WP_SCGC_GE_DIR . '/includes/class-wp-scgcge-model.php';
$wp_scgcge_model = new Wp_Scgc_Model();

//includes script class file
require_once WP_SCGC_GE_DIR . '/includes/class-wp-scgcge-scripts.php';
$wp_scgcge_scripts = new Wp_Scgc_Scripts();
$wp_scgcge_scripts->add_hooks();

// Admin class handles most of public panel functionalities of plugin
include_once WP_SCGC_GE_DIR . '/includes/class-wp-scgcge-public.php';
$wp_scgcge_public = new Wp_Scgc_Public();
$wp_scgcge_public->add_hooks();

/**
 * Includes all required files for admin
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

require_once WP_SCGC_GE_ADMIN . '/class-wp-scgcge-admin.php';
$wp_scgcge_admin = new Wp_Scgc_Admin_Pages();
$wp_scgcge_admin->add_hooks();

// include the shortcodes class files
require_once WP_SCGC_GE_DIR . '/includes/class-wp-scgcge-shortcodes.php';
$ww_tp_shortocde = new Wp_Scgc_Shortcode();
$ww_tp_shortocde->add_hooks();

// include the misc class files
require_once WP_SCGC_GE_DIR . '/includes/class-wp-scgcge-misc-functions.php';

// include the ECR class files
require_once WP_SCGC_GE_DIR . '/includes/class-wp-scgcge-ecr-functions.php';

// include the Company Registration class files
require_once WP_SCGC_GE_DIR . '/includes/class-wp-scgcge-form-functions.php';

// include the template functions file
require_once WP_SCGC_GE_DIR . '/includes/wp-scgcge-template-functions.php';

// include the template hook File
require_once WP_SCGC_GE_DIR . '/includes/wp-scgcge-template-hooks.php';
