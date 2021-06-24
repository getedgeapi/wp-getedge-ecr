<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Company Registration
 *
 * Company Name Registartion End Point URL Get All Query Variable.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
?>
<form action="<?php echo wp_scgcge_action_url() . '/general-details/'; ?>" method="post" class="searchflexiform">
    <div class="searchflexi">
        <div class="company_name_container">
            <label for="company_name"><?php echo __('Company Name', 'scgcge') ?></label>
            <input id="company_name" type="text" name="company_name" value="" class="medium company_name" aria-required="true" aria-invalid="false">
            <label for="acn_only" ><input name="acn_only" type="checkbox" value="yes" id="acn_only" class="acn_only"><?php echo __('Use the ACN as the company name', 'scgcge') ?> </label>
        </div>
        <div class="legal_elements_container">
            <label for="legal_elements"><?php echo __('Legal Elements', 'scgcge') ?></label>
            <div>
                <select name="legal_elements" id="legal_elements" class="legal_elements" aria-required="true" aria-invalid="false" style="color: #777;">
                    <option value="PTY LTD"><?php echo __('PTY LTD', 'scgcge') ?></option>
                    <option value="PTY. LTD."><?php echo __('PTY. LTD.', 'scgcge') ?></option>
                    <option value="PTY. LTD"><?php echo __('PTY. LTD.', 'scgcge') ?></option>
                    <option value="PTY. LIMITED"><?php echo __('PTY. LIMITED', 'scgcge') ?></option>
                    <option value="PTY LTD."><?php echo __('PTY LTD.', 'scgcge') ?></option>
                    <option value="PTY LIMITED"><?php echo __('PTY LIMITED', 'scgcge') ?></option>
                    <option value="PROPRIETARY LTD."><?php echo __('PROPRIETARY LTD.', 'scgcge') ?></option>
                    <option value="PROPRIETARY LTD"><?php echo __('PROPRIETARY LTD', 'scgcge') ?></option>
                    <option value="PROPRIETARY LIMITED"><?php echo __('PROPRIETARY LIMITED', 'scgcge') ?></option>
                </select>
            </div>
        </div>
        <div>
            <label>&nbsp;</label>
            <div>
                <button class="search_button button check-name-now"><?php echo __('Check Availability', 'scgcge') ?></button>
            </div>
        </div>
    </div>
    <div class="results">
    <input type="text" id="search_result" class="hideme" name="search_result">
    <input type="text" id="company_name_full" class="hideme" name="company_name_full">
    <div id="search-result"></div>
        <input type="submit" id="register" class="button" value="<?php echo __('Start your New Company', 'scgcge') ?>">
    </div>
</form>