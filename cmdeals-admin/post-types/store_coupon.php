<?php
/**
 * Admin functions for the store_coupon post type
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
 * Columns for Coupons page
 **/
add_filter('manage_edit-store_coupon_columns', 'cmdeals_edit_coupon_columns');

function cmdeals_edit_coupon_columns($columns){
	
	$columns = array();
	
	$columns["cb"] 			= "<input type=\"checkbox\" />";
	$columns["title"] 		= __("Code", 'cmdeals');
	$columns["type"] 		= __("Coupon type", 'cmdeals');
	$columns["amount"] 		= __("Coupon amount", 'cmdeals');
	$columns["daily-deals"]	= __("Deal IDs", 'cmdeals');
	$columns["usage_limit"] = __("Usage limit", 'cmdeals');
	$columns["usage_count"] = __("Usage count", 'cmdeals');
	$columns["expiry_date"] = __("Expiry date", 'cmdeals');

	return $columns;
}


/**
 * Custom Columns for Coupons page
 **/
add_action('manage_store_coupon_posts_custom_column', 'cmdeals_custom_coupon_columns', 2);

function cmdeals_custom_coupon_columns($column) {
	global $post, $cmdeals;
	
	$type 			= get_post_meta($post->ID, 'discount_type', true);
	$amount 		= get_post_meta($post->ID, 'coupon_amount', true);
	$individual_use = get_post_meta($post->ID, 'individual_use', true);
	$deal_ids 	= (get_post_meta($post->ID, 'deal_ids', true)) ? explode(',', get_post_meta($post->ID, 'deal_ids', true)) : array();
	$usage_limit 	= get_post_meta($post->ID, 'usage_limit', true);
	$usage_count 	= (int) get_post_meta($post->ID, 'usage_count', true);
	$expiry_date 	= get_post_meta($post->ID, 'expiry_date', true);

	switch ($column) {
		case "type" :
			echo $cmdeals->get_coupon_discount_type($type);			
		break;
		case "amount" :
			echo $amount;
		break;
		case "daily-deals" :
			if (sizeof($deal_ids)>0) echo implode(', ', $deal_ids); else echo '&ndash;';
		break;
		case "usage_limit" :
			if ($usage_limit) echo $usage_limit; else echo '&ndash;';
		break;
		case "usage_count" :
			echo $usage_count;
		break;
		case "expiry_date" :
			if ($expiry_date) echo date('F j, Y', strtotime($expiry_date)); else echo '&ndash;';
		break;
	}
}
