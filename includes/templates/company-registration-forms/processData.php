<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Company Registration
 *
 * Company Name Registartion Process data Page.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

$parse_uri = explode('wp-content', dirname(__FILE__));
require_once $parse_uri[0] . 'wp-load.php';

global $wpdb, $wp_scgcge_public;
$error = '';
$public = $wp_scgcge_public;

if (wp_scgcge_getSession()) {
    $code = wp_scgcge_getSession();
    $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
    $results = $wpdb->get_row($query, ARRAY_A);

    if (is_array($results)) {
        extract($results, EXTR_PREFIX_SAME, 'scgcge');
        if (!$user_id == get_current_user_id()) {
            wp_redirect(wp_scgcge_action_url() . '/entities/');
            exit();
        }
    } else {
        wp_redirect(wp_scgcge_action_url() . '/entities/');
        exit();
    }
}

if ((isset($_POST['step']) && ($_POST['step'] == 'add-individual' || $_POST['step'] == 'add-company')) && wp_scgcge_getSession()) {
    $previousStep = wp_scgcge_toUpper($_POST);
    $code = wp_scgcge_getSession();
    $result = $wpdb->insert($wpdb->prefix . 'asic_entities', [
        'entity_type' => isset($previousStep['entity_type']) ? $previousStep['entity_type'] : '',
        'entity_role_dir' => isset($previousStep['entity_role_dir']) ? $previousStep['entity_role_dir'] : '',
        'entity_role_sec' => isset($previousStep['entity_role_sec']) ? $previousStep['entity_role_sec'] : '',
        'entity_role_sha' => isset($previousStep['entity_role_sha']) ? $previousStep['entity_role_sha'] : '',
        'entity_first_name' => isset($previousStep['entity_first_name']) ? $previousStep['entity_first_name'] : '',
        'entity_middle_name1' => isset($previousStep['entity_middle_name1']) ? $previousStep['entity_middle_name1'] : '',
        'entity_middle_name2' => isset($previousStep['entity_middle_name2']) ? $previousStep['entity_middle_name2'] : '',
        'entity_last_name' => isset($previousStep['entity_last_name']) ? $previousStep['entity_last_name'] : '',
        'entity_former' => isset($previousStep['entity_former']) ? $previousStep['entity_former'] : '',
        'entity_former_first_name' => isset($previousStep['entity_former_first_name']) ? $previousStep['entity_former_first_name'] : '',
        'entity_former_middle_name1' => isset($previousStep['entity_former_middle_name1']) ? $previousStep['entity_former_middle_name1'] : '',
        'entity_former_middle_name2' => isset($previousStep['entity_former_middle_name2']) ? $previousStep['entity_former_middle_name2'] : '',
        'entity_former_last_name' => isset($previousStep['entity_former_last_name']) ? $previousStep['entity_former_last_name'] : '',
        'entity_birth_date' => isset($previousStep['entity_birth_date']) ? date('Y-m-d', strtotime(str_replace('/', '-', $previousStep['entity_birth_date']))) : '',
        'entity_birth_country' => isset($previousStep['entity_birth_country']) ? $previousStep['entity_birth_country'] : '',
        'entity_birth_state' => isset($previousStep['entity_birth_state']) ? $previousStep['entity_birth_state'] : '',
        'entity_birth_suburb' => isset($previousStep['entity_birth_suburb']) ? $previousStep['entity_birth_suburb'] : '',
        'entity_company_name' => isset($previousStep['entity_company_name']) ? $previousStep['entity_company_name'] : '',
        'entity_company_country' => isset($previousStep['entity_company_country']) ? $previousStep['entity_company_country'] : '',
        'entity_company_acn' => isset($previousStep['entity_company_acn']) ? $previousStep['entity_company_acn'] : '',
        'address_care' => isset($previousStep['address_care']) ? $previousStep['address_care'] : '',
        'address_line2' => isset($previousStep['address_line2']) ? $previousStep['address_line2'] : '',
        'address_street' => isset($previousStep['address_street']) ? $previousStep['address_street'] : '',
        'address_suburb' => isset($previousStep['address_suburb']) ? $previousStep['address_suburb'] : '',
        'address_state' => isset($previousStep['address_state']) ? $previousStep['address_state'] : '',
        'address_postcode' => isset($previousStep['address_postcode']) ? $previousStep['address_postcode'] : '',
        'address_country' => isset($previousStep['address_country']) ? $previousStep['address_country'] : '',
        'user_id' => get_current_user_id(),
        'code' => $code
    ]);

    $entitiesid = $wpdb->insert_id;
    // echo '<pre>';
    // print_r($previousStep);
    // echo '</pre>';
    // die();
    foreach ($previousStep['share_details'] as $key => $SharepreviousStep) {
        if ($SharepreviousStep['share_class'] != '') {
            $result = $wpdb->insert($wpdb->prefix . 'asic_entity_shares', [
                'entity_id' => isset($entitiesid) ? $entitiesid : '',

                'share_class' => isset($SharepreviousStep['share_class']) ? $SharepreviousStep['share_class'] : '',
                'share_number' => isset($SharepreviousStep['share_number']) ? $SharepreviousStep['share_number'] : '',
                'share_paid' => isset($SharepreviousStep['share_paid']) ? $SharepreviousStep['share_paid'] : '',
                'share_paid_total' => (isset($previousStep['entity_role_sha']) == 'Y' ? (intval($SharepreviousStep['share_number']) * floatval($SharepreviousStep['share_paid'])) : ''),
                'share_unpaid' => isset($SharepreviousStep['share_unpaid']) ? $SharepreviousStep['share_unpaid'] : '',
                'share_unpaid_total' => (isset($previousStep['entity_role_sha']) == 'Y' ? (intval($SharepreviousStep['share_number']) * floatval($SharepreviousStep['share_unpaid'])) : ''),
                'share_beneficial' => isset($SharepreviousStep['share_beneficial']) ? $SharepreviousStep['share_beneficial'] : '',
                'share_beneficiary' => isset($SharepreviousStep['share_beneficiary']) ? $SharepreviousStep['share_beneficiary'] : '',
                'user_id' => get_current_user_id(),
                'code' => $code,
            ]);
        }
    }
} elseif ((isset($_POST['step']) && ($_POST['step'] == 'edit-individual' || $_POST['step'] == 'edit-company')) && wp_scgcge_getSession()) {
    $previousStep = wp_scgcge_toUpper($_POST);
    $code = wp_scgcge_getSession();
    $result = $wpdb->update($wpdb->prefix . 'asic_entities', [
        'entity_type' => isset($previousStep['entity_type']) ? $previousStep['entity_type'] : '',
        'entity_role_dir' => isset($previousStep['entity_role_dir']) ? $previousStep['entity_role_dir'] : '',
        'entity_role_sec' => isset($previousStep['entity_role_sec']) ? $previousStep['entity_role_sec'] : '',
        'entity_role_sha' => isset($previousStep['entity_role_sha']) ? $previousStep['entity_role_sha'] : '',
        'entity_first_name' => isset($previousStep['entity_first_name']) ? $previousStep['entity_first_name'] : '',
        'entity_middle_name1' => isset($previousStep['entity_middle_name1']) ? $previousStep['entity_middle_name1'] : '',
        'entity_middle_name2' => isset($previousStep['entity_middle_name2']) ? $previousStep['entity_middle_name2'] : '',
        'entity_last_name' => isset($previousStep['entity_last_name']) ? $previousStep['entity_last_name'] : '',
        'entity_former' => isset($previousStep['entity_former']) ? $previousStep['entity_former'] : '',
        'entity_former_first_name' => isset($previousStep['entity_former_first_name']) ? $previousStep['entity_former_first_name'] : '',
        'entity_former_middle_name1' => isset($previousStep['entity_former_middle_name1']) ? $previousStep['entity_former_middle_name1'] : '',
        'entity_former_middle_name2' => isset($previousStep['entity_former_middle_name2']) ? $previousStep['entity_former_middle_name2'] : '',
        'entity_former_last_name' => isset($previousStep['entity_former_last_name']) ? $previousStep['entity_former_last_name'] : '',
        'entity_birth_date' => isset($previousStep['entity_birth_date']) ? date('Y-m-d', strtotime(str_replace('/', '-', $previousStep['entity_birth_date']))) : '',
        'entity_birth_country' => isset($previousStep['entity_birth_country']) ? $previousStep['entity_birth_country'] : '',
        'entity_birth_state' => isset($previousStep['entity_birth_state']) ? $previousStep['entity_birth_state'] : '',
        'entity_birth_suburb' => isset($previousStep['entity_birth_suburb']) ? $previousStep['entity_birth_suburb'] : '',
        'entity_company_name' => isset($previousStep['entity_company_name']) ? $previousStep['entity_company_name'] : '',
        'entity_company_country' => isset($previousStep['entity_company_country']) ? $previousStep['entity_company_country'] : '',
        'entity_company_acn' => isset($previousStep['entity_company_acn']) ? $previousStep['entity_company_acn'] : '',
        'address_care' => isset($previousStep['address_care']) ? $previousStep['address_care'] : '',
        'address_line2' => isset($previousStep['address_line2']) ? $previousStep['address_line2'] : '',
        'address_street' => isset($previousStep['address_street']) ? $previousStep['address_street'] : '',
        'address_suburb' => isset($previousStep['address_suburb']) ? $previousStep['address_suburb'] : '',
        'address_state' => isset($previousStep['address_state']) ? $previousStep['address_state'] : '',
        'address_postcode' => isset($previousStep['address_postcode']) ? $previousStep['address_postcode'] : '',
        'address_country' => isset($previousStep['address_country']) ? $previousStep['address_country'] : '',
    ], ['code' => $code, 'id' => $_POST['id']]);

    $entitiesid = $wpdb->insert_id;

    $code = wp_scgcge_getSession();
    $id = $_POST['id'];
    $share_query = "SELECT * FROM $wpdb->prefix" . "asic_entity_shares WHERE code = '$code' and entity_id ='$id'";
    $share_entities = $wpdb->get_results($share_query, ARRAY_A);

    /*echo "<pre>";
    print_r($share_entities);
    print_r($previousStep['share_details']);
    exit;*/

    foreach ($previousStep['share_details'] as $key => $SharepreviousStep) {
        // check if shareid exists
        if ($SharepreviousStep['share_id'] != '') {
            $result = $wpdb->update($wpdb->prefix . 'asic_entity_shares', [
                'share_class' => isset($SharepreviousStep['share_class']) ? $SharepreviousStep['share_class'] : '',
                'share_number' => isset($SharepreviousStep['share_number']) ? $SharepreviousStep['share_number'] : '',
                'share_paid' => isset($SharepreviousStep['share_paid']) ? $SharepreviousStep['share_paid'] : '',
                'share_paid_total' => (isset($previousStep['entity_role_sha']) == 'Y' ? (intval($SharepreviousStep['share_number']) * floatval($SharepreviousStep['share_paid'])) : ''),
                'share_unpaid' => isset($SharepreviousStep['share_unpaid']) ? $SharepreviousStep['share_unpaid'] : '',
                'share_unpaid_total' => (isset($previousStep['entity_role_sha']) == 'Y' ? (intval($SharepreviousStep['share_number']) * floatval($SharepreviousStep['share_unpaid'])) : ''),
                'share_beneficial' => isset($SharepreviousStep['share_beneficial']) ? $SharepreviousStep['share_beneficial'] : '',
                'share_beneficiary' => isset($SharepreviousStep['share_beneficiary']) ? $SharepreviousStep['share_beneficiary'] : '',
            ], ['code' => $code, 'entity_id' => $_POST['id'], 'id' => $SharepreviousStep['share_id']]);
        } else { // insert share
            if ($SharepreviousStep['share_class'] != '') {
                $result = $wpdb->insert($wpdb->prefix . 'asic_entity_shares', [
                    'entity_id' => isset($_POST['id']) ? $_POST['id'] : '',

                    'share_class' => isset($SharepreviousStep['share_class']) ? $SharepreviousStep['share_class'] : '',
                    'share_number' => isset($SharepreviousStep['share_number']) ? $SharepreviousStep['share_number'] : '',
                    'share_paid' => isset($SharepreviousStep['share_paid']) ? $SharepreviousStep['share_paid'] : '',
                    'share_paid_total' => (isset($previousStep['entity_role_sha']) == 'Y' ? (intval($SharepreviousStep['share_number']) * floatval($SharepreviousStep['share_paid'])) : ''),
                    'share_unpaid' => isset($SharepreviousStep['share_unpaid']) ? $SharepreviousStep['share_unpaid'] : '',
                    'share_unpaid_total' => (isset($previousStep['entity_role_sha']) == 'Y' ? (intval($SharepreviousStep['share_number']) * floatval($SharepreviousStep['share_unpaid'])) : ''),
                    'share_beneficial' => isset($SharepreviousStep['share_beneficial']) ? $SharepreviousStep['share_beneficial'] : '',
                    'share_beneficiary' => isset($SharepreviousStep['share_beneficiary']) ? $SharepreviousStep['share_beneficiary'] : '',
                    'user_id' => get_current_user_id(),
                    'code' => $code,
                ]);
            }
        }
    }
} elseif ((isset($_POST['step']) && ($_POST['step'] == 'review')) && wp_scgcge_getSession()) {
    global $woocommerce;
    $previousStep = wp_scgcge_toUpper($_POST);
    $code = wp_scgcge_getSession();
    if ($previousStep['applicant'] == '0') {
        $result = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'agree' => isset($previousStep['agree']) ? $previousStep['agree'] : '',
            'applicant' => isset($previousStep['applicant']) ? $previousStep['applicant'] : '',
            'applicant_first_name' => isset($previousStep['applicant_first_name']) ? $previousStep['applicant_first_name'] : '',
            'applicant_middle_name' => isset($previousStep['applicant_middle_name']) ? $previousStep['applicant_middle_name'] : '',
            'applicant_last_name' => isset($previousStep['applicant_last_name']) ? $previousStep['applicant_last_name'] : '',
            'applicant' => isset($previousStep['applicant']) ? $previousStep['applicant'] : '',
            'applicant_line2' => isset($previousStep['applicant_line2']) ? $previousStep['applicant_line2'] : '',
            'applicant_street' => isset($previousStep['applicant_street']) ? $previousStep['applicant_street'] : '',
            'applicant_suburb' => isset($previousStep['applicant_suburb']) ? $previousStep['applicant_suburb'] : '',
            'applicant_state' => isset($previousStep['applicant_state']) ? $previousStep['applicant_state'] : '',
            'applicant_postcode' => isset($previousStep['applicant_postcode']) ? $previousStep['applicant_postcode'] : '',
        ], ['code' => $code, 'user_id' => get_current_user_id()]);
    } else {
        $applicant_id = $previousStep['applicant'];
        $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '$code' AND id = '$applicant_id'";
        $applicant_data = $wpdb->get_row($query, ARRAY_A);
        $result = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'agree' => isset($previousStep['agree']) ? $previousStep['agree'] : '',
            'applicant' => $applicant_id,
            'applicant_first_name' => $applicant_data['entity_first_name'],
            'applicant_middle_name' => isset($applicant_data['entity_middle_name1']) ? $applicant_data['entity_middle_name1'] : '',
            'applicant_last_name' => $applicant_data['entity_last_name'],
            'applicant_line2' => isset($applicant_data['address_line2']) ? $applicant_data['address_line2'] : '',
            'applicant_street' => $applicant_data['address_street'],
            'applicant_suburb' => $applicant_data['address_suburb'],
            'applicant_state' => isset($applicant_data['address_state']) ? $applicant_data['address_state'] : '',
            'applicant_postcode' => isset($applicant_data['address_postcode']) ? $applicant_data['address_postcode'] : ''
        ], ['code' => $code, 'user_id' => get_current_user_id()]);
    }

    $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
    $results = $wpdb->get_row($query, ARRAY_A);
    $results = stripslashes_deep($results);
    extract($results, EXTR_PREFIX_SAME, 'scgcge');

    if ($order_id != '' && $edge_id != '' && in_array($status, ['validation failed', 'rejected'])) {
        $company_registration_id = wp_scgcge_getSession();
        //$array = wp_scgcge_prepare_ge_array($company_registration_id, true);
        $array = $public->prepare_ge_array($company_registration_id, true);

        if (!isset($array) || !is_array($array) || empty($array)) {
            $text = '<p>' . __('Hi', 'scgcge') . ' ' . get_option('woocommerce_email_from_name') . ',</p>';
            $text .= '<p>' . __('There was data processing error. ', 'scgcge') . '</p>';
            $text .= '<p>' . __('Please manually relodge the application by changing the order status to processing. If the problem persists, please contact GetEDGE support team.', 'scgcge') . '</p>';
            $text .= '<p>' . __('Kind regards,', 'scgcge') . '<br />' . __('GetEDGE Team', 'scgcge') . '</p>';
            $public->sendEmail(get_option('woocommerce_email_from_address'), 'GetEDGE Error', 'GetEDGE Error', $text);
            if (get_option('wp_scgcge_options')['slack_webhook_url']) {
                wp_scgcge_toSlack('There was an error processing Order #' . $order_id . ' - Data processing');
            }
            return false;
        }

        $save = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'status' => 'placed',
        ], ['code' => $company_registration_id]);


