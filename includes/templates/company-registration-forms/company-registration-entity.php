<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Company Registration
 *
 * Company Name Registartion entity Page.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
$error = '';

global $wpdb;
$code = wp_scgcge_getSession();
if (wp_scgcge_getSession()) {
    $code = wp_scgcge_getSession();

    if (is_int(intval(wp_scgcge_previous_segment())) && intval(wp_scgcge_previous_segment()) != 0) {
        $id = wp_scgcge_previous_segment();
        $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '$code' and id = '$id'";
        $results = $wpdb->get_row($query, ARRAY_A);

        if (is_array($results)) {
            $results = stripslashes_deep($results);
            extract($results, EXTR_PREFIX_SAME, 'scgcge');
            $entity_birth_date = date('d/m/Y', strtotime($entity_birth_date));
            $address = [$address_care, $address_line2, $address_street, $address_suburb, $address_state, $address_postcode, $address_country];
        } else {
            $error = __('Session expired! Please restart the application process from the homepage.', 'scgcge');
        }

        $share_query = "SELECT * FROM $wpdb->prefix" . "asic_entity_shares WHERE code = '$code' and entity_id = '$id'";
        $share_results = $wpdb->get_results($share_query, ARRAY_A);
    } else {
        $results = [
            'entity_type' => '',
            'entity_role_dir' => '',
            'entity_role_sec' => '',
            'entity_role_sha' => '',
            'entity_first_name' => '',
            'entity_middle_name1' => '',
            'entity_middle_name2' => '',
            'entity_last_name' => '',
            'entity_former' => '',
            'entity_former_first_name' => '',
            'entity_former_middle_name1' => '',
            'entity_former_middle_name2' => '',
            'entity_former_last_name' => '',
            'entity_birth_date' => '',
            'entity_birth_country' => '',
            'entity_birth_state' => '',
            'entity_birth_suburb' => '',
            'share_details' => [
                'share_class' => '',
                'share_number' => '1',
                'share_paid' => '1',
                'share_paid_total' => '',
                'share_unpaid' => '0',
                'share_unpaid_total' => '',
                'share_beneficial' => '',
                'share_beneficiary' => ''
            ],
            'entity_company_name' => '',
            'entity_company_country' => '',
            'entity_company_acn' => '',
            'address_care' => '',
            'address_line2' => '',
            'address_street' => '',
            'address_suburb' => '',
            'address_state' => '',
            'address_postcode' => '',
            'address_country' => ''
        ];
        extract($results, EXTR_PREFIX_SAME, 'scgcge');
        $address = [$address_care, $address_line2, $address_street, $address_suburb, $address_state, $address_postcode, $address_country];
        $share_results = [$share_details['share_class'], $share_details['share_number'], $share_details['share_paid'], $share_details['share_paid_total'], $share_details['share_unpaid'], $share_details['share_unpaid_total'], $share_details['share_beneficial'], $share_details['share_beneficiary']];
    }
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
} else {
    $error = __('Session expired! Please restart the application process from the homepage.', 'scgcge');
}

