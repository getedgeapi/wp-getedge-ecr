<?php
    header('Content-type: text/css; charset: UTF-8');
    include_once '../../../../../wp-config.php';

    $wp_scgcge_options = get_option('wp_scgcge_options');

    $hex_accent_bg = $wp_scgcge_options['hex_accent_bg'];
    $hex_accent_colour = $wp_scgcge_options['hex_accent_colour'];
    $hex_hover_bg = $wp_scgcge_options['hex_hover_bg'];
    $hex_hover_colour = $wp_scgcge_options['hex_hover_colour'];

    if (!$hex_accent_bg || !$hex_accent_colour || !$hex_hover_bg || !$hex_hover_colour) {
        $hex_accent_bg = '#0665d0';
        $hex_accent_colour = '#ffffff';
        $hex_hover_bg = '#343a40';
        $hex_hover_colour = '#ffffff';
    }

    function hex2rgb( $colour ) {
        if ( $colour[0] == '#' ) {
                $colour = substr( $colour, 1 );
        }
        if ( strlen( $colour ) == 6 ) {
                list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
        } elseif ( strlen( $colour ) == 3 ) {
                list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
        } else {
                return false;
        }
        $r = hexdec( $r );
        $g = hexdec( $g );
        $b = hexdec( $b );
        return array( 'red' => $r, 'green' => $g, 'blue' => $b );
    }

    $rgb = hex2rgb($hex_accent_bg)['red'].', '.hex2rgb($hex_accent_bg)['green'].', '.hex2rgb($hex_accent_bg)['blue'];

?>

html body .bootstrapiso .btn-primary {
    background-color: <?=$hex_accent_bg?> !important;
    border-color: <?=$hex_accent_bg?> !important;
}
 html body .bootstrapiso .btn-primary:hover {
    background-color: <?=$hex_hover_bg?> !important;
    border-color: <?=$hex_hover_bg?> !important;
}

body .bootstrapiso .progress-bar {
    background-color: <?=$hex_accent_bg?> !important;
}

body .gform_wrapper .gform_body .gform_next_button.button, .searchflexiform .button, .single_add_to_cart_button.gform_button, body .gform_wrapper .gform_page_footer .button.gform_button, body .gform_wrapper .gform_body .gform_previous_button.button {
    background-color: <?=$hex_accent_bg?> !important;
    border: 1px solid <?=$hex_accent_bg?> !important;
}
body .gform_wrapper .gform_body .gform_next_button.button:hover,
body .gform_wrapper .gform_body .gform_previous_button.button:hover,
body .gform_wrapper .gform_page_footer .button.gform_button:hover,
.searchflexiform .button:hover,
.single_add_to_cart_button.gform_button:hover,
.single_add_to_cart_button.gform_button:active,
body .gform_wrapper .gform_body .gform_next_button.button:active,
.searchflexiform .search_button.button:active,
body .gform_wrapper .gform_page_footer .button.gform_button:active,
body .gform_wrapper .gform_body .gform_previous_button.button:active {
    background-color: <?=$hex_hover_bg?> !important;
    border: 1px solid <?=$hex_hover_bg?> !important;
    color: <?=$hex_hover_colour?> !important;
}

body .bootstrapiso .custom-radio .custom-control-input:checked ~ .custom-control-label::before {
    background-color: <?=$hex_accent_bg?> !important;
    box-shadow: 0 1px 1px rgba(<?=$rgb?>, 0.075) inset, 0 0 8px rgba(<?=$rgb?>, 0.4) !important;
}
body .bootstrapiso .custom-control-input:checked ~ .custom-control-label::before {
    background-color: <?=$hex_accent_bg?> !important;
    box-shadow: 0 1px 1px rgba(<?=$rgb?>, 0.075) inset, 0 0 8px rgba(<?=$rgb?>, 0.4) !important;
}

textarea:focus,
input[type="text"]:focus,
input[type="password"]:focus,
input[type="datetime"]:focus,
input[type="datetime-local"]:focus,
input[type="date"]:focus,
input[type="month"]:focus,
input[type="time"]:focus,
input[type="week"]:focus,
input[type="number"]:focus,
input[type="email"]:focus,
input[type="url"]:focus,
input[type="search"]:focus,
input[type="tel"]:focus,
input[type="color"]:focus,
.uneditable-input:focus {   
    border-color: rgba(<?=$rgb?>, 0.1) !important;
    box-shadow: 0 1px 1px rgba(<?=$rgb?>, 0.075) inset, 0 0 8px rgba(<?=$rgb?>, 0.4) !important;
    outline: 0 none !important;
}
