<?php
/**
 * CMDeals coupons
 * 
 * The CMDeals coupons class gets coupon data from storage
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

class cmdeals_coupon {
	
	var $code;
	var $id;
	var $type;
	var $amount;
	var $individual_use;
	var $deal_ids;
	var $usage_limit;
	var $usage_count;
	var $expiry_date;
	var $apply_before_tax;
	var $free_shipping;
	
	/** get coupon with $code */
	function cmdeals_coupon( $code ) {
		
		$this->code = $code;
		
		$coupon = get_page_by_title( $this->code, 'OBJECT', 'store_coupon' );
		
		if ($coupon && $coupon->post_status == 'publish') :
			
			$this->id					= $coupon->ID;
			$this->type 				= get_post_meta($coupon->ID, 'discount_type', true);
			$this->amount 				= get_post_meta($coupon->ID, 'coupon_amount', true);
			$this->individual_use 		= get_post_meta($coupon->ID, 'individual_use', true);
			$this->deal_ids 			= array_filter(array_map('trim', explode(',', get_post_meta($coupon->ID, 'deal_ids', true))));
			$this->exclude_deals_ids	= array_filter(array_map('trim', explode(',', get_post_meta($coupon->ID, 'exclude_deals_ids', true))));
			$this->usage_limit 			= get_post_meta($coupon->ID, 'usage_limit', true);
			$this->usage_count 			= (int) get_post_meta($coupon->ID, 'usage_count', true);
			$this->expiry_date 			= ($expires = get_post_meta($coupon->ID, 'expiry_date', true)) ? strtotime($expires) : '';
			$this->apply_before_tax 	= get_post_meta($coupon->ID, 'apply_before_tax', true);
			$this->free_shipping 		= get_post_meta($coupon->ID, 'free_shipping', true);
			
			return true;
			
		endif;
		
		return false;
	}
	
	/** Check if coupon needs applying before tax **/
	function apply_before_tax() {
		if ($this->apply_before_tax=='yes') return true; else return false;
	}
	
	function enable_free_shipping() {
		if ($this->free_shipping=='yes') return true; else return false;
	}
	
	/** Increase usage count */
	function inc_usage_count() {
		$this->usage_count++;
		update_post_meta($this->id, 'usage_count', $this->usage_count);
	}
	
	/** Check coupon is valid */
	function is_valid() {
		
		global $cmdeals;
				
		if ($this->id) :
			
			if ($this->usage_limit>0) :
				if ($this->usage_count>=$this->usage_limit) :
					return false;
				endif;
			endif;
			
			if ($this->expiry_date) :
				if (strtotime('NOW')>$this->expiry_date) :
					return false;
				endif;
			endif;
			
			// Deal ids - If a deals included is found in the cart then its valid
			if (sizeof( $this->deal_ids )>0) :
				$valid = false;
				if (sizeof($cmdeals->cart->get_cart())>0) : foreach ($cmdeals->cart->get_cart() as $cart_item_key => $cart_item) :
					if (in_array($cart_item['deal_id'], $this->deal_ids) || in_array($cart_item['variation_id'], $this->deal_ids)) :
						$valid = true;
					endif;
				endforeach; endif;
				if (!$valid) return false;
			endif;
			
			// Cart discounts cannot be added if non-eligble deals is found in cart
			if ($this->type!='fixed_deals' && $this->type!='percent_deals') : 

				if (sizeof( $this->exclude_deals_ids )>0) :
					$valid = true;
					if (sizeof($cmdeals->cart->get_cart())>0) : foreach ($cmdeals->cart->get_cart() as $cart_item_key => $cart_item) :
						if (in_array($cart_item['deal_id'], $this->exclude_deals_ids) || in_array($cart_item['variation_id'], $this->exclude_deals_ids)) :
							$valid = false;
						endif;
					endforeach; endif;
					if (!$valid) return false;
				endif;
			
			endif;
			
			$valid = apply_filters('cmdeals_coupon_is_valid', true, $this);
			if (!$valid) return false;
			
			return true;
		
		endif;
		
		return false;
	}
}
