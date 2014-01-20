<?php
/**
 * CMDeals Payment Gateways class
 * 
 * Loads payment gateways via hooks for use in the store.
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

class cmdeals_payment_gateways {
	
	var $payment_gateways;
   
    function init() {
    
    	$load_gateways = apply_filters('cmdeals_payment_gateways', array());
		
		// Get order option
		$ordering 	= (array) get_option('cmdeals_gateway_order');
		$order_end 	= 999;
		
		// Load gateways in order
		foreach ($load_gateways as $gateway) :
			
			$load_gateway = new $gateway();
			
			if (isset($ordering[$load_gateway->id]) && is_numeric($ordering[$load_gateway->id])) :
				// Add in position
				$this->payment_gateways[$ordering[$load_gateway->id]] = $load_gateway;
			else :
				// Add to end of the array
				$this->payment_gateways[$order_end] = $load_gateway;
				$order_end++;
			endif;
			
		endforeach;
		
		ksort($this->payment_gateways);
		
		add_action('cmdeals_update_options_payment_gateways', array(&$this, 'process_admin_options'));
    	
    }

    function payment_gateways() {
		
		$_available_gateways = array();
		
		if (sizeof($this->payment_gateways) > 0) :
			foreach ( $this->payment_gateways as $gateway ) :
				
				$_available_gateways[$gateway->id] = $gateway;
				
			endforeach;
		endif;

		return $_available_gateways;
	}
	
	function get_available_payment_gateways() {
		
		$_available_gateways = array();
	
		foreach ( $this->payment_gateways as $gateway ) :
			
			if ($gateway->is_available()) $_available_gateways[$gateway->id] = $gateway;
			
		endforeach;

		return $_available_gateways;
	}
	
	function process_admin_options() {
		
		$default_gateway = (isset($_POST['default_gateway'])) ? esc_attr($_POST['default_gateway']) : '';
		$gateway_order = (isset($_POST['gateway_order'])) ? $_POST['gateway_order'] : '';
		
		$order = array();
		
		if (is_array($gateway_order) && sizeof($gateway_order)>0) :
			$loop = 0;
			foreach ($gateway_order as $gateway_id) :
				$order[$gateway_id] = $loop;
				$loop++;
			endforeach;
		endif;
		
		update_option( 'cmdeals_default_gateway', $default_gateway );
		update_option( 'cmdeals_gateway_order', $order );
		
	}
		
}