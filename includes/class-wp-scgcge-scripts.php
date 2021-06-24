<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scripts Class
 *
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
class Wp_Scgc_Scripts
{
    public function __construct()
    {
    }
    
    /**
     * Enqueue Scripts
     *
     * Loads Javascript for managing
     * metaboxes in plugin settings page
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_admin_meta_scripts($hook_suffix)
    {
        
        // loads the required scripts for the meta boxes
        if ($hook_suffix == 'toplevel_page_wp-scgcge-settings') { //check hoo suffix of page
            
            wp_enqueue_script('common');

            wp_enqueue_script('wp-lists');

            wp_enqueue_script('postbox');

            wp_enqueue_script('media-upload');
            
            wp_enqueue_media();
        }
    }
    
    /**
     * Enqueue Scripts
     *
     * Loads Javascript file for managing datepicker
     * and other functionality in backend
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_admin_scripts($hook_suffix)
    {
        global $wp_version;
        
        $pages_hook_suffix = array( 'toplevel_page_wp-scgcge-settings' );
    
        if (in_array($hook_suffix, $pages_hook_suffix)) {
            wp_enqueue_script(array( 'jquery'));

            // Register & Enqueue admin script
            wp_register_script('wp-scgcge-admin-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/wp-scgcge-admin.js', array(), WP_SCGC_GE_VERSION, true);
            wp_enqueue_script('wp-scgcge-admin-script');

            //localize script
            $newui = $wp_version >= '3.5' ? '1' : '0'; //check wp version for showing media uploader
            wp_localize_script('wp-scgcge-admin-script', 'WpdWsSettings', array( 'new_media_ui'	=>	$newui,	));
        }
    }
    
    /**
     * Enqueue Styles
     *
     * Loads CSS file for managing datepicker
     * and other functionality in backend
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_admin_styles($hook_suffix)
    {
        global $wp_version;
            
        $pages_hook_suffix = array( 'toplevel_page_wp-scgcge-settings' );
        
        if (in_array($hook_suffix, $pages_hook_suffix)) {
            wp_enqueue_style('thickbox');
            
            // Register & Enqueue admin style
            wp_register_style('wp-scgcge-admin-style', WP_SCGC_GE_PLUGIN_URL.'includes/css/wp-scgcge-admin.css', array(), WP_SCGC_GE_VERSION);
            wp_enqueue_style('wp-scgcge-admin-style');
        }
    }
    
    /**
    * Loading Additional Java Script
    *
    * Loads the JavaScript required for toggling the meta boxes.
    *
    * @package SCGC GetEDGE API
    * @since 1.0.0
    */

    public function wp_scgcge_settings_scripts()
    {
        echo '<script type="text/javascript">

				//<![CDATA[

				jQuery(document).ready( function($) {

					$(".if-js-closed").removeClass("if-js-closed").addClass("closed");
					
					postboxes.add_postbox_toggles( "admin_page_wp-scgcge-settings" );
					
				});

				//]]>

			</script>';
    }


