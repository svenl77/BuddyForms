<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function buddyforms_marketing_meet_condition() {
	$meet = false;


	return $meet;
}

add_action( 'admin_init', 'buddyforms_marketing_init' );

function buddyforms_marketing_init() {
//	add_action( 'admin_enqueue_scripts', 'buddyforms_marketing_assets' );
}

function buddyforms_marketing_assets() {

	$user_id = get_current_user_id();
	if ( empty( $user_id ) || ! is_user_logged_in() ) {
		return;
	}
	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	$base_content = "<p class=\"corner-head\">This is only for you</p><p class=\"corner-text\">%content</p><div class=\"bf-marketing-action-container\"><a href=\"#\" class=\"bf-marketing-btn corner-btn-close\">%no</a><a href=\"#\" class=\"bf-marketing-btn corner-btn-close\">%yes</a></div>";
	$content      = buddyforms_marketing_content_interest();
	$is_free      = false;
	$is_pro       = false;
	$is_trial     = false;

	$first_question = get_user_meta( $user_id, '_buddyforms_marketing_question1', true );
	$start_date     = get_user_meta( $user_id, '_buddyforms_marketing_start_date', true );

	$freeemius = buddyforms_core_fs();
	if ( empty( $freeemius ) ) {
		return;
	}

	$is_free  = $freeemius->is_free_plan();
	$is_pro   = $freeemius->is_premium();
	$is_trial = $freeemius->is_trial();
	$upgrade_url = $freeemius->get_upgrade_url();
	$license = $freeemius->_get_license();
//	$subscription = $freeemius->_get_subscription($license->id);

	if ( $is_free ) {
		$content = buddyforms_marketing_content_pro_coupon();
	} else if ( $is_trial ) {
		$content = buddyforms_marketing_content_pro_coupon();
	} else if ( $is_pro ) {

	}


	wp_enqueue_style( 'buddyforms-marketing-popup', BUDDYFORMS_ASSETS . 'resources/corner-popup/css/corner-popup.min.css', array(), BUDDYFORMS_VERSION );
	wp_enqueue_script( 'buddyforms-marketing-popup', BUDDYFORMS_ASSETS . 'resources/corner-popup/js/corner-popup.min.js', array( 'jquery' ), BUDDYFORMS_VERSION );
	wp_enqueue_script( 'buddyforms-marketing-popup-handler', BUDDYFORMS_ASSETS . 'admin/js/admin-marketing.js', array( 'jquery' ), BUDDYFORMS_VERSION );
	wp_localize_script( 'buddyforms-marketing-popup-handler', 'buddyformsMarketingHandler', array(
		'content' => str_replace( array_keys( $content ), array_values( $content ), $base_content ),
	) );
}

/**
 * Popup content asking to receive the offer
 */
function buddyforms_marketing_content_interest() {
	return array( '%content' => 'Are you interest in get a personal offer', '%no' => 'No Thanks', '%yes' => 'Yes' );
}

/**
 * Popup content to offer a discount coupon
 */
function buddyforms_marketing_content_pro_coupon() {
	return array( '%content' => 'Get your discount coupon', '%no' => 'No thanks', '%yes' => 'Yes please' );
}

/**
 * Popup content to upgrade from month to yearly
 */
function buddyforms_marketing_content_yearly_coupon() {
	return array( '%content' => 'Upgrade to yearly with a special copuon', '%no' => 'No thanks', '%yes' => 'Go for it' );
}

/**
 * Popup content to increase site quote
 */
function buddyforms_marketing_content_site_quote() {
	return array( '%content' => 'Be ready to install this amazing tool in more sites', '%no' => 'No thanks', '%yes' => 'Yes please' );
}