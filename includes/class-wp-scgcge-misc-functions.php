<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * GET Current Segment
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

function wp_scgcge_current_segment()
{
    $urlArray = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $segments = explode('/', $urlArray);
    $numSegments = count($segments);
    $currentSegment = $segments[$numSegments - 1];
    return $currentSegment;
}

/**
 *
 * Get Company Registration  State
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_get_state($long)
{
    switch (ucfirst($long)) {
        case 'AUSTRALIAN CAPITAL TERRITORY':
            return 'ACT';
            break;

        case 'NEW SOUTH WALES':
            return 'NSW';
            break;

        case 'NORTHERN TERRITORY':
            return 'NT';
            break;

        case 'QUEENSLAND':
            return 'QLD';
            break;

        case 'SOUTH AUSTRALIA':
            return 'SA';
            break;

        case 'TASMANIA':
            return 'TAS';
            break;

        case 'VICTORIA':
            return 'VIC';
            break;

        case 'WESTERN AUSTRALIA':
            return 'WA';
            break;
    }
}

/**
 * Company Registration Form in Previes Segment
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_previous_segment()
{
    $urlArray = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $segments = explode('/', $urlArray);
    $numSegments = count($segments);
    $currentSegment = $segments[$numSegments - 2];
    return $currentSegment;
}

/**
 * action url  Eg : http://visualiris/register-company/
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */

function wp_scgcge_action_url()
{
    global $post;
    $wp_scgcge_options = get_option('wp_scgcge_options');
    $scgcge_page_id = !empty($wp_scgcge_options['page_id']) ? $wp_scgcge_options['page_id'] : '';
    $post = get_post($scgcge_page_id);
    $scgcge_page_url = $post->post_name;

    return home_url() . '/' . $scgcge_page_url;
}

