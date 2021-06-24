<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Page
 *
 * Handle settings
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

global $wpd_ws_model;

$model = $wpd_ws_model;

//all settings will reset as per default
if (isset($_POST['wpd_ws_reset_settings']) && !empty($_POST['wpd_ws_reset_settings']) && $_POST['wpd_ws_reset_settings'] == __('Reset All Settings', 'scgcge')) { //check click of reset button
    wp_scgcge_default_settings(); // set default settings

    echo '<div class="updated" id="message">
		<p><strong>' . __('All Settings Reset Successfully.', 'scgcge') . '</strong></p>
	</div>';
}
//check settings updated or not
if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
    echo '<div class="updated" id="message">
		<p><strong>' . __('Changes Saved Successfully.', 'scgcge') . '</strong></p>
	</div>';
}
?>
	<!-- . begining of wrap -->
	<div class="wrap">
		<?php
            //echo screen_icon('options-general');
    echo '<h2>' . __('GetEDGE ECR Settings', 'scgcge') . '</h2>';
    ?>	
		<div class="wpd-ws-reset-setting">
			<form method="post" action="">
				<input id="wpd-ws-reset-all-options" type="submit" class="button-primary" name="wpd_ws_reset_settings" value="<?php echo __('Reset All Settings', 'scgcge'); ?>" />
			</form>
		</div>
			
		<!-- beginning of the plugin options form -->
		<form  method="post" action="options.php" enctype="multipart/form-data">		
		
			<?php
        settings_fields('wp_scgcge_plugin_options');
        $wp_scgcge_options = get_option('wp_scgcge_options');
        ?>
		<!-- beginning of the settings meta box -->	
			<div id="wp-scgcge-settings" class="post-box-container">
			
				<div class="metabox-holder">	
			
					<div class="meta-box-sortables ui-sortable">
			
						<div id="settings" class="postbox">	
			
							<div class="handlediv" title="<?php echo __('Click to toggle', 'scgcge') ?>"><br /></div>
			
								<!-- settings box title -->					
								<h3 class="hndle">					
									<span style="vertical-align: top;"><?php echo __('GetEDGE ECR Settings', 'scgcge') ?></span>					
								</h3>
			
								<div class="inside">			

									<table class="form-table wpd-ws-settings-box"> 
										<tbody>
							
											<tr><th><h3>GetEDGE API</h3></td><td><hr></td></tr>
											
											 <tr>
												<th scope="row">
													<label><strong><?php echo __('GetEDGE API Key:', 'scgcge') ?></strong></label>
												</th>
												<td><input type="text" id="wpd-ws-settings-ge-api-key" name="wp_scgcge_options[ge_api_key]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['ge_api_key']) ? $wp_scgcge_options['ge_api_key'] : '') ?>" size="63" /><br />
												<input type="hidden" id="wpd-ws-settings-ge-url" name="wp_scgcge_options[ge_url]" value="https://getedge.com.au/api/v1" />	
												<span class="description"><?php echo __('Enter the GetEDGE API Key.', 'scgcge') ?></span>
												</td>
											 </tr>
											 
											 <tr>
												<th scope="row">
													<label><strong><?php echo __('DOCS API Key:', 'scgcge') ?></strong></label>
												</th>
												<td><input type="text" id="wpd-ws-settings-docs-api-key" name="wp_scgcge_options[docs_api_key]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['docs_api_key']) ? $wp_scgcge_options['docs_api_key'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter the Document Production API Key or leave blank for the default document set.', 'scgcge') ?></span>
												</td>
											 </tr>


											 <tr>
												<th scope="row">
													<label><strong><?php echo __('Transmission Type:', 'scgcge') ?></strong></label>
												</th>
												<td><select id="wpd-ws-settings-test-transmission" name="wp_scgcge_options[test_transmission]">
												<option value =""><?php echo __('Please Select'); ?></option>
													<option value="Y" <?php echo selected($this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['test_transmission']) ? $wp_scgcge_options['test_transmission'] : '') ? $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['test_transmission']) ? $wp_scgcge_options['test_transmission'] : '') : '', 'Y', false) ?>>Test lodgements</option>
													<option value="N" <?php echo selected($this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['test_transmission']) ? $wp_scgcge_options['test_transmission'] : '') ? $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['test_transmission']) ? $wp_scgcge_options['test_transmission'] : '') : '', 'N', false) ?>>Live lodgements</option>
												</select><br />
													<span class="description"><?php echo __('Select if the transmission should be set to TEST or LIVE.', 'scgcge') ?></span>
												</td>
											 </tr>	

											 <tr>
												<th scope="row">
													<label><strong><?php echo __('Agent details:', 'scgcge') ?></strong></label>
												</th>
												<td>
												<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-agt-number" name="wp_scgcge_options[agt_number]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['agt_number']) ? $wp_scgcge_options['agt_number'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Agent Number', 'scgcge') ?></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-agt-company" name="wp_scgcge_options[agt_company]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['agt_company']) ? $wp_scgcge_options['agt_company'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Company Name', 'scgcge') ?></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-agt-name" name="wp_scgcge_options[agt_name]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['agt_name']) ? $wp_scgcge_options['agt_name'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Agent representative', 'scgcge') ?></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-agt-email" name="wp_scgcge_options[agt_email]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['agt_email']) ? $wp_scgcge_options['agt_email'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Agent email', 'scgcge') ?></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-agt-phone" name="wp_scgcge_options[agt_phone]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['agt_phone']) ? $wp_scgcge_options['agt_phone'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Agent phone', 'scgcge') ?></span>
													</span>
												</td>
											 </tr>


											 <tr>
												<th scope="row">
													<label><strong><?php echo __('Agent address:', 'scgcge') ?></strong></label>
												</th>
												<td>
												<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-agt-street" name="wp_scgcge_options[agt_street]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['agt_street']) ? $wp_scgcge_options['agt_street'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Street address', 'scgcge') ?></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-agt-suburb" name="wp_scgcge_options[agt_suburb]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['agt_suburb']) ? $wp_scgcge_options['agt_suburb'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Suburb', 'scgcge') ?></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-agt-state" name="wp_scgcge_options[agt_state]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['agt_state']) ? $wp_scgcge_options['agt_state'] : '') ?>"  />
														<br /><span class="description"><?php echo __('State', 'scgcge') ?></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-agt-postcode" name="wp_scgcge_options[agt_postcode]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['agt_postcode']) ? $wp_scgcge_options['agt_postcode'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Postcode', 'scgcge') ?></span>
													</span>
												</td>
											 </tr>




											 <tr><th><h3><?php echo __('Pages & Products', 'scgcge') ?></h3></td><td><hr></td></tr>

											  <tr>
												<th scope="row">
													<label><strong><?php echo __('Registration Page:', 'scgcge') ?></strong></label>
												</th>

												<td><select id="wpd-ws-settings-from-page-id" name="wp_scgcge_options[page_id]">
												<option value =""><?php echo __('Please Select'); ?></option>
												<?php
                                            $pages = get_pages();
                                            if (!empty($pages)) {
                                                foreach ($pages as $page) {
                                                    ?>
													<option value="<?php echo $page->ID; ?>" <?php echo selected($this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['page_id']) ? $wp_scgcge_options['page_id'] : '') ? $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['page_id']) ? $wp_scgcge_options['page_id'] : '') : '', "$page->ID", false) ?>><?php echo $page->post_title; ?> [Page]</option>
												<?php
                                                }
                                            } ?>
												</select><br />
													<span class="description"><?php echo __('Select the company registration page that has the [company_registration_form] schortcode.', 'scgcge') ?></span>
												</td>
											 </tr>

											 <tr>
												<th scope="row">
													<label><strong><?php echo __('Registration Service:', 'scgcge') ?></strong></label>
												</th>
												<td><select id="wpd-ws-settings-ge-coy-reg-fee-id" name="wp_scgcge_options[ge_coy_reg_fee_id]">
												<option value =""><?php echo __('Please Select'); ?></option>
												<?php
                                            $args = [
                                                'post_type' => 'product',
                                                'posts_per_page' => 99
                                            ];
                                            $loop = new WP_Query($args);
                                            $allProducts = $loop->posts;
                                            if (!empty($allProducts)) {
                                                foreach ($allProducts as $key => $value) {
                                                    ?>	
													<option value="<?php echo $value->ID; ?>" <?php echo selected($this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '') ? $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '') : '', "$value->ID", false) ?>><?php echo $value->post_title; ?> [Product]</option>
												<?php
                                                }
                                            } ?>
												</select><br />
													<span class="description"><?php echo __('Select the WooCommerce company registration service product.', 'scgcge') ?></span>
												</td>
											 </tr>

											 <tr>
												<th scope="row">
													<label><strong><?php echo __('ASIC Registration Fee:', 'scgcge') ?></strong></label>
												</th>
												<td><select id="wpd-ws-settings-ge-asic-fee-id" name="wp_scgcge_options[ge_asic_fee_id]">
												<option value =""><?php echo __('Please Select'); ?></option>
												<?php
                                            $args = [
                                                'post_type' => 'product',
                                                'posts_per_page' => 99
                                            ];
                                            $loop = new WP_Query($args);
                                            $allProducts = $loop->posts;
                                            if (!empty($allProducts)) {
                                                foreach ($allProducts as $key => $value) {
                                                    ?>
													<option value="<?php echo $value->ID; ?>" <?php echo selected($this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '') ? $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '') : '', "$value->ID", false) ?>><?php echo $value->post_title; ?> [Product]</option>
													<?php
                                                }
                                            } ?>
												</select><br />
													<span class="description"><?php echo __('Select the WooCommerce ASIC registration fee product.', 'scgcge') ?></span>
												</td>
											 </tr>

											 <tr>
												<th scope="row">
													<label><strong><?php echo __('Accent colour:', 'scgcge') ?></strong></label>
												</th>
												<td>
												<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-hex-accent-bg" name="wp_scgcge_options[hex_accent_bg]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['hex_accent_bg']) ? $wp_scgcge_options['hex_accent_bg'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Active button background', 'scgcge')?><br><small><?php echo __('Default', 'scgcge')?> </small><small><code><small>#0665d0</small></code> <span class="dashicons dashicons-admin-appearance" style="color: <?php echo $wp_scgcge_options['hex_accent_bg'];?>"></span> </small></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-hex-accent-colour" name="wp_scgcge_options[hex_accent_colour]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['hex_accent_colour']) ? $wp_scgcge_options['hex_accent_colour'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Active button colour', 'scgcge')?><br><small><?php echo __('Default', 'scgcge')?> </small><small><code><small>#ffffff</small></code> <span class="dashicons dashicons-admin-appearance" style="color: <?php echo $wp_scgcge_options['hex_accent_colour'];?>"></span></small></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-hex-hover-bg" name="wp_scgcge_options[hex_hover_bg]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['hex_hover_bg']) ? $wp_scgcge_options['hex_hover_bg'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Hover button background', 'scgcge')?><br><small><?php echo __('Default', 'scgcge')?> </small><small><code><small>#343a40</small></code> <span class="dashicons dashicons-admin-appearance" style="color: <?php echo $wp_scgcge_options['hex_hover_bg'];?>"></span></small></span>
													</span>
													<span style="display: inline-block;">
														<input type="text" id="wpd-ws-settings-hex-hover-colour" name="wp_scgcge_options[hex_hover_colour]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['hex_hover_colour']) ? $wp_scgcge_options['hex_hover_colour'] : '') ?>"  />
														<br /><span class="description"><?php echo __('Hover button colour', 'scgcge')?><br><small><?php echo __('Default', 'scgcge')?> </small><small><code><small>#ffffff</small></code> <span class="dashicons dashicons-admin-appearance" style="color: <?php echo $wp_scgcge_options['hex_hover_colour'];?>"></span></small></span>
													</span>
												</td>
											 </tr>


											 <tr><th><h3><?php echo __('Xero Integration', 'scgcge') ?></h3></td><td><hr></td></tr>

                                             
											 <tr>
												<th scope="row" style="padding-top: 0; padding-bottom: 0">
												</th>
												<td style="padding-top: 0; padding-bottom: 0">
												<p><a href="https://developer.xero.com/myapps/" target="_blank">Click here</a> to create a new web app. You will be required to enter:<br>- Application name: (eg. GetEDGE Xero)<br>- Company URL: <?php echo home_url(); ?><br>- OAuth 2.0 redirect URI: <?php echo home_url(); ?>/xeroAuth</p>
												<p>Xero access tokens have a 30 minutes expiration. Please set a CRON to run every 25 minutes, calling <?php echo home_url(); ?>/xeroRefresh to keep refreshing the access tokens. <br><code>*/25 * * * *      curl --silent <?php echo home_url(); ?>/xeroRefresh > /dev/null</code></p>
												</td>
											 </tr>											
											
											<!-- Xero oAuth2.0 -->
                                            <tr>
                                                <th scope="row">
                                                    <label><strong><?php echo __('Xero Client ID:', 'scgcge') ?></strong></label>
                                                </th>
												<td><input type="text" id="wpd-ws-settings-xero-client-id" name="wp_scgcge_options[getedge_xero_client_id]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['getedge_xero_client_id']) ? $wp_scgcge_options['getedge_xero_client_id'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter the Xero Client ID.', 'scgcge') ?></span>
												</td>
                                            </tr>
											
                                            <tr>
                                                <th scope="row">
                                                    <label><strong><?php echo __('Xero Client Secret:', 'scgcge') ?></strong></label>
                                                </th>
												<td><input type="password" id="wpd-ws-settings-xero-client-secret" name="wp_scgcge_options[getedge_xero_client_secret]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['getedge_xero_client_secret']) ? $wp_scgcge_options['getedge_xero_client_secret'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter the Xero Client Secret.', 'scgcge') ?></span>
												</td>
                                            </tr>
											
                                            <tr>
                                                <th scope="row">
                                                    <label><strong><?php echo __('Xero Authorisation:', 'scgcge') ?></strong></label>
                                                </th>
                                                <td><?php 
                                                    if(isset($authUrl)) {
                                                        echo '<a href="'.$authUrl.'">Authorise Xero</a>';
                                                    }
                                                    if((get_option('getedge_xero_auth_token') != null &&  get_option('getedge_xero_auth_expiration') !== null && get_option('getedge_xero_auth_expiration') > time())) {
                                                        echo '<span style="color: green">Xero is connected (authorisation expires on '.date('d/m/Y \a\t g:ia', get_option('getedge_xero_auth_expiration')).' GMT)</span>';
                                                        echo '<br><a href="'.home_url().'/xeroDisconnect">Disconnect Xero</a>';
                                                    } else {
                                                        echo '<span style="color: red">Please fill in Xero Client ID and Secret.</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
											<!-- Xero oAuth2.0 -->


											<tr>
												<th scope="row">
													<label><strong><?php echo __('Xero Prefix:', 'scgcge') ?></strong></label>
												</th>
												<td><input type="text" id="wpd-ws-settings-xero-prefix" name="wp_scgcge_options[xero_prefix]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['xero_prefix']) ? $wp_scgcge_options['xero_prefix'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter the Xero prefix for invoices and references.', 'scgcge') ?></span>
												</td>
											 </tr>



											 <tr>
												<th scope="row">
													<label><strong><?php echo __('ASIC disbursement Account:', 'scgcge') ?></strong></label>
												</th>
												<td><input type="text" id="wpd-ws-settings-xero-account-code-asic" name="wp_scgcge_options[xero_account_code_asic]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['xero_account_code_asic']) ? $wp_scgcge_options['xero_account_code_asic'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter Xero account code for ASIC disbursement account.', 'scgcge') ?></span>
												</td>
											 </tr>

											 <tr>
												<th scope="row">
													<label><strong><?php echo __('Registrations Account:', 'scgcge') ?></strong></label>
												</th>
												<td><input type="text" id="wpd-ws-settings-xero-account-code-sales-coy-reg" name="wp_scgcge_options[xero_account_code_sales_coy_reg]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['xero_account_code_sales_coy_reg']) ? $wp_scgcge_options['xero_account_code_sales_coy_reg'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter Xero account code for Company Registration Sales.', 'scgcge') ?></span>
												</td>
											 </tr>

											 <tr>
												<th scope="row">
													<label><strong><?php echo __('General Sales Code:', 'scgcge') ?></strong></label>
												</th>
												<td><input type="text" id="wpd-ws-settings-xero-account-code-sales" name="wp_scgcge_options[xero_account_code_sales]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['xero_account_code_sales']) ? $wp_scgcge_options['xero_account_code_sales'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter Xero account code for General Sales.', 'scgcge') ?></span>
												</td>
											 </tr>

											 <tr>
												<th scope="row">
													<label><strong><?php echo __('ASIC Contact ID:', 'scgcge') ?></strong></label>
												</th>
												<td><input type="text" id="wpd-ws-settings-xero-asic-contact-id" name="wp_scgcge_options[xero_asic_contact_id]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['xero_asic_contact_id']) ? $wp_scgcge_options['xero_asic_contact_id'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter Xero ASIC Contact ID.', 'scgcge') ?></span>
												</td>
											 </tr>	

											 <tr><th><h3><?php echo __('Google Integration', 'scgcge') ?></h3></td><td><hr></td></tr>

											 <tr>
												<th scope="row">
													<label><strong><?php echo __('Google Map API Key:', 'scgcge') ?></strong></label>
												</th>
												<td><input type="text" id="wpd-ws-settings-google-map-api-key" name="wp_scgcge_options[google_map_api_key]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['google_map_api_key']) ? $wp_scgcge_options['google_map_api_key'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter Google Map API Key. <a href="https://developers.google.com/places/web-service/get-api-key" target="_blank">Click here</a> to register for a Google Maps Places API Key', 'scgcge') ?></span>
												</td>
											 </tr>										  

											 <tr><th><h3><?php echo __('Slack Integration', 'scgcge') ?></h3></td><td><hr></td></tr>

											 <tr>
												<th scope="row">
													<label><strong><?php echo __('Slack Webhook Url:', 'scgcge') ?></strong></label>
												</th>
												<td><input type="url" id="wpd-ws-settings-slack-webhook-url" name="wp_scgcge_options[slack_webhook_url]" value="<?php echo $this->model->wp_scgcge_escape_attr(!empty($wp_scgcge_options['slack_webhook_url']) ? $wp_scgcge_options['slack_webhook_url'] : '') ?>" size="63" /><br />
													<span class="description"><?php echo __('Enter Slack Webhook URL. <a href="https://api.slack.com/incoming-webhooks" target="_blank">Click here</a> to get your Slack Webhook URL<br>e.g. https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX', 'scgcge') ?></span>
												</td>
											 </tr>


												
											<tr>
												<td colspan="2">
													<input type="submit" class="button-primary wpd-ws-settings-save" name="wpd_ws_settings_save" class="" value="<?php echo __('Save Changes', 'scgcge') ?>" />
												</td>
											</tr>								
							
										</tbody>
									</table>
						
							</div><!-- .inside -->
				
						</div><!-- #settings -->
			
					</div><!-- .meta-box-sortables ui-sortable -->
			
				</div><!-- .metabox-holder -->
			
			</div><!-- #wps-settings-general -->
			
		<!-- end of the settings meta box -->		

		</form><!-- end of the plugin options form -->
	
	</div><!-- .end of wrap -->