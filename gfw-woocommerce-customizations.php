<?php
/**
 * Plugin Name: GFW Woocommerce customizations
 * Plugin URI: https://github.com/alexmoise/gfw-woocommerce-customizations
 * GitHub Plugin URI: https://github.com/alexmoise/gfw-woocommerce-customizations
 * Description: A custom plugin to add required customizations to Girlfridayweddings Woocommerce shop and to style the front end as required. For details/troubleshooting please contact me at <a href="https://moise.pro/contact/">https://moise.pro/contact/</a>
 * Version: 0.28
 * Author: Alex Moise
 * Author URI: https://moise.pro
 */

if ( ! defined( 'ABSPATH' ) ) {	exit(0); }

// Redirect shop-related URLs to home if "!is_user_logged_in()"
// Not needed anymore as the shop is live already (maybe delete this function altogether?)
// add_action( 'template_redirect', 'mogfw_keep_shop_private' );
function mogfw_keep_shop_private() {
	// Remove styles below when putting the shop live
	add_action( 'wp_head', function() { echo '<style>body:not(.logged-in) .xoo-wsc-modal { display: none; }</style>'; } );	// this hides the floating cart added with Woo Side Cart plugin
	add_action( 'wp_head', function() { echo '<style>body:not(.logged-in) .widget.widget_search { display: none !important; }</style>'; } );	// this hides the Search Widget
	if (!is_user_logged_in()) {
		if( strpos($_SERVER['REQUEST_URI'], '/shop/') !== false || strpos($_SERVER['REQUEST_URI'], '/product') !== false || strpos($_SERVER['REQUEST_URI'], '/product-category') !== false || strpos($_SERVER['REQUEST_URI'], '/cart') !== false || strpos($_SERVER['REQUEST_URI'], '/checkout') !== false ) {
			wp_redirect( '/');
			exit;
		}
	}
}

// Load our own CSS
add_action( 'wp_enqueue_scripts', 'mogfw_adding_styles', 9999999 );
function mogfw_adding_styles() {
	wp_register_style('mogfw-styles', plugins_url('gfwwc.css', __FILE__));
	wp_enqueue_style('mogfw-styles');
}

// Load our own JS
add_action( 'wp_enqueue_scripts', 'mogfw_adding_scripts', 9999999 );
function mogfw_adding_scripts() {
	wp_register_script('gfwwc-script', plugins_url('gfwwc.js', __FILE__), array('jquery'), '', true);
	wp_enqueue_script('gfwwc-script');
}

// === Few Woocommerce layout changes
// Apply no-sidebars layout in shop and products
// Adjust IF conditions later to match the requests
add_action( 'get_header', 'mogfw_nosidebars_inshop' );
function mogfw_nosidebars_inshop() {
	if ( is_product() || is_shop() || is_product_category() || is_cart() || is_checkout() || is_account_page() || is_wc_endpoint_url() ) {
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
	}
}
// Remove Add To Cart / Choose Options button in archives
add_action( 'init', 'mogfw_layout_adjustments' );
function mogfw_layout_adjustments() {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 15 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
}
// Remove Additional Information tab from Single Products
add_filter( 'woocommerce_product_tabs', 'mogfw_remove_product_tabs', 98);
function mogfw_remove_product_tabs($tabs) {
	unset($tabs['additional_information']);
	return $tabs;
}
// Adding Additional Information data as a string to Single Product
add_action( 'woocommerce_single_product_summary', 'mogfw_additional_information_string', 90);
function mogfw_additional_information_string() {
	global $product;
	$units_weight = get_option('woocommerce_weight_unit');
	$units_dimension = get_option('woocommerce_dimension_unit');
	if( $product->get_dimensions() ) { $product_dimensions = ' Dimensions: '.$product->get_dimensions(); }
	if( $product->get_weight() ) { $product_weight = ', Weight: '.$product->get_weight().' '.$units_weight; }
	if( $product->get_sku() ) { $product_sku = ', SKU: '.$product->get_sku(); }
	$additional_info_string = '<div class="additional_info_string">'.$product_dimensions.$product_weight.$product_sku.'</div>';
	echo $additional_info_string;
}

// === Product Filter at the top of the page (based on WOOF plugin)
// add_action( 'woocommerce_before_shop_loop', 'mogfw_add_woof_filter', 10 );
function mogfw_add_woof_filter() {
	echo do_shortcode('[woof]');
	// echo "We'll have the products filter here. (02)"; 
}

