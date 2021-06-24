<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SCGC GetEDGE API requests1
 *
 * The html markup for all requests1 list
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Wp_Scg_Api_Requests1_List extends WP_List_Table
{
    public $model;

    public $render;

    public $per_page;

    public function __construct()
    {
        global $wp_scgcge_model;

        $this->model = $wp_scgcge_model;

        //Set parent defaults
        parent::__construct([
            'singular' => 'requestid',     //singular name of the listed records
            'plural' => 'requestids',    //plural name of the listed records
            'ajax' => false       //does this table support ajax?
        ]);

        $this->model = $wp_scgcge_model;

        $this->per_page = apply_filters('wp_scgcge_requests_per_page', 10); // Per page
    }

    /**
     * Displaying API Requests1 Lists
     *
     * Does prepare the data for displaying requests1 lists in the table.
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function display_api_request_lists()
    {
        $prefix = WP_SCGC_GE_META_PREFIX;

        // Taking parameter
        $orderby = isset($_GET['orderby']) ? urldecode($_GET['orderby']) : 'id';
        $order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $search = isset($_GET['s']) ? sanitize_text_field(trim($_GET['s'])) : null;

        $args = [
            'posts_per_page' => $this->per_page,
            'page' => isset($_GET['paged']) ? $_GET['paged'] : null,
            'orderby' => $orderby,
            'order' => $order,
            'offset' => ($this->get_pagenum() - 1) * $this->per_page
        ];

        //search by company name
        if (isset($_REQUEST['wp_scgcge_company_name_full']) && !empty($_REQUEST['wp_scgcge_company_name_full'])) {
            $args['company_name_full'] = $_REQUEST['wp_scgcge_company_name_full'];
        }

        //search by order id
        if (isset($_REQUEST['wp_scgcge_order_id']) && !empty($_REQUEST['wp_scgcge_order_id'])) {
            $args['order_id'] = $_REQUEST['wp_scgcge_order_id'];
        }

        //search by user id
        if (isset($_REQUEST['wp_scgcge_user_id']) && !empty($_REQUEST['wp_scgcge_user_id'])) {
            $args['user_id'] = $_REQUEST['wp_scgcge_user_id'];
        }

        //search by status
        if (isset($_REQUEST['wp_scgcge_status']) && !empty($_REQUEST['wp_scgcge_status'])) {
            $args['status'] = urldecode(strtolower($_REQUEST['wp_scgcge_status']));
        }

        //call function to retrive data from table
        $data = $this->model->wp_scgcge_get_requests_data($args);
        return $data;
    }

    /**
     * Mange column data
     *
     * Default Column for listing table
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'company_name_full':
                $title = $item[$column_name];
                if (strlen($title) > 50) {
                    $title = substr($title, 0, 50);
                    $title = $title . '...';
                }
                return stripslashes_deep($title);
            case 'order_id':
                $order_number = (isset($item[$column_name]) && !empty($item[$column_name])) ? 'Order #' . $item[$column_name] : '-';
                return $order_number;
            case 'status':
                $document = (isset($item['document'])) ? '<br>' . $item['document'] : '';
                $status = (isset($item[$column_name]) && !empty($item[$column_name])) ? $item[$column_name] . $document : 'Incomplete';
                return $status;
            case 'user_id':
                $user_id = $item[$column_name];
                $user = ($user_id == 0 ? 'Guest' : ucfirst(get_userdata($user_id)->first_name) . ' ' . ucfirst(get_userdata($user_id)->last_name) . ' (#' . $user_id . ')');
                return $user;
            case 'action':
                $vieworderhtml = '';
                if (isset($item['order_id']) && !empty($item['order_id'])) {
                    $viewOrderLink = add_query_arg(
                        [
                            'post' => $item['order_id'],
                            'action' => 'edit'
                        ],
                        admin_url('post.php')
                    );
                    $vieworderhtml = '<a href="' . $viewOrderLink . '" class="wp-scgc-view-order">' . __('View Order', 'scgcge') . '</a>';
                }
                return $vieworderhtml;
            default:
                return $item[$column_name];
        }
    }

    /**
     * Manage Post Title Column
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function column_company_name_full($item)
    {
        if (strtolower($item['status']) == 'finished') {
            //Build row actions
            $actions = [
                //'delete' => sprintf('<a href="?page=%s&action=%s&requestid[]=%s">' . __('Delete', 'scgcge') . '</a>', $_REQUEST['page'], 'delete', $item['id']),
                'documents' => sprintf('<a href="?page=%s&action=%s&requestid=%s">' . __('Documents', 'scgcge') . '</a>', $_REQUEST['page'], 'documents', $item['id']),
                'documentsnew' => sprintf('<a href="?page=%s&action=%s&requestid=%s">' . __('Regenerate Documents', 'scgcge') . '</a>', $_REQUEST['page'], 'documentsnew', $item['id']),
                'certificate' => sprintf('<a href="?page=%s&action=%s&requestid=%s">' . __('Certificate', 'scgcge') . '</a>', $_REQUEST['page'], 'certificate', $item['id']),
            ];
            $actions[] = '<a href="' . home_url() . '/download201?token=' . $item['code'] . '" target="_blank">Form 201</a>';
        } elseif (strtolower($item['status']) == 'validation failed') {
            //Build row actions
            $actions = [
                'validations' => sprintf('<a href="?page=%s&action=%s&requestid=%s">' . __('Validation Errors', 'scgcge') . '</a>', $_REQUEST['page'], 'validations', $item['id']),
            ];
            $actions[] = '<a href="' . home_url() . '/download201?token=' . $item['code'] . '" target="_blank">Form 201</a>';
        } elseif (strtolower($item['status']) == 'manual review') {
            //Build row actions
            $actions = [
                'manualreview' => sprintf('<a href="?page=%s&action=%s&requestid=%s">' . __('Manual Review details', 'scgcge') . '</a>', $_REQUEST['page'], 'manualreview', $item['id']),
            ];
            $actions[] = '<a href="' . home_url() . '/download201?token=' . $item['code'] . '" target="_blank">Form 201</a>';
        } elseif (strtolower($item['status']) == 'rejected') {
            //Build row actions
            $actions = [
                'rejected' => sprintf('<a href="?page=%s&action=%s&requestid=%s">' . __('Rejection details', 'scgcge') . '</a>', $_REQUEST['page'], 'rejected', $item['id']),
            ];
            $actions[] = '<a href="' . home_url() . '/download201?token=' . $item['code'] . '" target="_blank">Form 201</a>';
        } else {
            //Build row actions
            $actions = [
                'delete' => sprintf('<a href="?page=%s&action=%s&requestid[]=%s">' . __('Delete', 'scgcge') . '</a>', $_REQUEST['page'], 'delete', $item['id']),
            ];
        }


        //Return the title contents
        return sprintf(
            '%1$s %2$s',
            /*$1%s*/
            stripslashes_deep($item['company_name_full']),
            /*$2%s*/
            $this->row_actions($actions)
        );
    }

    public function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/
            $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/
            $item['id']                //The value of the checkbox should be the record's id
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
    public function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'company_name_full' => __('Company Name', 'scgcge'),
            'order_id' => __('Order Number', 'scgcge'),
            'status' => __('Status', 'scgcge'),
            'user_id' => __('User', 'scgcge'),
            'action' => __('Action', 'scgcge'),
        ];

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
    public function get_sortable_columns()
    {
        $sortable_columns = [
            'company_name_full' => ['company_name_full', true],    //true means its already sorted
        ];

        return $sortable_columns;
    }

    public function no_items()
    {
        //message to show when no records in database table
        _e('No requests found.', 'scgcge');
    }

    /**
     * Bulk actions field
     *
     * Handles Bulk Action combo box values
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function get_bulk_actions()
    {
        //bulk action combo box parameter
        //if you want to add some more value to bulk action parameter then push key value set in below array
        $actions = [
            //'delete' => __('Delete', 'scgcge')
        ];

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
    public function extra_tablenav($which)
    {
        if ($which == 'top') {
            $parent_id_args = ['fields' => 'id=>parent'];

            //get social posted logs post parent data from database
            $company_ids = $this->model->wp_scgcge_get_requests_data($parent_id_args);

            $post_parent_ids = [];

            $statuses = ['new', 'validation failed', 'manual review', 'finished', 'rejected'];

            $html = '';

            $html .= '<div class="alignleft actions">';

            $html .= '<select name="wp_scgcge_post_id" id="wp_scgcge_post_id" data-placeholder="' . __('Show all company name', 'scgcge') . '"  style="display: none">';
            $html .= '<option value="" ' . selected(isset($_GET['wp_scgcge_post_id']) ? $_GET['wp_scgcge_post_id'] : '', '', false) . '>' . __('Show all company name', 'scgcge') . '</option>';
            if (!empty($company_ids['data'])) {
                foreach ($company_ids['data'] as $company_data) {
                    if (!empty($company_data['id'])) {
                        $post_id = $company_data['id'];

                        $html .= '<option value="' . $post_id . '" ' . selected(isset($_GET['wp_scgcge_post_id']) ? $_GET['wp_scgcge_post_id'] : '', $post_id, false) . '>' . stripslashes_deep($company_data['company_name_full']) . '</option>';
                    }
                }
            }
            $html .= '</select>';

            $html .= '<select name="wp_scgcge_status" id="wp_scgcge_status" data-placeholder="' . __('All Statuses', 'scgcge') . '">';
            $html .= '<option value="" ' . selected(isset($_GET['wp_scgcge_status']) ? $_GET['wp_scgcge_status'] : '', '', false) . '>' . __('All Statuses', 'scgcge') . '</option>';
            foreach ($statuses as $status) {
                $html .= '<option value="' . $status . '" ' . selected(isset($_GET['wp_scgcge_status']) ? $_GET['wp_scgcge_status'] : '', $status, false) . '>' . ucfirst($status) . '</option>';
            }
            $html .= '</select>';

            $html .= '<input type="text" name="wp_scgcge_order_id" id="wp_scgcge_order_id" placeholder="' . __('Filter by order number', 'scgcge') . '" value="' . $this->model->wp_scgcge_escape_attr(isset($_GET['wp_scgcge_order_id']) ? $_GET['wp_scgcge_order_id'] : '') . '">';
            $html .= '<input type="text" name="wp_scgcge_company_name_full" id="wp_scgcge_company_name_full" placeholder="' . __('Filter by company name', 'scgcge') . '" value="' . $this->model->wp_scgcge_escape_attr(isset($_GET['wp_scgcge_company_name_full']) ? stripslashes_deep($_GET['wp_scgcge_company_name_full']) : '') . '">';
            $html .= '<input type="text" name="wp_scgcge_user_id" id="wp_scgcge_user_id" placeholder="' . __('Filter by user ID', 'scgcge') . '" value="' . $this->model->wp_scgcge_escape_attr(isset($_GET['wp_scgcge_user_id']) ? $_GET['wp_scgcge_user_id'] : '') . '">';

            $html .= '	<input type="submit" value="' . __('Filter', 'scgcge') . '" class="button" id="post-query-submit" name="">';
            $html .= '</div>';

            echo $html;
        }
    }

    public function prepare_items()
    {
        // Get how many records per page to show
        $per_page = $this->per_page;

        // Get All, Hidden, Sortable columns
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();

        // Get final column header
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Get Data of particular page
        $data_res = $this->display_api_request_lists();
        $data = $data_res['data'];

        // Get current page number
        $current_page = $this->get_pagenum();

        // Get total count
        $total_items = $data_res['total'];

        // Get page items
        $this->items = $data;

        // We also have to register our pagination options & calculations.
        $this->set_pagination_args([
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
        ]);
    }
}

//Create an instance of our package class...
$WpScgcApiRequests1ListTable = new Wp_Scg_Api_Requests1_List();

//Fetch, prepare, sort, and filter our data...
$WpScgcApiRequests1ListTable->prepare_items();

if (isset($_GET['message']) && !empty($_GET['message'])) { //check message
    if ($_GET['message'] == '3') { //check message
        echo '<div class="updated fade" id="message">
				<p><strong>' . __('Record (s) deleted successfully.', 'scgcge') . '</strong></p>
			</div>';
    }
}

?>

<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
<form id="product-filter" method="get" class="wp-scgcge-form">

    <!-- For plugins, we also need to ensure that the form posts back to our current page -->
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

    <!-- Now we can render the completed list table -->
    <?php $WpScgcApiRequests1ListTable->display(); ?>

</form>