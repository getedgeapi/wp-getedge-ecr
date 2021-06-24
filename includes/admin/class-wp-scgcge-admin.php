<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Pages Class
 *
 * Handles generic Admin functionailties
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

class Wp_Scgc_Admin_Pages
{
    public $model;
    public $scripts;

    public function __construct()
    {
        global $wp_scgcge_model, $wp_scgcge_scripts;
        $this->model = $wp_scgcge_model;
        $this->scripts = $wp_scgcge_scripts;
    }

    /**
     * Create menu page
     *
     * Adding required menu pages and submenu pages
     * to manage the plugin functionality
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_add_menu_page()
    {
        // plugin settings option
        $wp_scgcge_requests = add_menu_page(__('GetEDGE ECR', 'scgcge'), __('GetEDGE ECR', 'scgcge'), wpscgcgelevel, 'wp-scgcge-api-requests', '', 'dashicons-networking');

        $wp_scgcge_requests = add_submenu_page('wp-scgcge-api-requests', __('GetEDGE ECR Settings', 'scgcge'), __('Requests', 'scgcge'), wpscgcgelevel, 'wp-scgcge-api-requests', [$this, 'wp_scgcge_api_requests']);

        $wp_scgcge_settings = add_submenu_page('wp-scgcge-api-requests', __('GetEDGE ECR Settings', 'scgcge'), __('Settings', 'scgcge'), wpscgcgelevel, 'wp-scgcge-settings', [$this, 'wp_scgcge_settings']);

        add_action("admin_head-$wp_scgcge_requests", [$this->scripts, 'wp_scgcge_settings_scripts']);
    }

    /**
     * Register Settings
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_admin_init()
    {
        register_setting('wp_scgcge_plugin_options', 'wp_scgcge_options', [$this, 'wp_scgcge_validate_options']);
    }

    /**
     * Validation/Sanitization
     *
     * Sanitize and validate input fields.
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_validate_options($input)
    {
        global $wp_scgcge_options;

        if (empty($input['hex_accent_bg'])) {
            $input['hex_accent_bg'] = '#0665d0';
        }
        if (empty($input['hex_accent_colour'])) {
            $input['hex_accent_colour'] = '#ffffff';
        }
        if (empty($input['hex_hover_bg'])) {
            $input['hex_hover_bg'] = '#343a40';
        }
        if (empty($input['hex_hover_colour'])) {
            $input['hex_hover_colour'] = '#ffffff';
        }

        // upload public certificate file

        if (!empty($_FILES['public_cert_file']['tmp_name'])) {
            $upload_file = wp_handle_upload($_FILES['public_cert_file'], [
                'test_form' => false,
                'mimes' => [
                    'cer' => 'text/plain'
                ]
            ]);
            $input['public_cert_file'] = $upload_file['file'];
        } else {
            $input['public_cert_file'] = $wp_scgcge_options['public_cert_file'];
        }

        // upload private key file
        if (!empty($_FILES['private_key_file']['tmp_name'])) {
            $upload_file = wp_handle_upload($_FILES['private_key_file'], [
                'test_form' => false,
                'mimes' => [
                    'pem' => 'text/plain'
                ]
            ]);
            $input['private_key_file'] = $upload_file['file'];
        } else {
            $input['private_key_file'] = $wp_scgcge_options['private_key_file'];
        }

        return $input;
    }

    /**
     * Includes Plugin Settings
     *
     * Renders plugin settings page
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_settings()
    {
        // Xero oAuth2.0 authentication URL
        global $post;
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $authUrl = null;
        if( isset($wp_scgcge_options['getedge_xero_client_id']) && isset($wp_scgcge_options['getedge_xero_client_secret']) ) {
            if((get_option('getedge_xero_auth_expiration') == null || (get_option('getedge_xero_auth_expiration') !== null && get_option('getedge_xero_auth_expiration') < time()))) {
                $provider = new \Calcinai\OAuth2\Client\Provider\Xero([
                    'clientId'          => $wp_scgcge_options['getedge_xero_client_id'],
                    'clientSecret'      => $wp_scgcge_options['getedge_xero_client_secret'],
                    'redirectUri'       => home_url() . '/xeroAuth',
                ]);
                
                $authUrl = $provider->getAuthorizationUrl([
                    'scope' => 'offline_access accounting.transactions accounting.contacts'
                ]);
    
                $_SESSION['oauth2state'] = $provider->getState();
            }
        }
        $xeroAuth20 = false;
        if(get_option('getedge_xero_auth_expiration') !== null && get_option('getedge_xero_auth_expiration') > time()) {
            $xeroAuth20 = true;
        }
        include_once WP_SCGC_GE_ADMIN . '/forms/wp-scgcge-plugin-settings.php';
    }

    /**
     * API Requests list
     *
     * Renders all api requests list page
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_api_requests()
    {
?>
        <div class="wrap">

            <h2><?php _e('GetEDGE ECR Requests', 'scgcge'); ?></h2>

            <div class="content">
                <div class="wp-scgcge-content">
                    <div class="wp-scgcge-tab-content" id="wp-scgcge-tab-requests" style="display:block">
                        <?php
                        include WP_SCGC_GE_ADMIN . '/forms/wp-scgcge-requests-list.php'; ?>
                    </div>
                </div>
            </div>
        </div>
<?php
    }

    /**
     * Bulk Action
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_process_bulk_action()
    {
        global $wpdb, $wp_scgcge_public;
        // check if action is not blank and if page is request listing page
        if (((isset($_GET['action']) && $_GET['action'] == 'delete') || (isset($_GET['action2']) && $_GET['action2'] == 'delete')) && isset($_GET['page']) && $_GET['page'] == 'wp-scgcge-api-requests') { //check action and page
            // get redirect url
            $redirect_url = add_query_arg(['page' => 'wp-scgcge-api-requests'], admin_url('admin.php'));

            if (isset($_GET['requestid'])) {
                $action_on_id = $_GET['requestid'];
            } else {
                $action_on_id = [];
            }

            if (count($action_on_id) > 0) { //check if any checkbox is selected
                foreach ($action_on_id as $company_id) {
                    $args = [
                        'company_id' => $company_id,
                    ];
                    $this->model->wp_scgcge_bulk_delete($args);
                }

                $redirect_url = add_query_arg(['message' => '3'], $redirect_url);

                wp_redirect($redirect_url);
                exit;
            } else {
                wp_redirect($redirect_url);
                exit;
            }
        }

        if ((isset($_GET['action']) && $_GET['action'] == 'validations') && isset($_GET['page']) && $_GET['page'] == 'wp-scgcge-api-requests') { //check action and page
            global $wpdb;
            $wp_scgcge_options = get_option('wp_scgcge_options');

            header('HTTP/1.1 200 OK');
            $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE id = '" . $_GET['requestid'] . "'";
            $company = $wpdb->get_row($query, ARRAY_A);
            if (empty($company) || !isset($company)) {
                $error = 'Not authorised.';
                wp_redirect($redirect_url);
                exit;
            }

            $array = [
                'form_main_id' => $company['code'],
                'edge_id' => $company['edge_id'],
                'order_id' => $company['order_id'],
                'token' => $wp_scgcge_options['ge_api_key'],
                'user_id' => $company['user_id'],
                'search_result' => $company['search_result'],
                'legal_elements' => $company['legal_elements'],
            ];
            $checkErrors = checkLog($array);
            foreach ($checkErrors as $report) {
                if ($report['communication']['form'] == 'VALIDATION') {
                    $temp = time() . '.zip';
                    $details = $report['communication']['values']['documents_rejected'][0];
                    $text = 'Company Name: ' . stripslashes_deep($details['company_name']) . "\n";
                    $text .= 'Order ID ' . $company['order_id'] . ' / Request ID ' . $details['trace_number'] . "\n\n";
                    $text .= "ASIC Validation Errors\n======================" . "\n\n";
                    foreach ($details['items'] as $item) {
                        $text .= 'Segment: ' . $item['segment_tag'] . '/' . $item['number'] . "\n";
                        $text .= 'Contents: ' . $item['contents'] . "\n";
                        $text .= 'Error: ' . $item['error'] . "\n--------------\n\n";
                    }
                    //file_put_contents($temp, print_r($report['communication']['values']['documents_rejected'][0], true));
                    file_put_contents($temp, $text);
                    if (file_exists($temp)) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: text/plain');
                        header('Content-Disposition: attachment; filename="Order ' . $company['order_id'] . ' - Validation Errors.txt"');
                        header('Expires: on, 01 Jan 1970 00:00:00 GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                        header('Cache-Control: no-store, no-cache, must-revalidate');
                        header('Cache-Control: post-check=0, pre-check=0', false);
                        header('Pragma: no-cache');
                        header('Content-Length: ' . filesize($temp));
                        readfile($temp);
                        unlink($temp);
                        exit;
                    }
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        }

        if ((isset($_GET['action']) && $_GET['action'] == 'manualreview') && isset($_GET['page']) && $_GET['page'] == 'wp-scgcge-api-requests') { //check action and page
            global $wpdb;
            $wp_scgcge_options = get_option('wp_scgcge_options');

            header('HTTP/1.1 200 OK');
            $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE id = '" . $_GET['requestid'] . "'";
            $company = $wpdb->get_row($query, ARRAY_A);
            if (empty($company) || !isset($company)) {
                $error = 'Not authorised.';
                wp_redirect($redirect_url);
                exit;
            }

            $array = [
                'form_main_id' => $company['code'],
                'edge_id' => $company['edge_id'],
                'order_id' => $company['order_id'],
                'token' => $wp_scgcge_options['ge_api_key'],
                'user_id' => $company['user_id'],
                'search_result' => $company['search_result'],
                'legal_elements' => $company['legal_elements'],
            ];
            $checkErrors = checkLog($array);
            foreach ($checkErrors as $report) {
                if ($report['communication']['form'] == 'ASCRA56') {
                    $temp = time() . '.zip';
                    $details = $report['communication']['values']['ZTE'];
                    $text = 'Company Name: ' . $report['communication']['values']['ZNR']['proposed_name'] . "\n";
                    $text .= 'Order ID ' . $company['order_id'] . ' / Request ID ' . $report['communication']['reference'] . ' / Document ' . $report['communication']['values']['ZNR']['document_number'] . "\n\n";
                    $text .= $report['communication']['values']['ZNR']['asic_advice_type_description'] . "\n======================" . "\n\n";
                    $text .= $details;
                    //file_put_contents($temp, print_r($report['communication']['values']['documents_rejected'][0], true));
                    file_put_contents($temp, $text);
                    if (file_exists($temp)) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: text/plain');
                        header('Content-Disposition: attachment; filename="Order ' . $company['order_id'] . ' - Manual Review.txt"');
                        header('Expires: on, 01 Jan 1970 00:00:00 GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                        header('Cache-Control: no-store, no-cache, must-revalidate');
                        header('Cache-Control: post-check=0, pre-check=0', false);
                        header('Pragma: no-cache');
                        header('Content-Length: ' . filesize($temp));
                        readfile($temp);
                        unlink($temp);
                        exit;
                    }
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        }

        if ((isset($_GET['action']) && $_GET['action'] == 'rejected') && isset($_GET['page']) && $_GET['page'] == 'wp-scgcge-api-requests') { //check action and page
            global $wpdb;
            $wp_scgcge_options = get_option('wp_scgcge_options');

            header('HTTP/1.1 200 OK');
            $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE id = '" . $_GET['requestid'] . "'";
            $company = $wpdb->get_row($query, ARRAY_A);
            if (empty($company) || !isset($company)) {
                $error = 'Not authorised.';
                wp_redirect($redirect_url);
                exit;
            }

            $array = [
                'form_main_id' => $company['code'],
                'edge_id' => $company['edge_id'],
                'order_id' => $company['order_id'],
                'token' => $wp_scgcge_options['ge_api_key'],
                'user_id' => $company['user_id'],
                'search_result' => $company['search_result'],
                'legal_elements' => $company['legal_elements'],
            ];
            $checkErrors = checkLog($array);
            foreach ($checkErrors as $report) {
                if ($report['communication']['form'] == 'ASCRA56') {
                    $temp = time() . '.zip';
                    $details = $report['communication']['values']['ZTE'];
                    $text = 'Company Name: ' . $report['communication']['values']['ZNR']['proposed_name'] . "\n";
                    $text .= 'Order ID ' . $company['order_id'] . ' / Request ID ' . $report['communication']['reference'] . ' / Document ' . $report['communication']['values']['ZNR']['document_number'] . "\n\n";
                    $text .= $report['communication']['values']['ZNR']['asic_advice_type_description'] . "\n======================" . "\n\n";
                    $text .= $details;
                    //file_put_contents($temp, print_r($report['communication']['values']['documents_rejected'][0], true));
                    file_put_contents($temp, $text);
                    if (file_exists($temp)) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: text/plain');
                        header('Content-Disposition: attachment; filename="Order ' . $company['order_id'] . ' - Rejection Notice.txt"');
                        header('Expires: on, 01 Jan 1970 00:00:00 GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                        header('Cache-Control: no-store, no-cache, must-revalidate');
                        header('Cache-Control: post-check=0, pre-check=0', false);
                        header('Pragma: no-cache');
                        header('Content-Length: ' . filesize($temp));
                        readfile($temp);
                        unlink($temp);
                        exit;
                    }
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        }

        if (((isset($_GET['action']) && ($_GET['action'] == 'documents' || $_GET['action'] == 'documentsnew'))) && isset($_GET['page']) && $_GET['page'] == 'wp-scgcge-api-requests') { //check action and page
            global $wpdb;
            $wp_scgcge_options = get_option('wp_scgcge_options');

            header('HTTP/1.1 200 OK');
            $current_user = get_current_user_id();
            $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE id = '" . $_GET['requestid'] . "'";
            $company = $wpdb->get_row($query, ARRAY_A);
            $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '" . $company['code'] . "'"; // AND user_id = '$current_user'
            $entities = $wpdb->get_results($query, ARRAY_A);
            $query = "SELECT * FROM $wpdb->prefix" . "asic_entity_shares WHERE code = '" . $company['code'] . "'"; // AND user_id = '$current_user'
            $shares = $wpdb->get_results($query, ARRAY_A);

            if (empty($company) || !isset($company)) {
                $error = 'Not authorised.';
                wp_redirect($redirect_url);
                exit;
            }
            $pro_name = get_user_meta($company['user_id'], 'ecr_company_name', true) ? get_user_meta($company['user_id'], 'ecr_company_name', true) : '';
            $pro_image = get_user_meta($company['user_id'], 'profile_pic', true) ? wp_get_attachment_url(get_user_meta($company['user_id'], 'profile_pic', true)) : '';

            $agent = [
                'number' => $wp_scgcge_options['agt_number'] ? $wp_scgcge_options['agt_number'] : '',
                'company' => $wp_scgcge_options['agt_company'] ? $wp_scgcge_options['agt_company'] : '',
                'name' => $wp_scgcge_options['agt_name'] ? $wp_scgcge_options['agt_name'] : '',
                'email' => $wp_scgcge_options['agt_email'] ? $wp_scgcge_options['agt_email'] : '',
                'phone' => $wp_scgcge_options['agt_phone'] ? $wp_scgcge_options['agt_phone'] : '',
                'street' => $wp_scgcge_options['agt_street'] ? $wp_scgcge_options['agt_street'] : '',
                'suburb' => $wp_scgcge_options['agt_suburb'] ? $wp_scgcge_options['agt_suburb'] : '',
                'state' => $wp_scgcge_options['agt_state'] ? $wp_scgcge_options['agt_state'] : '',
                'postcode' => $wp_scgcge_options['agt_postcode'] ? $wp_scgcge_options['agt_postcode'] : '',
            ];

            $values = ['company' => stripslashes_deep($company), 'entities' => stripslashes_deep($entities), 'shares' => $shares, 'pro_name' => stripslashes_deep($pro_name), 'pro_image' => $pro_image, 'agent' => stripslashes_deep($agent)];

            $test_transmission = !empty($wp_scgcge_options['test_transmission']) ? $wp_scgcge_options['test_transmission'] : '';
            $public = $wp_scgcge_public;
            // $values = $public->prepare_ge_array($company['code'], false, $test_transmission);
            $values['agent'] = $agent;
            $values['pro_name'] = $pro_name;
            $values['pro_image'] = $pro_image;
            $values['registration'] = [
                'acn' => $company['acn'],
            ];

            $temp = time() . '.zip';

            $new = $_GET['action'] == 'documentsnew' ? '&refresh=true' : '';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://documents.getedge.com.au/' . (($wp_scgcge_options['docs_api_key'] == '') ? 'scgc' : $wp_scgcge_options['docs_api_key']) . '/index.php?acn=' . $company['acn'] . $new);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, false);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($values));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec($ch);
            curl_close($ch);

            // dd($result);


            file_put_contents($temp, $result);

            if (file_exists($temp)) {
                header('Content-Description: File Transfer');
                // header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/octet-stream');
                // header('Content-Disposition: attachment; filename="ACN ' . $company['acn'] . ' - Company Documents.docx"');
                header('Content-Type: application/zip, application/octet-stream');
                header('Content-Disposition: attachment; filename="ACN ' . $company['acn'] . ' - Company Documents.zip"');
                header('Expires: on, 01 Jan 1970 00:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');
                header('Content-Length: ' . filesize($temp));
                readfile($temp);
                unlink($temp);
                exit;
            }
            wp_redirect($redirect_url);
            exit;
        }

        if (((isset($_GET['action']) && $_GET['action'] == 'certificate')) && isset($_GET['page']) && $_GET['page'] == 'wp-scgcge-api-requests') { //check action and page
            global $wpdb;
            header('HTTP/1.1 200 OK');
            $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE id = '" . $_GET['requestid'] . "'";
            $company = $wpdb->get_row($query, ARRAY_A);
            $code = $company['code'];
            $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
            $results = $wpdb->get_row($query, ARRAY_A);

            if (!empty($results)) {
                extract($results, EXTR_PREFIX_SAME, 'scgc');
                $file = wp_upload_dir()['basedir'] . '/_certs/' . $acn . '.pdf';
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                flush(); // Flush system output buffer
                readfile($file);
                exit;
            }
            wp_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Adding Hooks
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function add_hooks()
    {
        // add action to add sub-menu pages
        add_action('admin_menu', [$this, 'wp_scgcge_add_menu_page']);

        // add action to register settings
        add_action('admin_init', [$this, 'wp_scgcge_admin_init']);

        // add action to manage delete action
        add_action('admin_init', [$this, 'wp_scgcge_process_bulk_action']);
    }
}
?>