// === Email confirmation field functions
// Make original email field half width and add a new confirm email field
add_filter( 'woocommerce_checkout_fields' , 'mogfw_add_email_verification_field_checkout' );
function mogfw_add_email_verification_field_checkout( $fields ) {
	$fields['billing']['billing_email']['class'] = array( 'form-row-first' );
	$fields['billing']['billing_em_ver'] = array(
		'label' => 'Confirm mail Address',
		'required' => true,
		'class' => array( 'form-row-last' ),
		'clear' => true,
		'priority' => 998,
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

// === Add Date of Event field in checkout
// Add the field in the first place
add_filter( 'woocommerce_checkout_fields', 'mogfw_filter_checkout_fields' );
function mogfw_filter_checkout_fields($fields){
    $fields['extra_fields'] = array(
            'event_date' => array(
                'type' => 'date',
                'required'      => false,
                'label' => __( 'Event date' )
                )
            );

    return $fields;
}
// Display the field on the checkout form
add_action( 'woocommerce_checkout_after_customer_details' ,'mogfw_extra_checkout_fields' );
function mogfw_extra_checkout_fields(){ 
    $checkout = WC()->checkout(); ?>
    <div class="extra-fields" style="margin-bottom: 20px;">
		<h3 style="margin-top: 16px;"><?php _e( 'Additional Info' ); ?></h3>
		<?php 
		// automatically display everything added to the array in the previous function (so we could add more fields later)
		foreach ( $checkout->checkout_fields['extra_fields'] as $key => $field ) : ?>
			<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
			<?php endforeach; ?>
    </div>
<?php }
// Save the field at checkout processing
add_action( 'woocommerce_checkout_create_order', 'mogfw_save_extra_checkout_fields', 10, 2 );
function mogfw_save_extra_checkout_fields( $order, $data ){
    if( isset( $data['event_date'] ) ) {
        $order->update_meta_data( '_event_date', sanitize_text_field( $data['event_date'] ) );
    }
}
// Display the field on order recieved page and in order view on my-account page
add_action( 'woocommerce_thankyou', 'mogfw_display_order_data', 20 );
add_action( 'woocommerce_view_order', 'mogfw_display_order_data', 20 );
function mogfw_display_order_data( $order_id ){  
    $order = wc_get_order( $order_id ); ?>
    <h2><?php _e( 'Additional Info' ); ?></h2>
    <table class="shop_table shop_table_responsive additional_info">
        <tbody>
            <tr>
                <th><?php _e( 'Event date:' ); ?></th>
                <td><?php echo $order->get_meta( '_event_date' ); ?></td>
            </tr>
        </tbody>
    </table>
<?php }
// Display the field in the order edit screen
add_action( 'woocommerce_admin_order_data_after_order_details', 'mogfw_display_order_data_in_admin' );
function mogfw_display_order_data_in_admin( $order ){  ?>
    <div class="form-field form-field-wide">
        <h4><?php _e( 'Extra Details', 'woocommerce' ); ?></h4>
        <?php 
            echo '<label for="event_date" style="display: inline-block">' . __( 'Event Date' ) . ': </label><span style="display: inline-block;margin-left: 5px;">' . $order->get_meta( '_event_date' ) . '</span>'; 
		?>
    </div>
<?php }
// Add the field to order emails
add_filter( 'woocommerce_email_order_meta_fields', 'mogfw_email_order_meta_fields', 10, 3 );
function mogfw_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
    $fields['event_date'] = array(
                'label' => __( 'Event date' ),
                'value' => $order->get_meta( '_event_date' ),
            );
    return $fields;
}
// Woocommerce templates overrides
add_filter( 'woocommerce_locate_template', 'mogfw_replace_woocommerce_templates', 20, 3 );
function mogfw_replace_woocommerce_templates( $template, $template_name, $template_path ) {
	global $woocommerce;
	$_template = $template;
	if ( ! $template_path ) { $template_path = $woocommerce->template_url; }
	$plugin_path  = untrailingslashit( plugin_dir_path( __FILE__ ) )  . '/woocommerce/';
	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			$template_path . $template_name,
			$template_name
		)
	);
	if ( ! $template && file_exists( $plugin_path . $template_name ) ) { $template = $plugin_path . $template_name; }
	if ( ! $template ) { $template = $_template; }
	return $template;
}

?>
