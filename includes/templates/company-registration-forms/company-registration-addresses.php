<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Company Registration
 *
 * Company Name Registartion Addresses Page.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

$error = '';
global $wpdb;

    if ((isset($_POST['step']) && $_POST['step'] == '1') && wp_scgcge_getSession()) {
        $previousStep = wp_scgcge_toUpper($_POST);
        $code = wp_scgcge_getSession();
        $result = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'jurisdiction' => $previousStep['jurisdiction'],
            'company_type' => 'APTY',
            'company_class' => 'LMSH',
            'company_subclass' => $previousStep['company_subclass'],
            'holding_company' => isset($previousStep['holding_company']) ? $previousStep['holding_company'] : '',
            'holding_name' => isset($previousStep['holding_name']) ? $previousStep['holding_name'] : '',
            'holding_country' => isset($previousStep['holding_country']) ? $previousStep['holding_country'] : '',
            'holding_acn' => isset($previousStep['holding_acn']) ? $previousStep['holding_acn'] : '',
            'holding_abn' => isset($previousStep['holding_abn']) ? $previousStep['holding_abn'] : '',
            'bn_when' => isset($previousStep['bn_when']) ? $previousStep['bn_when'] : '',
            'bn_abn' => isset($previousStep['bn_abn']) ? $previousStep['bn_abn'] : '',
            'bn_state' => isset($previousStep['bn_state']) ? $previousStep['bn_state'] : '',
            'bn_number' => isset($previousStep['bn_number']) ? $previousStep['bn_number'] : '',
            ], ['code' => $code]);
        $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
        $results = $wpdb->get_row($query, ARRAY_A);
        $results = stripslashes_deep($results);
        extract($results, EXTR_PREFIX_SAME, 'scgcge');
        if (!$user_id == get_current_user_id()) {
            $error = __('You are not authorised to edit the application!', 'scgcge');
        }

        $ro_address = [$ro_care, $ro_line2, $ro_street, $ro_suburb, $ro_state, $ro_postcode];
        $ppb_address = [$ppb_care, $ppb_line2, $ppb_street, $ppb_suburb, $ppb_state, $ppb_postcode];
    } elseif (isset($_GET['token']) && preg_replace('/[^a-zA-Z0-9]+/', '', $_GET['token'])) {
        if ($woocommerce->cart->get_cart_contents_count() != 0) {
            $woocommerce->cart->empty_cart();
        }
        $code = wp_scgcge_getSession();
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
        $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
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

        $ro_address = [$ro_care, $ro_line2, $ro_street, $ro_suburb, $ro_state, $ro_postcode];
        $ppb_address = [$ppb_care, $ppb_line2, $ppb_street, $ppb_suburb, $ppb_state, $ppb_postcode];
    } else {
        $error = __('Session expired! Please restart the application process from the homepage.', 'scgcge');
    }

    if ($error != '') {
        echo '<div class="bootstrapiso" style="min-height: 500px; padding-top: 100px;"><div class="alert alert-warning fade show mt-5 mb-5 text-center" role="alert">' . $error . '<br /><a href="' . get_bloginfo('url') . '" class="alert-link">' . __('Go to the homepage', 'scgcge') . ' &rarr;</a></div></div>';
    } else {
        ?>
        
<form id="companyRegistrationFormAddresses" action="<?php echo wp_scgcge_action_url() . '/entities/'; ?>" class="bootstrapiso" method="POST" >
<fieldset>
<div class="row mt-0 mb-5">
        <div class="col-md">
            <div class="progress" style="height: 20px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width: 50%"><?php echo __('50%'); ?></div>
            </div>
            <p class="mb-0 text-right mt-2"><strong><?=!empty($company_name_full) ? $company_name_full : ''?></strong></p>
        </div>
    </div>

    <input type="hidden" name="step" id="step" value="2">
    <div>
        <div class="row">
            <div class="col-md"><h2><?php echo __('Registered Office', 'scgcge'); ?></h2></div>
        </div>

        <?php echo wp_scgcge_AddressAU('ro', !empty($ro_address) ? $ro_address : '')  ?>

        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    <?php echo wp_scgcge_Label('ro_occupy', __('Does this company occupy the above premises?', 'scgcge'), __('By selecting No, I declare that I have written consent from the occupier to use it as the registered office of the company.', 'scgcge')) ?>
                    <?php echo wp_scgcge_Radio('ro_occupy', [['label' => 'Yes', 'value' => 'YES'], ['label' => 'No', 'value' => 'NO']], !empty($ro_occupy) ? $ro_occupy : '', 'required', true) ?>
                </div>
            </div>
            <div class="col-md">
                <div class="form-group" data-do-when='{ "ro_occupy": ["NO"] }' data-do-action="show">
                    <?php echo wp_scgcge_Label('ro_occupier', __('Occupier\'s Name'), 'scgcge') ?>
                    <?php echo wp_scgcge_Input('text', 'ro_occupier', !empty($ro_occupier) ? $ro_occupier : '', '', 'required') ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    <?php echo wp_scgcge_Label('ro_same', __('Is the Principal Place of Business identical with the Registered Office', 'scgcge'), __('By selecting Yes, I declare that the principal place of business where the business is primarily conducted and or decisions are made, is the same as the registered office address.', 'scgcge')) ?>
                    <?php echo wp_scgcge_Radio('ro_same', [['label' => 'Yes', 'value' => 'YES'], ['label' => 'No', 'value' => 'NO']], !empty($ro_same) ? $ro_same : '', 'required', true) ?>
                </div>
            </div>
        </div>

        <div data-do-when='{ "ro_same": ["NO"] }' data-do-action="show">
            <?php echo wp_scgcge_AddressAU('ppb', !empty($ppb_address) ? $ppb_address : '') ?>
        </div>

    </div><!-- /step2 -->
    <div class="row mt-5">
        <div class="col-md text-left error">
            <span></span>
        </div>
        <div class="col-md text-right">
            <a href="<?php echo wp_scgcge_action_url() . '/processData?save=true'; ?>" class="btn btn-secondary mr-1"><?php echo __('Save Form', 'scgcge'); ?></a>
            <a href="<?php echo wp_scgcge_action_url() . '/general-details/'; ?>" class="btn btn-secondary mr-1"><?php echo __('Previous Step', 'scgcge'); ?></a>
            <button type="submit" class="btn btn-primary"><?php echo __('Next Step', 'scgcge'); ?></button>
            <br><small><?php echo __('Please check spelling and correctness of your data before proceeding', 'scgcge'); ?></small>

        </div>
    </div>
</fieldset>
</form>
<?php
    }
?>