/**
 * String To Uppercase
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_toUpper($values, $keepVar = false)
{
    $return_array = [];
    foreach ($values as $postKey => $postVar) {
        if (is_string($postVar)) {
            $return_array[$postKey] = trim(strtoupper(remove_accents($postVar)));
        } else {
            foreach ($postVar as $postKeyArray => $postVarArray) {
                if (is_string($postVarArray)) {
                    $return_array[$postKey][$postKeyArray] = trim(strtoupper(remove_accents($postVarArray)));
                } else {
                    foreach ($postVarArray as $postKeyArrayIn => $postVarArrayIn) {
                        $return_array[$postKey][$postKeyArray][$postKeyArrayIn] = trim(strtoupper(remove_accents($postVarArrayIn)));
                    }
                }
            }
        }
    }
    if ($keepVar) {
        $values = $return_array;
    } else {
        return $return_array;
    }
}

/**
 * Set Session
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_setSession($code = null)
{
    $now = time();
    $_SESSION['discard_after'] = $now + 3600;
    $_SESSION['scgcge'] = (isset($code) ? $code : md5(time()));
}

/**
 * Get Session
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_getSession()
{
    $now = time();
    if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
        // this session has worn out its welcome; kill it and start a brand new one
        session_unset();
        session_destroy();
        //session_start();
        return false;
    }

    return (isset($_SESSION['scgcge']) && $_SESSION['scgcge'] != '') ? $_SESSION['scgcge'] : false;
}

/**
 * Destroy Session
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_destroySession($code)
{
    global $wpdb;
    $result = $wpdb->update($wpdb->prefix . 'asic_companies', [
        'session_id' => '',
    ], ['code' => $code]);
    unset($_SESSION['scgcge']);
}

/**
 * Get State Options
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_get_state_options($val = '')
{
    $html = "
    <option value='' " . ($val == '' ? 'selected' : '') . '>' . __('Select State', 'scgcge') . "</option>
    <option value='ACT' " . ($val == 'ACT' ? 'selected' : '') . '>' . __('Australian Capital Territory', 'scgcge') . "</option>
    <option value='NSW' " . ($val == 'NSW' ? 'selected' : '') . '>' . __('New South Wales', 'scgcge') . "</option>
    <option value='NT' " . ($val == 'NT' ? 'selected' : '') . '>' . __('Northern Territory', 'scgcge') . "</option>
    <option value='QLD' " . ($val == 'QLD' ? 'selected' : '') . ' >' . __('Queensland', 'scgcge') . "</option>
    <option value='SA' " . ($val == 'SA' ? 'selected' : '') . '>' . __('South Australia', 'scgcge') . "</option>
    <option value='TAS' " . ($val == 'TAS' ? 'selected' : '') . '>' . __('Tasmania', 'scgcge') . "</option>
    <option value='VIC' " . ($val == 'VIC' ? 'selected' : '') . '>' . __('Victoria', 'scgcge') . "</option>
    <option value='WA' " . ($val == 'WA' ? 'selected' : '') . '>' . __('Western Australia', 'scgcge') . '</option>
    ';
    return $html;
}

/**
 * Get Share Classes In Entities Page
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_get_share($code)
{
    switch ($code) {
        case 'ORD':
            return 'ORDINARY';
            break;
        case 'A':
            return 'CLASS A';
            break;
        case 'B':
            return 'CLASS B';
            break;
        case 'C':
            return 'CLASS C';
            break;
        case 'D':
            return 'CLASS D';
            break;
        case 'E':
            return 'CLASS E';
            break;
        case 'F':
            return 'CLASS F';
            break;
        case 'G':
            return 'CLASS G';
            break;
        case 'H':
            return 'CLASS H';
            break;
        case 'I':
            return 'CLASS I';
            break;
        case 'J':
            return 'CLASS J';
            break;
        case 'K':
            return 'CLASS K';
            break;
        case 'MAN':
            return 'MANAGEMENT';
            break;
        case 'LG':
            return 'LIFE GOVERNORS';
            break;
        case 'EMP':
            return 'EMPLOYEES';
            break;
        case 'FOU':
            return 'FOUNDERS';
            break;
        case 'PRF':
            return 'PREFERENCE';
            break;
        case 'CUMP':
            return 'CUMULATIVE PREFERENCE';
            break;
        case 'NCP':
            return 'NON CUMULATIVE PREFERENCE';
            break;
        case 'REDP':
            return 'REDEEMABLE PREFERENCE';
            break;
        case 'NRP':
            return 'NON REDEEMABLE PREFERENCE';
            break;
        case 'XXX':
            return 'CUMULATIVE REDEEMABLE PREFERENCE';
            break;
        case 'NCRP':
            return 'NON CUMULATIVE REDEEMABLE PREFERENCE';
            break;
        case 'PARP':
            return 'PARTICIPATIVE PREFERENCE';
            break;
        case 'RED':
            return 'REDEEMABLE';
            break;
        case 'IMI':
            return 'INITIAL';
            break;
        case 'SPE':
            return 'SPECIAL';
            break;
    }
}

/**
 * Get Share Classes Selected In Entities Page
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_get_share_options($val = '')
{
    $html = "
    <option value='' " . ($val == '' ? 'selected' : '') . '>' . __('Select Share Class', 'scgcge') . "</option>
    <option value='ORD' " . ($val == 'ORD' ? 'selected' : '') . '>' . __('Ordinary', 'scgcge') . "</option>
    <option value='A' " . ($val == 'A' ? 'selected' : '') . '>' . __('Class A', 'scgcge') . "</option>
    <option value='B' " . ($val == 'B' ? 'selected' : '') . '>' . __('Class B', 'scgcge') . "</option>
    <option value='C' " . ($val == 'C' ? 'selected' : '') . '>' . __('Class C', 'scgcge') . "</option>
    <option value='D' " . ($val == 'D' ? 'selected' : '') . '>' . __('Class D', 'scgcge') . "</option>
    <option value='E' " . ($val == 'E' ? 'selected' : '') . '>' . __('Class E', 'scgcge') . "</option>
    <option value='F' " . ($val == 'F' ? 'selected' : '') . '>' . __('Class F', 'scgcge') . "</option>
    <option value='G' " . ($val == 'G' ? 'selected' : '') . '>' . __('Class G', 'scgcge') . "</option>
    <option value='H' " . ($val == 'H' ? 'selected' : '') . '>' . __('Class H', 'scgcge') . "</option>
    <option value='I' " . ($val == 'I' ? 'selected' : '') . '>' . __('Class I', 'scgcge') . "</option>
    <option value='J' " . ($val == 'J' ? 'selected' : '') . '>' . __('Class J', 'scgcge') . "</option>
    <option value='K' " . ($val == 'K' ? 'selected' : '') . '>' . __('Class K', 'scgcge') . "</option>
    <option value='MAN' " . ($val == 'MAN' ? 'selected' : '') . '>' . __('Management', 'scgcge') . "</option>
    <option value='LG' " . ($val == 'LG' ? 'selected' : '') . '>' . __('Life Governors', 'scgcge') . "</option>
    <option value='EMP' " . ($val == 'EMP' ? 'selected' : '') . '>' . __('Employees', 'scgcge') . "</option>
    <option value='FOU' " . ($val == 'FOU' ? 'selected' : '') . '>' . __('Founders', 'scgcge') . "</option>
    <option value='PRF' " . ($val == 'PRF' ? 'selected' : '') . '>' . __('Preference', 'scgcge') . "</option>
    <option value='CUMP' " . ($val == 'CUMP' ? 'selected' : '') . '>' . __('Cumulative Preference', 'scgcge') . "</option>
    <option value='NCP' " . ($val == 'NCP' ? 'selected' : '') . '>' . __('Non Cumulative Preference', 'scgcge') . "</option>
    <option value='REDP' " . ($val == 'REDP' ? 'selected' : '') . '>' . __('Redeemable Preference', 'scgcge') . "</option>
    <option value='NRP' " . ($val == 'NRP' ? 'selected' : '') . '>' . __('Non Redeemable Preference', 'scgcge') . "</option>
    <option value='CRP' " . ($val == 'CRP' ? 'selected' : '') . '>' . __('Cumulative Redeemable Preference', 'scgcge') . "</option>
    <option value='NCRP' " . ($val == 'NCRP' ? 'selected' : '') . '>' . __('Non Cumulative Redeemable Preference', 'scgcge') . "</option>
    <option value='PARP' " . ($val == 'PARP' ? 'selected' : '') . '>' . __('Participative Preference', 'scgcge') . "</option>
    <option value='RED' " . ($val == 'RED' ? 'selected' : '') . '>' . __('Redeemable', 'scgcge') . "</option>
    <option value='SPE' " . ($val == 'SPE' ? 'selected' : '') . '>' . __('Special', 'scgcge') . '</option>
    ';
    return $html;
}

/**
 * To Slack
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_toSlack($message)
{
    $wp_scgcge_options = get_option('wp_scgcge_options');

    $slack_webhook_url = !empty($wp_scgcge_options['slack_webhook_url']) ? $wp_scgcge_options['slack_webhook_url'] : '';

    $slack_endpoint = $slack_webhook_url;

    // Prepare the data / payload to be posted to Slack
    $data = [
        'payload' => json_encode([
            'channel' => '',
            'text' => $message,
            'username' => '',
            'icon_emoji' => ''
        ])
    ];
    // Post our data via the slack webhook endpoint using wp_remote_post
    $posting_to_slack = wp_remote_post(
        $slack_endpoint,
        [
            'method' => 'POST',
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => [],
            'body' => $data,
            'cookies' => []
        ]
    );
}

/**
 * Get Country
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_get_country_by_code($code)
{
    $countries = [
        'AF' => __('Afghanistan', 'scgcge'),
        'AX' => __('Aland Islands', 'scgcge'),
        'AL' => __('Albania', 'scgcge'),
        'DZ' => __('Algeria', 'scgcge'),
        'AS' => __('American Samoa', 'scgcge'),
        'AD' => __('Andorra', 'scgcge'),
        'AO' => __('Angola', 'scgcge'),
        'AI' => __('Anguilla', 'scgcge'),
        'AQ' => __('Antarctica', 'scgcge'),
        'AG' => __('Antigua And Barbuda', 'scgcge'),
        'AR' => __('Argentina', 'scgcge'),
        'AM' => __('Armenia', 'scgcge'),
        'AW' => __('Aruba', 'scgcge'),
        'AU' => __('Australia', 'scgcge'),
        'AT' => __('Austria', 'scgcge'),
        'AZ' => __('Azerbaijan', 'scgcge'),
        'BS' => __('Bahamas', 'scgcge'),
        'BH' => __('Bahrain', 'scgcge'),
        'BD' => __('Bangladesh', 'scgcge'),
        'BB' => __('Barbados', 'scgcge'),
        'BY' => __('Belarus', 'scgcge'),
        'BE' => __('Belgium', 'scgcge'),
        'BZ' => __('Belize', 'scgcge'),
        'BJ' => __('Benin', 'scgcge'),
        'BM' => __('Bermuda', 'scgcge'),
        'BT' => __('Bhutan', 'scgcge'),
        'BO' => __('Bolivia', 'scgcge'),
        'BQ' => __('Bonaire St Eustatius And Saba', 'scgcge'),
        'BA' => __('Bosnia and Herzegovina', 'scgcge'),
        'BW' => __('Botswana', 'scgcge'),
        'BV' => __('Bouvet Island', 'scgcge'),
        'BR' => __('Brazil', 'scgcge'),
        'IO' => __('British Indian Ocean Territory', 'scgcge'),
        'BN' => __('Brunei Darussalam', 'scgcge'),
        'BG' => __('Bulgaria', 'scgcge'),
        'BF' => __('Burkina Faso', 'scgcge'),
        'BI' => __('Burundi', 'scgcge'),
        'KH' => __('Cambodia', 'scgcge'),
        'CM' => __('Cameroon', 'scgcge'),
        'CA' => __('Canada', 'scgcge'),
        'CV' => __('Cape Verde', 'scgcge'),
        'KY' => __('Cayman Islands', 'scgcge'),
        'CF' => __('Central African Republic', 'scgcge'),
        'TD' => __('Chad', 'scgcge'),
        'CL' => __('Chile', 'scgcge'),
        'CN' => __('China', 'scgcge'),
        'CX' => __('Christmas Island', 'scgcge'),
        'CC' => __('Cocos (Keeling) Islands', 'scgcge'),
        'CO' => __('Colombia', 'scgcge'),
        'KM' => __('Comoros', 'scgcge'),
        'CG' => __('Congo', 'scgcge'),
        'CD' => __('Congo, Democratic Republic Of', 'scgcge'),
        'CK' => __('Cook Islands', 'scgcge'),
        'CR' => __('Costa Rica', 'scgcge'),
        'CI' => __('Cote D\'Ivoire', 'scgcge'),
        'HR' => __('Croatia', 'scgcge'),
        'CU' => __('Cuba', 'scgcge'),
        'CW' => __('Curacao', 'scgcge'),
        'CY' => __('Cyprus', 'scgcge'),
        'CZ' => __('Czech Republic', 'scgcge'),
        'DK' => __('Denmark', 'scgcge'),
        'DJ' => __('Djibouti', 'scgcge'),
        'DM' => __('Dominica', 'scgcge'),
        'DO' => __('Dominican Republic', 'scgcge'),
        'EC' => __('Ecuador', 'scgcge'),
        'EG' => __('Egypt', 'scgcge'),
        'SV' => __('El Salvador', 'scgcge'),
        'GQ' => __('Equatorial Guinea', 'scgcge'),
        'ER' => __('Eritrea', 'scgcge'),
        'EE' => __('Estonia', 'scgcge'),
        'ET' => __('Ethiopia', 'scgcge'),
        'FK' => __('Falkland Islands (Malvinas)', 'scgcge'),
        'FO' => __('Faroe Islands', 'scgcge'),
        'FJ' => __('Fiji', 'scgcge'),
        'FI' => __('Finland', 'scgcge'),
        'FR' => __('France', 'scgcge'),
        'GF' => __('French Guiana', 'scgcge'),
        'PF' => __('French Polynesia', 'scgcge'),
        'TF' => __('French Southern Territories', 'scgcge'),
        'GA' => __('Gabon', 'scgcge'),
        'GM' => __('Gambia', 'scgcge'),
        'GE' => __('Georgia', 'scgcge'),
        'DE' => __('Germany', 'scgcge'),
        'GH' => __('Ghana', 'scgcge'),
        'GI' => __('Gibraltar', 'scgcge'),
        'GR' => __('Greece', 'scgcge'),
        'GL' => __('Greenland', 'scgcge'),
        'GD' => __('Grenada', 'scgcge'),
        'GP' => __('Guadeloupe', 'scgcge'),
        'GU' => __('Guam', 'scgcge'),
        'GT' => __('Guatemala', 'scgcge'),
        'GG' => __('Guernsey', 'scgcge'),
        'GN' => __('Guinea', 'scgcge'),
        'GW' => __('Guinea-Bissau', 'scgcge'),
        'GY' => __('Guyana', 'scgcge'),
        'HT' => __('Haiti', 'scgcge'),
        'HM' => __('Heard Is And Mcdonald Is', 'scgcge'),
        'VA' => __('Holy See (Vatican City State)', 'scgcge'),
        'HN' => __('Honduras', 'scgcge'),
        'HK' => __('Hong Kong', 'scgcge'),
        'HU' => __('Hungary', 'scgcge'),
        'IS' => __('Iceland', 'scgcge'),
        'IN' => __('India', 'scgcge'),
        'ID' => __('Indonesia', 'scgcge'),
        'IR' => __('Iran, Islamic Republic Of', 'scgcge'),
        'IQ' => __('Iraq', 'scgcge'),
        'IE' => __('Ireland', 'scgcge'),
        'IM' => __('Isle Of Man', 'scgcge'),
        'IL' => __('Israel', 'scgcge'),
        'IT' => __('Italy', 'scgcge'),
        'JM' => __('Jamaica', 'scgcge'),
        'JP' => __('Japan', 'scgcge'),
        'JE' => __('Jersey', 'scgcge'),
        'JO' => __('Jordan', 'scgcge'),
        'KZ' => __('Kazakhstan', 'scgcge'),
        'KE' => __('Kenya', 'scgcge'),
        'KI' => __('Kiribati', 'scgcge'),
        'KP' => __('Korea, DPR', 'scgcge'),
        'KR' => __('Korea, Republic Of', 'scgcge'),
        'KW' => __('Kuwait', 'scgcge'),
        'KG' => __('Kyrgyzstan', 'scgcge'),
        'LA' => __('Lao, PDR', 'scgcge'),
        'LV' => __('Latvia', 'scgcge'),
        'LB' => __('Lebanon', 'scgcge'),
        'LS' => __('Lesotho', 'scgcge'),
        'LR' => __('Liberia', 'scgcge'),
        'LY' => __('Libyan Arab Jamahiriya', 'scgcge'),
        'LI' => __('Liechtenstein', 'scgcge'),
        'LT' => __('Lithuania', 'scgcge'),
        'LU' => __('Luxembourg', 'scgcge'),
        'MO' => __('Macao', 'scgcge'),
        'MK' => __('Macedonia, The Former Yugoslav', 'scgcge'),
        'MG' => __('Madagascar', 'scgcge'),
        'MW' => __('Malawi', 'scgcge'),
        'MY' => __('Malaysia', 'scgcge'),
        'MV' => __('Maldives', 'scgcge'),
        'ML' => __('Mali', 'scgcge'),
        'MT' => __('Malta', 'scgcge'),
        'MH' => __('Marshall Islands', 'scgcge'),
        'MQ' => __('Martinique', 'scgcge'),
        'MR' => __('Mauritania', 'scgcge'),
        'MU' => __('Mauritius', 'scgcge'),
        'YT' => __('Mayotte', 'scgcge'),
        'MX' => __('Mexico', 'scgcge'),
        'FM' => __('Micronesia', 'scgcge'),
        'MD' => __('Moldova, Republic Of', 'scgcge'),
        'MC' => __('Monaco', 'scgcge'),
        'MN' => __('Mongolia', 'scgcge'),
        'ME' => __('Montenegro', 'scgcge'),
        'MS' => __('Montserrat', 'scgcge'),
        'MA' => __('Morocco', 'scgcge'),
        'MZ' => __('Mozambique', 'scgcge'),
        'MM' => __('Myanmar', 'scgcge'),
        'NA' => __('Namibia', 'scgcge'),
        'NR' => __('Nauru', 'scgcge'),
        'NP' => __('Nepal', 'scgcge'),
        'NL' => __('Netherlands', 'scgcge'),
        'NC' => __('New Caledonia', 'scgcge'),
        'NZ' => __('New Zealand', 'scgcge'),
        'NI' => __('Nicaragua', 'scgcge'),
        'NE' => __('Niger', 'scgcge'),
        'NG' => __('Nigeria', 'scgcge'),
        'NU' => __('Niue', 'scgcge'),
        'NF' => __('Norfolk Island', 'scgcge'),
        'MP' => __('Northern Mariana Islands', 'scgcge'),
        'NO' => __('Norway', 'scgcge'),
        'OM' => __('Oman', 'scgcge'),
        'PK' => __('Pakistan', 'scgcge'),
        'PW' => __('Palau', 'scgcge'),
        'PS' => __('Palestine, State Of', 'scgcge'),
        'PA' => __('Panama', 'scgcge'),
        'PG' => __('Papua New Guinea', 'scgcge'),
        'PY' => __('Paraguay', 'scgcge'),
        'PE' => __('Peru', 'scgcge'),
        'PH' => __('Philippines', 'scgcge'),
        'PN' => __('Pitcairn', 'scgcge'),
        'PL' => __('Poland', 'scgcge'),
        'PT' => __('Portugal', 'scgcge'),
        'PR' => __('Puerto Rico', 'scgcge'),
        'QA' => __('Qatar', 'scgcge'),
        'RE' => __('Reunion', 'scgcge'),
        'RO' => __('Romania', 'scgcge'),
        'RU' => __('Russian Federation', 'scgcge'),
        'RW' => __('Rwanda', 'scgcge'),
        'BL' => __('Saint Barthelemy', 'scgcge'),
        'SH' => __('St Helena Ascension Tris Cunha', 'scgcge'),
        'KN' => __('Saint Kitts And Nevis', 'scgcge'),
        'LC' => __('Saint Lucia', 'scgcge'),
        'MF' => __('Saint Martin (French Part)', 'scgcge'),
        'PM' => __('Saint Pierre And Miquelon', 'scgcge'),
        'VC' => __('St Vincent And Grenadines', 'scgcge'),
        'WS' => __('Samoa', 'scgcge'),
        'SM' => __('San Marino', 'scgcge'),
        'ST' => __('Sao Tome And Principe', 'scgcge'),
        'SA' => __('Saudi Arabia', 'scgcge'),
        'SN' => __('Senegal', 'scgcge'),
        'RS' => __('Serbia', 'scgcge'),
        'SC' => __('Seychelles', 'scgcge'),
        'SL' => __('Sierra Leone', 'scgcge'),
        'SG' => __('Singapore', 'scgcge'),
        'SX' => __('Sint Maarten (Dutch Part)', 'scgcge'),
        'SK' => __('Slovakia', 'scgcge'),
        'SI' => __('Slovenia', 'scgcge'),
        'SB' => __('Solomon Islands', 'scgcge'),
        'SO' => __('Somalia', 'scgcge'),
        'ZA' => __('South Africa', 'scgcge'),
        'GS' => __('Sth Georgia Sth Sandwich Is', 'scgcge'),
        'ES' => __('Spain', 'scgcge'),
        'LK' => __('Sri Lanka', 'scgcge'),
        'SD' => __('Sudan', 'scgcge'),
        'SR' => __('Suriname', 'scgcge'),
        'SJ' => __('Svalbard And Jan Mayen', 'scgcge'),
        'SZ' => __('Swaziland', 'scgcge'),
        'SE' => __('Sweden', 'scgcge'),
        'CH' => __('Switzerland', 'scgcge'),
        'SY' => __('Syrian Arab Republic', 'scgcge'),
        'TW' => __('Taiwan', 'scgcge'),
        'TJ' => __('Tajikistan', 'scgcge'),
        'TZ' => __('Tanzania, United Republic Of', 'scgcge'),
        'TH' => __('Thailand', 'scgcge'),
        'TL' => __('Timor-Leste', 'scgcge'),
        'TG' => __('Togo', 'scgcge'),
        'TK' => __('Tokelau', 'scgcge'),
        'TO' => __('Tonga', 'scgcge'),
        'TT' => __('Trinidad And Tobago', 'scgcge'),
        'TN' => __('Tunisia', 'scgcge'),
        'TR' => __('Turkey', 'scgcge'),
        'TM' => __('Turkmenistan', 'scgcge'),
        'TC' => __('Turks And Caicos Islands', 'scgcge'),
        'TV' => __('Tuvalu', 'scgcge'),
        'UG' => __('Uganda', 'scgcge'),
        'UA' => __('Ukraine', 'scgcge'),
        'AE' => __('United Arab Emirates', 'scgcge'),
        'GB' => __('United Kingdom', 'scgcge'),
        'US' => __('United States', 'scgcge'),
        'UM' => __('US Minor Outlying Islands', 'scgcge'),
        'UY' => __('Uruguay', 'scgcge'),
        'UZ' => __('Uzbekistan', 'scgcge'),
        'VU' => __('Vanuatu', 'scgcge'),
        'VE' => __('Venezuela, Bolivarian Republic', 'scgcge'),
        'VN' => __('Viet Nam', 'scgcge'),
        'VG' => __('Virgin Islands, British', 'scgcge'),
        'VI' => __('Virgin Islands, U.S.', 'scgcge'),
        'WF' => __('Wallis And Futuna', 'scgcge'),
        'EH' => __('Western Sahara', 'scgcge'),
        'YE' => __('Yemen', 'scgcge'),
        'ZM' => __('Zambia', 'scgcge'),
        'ZW' => __('Zimbabwe', 'scgcge')
    ];
    return $countries[$code];
}

/**
 * Get Country Options
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function wp_scgcge_get_countries_options($val = '')
{
    $countries = [
        'AF' => __('Afghanistan', 'scgcge'),
        'AX' => __('Aland Islands', 'scgcge'),
        'AL' => __('Albania', 'scgcge'),
        'DZ' => __('Algeria', 'scgcge'),
        'AS' => __('American Samoa', 'scgcge'),
        'AD' => __('Andorra', 'scgcge'),
        'AO' => __('Angola', 'scgcge'),
        'AI' => __('Anguilla', 'scgcge'),
        'AQ' => __('Antarctica', 'scgcge'),
        'AG' => __('Antigua And Barbuda', 'scgcge'),
        'AR' => __('Argentina', 'scgcge'),
        'AM' => __('Armenia', 'scgcge'),
        'AW' => __('Aruba', 'scgcge'),
        'AU' => __('Australia', 'scgcge'),
        'AT' => __('Austria', 'scgcge'),
        'AZ' => __('Azerbaijan', 'scgcge'),
        'BS' => __('Bahamas', 'scgcge'),
        'BH' => __('Bahrain', 'scgcge'),
        'BD' => __('Bangladesh', 'scgcge'),
        'BB' => __('Barbados', 'scgcge'),
        'BY' => __('Belarus', 'scgcge'),
        'BE' => __('Belgium', 'scgcge'),
        'BZ' => __('Belize', 'scgcge'),
        'BJ' => __('Benin', 'scgcge'),
        'BM' => __('Bermuda', 'scgcge'),
        'BT' => __('Bhutan', 'scgcge'),
        'BO' => __('Bolivia', 'scgcge'),
        'BQ' => __('Bonaire St Eustatius And Saba', 'scgcge'),
        'BA' => __('Bosnia and Herzegovina', 'scgcge'),
        'BW' => __('Botswana', 'scgcge'),
        'BV' => __('Bouvet Island', 'scgcge'),
        'BR' => __('Brazil', 'scgcge'),
        'IO' => __('British Indian Ocean Territory', 'scgcge'),
        'BN' => __('Brunei Darussalam', 'scgcge'),
        'BG' => __('Bulgaria', 'scgcge'),
        'BF' => __('Burkina Faso', 'scgcge'),
        'BI' => __('Burundi', 'scgcge'),
        'KH' => __('Cambodia', 'scgcge'),
        'CM' => __('Cameroon', 'scgcge'),
        'CA' => __('Canada', 'scgcge'),
        'CV' => __('Cape Verde', 'scgcge'),
        'KY' => __('Cayman Islands', 'scgcge'),
        'CF' => __('Central African Republic', 'scgcge'),
        'TD' => __('Chad', 'scgcge'),
        'CL' => __('Chile', 'scgcge'),
        'CN' => __('China', 'scgcge'),
        'CX' => __('Christmas Island', 'scgcge'),
        'CC' => __('Cocos (Keeling) Islands', 'scgcge'),
        'CO' => __('Colombia', 'scgcge'),
        'KM' => __('Comoros', 'scgcge'),
        'CG' => __('Congo', 'scgcge'),
        'CD' => __('Congo, Democratic Republic Of', 'scgcge'),
        'CK' => __('Cook Islands', 'scgcge'),
        'CR' => __('Costa Rica', 'scgcge'),
        'CI' => __('Cote D\'Ivoire', 'scgcge'),
        'HR' => __('Croatia', 'scgcge'),
        'CU' => __('Cuba', 'scgcge'),
        'CW' => __('Curacao', 'scgcge'),
        'CY' => __('Cyprus', 'scgcge'),
        'CZ' => __('Czech Republic', 'scgcge'),
        'DK' => __('Denmark', 'scgcge'),
        'DJ' => __('Djibouti', 'scgcge'),
        'DM' => __('Dominica', 'scgcge'),
        'DO' => __('Dominican Republic', 'scgcge'),
        'EC' => __('Ecuador', 'scgcge'),
        'EG' => __('Egypt', 'scgcge'),
        'SV' => __('El Salvador', 'scgcge'),
        'GQ' => __('Equatorial Guinea', 'scgcge'),
        'ER' => __('Eritrea', 'scgcge'),
        'EE' => __('Estonia', 'scgcge'),
        'ET' => __('Ethiopia', 'scgcge'),
        'FK' => __('Falkland Islands (Malvinas)', 'scgcge'),
        'FO' => __('Faroe Islands', 'scgcge'),
        'FJ' => __('Fiji', 'scgcge'),
        'FI' => __('Finland', 'scgcge'),
        'FR' => __('France', 'scgcge'),
        'GF' => __('French Guiana', 'scgcge'),
        'PF' => __('French Polynesia', 'scgcge'),
        'TF' => __('French Southern Territories', 'scgcge'),
        'GA' => __('Gabon', 'scgcge'),
        'GM' => __('Gambia', 'scgcge'),
        'GE' => __('Georgia', 'scgcge'),
        'DE' => __('Germany', 'scgcge'),
        'GH' => __('Ghana', 'scgcge'),
        'GI' => __('Gibraltar', 'scgcge'),
        'GR' => __('Greece', 'scgcge'),
        'GL' => __('Greenland', 'scgcge'),
        'GD' => __('Grenada', 'scgcge'),
        'GP' => __('Guadeloupe', 'scgcge'),
        'GU' => __('Guam', 'scgcge'),
        'GT' => __('Guatemala', 'scgcge'),
        'GG' => __('Guernsey', 'scgcge'),
        'GN' => __('Guinea', 'scgcge'),
        'GW' => __('Guinea-Bissau', 'scgcge'),
        'GY' => __('Guyana', 'scgcge'),
        'HT' => __('Haiti', 'scgcge'),
        'HM' => __('Heard Is And Mcdonald Is', 'scgcge'),
        'VA' => __('Holy See (Vatican City State)', 'scgcge'),
        'HN' => __('Honduras', 'scgcge'),
        'HK' => __('Hong Kong', 'scgcge'),
        'HU' => __('Hungary', 'scgcge'),
        'IS' => __('Iceland', 'scgcge'),
        'IN' => __('India', 'scgcge'),
        'ID' => __('Indonesia', 'scgcge'),
        'IR' => __('Iran, Islamic Republic Of', 'scgcge'),
        'IQ' => __('Iraq', 'scgcge'),
        'IE' => __('Ireland', 'scgcge'),
        'IM' => __('Isle Of Man', 'scgcge'),
        'IL' => __('Israel', 'scgcge'),
        'IT' => __('Italy', 'scgcge'),
        'JM' => __('Jamaica', 'scgcge'),
        'JP' => __('Japan', 'scgcge'),
        'JE' => __('Jersey', 'scgcge'),
        'JO' => __('Jordan', 'scgcge'),
        'KZ' => __('Kazakhstan', 'scgcge'),
        'KE' => __('Kenya', 'scgcge'),
        'KI' => __('Kiribati', 'scgcge'),
        'KP' => __('Korea, DPR', 'scgcge'),
        'KR' => __('Korea, Republic Of', 'scgcge'),
        'KW' => __('Kuwait', 'scgcge'),
        'KG' => __('Kyrgyzstan', 'scgcge'),
        'LA' => __('Lao, PDR', 'scgcge'),
        'LV' => __('Latvia', 'scgcge'),
        'LB' => __('Lebanon', 'scgcge'),
        'LS' => __('Lesotho', 'scgcge'),
        'LR' => __('Liberia', 'scgcge'),
        'LY' => __('Libyan Arab Jamahiriya', 'scgcge'),
        'LI' => __('Liechtenstein', 'scgcge'),
        'LT' => __('Lithuania', 'scgcge'),
        'LU' => __('Luxembourg', 'scgcge'),
        'MO' => __('Macao', 'scgcge'),
        'MK' => __('Macedonia, The Former Yugoslav', 'scgcge'),
        'MG' => __('Madagascar', 'scgcge'),
        'MW' => __('Malawi', 'scgcge'),
        'MY' => __('Malaysia', 'scgcge'),
        'MV' => __('Maldives', 'scgcge'),
        'ML' => __('Mali', 'scgcge'),
        'MT' => __('Malta', 'scgcge'),
        'MH' => __('Marshall Islands', 'scgcge'),
        'MQ' => __('Martinique', 'scgcge'),
        'MR' => __('Mauritania', 'scgcge'),
        'MU' => __('Mauritius', 'scgcge'),
        'YT' => __('Mayotte', 'scgcge'),
        'MX' => __('Mexico', 'scgcge'),
        'FM' => __('Micronesia', 'scgcge'),
        'MD' => __('Moldova, Republic Of', 'scgcge'),
        'MC' => __('Monaco', 'scgcge'),
        'MN' => __('Mongolia', 'scgcge'),
        'ME' => __('Montenegro', 'scgcge'),
        'MS' => __('Montserrat', 'scgcge'),
        'MA' => __('Morocco', 'scgcge'),
        'MZ' => __('Mozambique', 'scgcge'),
        'MM' => __('Myanmar', 'scgcge'),
        'NA' => __('Namibia', 'scgcge'),
        'NR' => __('Nauru', 'scgcge'),
        'NP' => __('Nepal', 'scgcge'),
        'NL' => __('Netherlands', 'scgcge'),
        'NC' => __('New Caledonia', 'scgcge'),
        'NZ' => __('New Zealand', 'scgcge'),
        'NI' => __('Nicaragua', 'scgcge'),
        'NE' => __('Niger', 'scgcge'),
        'NG' => __('Nigeria', 'scgcge'),
        'NU' => __('Niue', 'scgcge'),
        'NF' => __('Norfolk Island', 'scgcge'),
        'MP' => __('Northern Mariana Islands', 'scgcge'),
        'NO' => __('Norway', 'scgcge'),
        'OM' => __('Oman', 'scgcge'),
        'PK' => __('Pakistan', 'scgcge'),
        'PW' => __('Palau', 'scgcge'),
        'PS' => __('Palestine, State Of', 'scgcge'),
        'PA' => __('Panama', 'scgcge'),
        'PG' => __('Papua New Guinea', 'scgcge'),
        'PY' => __('Paraguay', 'scgcge'),
        'PE' => __('Peru', 'scgcge'),
        'PH' => __('Philippines', 'scgcge'),
        'PN' => __('Pitcairn', 'scgcge'),
        'PL' => __('Poland', 'scgcge'),
        'PT' => __('Portugal', 'scgcge'),
        'PR' => __('Puerto Rico', 'scgcge'),
        'QA' => __('Qatar', 'scgcge'),
        'RE' => __('Reunion', 'scgcge'),
        'RO' => __('Romania', 'scgcge'),
        'RU' => __('Russian Federation', 'scgcge'),
        'RW' => __('Rwanda', 'scgcge'),
        'BL' => __('Saint Barthelemy', 'scgcge'),
        'SH' => __('St Helena Ascension Tris Cunha', 'scgcge'),
        'KN' => __('Saint Kitts And Nevis', 'scgcge'),
        'LC' => __('Saint Lucia', 'scgcge'),
        'MF' => __('Saint Martin (French Part)', 'scgcge'),
        'PM' => __('Saint Pierre And Miquelon', 'scgcge'),
        'VC' => __('St Vincent And Grenadines', 'scgcge'),
        'WS' => __('Samoa', 'scgcge'),
        'SM' => __('San Marino', 'scgcge'),
        'ST' => __('Sao Tome And Principe', 'scgcge'),
        'SA' => __('Saudi Arabia', 'scgcge'),
        'SN' => __('Senegal', 'scgcge'),
        'RS' => __('Serbia', 'scgcge'),
        'SC' => __('Seychelles', 'scgcge'),
        'SL' => __('Sierra Leone', 'scgcge'),
        'SG' => __('Singapore', 'scgcge'),
        'SX' => __('Sint Maarten (Dutch Part)', 'scgcge'),
        'SK' => __('Slovakia', 'scgcge'),
        'SI' => __('Slovenia', 'scgcge'),
        'SB' => __('Solomon Islands', 'scgcge'),
        'SO' => __('Somalia', 'scgcge'),
        'ZA' => __('South Africa', 'scgcge'),
        'GS' => __('Sth Georgia Sth Sandwich Is', 'scgcge'),
        'ES' => __('Spain', 'scgcge'),
        'LK' => __('Sri Lanka', 'scgcge'),
        'SD' => __('Sudan', 'scgcge'),
        'SR' => __('Suriname', 'scgcge'),
        'SJ' => __('Svalbard And Jan Mayen', 'scgcge'),
        'SZ' => __('Swaziland', 'scgcge'),
        'SE' => __('Sweden', 'scgcge'),
        'CH' => __('Switzerland', 'scgcge'),
        'SY' => __('Syrian Arab Republic', 'scgcge'),
        'TW' => __('Taiwan', 'scgcge'),
        'TJ' => __('Tajikistan', 'scgcge'),
        'TZ' => __('Tanzania, United Republic Of', 'scgcge'),
        'TH' => __('Thailand', 'scgcge'),
        'TL' => __('Timor-Leste', 'scgcge'),
        'TG' => __('Togo', 'scgcge'),
        'TK' => __('Tokelau', 'scgcge'),
        'TO' => __('Tonga', 'scgcge'),
        'TT' => __('Trinidad And Tobago', 'scgcge'),
        'TN' => __('Tunisia', 'scgcge'),
        'TR' => __('Turkey', 'scgcge'),
        'TM' => __('Turkmenistan', 'scgcge'),
        'TC' => __('Turks And Caicos Islands', 'scgcge'),
        'TV' => __('Tuvalu', 'scgcge'),
        'UG' => __('Uganda', 'scgcge'),
        'UA' => __('Ukraine', 'scgcge'),
        'AE' => __('United Arab Emirates', 'scgcge'),
        'GB' => __('United Kingdom', 'scgcge'),
        'US' => __('United States', 'scgcge'),
        'UM' => __('US Minor Outlying Islands', 'scgcge'),
        'UY' => __('Uruguay', 'scgcge'),
        'UZ' => __('Uzbekistan', 'scgcge'),
        'VU' => __('Vanuatu', 'scgcge'),
        'VE' => __('Venezuela, Bolivarian Republic', 'scgcge'),
        'VN' => __('Viet Nam', 'scgcge'),
        'VG' => __('Virgin Islands, British', 'scgcge'),
        'VI' => __('Virgin Islands, U.S.', 'scgcge'),
        'WF' => __('Wallis And Futuna', 'scgcge'),
        'EH' => __('Western Sahara', 'scgcge'),
        'YE' => __('Yemen', 'scgcge'),
        'ZM' => __('Zambia', 'scgcge'),
        'ZW' => __('Zimbabwe', 'scgcge')
    ];
    $html = "<option value='' " . ($val == '' ? 'selected' : '') . '>' . __('Select Country', 'scgcge') . '</option>';
    foreach ($countries as $code => $country) {
        $html .= "<option value='$code' " . ($val == $code ? 'selected' : '') . ">$country</option>";
    }
    return $html;
}

function checkXeroSettings()
{
    $wp_scgcge_options = get_option('wp_scgcge_options');
    if (
        $wp_scgcge_options['consumer_key'] &&
        $wp_scgcge_options['consumer_secret'] &&
        $wp_scgcge_options['public_cert_file'] &&
        $wp_scgcge_options['private_key_file'] &&
        $wp_scgcge_options['xero_account_code_asic'] &&
        $wp_scgcge_options['xero_account_code_sales_coy_reg'] &&
        $wp_scgcge_options['xero_account_code_sales'] &&
        $wp_scgcge_options['xero_asic_contact_id']
    ) {
        return true;
    }

    return false;
}

if (!function_exists('dd')) {
    function dd($x)
    {
        echo '<pre>';
        array_map(function ($x) {
            print_r($x);
        }, func_get_args());
        die;
    }
}

/**
 * Process Xero
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function process_xero($order_id)
{
    global $wpdb;
    
    // Xero oAuth2.0 Check
    $xeroAuth20 = false;
    if(get_option('getedge_xero_auth_expiration') !== null && get_option('getedge_xero_auth_expiration') > time()) {
        $xeroAuth20 = true;
    }
    
    $wp_scgcge_options = get_option('wp_scgcge_options');
    $ge_asic_fee_id = !empty($wp_scgcge_options['ge_asic_fee_id']) ? $wp_scgcge_options['ge_asic_fee_id'] : '';
    $xero_prefix = !empty($wp_scgcge_options['xero_prefix']) ? $wp_scgcge_options['xero_prefix'] : '';
    $xero_account_code_asic = !empty($wp_scgcge_options['xero_account_code_asic']) ? $wp_scgcge_options['xero_account_code_asic'] : '';
    $xero_account_code_sales_coy_reg = !empty($wp_scgcge_options['xero_account_code_sales_coy_reg']) ? $wp_scgcge_options['xero_account_code_sales_coy_reg'] : '';
    $xero_account_code_sales = !empty($wp_scgcge_options['xero_account_code_sales']) ? $wp_scgcge_options['xero_account_code_sales'] : '';
    $xero_asic_contact_id = !empty($wp_scgcge_options['xero_asic_contact_id']) ? $wp_scgcge_options['xero_asic_contact_id'] : '';



    if ($xeroAuth20 == true) {
        $xero = new \XeroPHP\Application(get_option('getedge_xero_auth_token'), get_option('getedge_xero_org_tenant_id'));
        $reference = $xero_prefix ? ($xero_prefix . '-' . $order_id) : ('Order #' . $order_id);
        $invoice_serial = $xero_prefix ? ($xero_prefix . '-' . $order_id) : ('ORDER-' . $order_id);
        $invoice_items = [];
        $order = wc_get_order($order_id);

        $has_asic = false;

        foreach ($order->get_items() as $item) {
            $item_id = $item->get_id();
            $product_id = $item->get_product_id();

            $prod = wc_get_product($product_id);
            $item_sku = $prod->get_sku();

            $wp_scgcge_options = get_option('wp_scgcge_options');
            $reg_fee_id = !empty($wp_scgcge_options['ge_coy_reg_fee_id']) ? $wp_scgcge_options['ge_coy_reg_fee_id'] : '';
            if ($product_id == $reg_fee_id) {
                $query = "SELECT company_name_full FROM $wpdb->prefix" . "asic_companies WHERE order_id = '" . $order_id . "'";
                $company_name_full = $wpdb->get_var($query);

                $invoice_items[] = [
                    'ItemCode' => ($item_sku !== null && $item_sku != '') ? $item_sku : '',
                    'Description' => $xero_prefix . " Service Fee\n" . $company_name_full,
                    'TaxType' => 'OUTPUT',
                    'Quantity' => '1.0000',
                    'UnitAmount' => $item->get_total() + $item->get_total_tax(),
                    'AccountCode' => $xero_account_code_sales_coy_reg
                ];

                $reference = $company_name_full;
            } elseif ($product_id == $ge_asic_fee_id) {
                $invoice_items[] = [
                    'ItemCode' => ($item_sku !== null && $item_sku != '') ? $item_sku : '',
                    'Description' => "ASIC Registration Fee\n" . $reference,
                    //"TaxType" => "EXEMPTOUTPUT",
                    'Quantity' => '1.0000',
                    'UnitAmount' => $item->get_total() + $item->get_total_tax(),
                    'AccountCode' => $xero_account_code_asic
                ];
                $has_asic = true;
            } else {
                $invoice_items[] = [
                    'ItemCode' => ($item_sku !== null && $item_sku != '') ? $item_sku : '',
                    'Description' => $item->get_name(),
                    'TaxType' => 'OUTPUT',
                    'Quantity' => $item->get_quantity(),
                    'UnitAmount' => $item->get_total() + $item->get_total_tax(),
                    'AccountCode' => $xero_account_code_sales
                ];
            }
        }

        $new_contact = [
            [
                'Name' => ucfirst($order->get_billing_first_name()) . ' ' . ucfirst($order->get_billing_last_name()),
                'FirstName' => ucfirst($order->get_billing_first_name()),
                'LastName' => ucfirst($order->get_billing_last_name()),
                'EmailAddress' => $order->get_billing_email(),
                'Addresses' => [
                    'Address' => [
                        [
                            'AddressType' => 'POBOX',
                            'AddressLine1' => ucfirst($order->get_billing_address_1()),
                            'City' => ucfirst($order->get_billing_city()),
                            'Region' => ucfirst($order->get_billing_state()),
                            'PostalCode' => ucfirst($order->get_billing_postcode()),
                            'Country' => ucfirst($order->get_billing_country())
                        ],
                        [
                            'AddressType' => 'STREET',
                            'AddressLine1' => ucfirst($order->get_billing_address_1()),
                            'City' => ucfirst($order->get_billing_city()),
                            'Region' => ucfirst($order->get_billing_state()),
                            'PostalCode' => ucfirst($order->get_billing_postcode()),
                            'Country' => ucfirst($order->get_billing_country())
                        ]
                    ]
                ]
            ]
        ];
        
        
            $contact = $xero->load(\XeroPHP\Models\Accounting\Contact::class)->where('Name', $new_contact[0]['Name'])->where('EmailAddress', $new_contact[0]['EmailAddress'])->first();
            if(!isset($contact)) {
                $address1 = new \XeroPHP\Models\Accounting\Address($xero);
                $address1->setAddressType($new_contact[0]['Addresses']['Address'][0]['AddressType'])
                    ->setAddressLine1($new_contact[0]['Addresses']['Address'][0]['AddressLine1'])
                    ->setCity($new_contact[0]['Addresses']['Address'][0]['City'])
                    ->setRegion($new_contact[0]['Addresses']['Address'][0]['Region'])
                    ->setPostalCode($new_contact[0]['Addresses']['Address'][0]['PostalCode'])
                    ->setCountry($new_contact[0]['Addresses']['Address'][0]['Country']);
                $address1->save();
                $address2 = new \XeroPHP\Models\Accounting\Address($xero);
                $address2->setAddressType($new_contact[0]['Addresses']['Address'][0]['AddressType'])
                    ->setAddressLine1($new_contact[0]['Addresses']['Address'][0]['AddressLine1'])
                    ->setCity($new_contact[0]['Addresses']['Address'][0]['City'])
                    ->setRegion($new_contact[0]['Addresses']['Address'][0]['Region'])
                    ->setPostalCode($new_contact[0]['Addresses']['Address'][0]['PostalCode'])
                    ->setCountry($new_contact[0]['Addresses']['Address'][0]['Country']);
                $address2->save();
                $contact = new \XeroPHP\Models\Accounting\Contact($xero);
                $contact->setName($new_contact[0]['Name'])
                    ->setFirstName($new_contact[0]['FirstName'])
                    ->setLastName($new_contact[0]['LastName'])
                    ->addAddress($address1)
                    ->addAddress($address2)
                    ->setEmailAddress($new_contact[0]['EmailAddress']);
                $contact->save();
            }
            $contactID = $contact->getContactID();
        
        
        $new_invoice = [
            [
                'Type' => 'ACCREC', // ACCREC and ACCPAY
                'Reference' => $reference, // ACCREC and ACCPAY
                'InvoiceNumber' => $invoice_serial,
                'Contact' => [
                    'ContactID' => $contact_result_id // $contact_result->Contacts->Contact->ContactID
                ],
                'Date' => date('Y-m-d'), // YYYY-MM-DD
                'DueDate' => date('Y-m-d'), // YYYY-MM-DD
                'Status' => 'AUTHORISED', // DRAFT and SUBMITTED and AUTHORISED
                'LineAmountTypes' => 'Inclusive', // Inclusive and Exclusive and NoTax
                'LineItems' => [
                    'LineItem' => $invoice_items
                ]
            ]
        ];
                
            $xeroInvoice = new \XeroPHP\Models\Accounting\Invoice($xero);
            $xeroInvoice->setType($new_invoice[0]['Type'])
                ->setReference($new_invoice[0]['Reference'])
                ->setInvoiceNumber($new_invoice[0]['InvoiceNumber'])
                ->setDate(\DateTime::createFromFormat('Y-m-d', date('Y-m-d')))
                ->setDueDate(\DateTime::createFromFormat('Y-m-d', date('Y-m-d')))
                ->setStatus('AUTHORISED')
                ->setContact($contact)
                ->setLineAmountType('Inclusive');
            foreach($invoice_items as $line) {
                $item = new \XeroPHP\Models\Accounting\LineItem();
                $item->setDescription($line['Description'])
                    ->setQuantity($line['quantity'])
                    ->setUnitAmount($line['UnitAmount'])
                    ->setTaxType($line['TaxType'])
                    ->setAccountCode($line['AccountCode']);
                $xeroInvoice->addLineItem($item);
            }
            $xeroInvoice->save();
            $invoice_result_id = $xeroInvoice->InvoiceID;
            $invoice_result_number = $xeroInvoice->InvoiceNumber;
            $url = new \XeroPHP\Remote\URL($xero, 'Invoices/' . $xeroInvoice->InvoiceID . '/OnlineInvoice');
            $request = new \XeroPHP\Remote\Request($xero, $url);
            $request->send();
            $pdf_invoice_url = $request->getResponse()->getElements()[0]['OnlineInvoiceUrl'];

        update_post_meta($order_id, '_xero2_invoice_id', $invoice_result_id);
        update_post_meta($order_id, '_xero2_invoice_number', $invoice_result_number);
        update_post_meta($order_id, '_xero2_invoice_url', $pdf_invoice_url);
        
        $message = sprintf(__('Invoice %s was pushed to Xero.', 'scgcge'), $invoice_result_number);
        $order->add_order_note($message);

        if ($has_asic == true) {
            $new_bill = [
                [
                    'Type' => 'ACCPAY', // ACCREC and ACCPAY
                    'InvoiceNumber' => $invoice_serial, // ACCREC and ACCPAY
                    'Contact' => [
                        'ContactID' => $xero_asic_contact_id // $contact_result->Contacts->Contact->ContactID
                    ],
                    'Date' => date('Y-m-d'), // YYYY-MM-DD
                    'DueDate' => date('Y-m-d', strtotime(date('Y-m-d') . ' +2 day')), // YYYY-MM-DD
                    'Status' => 'AUTHORISED', // DRAFT and SUBMITTED and AUTHORISED
                    'LineAmountTypes' => 'Inclusive', // Inclusive and Exclusive and NoTax
                    'LineItems' => [
                        'LineItem' => [
                            [
                                //"ItemCode" => "REG701",
                                'Description' => "ASIC Registration Fee\n" . $reference,
                                'Quantity' => '1.0000',
                                //"TaxType" => "EXEMPTEXPENSES",
                                'UnitAmount' => wc_get_product($ge_asic_fee_id)->get_price(),
                                'AccountCode' => $xero_account_code_asic
                            ]
                        ]
                    ]
                ]
            ];
            
            $contact = $xero->loadByGUID(\XeroPHP\Models\Accounting\Contact::class, $new_bill[0]['Contact']['ContactID']);
            
                $xeroBill = new \XeroPHP\Models\Accounting\Invoice($xero);
                $xeroBill->setType($new_bill[0]['Type'])
                    ->setInvoiceNumber($new_bill[0]['InvoiceNumber'])
                    ->setDate(\DateTime::createFromFormat('Y-m-d', date('Y-m-d')))
                    ->setDueDate(\DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime(date('Y-m-d') . ' +2 day'))))
                    ->setStatus('AUTHORISED')
                    ->setContact($contact)
                    ->setLineAmountType('Inclusive');
                $item = new \XeroPHP\Models\Accounting\LineItem();
                $item->setDescription($new_bill[0]['LineItems']['LineItem'][0]['Description'])
                    ->setQuantity($new_bill[0]['LineItems']['LineItem'][0]['Quantity'])
                    ->setUnitAmount($new_bill[0]['LineItems']['LineItem'][0]['UnitAmount'])
                    //->setTaxType($new_bill[0]['LineItems']['LineItem'][0]['TaxType'])
                    ->setAccountCode($new_bill[0]['LineItems']['LineItem'][0]['AccountCode']);
                $xeroBill->addLineItem($item);
                $xeroBill->save();
                $bill_result_id = $xeroInvoice->InvoiceID;
                $bill_result_number = $xeroInvoice->InvoiceNumber;

            // add the order note
            $message = sprintf(__('Bill %s was pushed to Xero.', 'scgcge'), $bill_result_number);
            $order->add_order_note($message);
        }
    } else {
        $order = wc_get_order($order_id);
        // add the Not match Public key and Private key
        $message = __('Error processing Xero request. Public certificate and private key files are not found.', 'scgcge');
        $order->add_order_note($message);
    }
}

/**
 * Test Xero
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */
function test_xero($order_id)
{
    //
}
