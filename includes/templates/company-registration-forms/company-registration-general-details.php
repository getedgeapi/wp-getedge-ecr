<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Company Registration
 *
 * Company Name Registartion general Detail Page.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

global $wpdb, $woocommerce;
$error = '';
$prefix = $wpdb->prefix;
if (isset($_POST['company_name']) && isset($_POST['legal_elements']) && $_POST['legal_elements'] != '' && isset($_POST['search_result']) && isset($_POST['company_name_full']) && $_POST['company_name_full'] != '') {
    $previousStep = wp_scgcge_toUpper($_POST);

    if (!wp_scgcge_getSession()) {
        wp_scgcge_setSession();
        $code = wp_scgcge_getSession();
        $result = $wpdb->insert($prefix . 'asic_companies', [
            'company_name_full' => $previousStep['company_name_full'],
            'company_name' => $previousStep['company_name'],
            'legal_elements' => $previousStep['legal_elements'],
            'search_result' => $previousStep['search_result'],
            'user_id' => get_current_user_id(),
            'code' => $code,
            'session_id' => session_id()
            ], ['%s', '%s', '%s', '%s', '%d', '%s']);
    } else {
        $code = wp_scgcge_getSession();
        $result = $wpdb->update($prefix . 'asic_companies', [
            'company_name' => $previousStep['company_name'],
            'legal_elements' => $previousStep['legal_elements'],
            'search_result' => $previousStep['search_result'],
            'company_name_full' => $previousStep['company_name_full'],
            'user_id' => get_current_user_id()
            ], ['code' => $code]);
        // if(!$result) {
        // $code = wp_scgcge_getSession();
        // $result = $wpdb->insert($prefix . 'asic_companies', [
        //     'company_name_full' => $previousStep['company_name_full'],
        //     'company_name' => $previousStep['company_name'],
        //     'legal_elements' => $previousStep['legal_elements'],
        //     'search_result' => $previousStep['search_result'],
        //     'user_id' => get_current_user_id(),
        //     'code' => $code,
        //     'session_id' => session_id()
        //     ], ['%s', '%s', '%s', '%s', '%d', '%s']);
        // }
    }

    $query = "SELECT * FROM $prefix" . "asic_companies WHERE code = '$code'";

    $results = $wpdb->get_row($query, ARRAY_A);

    if (is_array($results)) {
        $results = stripslashes_deep($results);
        extract($results, EXTR_PREFIX_SAME, 'scgcge');
    } else {
        $error = __('Session expired! Please restart the application process from the homepage.', 'scgcge');
    }
} elseif (isset($_GET['token']) && preg_replace('/[^a-zA-Z0-9]+/', '', $_GET['token'])) {
    if ($woocommerce->cart->get_cart_contents_count() != 0) {
        $woocommerce->cart->empty_cart();
    }
    $code = $_GET['token'];
    $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
    $results = $wpdb->get_row($query, ARRAY_A);
    if (is_array($results)) {
        $results = stripslashes_deep($results);
        extract($results, EXTR_PREFIX_SAME, 'scgcge');
    } else {
        $error = __('Session expired! Please restart the application process from the homepage.', 'scgcge');
    }
    if (!$results['user_id'] == get_current_user_id()) {
        $error = __('You are not authorised to edit the application!', 'scgcge');
    }

    if ($results['status'] != '' && $results['status'] != 'validation failed' && $results['status'] != 'rejected' && $results['status'] !== null) {
        $error = __('Application locked! The application was already submitted.', 'scgcge');
    }

    wp_scgcge_destroySession($_GET['token']);
    wp_scgcge_setSession($_GET['token']);

//echo 'token';
} elseif (wp_scgcge_getSession()) {
    $code = wp_scgcge_getSession();
    $query = "SELECT * FROM $prefix" . "asic_companies WHERE code = '$code'";
    $results = $wpdb->get_row($query, ARRAY_A);
    if (is_array($results)) {
        $results = stripslashes_deep($results);
        extract($results, EXTR_PREFIX_SAME, 'scgcge');
    } else {
        $error = __('Session expired! Please restart the application process from the homepage.', 'scgcge');
    }
    if (!$user_id == get_current_user_id()) {
        $error = __('You are not authorised to edit the application!', 'scgcge');
    }
} else {
    $error = __('Session expired! Please restart the application process from the homepage.', 'scgcge');
}

 if ($error != '') {
     echo '<div class="bootstrapiso" style="min-height: 500px; padding-top: 100px;"><div class="alert alert-warning fade show mt-5 mb-5 text-center" role="alert">' . $error . '<br /><a href="' . get_bloginfo('url') . '" class="alert-link">' . __('Go to the homepage', 'scgcge') . ' &rarr;</a></div></div>';
 } else {
     ?>




<form id="companyRegistrationFormGeneral" action="<?php echo wp_scgcge_action_url() . '/addresses/'; ?>" class="bootstrapiso" method="POST" novalidate>

    <div class="row mt-5 mb-5">
        <div class="col-md">
            <div class="progress" style="height: 20px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%"><?php echo __('25%'); ?></div>
            </div>
            <p class="mb-0 text-right mt-2"><strong><?=!empty($company_name_full) ? $company_name_full : ''?></strong></p>
        </div>
    </div>



    <div>
        <input type="hidden" name="search_result" id="search_result" value="<?=$search_result?>">
        <input type="hidden" name="step" id="step" value="1">
       <div class="row">
            <div class="col-md">
                <div class="form-group">

                    <?php echo wp_scgcge_Label('fullcompanyname', __('Full Company Name', 'scgcge'), '') ?>
                    <?php echo wp_scgcge_Input('text', 'fullcompanyname', !empty($company_name_full) ? $company_name_full : '', 'readonly', 'required') ?>
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    <?php echo wp_scgcge_Label('jurisdiction', __('Jurisdiction', 'scgcge'), '') ?>
                    <?php echo wp_scgcge_Select('jurisdiction', wp_scgcge_get_state_options(!empty($jurisdiction) ? $jurisdiction : ''), '', 'required') ?>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md">
                <div class="form-group">
                    <?php echo wp_scgcge_Label('company_subclass', __('Company Subclass', 'scgcge'), '') ?>
                    <?php echo wp_scgcge_Radio('company_subclass', [['label' => __('Proprietary Limited', 'scgcge'), 'value' => 'PROP'], ['label' => __('Superannuation Trustee', 'scgcge'), 'value' => 'PSTC']], !empty($company_subclass) ? $company_subclass : '', 'required', false) ?>
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    <?php echo wp_scgcge_Label('holding_company', __('Will the company have an ultimate holding company?', 'scgcge'), __('If one company controls another company and is itself not a subsidiary of another company, then it is called an ultimate holding company.', 'scgcge')) ?>
                    <?php echo wp_scgcge_Radio('holding_company', [['label' => 'No', 'value' => 'NO'], ['label' => 'Yes', 'value' => 'YES']], !empty($holding_company) ? $holding_company : '', 'required', false) ?>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md">
                <div  class="form-group" data-do-when='{ "holding_company": ["YES"] }' data-do-action="show">
                    <?php echo wp_scgcge_Label('holding_name', __('Ultimate Holding Company', 'scgcge'), '') ?>
                    <?php echo wp_scgcge_Input('text', 'holding_name', !empty($holding_name) ? $holding_name : '', '', 'required', '', '') ?>
                </div>
            </div>
            <div class="col-md">
                <div class="form-group" data-do-when='{ "holding_company": ["YES"] }' data-do-action="show">
                    <?php echo wp_scgcge_Label('holding_country', __('Country of Incorporation', 'scgcge')) ?>
                    <?php echo wp_scgcge_Select('holding_country', wp_scgcge_get_countries_options(!empty($holding_country) ? $holding_country : ''), '', 'required', '', '') ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md">
                <div class="form-group" data-do-when='{ "holding_country": ["AU"], "holding_company": ["YES"] }' data-do-action="show">
                    <?php echo wp_scgcge_Label('holding_acn', __('ACN / ARBN', 'scgcge'), '') ?>
                    <?php echo wp_scgcge_Input('text', 'holding_acn', !empty($holding_acn) ? $holding_acn : '', '', 'required', '', '') ?>
                </div>
            </div>
            <div class="col-md">
                <div class="form-group" data-do-when='{ "holding_country": ["AU"], "holding_company": ["YES"] }' data-do-action="show">
                    <?php echo wp_scgcge_Label('holding_abn', __('ABN', 'scgcge')) ?>
                    <?php echo wp_scgcge_Input('text', 'holding_abn', !empty($holding_abn) ? $holding_abn : '', '', 'required', '', '') ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md">
                <div class="form-group" data-do-when='{ "search_result": ["CHECKBN"] }' data-do-action="show">
                    <?php echo wp_scgcge_Label('bn_when', __('When was your business name registered?', 'scgcge'), '') ?>
                    <?php echo wp_scgcge_Radio('bn_when', [['label' => __('After May 28th 2012', 'scgcge'), 'value' => 'AFTER'], ['label' => __('Before May 28th 2012', 'scgcge'), 'value' => 'BEFORE']], !empty($bn_when) ? $bn_when : '', 'required') ?>
                </div>
            </div>
            <div class="col-md">
                <div class="form-group" data-do-when='{ "search_result": ["CHECKBN"], "bn_when": ["AFTER"] }' data-do-action="show">
                    <?php echo wp_scgcge_Label('bn_abn', __('ABN of the Business Name holder', 'scgcge'), '') ?>
                    <?php echo wp_scgcge_Input('text', 'bn_abn', !empty($bn_abn) ? $bn_abn : '', '', 'required', '', '') ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md">
                <div class="form-group" data-do-when='{ "bn_when": ["BEFORE"] }' data-do-action="show">
                    <?php echo wp_scgcge_Label('bn_state', __('State of Registration', 'scgcge'), '') ?>
                    <?php echo wp_scgcge_Select('bn_state', wp_scgcge_get_state_options(!empty($bn_state) ? $bn_state : ''), '', 'required') ?>
                </div>
            </div>
            <div class="col-md">
                <div class="form-group" data-do-when='{ "bn_when": ["BEFORE"] }' data-do-action="show">
                    <?php echo wp_scgcge_Label('bn_number', __('State Business Number', 'scgcge'), '') ?>
                    <?php echo wp_scgcge_Input('text', 'bn_number', !empty($bn_number) ? $bn_number : '', '', 'required', '', '') ?>
                </div>
            </div>
        </div>
    </div><!-- /step1 -->



    <div class="row mt-5">
        <div class="col-md text-left error">
            <span></span>
        </div>
        <div class="col-md text-right">
            <button onclick="window.history.back();" type="button" class="btn btn-secondary mr-1"><?php echo __('Go Back', 'scgcge'); ?></button>
            <button type="submit" class="btn btn-primary"><?php echo __('Next Step', 'scgcge'); ?></button>
            <br><small><?php echo __('Please check spelling and correctness of your data before proceeding', 'scgcge'); ?></small>

        </div>
    </div>
</form>
<?php
 }
 ?>