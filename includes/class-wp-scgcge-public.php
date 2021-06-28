<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Public Class
 *
 * Manage Public Panel Class
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
class Wp_Scgc_Public
{
    public $scripts;

    //class constructor
    public function __construct()
    {
        global $wp_scgcge_scripts;

        $this->scripts = $wp_scgcge_scripts;
    }

    /**
     * Adding Custom Endpoints Rewrite
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_custom_rewrite()
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');

        $scgcge_page_id = !empty($wp_scgcge_options['page_id']) ? $wp_scgcge_options['page_id'] : '';
        $post = get_post($scgcge_page_id);
        $scgcge_page_url = $post->post_name;

        add_rewrite_rule($scgcge_page_url . '/general-details', 'index.php?page_id=' . $scgcge_page_id . '', 'top');
        add_rewrite_rule($scgcge_page_url . '/addresses', 'index.php?page_id=' . $scgcge_page_id . '', 'top');
        add_rewrite_rule($scgcge_page_url . '/entities', 'index.php?page_id=' . $scgcge_page_id . '', 'top');
        add_rewrite_rule($scgcge_page_url . '/add-individual', 'index.php?page_id=' . $scgcge_page_id . '', 'top');
        add_rewrite_rule($scgcge_page_url . '/add-company', 'index.php?page_id=' . $scgcge_page_id . '', 'top');
        add_rewrite_rule($scgcge_page_url . '/edit-individual', 'index.php?page_id=' . $scgcge_page_id . '', 'top');
        add_rewrite_rule($scgcge_page_url . '/edit-company', 'index.php?page_id=' . $scgcge_page_id . '', 'top');
        add_rewrite_rule($scgcge_page_url . '/delete-entity', 'index.php?page_id=' . $scgcge_page_id . '', 'top');
        add_rewrite_rule($scgcge_page_url . '/review', 'index.php?page_id=' . $scgcge_page_id . '', 'top');
        print_r(add_rewrite_rule($scgcge_page_url . '/review', 'index.php?page_id=' . $scgcge_page_id . '', 'top'));
    }

    /**
     * Flush rewrite rules.
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_custom_flush_rewrite_rules()
    {
        flush_rewrite_rules();
    }

    /**
     * Adding Template Redirect Cron
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_cron_template_redirect()
    {
        global $wpdb;

        $wp_scgcge_options = get_option('wp_scgcge_options');

        $reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';
        $ge_asic_fee_id = !empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '';
        $scgcge_ge_url = !empty($wp_scgcge_options['ge_url']) ? $wp_scgcge_options['ge_url'] : '';
        $scgcge_ge_api_key = !empty($wp_scgcge_options['ge_api_key']) ? $wp_scgcge_options['ge_api_key'] : '';

        if (is_shop() || is_single($reg_fee_id) || is_single($ge_asic_fee_id)) {
            wp_redirect(home_url());
            exit();
        }

        if (wp_scgcge_current_segment() == 'processData') { //if Registration pages Endpoint processData Than
            header('HTTP/1.1 200 OK');
            require_once WP_SCGC_GE_TEMPLATES . '/company-registration-forms/processData.php';
            exit;
        }

        if (wp_scgcge_current_segment() == 'checkAsic') { //if Registration pages Endpoint checkAsic Than

            $newOrders = "SELECT order_id, code, edge_id FROM $wpdb->prefix" . "asic_companies WHERE order_id IS NOT NULL AND status = 'placed' ";
            $newCompanies = $wpdb->get_results($newOrders, ARRAY_A);
            if (!empty($newCompanies)) {
                foreach ($newCompanies as $newCompany) {
                    $order = wc_get_order($newCompany['order_id']);
                    foreach ($order->get_items() as $item) {
                        $item_id = $item->get_id();
                        $product_id = $item->get_product_id();
                        if ($product_id == $reg_fee_id) {
                            $company_registration_id = get_metadata('post', $newCompany['order_id'], 'company_registration_id', true);
                            $test_transmission = !empty($wp_scgcge_options['test_transmission']) ? $wp_scgcge_options['test_transmission'] : '';
                            $array = $this->prepare_ge_array($company_registration_id, isset($newCompany['edge_id']) ? true : false, $test_transmission);
                            if (!isset($array) || !is_array($array) || empty($array)) {
                                $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                                    'status' => 'validation failed',
                                ], ['code' => $company_registration_id]);
                                // add email
                            }
                            $query = "SELECT company_name_full FROM $wpdb->prefix" . "asic_companies WHERE order_id = '" . $newCompany['order_id'] . "'";
                            $company_name_full = stripslashes_deep($wpdb->get_var($query));

                            $lodge201 = lodge201($company_registration_id, $array, $newCompany['order_id']);
                            if ($lodge201) {
                                $verb = isset($newCompany['edge_id']) ? 'relodged' : 'lodged';
                                
                                $subject = $company_name_full . ' has been ' . $verb . ' with ASIC';
                                $title = $company_name_full . ' has been ' . $verb . ' with ASIC';
                                $text = '<p>' . __('Dear', 'scgcge') . ' ' . ucfirst($order->get_billing_first_name()) . ',<p>';
                                $text .= '<p>' . __('We are pleased to confirm that your application for ', 'scgcge') . $company_name_full . ' has been ' . $verb . ' with ASIC' . ' (Ref# ' . $newCompany['order_id'] . ' / ' . $lodge201 . ').<p>';
                                $text .= '<p>' . __('In most cases, submitted orders will be processed by ASIC within minutes however, please note that unexpected delays may occur if:', 'scgcge') . '</p>';
                                $text .= '<ul><li>' . ('A manual review is initiated by ASIC for reasons such as an unusual name containing non-dictionary words') . '</li><li>' . __('The ASIC system is offline for maintenance or inaccessible for any reason', 'scgcge') . '</li></ul>';
                                $text .= '<p>' . __('You may review the status of your order at anytime by logging in to your', 'scgcge') . ' <a href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) . '/">' . __('client area', 'scgcge') . '</a>.' . __('Information on how to access your client area has been sent to you in a previous email.', 'scgcge') . '</p>';
                                $text .= '<p>' . __('We appreciate and value your comments, suggestions and general feedback as this helps us to further develop our systems for an ever-improving customer experience. Please write to', 'scgcge') . ' ' . get_option('woocommerce_email_from_address') . ' ' . __('with anything that you would like us to know.', 'scgcge') . '</p>';
                                $text .= '<p>' . __('Thank you for your business.', 'scgcge') . ' </p>';
                                $text .= '<p>' . __('Sincerely,', 'scgcge') . '<br>' . get_option('woocommerce_email_from_name') . ' </p>';
                
                                $reference = $company_name_full;
                                

                
                                $this->sendEmail($order->get_billing_email(), $subject, $title, $text);
                                echo 'Order #' . $newCompany['order_id'] . ': lodged<br />';
                            }
                        }
                    }
                }
            }

            $query = "SELECT order_id, edge_id, user_id, code, search_result, legal_elements FROM $wpdb->prefix" . "asic_companies WHERE order_id IS NOT NULL AND edge_id IS NOT NULL AND ( status = 'new' OR status = 'New' OR status = 'sent' OR status = 'transmission ok' OR status = 'validation ok' OR status = 'manual review' OR status = 'retry error E01' OR status = 'stop error D01' OR status = 'retry error E02' OR status = 'retry 0003' OR status = 'retry error D02' OR status = 'retry error D04' )  ";

            $companies = $wpdb->get_results($query, ARRAY_A);

            if (!empty($companies)) {
                foreach ($companies as $company) {
                    $array = [
                        'form_main_id' => $company['code'],
                        'edge_id' => $company['edge_id'],
                        'order_id' => $company['order_id'],
                        'token' => $scgcge_ge_api_key,
                        'user_id' => $company['user_id'],
                        'search_result' => $company['search_result'],
                        'legal_elements' => $company['legal_elements'],
                        'company_name_full' => $company['company_name_full'],
                    ];
                    write_log('Checking request status...');
                    $checkStatus = checkStatus($array);

                    echo 'Order #' . $array['order_id'] . ': ' . $checkStatus . '<br />';
                }
            } else {
                echo __('Nothing new!', 'scgcge');
            }
            //header('HTTP/1.1 200 OK');
            require_once WP_SCGC_GE_LIB . '/blank.html';
            exit;
        }
        
        // Xero oAuth2.0 redirect endpoint
        if (wp_scgcge_current_segment() == 'xeroAuth') {
            if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
                header('Location: ' . site_url().'/wp-admin/admin.php?page=wp-scgcge-settings');
                exit('Invalid state');
            } else {
                $provider = new \Calcinai\OAuth2\Client\Provider\Xero([
                    'clientId'          => $wp_scgcge_options['getedge_xero_client_id'],
                    'clientSecret'      => $wp_scgcge_options['getedge_xero_client_secret'],
                    'redirectUri'       => home_url() . '/xeroAuth',
                ]);
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
                $tenants = $provider->getTenants($token);
                update_option('getedge_xero_auth_token', $token->getToken());
                update_option('getedge_xero_auth_refresh', $token->getRefreshToken());
                update_option('getedge_xero_auth_expiration', $token->getExpires());
                update_option('getedge_xero_org_id', $tenants[0]->id);
                update_option('getedge_xero_org_tenant_id', $tenants[0]->tenantId);
            }
            header('Location: ' . site_url().'/wp-admin/admin.php?page=wp-scgcge-settings');
            exit;
        }

        // Xero oAuth2.0 refresh endpoint
        if (wp_scgcge_current_segment() == 'xeroRefresh') {
            if (get_option('getedge_xero_auth_refresh') != null) {
                $provider = new \Calcinai\OAuth2\Client\Provider\Xero([
                    'clientId'          => $wp_scgcge_options['getedge_xero_client_id'],
                    'clientSecret'      => $wp_scgcge_options['getedge_xero_client_secret'],
                    'redirectUri'       => home_url() . '/xeroAuth',
                ]);
                $newAccessToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => get_option('getedge_xero_auth_refresh')
                ]);
                
                update_option('getedge_xero_auth_token', $newAccessToken->getToken());
                update_option('getedge_xero_auth_refresh', $newAccessToken->getRefreshToken());
                update_option('getedge_xero_auth_expiration', $newAccessToken->getExpires());
                header('HTTP/1.1 200 OK');
                echo 'Xero access token refreshed';
                die();
            } else {
                header('HTTP/1.1 200 OK');
                header('Location: ' . home_url());
                exit;
            }
        }

        // Xero oAuth2.0 disconnect endpoint
        if (wp_scgcge_current_segment() == 'xeroDisconnect') {
            delete_option('getedge_xero_auth_token');
            delete_option('getedge_xero_auth_refresh');
            delete_option('getedge_xero_auth_expiration');
            delete_option('getedge_xero_org_id');
            delete_option('getedge_xero_org_tenant_id');
            header('HTTP/1.1 200 OK');
            header('Location: ' . site_url().'/wp-admin/admin.php?page=wp-scgcge-settings');
            exit;
        }

        //if Registration pages Endpoint downloadCert Than
        if (wp_scgcge_current_segment() == 'downloadCert' && isset($_GET['token']) && preg_replace('/[^a-zA-Z0-9]+/', '', $_GET['token'])) {
            header('HTTP/1.1 200 OK');
            $code = $_GET['token'];
            $current_user = get_current_user_id();
            $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code' AND user_id = '$current_user'";
            $results = $wpdb->get_row($query, ARRAY_A);
            if (!empty($results)) {
                extract($results, EXTR_PREFIX_SAME, 'scgcge');
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
            } else {
                $error = 'Not authorised.';
                wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')) . '?not-authorised');
                exit;
            }
        }

        //if Registration pages Endpoint downloadDocs Than
        if (wp_scgcge_current_segment() == 'downloadDocs' && isset($_GET['token']) && preg_replace('/[^a-zA-Z0-9]+/', '', $_GET['token'])) {
            header('HTTP/1.1 200 OK');
            $code = $_GET['token'];
            $current_user = get_current_user_id();
            $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code' AND user_id = '$current_user'";
            $company = $wpdb->get_row($query, ARRAY_A);
            $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '$code' "; // AND user_id = '$current_user'
            $entities = $wpdb->get_results($query, ARRAY_A);
            $query = "SELECT * FROM $wpdb->prefix" . "asic_entity_shares WHERE code = '$code'"; // AND user_id = '$current_user'
            $shares = $wpdb->get_results($query, ARRAY_A);

            if (empty($company) || !isset($company)) {
                $error = 'Not authorised.';
                wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')) . '?not-authorised');
            }
            $pro_name = get_user_meta($current_user, 'ecr_company_name', true) ? get_user_meta($current_user, 'ecr_company_name', true) : '';
            $pro_image = get_user_meta($current_user, 'profile_pic', true) ? wp_get_attachment_url(get_user_meta($current_user, 'profile_pic', true)) : '';

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
            $temp = time() . '.zip';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://documents.getedge.com.au/' . (($wp_scgcge_options['docs_api_key'] == '') ? 'scgc' : $wp_scgcge_options['docs_api_key']) . '/index.php?acn=' . $company['acn']);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, false);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($values));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            file_put_contents($temp, $result);

            if (file_exists($temp)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip, application/octet-stream');
                header('Content-Disposition: attachment; filename="ACN ' . $company['acn'] . ' - Company Documents.zip"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($temp));
                readfile($temp);
                unlink($temp);
                exit;
            }
        }

        //if Registration pages Endpoint download201 Than
        if (wp_scgcge_current_segment() == 'download201' && isset($_GET['token']) && preg_replace('/[^a-zA-Z0-9]+/', '', $_GET['token'])) {
            header('HTTP/1.1 200 OK');
            $code = $_GET['token'];
            $current_user = get_current_user_id();
            //$query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code' AND user_id = '$current_user'";
            $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
            $company = $wpdb->get_row($query, ARRAY_A);
            $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '$code' "; // AND user_id = '$current_user'
            $entities = $wpdb->get_results($query, ARRAY_A);
            $query = "SELECT * FROM $wpdb->prefix" . "asic_entity_shares WHERE code = '$code'"; // AND user_id = '$current_user'
            $shares = $wpdb->get_results($query, ARRAY_A);

            if ($current_user != $company['user_id'] && !current_user_can('administrator')) {
                $error = 'Not authorised.';
                wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')) . '?not-authorised');
            }

            if (empty($company) || !isset($company)) {
                $error = 'Not authorised.';
                wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')) . '?not-authorised');
            }
            $pro_name = get_user_meta($current_user, 'ecr_company_name', true) ? get_user_meta($current_user, 'ecr_company_name', true) : '';
            $pro_image = get_user_meta($current_user, 'profile_pic', true) ? wp_get_attachment_url(get_user_meta($current_user, 'profile_pic', true)) : '';

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

            $temp = time() . '.zip';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://documents.getedge.com.au/scgc/download201.php?acn=' . $company['acn']);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, false);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($values));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec($ch);
            curl_close($ch);
            file_put_contents($temp, $result);


            if (file_exists($temp)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip, application/octet-stream');
                header('Content-Disposition: attachment; filename="' . stripslashes($company['company_name_full']) . ' - Form 201.docx"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($temp));
                readfile($temp);
                unlink($temp);
                exit;
            }
        }

        if (wp_scgcge_current_segment() == 'test_xero') {
            test_xero(4298);      //491
        }
    }

    /**
     * Add To cart Product than ASIC FEE Product Add
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_add_product_to_cart($item_key, $product_id)
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $ge_asic_fee_id = !empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '';
        $ge_coy_reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';

        // Product Id of the free product which will get added to cart
        $free_product_id = $ge_asic_fee_id;
        $asic_found = false;
        $reg_found = false;

        //check if product already in cart
        if (sizeof(WC()->cart->get_cart()) > 0) {
            foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
                $_product = $values['data'];
                if ($_product->get_id() == $free_product_id && $_product->get_id() == $ge_coy_reg_fee_id) {
                    $asic_found = true;
                }
                if ($_product->get_id() == $ge_coy_reg_fee_id) {
                    $reg_found = true;
                }
            }

            // if product not found, add it
            if (!$asic_found && $_product->get_id() == $ge_coy_reg_fee_id) {
                WC()->cart->add_to_cart($free_product_id);
            }
        }
    }

    /**
     * Add a custom action to order actions on edit order page
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_add_order_meta_box_action($actions)
    {
        global $theorder;
        // add "mark printed" custom action
        $actions['wc_push_invoice_to_xero_action'] = 'Push invoice to Xero';
        return $actions;
    }

    /**
     * order Processing than fire Xero
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_process_order_meta_box_action($order)
    {
        // add the order note
        $wp_scgcge_options = get_option('wp_scgcge_options');
        if (
            $wp_scgcge_options['consumer_key'] &&
            $wp_scgcge_options['consumer_secret'] &&
            $wp_scgcge_options['public_cert_file'] &&
            $wp_scgcge_options['private_key_file'] &&
            $wp_scgcge_options['xero_account_code_asic'] &&
            $wp_scgcge_options['xero_account_code_sales_coy_reg'] &&
            $wp_scgcge_options['xero_account_code_sales'] &&
            $wp_scgcge_options['xero_asic_contact_id']
        ) {
            process_xero($order->get_id());
        }
    }

    /**
     * Get Product Price
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_woocommerce_product_get_price($price, $product)
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';
        $product_id = !empty($wp_scgcge_options['product_id']) ? $wp_scgcge_options['product_id'] : '';

        if (!is_user_logged_in()) {
            return $price;
        }

        if (is_single($product_id)) {
            $current_user = wp_get_current_user();
            if (wc_customer_bought_product($current_user->user_email, $current_user->ID, $reg_fee_id)) {
                return $price * 0;
            } else {
                return $price;
            }
        } else {
            return $price;
        }
    }

    /**
     * Custom Field Add On Edit Account Page
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_add_fields_to_edit_account_form()
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $display = (isset($wp_scgcge_options['branding']) && strtoupper($wp_scgcge_options['branding']) == 'YES') ? '' : 'style="display: none !important;"';
        $display = '';

        $user = wp_get_current_user(); ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide" <?= $display ?>>
            <label for="ecr_company_name"><?php echo __('Company Name', 'scgcge'); ?>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="ecr_company_name" id="ecr_company_name" value="<?php echo esc_attr($user->ecr_company_name); ?>" />
                <small><em><?php echo __('If you are a company and you want to brand the company documents, fill in the above field.', 'scgcge'); ?></em></small>
        </p>
        <?php
    }

    /**
     * Custom Field Add On save Account Page
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_save_fields_in_edit_account_form($user_id)
    {
        if (isset($_POST['ecr_company_name'])) {
            update_user_meta($user_id, 'ecr_company_name', sanitize_text_field($_POST['ecr_company_name']));
        }
    }

    /**
     * Custom Message Add My Account
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_filter_woocommerce_my_account_message($var)
    {
        if (isset($_GET['save'])) {
            $var = __('Please login into your account or create a new one in order to save your current application progress.', 'scgcge');
        }
        if (isset($_GET['not-authorised'])) {
            $var = __('Please login into your account to download the company documents.', 'scgcge');
        }
        return $var;
    }

    /**
     * Custom Message Add After Cart Total
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_woocommerce_after_cart_totals()
    {
        echo '<div style="text-align: center; margin-top: 10px;">' . __('Placing the order will lock the application.', 'scgcge') . '<br /><a href="' . wp_scgcge_action_url() . '/general-details/?token=' . wp_scgcge_getSession() . '">' . __('Do you want to review the application once again?', 'scgcge') . '</a></div>';
    }

    /**
     * Custom Endpoint Content in Dashboard
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_companies_dashboard_menu_endpoint_companies_content()
    {
        global $wpdb;

        $current_user = wp_get_current_user();
        $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE user_id = '$current_user->ID' AND acn IS NOT NULL ORDER BY id DESC";
        $records = $wpdb->get_results($query, ARRAY_A);

        echo '<h2 style="margin-top: 0px;">' . __('Companies list', 'scgcge') . '</h2>';

        if (!empty($records)) {
            echo '<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table"><thead><tr><th style="text-align: left;">' . __('Company Details', 'scgcge') . '</th><th style="text-align: right; white-space: nowrap;">' . __('Downloads', 'scgcge') . '</th></tr></thead><tbody>';
            foreach ($records as $record) {
                $reg_date = date('d/m/Y', strtotime($record['reg_date']));
                $acn = chunk_split($record['acn'], 3, ' ');
                $registration_details = 'ACN ' . $acn . ' / ' . 'Registered on ' . $reg_date; ?>

                <tr>
                    <td><?= stripslashes_deep($record['company_name_full']) ?><small><em><br /><?= $registration_details ?></em></small></td>
                    <td style="text-align: right;">
                        <a href="<?php echo home_url() . '/downloadCert?token=' . $record['code']; ?>"><i class="fa fa-download"></i> Certificate</a><br />
                        <a href="<?php echo home_url() . '/downloadDocs?token=' . $record['code']; ?>"><i class="fa fa-download"></i> Documents</a><br />
                    </td>
                </tr>

            <?php
            }
            echo '</tbody></table>';
        } else {
            echo '<p>' . __('You have no company registered with us.', 'scgcge') . '</p>';
        }
    }

    /**
     * Custom Endpoint Application Content in Dashboard
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_companies_dashboard_menu_endpoint_applications_content()
    {
        global $wpdb;
        $current_user = wp_get_current_user();
        $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE user_id = '$current_user->ID' AND acn IS NULL ORDER BY id DESC";
        $records = $wpdb->get_results($query, ARRAY_A);

        echo '<h2 style="margin-top: 0px;">Open applications list</h2>';

        if (!empty($records)) {
            echo '<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table"><thead><tr><th style="text-align: left;">' . __('Company Name', 'scgcge') . '</th><th style="text-align: center; white-space: nowrap;">' . __('Status', 'scgcge') . '</th><th style="text-align: right; white-space: nowrap;">' . __('Actions', 'scgcge') . '</th></tr></thead><tbody>';
            foreach ($records as $record) {
                $reg_date = date('d/m/Y', strtotime($record['reg_date']));
                $acn = chunk_split($record['acn'], 3, ' ');
                $registration_details = 'ACN' . $acn . ' / ' . __('Registered on', 'scgcge') . ' ' . $reg_date; ?>

                <tr>
                    <td><?= stripslashes_deep($record['company_name_full']) ?></td>
                    <td style="text-align: center;">
                        <?php if (isset($record['status'])) {
                    if ($record['status'] == 'new') {
                        echo __('Submitted to ASIC', 'scgcge');
                    } else {
                        echo ucwords($record['status']);
                    }
                } else {
                    echo __('Open', 'scgcge');
                } ?>
                    </td>
                    <td style="text-align: right;">
                        <?php if ($record['status'] == 'validation failed' || $record['status'] == 'rejected') {
                    echo '<a href="' . wp_scgcge_action_url() . '/general-details/?token=' . $record['code'] . '"><i class="fa fa-edit"></i> ' . __('Amend application', 'scgcge') . '</a>';
                } elseif ($record['status'] == '') {
                    echo '<a href="' . wp_scgcge_action_url() . '/general-details/?token=' . $record['code'] . '"><i class="fa fa-edit"></i> ' . __('Continue application', 'scgcge') . '</a>';
                } else {
                    echo __('No action required', 'scgcge');
                } ?>
                    </td>
                </tr>

<?php
            }
            echo '</tbody></table>';
        } else {
            echo '<p>' . __('You have no open company registration applications.', 'scgcge') . ' </p>';
        }
    }

    /**
     * Custom Endpoint Add in Dashboard
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_companies_dashboard_menu_endpoint()
    {
        add_rewrite_endpoint('list-companies', EP_PAGES);
        add_rewrite_endpoint('list-applications', EP_PAGES);
    }

    /**
     * Custom Companies Endpoint in Dashboard
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_companies_dashboard_menu($menu_links)
    {
        $menu_links = array_slice($menu_links, 0, 1, true)
            + ['list-applications' => 'Open Applications']
            + ['list-companies' => 'Companies']
            + array_slice($menu_links, 1, null, true);

        return $menu_links;
    }

    /**
     * Update the order meta with field value
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_add_order_meta($order_id, $posted)
    {
        global $wordpress, $woocommerce, $wpdb;

        $order = new \WC_Order($order_id);
        $user_id = $order->get_user_id();

        $session_id = wp_scgcge_getSession();
        update_post_meta($order_id, 'company_registration_id', $session_id);
        // $company_registration_id = get_metadata('post', $order_id, 'company_registration_id', true);

        $wpdb->query("UPDATE $wpdb->prefix" . "asic_companies SET `user_id`='" . $user_id . "', `order_id`='" . $order_id . "', `status`='new' WHERE code='" . $session_id . "'");

        // wp_scgcge_destroySession($session_id);
    }

    /**
     * Order Status Processing Than
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_woocommerce_order_status_processing($order_id, $amend = false)
    {
        global $wpdb;

        $wp_scgcge_options = get_option('wp_scgcge_options');
        $reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';
        $test_transmission = !empty($wp_scgcge_options['test_transmission']) ? $wp_scgcge_options['test_transmission'] : '';
        $xero_account_code_sales = !empty($wp_scgcge_options['xero_account_code_sales']) ? $wp_scgcge_options['xero_account_code_sales'] : '';



        $company_registration_id = get_metadata('post', $order_id, 'company_registration_id', true);
        $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'status' => 'placed',
        ], ['code' => $company_registration_id]);


        /*
                $order = wc_get_order($order_id);
                foreach ($order->get_items() as $item) {
                    $item_id = $item->get_id();
                    $product_id = $item->get_product_id();
                    if ($product_id == $reg_fee_id) {
                        $company_registration_id = get_metadata('post', $order_id, 'company_registration_id', true);
                        $array = $this->prepare_ge_array($company_registration_id, false, $test_transmission);


                        if (!isset($array) || !is_array($array) || empty($array)) {
                            $text = '<p>' . __('Hi', 'scgcge') . ' ' . get_option('woocommerce_email_from_name') . ',</p>';
                            $text .= '<p>' . __('There was data processing error. ', 'scgcge') . '</p>';
                            $text .= '<p>' . __('Please manually lodge the application by changing the order status to processing. If the problem persists, please contact GetEDGE support team.', 'scgcge') . '</p>';
                            $text .= '<p>' . __('Kind regards,', 'scgcge') . '<br />' . __('GetEDGE Team', 'scgcge') . '</p>';
                            $this->sendEmail(get_option('woocommerce_email_from_address'), 'GetEDGE Error', 'GetEDGE Error', $text);
                            if (get_option('wp_scgcge_options')['slack_webhook_url']) {
                                wp_scgcge_toSlack('There was an error processing Order #' . $order_id . ' - Data processing');
                            }
                            return false;
                        }

                        $lodge201 = lodge201($company_registration_id, $array, $order_id);
                        if (!$lodge201) {
                            $order->update_status('on-hold', 'There was an error processing your request.'); // order note is optional, if you want to  add a note to order
                            return false;
                        }
                        $query = "SELECT company_name_full FROM $wpdb->prefix" . "asic_companies WHERE order_id = '" . $order_id . "'";
                        $company_name_full = stripslashes_deep($wpdb->get_var($query));

                        $subject = $company_name_full . ' ' . __('has been lodged with ASIC', 'scgcge');
                        $title = $company_name_full . ' ' . __('has been lodged with ASIC', 'scgcge');
                        $text = '<p>' . __('Dear', 'scgcge') . ' ' . ucfirst($order->get_billing_first_name()) . ',<p>';
                        $text .= '<p>' . ('We are pleased to confirm that your application for ') . $company_name_full . ' ' . __('has been lodged with ASIC', 'scgcge') . ' (Ref# ' . $order_id . ' / ' . $lodge201 . ').<p>';
                        $text .= '<p>' . __('In most cases, submitted orders will be processed by ASIC within minutes however, please note that unexpected delays may occur if:', 'scgcge') . '</p>';
                        $text .= '<ul><li>' . ('A manual review is initiated by ASIC for reasons such as an unusual name containing non-dictionary words') . '</li><li>' . __('The ASIC system is offline for maintenance or inaccessible for any reason', 'scgcge') . '</li></ul>';
                        $text .= '<p>' . __('You may review the status of your order at anytime by logging in to your', 'scgcge') . ' <a href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) . '/">' . __('client area', 'scgcge') . '</a>.' . __('Information on how to access your client area has been sent to you in a previous email.', 'scgcge') . '</p>';
                        $text .= '<p>' . __('We appreciate and value your comments, suggestions and general feedback as this helps us to further develop our systems for an ever-improving customer experience. Please write to', 'scgcge') . ' ' . get_option('woocommerce_email_from_address') . ' ' . __('with anything that you would like us to know.', 'scgcge') . '</p>';
                        $text .= '<p>' . __('Thank you for your business.', 'scgcge') . ' </p>';
                        $text .= '<p>' . __('Sincerely,', 'scgcge') . '<br>' . get_option('woocommerce_email_from_name') . ' </p>';

                        $reference = $company_name_full;

                        $this->sendEmail($order->get_billing_email(), $subject, $title, $text);
                    }
                }
        */
        
        
        
        
        
