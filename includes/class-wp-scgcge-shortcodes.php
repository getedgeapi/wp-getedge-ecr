<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * Property Shortcode Class
 *
 * Handles property shortcode features and functions
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
class Wp_Scgc_Shortcode {

  public $model, $scripts;

  public function __construct() {

    global $wp_scgcge_model, $wp_scgcge_scripts;
    $this->model   = $wp_scgcge_model;
    $this->scripts = $wp_scgcge_scripts;
  }

  /**
   * Property Listing
   *
   * @package SCGC GetEDGE API
   * @since 1.0.0
   */
  public function wp_scgcge_company_search_form( $attributes, $content ) {

    ob_start();

    // display company search form
    do_action('wp_scgcge_company_search_form');

    $content .= ob_get_clean();

    return $content;
  }


  /**
   * Company Registration
   *
   * @package SCGC GetEDGE API
   * @since 1.0.0
   */
  public function wp_scgcge_company_registration_form( $attributes, $content ) {

    ob_start();

    // display company search form
    do_action('wp_scgcge_company_registration_form');

    $content .= ob_get_clean();

    return $content;
  }

  
  /**
	 * Adding Hooks
	 *
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
	 */
  public function add_hooks() {

    // add shortcode to display company search form
    add_shortcode( 'company_search', array( $this, 'wp_scgcge_company_search_form' ) );

    // add shortcode to display company search form
    add_shortcode( 'company_registration_form', array( $this, 'wp_scgcge_company_registration_form' ) );

  }

}