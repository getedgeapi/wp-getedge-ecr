<?php
/**
 * Templates Functions
 *
 * Handles to manage templates of plugin
 * 
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Returns the path to the Review Engine templates directory
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_get_templates_dir() {
	
	return WP_SCGC_GE_DIR . '/includes/templates/';
	
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 * 
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	
	if ( ! $template_path ) $template_path = WP_SCGC_GE_PLUGIN_BASENAME . '/'; //woo_slg_get_templates_dir();
	if ( ! $default_path ) $default_path = wp_scgcge_get_templates_dir();
	
	// Look within passed path within the theme - this is priority
	
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);
	
	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return $template;
}

/**
 * Get other templates (e.g. fbre attributes) passing attributes and including the file.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	
	if ( $args && is_array($args) )
		extract( $args );

	$located = wp_scgcge_locate_template( $template_name, $template_path, $default_path );
		
	include( $located );
}