        wp_scgcge_destroySession($company_registration_id);

        $wp_scgcge_options = get_option('wp_scgcge_options');
        if (
            $wp_scgcge_options['consumer_key'] &&
            $wp_scgcge_options['consumer_secret'] &&
            $wp_scgcge_options['public_cert_file'] &&
            $wp_scgcge_options['private_key_file'] &&
            $wp_scgcge_options['xero_account_code_asic'] &&
            $wp_scgcge_options['xero_account_code_sales_coy_reg'] &&
            $wp_scgcge_options['xero_account_code_sales'] &&
            $wp_scgcge_options['xero_asic_contact_id'] &&
            isset($array) && is_array($array)
        ) {
            process_xero($order_id);
        }
    }

    /**
     * Add To Cart Redirect
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_add_to_cart_redirect($url)
    {
        $url = wc_get_checkout_url(); // since WC 2.5.0
        return $url;
    }

    /**
     * Woocommerce Template Override
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_woocommerce_locate_template($template, $template_name, $template_path)
    {
        $path = WP_SCGC_GE_DIR . '/' . $template_path . $template_name;

        return file_exists($path) ? $path : $template;
    }

    /**
     * Woocommerce Template part
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_wc_get_template_part($template, $slug, $name)
    {
        // look in plugin/woocommerce/slug-name.php or plugin/woocommerce/slug.php
        if ($name) {
            $path = WP_SCGC_GE_DIR . '/' . WC()->template_path() . "{$slug}-{$name}.php";
        } else {
            $path = WP_SCGC_GE_DIR . '/' . WC()->template_path() . "{$slug}.php";
        }
        return file_exists($path) ? $path : $template;
    }

    /**
     * Remove Cart Product Link
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_remove_cart_product_link($product_link, $cart_item, $cart_item_key)
    {
        $product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        return $product->get_title();
    }

    /**
     * remove cart item to remove button
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_cart_item_remove_link($button_link, $cart_item_key)
    {
        //SET HERE your specific products IDs
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $ge_asic_fee_id = !empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '';
        $targeted_products_ids = [$ge_asic_fee_id];

        // Get the current cart item
        $cart_item = WC()->cart->get_cart()[$cart_item_key];

        // If the targeted product is in cart we remove the button link
        if (in_array($cart_item['data']->get_id(), $targeted_products_ids)) {
            $button_link = '';
        }

        return $button_link;
    }

    /**
     * Remove Permalink Order Table
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_remove_permalink_order_table($name, $item, $is_visible)
    {
        $name = $item['name'];
        return $name;
    }

    /**
     * order details - get Company Name by order detail page
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_filter_order_detail_name($html, $item, $args)
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';
        if ($item->get_product_id() == $reg_fee_id) {
            global $wpdb;

            $query = "SELECT company_name_full FROM $wpdb->prefix" . "asic_companies WHERE order_id = '" . $item->get_order_id() . "'";
            $company_name_full = stripslashes_deep($wpdb->get_var($query));
            $html = '<ul class="wc-item-meta"><li> ' . $company_name_full . '</li>';
            $html .= '</ul>';
        }
        return $html;
    }

    /**
     * after Order get edit Oder Page in Company Name
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_woocommerce_after_order_itemmeta($item_id, $item, $null)
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';

        $item_decoded = json_decode($item);
        $product_id = isset($item_decoded->product_id) ? $item_decoded->product_id : 0;

        if ($product_id == $reg_fee_id) { // admin order
            global $wpdb;
            $query = "SELECT company_name_full FROM $wpdb->prefix" . "asic_companies WHERE order_id = '" . json_decode($item)->order_id . "'";
            $company_name_full = stripslashes_deep($wpdb->get_var($query));
            echo $company_name_full;
        }
    }

    /**
     * Company Full name
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_company_item_data($company_data, $cart_item)
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';
        $ge_asic_fee_id = !empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '';

        if ($cart_item['product_id'] == $reg_fee_id) { // cart
            global $wpdb;
            $query = "SELECT company_name_full FROM $wpdb->prefix" . "asic_companies WHERE code = '" . wp_scgcge_getSession() . "'";
            $company_name_full = stripslashes_deep($wpdb->get_var($query));
            unset($company_data);
            $company_data[] =
                [
                    'key' => 'Name',
                    'value' => $company_name_full,
                ];
        }
        if ($cart_item['product_id'] == $ge_asic_fee_id) {
            unset($company_data);
            $company_data = [];
        }
        return $company_data;
    }

    /**
     * Change WooCommerce Add to Cart Text
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_woo_custom_single_add_to_cart_text($var)
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';

        if (get_the_ID() == $reg_fee_id) {
            return __('Proceed to Checkout', 'scgcge');
        } else {
            return $var;
        }
    }

    /**
     * Customize Account menu.
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_woocommerce_account_menu_items($items)
    {
        //unset($items['payment-methods']);
        $items['downloads'] = __('Downloadable Products', 'scgcge');
        return $items;
    }

    /**
     * Send Email After Processing.
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function sendEmail($email, $subject, $title, $text)
    {
        //global $woocommerce;
        $mailer = \WC()->mailer();

        ob_start();
        wc_get_template('emails/customer-email.php', [
            'email' => $email,
            'email_heading' => $title,
            'customer_message' => $text,
            'plain_text' => false
        ]);
        $message = ob_get_clean();
        $headers[] = 'From:' . get_option('woocommerce_email_from_name') . ' <' . get_option('woocommerce_email_from_address') . '>';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $send = $mailer->send($email, $subject, $message, $headers);
    }

    /**
     * Send Email Styles
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_woocommerce_email_styles($css)
    {
        $css .= 'h1 { font-size: 22px !important; } #template_footer { margin-top: 30px !important; }';
        return $css;
    }

    /**
     * Prepare GetEDGE
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function prepare_ge_array($form_main_id, $amend = false, $testTransmission = 'false')
    {
        global $wpdb;

        $wp_scgcge_options = get_option('wp_scgcge_options');

        $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$form_main_id'";
        $company = $wpdb->get_row($query, ARRAY_A);
        extract($company, EXTR_PREFIX_SAME, 'scgcge');

        if (!isset($company_name_full) || $company_name_full == '') {
            $text = '<p>' . __('Hi', 'scgcge') . ' ' . get_option('woocommerce_email_from_name') . ',</p>';
            $text .= '<p>' . __('There was core data processing error. ', 'scgcge') . '</p>';
            $text .= '<p>' . __('Please manually lodge the application by changing the order status to processing. If the problem persists, please contact GetEDGE support team.', 'scgcge') . '</p>';
            $text .= '<p>' . __('Kind regards,', 'scgcge') . '<br />' . __('GetEDGE Team', 'scgcge') . '</p>';
            $this->sendEmail(get_option('woocommerce_email_from_address'), 'GetEDGE Error', 'GetEDGE Error', $text);
            if (get_option('wp_scgcge_options')['slack_webhook_url']) {
                wp_scgcge_toSlack('There was an error processing Order #' . $order_id . ' - Data processing');
            }
            return false;
        }

        $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '$form_main_id'";
        $entities = $wpdb->get_results($query, ARRAY_A);

        $query = "SELECT * FROM $wpdb->prefix" . "asic_entity_shares WHERE code = '$form_main_id'";
        $shares = $wpdb->get_results($query, ARRAY_A);

        $name_identical = '';
        $business = [];
        $abn = '';
        // company_id + business

        if ($search_result == 'CHECKBN') {
            if ($bn_when == 'AFTER') {
                $name_identical = 'Y';
                $abn = $bn_abn;
                $business = [];
            } elseif ($bn_when == 'BEFORE') {
                $name_identical = 'Y';
                $abn = '';
                $business[] = [
                    'place_registration' => $bn_state,
                    'registration_number' => $bn_number
                ];
            }
        } else {
            $abn = '';
            $name_identical = 'N';
            $business = [];
        }

        // principal_place
        $principal_place = [
            'care_of' => $ppb_care,
            'line2' => $ppb_line2,
            'street' => $ppb_street,
            'locality' => $ppb_suburb,
            'state' => $ppb_state,
            'postcode' => $ppb_postcode,
            'country' => ''
        ];

        // ultimate_holding
        if ($holding_company == 'YES') {
            $ultimate_holding = [
                'name' => $holding_name,
                'acn' => $holding_acn,
                'place_incorporation' => strtoupper(wp_scgcge_get_country_by_code($holding_country)),
                'abn' => $holding_abn
            ];
        } else {
            $ultimate_holding = [];
        }

        // officers
        $officers = [];
        foreach ($entities as $entity) {
            if ($entity['entity_type'] == 'IND') {
                $roles = [];
                $roles[] = $entity['entity_role_dir'] == 'Y' ? 'DIR' : '';
                $roles[] = $entity['entity_role_sec'] == 'Y' ? 'SEC' : '';
                $roles = array_filter($roles);
                if (!empty($roles)) {
                    $officers[] = [
                        'name' => [
                            'family_name' => $entity['entity_last_name'],
                            'given_name1' => $entity['entity_first_name'],
                            'given_name2' => $entity['entity_middle_name1'],
                            'given_name3' => $entity['entity_middle_name2']
                        ],
                        'birth_details' => [
                            'date' => date('Ymd', strtotime($entity['entity_birth_date'])),
                            'locality' => $entity['entity_birth_suburb'],
                            'locality_qualifier' => $entity['entity_birth_country'] == 'AU' ? $entity['entity_birth_state'] : strtoupper(wp_scgcge_get_country_by_code($entity['entity_birth_country']))
                        ],
                        'address' => [
                            'care_of' => $entity['address_care'],
                            'line2' => $entity['address_line2'],
                            'street' => $entity['address_street'],
                            'locality' => $entity['address_suburb'],
                            'state' => $entity['address_country'] == 'AU' ? $entity['address_state'] : '',
                            'postcode' => $entity['address_country'] == 'AU' ? $entity['address_postcode'] : '',
                            'country' => $entity['address_country'] == 'AU' ? '' : strtoupper(wp_scgcge_get_country_by_code($entity['address_country']))
                        ],
                        'address_overridden' => 'N',
                        'former_name' => [
                            'family_name' => $entity['entity_former_last_name'],
                            'given_name1' => $entity['entity_former_first_name'],
                            'given_name2' => $entity['entity_former_middle_name1'],
                            'given_name3' => $entity['entity_former_middle_name2']
                        ],
                        'offices' => $roles
                    ];
                }
            }
        }

        // members
        $members = [];
        $the_shares_array = [];
        foreach ($shares as $share) {
            $shareholder_query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '$form_main_id' and id ='" . $share['entity_id'] . "'";
            $shareholder_results = $wpdb->get_results($shareholder_query, ARRAY_A);

            foreach ($shareholder_results as $shareholder) {
                if ($shareholder['entity_type'] == 'IND') {
                    $share_holder = [
                        [
                            'member_name_person' => [
                                'family_name' => $shareholder['entity_last_name'],
                                'given_name1' => $shareholder['entity_first_name'],
                                'given_name2' => $shareholder['entity_middle_name1'],
                                'given_name3' => $shareholder['entity_middle_name2']
                            ],
                            'member_name_organisation' => '',
                            'member_acn_organisation' => '',
                            'member_address' => [
                                'care_of' => $shareholder['address_care'],
                                'line2' => $shareholder['address_line2'],
                                'street' => $shareholder['address_street'],
                                'locality' => $shareholder['address_suburb'],
                                'state' => $shareholder['address_country'] == 'AU' ? $shareholder['address_state'] : '',
                                'postcode' => $shareholder['address_country'] == 'AU' ? $shareholder['address_postcode'] : '',
                                'country' => $shareholder['address_country'] == 'AU' ? '' : strtoupper(wp_scgcge_get_country_by_code($shareholder['address_country']))
                            ],
                            'member_has_acn' => '',
                            'address_overridden' => 'N'
                        ]
                    ];
                } else {
                    $share_holder = [
                        [
                            'member_name_person' => [
                                'family_name' => '',
                                'given_name1' => '',
                                'given_name2' => '',
                                'given_name3' => ''
                            ],
                            'member_name_organisation' => $shareholder['entity_company_name'],
                            'member_acn_organisation' => $shareholder['entity_company_acn'],
                            'member_address' => [
                                'care_of' => $shareholder['address_care'],
                                'line2' => $shareholder['address_line2'],
                                'street' => $shareholder['address_street'],
                                'locality' => $shareholder['address_suburb'],
                                'state' => $shareholder['address_country'] == 'AU' ? $shareholder['address_state'] : '',
                                'postcode' => $shareholder['address_country'] == 'AU' ? $shareholder['address_postcode'] : '',
                                'country' => $shareholder['address_country'] == 'AU' ? '' : strtoupper(wp_scgcge_get_country_by_code($shareholder['address_country']))
                            ],
                            'member_has_acn' => $shareholder['address_country'] == 'AU' ? 'Y' : 'N',
                            'address_overridden' => 'N'
                        ]
                    ];
                }
            }

            $members[] = [
                'share_class' => $share['share_class'],
                'number' => $share['share_number'],
                'shares_fully_paid' => ($share['share_unpaid_total'] == 0) ? 'Y' : 'N',
                'beneficial_owner' => $share['share_beneficial'] == 'Y' ? 'Y' : 'N',
                'beneficial_owner_name' => $share['share_beneficial'] == 'Y' ? '' : $share['share_beneficiary'],
                'total_paid' => floatval($share['share_paid_total']) * 100,
                'total_unpaid' => floatval($share['share_unpaid_total']) * 100,
                'amount_paid_per_share' => floatval($share['share_paid']) * 100,
                'amount_due_per_share' => floatval($share['share_unpaid']) * 100,
                'holding_owners' => $share_holder,
            ];

            $the_shares_array[] = [
                'share_class' => $share['share_class'],
                'number' => $share['share_number'],
                'total_paid' => floatval($share['share_paid_total']) * 100,
                'total_unpaid' => floatval($share['share_unpaid_total']) * 100,
            ];
        }

        // share_class
        $final_structure = [];
        $share_types_full = ['ORD' => __('Ordinary', 'scgcge'), 'A' => __('Class A', 'scgcge'), 'B' => __('Class B', 'scgcge'), 'C' => __('Class C', 'scgcge'), 'D' => __('Class D', 'scgcge'), 'E' => __('Class E', 'scgcge'), 'F' => __('Class F', 'scgcge'), 'G' => __('Class G', 'scgcge'), 'H' => __('Class H', 'scgcge'), 'I' => __('Class I', 'scgcge'), 'J' => __('Class J', 'scgcge'), 'K' => __('Class K', 'scgcge'), 'MAN' => __('Management', 'scgcge'), 'LG' => __('Life Governors', 'scgcge'), 'EMP' => __('Employees', 'scgcge'), 'FOU' => __('Founders', 'scgcge'), 'PRF' => __('Preference', 'scgcge'), 'CUMP' => __('Cumulative Preference', 'scgcge'), 'NCP' => __('Non Cumulative Preference', 'scgcge'), 'NCRP' => __('Non Cumulative Redeemable Preference', 'scgcge'), 'PARP' => __('Participative Preference', 'scgcge'), 'RED' => __('Redeemable', 'scgcge'), 'INI' => __('Initial', 'scgcge'), 'SPE' => __('Special', 'scgcge')];
        $share_types = ['ORD', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'MAN', 'LG', 'EMP', 'FOU', 'PRF', 'CUMP', 'NCP', 'NCRP', 'PARP', 'RED', 'INI', 'SPE'];
        $the_structure = [
            'ORD' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'A' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'B' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'C' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'D' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'E' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'F' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'G' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'H' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'I' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'J' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'K' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'MAN' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'LG' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'EMP' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'FOU' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'PRF' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'CUMP' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'NCP' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'NCRP' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'PARP' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'RED' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'INI' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0],
            'SPE' => ['number' => 0, 'total_paid' => 0, 'total_unpaid' => 0]
        ];

        foreach ($the_shares_array as $the_shares_subarray) {
            foreach ($the_shares_subarray as $key => $value) {
                if ($key == 'share_class' && in_array($value, $share_types)) {
                    $the_structure[$value]['number'] = (($the_structure[$value]['number'] ? $the_structure[$value]['number'] : 0) + $the_shares_subarray['number']);
                    $the_structure[$value]['total_paid'] = (($the_structure[$value]['total_paid'] ? $the_structure[$value]['total_paid'] : 0) + $the_shares_subarray['total_paid']);
                    $the_structure[$value]['total_unpaid'] = (($the_structure[$value]['total_unpaid'] ? $the_structure[$value]['total_unpaid'] : 0) + $the_shares_subarray['total_unpaid']);
                }
            }
        }

        foreach ($the_structure as $key => $value) {
            if ($value['number'] > 0) {
                $final_structure[] = [
                    'code' => $key,
                    'title' => strtoupper($share_types_full[$key]),
                    'total_number_issued' => $value['number'],
                    'total_amount_paid' => $value['total_paid'],
                    'total_amount_unpaid' => $value['total_unpaid']
                ];
            }
        }

        // applicant
        if ($applicant != '0') {
            $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE id = '$applicant'";
            $entity = $wpdb->get_row($query, ARRAY_A);
            $applicant = [
                'name_person' => [
                    'family_name' => $entity['entity_last_name'],
                    'given_name1' => $entity['entity_first_name'],
                    'given_name2' => $entity['entity_middle_name1'],
                    'given_name3' => ''
                ],
                'name_organisation' => '',
                'acn_organisation' => '',
                'address' => [
                    'care_of' => $entity['address_care'],
                    'line2' => $entity['address_line2'],
                    'street' => $entity['address_street'],
                    'locality' => $entity['address_suburb'],
                    'state' => $entity['address_country'] == 'AU' ? $entity['address_state'] : '',
                    'postcode' => $entity['address_country'] == 'AU' ? $entity['address_postcode'] : '',
                    'country' => ($entity['address_country'] == 'AU' || $entity['address_country'] == '') ? '' : strtoupper(wp_scgcge_get_country_by_code($entity['address_country']))
                ],
                'signatory_name' => [
                    'family_name' => $entity['entity_last_name'],
                    'given_name1' => $entity['entity_first_name'],
                    'given_name2' => $entity['entity_middle_name1'],
                    'given_name3' => $entity['entity_middle_name2']
                ],
                'signatory_role' => '',
                'date_signed' => date('Ymd'),
                'confirm_assented_to' => 'Y'
            ];
        } else {
            $applicant = [
                'name_person' => [
                    'family_name' => $applicant_last_name,
                    'given_name1' => $applicant_first_name,
                    'given_name2' => isset($applicant_middle_name) ? $applicant_middle_name : '',
                    'given_name3' => ''
                ],
                'name_organisation' => '',
                'acn_organisation' => '',
                'address' => [
                    'care_of' => '',
                    'line2' => isset($applicant_line2) ? $applicant_line2 : '',
                    'street' => $applicant_street,
                    'locality' => $applicant_suburb,
                    'state' => $applicant_state,
                    'postcode' => $applicant_postcode,
                    'country' => ''
                ],
                'signatory_name' => [
                    'family_name' => $applicant_last_name,
                    'given_name1' => $applicant_first_name,
                    'given_name2' => isset($applicant_middle_name) ? $applicant_middle_name : '',
                    'given_name3' => ''
                ],
                'signatory_role' => '',
                'date_signed' => date('Ymd'),
                'confirm_assented_to' => 'Y'
            ];
        }

        $form201 = [
            'identifier' => time(),
            'company_id' => [
                'company_name' => $search_result == 'ACNONLY' ? '' : $company_name_full,
                'company_type' => 'APTY',
                'company_class' => 'LMSH',
                'company_subclass' => $company_subclass,
                'acn_yesno' => $search_result == 'ACNONLY' ? 'Y' : 'N',
                'acn_legal' => $search_result == 'ACNONLY' ? $legal_elements : '',
                'governed_constitution' => '',
                'shares_non_cash' => '',
                'reserved_410' => 'N',
                'name_identical' => $name_identical,
                'jurisdiction' => $jurisdiction,
                'residential_officeholder' => 'Y',
                'abn' => $abn,
                'crowd_source' => 'N'
            ], // companyId
            'reservation' => '',
            'business' => $business, // business
            'members_amount' => '',
            'registered_office' => [
                'address' => [
                    'care_of' => $ro_care,
                    'line2' => $ro_line2,
                    'street' => $ro_street,
                    'locality' => $ro_suburb,
                    'state' => $ro_state,
                    'postcode' => $ro_postcode,
                    'country' => ''
                ],
                'occupy' => $ro_occupy == 'YES' ? 'Y' : 'N',
                'occupier_name' => $ro_occupy == 'YES' ? '' : $ro_occupier,
                'occupant_consent' => $ro_occupy == 'YES' ? '' : 'Y',
                'address_overridden' => 'N'
            ], // registeredOffice
            'standard_hours' => '',
            'office_hours' => [],
            'principal_place' => [
                'address_overridden' => 'N',
                'address' => $principal_place
            ], // principalPlace
            'ultimate_holding' => $ultimate_holding, // ultimateHolding
            'officers' => $officers,
            'share_class' => $final_structure,
            'members' => $members,
            'nonshare_members' => [],
            'applicant' => $applicant,
            'admin' => [
                'request_manual_review' => '',
                'has_asic_consent_for_name' => '',
                'certificate_delivery_option' => 'PDF',
                'text_manual_review' => ''
            ], // admin
            'test_transmission' => $wp_scgcge_options['test_transmission'],
            'token' => $wp_scgcge_options['ge_api_key'],
            'amend' => $amend ? $edge_id : '',
            'order' => $company['order_id'],
            'company_full_name' => $company['company_name_full'],
            'document' => $company['document'],
            'self_signed' => 1
        ];
        write_log('Form 201: ' . json_encode($form201));

        return stripslashes_deep($form201);
    }

    /**
     * Remove Quantity field for ASIC product
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_remove_quantity_field($return, $product)
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $ge_asic_fee_id = !empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '';
        $productID = $product->get_id();
        if ($productID == $ge_asic_fee_id) {
            $return = true;
        }
        return $return;
    }

    public function wp_scgcge_remove_product_from_cart($removed_cart_item_key, $cart)
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');

        $ge_asic_fee_id = !empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '';
        $ge_coy_reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';

        $line_item = $cart->removed_cart_contents[$removed_cart_item_key];
        $product_id = $line_item['product_id'];

        if ($product_id == $ge_coy_reg_fee_id) {
            $cartId = WC()->cart->generate_cart_id($ge_asic_fee_id);
            $cartItemKey = WC()->cart->find_product_in_cart($cartId);
            WC()->cart->remove_cart_item($cartItemKey);
        }
    }

    /**
     * Add "ASIC Fee" product to cart if "Company Registartion" product exists in cart
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function wp_scgcge_handle_undo_cart_item()
    {
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $ge_asic_fee_id = !empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '';
        $ge_coy_reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';

        $asic_found = false;
        $reg_found = false;

        if (sizeof(WC()->cart->get_cart()) > 0) {
            foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
                $_product = $values['data'];
                if ($_product->get_id() == $ge_asic_fee_id && $_product->get_id() == $ge_coy_reg_fee_id) {
                    $asic_found = true;
                }
                if ($_product->get_id() == $ge_coy_reg_fee_id) {
                    $reg_found = true;
                }
            }

            // if product not found, add it
            if (!$asic_found && $_product->get_id() == $ge_coy_reg_fee_id) {
                WC()->cart->add_to_cart($ge_asic_fee_id);
            }
        }
    }

    public function wp_scgcge_share_entity_delete_action()
    {
        global $wpdb;

        if (isset($_POST['action']) && $_POST['action'] == 'share_entity_delete_action') {
            $shareID = $_POST['share_id'];
            $result = $wpdb->delete($wpdb->prefix . 'asic_entity_shares', ['id' => $shareID]);
        }
        exit;
    }

    /**
     * Adding Hooks
     *
     * @package SCGC GetEDGE API
     * @since 1.0.0
     */
    public function add_hooks()
    {
        add_action('wp_ajax_share_entity_delete_action', [$this, 'wp_scgcge_share_entity_delete_action']);

        add_action('wp_ajax_nopriv_share_entity_delete_action', [$this, 'wp_scgcge_share_entity_delete_action']);

        // action to register custom endpoint for company registration form
        add_action('init', [$this, 'wp_scgcge_custom_rewrite']);

        // add action to flush rewrite rules.
        add_action('wp_loaded', [$this, 'wp_scgcge_custom_flush_rewrite_rules']);

        // Template Redirect
        add_action('template_redirect', [$this, 'wp_scgcge_cron_template_redirect']);

        //order status Processing
        add_action('woocommerce_order_status_processing', [$this, 'wp_scgcge_woocommerce_order_status_processing'], 10, 1);

        // add To cart Redirect
        add_filter('woocommerce_add_to_cart_redirect', [$this, 'wp_scgcge_add_to_cart_redirect']);

        // Woocommerce Tempalte Override
        add_filter('woocommerce_locate_template', [$this, 'wp_scgcge_woocommerce_locate_template'], 10, 3);

        // Woocommerce Tempalte part
        add_filter('wc_get_template_part', [$this, 'wp_scgcge_wc_get_template_part'], 10, 3);

        // Remove Cart Product Link
        add_filter('woocommerce_cart_item_name', [$this, 'wp_scgcge_remove_cart_product_link'], 10, 3);

        // Remove Permalink Order Table
        add_filter('woocommerce_order_item_name', [$this, 'wp_scgcge_remove_permalink_order_table'], 10, 3);

        // Dispaly Oder Detail in Company Name
        add_filter('woocommerce_display_item_meta', [$this, 'wp_scgcge_filter_order_detail_name'], 10, 3);

        // after order process Dispaly Edit Oder in Company Name
        add_filter('woocommerce_after_order_itemmeta', [$this, 'wp_scgcge_woocommerce_after_order_itemmeta'], 10, 3);

        // Get Company Data
        add_filter('woocommerce_get_item_data', [$this, 'wp_scgcge_company_item_data'], 99, 2);

        // Change WooCommerce Add to Cart Text
        add_filter('woocommerce_product_single_add_to_cart_text', [$this, 'wp_scgcge_woo_custom_single_add_to_cart_text']);

        // Insert the new endpoint into the My Account menu.
        add_filter('woocommerce_account_menu_items', [$this, 'wp_scgcge_woocommerce_account_menu_items']);

        // Update the order meta with field value
        add_action('woocommerce_checkout_update_order_meta', [$this, 'wp_scgcge_add_order_meta'], 10, 2);

        // Remove the "Additional Info" order notes
        add_filter('woocommerce_enable_order_notes_field', '__return_false');

        // Customize Account Messsage
        add_filter('woocommerce_my_account_message', [$this, 'wp_scgcge_filter_woocommerce_my_account_message'], 10, 1);

        // After cart Dispaly Message
        add_action('woocommerce_after_cart_totals', [$this, 'wp_scgcge_woocommerce_after_cart_totals'], 10, 2);

        // order Submit After Dispaly Message
        add_action('woocommerce_review_order_after_submit', [$this, 'wp_scgcge_woocommerce_after_cart_totals'], 10, 0);

        // Add Custom Endpoints on my Account
        add_filter('woocommerce_account_menu_items', [$this, 'wp_scgcge_companies_dashboard_menu']);

        // Add Custom rewrite Endpoints on my Account
        add_action('init', [$this, 'wp_scgcge_companies_dashboard_menu_endpoint']);

        // Add List Companies Content
        add_action('woocommerce_account_list-companies_endpoint', [$this, 'wp_scgcge_companies_dashboard_menu_endpoint_companies_content']);

        // Add List Applications Content
        add_action('woocommerce_account_list-applications_endpoint', [$this, 'wp_scgcge_companies_dashboard_menu_endpoint_applications_content']);

        // Custom Field Add On Edit Account Page
        add_action('woocommerce_edit_account_form', [$this, 'wp_scgcge_add_fields_to_edit_account_form']);

        // Custom Field Add On save Account Page
        add_action('woocommerce_save_account_details', [$this, 'wp_scgcge_save_fields_in_edit_account_form']);

        // Get Product Price
        add_filter('woocommerce_product_get_price', [$this, 'wp_scgcge_woocommerce_product_get_price'], 10, 2);

        // Email Styles
        add_filter('woocommerce_email_styles', [$this, 'wp_scgcge_woocommerce_email_styles']);

        //reg Product Add To Cart Than Asic Product add
        add_action('woocommerce_add_to_cart', [$this, 'wp_scgcge_add_product_to_cart'], 10, 2);

        // Remove ASIC Company Quentity
        add_filter('woocommerce_is_sold_individually', [$this, 'wp_scgcge_remove_quantity_field'], 10, 2);

        // add filter to remove "Delete" link for asic product
        add_filter('woocommerce_cart_item_remove_link', [$this, 'wp_scgcge_cart_item_remove_link'], 20, 2);

        // add action to remove asic fee when main product is removed
        add_action('woocommerce_cart_item_removed', [$this, 'wp_scgcge_remove_product_from_cart'], 10, 2);

        // action to add "Asic Fee" product to cart if "Company Registration" product is present in cart
        add_action('woocommerce_cart_contents', [$this, 'wp_scgcge_handle_undo_cart_item']);

        // if Xero Processing Than
        $wp_scgcge_options = get_option('wp_scgcge_options');
        if (
            $wp_scgcge_options['consumer_key'] &&
            $wp_scgcge_options['consumer_secret'] &&
            $wp_scgcge_options['public_cert_file'] &&
            $wp_scgcge_options['private_key_file'] &&
            $wp_scgcge_options['xero_account_code_asic'] &&
            $wp_scgcge_options['xero_account_code_sales_coy_reg'] &&
            $wp_scgcge_options['xero_account_code_sales'] &&
            $wp_scgcge_options['xero_asic_contact_id']
        ) {
            add_action('woocommerce_order_actions', [$this, 'wp_scgcge_add_order_meta_box_action']);
            add_action('woocommerce_order_action_wc_push_invoice_to_xero_action', [$this, 'wp_scgcge_process_order_meta_box_action']);
        }
    }
}
