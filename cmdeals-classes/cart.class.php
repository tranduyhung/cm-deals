<?php
/**
 * CMDeals cart
 * 
 * The CMDeals cart class stores cart data and active coupons as well as handling customer sessions and some cart related urls.
 * The cart class also has a price calculation function which calls upon other classes to calcualte totals.
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

class cmdeals_cart {
	
	/* Public Variables */
	var $cart_contents;
	var $applied_coupons;
	
	var $cart_contents_total;
	var $cart_contents_weight;
	var $cart_contents_count;
	var $cart_contents_tax;
	
	var $total;
	var $subtotal;
	var $subtotal_ex_tax;
	var $tax_total;
	var $discount_cart;
	var $discount_total;
	var $shipping_total;
	var $shipping_tax_total;
	
	/* Private variables */
	var $tax;
	
	/**
	 * Constructor
	 */
	function __construct() {
		add_action('init', array(&$this, 'init'), 1);				// Get cart on init
		add_action('wp', array(&$this, 'calculate_totals'), 1);		// Defer calculate totals so we can detect page
	}
    
    /**
	 * Loads the cart data from the session during WordPress init
	 */
    function init() {
  		$this->applied_coupons = array();
		$this->get_cart_from_session();
		if ( isset($_SESSION['coupons']) ) $this->applied_coupons = $_SESSION['coupons'];
		add_action('cmdeals_check_cart_items', array(&$this, 'check_cart_items'), 1);
    }

 	/*-----------------------------------------------------------------------------------*/
	/* Cart Session Handling */
	/*-----------------------------------------------------------------------------------*/ 
	
		/**
		 * Get the cart data from the PHP session
		 */
		function get_cart_from_session() {
	
			if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty ($_SESSION['cart'])) :
				$cart = $_SESSION['cart'];
				
				foreach ($cart as $key => $values) :
				
					if ($values['variation_id'] > 0) :
						$_deals = new cmdeals_deals_variation($values['variation_id']);
					else :
						$_deals = new cmdeals_deals($values['deal_id']);
					endif;
					
					if ($_deals->exists && $values['quantity']>0) :
						
						// Put session data into array. Run through filter so other plugins can load their own session data
						$this->cart_contents[$key] = apply_filters('cmdeals_get_cart_item_from_session', array(
							'deal_id'	=> $values['deal_id'],
							'variation_id'	=> $values['variation_id'],
							'variation' 	=> $values['variation'],
							'quantity' 	=> $values['quantity'],
							'data'  	=> $_deals
						), $values);
	
					endif;
				endforeach;
				
			else :
				$this->cart_contents = array();
			endif;
			
			if (!is_array($this->cart_contents)) $this->cart_contents = array();
		}
		
		/**
		 * Sets the php session data for the cart and coupons
		 */
		function set_session() {
			// Set cart and coupon session data
			$_SESSION['cart'] = $this->cart_contents;
			$_SESSION['coupons'] = $this->applied_coupons;
			
			// Cart contents change so reset shipping
			unset($_SESSION['_chosen_shipping_method']);
			
			// Calculate totals
			$this->calculate_totals();
		}
		
		/**
		 * Empty the cart data and destroy the session
		 */
		function empty_cart() {
			$this->cart_contents = array();
			$this->reset_totals();
			unset($_SESSION['cart']);
			unset($_SESSION['coupons']);
		}

 	/*-----------------------------------------------------------------------------------*/
	/* Cart Data Functions */
	/*-----------------------------------------------------------------------------------*/ 

		/**
		 * Check cart items for errors
		 */
		function check_cart_items() {
			global $cmdeals;
			
			$result = $this->check_cart_item_stock();
			if (is_wp_error($result)) $cmdeals->add_error( $result->get_error_message() );
		}
		
		/** 
		 * looks through the cart to check each item is in stock 
		 */
		function check_cart_item_stock() {
			$error = new WP_Error();
			foreach ($this->cart_contents as $cart_item_key => $values) :
				$_deals = $values['data'];
				if ($_deals->managing_stock()) :
					if ($_deals->is_in_stock() && $_deals->has_enough_stock( $values['quantity'] )) :
						// :)
					else :
						$error->add( 'out-of-stock', sprintf(__('Sorry, we do not have enough "%s" in stock to fulfill your order (%s in stock). Please edit your cart and try again. We apologise for any inconvenience caused.', 'cmdeals'), $_deals->get_title(), $_deals->_stock ) );
						return $error;
					endif;
				endif;
			endforeach;
			return true;
		}
		
		/**
		 * Gets and formats a list of cart item data + variations for display on the frontend
		 */
		function get_item_data( $cart_item, $flat = false ) {
			global $cmdeals;
			
			if (!$flat) $return = '<dl class="variation">';
			
			// Variation data
			if($cart_item['data'] instanceof cmdeals_deals_variation && is_array($cart_item['variation'])) :
			
				$variation_list = array();
				
				foreach ($cart_item['variation'] as $name => $value) :
					
					if (!$value) continue;
					
					// If this is a term slug, get the term's nice name
		            if (taxonomy_exists(esc_attr(str_replace('attribute_', '', $name)))) :
		            	$term = get_term_by('slug', $value, esc_attr(str_replace('attribute_', '', $name)));
		            	if (!is_wp_error($term) && $term->name) :
		            		$value = $term->name;
		            	endif;
		            else :
		            	$value = ucfirst($value);
		            endif;
					
					if ($flat) :
						$variation_list[] = $cmdeals->attribute_label(str_replace('attribute_', '', $name)).': '.$value;
					else :
						$variation_list[] = '<dt>'.$cmdeals->attribute_label(str_replace('attribute_', '', $name)).':</dt><dd>'.$value.'</dd>';
					endif;
					
				endforeach;
				
				if ($flat) :
					$return .= implode(', ', $variation_list);
				else :
					$return .= implode('', $variation_list);
				endif;
			
			endif;
			
			// Other data - returned as array with name/value values
			$other_data = apply_filters('cmdeals_get_item_data', array(), $cart_item);
			
			if ($other_data && is_array($other_data) && sizeof($other_data)>0) :
				
				$data_list = array();
				
				foreach ($other_data as $data) :
					
					if ($flat) :
						$data_list[] = $data['name'].': '.$data['value'];
					else :
						$data_list[] = '<dt>'.$data['name'].':</dt><dd>'.$data['value'].'</dd>';
					endif;
					
				endforeach;
				
				if ($flat) :
					$return .= implode(', ', $data_list);
				else :
					$return .= implode('', $data_list);
				endif;
				
			endif;
			
			if (!$flat) $return .= '</dl>';
			
			return $return;
	   				
		}
	
		/**
		 * Gets cross sells based on the items in the cart
		 *
		 * @return   array	cross_sells	item ids of cross sells
		 */
		function get_cross_sells() {
			$cross_sells = array();
			$in_cart = array();
			if (sizeof($this->cart_contents)>0) : foreach ($this->cart_contents as $cart_item_key => $values) :
				if ($values['quantity']>0) :
					$cross_sells = array_merge($values['data']->get_cross_sells(), $cross_sells);
					$in_cart[] = $values['deal_id'];
				endif;
			endforeach; endif;
			$cross_sells = array_diff($cross_sells, $in_cart);
			return $cross_sells;
		}
		
		/** gets the url to the cart page */
		function get_cart_url() {
			$cart_page_id = get_option('cmdeals_checkout_page_id');
			if ($cart_page_id) return get_permalink($cart_page_id);
		}
		
		/** gets the url to the checkout page */
		function get_checkout_url() {
			$checkout_page_id = get_option('cmdeals_checkout_page_id');
			if ($checkout_page_id) :
				if (is_ssl()) return str_replace('http:', 'https:', get_permalink($checkout_page_id));
				return get_permalink($checkout_page_id);
			endif;
		}
		
		/** gets the url to remove an item from the cart */
		function get_remove_url( $cart_item_key ) {
			global $cmdeals;
			$cart_page_id = get_option('cmdeals_checkout_page_id');
			if ($cart_page_id) return $cmdeals->nonce_url( 'cart', add_query_arg('remove_item', $cart_item_key, get_permalink($cart_page_id)));
		}
		
		/**
		 * Returns the contents of the cart
		 */
		function get_cart() {
			return $this->cart_contents;
		}	
	
 	/*-----------------------------------------------------------------------------------*/
	/* Buy now handling */
	/*-----------------------------------------------------------------------------------*/ 
	
		/**
	     * Check if deals is in the cart and return cart item key
	     * 
	     * Cart item key will be unique based on the item and its properties, such as variations
	     */
	    function find_deals_in_cart( $cart_id = false ) {
	        if ($cart_id !== false) foreach ($this->cart_contents as $cart_item_key => $cart_item) if ($cart_item_key == $cart_id) return $cart_item_key;
	    }
		
		/**
	     * Generate a unique ID for the cart item being added
	     */
	    function generate_cart_id( $deal_id, $variation_id = '', $variation = '', $cart_item_data = '' ) {
	        
	        $id_parts = array( $deal_id );
	        
	        if ($variation_id) $id_parts[] = $variation_id;
	 
	        if (is_array($variation)) :
	            $variation_key = '';
	            foreach ($variation as $key => $value) :
	                $variation_key .= trim($key) . trim($value);
	            endforeach;
	            $id_parts[] = $variation_key;
	        endif;
	        
	        if (is_array($cart_item_data)) :
	            $cart_item_data_key = '';
	            foreach ($cart_item_data as $key => $value) :
	            	if (is_array($value)) $value = http_build_query($value);
	                $cart_item_data_key .= trim($key) . trim($value);
	            endforeach;
	            $id_parts[] = $cart_item_data_key;
	        endif;
	
	        return md5( implode('_', $id_parts) );
	    }	
		
		/**
		 * Add a deals to the cart
		 *
		 * @param   string	deal_id	contains the id of the deals to add to the cart
		 * @param   string	quantity	contains the quantity of the item to add
		 * @param   int     variation_id
		 * @param   array   variation attribute values
		 */
		function add_to_cart( $deal_id, $quantity = 1, $variation_id = '', $variation = '' ) {
			global $cmdeals;
			
			if ($quantity < 1) return false;
			
			// Load cart item data - may be added by other plugins
			$cart_item_data = (array) apply_filters('cmdeals_add_cart_item_data', array(), $deal_id);
			
			// Generate a ID based on deals ID, variation ID, variation data, and other cart item data
			$cart_id = $this->generate_cart_id( $deal_id, $variation_id, $variation, $cart_item_data );
			
			// See if this deals and its options is already in the cart
			$cart_item_key = $this->find_deals_in_cart($cart_id);
			
			if ($variation_id>0) :
				$deal_data = new cmdeals_deals_variation( $variation_id );
			else :
				$deal_data = new cmdeals_deals( $deal_id );
			endif;
			
			// Type/Exists check
			if ( $deal_data->is_type('external') || !$deal_data->exists() ) :
				$cmdeals->add_error( __('This deals cannot be purchased.', 'cmdeals') );
				return false; 
			endif;
			
			// Price set check
			if( $deal_data->get_price() === '' ) :
				$cmdeals->add_error( __('This deals cannot be purchased - the price is not yet set.', 'cmdeals') );
				return false; 
			endif;
	
			// Stock check - only check if we're managing stock and backsales are not allowed
			if ( !$deal_data->has_enough_stock( $quantity ) ) :
				$cmdeals->add_error( sprintf(__('You cannot add that amount to the cart since there is not enough stock. We have %s in stock.', 'cmdeals'), $deal_data->get_stock_quantity() ));
				return false; 
			elseif ( !$deal_data->is_in_stock() ) :
				$cmdeals->add_error( __('You cannot add that deals to the cart since the deals is out of stock.', 'cmdeals') );
				return false;
			endif;
			
			if ($cart_item_key) :
	
				$quantity = $quantity + $this->cart_contents[$cart_item_key]['quantity'];
				
				// Stock check - this time accounting for whats already in-cart
				if ( !$deal_data->has_enough_stock( $quantity ) ) :
					$cmdeals->add_error( sprintf(__('You cannot add that amount to the cart since there is not enough stock. We have %s in stock and you already have %s in your cart.', 'cmdeals'), $deal_data->get_stock_quantity(), $this->cart_contents[$cart_item_key]['quantity'] ));
					return false; 
				elseif ( !$deal_data->is_in_stock() ) :
					$cmdeals->add_error( __('You cannot add that deals to the cart since the deals is out of stock.', 'cmdeals') );
					return false;
				endif;
	
				$this->cart_contents[$cart_item_key]['quantity'] = $quantity;
	
			else :
				
				// Add item after merging with $cart_item_data - hook to allow plugins to modify cart item
				$this->cart_contents[$cart_id] = apply_filters('cmdeals_add_cart_item', array_merge( $cart_item_data, array(
					'deal_id'	=> $deal_id,
					'variation_id'	=> $variation_id,
					'variation' 	=> $variation,
					'quantity' 		=> $quantity,
					'data'			=> $deal_data
				)));
			
			endif;
	
			$this->set_session();
			
			return true;
		}

		/**
		 * Set the quantity for an item in the cart
		 *
		 * @param   string	cart_item_key	contains the id of the cart item
		 * @param   string	quantity	contains the quantity of the item
		 */
		function set_quantity( $cart_item_key, $quantity = 1 ) {
			if ($quantity==0 || $quantity<0) :
				unset($this->cart_contents[$cart_item_key]);
			else :
				$this->cart_contents[$cart_item_key]['quantity'] = $quantity;
			endif;
	
			$this->set_session();
		}

    /*-----------------------------------------------------------------------------------*/
	/* Cart Calculation Functions */
	/*-----------------------------------------------------------------------------------*/ 

		/** 
		 * Reset totals
		 */
		private function reset_totals() {
			$this->total = 0;
			$this->cart_contents_total = 0;
			$this->cart_contents_weight = 0;
			$this->cart_contents_count = 0;
			$this->cart_contents_tax = 0;
			$this->tax_total = 0;
			$this->shipping_tax_total = 0;
			$this->subtotal = 0;
			$this->subtotal_ex_tax = 0;
			$this->discount_total = 0;
			$this->discount_cart = 0;
			$this->shipping_total = 0;
		}
		
		/** 
		 * Function to apply discounts to a deals and get the discounted price (before tax is applied)
		 */
		function get_discounted_price( $values, $price, $add_totals = false ) {
	
			if ($this->applied_coupons) foreach ($this->applied_coupons as $code) :
				$coupon = new cmdeals_coupon( $code );
				
				if ( $coupon->apply_before_tax() && $coupon->is_valid() ) :
					
					switch ($coupon->type) :
					
						case "fixed_deals" :
						case "percent_deals" :
								
							$this_item_is_discounted = false;
				
							// Specific deals ID's get the discount
							if (sizeof($coupon->deal_ids)>0) :
								
								if ((in_array($values['deal_id'], $coupon->deal_ids) || in_array($values['variation_id'], $coupon->deal_ids))) :
									$this_item_is_discounted = true;
								endif;
							
							else :
								
								// No deals ids - all items discounted
								$this_item_is_discounted = true;
							
							endif;
				
							// Specific deals ID's excluded from the discount
							if (sizeof($coupon->exclude_deals_ids)>0) :
								
								if ((in_array($values['deal_id'], $coupon->exclude_deals_ids) || in_array($values['variation_id'], $coupon->exclude_deals_ids))) :
									$this_item_is_discounted = false;
								endif;
								
							endif;
							
							// Apply filter
							$this_item_is_discounted = apply_filters( 'cmdeals_item_is_discounted', $this_item_is_discounted, $values, $before_tax = true );
							
							// Apply the discount
							if ($this_item_is_discounted) :
								if ($coupon->type=='fixed_deals') :
									
									if ($price < $coupon->amount) :
										$discount_amount = $price;
									else :
										$discount_amount = $coupon->amount;
									endif;
									
									$price = $price - $coupon->amount;
									
									if ($price<0) $price = 0;
									
									if ($add_totals) :
										$this->discount_cart = $this->discount_cart + ( $discount_amount * $values['quantity'] );
									endif;
									
								elseif ($coupon->type=='percent_deals') :
								
									$percent_discount = ( $values['data']->get_price( false ) / 100 ) * $coupon->amount;
									
									if ($add_totals) $this->discount_cart = $this->discount_cart + ( $percent_discount * $values['quantity'] );
									
									$price = $price - $percent_discount;
									
								endif;
							endif;
	
						break;
						
						case "fixed_cart" :
							
							/** 
							 * This is the most complex discount - we need to divide the discount between rows based on their price in
							 * proportion to the subtotal. This is so rows with different tax rates get a fair discount, and so rows
							 * with no price (free) don't get discount too.
							 */
							
							// Get item discount by dividing item cost by subtotal to get a %
							$discount_percent = ($values['data']->get_price( false )*$values['quantity']) / $this->subtotal_ex_tax;
							
							// Use pence to help prevent rounding errors
							$coupon_amount_pence = $coupon->amount * 100;
							
							// Work out the discount for the row
							$item_discount = $coupon_amount_pence * $discount_percent;
							
							// Work out discount per item
							$item_discount = $item_discount / $values['quantity'];
							
							// Pence
							$price = ( $price * 100 );
							
							// Check if discount is more than price
							if ($price < $item_discount) :
								$discount_amount = $price;
							else :
								$discount_amount = $item_discount;
							endif;
							
							// Take discount off of price (in pence)
							$price = $price - $discount_amount;
							
							// Back to pounds
							$price = $price / 100; 
							
							// Cannot be below 0
							if ($price<0) $price = 0;
							
							// Add coupon to discount total (once, since this is a fixed cart discount and we don't want rounding issues)
							if ($add_totals) $this->discount_cart = $this->discount_cart + (($discount_amount*$values['quantity']) / 100);
	
						break;
						
						case "percent" :
						
							// Get % off each item - this works out the same as doing the whole cart
							//$percent_discount = ( $values['data']->get_price( false ) / 100 ) * $coupon->amount;
							
							$percent_discount = ( $values['data']->get_price(  ) / 100 ) * $coupon->amount;
									
							if ($add_totals) $this->discount_cart = $this->discount_cart + ( $percent_discount * $values['quantity'] );
							
							$price = $price - $percent_discount;
							
						break;
						
					endswitch;
					
				endif;
			endforeach;
			
			return apply_filters( 'cmdeals_get_discounted_price_', $price, $values, $this );
		}
		
		/** 
		 * Function to apply deals discounts after tax
		 */
		function apply_deals_discounts_after_tax( $values, $price ) {
			
			if ($this->applied_coupons) foreach ($this->applied_coupons as $code) :
				$coupon = new cmdeals_coupon( $code );
				
				do_action( 'cmdeals_deals_discount_after_tax_' . $coupon->type, $coupon );
				
				if ($coupon->type!='fixed_deals' && $coupon->type!='percent_deals') continue;
				
				if ( !$coupon->apply_before_tax() && $coupon->is_valid() ) :
					
					$this_item_is_discounted = false;
		
					// Specific deals ID's get the discount
					if (sizeof($coupon->deal_ids)>0) :
						
						if ((in_array($values['deal_id'], $coupon->deal_ids) || in_array($values['variation_id'], $coupon->deal_ids))) :
							$this_item_is_discounted = true;
						endif;
					
					else :
						
						// No deals ids - all items discounted
						$this_item_is_discounted = true;
					
					endif;
		
					// Specific deals ID's excluded from the discount
					if (sizeof($coupon->exclude_deals_ids)>0) :
						
						if ((in_array($values['deal_id'], $coupon->exclude_deals_ids) || in_array($values['variation_id'], $coupon->exclude_deals_ids))) :
							$this_item_is_discounted = false;
						endif;
						
					endif;
					
					// Apply filter
					$this_item_is_discounted = apply_filters( 'cmdeals_item_is_discounted', $this_item_is_discounted, $values, $before_tax = false );
					
					// Apply the discount
					if ($this_item_is_discounted) :
						if ($coupon->type=='fixed_deals') :
						
							if ($price < $coupon->amount) :
								$discount_amount = $price;
							else :
								$discount_amount = $coupon->amount;
							endif;
									
							$this->discount_total = $this->discount_total + ( $discount_amount * $values['quantity'] );
							
						elseif ($coupon->type=='percent_deals') :
							$this->discount_total = $this->discount_total + ( $price / 100 ) * $coupon->amount;
						endif;
					endif;
					
				endif;
			endforeach;
		}
		
		/** 
		 * Function to apply cart discounts after tax
		 */
		function apply_cart_discounts_after_tax() {	
			
			if ($this->applied_coupons) foreach ($this->applied_coupons as $code) :
				$coupon = new cmdeals_coupon( $code );
				
				do_action( 'cmdeals_cart_discount_after_tax_' . $coupon->type, $coupon );
				
				if ( !$coupon->apply_before_tax() && $coupon->is_valid() ) :
					
					switch ($coupon->type) :
					
						case "fixed_cart" :
	
							$this->discount_total = $this->discount_total + $coupon->amount;
							
						break;
						
						case "percent" :
							
							$percent_discount = (round( $this->cart_contents_total + $this->tax_total , 2) / 100 ) * $coupon->amount;
							
							$this->discount_total = $this->discount_total + $percent_discount;
							
						break;
						
					endswitch;
	
				endif;
			endforeach;
		}
		
		/** 
		 * calculate totals for the items in the cart 
		 */
		function calculate_totals() {
			global $cmdeals;
			
			$this->reset_totals();
			
			// Get count of all items + weights + subtotal (we may need this for discounts)
			if (sizeof($this->cart_contents)>0) foreach ($this->cart_contents as $cart_item_key => $values) :
				
				$_deals = $values['data'];
				
				$this->cart_contents_count 	= $this->cart_contents_count + $values['quantity'];
				
				// Base Price (inlusive of tax for now)
				$base_price 			= $_deals->get_sale();
					
                                // Sub total is based on base prices (without discounts)
                                $this->subtotal 			= $this->subtotal + ( $base_price * $values['quantity'] );
								
			endforeach;
			
			
				if (sizeof($this->cart_contents)>0) : foreach ($this->cart_contents as $cart_item_key => $values) :
				
					/** 
					 * Prices exclude tax
					 *
					 * This calculation is simpler - work with the base, untaxed price.
					 */
					$_deals = $values['data'];
	
					// Base Price (i.e. no tax, regardless of region)
					$base_price 				= $_deals->get_sale();
		
					// Discounted Price (base price with any pre-tax discounts applied
					$discounted_price 			= $this->get_discounted_price( $values, $base_price, true );		
                                        													
					// Total item price (price, discount and quantity)
					$total_item_price 			= $discounted_price * $values['quantity'];		
											
					// Cart contents total is based on discounted prices and is used for the final total calculation
					$this->cart_contents_total              = $this->cart_contents_total 		+ $total_item_price;
				
				endforeach; endif;
			
			// Only go beyond this point if on the cart/checkout
			if (!is_checkout() && !defined('CMDEALS_CHECKOUT') && !is_ajax()) return;
			
			// VAT exemption done at this point - so all totals are correct before exemption
			if ($cmdeals->customer->is_vat_exempt()) :
				$this->shipping_tax_total = $this->tax_total = 0;
			endif;
			
			// Allow plugins to hook and alter totals before final total is calculated
			do_action('cmdeals_calculate_totals', $this);
					
			/** 
			 * Grand Total
			 *
			 * Based on discounted deals prices, discounted tax, shipping cost + tax, and any discounts to be added after tax (e.g. store credit)
			 */
			$this->total = $this->cart_contents_total + $this->discount_total;
			
			if ($this->total < 0) $this->total = 0;
		}

		/** 
		 * looks at the totals to see if payment is actually required 
		 */
		function needs_payment() {
			if ( $this->total > 0 ) return true; else return false;
		}
	
    /*-----------------------------------------------------------------------------------*/
	/* Coupons/Discount related functions */
	/*-----------------------------------------------------------------------------------*/ 

		/** 
		 *returns whether or not a discount has been applied 
		 */
		function has_discount( $code ) {
			if (in_array($code, $this->applied_coupons)) return true;
			return false;
		}

		/**
		 * Applies a coupon code
		 *
		 * @param   string	code	The code to apply
		 * @return   bool	True if the coupon is applied, false if it does not exist or cannot be applied
		 */
		function add_discount( $coupon_code ) {
			global $cmdeals;
			
			$the_coupon = new cmdeals_coupon($coupon_code);
			
			if ($the_coupon->id) :
				
				// Check if applied
				if ($cmdeals->cart->has_discount($coupon_code)) :
					$cmdeals->add_error( __('Discount code already applied!', 'cmdeals') );
					return false;
				endif;	
				
				// Check it can be used with cart
				if (!$the_coupon->is_valid()) :
					$cmdeals->add_error( __('Invalid coupon.', 'cmdeals') );
					return false;
				endif;
				
				// If its individual use then remove other coupons
				if ($the_coupon->individual_use=='yes') :
					$this->applied_coupons = array();
				endif;
				
				foreach ($this->applied_coupons as $code) :
					$coupon = new cmdeals_coupon($code);
					if ($coupon->individual_use=='yes') :
						$this->applied_coupons = array();
					endif;
				endforeach;
				
				$this->applied_coupons[] = $coupon_code;
				$this->set_session();
				$cmdeals->add_message( __('Discount code applied successfully.', 'cmdeals') );
				return true;
			
			else :
				$cmdeals->add_error( __('Coupon does not exist!', 'cmdeals') );
				return false;
			endif;
			return false;
		}
	
		/**
		 * gets the array of applied coupon codes
		 */
		function get_applied_coupons() {
			return (array) $this->applied_coupons;
		}
		
		/**
		 * gets the array of applied coupon codes
		 */
		function remove_coupons( $type = 0 ) {
		
			if ($type == 1) :
				if ($this->applied_coupons) foreach ($this->applied_coupons as $index => $code) :
					$coupon = new cmdeals_coupon( $code );
					if ( $coupon->apply_before_tax() ) unset($this->applied_coupons[$index]);
				endforeach;
				$_SESSION['coupons'] = $this->applied_coupons;
			elseif ($type == 2) :
				if ($this->applied_coupons) foreach ($this->applied_coupons as $index => $code) :
					$coupon = new cmdeals_coupon( $code );
					if ( !$coupon->apply_before_tax() ) unset($this->applied_coupons[$index]);
				endforeach;
				$_SESSION['coupons'] = $this->applied_coupons;
			else :
				unset($_SESSION['coupons']);
				$this->applied_coupons = array();
			endif;
		}

    /*-----------------------------------------------------------------------------------*/
	/* Get Formatted Totals */
	/*-----------------------------------------------------------------------------------*/ 
	
		/** 
		 * Get the total of all order discounts (after tax discounts)
		 */
		function get_order_discount_total() {
			return $this->discount_total;
		}
		
		/** 
		 * Get the total of all cart discounts (before tax discounts)
		 */
		function get_cart_discount_total() {
			return $this->discount_cart;
		}
		
		/**
		 * gets the total (after calculation)
		 */
		function get_total() {
			return cmdeals_price($this->total);
		}
		
		/**
		 * gets the cart contents total (after calculation)
		 */
		function get_cart_total() {
			if (!$this->prices_include_tax) :
				return cmdeals_price($this->cart_contents_total);
			else :
				return cmdeals_price($this->cart_contents_total + $this->tax_total);
			endif;
		}
		
		/**
		 * gets the sub total (after calculation)
		 */
		function get_cart_subtotal() {
			global $cmdeals;
			
			// Display ex tax if the option is set, or prices exclude tax
			if ($this->display_totals_ex_tax || !$this->prices_include_tax) :
				
				$return = cmdeals_price( $this->subtotal_ex_tax );
				
				if ($this->tax_total>0 && $this->prices_include_tax) :
					$return .= ' <small>'.$cmdeals->countries->ex_tax_or_vat().'</small>';
				endif;
				return $return;
				
			else :
				
				$return = cmdeals_price( $this->subtotal );
				
				if ($this->tax_total>0 && !$this->prices_include_tax) :
					$return .= ' <small>'.$cmdeals->countries->inc_tax_or_vat().'</small>';
				endif;
				return $return;
			
			endif;
	
		}
		
		/** 
		 * Get the deals row subtotal
		 *
		 * Gets the tax etc to avoid rounding issues.
		 *
		 * When on the checkout (review order), this will get the subtotal based on the customer's tax rate rather than the base rate
		 */
		function get_deals_subtotal( $_deals, $quantity ) {
			global $cmdeals;
			
			$price 			= $_deals->get_sale();
							
                        $row_price 		= $price * $quantity;
                        $return                 = cmdeals_price( $row_price );
			
			return $return; 
			
		}
		
		/**
		 * gets the cart tax (after calculation)
		 */
		function get_cart_tax() {
			$cart_total_tax = $this->tax_total + $this->shipping_tax_total;
			if ($cart_total_tax > 0) return cmdeals_price( $cart_total_tax );
			return false;
		}	
		
		/**
		 * gets the total (daily-deals) discount amount - these are applied before tax
		 */
		function get_discounts_before_tax() {
			if ($this->discount_cart) :
				return cmdeals_price($this->discount_cart); 
			endif;
			return false;
		}
		
		/**
		 * gets the order discount amount - these are applied after tax
		 */
		function get_discounts_after_tax() {
			if ($this->discount_total) :
				return cmdeals_price($this->discount_total); 
			endif;
			return false;
		}
		
		
		/**
		 * gets the total discount amount - both kinds
		 */
		function get_total_discount() {
			if ($this->discount_total || $this->discount_cart) :
				return cmdeals_price($this->discount_total + $this->discount_cart); 
			endif;
			return false;
		}	
	
}