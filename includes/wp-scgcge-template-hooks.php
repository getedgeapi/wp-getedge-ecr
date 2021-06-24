<?php

/**
 * Template Hooks
 * 
 * Handles to add all hooks of template
 * 
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


//add action to load property_listing template
add_action( 'wp_scgcge_company_search_form', 'wp_scgcge_company_search_form', 10);

if( !function_exists('wp_scgcge_company_search_form' ) ) {

	function wp_scgcge_company_search_form( $arguments ) {

		//load property_listing template
		wp_scgcge_get_template( 'wp-scgcge-company-search-form.php', array( 'arguments' => $arguments ) );	
	}
}

//add action to load property_listing template
add_action( 'wp_scgcge_company_registration_form', 'wp_scgcge_company_registration_form', 10);

if( !function_exists('wp_scgcge_company_registration_form' ) ) {

	function wp_scgcge_company_registration_form( $arguments ) {

		//load property_listing template
		wp_scgcge_get_template( 'wp-scgcge-company-registration-form.php', array( 'arguments' => $arguments ) );	
	}
}