<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registration Form
 *
 * Manage Registration Form  in  Input
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_Input($type = 'text', $name, $value = '', $readonly = '', $required = '', $placeholder = '', $class = '', $id = '', $fv = '', $gfaa = '')
{
    $gfaa_tag = $gfaa != '' ? ' maps = "' . $gfaa . '" ' : '';
    $readonly = $readonly != '' ? 'readonly' : '';
    $required = $required != '' ? 'required' : '';
    $fv = $fv != '' ? 'fv="' . $fv . '"' : '';
    return '<input type="' . $type . '" value="' . $value . '" ' . $required . ' name="' . $name . '" class="form-control ' . $class . '" id="' . $name . ' ' . $id . '" aria-describedby="' . $name . 'Help" placeholder="' . __($placeholder, 'scgcge') . '" ' . $readonly . ' ' . $fv . ' ' . $gfaa_tag . ' >';
}

/**
 * Registration Form
 *
 * Manage Registration Form in Input Icon
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_InputIcon($type = 'text', $name, $value = '', $readonly = '', $required = '', $placeholder = '', $class = '', $id = '', $fv = '', $icon = '$')
{
    $readonly = $readonly != '' ? 'readonly' : '';
    $required = $required != '' ? 'required' : '';
    $fv = $fv != '' ? 'fv="' . $fv . '"' : '';
    return '<div class="input-group"><div class="input-group-prepend"><span class="input-group-text" id="' . $name . ' ' . $id . '">' . $icon . '</span></div><input type="' . $type . '" value="' . $value . '" ' . $required . ' name="' . $name . '" class="form-control ' . $class . '" id="' . $name . ' ' . $id . '" aria-describedby="' . $name . 'Help" placeholder="' . __($placeholder, 'scgcge') . '" ' . $readonly . ' ' . $fv . '></div>';
}

/**
 * Registration Form
 *
 * Manage Registration Form in Input Line
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_InputLine($type = 'text', $name, $value = '', $readonly = '', $required = '', $placeholder = '', $class = '', $id = '', $fv = '', $title = '')
{
    $readonly = $readonly != '' ? 'readonly' : '';
    $required = $required != '' ? 'required' : '';
    $fv = $fv != '' ? 'fv="' . $fv . '"' : '';
    return '<div class="form-group row"><label for="' . $name . '" class="col-sm-2 col-form-label"> ' . $title . '</label><div class="col-sm-10"><input type="' . $type . '" value="' . $value . '" ' . $required . ' name="' . $name . '" class="form-control ' . $class . '" id="' . $name . ' ' . $id . '" aria-describedby="' . $name . 'Help" placeholder="' . __($placeholder, 'scgcge') . '" ' . $readonly . ' ' . $fv . '></div></div>';
}

/**
 * Registration Form
 *
 * Manage Registration Form in Select
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_Select($name, $values = '', $value = '', $required = '', $class = '', $id = '', $gfaa = '')
{
    $gfaa_tag = $gfaa != '' ? ' maps = "' . $gfaa . '" ' : '';
    $required = $required != '' ? 'required' : '';
    return '<select id="' . $name . '" name="' . $name . '" class="form-control ' . $class . '" ' . $required . ' ' . $gfaa_tag . '>
        ' . $values . '
    </select>';
}

/**
 * Registration Form
 *
 * Manage Registration Form in Radio Button
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_Radio($name, $options, $value = '', $class, $required = '', $inline = true)
{
    $inline = $inline ? '-inline' : '';
    $required = $required != '' ? 'required' : '';
    $html = '';
    foreach ($options as $key => $option) {
        if ($value == '' && $name == 'bn_when') {
            $value = 'after';
        }
        $checked = ($value == $option['value']) ? "checked='checked'" : '';
        $html .= '
      <div class="custom-control custom-radio custom-control' . $inline . '">
          <input type="radio" id="' . $name . $key . '" name="' . $name . '" value="' . $option['value'] . '" class="custom-control-input ' . $class . '" ' . $checked . ' ' . $required . '>
          <label class="custom-control-label" for="' . $name . $key . '">' . $option['label'] . ' </label>
      </div>';
    }
    return $html;
}

/**
 * Registration Form
 *
 * Manage Share Radio Button in Registration Form
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_share_Radio($id, $name, $options, $value = '', $class, $required = '', $inline = true, $parentKey = '')
{
    $inline = $inline ? '-inline' : '';
    $required = $required != '' ? 'required' : '';
    $html = '';
    foreach ($options as $key => $option) {
        if ($value == '' && $name == 'bn_when') {
            $value = 'after';
        }
        $checked = ($value == $option['value']) ? "checked='checked'" : '';
        $html .= '
      <div class="custom-control custom-radio custom-control' . $inline . '">
          <input type="radio" id="' . $id . $key . '" name="' . $name . '" value="' . $option['value'] . '" class="custom-control-input ' . $class . '" ' . $checked . ' ' . $required . '>
          <label class="custom-control-label" for="' . $id . $key . '">' . $option['label'] . ' </label>
      </div>';
    }
    return $html;
}

/**
 * Registration Form
 *
 * Manage Registration Form in Check Box
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_Checkbox($name, $options, $value = '', $required = '', $inline = true, $class = '')
{
    $inline = $inline ? '-inline' : '';
    $required = $required != '' ? 'required' : '';
    $html = '';
    foreach ($options as $key => $option) {
        $checked = ($value == $option['value']) ? "checked='checked'" : '';
        $html .= '
      <div class="custom-control custom-checkbox custom-control' . $inline . '">
          <input type="checkbox" id="' . $name . '" name="' . $name . '" value="' . $option['value'] . '" class="custom-control-input ' . $class . '" ' . $checked . ' ' . $required . ' class="' . $class . '">
          <label class="custom-control-label" for="' . $name . '">' . $option['label'] . ' </label>
      </div>';
    }
    return $html;
}

/**
 * Registration Form
 *
 * Manage Registration Form in Label
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_Label($name, $title = '', $text = '')
{
    $info = ($text != '') ? '<i class="fa fa-info-circle fa-1" title="' . $text . '" data-toggle="tooltip" data-placement="top"></i>' : '';
    return '<label for="' . $name . '">' . $title . ' ' . $info . '</label>';
}

/**
 * Registration Form
 *
 * Manage Registration Form in Help
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_Help($name, $text, $class = '')
{
    return '<small id="' . $name . 'Help" class="form-text text-muted ' . $class . '">' . $text . '</small>';
}

/**
 * Registration Form
 *
 * Manage Registration Form in Address Of Australia
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_AddressAU($name, $values = ['', '', '', '', '', ''], $title = '')
{
    $wp_scgcge_options = get_option('wp_scgcge_options');
    if (!empty($wp_scgcge_options['google_map_api_key'])) {
        $autocomplete = 'places-autocomplete';
    } else {
        $autocomplete = '';
    }

    $title2 = '';
    if ($title != '') {
        $title = '<label for="' . $name . '">' . $title . '</label>';
        $title2 = '<label for="' . $name . '" class="d-sm-none d-md-block">&nbsp;</label>';
    }
    return '
    <div class="address-fields">
        <div class="row">
            <div class="col-md-4">
                ' . $title . '
                <div class="form-group">
                    ' . wp_scgcge_Input('text', $name . '_care', !empty($values[0]) ? $values[0] : '', '', '', '', '')
                    . wp_scgcge_Help($name . '_care', 'Care Of') . '
                </div>
            </div>

            <div class="col-md-2">
                ' . $title2 . '
                <div class="form-group">
                    ' . wp_scgcge_Input('text', $name . '_line2', !empty($values[1]) ? $values[1] : '', '', '', '', '')
                    . wp_scgcge_Help($name . '_line2', 'Unit / Suite') . '
                </div>
            </div>
            <div class="col-md-6">
                ' . $title2 . '
                <div class="form-group">
                ' . wp_scgcge_Input('text', $name . '_street', !empty($values[2]) ? $values[2] : '', '', 'required', __('Enter street address...', 'scgcge'), $autocomplete, '', '', 'street-address')
                    . wp_scgcge_Help($name . '_street', 'Street Address') . '
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    ' . wp_scgcge_Input('text', $name . '_suburb', !empty($values[3]) ? $values[3] : '', '', 'required', '', '', '', '', 'address-level2')
                    . wp_scgcge_Help($name . '_suburb', 'Suburb') . '
                </div>
            </div>

            <div class="col-md">
                <div class="form-group">
                    ' . wp_scgcge_Select($name . '_state', wp_scgcge_get_state_options(!empty($values[4]) ? $values[4] : ''), '', 'required', '', '', 'address-level1')
                    . wp_scgcge_Help($name . '_state', 'State') . '
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                    ' . wp_scgcge_Input('text', $name . '_postcode', !empty($values[5]) ? $values[5] : '', '', 'required', '', '', 'PostalCode', '', 'postal-code')
                    . wp_scgcge_Help($name . '_postcode', 'Postcode') . '
                </div>
            </div>
        </div>
    </div>';
}

/**
 * Registration Form
 *
 * Manage Registration Form in Address Of General
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
*/
function wp_scgcge_AddressGeneral($name, $values = ['', '', '', '', '', '', ''], $title = '')
{
    $wp_scgcge_options = get_option('wp_scgcge_options');
    if (!empty($wp_scgcge_options['google_map_api_key'])) {
        $autocomplete = 'places-autocomplete';
    } else {
        $autocomplete = '';
    }

    $title2 = '';
    if ($title != '') {
        $title = '<label for="' . $name . '">' . $title . '</label>';
        $title2 = '<label for="' . $name . '" class="d-sm-none d-md-block">&nbsp;</label>';
    }
    return '
    <div class="address-fields">
        <div class="row">
            <div class="col-md-4">
                ' . $title . '
                <div class="form-group">
                    ' . wp_scgcge_Input('text', $name . '_care', !empty($values[0]) ? $values[0] : '', '', '', '', '')
                    . wp_scgcge_Help($name . '_care', 'Care Of') . '
                </div>
            </div>

            <div class="col-md-2">
                ' . $title2 . '
                <div class="form-group">
                    ' . wp_scgcge_Input('text', $name . '_line2', !empty($values[1]) ? $values[1] : '', '', '', '', '')
                    . wp_scgcge_Help($name . '_line2', 'Unit / Suite') . '
                </div>
            </div>
            <div class="col-md-6">
                ' . $title2 . '
                <div class="form-group">
                ' . wp_scgcge_Input('text', $name . '_street', !empty($values[2]) ? $values[2] : '', '', 'required', __('Enter street address...', 'scgcge'), $autocomplete, '', '', 'street-address')
                    . wp_scgcge_Help($name . '_street', 'Street Address') . '
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md">
                <div class="form-group">
                    ' . wp_scgcge_Input('text', $name . '_suburb', !empty($values[3]) ? $values[3] : '', '', 'required', '', '', '', '', 'address-level2')
                    . wp_scgcge_Help($name . '_suburb', 'City or Suburb') . '
                </div>
            </div>

            <div class="col-md" data-do-when=\'{ "' . $name . '_country": ["AU"] }\' data-do-action="show">
                <div>
                    ' . wp_scgcge_Select($name . '_state', wp_scgcge_get_state_options(!empty($values[4]) ? $values[4] : ''), '', 'required', '', '', 'address-level1')
                    . wp_scgcge_Help($name . '_state', 'State') . '
                </div>
            </div>
            <div class="col-md" data-do-when=\'{ "' . $name . '_country": ["AU"] }\' data-do-action="show">
                <div class="form-group">
                    ' . wp_scgcge_Input('text', $name . '_postcode', !empty($values[5]) ? $values[5] : '', '', 'required', '', '', '', '', 'postal-code')
                    . wp_scgcge_Help($name . '_postcode', 'Postcode') . '
                </div>
            </div>
            <div class="col-md">
                <div class="form-group">
                ' . wp_scgcge_Select($name . '_country', wp_scgcge_get_countries_options(!empty($values[6]) ? $values[6] : ''), '', 'required', '', '', 'country')
                . wp_scgcge_Help($name . '_country', 'Country') . '
                </div>
            </div>
        </div>
    </div>';
}