/*
        $lodge201 = lodge201($company_registration_id, $array, $order_id);

        $subject = $company_name_full . ' ' . __('has been relodged with ASIC', 'scgcge');
        $title = $company_name_full . ' ' . __('has been relodged with ASIC', 'scgcge');
        $text = '<p>' . __('Dear', 'scgcge') . ' ' . ucfirst(get_user_meta($user_id, 'billing_first_name', true)) . ',<p>';
        $text .= '<p>' . __('We are pleased to confirm that your application for', 'scgcge') . ' ' . $company_name_full . ' ' . __('has been relodged with ASIC', 'scgcge') . ' (Ref# ' . $order_id . ' / ' . $lodge201 . ').<p>';
        $text .= '<p>' . __('In most cases, submitted orders will be processed by ASIC within minutes however, please note that unexpected delays may occur if:', 'scgcge') . '</p>';
        $text .= '<ul><li>' . __('A manual review is initiated by ASIC for reasons such as an unusual name containing non-dictionary words</li><li>The ASIC system is offline for maintenance or inaccessible for any reason', 'scgcge') . '</li></ul>';
        $text .= '<p>' . __('You may review the status of your order at anytime by logging in to your', 'scgcge') . ' <a href="' . get_permalink(get_option('woocommerce_myaccount_page_id')) . '/">client area</a>.' . __('Information on how to access your client area has been sent to you in a previous email.', 'scgcge') . '</p>';
        $text .= '<p>' . __('We appreciate and value your comments, suggestions and general feedback as this helps us to further develop our systems for an ever-improving customer experience. Please write to', 'scgcge') . ' ' . get_option('woocommerce_email_from_address') . ' ' . __('with anything that you would like us to know.', 'scgcge') . '</p>';
        $text .= '<p>' . __('Thank you for your business.', 'scgcge') . ' </p>';
        $text .= '<p>' . __('Sincerely,', 'scgcge') . '<br>' . get_option('woocommerce_email_from_name') . ' </p>';
*/

        //wp_scgcge_sendEmail(get_user_meta( $user_id, 'billing_email', true ), $subject, $title, $text);
        wp_scgcge_destroySession($company_registration_id);
        wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')) . 'orders');
        exit();
    } else {
        if ($woocommerce->cart->get_cart_contents_count() != 0) {
            $woocommerce->cart->empty_cart();
        }
        $wp_scgcge_options = get_option('wp_scgcge_options');
        $reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';
        $woocommerce->cart->add_to_cart($reg_fee_id);
        wp_redirect(wc_get_cart_url());
        exit();
    }
} elseif ((isset($_GET['step']) && ($_GET['step'] == 'delete-entity')) && wp_scgcge_getSession()) {
    $code = wp_scgcge_getSession();

    $id = $_GET['id'];
    $share_query = "SELECT * FROM $wpdb->prefix" . "asic_entity_shares WHERE code = '$code' and entity_id ='$id'";
    $share_entities = $wpdb->get_results($share_query, ARRAY_A);

    foreach ($share_entities as $key => $share_entitie) {
        $result = $wpdb->delete($wpdb->prefix . 'asic_entity_shares', ['entity_id' => $share_entitie['entity_id'], 'id' => $share_entitie['id']]);
    }

    $result = $wpdb->delete($wpdb->prefix . 'asic_entities', ['id' => $_GET['id']]);
} elseif ((isset($_GET['save']) && ($_GET['save'] == 'true')) && wp_scgcge_getSession()) {
    $code = wp_scgcge_getSession();
    $current_user = get_current_user_id();
    $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
    $results = $wpdb->get_row($query, ARRAY_A);
    extract($results, EXTR_PREFIX_SAME, 'scgcge');
    if ($current_user == 0) {
        wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')) . '?save');
        exit();
    } else {
        $result = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'user_id' => $current_user,
        ], ['code' => $code]);
        wp_redirect(get_permalink(get_option('woocommerce_myaccount_page_id')) . 'list-applications');
        exit();
    }
}

wp_redirect(wp_scgcge_action_url() . '/entities/');
exit();
