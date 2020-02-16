<?php
/**
 * Plugin Name: GFW Woocommerce customizations
 * Plugin URI: https://github.com/alexmoise/gfw-woocommerce-customizations
 * GitHub Plugin URI: https://github.com/alexmoise/gfw-woocommerce-customizations
 * Description: A custom plugin to add required customizations to Girlfridayweddings Woocommerce shop and to style the front end as required. For details/troubleshooting please contact me at <a href="https://moise.pro/contact/">https://moise.pro/contact/</a>
 * Version: 0.2
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










?>
