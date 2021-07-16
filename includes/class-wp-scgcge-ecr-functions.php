<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 *
 * Manage ECR Panel
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

function lodge201($form_main_id, $array, $order_id)
{
    date_default_timezone_set('Australia/Sydney');
    global $wpdb, $wp_scgcge_public;
    $public = $wp_scgcge_public;

    $wp_scgcge_options = get_option('wp_scgcge_options');
    $scgcge_ge_api_key = !empty($wp_scgcge_options['ge_api_key']) ? $wp_scgcge_options['ge_api_key'] : '';
    $scgcge_ge_url = !empty($wp_scgcge_options['ge_url']) ? $wp_scgcge_options['ge_url'] : '';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $scgcge_ge_url . '/201');
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-Auth-Edge: ' . $scgcge_ge_api_key]);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($array));
    $result = curl_exec($curl);
    $result_array = json_decode($result, true);
    curl_close($curl);
    
    if ($result === false) {
        $text = '<p>' . __('Hi', 'scgcge') . ' ' . get_option('woocommerce_email_from_name') . ',</p>';
        $text .= '<p>';
        foreach ($result_array['error'] as $error) {
            $text .= $error[0] . '<br>';
        }
        $text .= '</p>';
        $text .= '<p>' . __('Please fix the errors and resubmit. If the problem persists, please contact GetEDGE support team.', 'scgcge') . '</p>';
        $text .= '<p>' . __('Kind regards,', 'scgcge') . '<br />' . __('GetEDGE Team', 'scgcge') . '</p>';
        $public->sendEmail(get_option('woocommerce_email_from_address'), 'GetEDGE Error', 'GetEDGE Error', $text);
        if (get_option('wp_scgcge_options')['slack_webhook_url']) {
            wp_scgcge_toSlack('There was an error processing Order #' . $order_id . ' - Communication error');
        }
        return false;
    }

    if (isset($result_array['error']) && is_array($result_array['error']) && count($result_array['error'])) {
        $status = $result_array['error'];
    } else {
        $status = 'new';
    }

    if ($status == 'new') {
        $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'edge_id' => $result_array['response'],
            'status' => is_array($status) && count($result_array['error']) ? 'validation failed' : $status,
        ], ['code' => $form_main_id]);
    } else {
        $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'status' => is_array($status) && count($result_array['error']) ? 'validation failed' : $status,
        ], ['code' => $form_main_id]);
    }


    if ($status != 'new') {
        $order = wc_get_order($order_id);

        $text = '<p>' . __('Hi', 'scgcge') . ' ' . $order->get_billing_first_name() . ',</p>';
        $text .= '<p>There was an error with the lodgement for order #'.$order_id.'.</p>';
        $text .= '<p><code>';
        if (isset($result_array['error']) && is_array($result_array['error']) && !empty($result_array['error'])) {
            foreach ($result_array['error'] as $error) {
                $text .= "- " . $error[0]."<br>";
            }
        }
        $text .= '</code></p>';
        $text .= '<p>' . __('You may review and amend your order at anytime by logging in to your', 'scgcge') . ' <a href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) . '/">' . __('client area', 'scgcge') . '</a>.</p>';

        $text .= '<p>' . __('Thank you for your business.', 'scgcge') . ' </p>';
        $text .= '<p>' . __('Sincerely,', 'scgcge') . '<br>' . get_option('woocommerce_email_from_name') . ' </p>';
        
        $public->sendEmail($order->get_billing_email(), $array['company_full_name'] . ' Error', $array['company_full_name'] . ' Error', $text);
        $public->sendEmail(get_option('woocommerce_email_from_address'), $array['company_full_name'] . ' Error', $array['company_full_name'] . ' Error', $text);
        
        if (get_option('wp_scgcge_options')['slack_webhook_url']) {
            wp_scgcge_toSlack('There was an error processing Order #' . $order_id . ' - ' . $status);
        }
        return false;
    }

    if (get_option('wp_scgcge_options')['slack_webhook_url']) {
        wp_scgcge_toSlack('Form 201 lodged for order #' . $order_id);
    }

    wp_scgcge_destroySession($form_main_id);

    return isset($result_array['response']) ? $result_array['response'] : false;
}

