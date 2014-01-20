<?php
/**
 * Bank Transfer Payment Gateway
 * 
 * Provides a Bank Transfer Payment Gateway. Based on code by Mike Pepper.
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

class cmdeals_bacs extends cmdeals_payment_gateway {

    public function __construct() { 
		$this->id				= 'bacs';
		$this->icon 			= apply_filters('cmdeals_bacs_icon', '');
		$this->has_fields 		= false;
		
		// Load the form fields.
		$this->init_form_fields();
		
		// Load the settings.
		$this->init_settings();
		
		// Define user set variables
		$this->title 		= $this->settings['title'];
		$this->description      = $this->settings['description'];
		$this->account_name     = $this->settings['account_name'];
		$this->account_number   = $this->settings['account_number'];
		$this->sort_code        = $this->settings['sort_code'];
		$this->bank_name        = $this->settings['bank_name'];
		$this->iban             = $this->settings['iban'];
		$this->bic              = $this->settings['bic'];  
		
		// Actions
		add_action('cmdeals_update_options_payment_gateways', array(&$this, 'process_admin_options'));
                add_action('cmdeals_thankyou_bacs', array(&$this, 'thankyou_page'));

                // Customer Emails
                add_action('cmdeals_email_before_order_table', array(&$this, 'email_instructions'), 10, 2);
    } 

	/**
     * Initialise Gateway Settings Form Fields
     */
    function init_form_fields() {
    
    	$this->form_fields = array(
			'enabled' => array(
							'title' => __( 'Enable/Disable', 'cmdeals' ), 
							'type' => 'checkbox', 
							'label' => __( 'Enable Bank Transfer', 'cmdeals' ), 
							'default' => 'yes'
						), 
			'title' => array(
							'title' => __( 'Title', 'cmdeals' ), 
							'type' => 'text', 
							'description' => __( 'This controls the title which the user sees during checkout.', 'cmdeals' ), 
							'default' => __( 'Direct Bank Transfer', 'cmdeals' )
						),
			'description' => array(
							'title' => __( 'Customer Message', 'cmdeals' ), 
							'type' => 'textarea', 
							'description' => __( 'Give the customer instructions for paying via BACS, and let them know that their order won\'t be shipping until the money is received.', 'cmdeals' ), 
							'default' => __('Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order wont be shipped until the funds have cleared in our account.', 'cmdeals')
						),
			'account_name' => array(
							'title' => __( 'Account Name', 'cmdeals' ), 
							'type' => 'text', 
							'description' => '', 
							'default' => ''
						),
			'account_number' => array(
							'title' => __( 'Account Number', 'cmdeals' ), 
							'type' => 'text', 
							'description' => '', 
							'default' => ''
						),
			'sort_code' => array(
							'title' => __( 'Sort Code', 'cmdeals' ), 
							'type' => 'text', 
							'description' => '', 
							'default' => ''
						),
			'bank_name' => array(
							'title' => __( 'Bank Name', 'cmdeals' ), 
							'type' => 'text', 
							'description' => '', 
							'default' => ''
						),
			'iban' => array(
							'title' => __( 'IBAN', 'cmdeals' ), 
							'type' => 'text', 
							'description' => __('Your bank may require this for international payments','cmdeals'), 
							'default' => ''
						),
			'bic' => array(
							'title' => __( 'BIC (formerly Swift)', 'cmdeals' ), 
							'type' => 'text', 
							'description' => __('Your bank may require this for international payments','cmdeals'), 
							'default' => ''
						),

			);
    
    } // End init_form_fields()
    
	/**
	 * Admin Panel Options 
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {
    	?>
    	<h3><?php _e('BACS Payment', 'cmdeals'); ?></h3>
    	<p><?php _e('Allows payments by BACS (Bank Account Clearing System), more commonly known as direct bank/wire transfer.', 'cmdeals'); ?></p>
    	<table class="form-table">
    	<?php
    		// Generate the HTML For the settings form.
    		$this->generate_settings_html();
    	?>
		</table><!--/.form-table-->
    	<?php
    } // End admin_options()


    /**
    * There are no payment fields for bacs, but we want to show the description if set.
    **/
    function payment_fields() {
      if ($this->description) echo wpautop(wptexturize($this->description));
    }

    function thankyou_page() {
		if ($this->description) echo wpautop(wptexturize($this->description));
		
		?><h2><?php _e('Our Details', 'cmdeals') ?></h2><ul class="order_details bacs_details"><?php
		
		$fields = array(
			'account_name' 	=> __('Account Name', 'cmdeals'), 
			'account_number'=> __('Account Number', 'cmdeals'),  
			'sort_code'		=> __('Sort Code', 'cmdeals'),  
			'bank_name'		=> __('Bank Name', 'cmdeals'),  
			'iban'			=> __('IBAN', 'cmdeals'), 
			'bic'			=> __('BIC', 'cmdeals')
		);
		
		foreach ($fields as $key=>$value) :
		    if(!empty($this->$key)) :
		    	echo '<li class="'.$key.'">'.$value.': <strong>'.wptexturize($this->$key).'</strong></li>';
		    endif;
		endforeach;
		
		?></ul><?php
    }
    
    /**
    * Add text to user email
    **/
    function email_instructions( $order, $sent_to_admin ) {
    	
    	if ( $sent_to_admin ) return;
    	
    	if ( $order->status !== 'on-hold') return;
    	
    	if ( $order->payment_method !== 'bacs') return;
    	
		if ($this->description) echo wpautop(wptexturize($this->description));
		
		?><h2><?php _e('Our Details', 'cmdeals') ?></h2><ul class="order_details bacs_details"><?php
		
		$fields = array(
			'account_name'          => __('Account Name', 'cmdeals'), 
			'account_number'        => __('Account Number', 'cmdeals'),  
			'sort_code'		=> __('Sort Code', 'cmdeals'),  
			'bank_name'		=> __('Bank Name', 'cmdeals'),  
			'iban'			=> __('IBAN', 'cmdeals'), 
			'bic'			=> __('BIC', 'cmdeals')
		);
		
		foreach ($fields as $key=>$value) :
		    if(!empty($this->$key)) :
		    	echo '<li class="'.$key.'">'.$value.': <strong>'.wptexturize($this->$key).'</strong></li>';
		    endif;
		endforeach;
		
		?></ul><?php
    }

    /**
    * Process the payment and return the result
    **/
    function process_payment( $order_id ) {
    	global $cmdeals;
    	
		$order = new cmdeals_order( $order_id );
		
		// Mark as on-hold (we're awaiting the payment)
		$order->update_status('on-hold', __('Awaiting BACS payment', 'cmdeals'));
		
		// Reduce stock levels
		$order->reduce_order_stock();

		// Remove cart
		$cmdeals->cart->empty_cart();
		
		// Empty awaiting payment session
		unset($_SESSION['order_awaiting_payment']);
		
		// Return thankyou redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(get_option('cmdeals_thanks_page_id'))))
		);
    }

}

/**
 * Add the gateway to CMDeals
 **/
function add_bacs_gateway( $methods ) {
	$methods[] = 'cmdeals_bacs'; return $methods;
}

add_filter('cmdeals_payment_gateways', 'add_bacs_gateway' );
