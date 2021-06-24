<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
   exit;
}

/**
 * Company Registration
 *
 * Company Name Registartion Review Page.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

$error = '';

global $wpdb;
if (wp_scgcge_getSession()) {
   $code = wp_scgcge_getSession();
   $query = "SELECT * FROM $wpdb->prefix" . "asic_companies WHERE code = '$code'";
   $company = $wpdb->get_row($query, ARRAY_A);
   $query = "SELECT * FROM $wpdb->prefix" . "asic_entities WHERE code = '$code'";
   $entities = $wpdb->get_results($query, ARRAY_A);
   $entities = stripslashes_deep($entities);
   //echo 'continue';
   if (is_array($company)) {
      $company = stripslashes_deep($company);
      extract($company, EXTR_PREFIX_SAME, 'scgcge');
   } else {
      $error = __('Session expired! Please restart the application process from the homepage.', 'scgcge');
   }
   if (!$user_id == get_current_user_id()) {
      $error = __('You are not authorised to edit the application!', 'scgcge');
   }

   $applicant_address = ['', $applicant_line2, $applicant_street, $applicant_suburb, $applicant_state, $applicant_postcode];
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
   <form id="companyRegistrationFormReview" action="<?php echo wp_scgcge_action_url() . '/processData'; ?>" class="bootstrapiso" method="POST">
      <input type="hidden" name="step" id="step" value="review">
      <div class="row mt-5 mb-5">
         <div class="col-md">
            <div class="progress" style="height: 20px;">
               <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">100%</div>
            </div>
            <p class="mb-0 text-right mt-2">
               <strong><?= !empty($company_name_full) ? $company_name_full : '' ?></strong><br>
               <?php
               if (is_user_logged_in()) {
                  echo '<a href="' . home_url() . '/download201?token=' . $code . '" target="_blank">Download Form 201</a>';
               }
               ?>

            </p>
         </div>
      </div>
      <div class="row mb-3">
         <div class="col-md-4"><strong><?php echo __('Company Name / Type', 'scgcge'); ?></strong></div>
         <div class="col-md-8"><?php echo !empty($company['company_name_full']) ? $company['company_name_full'] : ''; ?> / <?php echo  $company['company_subclass'] == 'PROP' ? 'Proprietary Company' : 'Proprietary - Superannuation Trustee Company' ?></div>
      </div>
      <?php
      $holding_company = !empty($company['holding_company']) ? $company['holding_company'] : '';
      if ($holding_company == 'YES') {
         ?>
         <div class="row mb-3">
            <div class="col-md-4"><strong><?php echo __('Ultimate Holding Company', 'scgcge'); ?></strong></div>
            <div class="col-md-8"><?php echo !empty($company['holding_name']) ? $company['holding_name'] : ''; ?> <?php echo ($company['holding_country'] == 'AU') ? '(ACN' . $company['holding_country'] . ')' : '' ?></div>
         </div>
      <?php
   } ?>
      <?php
      $search_result = !empty($company['search_result']) ? $company['search_result'] : '';
      if ($search_result == 'CHECKBN') {
         ?>
         <div class="row mb-3">
            <div class="col-md-4"><strong><?php echo __('Identical Business Name', 'scgcge'); ?></strong></div>
            <div class="col-md-8"><?php echo ($company['bn_when'] == 'AFTER') ? 'ABN ' . $company['bn_abn'] : $company['bn_state'] . ' ' . $company['bn_number']; ?></div>
         </div>
      <?php
   } ?>
      <div class="row mb-3">
         <div class="col-md-4"><strong><?php echo __('Registered Office', 'scgcge'); ?></strong></div>
         <div class="col-md-8">
            <?php echo !empty($company['ro_care']) ? 'Care of ' . $company['ro_care'] . '<br />' : '' ?>
            <?php echo !empty($company['ro_line2']) ? $company['ro_line2'] . ', ' : '' ?><?php echo !empty($company['ro_street']) ? $company['ro_street'] : ''; ?><br />
            <?php echo !empty($company['ro_suburb']) ? $company['ro_suburb'] : ''; ?> <?php echo !empty($company['ro_state']) ? $company['ro_state'] : ''; ?> <?php echo !empty($company['ro_postcode']) ? $company['ro_postcode'] : ''; ?>
         </div>
      </div>
      <div class="row mb-3">
         <div class="col-md-4"><strong><?php echo __('Principal Place of Business', 'scgcge'); ?></strong></div>
         <div class="col-md-8">
            <?php echo !empty($company['ppb_care']) ? __('Care of', 'scgcge') . ' ' . $company['ppb_care'] . '<br />' : '' ?>
            <?php echo !empty($company['ppb_line2']) ? $company['ppb_line2'] . ', ' : ''; ?><?php echo !empty($company['ppb_street']) ? $company['ppb_street'] : ''; ?><br />
            <?php echo !empty($company['ppb_suburb']) ? $company['ppb_suburb'] : ''; ?> <?php echo !empty($company['ppb_state']) ? $company['ppb_state'] : ''; ?> <?php echo !empty($company['ppb_postcode']) ? $company['ppb_postcode'] : ''; ?>
         </div>
      </div>
      <div class="row mb-3">
         <div class="col-md-4"><strong><?php echo __('Office Holder/s', 'scgcge'); ?></strong></div>
         <div class="col-md-8">
            <?php
            if (!empty($entities)) {
               foreach ($entities as $key => $value) {
                  if (($value['entity_role_dir'] == 'Y') || ($value['entity_role_sec'] == 'Y')) {
                     $name = $value['entity_first_name'] . ' ' . $value['entity_last_name'];
                     $unit = !empty($value['address_line2']) ? $value['address_line2'] . ', ' : '';
                     $auaddress = $value['address_country'] == 'AU' ? $value['address_state'] . ' ' . $value['address_postcode'] . ', ' : '';
                     $address = $unit . $value['address_street'] . '<br>' . $value['address_suburb'] . ' ' . $auaddress . strtoupper(wp_scgcge_get_country_by_code($value['address_country']));
                     $dob = date('d/m/Y', strtotime($value['entity_birth_date']));
                     $roles = ($value['entity_role_dir'] == 'Y' ? 'Director, ' : '') . ($value['entity_role_sec'] == 'Y' ? 'Secretary, ' : '');

                     $middle1 = !empty($value['entity_middle_name1']) ? ' ' . $value['entity_middle_name1'] : '';
                     $middle2 = !empty($value['entity_middle_name2']) ? ' ' . $value['entity_middle_name2'] : '';
                     $name = $value['entity_first_name'] . $middle1 . $middle2 . ' ' . $value['entity_last_name'];

                     echo $name . ' - ' . substr($roles, 0, -2) . '<br />' . $address . '<br><small>Born ' . $dob . ' in ' . $value['entity_birth_suburb'] . ', ' . strtoupper(wp_scgcge_get_country_by_code($value['entity_birth_country'])) . '</small><hr>';
                  }
               }
            } ?>
         </div>
      </div>
      <div class="row mb-3">
         <div class="col-md-4"><strong><?php echo __('Shareholders', 'scgcge'); ?></strong></div>
         <div class="col-md-8">
            <?php
            if (!empty($entities)) {
               foreach ($entities as $key => $value) {
                  if ($value['entity_role_sha'] == 'Y') {
                     if ($value['entity_type'] == 'IND') {
                        $middle1 = !empty($value['entity_middle_name1']) ? ' ' . $value['entity_middle_name1'] : '';
                        $middle2 = !empty($value['entity_middle_name2']) ? ' ' . $value['entity_middle_name2'] : '';
                        $name = $value['entity_first_name'] . $middle1 . $middle2 . ' ' . $value['entity_last_name'] . ' - Shareholder';
                     } else {
                        $name = $value['entity_company_name'] . ' - Shareholder';
                     }

                     $unit = !empty($value['address_line2']) ? $value['address_line2'] . ', ' : '';
                     $auaddress = $value['address_country'] == 'AU' ? $value['address_state'] . ' ' . $value['address_postcode'] . ', ' : '';
                     $address = $unit . $value['address_street'] . '<br>' . $value['address_suburb'] . ' ' . $auaddress . strtoupper(wp_scgcge_get_country_by_code(!empty($value['address_country']) ? $value['address_country'] : ''));

                     echo $name . '<br />' . $address . '<br>';

                     $entities_id = $value['id'];
                     $share_query = "SELECT * FROM $wpdb->prefix" . "asic_entity_shares WHERE code = '$code' and entity_id ='$entities_id'";
                     $share_results = $wpdb->get_results($share_query, ARRAY_A);
                     if (!empty($share_results)) {
                        foreach ($share_results as $key => $share_result) {
                           $plural = $share_result['share_number'] > 1 ? 'S' : '';

                           $shares = $share_result['share_number'] . ' ' . wp_scgcge_get_share(!empty($share_result['share_class']) ? $share_result['share_class'] : '') . ' SHARE' . $plural . ' (Paid per share: $' . $share_result['share_paid'] . ' / Unpaid per share: $' . $share_result['share_unpaid'] . ')<br><small class="muted">' . ($share_result['share_beneficial'] == 'Y' ? __('Beneficially held', 'scgcge') : __('Held on behalf of', 'scgcge')) . ' ' . $share_result['share_beneficiary'] . '</small>';

                           echo  $shares . '<hr>';
                        }
                     }
                  }
               }
            } ?>
         </div>
      </div>
      <div class="row mb-4">
         <div class="col-md-4"><strong><?php echo __('Applicant', 'scgcge'); ?></strong></div>
         <div class="col-md-8">
            <?php
            $applicant = !empty($applicant) ? $applicant : '';
            $values = "<option value='' " . ($applicant == '' ? 'selected' : '') . '>' . __('Select Applicant', 'scgcge') . '</option>';
            if (!empty($entities)) {
               foreach ($entities as $key => $value) {
                  if ($value['entity_role_dir'] == 'Y' || $value['entity_role_sec'] == 'Y' || ($value['entity_role_sha'] == 'Y' && $value['entity_type'] == 'IND')) {
                     $unit = !empty($value['address_line2']) ? $value['address_line2'] . ', ' : '';
                     $auaddress = $value['address_country'] == 'AU' ? $value['address_state'] . ' ' . $value['address_postcode'] . ', ' : '';
                     $address = $unit . $value['address_street'] . '<br>' . $value['address_suburb'] . ' ' . $auaddress . strtoupper(wp_scgcge_get_country_by_code(!empty($value['address_country']) ? $value['address_country'] : ''));
                     $middle1 = !empty($value['entity_middle_name1']) ? ' ' . $value['entity_middle_name1'] : '';
                     $middle2 = !empty($value['entity_middle_name2']) ? ' ' . $value['entity_middle_name2'] : '';
                     $name = $value['entity_first_name'] . $middle1 . $middle2 . ' ' . $value['entity_last_name'];

                     $values .= "<option value='" . $value['id'] . "' " . ($applicant == $value['id'] ? 'selected' : '') . '>' . $name . '</option>';
                  }
               }
            }
            $values .= "<option value='0' " . ($applicant == '0' ? 'selected' : '') . '>' . __('OTHER', 'scgcge') . '</option>';
            echo wp_scgcge_Select('applicant', !empty($values) ? $values : ''); ?>
            <div class="mt-5 row" data-do-when='{ "applicant": ["0"] }' data-do-action="show" style="margin-left: -5px; margin-right: -5px;">
               <div class="col-md-4">
                  <?php echo wp_scgcge_Input('text', 'applicant_first_name', !empty($applicant_first_name) ? $applicant_first_name : '', '', 'required', __('First Name', 'scgcge'), 'mb-2', '', '', '') ?>
               </div>
               <div class="col-md-4">
                  <?php echo wp_scgcge_Input('text', 'applicant_middle_name', !empty($applicant_middle_name) ? $applicant_middle_name : '', '', '', __('Middle Name', 'scgcge'), 'mb-2', '', '', '') ?>
               </div>
               <div class="col-md-4">
                  <?php echo wp_scgcge_Input('text', 'applicant_last_name', !empty($applicant_last_name) ? $applicant_last_name : '', '', 'required', __('Last Name', 'scgcge'), 'mb-3', '', '', '') ?>
               </div>
            </div>
            <div data-do-when='{ "applicant": ["0"] }' data-do-action="show" class="mt-3" style="margin-left: 10px; margin-right: 10px;">
               <?php echo wp_scgcge_AddressAU('applicant', !empty($applicant_address) ? $applicant_address : '', __('Address', 'scgcge')) ?>
            </div>
         </div>
      </div>
      <div class="row mb-3">
         <div class="col-md-4"><strong><?php echo __('Acknowledgements & Declarations', 'scgcge'); ?></strong></div>
         <div class="col-md-8">
            <div style="border: 1px solid #e5e5e5; height: 200px; overflow: auto; padding: 10px;">
               <p><?php echo __('By completing and submitting this form to apply for ASIC registration of a company, I hereby declare that I have provided accurate information and that I have done so with the express permission and signed consent of all officeholders, members and occupiers listed on the form. I further declare that I, and or / or the aforementioned officeholders, members and occupiers understand the obligations to be appointed in the applicable position of the company being applied for.', 'scgcge'); ?></p>
               <p><?php echo sprintf(__('I authorise %s to transmit the information I have supplied within this form to any relevant third party in accordance with the privacy policy of the third party. Third parties include, but may not be limited to: Australian Securities and Investments Commission (ASIC), the Australian Business Register (ABR) and the Australian Taxation Office (ATO), and our printing suppliers. For more information on the privacy policies of ASIC and the ABR.', 'scgcge'), $_SERVER['HTTP_HOST']); ?></p>
            </div>
         </div>
      </div>
      <div class="row mb-3">
         <div class="col-md-4"></div>
         <div class="col-md-8">
            <?php echo wp_scgcge_Checkbox('agree', [['label' => __('I agree with the terms and conditions', 'scgcge'), 'value' => 'Y']], !empty($agree) ? $agree : '', '', true, '') ?>
         </div>
      </div>
      <div class="row mb-3">
         <div class="col-md-4"></div>
         <div class="col-md-8">
            <div class="alert alert-danger" role="alert">
               <?php echo __('Please double check that all details provided are 100% correct before submitting.', 'scgcge'); ?>
            </div>
         </div>
      </div>
      <div class="row mt-5">
         <div class="col-md text-left error">
            <span></span>
         </div>
         <div class="col-md text-right">
            <a href="<?php echo wp_scgcge_action_url() . '/processData?save=true'; ?>" class="btn btn-secondary mr-1"><?php echo __('Save Form', 'scgcge'); ?></a>
            <a href="<?php echo wp_scgcge_action_url() . '/entities/'; ?>" class="btn btn-secondary mr-1"><?php echo __('Previous Step', 'scgcge'); ?></a>
            <?php
            $company_status = !empty($company['status']) ? $company['status'] : '';
            if ($company_status == 'validation failed' || $company_status == 'rejected') {
               echo '<button type="submit" class="btn btn-primary">' . __('Submit Application', 'scgcge') . '</button>';
            } else {
               echo '<button type="submit" class="btn btn-primary">' . __('Go To Cart', 'scgcge') . '</button>';
            } ?>
            <br><small><?php echo __('Please check spelling and correctness of your data before proceeding', 'scgcge'); ?></small>

         </div>
      </div>
   </form>
<?php
}
?>