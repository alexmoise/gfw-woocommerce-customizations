<?php
/**
 * Plugin Name: GFW Woocommerce customizations
 * Plugin URI: https://github.com/alexmoise/gfw-woocommerce-customizations
 * GitHub Plugin URI: https://github.com/alexmoise/gfw-woocommerce-customizations
 * Description: A custom plugin to add required customizations to Girlfridayweddings Woocommerce shop and to style the front end as required. For details/troubleshooting please contact me at <a href="https://moise.pro/contact/">https://moise.pro/contact/</a>
 * Version: 0.3
 * Author: Alex Moise
 * Author URI: https://moise.pro
 */

if ( ! defined( 'ABSPATH' ) ) {	exit(0);}

// Load our own CSS
add_action( 'wp_enqueue_scripts', 'mogfw_adding_styles', 9999999 );
function mogfw_adding_styles() {
	wp_register_style('mogfw-styles', plugins_url('gfwwc.css', __FILE__));
	wp_enqueue_style('mogfw-styles');
}

// === Woocommerce START
// Apply no-sidebars layout in shop and products
// Adjust IF conditions later to match the requests
add_action( 'get_header', 'mogfw_nosidebars_inshop' );
function mogfw_nosidebars_inshop() {
	if ( is_product() || is_shop() || is_product_category || is_cart || is_checkout || is_account_page() || is_wc_endpoint_url() ) {
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
	}
}
// Remove Add To Cart / Choose Options button in archives
add_action( 'init', 'mogfw_layout_adjustments' );
function mogfw_layout_adjustments() {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
}
// Email confirmation field functions
// Make original email field half width and add a new confirm email field
add_filter( 'woocommerce_checkout_fields' , 'mogfw_add_email_verification_field_checkout' );
function mogfw_add_email_verification_field_checkout( $fields ) {
	$fields['billing']['billing_email']['class'] = array( 'form-row-first' );
	$fields['billing']['billing_em_ver'] = array(
		'label' => 'Confirm mail Address',
		'required' => true,
		'class' => array( 'form-row-last' ),
		'clear' => true,
		'priority' => 999,
	);
	return $fields;
}
// Generate error message if field values are different
add_action('woocommerce_checkout_process', 'mogfw_matching_email_addresses'); 
function mogfw_matching_email_addresses() { 
    $email1 = $_POST['billing_email'];
    $email2 = $_POST['billing_em_ver'];
    if ( $email2 !== $email1 ) {
        wc_add_notice( 'Your email addresses do not match', 'error' );
    }
}
// === Woocommerce END









?>