    /**
     * Enqueue Styles
     *
     * Handles to enqueue styles for front
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_public_styles()
    {

        // load bootstrapcustom css
        wp_register_style('bootstrapcustom-style', WP_SCGC_GE_PLUGIN_URL . 'includes/css/inc-css/bootstrapcustom.min.css', array(), WP_SCGC_GE_VERSION);
        wp_enqueue_style('bootstrapcustom-style');

        // load bootstrap datepicker css
        wp_register_style('bootstrap-datepicker-standalone-style', WP_SCGC_GE_PLUGIN_URL . 'includes/css/inc-css/bootstrap-datepicker.standalone.min.css', array(), WP_SCGC_GE_VERSION);
        wp_enqueue_style('bootstrap-datepicker-standalone-style');

        // Register & Enqueue public style
        wp_register_style('wp-scgcge-public-style', WP_SCGC_GE_PLUGIN_URL . 'includes/css/wp-scgcge-public.css', array(), WP_SCGC_GE_VERSION);
        wp_enqueue_style('wp-scgcge-public-style');
       
        // Register & Enqueue public style
        wp_register_style('wp-scgcge-public-style-css', WP_SCGC_GE_PLUGIN_URL . 'includes/css/wp-scgcge-public-css.php?lala=dddd', array(), WP_SCGC_GE_VERSION);
        wp_enqueue_style('wp-scgcge-public-style-css');
    }


    /**
     * Enqueue Scripts
     *
     * Handles to enqueue scripts for front
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_public_scripts()
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $google_map_api_key = !empty($wp_scgcge_options['google_map_api_key']) ? $wp_scgcge_options['google_map_api_key'] : '';

        // Register & Enqueue bootstrap datepicker script
        wp_register_script('wp-scgcge-bootstrap-datepicker-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/bootstrap-datepicker.min.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-bootstrap-datepicker-script');

        // Register & Enqueue validation script
        wp_register_script('wp-scgcge-jquery-validate-script', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/jquery.validate.min.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-jquery-validate-script');

        // Register & Enqueue additional methods validation script
        wp_register_script('wp-scgcge-jquery-validate-additional-methods-script', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.17.0/dist/additional-methods.min.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-jquery-validate-additional-methods-script');

        // Register & Enqueue inputmask script
        wp_register_script('wp-scgcge-inputmask-bundle-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/jquery.inputmask.bundle.min.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-inputmask-bundle-script');

        // Register & Enqueue inputmask date script
        wp_register_script('wp-scgcge-inputmask-date-extensions-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/inputmask.date.extensions.min.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-inputmask-date-extensions-script');

        // Register & Enqueue googleapis maps script
        if (!empty($wp_scgcge_options['google_map_api_key'])) {
            wp_register_script('wp-scgcge-googleapis-maps-script', 'https://maps.googleapis.com/maps/api/js?key=' . $google_map_api_key . '&libraries=places');
            wp_enqueue_script('wp-scgcge-googleapis-maps-script');
        }


        // Register & Enqueue Pages validation script
        $current_segment = wp_scgcge_current_segment();

        switch ($current_segment) {
            case 'general-details':
                wp_register_script('wp-scgcge-validator-general-details-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/wp-scgcge-validator-general-details.js', array(), WP_SCGC_GE_VERSION, true);
                wp_enqueue_script('wp-scgcge-validator-general-details-script');
            break;

            case 'addresses':
                wp_register_script('wp-scgcge-validator-addresses-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/wp-scgcge-validator-addresses.js', array(), WP_SCGC_GE_VERSION, true);
                wp_enqueue_script('wp-scgcge-validator-addresses-script');

                if (!empty($wp_scgcge_options['google_map_api_key'])) {
                    wp_register_script('wp-scgcge-gfaa-script', WP_SCGC_GE_PLUGIN_URL . 'includes/js/inc-js/gfaa.js', [], WP_SCGC_GE_VERSION, true);
                    wp_enqueue_script('wp-scgcge-gfaa-script');
                }
            break;

            case 'entities':
                wp_register_script('wp-scgcge-validator-entities-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/wp-scgcge-validator-entities.js', array(), WP_SCGC_GE_VERSION, true);
                wp_enqueue_script('wp-scgcge-validator-entities-script');
            break;

            case 'add-individual':
                wp_register_script('wp-scgcge-validator-entity-add-individual-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/wp-scgcge-validator-entity.js', array(), WP_SCGC_GE_VERSION, true);
                wp_enqueue_script('wp-scgcge-validator-entity-add-individual-script');

                if (!empty($wp_scgcge_options['google_map_api_key'])) {
                    wp_register_script('wp-scgcge-gfaa-intl-add-individual-script', WP_SCGC_GE_PLUGIN_URL . 'includes/js/inc-js/gfaa-intl.js', [], WP_SCGC_GE_VERSION, true);
                    wp_enqueue_script('wp-scgcge-gfaa-intl-add-individual-script');
                }
            break;

            case 'add-company':
                wp_register_script('wp-scgcge-validator-entity-add-company-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/wp-scgcge-validator-entity.js', array(), WP_SCGC_GE_VERSION, true);
                wp_enqueue_script('wp-scgcge-validator-entity-add-company-script');

                if (!empty($wp_scgcge_options['google_map_api_key'])) {
                    wp_register_script('wp-scgcge-gfaa-intl-add-company-script', WP_SCGC_GE_PLUGIN_URL . 'includes/js/inc-js/gfaa-intl.js', [], WP_SCGC_GE_VERSION, true);
                    wp_enqueue_script('wp-scgcge-gfaa-intl-add-company-script');
                }
            break;

            case 'edit-individual':
                wp_register_script('wp-scgcge-validator-entity-edit-individual-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/wp-scgcge-validator-entity.js', array(), WP_SCGC_GE_VERSION, true);
                wp_enqueue_script('wp-scgcge-validator-entity-edit-individual-script');

                if (!empty($wp_scgcge_options['google_map_api_key'])) {
                    wp_register_script('wp-scgcge-gfaa-intl-edit-individual-script', WP_SCGC_GE_PLUGIN_URL . 'includes/js/inc-js/gfaa-intl.js', [], WP_SCGC_GE_VERSION, true);
                    wp_enqueue_script('wp-scgcge-gfaa-intl-edit-individual-script');
                }
            break;

            case 'edit-company':
                wp_register_script('wp-scgcge-validator-entity-edit-company-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/wp-scgcge-validator-entity.js', array(), WP_SCGC_GE_VERSION, true);
                wp_enqueue_script('wp-scgcge-validator-entity-edit-company-script');

                if (!empty($wp_scgcge_options['google_map_api_key'])) {
                    wp_register_script('wp-scgcge-gfaa-intl-edit-company-script', WP_SCGC_GE_PLUGIN_URL . 'includes/js/inc-js/gfaa-intl.js', [], WP_SCGC_GE_VERSION, true);
                    wp_enqueue_script('wp-scgcge-gfaa-intl-edit-company-script');
                }
            break;

            case 'delete-entity':
                wp_register_script('wp-scgcge-validator-entity-delete-entity-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/wp-scgcge-validator-entity.js', array(), WP_SCGC_GE_VERSION, true);
                wp_enqueue_script('wp-scgcge-validator-entity-delete-entity-script');
            break;

            case 'review':
                wp_register_script('wp-scgcge-validator-review-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/wp-scgcge-validator-review.js', array(), WP_SCGC_GE_VERSION, true);
                wp_enqueue_script('wp-scgcge-validator-review-script');

                if (!empty($wp_scgcge_options['google_map_api_key'])) {
                    wp_register_script('wp-scgcge-gfaa-review-script', WP_SCGC_GE_PLUGIN_URL . 'includes/js/inc-js/gfaa.js', [], WP_SCGC_GE_VERSION, true);
                    wp_enqueue_script('wp-scgcge-gfaa-review-script');
                }
            break;

        }
        // Register & Enqueue dowhen script
        wp_register_script('wp-scgcge-jquery-dowhen-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/inc-js/jquery.dowhen.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-jquery-dowhen-script');

        // Register & Enqueue popper script
        wp_register_script('wp-scgcge-jquery-popper-script', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-jquery-popper-script');

        // Register & Enqueue bootstrap script
        wp_register_script('wp-scgcge-jquery-bootstrap-script', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-jquery-bootstrap-script');

        // Register & Enqueue misc script
        wp_register_script('wp-scgcge-misc-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/wp-scgcge-misc.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-misc-script');

        // Register & Enqueue public script
        wp_register_script('wp-scgcge-public-script', WP_SCGC_GE_PLUGIN_URL.'includes/js/wp-scgcge-public.js', array(), WP_SCGC_GE_VERSION, true);
        wp_enqueue_script('wp-scgcge-public-script');

        
        // in JavaScript, object properties are accessed as WpScgcgePublicScript.ajax_url
        wp_localize_script(
            'wp-scgcge-public-script',
            'WpScgcgePublicScript',
            array( 'ajax_url' => admin_url('admin-ajax.php') )
        );
    }
    
    /**
     * Adding Hooks
     *
     * Adding hooks for the styles and scripts.
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function add_hooks()
    {
        
        //add style for backend
        add_action('admin_enqueue_scripts', array( $this, 'wp_scgcge_admin_styles' ));
        
        //add js for backend
        add_action('admin_enqueue_scripts', array( $this, 'wp_scgcge_admin_scripts' ));
        
        // add meta scripts for backend
        add_action('admin_enqueue_scripts', array( $this, 'wp_scgcge_admin_meta_scripts' ));

        //add style for fronend
        add_action('wp_enqueue_scripts', array($this, 'wp_scgcge_public_styles'));

        //add scripts for fronend
        add_action('wp_enqueue_scripts', array($this, 'wp_scgcge_public_scripts'));
    }
}