function checkStatus($array)
{
    date_default_timezone_set('Australia/Sydney');

    global $wpdb, $wp_scgcge_public;
    $public = $wp_scgcge_public;

    $wp_scgcge_options = get_option('wp_scgcge_options');
    $scgcge_ge_url = !empty($wp_scgcge_options['ge_url']) ? $wp_scgcge_options['ge_url'] : '';
    $scgcge_ge_api_key = !empty($wp_scgcge_options['ge_api_key']) ? $wp_scgcge_options['ge_api_key'] : '';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $scgcge_ge_url . '/checkStatus/' . $array['edge_id']);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-Auth-Edge: ' . $scgcge_ge_api_key]);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);

    $result_array = json_decode($result, true);

    curl_close($curl);

    $query = "SELECT company_name_full FROM $wpdb->prefix" . "asic_companies WHERE order_id = '" . $array['order_id'] . "'";
    $query2 = "SELECT status FROM $wpdb->prefix" . "asic_companies WHERE order_id = '" . $array['order_id'] . "'";
    $company_name_full = $wpdb->get_var($query);
    $current_status = $wpdb->get_var($query2);
    $text = '<p>Dear ' . ucfirst(get_user_meta($array['user_id'], 'billing_first_name', true)) . ',<p>';
    if (isset($result_array['response'])) {
        switch ($result_array['response']['status']) {
            case 'transmission ok':
                $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                    // 'acn' => $result_array['response']['info']['acn'],
                    'status' => $result_array['response']['status'],
                ], ['code' => $array['form_main_id']]);

                break;

            case 'validation ok':
                $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                    // 'acn' => $result_array['response']['info']['acn'],
                    'status' => $result_array['response']['status'],
                    'document' => $result_array['response']['document_number'],
                ], ['code' => $array['form_main_id']]);

                break;

            case 'finished':
                if ($array['search_result'] == 'ACNONLY') {
                    $company_name_full = 'A.C.N. ' . chunk_split($result_array['response']['info']['acn'], 3, ' ') . ' ' . $array['legal_elements'];
                    $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                        'company_name_full' => 'A.C.N. ' . chunk_split($result_array['response']['info']['acn'], 3, ' ') . ' ' . $array['legal_elements'],
                        'acn' => $result_array['response']['info']['acn'],
                        'reg_date' => date('Y-m-d', time()),
                        'status' => $result_array['response']['status'],
                        'document' => $result_array['response']['document_number'],
                    ], ['code' => $array['form_main_id']]);
                } else {
                    $company_name_full = $array['company_name_full'];
                    $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                        'acn' => $result_array['response']['info']['acn'],
                        'reg_date' => date('Y-m-d', time()),
                        'status' => $result_array['response']['status'],
                        'document' => $result_array['response']['document_number'],
                    ], ['code' => $array['form_main_id']]);
                }
                $subject = __('Order Complete for ', 'scgcge') . $company_name_full;
                $title = __('Order Complete for ', 'scgcge') . $company_name_full;
                $text .= '<p>' . __('We are pleased to confirm that ASIC has accepted your new company registration order.', 'scgcge') . '</p>';
                $text .= '<p>' . __('You can download the documents for the new company using the links below:', 'scgcge') . '</p>';
                $text .= '<ul><li><a href="' . home_url() . '/downloadDocs?token=' . $array['form_main_id'] . '">' . __('Company Documents', 'scgcge') . '</a>, ' . __('to be printed, signed and kept at your registered office', 'scgcge') . '</li><li><a href="' . home_url() . '/downloadCert?token=' . $array['form_main_id'] . '">' . __('Certificate of Registration', 'scgcge') . '</a></li></ul>';
                $text .= __('Please note you will need to login to the account you have created at', 'scgcge') . ' <a href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) . '/list-companies">' . get_permalink(get_option('woocommerce_myaccount_page_id')) . '</a>.';

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $scgcge_ge_url . '/201/' . $array['edge_id']);
                curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-Auth-Edge: ' . $scgcge_ge_api_key]);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($curl);
                if (!file_exists(wp_upload_dir()['basedir'] . '/_certs/')) {
                    mkdir(wp_upload_dir()['basedir'] . '/_certs/', 0755, true);
                }
                echo file_put_contents(wp_upload_dir()['basedir'] . '/_certs/' . $result_array['response']['info']['acn'] . '.pdf', $result);
                curl_close($curl);

                $text .= '<p>' .
                    __('We appreciate and value your comments, suggestions and general feedback as this helps us to further develop our systems for an ever-improving customer experience. Please write to', 'scgcge') . ' ' .
                    get_option('woocommerce_email_from_address') . ' ' .
                    __('with anything that you would like us to know.', 'scgcge') .
                    '</p>' .
                    __('Thank you for your business. ', 'scgcge') .
                    '<p>' .
                    __('Sincerely,', 'scgcge') .
                    '<br>' .
                    get_option('woocommerce_email_from_name') .
                    ' </p>';

                $public->sendEmail(get_user_meta($array['user_id'], 'billing_email', true), $subject, $title, $text);
                $theOrder = wc_get_order($array['order_id']);
                $theOrder->update_status('completed');

                break;

            case 'validation failed':
                $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                    'status' => $result_array['response']['status'],
                    'document' => $result_array['response']['document_number'] ? $result_array['response']['document_number'] : '',
                ], ['code' => $array['form_main_id']]);

                $subject = __('Validation errors for ', 'scgcge') . $company_name_full;
                $title = __('Validation errors for ', 'scgcge') . $company_name_full;
                $text .= '<p>' . __('Unfortunately, ASIC has not accepted your company registration order due to certain validation failures. Details of the validation error(s) are outlined below. The good news is that you are entitled to amend and resubmit your order without any additional charges. ', 'scgcge') . '</p>';
                $text .= '<p>' . __('To review, amend and resubmit your registration order,', 'scgcge') . ' <a href="' . wp_scgcge_action_url() . '/general-details/?token=' . $array['form_main_id'] . '">' . __('click here', 'scgcge') . '</a> ' . __('or access your', 'scgcge') . ' <a href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) . '/">' . __('client area', 'scgcge') . '</a>, ' . __('locate the application and click amend.', 'scgcge') . '  </p>';
                $text .= '<p><strong>' . __('Validation error(s)', 'scgcge') . '</strong></p>';
                $i = 0;

                $reports = checkLog($array);
                foreach ($reports as $response_validation) {
                    if ($response_validation['communication']['form'] == 'VALIDATION') {
                        if ($i == 0) {
                            foreach ($response_validation['communication']['values']['documents_rejected'][0]['items'] as $error) {
                                $text .= '<blockquote>Error: ' . $error['error'] . '<br />Entered data: ' . $error['contents'] . '<br />Segment: ' . $error['segment_tag'] . '/' . $error['number'] . '</blockquote>';
                            }
                        }
                        $i++;
                        break;
                    }
                }
                $text .= '<p>' . __('We appreciate and value your comments, suggestions and general feedback as this helps us to further develop our systems for an ever-improving customer experience. Please write to', 'scgcge') . ' ' . get_option('woocommerce_email_from_address') . ' ' . __('with anything that you would like us to know.', 'scgcge') . '</p>';
                $text .= '<p>' . __('Thank you for your business. ', 'scgcge') . '</p>';
                $text .= '<p>' . __('Sincerely,', 'scgcge') . '<br>' . get_option('woocommerce_email_from_name') . ' </p>';
                $public->sendEmail(get_user_meta($array['user_id'], 'billing_email', true), $subject, $title, $text);

                break;

            case 'rejected':
                $responses = checkLog($array);
                $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                    'status' => 'rejected',
                    'document' => $result_array['response']['document_number'] ? $result_array['response']['document_number'] : '',
                ], ['code' => $array['form_main_id']]);
                foreach ($responses as $response) {
                    if ($response['communication']['form'] == 'ASCRA56' && $response['communication']['values']['ZNR']['asic_advice_type_code'] == '201RJCT') {
                        $response_code = $response['communication']['values']['ZNR']['asic_advice_type_code'];
                        $response_title = $response['communication']['values']['ZNR']['asic_advice_type_description'];
                        $response_text = $response['communication']['values']['ZTE'];
                        $html = '<hr><p><small><em>' . nl2br($response_text) . '<br></small></em></p><hr>';

                        $subject = __('Rejection Notice for ', 'scgcge') . $company_name_full;
                        $title = __('Rejection Notice for ', 'scgcge') . $company_name_full;
                        $text .= '<p>' . __('Unfortunately, ASIC has not accepted your company registration order due to the reason or reasons outlined further down this email. The good news is that you are entitled to amend and resubmit your order without any additional charges.', 'scgcge') . '</p>';
                        $text .= '<p>' . __('To review, amend and resubmit your registration order,', 'scgcge') . ' <a href="' . wp_scgcge_action_url() . '/general-details/?token=' . $array['form_main_id'] . '">' . __('click here', 'scgcge') . '</a>' . (' or access your ') . '<a href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) . '/">' . __('client area', 'scgcge') . '</a>, ' . __('locate the application and click amend.', 'scgcge') . ' </p>';
                        $text .= '<p><strong>' . __('Rejection Notice', 'scgcge') . '</strong></p>';
                        $text .= $html;
                    }
                }
                $public->sendEmail(get_user_meta($array['user_id'], 'billing_email', true), $subject, $title, $text);

                break;

            case 'manual review':
                $responses = checkLog($array);
                if (isset($current_status) && $current_status == 'manual review') {
                    $send_email = false;
                } else {
                    $send_email = true;
                }
                foreach ($responses as $response) {
                    if ($response['communication']['form'] == 'ASCRA56') {
                        $response_code = $response['communication']['values']['ZNR']['asic_advice_type_code'];
                        $response_title = $response['communication']['values']['ZNR']['asic_advice_type_description'];
                        $response_text = $response['communication']['values']['ZTE'];
                        $html = '<hr><p><small><em>' . nl2br($response_text) . '<br></small></em></p><hr>';
                        if ($response_code == '201MANL') { // Manual review invoked
                            $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                                'document' => $result_array['response']['document_number'] ? $result_array['response']['document_number'] : '',
                                'status' => 'manual review',
                            ], ['code' => $array['form_main_id']]);

                            $subject = __('ASIC Review Notice for for ', 'scgcge') . $company_name_full;
                            $title = __('ASIC Review Notice for for ', 'scgcge') . $company_name_full;
                            $text .= '<p>' . __('This email is to update you regarding your new company order. Currently ASIC is reviewing your order manually, which will cause an unavoidable delay of your company registration. No further action is required from you at this stage. We will check every 5 minutes for updates and relay any new information to you by email immediately.', 'scgcge') . '</p>';
                        } elseif ($response_code == '201PEND') { // Reserved pending action by applicant
                            $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                                'document' => $result_array['response']['document_number'] ? $result_array['response']['document_number'] : '',
                                'status' => 'manual review',
                            ], ['code' => $array['form_main_id']]);
                            $subject = __('ASIC Review Notice for for ', 'scgcge') . $company_name_full;
                            $title = __('ASIC Review Notice for for ', 'scgcge') . $company_name_full;
                            $text .= '<p>' . __('This email is to update you regarding your new company order. Currently your submitted company order is reserved pending action by applicant. This is causing an unavoidable delay of your company registration. No further action is required from you at this stage. We will check every 5 minutes for updates and relay any new information to you by email immediately.', 'scgcge') . '</p>';
                        } elseif ($response_code == '201SUBJ') { // Temporarily reserved subject to ASIC decision
                            $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                                'document' => $result_array['response']['document_number'] ? $result_array['response']['document_number'] : '',
                                'status' => 'manual review',
                            ], ['code' => $array['form_main_id']]);

                            $subject = __('ASIC Review Notice for for ', 'scgcge') . $company_name_full;
                            $title = __('ASIC Review Notice for for ', 'scgcge') . $company_name_full;
                            $text .= '<p>' . __('This email is to update you regarding your new company order. Currently your submitted company order is temporarily reserved subject to ASIC decision. This is causing an unavoidable delay of your company registration. No further action is required from you at this stage. We will check every 5 minutes for updates and relay any new information to you by email immediately.', 'scgcge') . '</p>';
                        } elseif ($response_code == '201WDRW') {
                            $subject = __('Validation errors for ', 'scgcge') . $company_name_full;
                            $title = __('Validation errors for ', 'scgcge') . $company_name_full;
                        }
                    }
                }
                $text .= '<p>' . __('We appreciate and value your comments, suggestions and general feedback as this helps us to further develop our systems for an ever-improving customer experience. Please write to', 'scgcge') . ' ' . get_option('woocommerce_email_from_address') . ' ' . __('with anything that you would like us to know.', 'scgcge') . '</p>';
                $text .= '<p>' . __('Thank you for your business.', 'scgcge') . ' </p>';
                $text .= '<p>' . __('Sincerely,', 'scgcge') . '<br>' . get_option('woocommerce_email_from_name') . ' </p>';
                if ($send_email) {
                    $public->sendEmail(get_user_meta($array['user_id'], 'billing_email', true), $subject, $title, $text);
                }

                break;

            default:
                $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
                    'status' => $result_array['response']['status'],
                    'document' => $result_array['response']['document_number'] ? $result_array['response']['document_number'] : '',
                ], ['code' => $array['form_main_id']]);
        }
        write_log('Status for order #' . $array['order_id'] . ' (request #' . $array['edge_id'] . '): ' . $result_array['response']['status']);
        return $result_array['response']['status'];
    }
}

function checkLog($array)
{
    global $wpdb, $wp_scgcge_public;
    $public = $wp_scgcge_public;

    $wp_scgcge_options = get_option('wp_scgcge_options');
    $scgcge_ge_url = !empty($wp_scgcge_options['ge_url']) ? $wp_scgcge_options['ge_url'] : '';
    $scgcge_ge_api_key = !empty($wp_scgcge_options['ge_api_key']) ? $wp_scgcge_options['ge_api_key'] : '';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $scgcge_ge_url . '/checkLog/' . $array['edge_id']);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['X-Auth-Edge: ' . $scgcge_ge_api_key]);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);
    $result_array = json_decode($result, true);
    curl_close($curl);
    return $result_array['response'];
}
