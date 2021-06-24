<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Company Registration
 *
 * Company Name Registartion Entities Page.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

$error = '';

global $wpdb;
    $mindir = 0;
    $minsha = 0;
    $code = wp_scgcge_getSession();
    if ((isset($_POST['step']) && $_POST['step'] == '2') && wp_scgcge_getSession()) {
        $previousStep = wp_scgcge_toUpper($_POST);
        $code = wp_scgcge_getSession();
        $result = $wpdb->update($wpdb->prefix . 'asic_companies', [
            'ro_care' => isset($previousStep['ro_care']) ? $previousStep['ro_care'] : '',
            'ro_line2' => isset($previousStep['ro_line2']) ? $previousStep['ro_line2'] : '',
            'ro_street' => $previousStep['ro_street'],
            'ro_suburb' => $previousStep['ro_suburb'],
            'ro_state' => $previousStep['ro_state'],
            'ro_postcode' => $previousStep['ro_postcode'],
            'ro_occupy' => $previousStep['ro_occupy'],
            'ro_occupier' => isset($previousStep['ro_occupier']) ? $previousStep['ro_occupier'] : '',
            'ro_same' => $previousStep['ro_same'],
            'ppb_care' => ($previousStep['ro_same'] == 'YES' ? $previousStep['ro_care'] : (isset($previousStep['ppb_care']) ? $previousStep['ppb_care'] : '')),
            'ppb_line2' => ($previousStep['ro_same'] == 'YES' ? $previousStep['ro_line2'] : (isset($previousStep['ppb_line2']) ? $previousStep['ppb_line2'] : '')),
            'ppb_street' => ($previousStep['ro_same'] == 'YES' ? $previousStep['ro_street'] : $previousStep['ppb_street']),
            'ppb_suburb' => ($previousStep['ro_same'] == 'YES' ? $previousStep['ro_suburb'] : $previousStep['ppb_suburb']),
            'ppb_state' => ($previousStep['ro_same'] == 'YES' ? $previousStep['ro_state'] : $previousStep['ppb_state']),
            'ppb_postcode' => ($previousStep['ro_same'] == 'YES' ? $previousStep['ro_postcode'] : $previousStep['ppb_postcode']),
            ], ['code' => $code]);
        $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '$code'";
        $results = $wpdb->get_results($query, ARRAY_A);

        $query = "SELECT user_id FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
        $user_id = $wpdb->get_var($query);

        if (!$user_id == get_current_user_id()) {
            $error = __('You are not authorised to edit the application!', 'scgcge');
        }
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
        $query = "SELECT user_id FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
        $user_id = $wpdb->get_var($query);
        $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '$code'";
        $results = $wpdb->get_results($query, ARRAY_A);

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
        $query2 = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
        $results2 = $wpdb->get_row($query2, ARRAY_A);
        $results2 = stripslashes_deep($results2);
        extract($results2, EXTR_PREFIX_SAME, 'scgcge'); ?>
    <form id="companyRegistrationFormEntities" action="<?php echo wp_scgcge_action_url() . '/review/'?>" class="bootstrapiso" method="POST">
    <input type="hidden" name="step" id="step" value="3">
    
    <div class="row mt-5 mb-3">
        <div class="col-md">
            <div class="progress" style="height: 20px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%">75%</div>
            </div>
            <p class="mb-0 text-right mt-2"><strong><?=!empty($company_name_full) ? $company_name_full : ''?></strong></p>

        </div>
    </div>

    <div class="row mb-4">
        <div class="col-sm-6 mt-3"><?php echo __('A proprietary limited company is required to have at least one Australian resident director and one shareholder. An individual can fulfill both roles if it is necessary.', 'scgcge'); ?> 
        </div>
        <div class="col-sm-6 text-sm-center text-lg-right mt-3">
        <a href="<?php echo wp_scgcge_action_url() . '/entities/add-individual/'; ?>" class="btn btn-sm btn-success mr-1 mb-1"><?php echo __('Add New Individual', 'scgcge'); ?></a>
            <a href="<?php echo wp_scgcge_action_url() . '/entities/add-company/'; ?>" class="btn btn-sm btn-success mb-1"><?php echo __('Add New Corporate Shareholder', 'scgcge'); ?></a>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md"><h2 ><?php echo __('Office Holder/s', 'scgcge'); ?></h2> </div>
    </div>
    <div class="row">
        <div class="col-md">
            <div class="table-responsive-lg">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo __('Name', 'scgcge'); ?></th>
                            <th scope="col"><?php echo __('Address', 'scgcge'); ?></th>
                            <th scope="col"><?php echo __('Role(s)', 'scgcge'); ?></th>
                            <th scope="col" class="text-right"><?php echo __('Actions', 'scgcge'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    <?php
                    if (!empty($results)) {
                        foreach ($results as $key => $value) {
                            if (($value['entity_role_dir'] == 'Y') || ($value['entity_role_sec'] == 'Y')) {
                                $name = $value['entity_first_name'] . ' ' . $value['entity_last_name'];
                                $unit = $value['address_line2'] ? $value['address_line2'] . ', ' : '';
                                $auaddress = $value['address_country'] == 'AU' ? $value['address_state'] . ' ' . $value['address_postcode'] . ', ' : '';
                                $address = $unit . $value['address_street'] . '<br>' . $value['address_suburb'] . ' ' . $auaddress . strtoupper(wp_scgcge_get_country_by_code($value['address_country']));
                                $dob = date('d/m/Y', strtotime($value['entity_birth_date']));
                                $roles = ($value['entity_role_dir'] == 'Y' ? 'Director<br>' : '') . ($value['entity_role_sec'] == 'Y' ? 'Secretary' : '');

                                if ($value['address_country'] == 'AU') {
                                    $mindir++;
                                } ?>

                            <tr>
                                <th scope="row"><?php echo !empty($name) ? $name : ''; ?><br><small class="muted"><?php echo !empty($dob) ? $dob : ''; ?></small></th>
                                <td><?php echo !empty($address) ? $address : ''; ?></td>
                                <td><?php echo !empty($roles) ? $roles : ''; ?></td>
                                <td class="text-right">
                                    <a href="<?php echo wp_scgcge_action_url() . '/entities/' . $value['id'] . '/edit-individual/'?>" class="btn btn-sm btn-outline-info"><?php echo __('Edit', 'scgcge'); ?></a>
                                    <a href="<?php echo wp_scgcge_action_url() . '/processData?id=' . $value['id'] . '&step=delete-entity'; ?>" class="btn btn-sm btn-outline-danger confirm"><?php echo __('Delete', 'scgcge'); ?></a>
                                </td>
                            </tr>

                            <?php
                            }
                        }
                    } ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="row mt-5">
        <div class="col-md"><h2 ><?php echo __('Shareholder/s', 'scgcge'); ?></h2> </div>
    </div>
    <div class="row">
        <div class="col-md">
            <div class="table-responsive-lg">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col"><?php echo __('Name', 'scgcge'); ?></th>
                            <th scope="col"><?php echo __('Address', 'scgcge'); ?></th>
                            <th scope="col"><?php echo __('Shares', 'scgcge'); ?></th>
                            <th scope="col" class="text-right"><?php echo __('Actions', 'scgcge'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    <?php
                    if (!empty($results)) {
                        foreach ($results as $key => $value) {
                            if ($value['entity_role_sha'] == 'Y') {
                                if ($value['entity_type'] == 'IND') {
                                    $link = 'individual';
                                    $name = $value['entity_first_name'] . ' ' . $value['entity_last_name'];
                                } else {
                                    $link = 'company';
                                    $name = $value['entity_company_name'] . '<br><small class="muted">' . strtoupper(wp_scgcge_get_country_by_code($value['entity_company_country'])) . '</small>';
                                }

                                $minsha++;

                                $unit = $value['address_line2'] ? $value['address_line2'] . ', ' : '';
                                $auaddress = $value['address_country'] == 'AU' ? $value['address_state'] . ' ' . $value['address_postcode'] . ', ' : '';
                                $address = $unit . $value['address_street'] . '<br>' . $value['address_suburb'] . ' ' . $auaddress . strtoupper(wp_scgcge_get_country_by_code($value['address_country'])); ?>
                            <tr>
                                <th scope="row"><?php echo !empty($name) ? $name : '' ; ?></th>
                                <td><?php echo !empty($address) ? $address : ''; ?></td>
                                <td>
                               <?php
                                $entities_id = $value['id'];
                                $share_query = "SELECT * FROM $wpdb->prefix" . "asic_entity_shares WHERE code = '$code' and entity_id ='$entities_id'";
                                $share_results = $wpdb->get_results($share_query, ARRAY_A);

                                if (!empty($share_results)) {
                                    foreach ($share_results as $key => $share_result) {
                                        $plural = $share_result['share_number'] > 1 ? 'S' : '';
                                        $shares_entities = $share_result['share_number'] . ' ' . wp_scgcge_get_share($share_result['share_class']) . ' SHARE' . $plural . '<br><small class="muted">' . ($share_result['share_beneficial'] == 'Y' ? 'Beneficially held' : 'Held on behalf of ') . $share_result['share_beneficiary'] . '</small>'; ?>
                                            <span><?php echo !empty($shares_entities) ? $shares_entities : ''; ?></span></br>
                                         
                                <?php
                                    }
                                } ?>
                                </td>
                               

                                <td class="text-right">
                                    <a href="<?php echo wp_scgcge_action_url() . '/entities/' . $value['id'] . '/edit-' . $link . '/'; ?>" class="btn btn-sm btn-outline-info"><?php echo __('Edit', 'scgcge'); ?></a>
                                    <a href="<?php echo wp_scgcge_action_url() . '/processData?id=' . $value['id'] . '&step=delete-entity'; ?>" class="btn btn-sm btn-outline-danger confirm"><?php echo __('Delete', 'scgcge'); ?></a>
                                </td>
                            </tr>

                            <?php
                            }
                        }
                    } ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="messages"></div>

    <input type="hidden" name="mindir" id="mindir" value="<?php echo $mindir?>">
    <input type="hidden" name="minsha" id="minsha" value="<?php echo $minsha?>">

    <div class="row mt-5">
        <div class="col-md text-left error">
            <span></span>
        </div>
        <div class="col-md text-right">
            <a href="<?php echo wp_scgcge_action_url() . '/processData?save=true'; ?>" class="btn btn-secondary mr-1"><?php echo __('Save Form', 'scgcge'); ?></a>
            <a href="<?php echo wp_scgcge_action_url() . '/addresses/'; ?>" class="btn btn-secondary mr-1"><?php echo __('Previous Step', 'scgcge'); ?></a>
            <button type="submit" class="btn btn-primary"><?php echo __('Next Step', 'scgcge'); ?></button>
            <br><small><?php echo __('Please check spelling and correctness of your data before proceeding', 'scgcge'); ?></small>

        </div>
    </div>
</form>
<?php
    }
?>