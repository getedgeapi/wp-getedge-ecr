<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * SCGC GetEDGE API requests
 *
 * The html markup for all requests list
 * 
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
	
class Wp_Scg_Api_Requests_List extends WP_List_Table {
	
	var $model, $render, $per_page;
	
	function __construct(){
		
		global $wp_scgcge_model;
		
		$this->model  = $wp_scgcge_model;
		
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'requestid',     //singular name of the listed records
            'plural'    => 'requestids',    //plural name of the listed records
            'ajax'      => false       //does this table support ajax?
        ) );   
		
		$this->per_page	= apply_filters( 'wp_scgcge_requests_per_page', 10 ); // Per page
	}
    
    /**
	 * Displaying API Requests Lists
	 *
	 * Does prepare the data for displaying requests lists in the table.
	 * 
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
	 */	
	function display_api_request_lists() {
	
		$prefix = WP_SCGC_GE_META_PREFIX;
			
		//if search is call then pass searching value to function for displaying searching values
		$args = array();
		
		// Taking parameter
		$orderby 	= isset( $_GET['orderby'] )	? urldecode( $_GET['orderby'] )		: 'ID';
		$order		= isset( $_GET['order'] )	? $_GET['order']                	: 'DESC';
		$search 	= isset( $_GET['s'] ) 		? sanitize_text_field( trim($_GET['s']) )	: null;
		
		$args = array(
			'posts_per_page'		=> $this->per_page,
			'page'					=> isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'orderby'				=> $orderby,
			'order'					=> $order,
			'offset'  				=> ( $this->get_pagenum() - 1 ) * $this->per_page,
			'wp_scgcge_request_list'=> true
		);
		
		//searched by search
		if( !empty( $search ) ) {
			$args['s']	= $search;
		}
		
		//searched by post name
		if(isset($_REQUEST['wp_scgcge_post_id']) && !empty($_REQUEST['wp_scgcge_post_id'])) {
			$args['p']	= $_REQUEST['wp_scgcge_post_id'];
		}

		//searched by order id
		if(isset($_REQUEST['wp_scgcge_order_id']) && !empty($_REQUEST['wp_scgcge_order_id'])) {
			$args['meta_query']	= array(
											array(
													'key' => $prefix . 'order_number',
													'value' => $_REQUEST['wp_scgcge_order_id'],
												)
										);
		}
		
		//get api requests list data from database
		$results = $this->model->wp_scgcge_get_requests_data( $args );

		$data	= isset( $results['data'] ) ? $results['data'] : '';
		$total	= isset( $results['total'] ) ? $results['total'] : 0;
		
		if( !empty( $data ) ) {

			foreach ($data as $key => $value){

				$title = $post_type = $edit_link = '';

				//post title & post type
				if( isset( $value[ 'ID' ] ) && !empty( $value[ 'ID' ] ) ) { // Check post parent is not empty
					$title		= get_the_title( $value[ 'ID' ] );
					$post_type	= get_post_type( $value[ 'post_parent' ] );
				}
				$data[$key]['post_title'] 	= $title;
				$data[$key]['post_type'] 	= $post_type;

				//order number
				$order_number = get_post_meta( $value['ID'], $prefix . 'order_number', true );
				$data[$key]['order_number'] = $order_number;

				//status
				$status = get_post_meta( $value['ID'], $prefix . 'request_status', true );
				$data[$key]['status'] = $status;

				//user
				$user = get_post_meta( $value['ID'], $prefix . 'user_id', true );
				$data[$key]['user'] = $user;

				//action
				$view_order_link = '';
				if( !empty($order_number) ){
					$view_order_link = add_query_arg( 
						array( 
							'post'   => $order_number ,
							'action' => 'edit'
						), admin_url( 'post.php' ) 
					);
				}
				$data[$key]['action'] = $view_order_link; 
			}
		}
		
		$result_arr['data']		= !empty($data) ? $data : array();
		$result_arr['total'] 	= $total; // Total no of data
		
		return $result_arr;
	}
	
	/**
	 * Mange column data
	 *
	 * Default Column for listing table
	 * 
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
	 */
	function column_default( $item, $column_name ){

		switch( $column_name ) {
			case 'post_title':
				$title = $item[ $column_name ];
		    	if( strlen( $title ) > 50 ) {
					$title = substr( $title, 0, 50 );
					$title = $title.'...';
				}
				return $title;
			case 'order_number':
				$order_number = ( isset( $item[ $column_name ] ) && !empty($item[ $column_name ] ) )? 'Order #'.$item[ $column_name ] : 'N/A';
			    return $order_number;
			case 'status':
				$status = ( isset( $item[ $column_name ] ) && !empty($item[ $column_name ] ) )? $this->model->wp_scgcge_get_status_label( $item[ $column_name ] ) : 'Incomplete';
				return $status;
			case 'user':
				$user_id = $item[ $column_name ];
				$user 	 = ( $user_id == 0 ? 'Guest' : ucfirst(get_userdata($user_id)->first_name) .' '.ucfirst(get_userdata($user_id)->last_name).' (#'.$user_id.')' );
				return $user;
			case 'action':
				$vieworderhtml = '';
				if( isset($item[ $column_name ]) && !empty($item[ $column_name ])){
					$viewOrderLink = $item[ $column_name ];
					$vieworderhtml = '<a href="'.$viewOrderLink.'" class="wp-scgc-view-order">'.__( 'View Order', 'scgcge' ).'</a>';
				}
				return $vieworderhtml;
            default:
				return $item[ $column_name ];
        }
    }
    
	/**
	 * Mange post type column data
	 *
	 * Handles to modify post type column for listing table
	 * 
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
	 */
    function column_post_type($item) {
    	
		// get all custom post types
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		
		$post_type_sort_link = '';
		if( !empty( $item[ 'post_type' ] ) && isset( $post_types[$item[ 'post_type' ]]->label ) ) {
			
			$post_type_sort_link = $post_types[$item[ 'post_type' ]]->label;
		}
		return $post_type_sort_link;
    }
    
    /**
     * Manage Post Title Column
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    
    function column_post_title($item){
    	
    	$pagestr = $pagenumber = '';
    	if( isset( $_GET['paged'] ) ) { $pagestr = '&paged=%s'; $pagenumber = $_GET['paged']; }
    	 
    	$actions['delete'] = sprintf('<a class="wp-scgcge-post-title-delete" href="?page=%s&action=%s&requestid[]=%s'.$pagestr.'">'.__('Delete', 'scgcge').'</a>','wp-scgcge-api-requests','delete',$item['ID'], $pagenumber );
    	
         //Return the title contents	        
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['post_title'],
            /*$2%s*/ $this->row_actions( $actions )
        );
        
    }
   	
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }
    
    /**
     * Display Columns
     * 
     * Handles which columns to show in table
     * 
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
     */
	function get_columns(){
	
        $columns = array(
			'cb'      	  => '<input type="checkbox" />', //Render a checkbox instead of text
            'post_title'  => __('Company Name', 'scgcge' ),
            'order_number'=> __('Order Number', 'scgcge' ),
            'status'	  => __('Status', 'scgcge' ),
            'user'        => __('User', 'scgcge' ),
            'action'	  => __('Action', 'scgcge' ),
        );
        return $columns;
    }
	
    /**
     * Sortable Columns
     *
     * Handles soratable columns of the table
     * 
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
     */
	function get_sortable_columns() {
		
		$sortable_columns	= array(
			'post_title'	=>	array( 'post_title', true ),    //true means its already sorted
		);
		
		return $sortable_columns;
	}
	
	function no_items() {
		//message to show when no records in database table
		_e( 'No requests found.', 'scgcge' );
	}
	
	/**
     * Bulk actions field
     *
     * Handles Bulk Action combo box values
     * 
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
     */
	function get_bulk_actions() {
		//bulk action combo box parameter
		//if you want to add some more value to bulk action parameter then push key value set in below array
		$actions = array(
			'delete'    => __('Delete','scgcge')
		);
		
		return $actions;
	}
    
	/**
     * Add filter for post types
     *
     * Handles to display records for particular post type
     * 
	 * @package SCGC GetEDGE API
	 * @since 1.0.0
     */
    function extra_tablenav( $which ) {
    	
    	if( $which == 'top' ) {
    		
    		$parent_id_args = array( 'fields' => 'id=>parent' );
    		
			//get social posted logs post parent data from database
			$post_ids = $this->model->wp_scgcge_get_requests_data( $parent_id_args );
				
			$post_parent_ids = array();
			
			$html = '';
			
    		$html .= '<div class="alignleft actions">';
    			
			$html .= '<select name="wp_scgcge_post_id" id="wp_scgcge_post_id" data-placeholder="' . __( 'Show all company name', 'scgcge' ) . '">';
			
			$html .= '<option value="" ' .  selected( isset( $_GET['wp_scgcge_post_id'] ) ? $_GET['wp_scgcge_post_id'] : '', '', false ) . '>'.__( 'Show all company name', 'scgcge' ).'</option>';
	
			if ( !empty( $post_ids ) ) {

				foreach ( $post_ids as $post_data ) {
					
					if( !empty( $post_data['ID'] ) ) {
						
						$post_id = $post_data['ID'];
						
						$html .= '<option value="' . $post_id . '" ' . selected( isset( $_GET['wp_scgcge_post_id'] ) ? $_GET['wp_scgcge_post_id'] : '', $post_id , false ) . '>' . get_the_title( $post_id ) . '</option>';
					}
				}
			
			}
			$html .= '</select>';

			$html .= '<input type="text" name="wp_scgcge_order_id" id="wp_scgcge_order_id" placeholder="' . __( 'Enter #Order Number', 'scgcge' ) . '" value="'.$this->model->wp_scgcge_escape_attr( isset($_GET['wp_scgcge_order_id']) ? $_GET['wp_scgcge_order_id'] : '' ) .'">';
    			
    		$html .= '	<input type="submit" value="'.__( 'Filter', 'scgcge' ).'" class="button" id="post-query-submit" name="">';
    		$html .= '</div>';
    		
			echo $html;
    	}
    }

      
    
    function prepare_items() {
        
        // Get how many records per page to show
        $per_page	= $this->per_page;
       
        // Get All, Hidden, Sortable columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

		// Get final column header
        $this->_column_headers = array($columns, $hidden, $sortable);
        
		// Get Data of particular page
		$data_res 	= $this->display_api_request_lists();
		$data 		= $data_res['data'];		
		
		// Get current page number
        $current_page = $this->get_pagenum();
        
		// Get total count
        $total_items  = $data_res['total'];
        
        // Get page items
        $this->items = $data;
        
		// We also have to register our pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}

//Create an instance of our package class...
$WpScgcApiRequestsListTable = new Wp_Scg_Api_Requests_List();
	
//Fetch, prepare, sort, and filter our data...
$WpScgcApiRequestsListTable->prepare_items();
		
    	//showing sorting links on the top of the list
    	$WpScgcApiRequestsListTable->views(); 
    	
		if(isset($_GET['message']) && !empty($_GET['message']) ) { //check message
			
			if( $_GET['message'] == '3' ) { //check message
				
				echo '<div class="updated fade" id="message">
						<p><strong>'.__("Record (s) deleted successfully.",'scgcge').'</strong></p>
					</div>'; 
				
			} 
		}
		
    ?>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
    <form id="product-filter" method="get" class="wp-scgcge-form">
        
    	<!-- For plugins, we also need to ensure that the form posts back to our current page -->
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        
        <!-- Now we can render the completed list table -->
        <?php $WpScgcApiRequestsListTable->display(); ?>
        
    </form>