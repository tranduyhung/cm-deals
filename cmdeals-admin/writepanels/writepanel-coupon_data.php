<?php
/**
 * Coupon Data
 * 
 * Functions for displaying the coupon data meta box
 *
 * @package WordPress
 * @subpackage CM Deals
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * Coupon data meta box
 * 
 * Displays the meta box
 */
function cmdeals_coupon_data_meta_box($post) {
	global $cmdeals;
	
	wp_nonce_field( 'cmdeals_save_data', 'cmdeals_meta_nonce' );
	
	?>
	<style type="text/css">
		#edit-slug-box { display:none }
	</style>
	<div id="coupon_options" class="panel cmdeals_options_panel">
		<?php

			// Type
    		cmdeals_wp_select( array( 'id' => 'discount_type', 'label' => __('Discount type', 'cmdeals'), 'options' => $cmdeals->get_coupon_discount_types() ) );
				
			// Amount
			cmdeals_wp_text_input( array( 'id' => 'coupon_amount', 'label' => __('Coupon amount', 'cmdeals'), 'placeholder' => __('0.00', 'cmdeals'), 'description' => __('Enter an amount e.g. 2.99', 'cmdeals') ) );
				
			// Individual use
			cmdeals_wp_checkbox( array( 'id' => 'individual_use', 'label' => __('Individual use', 'cmdeals'), 'description' => __('Check this box if the coupon cannot be used in conjunction with other coupons', 'cmdeals') ) );
			
			// Apply before tax
			cmdeals_wp_checkbox( array( 'id' => 'apply_before_tax', 'label' => __('Apply before tax', 'cmdeals'), 'description' => __('Check this box if the coupon should be applied before calculating cart tax', 'cmdeals') ) );
			
			// Free Shipping
			cmdeals_wp_checkbox( array( 'id' => 'free_shipping', 'label' => __('Enable free shipping', 'cmdeals'), 'description' => sprintf(__('Check this box if the coupon enables free shipping (see <a href="%s">Free Shipping</a>)', 'cmdeals'), admin_url('admin.php?page=cmdeals&tab=shipping_methods&subtab=shipping-free_shipping')) ) );
			
			// Deal ids
			cmdeals_wp_text_input( array( 'id' => 'deal_ids', 'label' => __('Deal IDs', 'cmdeals'), 'placeholder' => __('N/A', 'cmdeals'), 'description' => __('(optional) Comma separate IDs which need to be in the cart to use this coupon or, for "Deal Discounts", which deals are discounted.', 'cmdeals') ) );
			
			// Exclude Deal ids
			cmdeals_wp_text_input( array( 'id' => 'exclude_deals_ids', 'label' => __('Exclude Deal IDs', 'cmdeals'), 'placeholder' => __('N/A', 'cmdeals'), 'description' => __('(optional) Comma separate IDs which must not be in the cart to use this coupon or, for "Deal Discounts", which deals are not discounted.', 'cmdeals') ) );
			
			// Usage limit
			cmdeals_wp_text_input( array( 'id' => 'usage_limit', 'label' => __('Usage limit', 'cmdeals'), 'placeholder' => __('Unlimited usage', 'cmdeals'), 'description' => __('(optional) How many times this coupon can be used before it is void', 'cmdeals') ) );
				
			// Expiry date
			cmdeals_wp_text_input( array( 'id' => 'expiry_date', 'label' => __('Expiry date', 'cmdeals'), 'placeholder' => __('Never expire', 'cmdeals'), 'description' => __('(optional) The date this coupon will expire, <code>YYYY-MM-DD</code>', 'cmdeals'), 'class' => 'short date-picker' ) );
			
			do_action('cmdeals_coupon_options');
			
		?>
	</div>
	<?php	
}

/**
 * Coupon Data Save
 * 
 * Function for processing and storing all coupon data.
 */
add_action('cmdeals_process_store_coupon_meta', 'cmdeals_process_store_coupon_meta', 1, 2);

function cmdeals_process_store_coupon_meta( $post_id, $post ) {
	global $wpdb;
	
	$cmdeals_errors = array();
	
	// Add/Replace data to array
		$type 			= strip_tags(stripslashes( $_POST['discount_type'] ));
		$amount 		= strip_tags(stripslashes( $_POST['coupon_amount'] ));
		$deal_ids 	= strip_tags(stripslashes( $_POST['deal_ids'] ));
		$exclude_deals_ids = strip_tags(stripslashes( $_POST['exclude_deals_ids'] ));
		$usage_limit 	= (isset($_POST['usage_limit']) && $_POST['usage_limit']>0) ? (int) $_POST['usage_limit'] : '';
		$individual_use = isset($_POST['individual_use']) ? 'yes' : 'no';
		$expiry_date 	= strip_tags(stripslashes( $_POST['expiry_date'] ));
		$apply_before_tax = isset($_POST['apply_before_tax']) ? 'yes' : 'no';
		$free_shipping = isset($_POST['free_shipping']) ? 'yes' : 'no';
	
	// Save
		update_post_meta( $post_id, 'discount_type', $type );
		update_post_meta( $post_id, 'coupon_amount', $amount );
		update_post_meta( $post_id, 'individual_use', $individual_use );
		update_post_meta( $post_id, 'deal_ids', $deal_ids );
		update_post_meta( $post_id, 'exclude_deals_ids', $exclude_deals_ids );
		update_post_meta( $post_id, 'usage_limit', $usage_limit );
		update_post_meta( $post_id, 'expiry_date', $expiry_date );
		update_post_meta( $post_id, 'apply_before_tax', $apply_before_tax );
		update_post_meta( $post_id, 'free_shipping', $free_shipping );
		
		do_action('cmdeals_coupon_options');
	
	// Error Handling
		if (sizeof($cmdeals_errors)>0) update_option('cmdeals_errors', $cmdeals_errors);
}