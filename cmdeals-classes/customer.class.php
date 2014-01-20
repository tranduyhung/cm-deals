<?php
/**
 * Customer
 * 
 * The CMDeals customer class handles storage of the current customer's data, such as location.
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

class cmdeals_customer {
	
	/** constructor */
	function __construct() {
		
		if ( !isset($_SESSION['customer']) ) :
			
			$default = get_option('cmdeals_default_country');
        	if (strstr($default, ':')) :
        		$country = current(explode(':', $default));
        		$state = end(explode(':', $default));
        	else :
        		$country = $default;
        		$state = '';
        	endif;
			$data = array(
				'country' 			=> $country,
				'state' 			=> $state,
				'postcode' 			=> '',
				'shipping_country' 	=> $country,
				'shipping_state' 	=> $state,
				'shipping_postcode' => '',
				'is_vat_exempt' 	=> false
			);			
			$_SESSION['customer'] = $data;
			$_SESSION['calculated_shipping'] = false;
			
		endif;
		
	}
    
    /** Is customer outside base country? */
	function is_customer_outside_base() {
		if (isset($_SESSION['customer']['country'])) :
			
			$default = get_option('cmdeals_default_country');
        	if (strstr($default, ':')) :
        		$country = current(explode(':', $default));
        		$state = end(explode(':', $default));
        	else :
        		$country = $default;
        		$state = '';
        	endif;
        	
			if ($country!==$_SESSION['customer']['shipping_country']) return true;
			if ($state && $state!==$_SESSION['customer']['shipping_state']) return true;
			
		endif;
		return false;
	}
	
	/** Is customer VAT exempt? */
	function is_vat_exempt() {
		if (isset($_SESSION['customer']['is_vat_exempt']) && $_SESSION['customer']['is_vat_exempt']) return true;
		return false;
	}
	
	/** Gets the state from the current session */
	function get_state() {
		if (isset($_SESSION['customer']['state'])) return $_SESSION['customer']['state'];
	}
	
	/** Gets the country from the current session */
	function get_country() {
		if (isset($_SESSION['customer']['country'])) return $_SESSION['customer']['country'];
	}
	
	/** Gets the postcode from the current session */
	function get_postcode() {
		if (isset($_SESSION['customer']['postcode']) && $_SESSION['customer']['postcode'] !== false) return strtolower(str_replace(' ', '', $_SESSION['customer']['postcode']));
	}
	
	/** Gets the state from the current session */
	function get_shipping_state() {
		if (isset($_SESSION['customer']['shipping_state'])) return $_SESSION['customer']['shipping_state'];
	}
	
	/** Gets the country from the current session */
	function get_shipping_country() {
		if (isset($_SESSION['customer']['shipping_country'])) return $_SESSION['customer']['shipping_country'];
	}
	
	/** Gets the postcode from the current session */
	function get_shipping_postcode() {
		if (isset($_SESSION['customer']['shipping_postcode'])) return strtolower(str_replace(' ', '', $_SESSION['customer']['shipping_postcode']));
	}
	
	/** Sets session data for the location */
	function set_location( $country, $state, $postcode = '' ) {
		$data = (array) $_SESSION['customer'];
		
		$data['country'] = $country;
		$data['state'] = $state;
		$data['postcode'] = $postcode;
		
		$_SESSION['customer'] = $data;
	}
	
	/** Sets session data for the country */
	function set_country( $country ) {
		$_SESSION['customer']['country'] = $country;
	}
	
	/** Sets session data for the state */
	function set_state( $state ) {
		$_SESSION['customer']['state'] = $state;
	}
	
	/** Sets session data for the postcode */
	function set_postcode( $postcode ) {
		$_SESSION['customer']['postcode'] = $postcode;
	}
	
	/** Sets session data for the location */
	function set_shipping_location( $country, $state = '', $postcode = '' ) {
		$data = (array) $_SESSION['customer'];
		
		$data['shipping_country'] = $country;
		$data['shipping_state'] = $state;
		$data['shipping_postcode'] = $postcode;
		
		$_SESSION['customer'] = $data;
	}
	
	/** Sets session data for the country */
	function set_shipping_country( $country ) {
		$_SESSION['customer']['shipping_country'] = $country;
	}
	
	/** Sets session data for the state */
	function set_shipping_state( $state ) {
		$_SESSION['customer']['shipping_state'] = $state;
	}
	
	/** Sets session data for the postcode */
	function set_shipping_postcode( $postcode ) {
		$_SESSION['customer']['shipping_postcode'] = $postcode;
	}
	
	/** Sets session data for the tax exemption */
	function set_is_vat_exempt( $is_vat_exempt ) {
		$_SESSION['customer']['is_vat_exempt'] = $is_vat_exempt;
	}
	
	/**
	 * Gets a user's downloadable deals if they are logged in
	 *
	 * @return   array	downloads	Array of downloadable deals
	 */
	function get_downloadable_deals() {
		
		global $wpdb;
		
		$downloads = array();
		
		if (is_user_logged_in()) :
			
			$user_info = get_userdata(get_current_user_id());

			$results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."cmdeals_permissions WHERE user_id = '%s' AND vouchers = '';", get_current_user_id()) );
			
			if ($results) foreach ($results as $result) :
				
				if ($result->order_id>0) :
				
					$order = new cmdeals_order( $result->order_id );
					
					if ( $order->status!='completed' && $order->status!='processing' ) continue;
						
					$deal_post = get_post( $result->deal_id );
	
					if ($deal_post->post_type=='deal-variations') :
						$_deals = new cmdeals_deals_variation( $result->deal_id );
					else :
						$_deals = new cmdeals_deals( $result->deal_id );
					endif;					
					
					if ($_deals->exists) :
						$download_name = $_deals->get_title();
					else :
						$download_name = '#' . $result->deal_id;
					endif;
					
					$downloads[] = array(
						'download_url' => add_query_arg('download_file', $result->deal_id, add_query_arg('order', $result->order_key, add_query_arg('email', $user_info->user_email, home_url()))),
						'deal_id' => $result->deal_id,
						'download_name' => $download_name,
						'order_id' => $order->id,
						'order_key' => $order->order_key,
						'downloads_remaining' => $result->downloads_remaining
					);
				
				endif;
				
			endforeach;
		
		endif;
		
		return apply_filters('cmdeals_customer_get_downloadable_deals', $downloads);
		
	}
	
	/**
	 * Gets a user's coupon deals if they are logged in
	 *
	 * @return array coupons Array of couponable deals
	 */
	function get_coupons_deals() {
		
		global $wpdb;
		
		$coupons = array();
		
		if (is_user_logged_in()) :
			
			$user_info = get_userdata(get_current_user_id()); 

			$results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."cmdeals_permissions WHERE user_id = '%s' AND vouchers != '';", get_current_user_id()) );
			
			if ($results) foreach ($results as $result) :
				
				if ($result->order_id>0) :
                        
                                        $order = new cmdeals_order( $result->order_id );

                                        if ( $order->status!='completed' && $order->status!='processing' ) continue;
						
					$deal_post = get_post( $result->deal_id );
	
					if ($deal_post->post_type=='deal-variations') :
						$_deals = new cmdeals_deals_variation( $result->deal_id );
					else :
						$_deals = new cmdeals_deals( $result->deal_id );
					endif;		

                                        if ($_deals->exists) :
                                                $voucher_name = $_deals->get_title();
                                        else :
                                                $voucher_name = '#' . get_the_ID();
                                        endif;

                                        $coupons[]  = array(
                                            'deal_id'  => $result->deal_id,
                                            'voucher_name'  => $voucher_name,
                                            'voucher_value' => $result->vouchers
                                        );
				
				endif;
				
			endforeach;
		
		endif;
		
		return apply_filters('cmdeals_customer_get_coupon_deals', $coupons);
		
	}

}