if ($error != '') {
    echo '<div class="bootstrapiso" style="min-height: 500px; padding-top: 100px;"><div class="alert alert-warning fade show mt-5 mb-5 text-center" role="alert">' . $error . '<br /><a href="' . get_bloginfo('url') . '" class="alert-link">' . __('Go to the homepage', 'scgcge') . ' &rarr;</a></div></div>';
} else {
?>
    <form id="companyRegistrationFormEntity" action="<?php echo wp_scgcge_action_url() . '/processData'; ?>" class="bootstrapiso" autocomplete='off' method="POST">
        <input autocomplete="false" name="hidden" type="text" style="display:none;">

        <?php
        if (wp_scgcge_current_segment() == 'add-individual') {
            echo '<input type="hidden" name="step" id="step" value="add-individual">';
            echo '<input type="hidden" name="entity_type" id="entity_type" value="IND">';
            $page_title = __('Add Individual', 'scgcge');
        } elseif (wp_scgcge_current_segment() == 'add-company') {
            echo '<input type="hidden" name="step" id="step" value="add-company">';
            echo '<input type="hidden" name="entity_type" id="entity_type" value="ORG">';
            echo '<input type="hidden" name="entity_role_sha" id="entity_role_sha" value="Y" class="roles-group">';
            $page_title = __('Add Corporate Shareholder', 'scgcge');
        } elseif (wp_scgcge_current_segment() == 'edit-individual') {
            echo '<input type="hidden" name="step" id="step" value="edit-individual">';
            echo '<input type="hidden" name="id" id="id" value="' . intval(wp_scgcge_previous_segment()) . '">';
            echo '<input type="hidden" name="entity_type" id="entity_type" value="IND">';
            $page_title = __('Edit Individual', 'scgcge');
        } elseif (wp_scgcge_current_segment() == 'edit-company') {
            echo '<input type="hidden" name="step" id="step" value="edit-company">';
            echo '<input type="hidden" name="entity_type" id="entity_type" value="ORG">';
            echo '<input type="hidden" name="id" id="id" value="' . intval(wp_scgcge_previous_segment()) . '">';
            echo '<input type="hidden" name="entity_role_sha" id="entity_role_sha" value="Y" class="roles-group">';
            $page_title = __('Edit Corporate Shareholder', 'scgcge');
        }

        $query2 = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
        $results2 = $wpdb->get_row($query2, ARRAY_A);
        $results2 = stripslashes_deep($results2);
        extract($results2, EXTR_PREFIX_SAME, 'scgcge'); ?>

        <div class="row mt-5 mb-5">
            <div class="col-md">
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%">75%</div>
                </div>
                <p class="mb-0 text-right mt-2"><strong><?= !empty($company_name_full) ? $company_name_full : '' ?></strong></p>

            </div>
        </div>

        <div>
            <div class="entities">
                <div class="row mb-4">
                    <div class="col-md">
                        <h2><?php echo !empty($page_title) ? $page_title : ''; ?> </h2>
                    </div>
                </div>

                <div data-do-when='{ "entity_type": ["IND"] }' data-do-action="show">
                    <?php echo wp_scgcge_InputLine('text', 'entity_first_name', !empty($entity_first_name) ? $entity_first_name : '', '', 'required', '', '', '', '', 'First Name'); ?>
                    <div class="form-group row">
                        <label for="entity_middle_name1" class="col-sm-2 col-form-label"><?php echo __('Middle Name(s)', 'scgcge'); ?></label>
                        <div class="col-sm-5">
                            <input type="text" name="entity_middle_name1" value="<?php echo !empty($entity_middle_name1) ? $entity_middle_name1 : ''; ?>" class="form-control" id="entity_middle_name1" aria-describedby="entity_middle_name1Help" placeholder="">
                        </div>
                        <div class="col-sm-5">
                            <input type="text" name="entity_middle_name2" value="<?php echo !empty($entity_middle_name2) ? $entity_middle_name2 : ''; ?>"" class=" form-control" id="entity_middle_name2" aria-describedby="entity_middle_name2Help" placeholder="">
                        </div>
                    </div>
                    <?php echo wp_scgcge_InputLine('text', 'entity_last_name', !empty($entity_last_name) ? $entity_last_name : '', '', 'required', '', '', '', '', 'Last Name'); ?>
                </div>

                <div class="row mt-5 mb-3" data-do-when='{ "entity_type": ["IND"] }' data-do-action="show">
                    <div class="col-md">
                        <div class="form-group">
                            <?php echo wp_scgcge_Label('entity_roles', __('Role(s) within the company', 'scgcge'), ''); ?>
                            <?php echo wp_scgcge_Checkbox('entity_role_dir', [['label' => 'Director', 'value' => 'Y']], !empty($entity_role_dir) ? $entity_role_dir : '', '', true, 'roles-group'); ?>
                            <?php echo wp_scgcge_Checkbox('entity_role_sha', [['label' => 'Shareholder', 'value' => 'Y']], !empty($entity_role_sha) ? $entity_role_sha : '', '', true, 'roles-group'); ?>
                            <?php echo wp_scgcge_Checkbox('entity_role_sec', [['label' => 'Secretary', 'value' => 'Y']], !empty($entity_role_sec) ? $entity_role_sec : '', '', true, 'roles-group'); ?>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="form-group" data-do-when='{"entity_role_dir": ["Y"]} || {"entity_role_sec": ["Y"]}' data-do-action="show">
                            <?php echo wp_scgcge_Label('entity_former', __('Does the office holder have a former name?', 'scgcge'), ''); ?>
                            <?php echo wp_scgcge_Radio('entity_former', [['label' => 'Yes', 'value' => 'Y'], ['label' => 'No', 'value' => 'N']], !empty($entity_former) ? $entity_former : '', 'required', true); ?>
                        </div>
                    </div>
                </div>

                <div data-do-when='{"entity_role_dir": ["Y"]} || {"entity_role_sec": ["Y"]}' data-do-action="show">

                    <div data-do-when='{ "entity_former": ["Y"] }' data-do-action="show" class="mb-5">
                        <?php echo wp_scgcge_InputLine('text', 'entity_former_first_name', !empty($entity_former_first_name) ? $entity_former_first_name : '', '', 'required', '', '', '', '', 'First Name'); ?>
                        <div class="form-group row">
                            <label for="entity_former_middle_name1" class="col-sm-2 col-form-label"><?php echo __('Middle Name(s)', 'scgcge'); ?></label>
                            <div class="col-sm-5">
                                <input type="text" value="<?php echo !empty($entity_former_middle_name1) ? $entity_former_middle_name1 : ''; ?>" name="entity_former_middle_name1" class="form-control" id="entity_former_middle_name1" aria-describedby="entity_former_middle_name1Help" placeholder="">
                            </div>
                            <div class="col-sm-5">
                                <input type="text" value="<?php echo !empty($entity_former_middle_name2) ? $entity_former_middle_name2 : ''; ?>" name="entity_former_middle_name2" class="form-control" id="entity_former_middle_name2" aria-describedby="entity_former_middle_name2Help" placeholder="">
                            </div>
                        </div>
                        <?php echo wp_scgcge_InputLine('text', 'entity_former_last_name', !empty($entity_former_last_name) ? $entity_former_last_name : '', '', 'required', '', '', '', '', 'Last Name'); ?>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md">
                            <?php echo wp_scgcge_Label('entity_birth_date', __('Birth Details', 'scgcge'), ''); ?>
                            <div class="form-group">
                                <?php echo wp_scgcge_Input('text', 'entity_birth_date', !empty($entity_birth_date) ? $entity_birth_date : '', '', 'required', '', 'birthdate'); ?>
                                <?php echo wp_scgcge_Help('entity_birth_date', __('Date of Birth (dd/mm/yyyy)', 'scgcge')); ?>
                            </div>
                        </div>
                        <div class="col-md">
                            <?php echo wp_scgcge_Label('entity_birth_country', '&nbsp;', ''); ?>
                            <div class="form-group">
                                <?php echo wp_scgcge_Select('entity_birth_country', wp_scgcge_get_countries_options(!empty($entity_birth_country) ? $entity_birth_country : ''), '', 'required', '', ''); ?>
                                <?php echo wp_scgcge_Help('entity_birth_country', __('Country of Birth', 'scgcge')); ?>
                            </div>
                        </div>
                        <div class="col-md" data-do-when='{ "entity_birth_country": ["AU"] }' data-do-action="show">
                            <?php echo wp_scgcge_Label('entity_birth_country', '&nbsp;', ''); ?>
                            <div class="form-group">
                                <?php echo wp_scgcge_Select('entity_birth_state', wp_scgcge_get_state_options(!empty($entity_birth_state) ? $entity_birth_state : ''), '', 'required', '', ''); ?>
                                <?php echo wp_scgcge_Help('entity_birth_state', __('State of Birth', 'scgcge')); ?>
                            </div>
                        </div>
                        <div class="col-md">
                            <?php echo wp_scgcge_Label('entity_birth_suburb', '&nbsp;', ''); ?>
                            <div class="form-group">
                                <?php echo wp_scgcge_Input('text', 'entity_birth_suburb', !empty($entity_birth_suburb) ? $entity_birth_suburb : '', '', 'required', '', ''); ?>
                                <?php echo wp_scgcge_Help('entity_birth_suburb', __('City or Suburb', 'scgcge')); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div data-do-when='{ "entity_type": ["ORG"] }' data-do-action="show">
                    <div class="row">
                        <div class="col-md">
                            <div class="form-group">
                                <?php echo wp_scgcge_Label('entity_company_name', __('Company Name', 'scgcge'), ''); ?>
                                <?php echo wp_scgcge_Input('text', 'entity_company_name', !empty($entity_company_name) ? $entity_company_name : '', '', 'required', '', ''); ?>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="form-group">
                                <?php echo wp_scgcge_Label('entity_company_country', __('Country of Incorporation', 'scgcge')); ?>
                                <?php echo wp_scgcge_Select('entity_company_country', wp_scgcge_get_countries_options(!empty($entity_company_country) ? $entity_company_country : ''), '', 'required', '', ''); ?>
                            </div>
                        </div>
                        <div class="col-md" data-do-when='{ "entity_company_country": ["AU"] }' data-do-action="show">
                            <div class="form-group">
                                <?php echo wp_scgcge_Label('entity_company_acn', __('Company ACN', 'scgcge'), ''); ?>
                                <?php echo wp_scgcge_Input('text', 'entity_company_acn', !empty($entity_company_acn) ? $entity_company_acn : '', '', 'required', '', ''); ?>
                            </div>
                        </div>
                    </div>
                </div>


                <div data-do-when='{ "entity_type": ["IND", "ORG"] }' data-do-action="show" class="mt-3">
                    <?php echo wp_scgcge_AddressGeneral('address', !empty($address) ? $address : '', 'Address'); ?>

                </div>

                <div class="type_container" id="type_container">

                    <?php
                    if (wp_scgcge_current_segment() == 'add-company' || wp_scgcge_current_segment() == 'add-individual') {
                    ?>
                        <div data-do-when='{ "entity_role_sha": ["Y"] }' data-do-action="show" class="mt-3 type-row shareallow">

                            <div class="row">
                                <a class="add-type pull-right" href="javascript: void(0)" tiitle="Click to add more"><img src="<?php echo WP_SCGC_GE_PLUGIN_URL; ?>includes/images/add.png" alt="<?php echo __('Add', 'scgcge'); ?>" class="ww_olt_options_img_add" />Add Share</a>

                                <div class="col-md">
                                    <?php echo wp_scgcge_Label('share_class', __('Share Allocation', 'scgcge'), ''); ?>
                                    <div class="form-group">
                                        <?php echo wp_scgcge_Select('share_class', wp_scgcge_get_share_options(!empty($share_class) ? $share_class : ''), '', 'required', '', ''); ?>
                                        <?php echo wp_scgcge_Help('share_class', __('Share Class', 'scgcge')); ?>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <?php echo wp_scgcge_Label('share_number', '&nbsp;', ''); ?>
                                    <div class="form-group">
                                        <?php echo wp_scgcge_Input('text', 'share_number', !empty($share_number) ? $share_number : '', '', 'required', '', ''); ?>
                                        <?php echo wp_scgcge_Help('share_number', __('Number of Shares', 'scgcge')); ?>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <?php echo wp_scgcge_Label('share_paid', '&nbsp;', ''); ?>
                                    <div class="form-group">
                                        <?php echo wp_scgcge_InputIcon('text', 'share_paid', !empty($share_paid) ? $share_paid : '', '', 'required', '', ''); ?>
                                        <?php echo wp_scgcge_Help('share_paid', __('Paid per Share (eg. $1)', 'scgcge')); ?>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <?php echo wp_scgcge_Label('share_unpaid', '&nbsp;', ''); ?>
                                    <div class="form-group">
                                        <?php echo wp_scgcge_InputIcon('text', 'share_unpaid', !empty($share_unpaid) ? $share_unpaid : '', '', 'required', '', ''); ?>
                                        <?php echo wp_scgcge_Help('share_unpaid', __('Unpaid per Share (eg. $0)', 'scgcge')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md">
                                    <div class="form-group">
                                        <?php echo wp_scgcge_Label('share_beneficial', __('Will you be the owner of these shares?', 'scgcge'), __('If you hold the shares on behalf of someone else, select NO; otherwise, select YES', 'scgcge')); ?>
                                        <?php
                                        $key = 0;
                                        $radionID = 'share_beneficial_' . $key;
                                        $radioName = 'share_details[' . $key . '][share_beneficial]';
                                        echo wp_scgcge_share_Radio($radionID, $radioName, [['label' => 'Yes', 'value' => 'Y'], ['label' => 'No', 'value' => 'N']], !empty($share_result['share_beneficial']) ? $share_result['share_beneficial'] : '', 'share-beneficials', 'required', true, $key); ?>
                                    </div>
                                </div>
                                <div class="col-md">
                                    <div class="form-group share_beneficials_hide" id="share_beneficials_show">
                                        <?php echo wp_scgcge_Label('share_beneficiary', __('Who will be the owner of these shares?', 'scgcge'), __('Beneficial Owner\'s name', 'scgcge')); ?>
                                        <?php echo wp_scgcge_Input('text', 'share_beneficiary', !empty($share_beneficiary) ? $share_beneficiary : '', '', 'required', '', ''); ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    <?php
                    } else { // end add, start edit
                    ?>
                        <div data-do-when='{ "entity_role_sha": ["Y"] }' data-do-action="show" class="mt-3">
                            <a class="add-type pull-right" href="javascript: void(0)" tiitle="Click to add more"><img src="<?php echo WP_SCGC_GE_PLUGIN_URL; ?>includes/images/add.png" alt="<?php echo __('Add', 'scgcge'); ?>" class="ww_olt_options_img_add" />Add Share </a>
                        </div>
                        <?php
                        $id = wp_scgcge_previous_segment();
                        if (!empty($share_results)) {
                            foreach ($share_results as $key => $share_result) {
                        ?>


                                <div data-do-when='{ "entity_role_sha": ["Y"] }' data-do-action="show" class="mt-3 type-row shareallow">


                                    <div class="row">
                                        <input type="hidden" name="share_id" value="<?php echo $share_result['id']; ?>">

                                        <div class="col-md">
                                            <?php echo wp_scgcge_Label('share_class', __('Share Allocation', 'scgcge'), ''); ?>
                                            <div class="form-group">
                                                <?php echo wp_scgcge_Select('share_class', wp_scgcge_get_share_options(!empty($share_result['share_class']) ? $share_result['share_class'] : ''), '', 'required', '', ''); ?>
                                                <?php echo wp_scgcge_Help('share_class', __('Share Class', 'scgcge')); ?>
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <?php echo wp_scgcge_Label('share_number', '&nbsp;', ''); ?>
                                            <div class="form-group">
                                                <?php echo wp_scgcge_Input('text', 'share_number', !empty($share_result['share_number']) ? $share_result['share_number'] : '', '', 'required', '', ''); ?>
                                                <?php echo wp_scgcge_Help('share_number', __('Number of Shares', 'scgcge')); ?>
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <?php echo wp_scgcge_Label('share_paid', '&nbsp;', ''); ?>
                                            <div class="form-group">
                                                <?php echo wp_scgcge_InputIcon('text', 'share_paid', !empty($share_result['share_paid']) ? $share_result['share_paid'] : '', '', 'required', '', ''); ?>
                                                <?php echo wp_scgcge_Help('share_paid', __('Paid per Share (eg. $1)', 'scgcge')); ?>
                                            </div>
                                        </div>
                                        <div class="col-md">
                                            <?php echo wp_scgcge_Label('share_unpaid', '&nbsp;', ''); ?>
                                            <div class="form-group">
                                                <?php echo wp_scgcge_InputIcon('text', 'share_unpaid', !empty($share_result['share_unpaid']) ? $share_result['share_unpaid'] : '', '', 'required', '', ''); ?>
                                                <?php echo wp_scgcge_Help('share_unpaid', __('Unpaid per Share (eg. $0)', 'scgcge')); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md">
                                            <div class="form-group">
                                                <?php echo wp_scgcge_Label('share_beneficial', __('Will you be the owner of these shares?', 'scgcge'), __('If you hold the shares on behalf of someone else, select NO; otherwise, select YES', 'scgcge')); ?>
                                                <?php
                                                $radionID = 'share_beneficial_' . $key;
                                                $radioName = 'share_details[' . $key . '][share_beneficial]';
                                                echo wp_scgcge_share_Radio($radionID, $radioName, [['label' => 'Yes', 'value' => 'Y'], ['label' => 'No', 'value' => 'N']], !empty($share_result['share_beneficial']) ? $share_result['share_beneficial'] : '', 'share-beneficials', 'required', true, $key); ?>
                                            </div>
                                        </div>
                                        <?php $class = ($share_result['share_beneficial'] == 'Y') ? 'share_beneficials_hide' : 'share_beneficials_show'; ?>
                                        <div class="col-md">
                                            <div class="form-group <?php echo $class; ?>" id="share_beneficials_show">
                                                <?php echo wp_scgcge_Label('share_beneficiary', __('Who will be the owner of these shares?', 'scgcge'), __('Beneficial Owner\'s name', 'scgcge')); ?>
                                                <?php echo wp_scgcge_Input('text', 'share_beneficiary', !empty($share_result['share_beneficiary']) ? $share_result['share_beneficiary'] : '', '', 'required', '', ''); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <a class="remove-type pull-right get_data_<?php echo $key; ?>" targetDiv="" id="<?php echo $id; ?>" data-id="<?php echo $share_result['id']; ?>" href="javascript: void(0)"><img src="<?php echo WP_SCGC_GE_PLUGIN_URL; ?>includes/images/remove.png" alt="<?php echo __('Delete', 'scgcge'); ?>" />Delete Share</a>
                                    </div>

                                </div>
                            <?php
                            }
                        } else {
                            ?>



                            <div data-do-when='{ "entity_role_sha": ["Y"] }' data-do-action="show" class="mt-3 type-row shareallow">

                                <div class="row">

                                    <div class="col-md">
                                        <?php echo wp_scgcge_Label('share_class', __('Share Allocation', 'scgcge'), ''); ?>
                                        <div class="form-group">
                                            <?php echo wp_scgcge_Select('share_class', wp_scgcge_get_share_options(!empty($share_class) ? $share_class : ''), '', 'required', '', ''); ?>
                                            <?php echo wp_scgcge_Help('share_class', __('Share Class', 'scgcge')); ?>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <?php echo wp_scgcge_Label('share_number', '&nbsp;', ''); ?>
                                        <div class="form-group">
                                            <?php echo wp_scgcge_Input('text', 'share_number', !empty($share_number) ? $share_number : '', '', 'required', '', ''); ?>
                                            <?php echo wp_scgcge_Help('share_number', __('Number of Shares', 'scgcge')); ?>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <?php echo wp_scgcge_Label('share_paid', '&nbsp;', ''); ?>
                                        <div class="form-group">
                                            <?php echo wp_scgcge_InputIcon('text', 'share_paid', !empty($share_paid) ? $share_paid : '', '', 'required', '', ''); ?>
                                            <?php echo wp_scgcge_Help('share_paid', __('Paid per Share (eg. $1)', 'scgcge')); ?>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <?php echo wp_scgcge_Label('share_unpaid', '&nbsp;', ''); ?>
                                        <div class="form-group">
                                            <?php echo wp_scgcge_InputIcon('text', 'share_unpaid', !empty($share_unpaid) ? $share_unpaid : '', '', 'required', '', ''); ?>
                                            <?php echo wp_scgcge_Help('share_unpaid', __('Unpaid per Share (eg. $0)', 'scgcge')); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md">
                                        <div class="form-group">
                                            <?php echo wp_scgcge_Label('share_beneficial', __('Will you be the owner of these shares?', 'scgcge'), __('If you hold the shares on behalf of someone else, select NO; otherwise, select YES', 'scgcge')); ?>
                                            <?php
                                            $key = 0;
                                            $radionID = 'share_beneficial_' . $key;
                                            $radioName = 'share_details[' . $key . '][share_beneficial]';
                                            echo wp_scgcge_share_Radio($radionID, $radioName, [['label' => 'Yes', 'value' => 'Y'], ['label' => 'No', 'value' => 'N']], !empty($share_result['share_beneficial']) ? $share_result['share_beneficial'] : '', 'share-beneficials', 'required', true, $key); ?>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <div class="form-group share_beneficials_hide" id="share_beneficials_show">
                                            <?php echo wp_scgcge_Label('share_beneficiary', __('Who will be the owner of these shares?', 'scgcge'), __('Beneficial Owner\'s name', 'scgcge')); ?>
                                            <?php echo wp_scgcge_Input('text', 'share_beneficiary', !empty($share_beneficiary) ? $share_beneficiary : '', '', 'required', '', ''); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php






                        }
                    } ?>
                </div>
                <div id="type-container" class="hide">
                    <div data-do-when='{ "entity_role_sha": ["Y"] }' data-do-action="show" class="mt-3 type-row shareallow">
                        <input type="hidden" name="share_id" value="<?php echo !empty($share_id) ? $share_id : ''; ?>">
                        <div class="row">
                            <div class="col-md">
                                <?php echo wp_scgcge_Label('share_class', __('Share Allocation', 'scgcge'), ''); ?>
                                <div class="form-group">
                                    <?php echo wp_scgcge_Select('share_class', wp_scgcge_get_share_options(!empty($share_class) ? $share_class : ''), '', 'required', '', ''); ?>
                                    <?php echo wp_scgcge_Help('share_class', __('Share Class', 'scgcge')); ?>
                                </div>
                            </div>
                            <div class="col-md">
                                <?php echo wp_scgcge_Label('share_number', '&nbsp;', ''); ?>
                                <div class="form-group">
                                    <?php echo wp_scgcge_Input('text', 'share_number', !empty($share_number) ? $share_number : '', '', 'required', '', ''); ?>
                                    <?php echo wp_scgcge_Help('share_number', __('Number of Shares', 'scgcge')); ?>
                                </div>
                            </div>
                            <div class="col-md">
                                <?php echo wp_scgcge_Label('share_paid', '&nbsp;', ''); ?>
                                <div class="form-group">
                                    <?php echo wp_scgcge_InputIcon('text', 'share_paid', !empty($share_paid) ? $share_paid : '', '', 'required', '', ''); ?>
                                    <?php echo wp_scgcge_Help('share_paid', __('Paid per Share (eg. $1)', 'scgcge')); ?>
                                </div>
                            </div>
                            <div class="col-md">
                                <?php echo wp_scgcge_Label('share_unpaid', '&nbsp;', ''); ?>
                                <div class="form-group">
                                    <?php echo wp_scgcge_InputIcon('text', 'share_unpaid', !empty($share_unpaid) ? $share_unpaid : '', '', 'required', '', ''); ?>
                                    <?php echo wp_scgcge_Help('share_unpaid', __('Unpaid per Share (eg. $0)', 'scgcge')); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md">
                                <div class="form-group">
                                    <?php echo wp_scgcge_Label('share_beneficial', __('Will you be the owner of these shares?', 'scgcge'), __('If you hold the shares on behalf of someone else, select NO; otherwise, select YES', 'scgcge')); ?>
                                    <?php echo wp_scgcge_Radio('share_beneficial', [['label' => 'Yes', 'value' => 'Y'], ['label' => 'No', 'value' => 'N']], !empty($share_beneficial) ? $share_beneficial : '', 'required', true); ?>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="form-group share_beneficials_hide" id="share_beneficials_show">
                                    <?php echo wp_scgcge_Label('share_beneficiary', __('Who will be the owner of these shares?', 'scgcge'), __('Beneficial Owner\'s name', 'scgcge')); ?>
                                    <?php echo wp_scgcge_Input('text', 'share_beneficiary', !empty($share_beneficiary) ? $share_beneficiary : '', '', 'required', '', ''); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <a class="remove-type pull-right" targetDiv="" data-id="0" href="javascript: void(0)"><img src="<?php echo WP_SCGC_GE_PLUGIN_URL; ?>includes/images/remove.png" alt="<?php echo __('Delete', 'scgcge'); ?>" />Delete Share</a>
                        </div>
                    </div>
                </div>

            </div><!-- /entities1 -->

        </div><!-- /step3 -->


        <div class="row mt-5">
            <div class="col-md text-left error">
                <span></span>
            </div>
            <div class="col-md text-right">
                <a href="<?php echo wp_scgcge_action_url() . '/entities/'; ?>" class="btn btn-secondary mr-1"><?php echo __('Previous Step', 'scgcge'); ?></a>
                <button type="submit" class="btn btn-primary"><?php echo __('Save Entity', 'scgcge'); ?></button>
                <br><small><?php echo __('Please check spelling and correctness of your data before proceeding', 'scgcge'); ?></small>

            </div>
        </div>
    </form>
<?php
}
