<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Plugin Model Class
 *
 * Handles generic functionailties
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
 class Wp_Scgc_Model {
 	 	
 	//class constructor
	public function __construct()	{		

	}
		
	/**
	  * Escape Tags & Slashes
	  *
	  * Handles escapping the slashes and tags
	  *
	  * @package SCGC GetEDGE API
	  * @since 1.0.0
	  */
	   
	 public function wp_scgcge_escape_attr($data){
	  
	 	return esc_attr(stripslashes($data));
	 }
	 
	 /**
	  * Stripslashes 
 	  * 
  	  * It will strip slashes from the content
	  *
	  * @package SCGC GetEDGE API
	  * @since 1.0.0
	  */
	   
	public function wp_scgcge_escape_slashes_deep($data = array(),$flag=false){
		//return stripslashes_deep($data);
		if($flag != true) {
			$data = $this->wp_scgcge_nohtml_kses($data);
		}
		$data = stripslashes_deep($data);
		return $data;
	}
	 	
	/**
	 * Strip Html Tags 
	 * 
	 * It will sanitize text input (strip html tags, and escape characters)
	 * 
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
	 */
	public function wp_scgcge_nohtml_kses($data = array()) {
		
		
		if ( is_array($data) ) {
			
			$data = array_map(array($this,'wpd_ws_nohtml_kses'), $data);
			
		} /*elseif ( is_object($data) ) {
			
			$vars = get_object_vars( $data );
			
			foreach ($vars as $key=>$val) {
				$data->{$key} = $this->wpd_ws_nohtml_kses( $val );
			}
			
		} */elseif ( is_string( $data ) ) {
			
			$data = wp_filter_nohtml_kses($data);
		}
		
		return $data;
	}

	public function wp_scgcge_get_requests_data( $args=array() ) {
	
		global $wpdb;
		$sql = "SELECT * FROM ".WP_SCGC_ASIC_COMPANIES_TABLE." WHERE 1=1";
		$sql2 = "SELECT COUNT(*) FROM ".WP_SCGC_ASIC_COMPANIES_TABLE." WHERE 1=1";

		if(isset($args['company_name_full']) && !empty($args['company_name_full'])) {
			$sql .= " AND company_name_full like '%" . $args['company_name_full'] . "%'";
			$sql2 .= " AND company_name_full like '%" . $args['company_name_full'] . "%'";
		}
		if(isset($args['order_id']) && !empty($args['order_id'])) {
			$sql .= " AND order_id = '" . $args['order_id'] . "'";
			$sql2 .= " AND order_id = '" . $args['order_id'] . "'";
		}
		if(isset($args['user_id']) && !empty($args['user_id'])) {
			$sql .= " AND user_id = '" . $args['user_id'] . "'";
			$sql2 .= " AND user_id = '" . $args['user_id'] . "'";
		}
		if(isset($args['status']) && !empty($args['status']) && $args['status'] != '') {
			$sql .= " AND status = '" . $args['status'] . "'";
			$sql2 .= " AND status = '" . $args['status'] . "'";
		}
		if(isset($args['orderby']) && !empty($args['orderby'])) {
			$sql .= " ORDER BY " . $args['orderby'];
		}
		if(isset($args['order']) && !empty($args['order'])) {
			$sql .= " " . $args['order'];
		}
		if(isset($args['offset'])) {
			$sql .= " LIMIT " . $args['offset'];
		}
		if(isset($args['posts_per_page']) && !empty($args['posts_per_page'])) {
			$sql .= " , " . $args['posts_per_page'];
		}
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		$data_res['data']	= $result;

		//Get Total count of Items
		$data_res['total'] = $wpdb->get_var( $sql2 );
		//$data_res['total'] = count($data_res['data']);

		return $data_res;
	}

	/**
	 * Bulk Deletion
	 *
	 * Does handle deleting requests from the
	 * database table.
	 *
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
	 */
	public function wp_scgcge_bulk_delete( $args = array() ) { 
   
   		global $wpdb;
		
		if(isset($args['company_id']) && !empty($args['company_id'])) {
		
			$code = $wpdb->get_var("SELECT `code` FROM $wpdb->prefix" . "asic_companies WHERE id = '" . $args['company_id'] . "'");
			
			$wpdb->delete( WP_SCGC_ASIC_ENTITY_SHARES_TABLE, array( 'code' => $code ) );
			$wpdb->delete( WP_SCGC_ASIC_ENTITIES_TABLE, array( 'code' => $code ) );
			$wpdb->delete( WP_SCGC_ASIC_COMPANIES_TABLE, array( 'code' => $code ) );
			
		}
	}


	/**
	 * All Request Status
	 * 
	 * Handles to return all request status
	 * 
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
	 */
	public function wp_scgcge_get_status_label( $status = '' ) {
		
		$all_status = array(
			'new' 				=> __( 'New', 'scgcge' ),
			'finished' 			=> __( 'Finished', 'scgcge' ),
			'validation_failed' => __( 'Validation Failed', 'scgcge' )
		);

		if( empty( $status ) ) {
			
			return $all_status;
		}
		return !empty( $status ) && isset( $all_status[$status] ) ? $all_status[$status] : '';
	}	
		
 }
?>