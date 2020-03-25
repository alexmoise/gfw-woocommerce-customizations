/** 
 * JS functions for GFW Woocommerce customizations plugin
 * Version 0.21
 * (version above is equal with main plugin file version when this file was updated)
 */

// Just a debug version so we can see it in console thus being sure the cache doesn't play tricks on us :-P
jQuery(document).ready(function() {
	console.log('Loaded! v21');
});

// Call the plus_minus function here for the initial setup when document.ready:
jQuery(document).ready(function() {
	quantity_plus_minus();
});

// Add the plus/minus button to Quantity box
function quantity_plus_minus() {
	if(jQuery("div.quantity .plus").length == 0) {
		jQuery("<div class='plus'>+</div>").appendTo("div.quantity");
	}
	if(jQuery("div.quantity .minus").length == 0) {
		jQuery("<div class='minus'>-</div>").prependTo("div.quantity");
	}
	jQuery('div.quantity .minus').click(function () {
		var $input = jQuery(this).parent().find('input');
		var count = parseInt($input.val()) - 1;
		count = count < 1 ? 1 : count;
		$input.val(count);
		$input.change();
		return false;
	});
	jQuery('div.quantity .plus').click(function () {
		var $input = jQuery(this).parent().find('input');
		$input.val(parseInt($input.val()) + 1);
		$input.change();
		return false;
	});
};
// Call the plus_minus function at each cart update
jQuery(document.body).on('updated_cart_totals', function() { quantity_plus_minus(); });



