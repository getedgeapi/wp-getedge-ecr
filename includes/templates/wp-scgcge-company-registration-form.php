<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Company Registration
 *
 * Company Name Registartion End Point URL Get All Query Variable.
 *
 * @package SCGC GetEDGE API
 * @since 1.0.0
 */


//Get Current Segment Pages End points
$current_segment = wp_scgcge_current_segment();

switch ($current_segment) {
    
    case 'general-details':
        require_once ( WP_SCGC_GE_TEMPLATES . '/company-registration-forms/company-registration-general-details.php' );
    break;

    case 'addresses':
        require_once ( WP_SCGC_GE_TEMPLATES . '/company-registration-forms/company-registration-addresses.php');
    break;

    case 'entities':
        require_once ( WP_SCGC_GE_TEMPLATES . '/company-registration-forms/company-registration-entities.php' );
    break;

    case 'add-individual':
        require_once ( WP_SCGC_GE_TEMPLATES . '/company-registration-forms/company-registration-entity.php' );
    break;

    case 'add-company':
        require_once ( WP_SCGC_GE_TEMPLATES . '/company-registration-forms/company-registration-entity.php' );
    break;

    case 'edit-individual':
        require_once ( WP_SCGC_GE_TEMPLATES . '/company-registration-forms/company-registration-entity.php' );
    break;

    case 'edit-company':
        require_once ( WP_SCGC_GE_TEMPLATES . '/company-registration-forms/company-registration-entity.php' );
    break;

    case 'delete-entity':
        require_once ( WP_SCGC_GE_TEMPLATES . '/company-registration-forms/company-registration-entity.php' );
    break;

    case 'review':
        require_once ( WP_SCGC_GE_TEMPLATES . '/company-registration-forms/company-registration-review.php' );
    break;
}