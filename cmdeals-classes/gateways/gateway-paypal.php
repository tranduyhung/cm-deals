<?php
/**
 * PayPal Standard Payment Gateway
 * 
 * Provides a PayPal Standard Payment Gateway.
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

class cmdeals_paypal extends cmdeals_payment_gateway {
		
	public function __construct() { 
		global $cmdeals;
		
                $this->id		= 'paypal';
                $this->icon 		= apply_filters('cmdeals_paypal_icon', $cmdeals->plugin_url() . '/cmdeals-assets/images/icons/paypal.png');
                $this->has_fields 	= false;
                $this->liveurl 		= 'https://www.paypal.com/webscr';
		$this->testurl 		= 'https://www.sandbox.paypal.com/webscr';
        
		// Load the form fields.
		$this->init_form_fields();
		
		// Load the settings.
		$this->init_settings();
		
		// Define user set variables
		$this->title 		= $this->settings['title'];
		$this->description 	= $this->settings['description'];
		$this->email 		= $this->settings['email'];
		$this->testmode		= $this->settings['testmode'];
		$this->debug		= $this->settings['debug'];	
		
		// Logs
		if ($this->debug=='yes') $this->log = $cmdeals->logger();
		
		// Actions
		add_action( 'init', array(&$this, 'check_ipn_response') );
		add_action('valid-paypal-standard-ipn-request', array(&$this, 'successful_request') );
		add_action('cmdeals_receipt_paypal', array(&$this, 'receipt_page'));
		add_action('cmdeals_update_options_payment_gateways', array(&$this, 'process_admin_options'));
		
		if ( !$this->is_valid_for_use() ) $this->enabled = false;
    } 
    
     /**
     * Check if this gateway is enabled and available in the user's country
     */
    function is_valid_for_use() {
        if (!in_array(get_option('cmdeals_currency'), array('AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'TRY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP'))) return false;

        return true;
    }
    
	/**
	 * Admin Panel Options 
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {

    	?>
    	<h3><?php _e('PayPal standard', 'cmdeals'); ?></h3>
    	<p><?php _e('PayPal standard works by sending the user to PayPal to enter their payment information.', 'cmdeals'); ?></p>
    	<table class="form-table">
    	<?php
    		if ( $this->is_valid_for_use() ) :
    	
    			// Generate the HTML For the settings form.
    			$this->generate_settings_html();
    		
    		else :
    		
    			?>
            		<div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'cmdeals' ); ?></strong>: <?php _e( 'PayPal does not support your store currency.', 'cmdeals' ); ?></p></div>
        		<?php
        		
    		endif;
    	?>
		</table><!--/.form-table-->
    	<?php
    } // End admin_options()
    
	/**
     * Initialise Gateway Settings Form Fields
     */
    function init_form_fields() {
    
    	$this->form_fields = array(
			'enabled' => array(
							'title' => __( 'Enable/Disable', 'cmdeals' ), 
							'type' => 'checkbox', 
							'label' => __( 'Enable PayPal standard', 'cmdeals' ), 
							'default' => 'yes'
						), 
			'title' => array(
							'title' => __( 'Title', 'cmdeals' ), 
							'type' => 'text', 
							'description' => __( 'This controls the title which the user sees during checkout.', 'cmdeals' ), 
							'default' => __( 'PayPal', 'cmdeals' )
						),
			'description' => array(
							'title' => __( 'Description', 'cmdeals' ), 
							'type' => 'textarea', 
							'description' => __( 'This controls the description which the user sees during checkout.', 'cmdeals' ), 
							'default' => __("Pay via PayPal; you can pay with your credit card if you don't have a PayPal account", 'cmdeals')
						),
			'email' => array(
							'title' => __( 'PayPal Email', 'cmdeals' ), 
							'type' => 'text', 
							'description' => __( 'Please enter your PayPal email address; this is needed in order to take payment.', 'cmdeals' ), 
							'default' => ''
						),
			'testmode' => array(
							'title' => __( 'PayPal sandbox', 'cmdeals' ), 
							'type' => 'checkbox', 
							'label' => __( 'Enable PayPal sandbox', 'cmdeals' ), 
							'default' => 'yes'
						),
			'debug' => array(
							'title' => __( 'Debug', 'cmdeals' ), 
							'type' => 'checkbox', 
							'label' => __( 'Enable logging (<code>cmdeals/cmdeals-logs/paypal.txt</code>)', 'cmdeals' ), 
							'default' => 'no'
						)
			);
    
    } // End init_form_fields()
    
    /**
	 * There are no payment fields for paypal, but we want to show the description if set.
	 **/
    function payment_fields() {
    	if ($this->description) echo wpautop(wptexturize($this->description));
    }
    
	/**
	 * Generate the paypal button link
	 **/
    public function generate_paypal_form( $order_id ) {
		global $cmdeals;
		
		$order = new cmdeals_order( $order_id );
		
		if ( $this->testmode == 'yes' ):
			$paypal_adr = $this->testurl . '?test_ipn=1&';		
		else :
			$paypal_adr = $this->liveurl . '?';		
		endif;
		
		if ($this->debug=='yes') $this->log->add( 'paypal', 'Generating payment form for order #' . $order_id . '. Notify URL: ' . trailingslashit(home_url()).'?paypalListener=paypal_standard_IPN');
		
		
		$paypal_args = array(
                        'cmd' 			=> '_cart',
                        'business' 		=> $this->email,
                        'no_note' 		=> 1,
                        'currency_code' 	=> get_option('cmdeals_currency'),
                        'charset' 		=> 'UTF-8',
                        'rm' 			=> 2,
                        'upload' 		=> 1,
                        'return' 		=> $this->get_return_url( $order ),
                        'cancel_return'		=> $order->get_cancel_order_url(),
                        'no_shipping'           => 1,

                        // Order key
                        'custom'		=> $order_id,

                        // IPN
                        'notify_url'		=> trailingslashit(home_url()).'?paypalListener=paypal_standard_IPN',

                        // Payment Info
                        'invoice' 		=> $order->order_key
                );
                
                // Cart Contents
                $item_loop = 0;
                if (sizeof($order->items)>0) : foreach ($order->items as $item) :
                        if ($item['qty']) :

                                $item_loop++;

                                $item_name = $item['name'];

                                $item_meta = new order_item_meta( $item['item_meta'] );					
                                if ($meta = $item_meta->display( true, true )) :
                                        $item_name .= ' ('.$meta.')';
                                endif;

                                $paypal_args['item_name_'.$item_loop] = $item_name;
                                $paypal_args['quantity_'.$item_loop] = $item['qty'];
                                $paypal_args['amount_'.$item_loop] = number_format($item['cost'], 2, '.', '');

                        endif;
                endforeach; endif;
		
		$paypal_args_array = array();

		foreach ($paypal_args as $key => $value) {
			$paypal_args_array[] = '<input type="hidden" name="'.esc_attr( $key ).'" value="'.esc_attr( $value ).'" />';
		}
		
		$cmdeals->add_inline_js('
			jQuery("body").block({ 
					message: "<img src=\"'.esc_url( $cmdeals->plugin_url() ).'/cmdeals-assets/images/ajax-loader.gif\" alt=\"Redirecting...\" style=\"float:left; margin-right: 10px;\" />'.__('Thank you for your order. We are now redirecting you to PayPal to make payment.', 'cmdeals').'", 
					overlayCSS: 
					{ 
						background: "#fff", 
						opacity: 0.6 
					},
					css: { 
				        padding:        20, 
				        textAlign:      "center", 
				        color:          "#555", 
				        border:         "3px solid #aaa", 
				        backgroundColor:"#fff", 
				        cursor:         "wait",
				        lineHeight:		"32px"
				    } 
				});
			jQuery("#submit_paypal_payment_form").click();
		');
		
		return '<form action="'.esc_url( $paypal_adr ).'" method="post" id="paypal_payment_form">
				' . implode('', $paypal_args_array) . '
				<input type="submit" class="button-alt" id="submit_paypal_payment_form" value="'.__('Pay via PayPal', 'cmdeals').'" /> <a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__('Cancel order &amp; restore cart', 'cmdeals').'</a>
			</form>';
		
	}
	
	/**
	 * Process the payment and return the result
	 **/
	function process_payment( $order_id ) {
		
		$order = new cmdeals_order( $order_id );
		
		return array(
			'result' 	=> 'success',
			'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(get_option('cmdeals_pay_page_id'))))
		);
		
	}
	
	/**
	 * receipt_page
	 **/
	function receipt_page( $order ) {
		
		echo '<p>'.__('Thank you for your order, please click the button below to pay with PayPal.', 'cmdeals').'</p>';
		
		echo $this->generate_paypal_form( $order );
		
	}
	
	/**
	 * Check PayPal IPN validity
	 **/
	function check_ipn_request_is_valid() {
		global $cmdeals;
		
		if ($this->debug=='yes') $this->log->add( 'paypal', 'Checking IPN response is valid...' );
    
    	 // Add cmd to the post array
        $_POST['cmd'] = '_notify-validate';

        // Send back post vars to paypal
        $params = array( 
        	'body' => $_POST,
        	'sslverify' => false
        );

        // Get url
       	if ( $this->testmode == 'yes' ):
			$paypal_adr = $this->testurl;		
		else :
			$paypal_adr = $this->liveurl;		
		endif;
		
		// Post back to get a response
        $response = wp_remote_post( $paypal_adr, $params );
		
		 // Clean
        unset($_POST['cmd']);
        
        // check to see if the request was valid
        if ( !is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && (strcmp( $response['body'], "VERIFIED") == 0)) {
            if ($this->debug=='yes') $this->log->add( 'paypal', 'Received valid response from PayPal' );
            return true;
        } 
        
        if ($this->debug=='yes') :
        	$this->log->add( 'paypal', 'Received invalid response from PayPal' );
        	if (is_wp_error($response)) :
        		$this->log->add( 'paypal', 'Error response: ' . $result->get_error_message() );
        	endif;
        endif;
        
        return false;
    }
	
	/**
	 * Check for PayPal IPN Response
	 **/
	function check_ipn_response() {
			
		if (isset($_GET['paypalListener']) && $_GET['paypalListener'] == 'paypal_standard_IPN'):
		
        	$_POST = stripslashes_deep($_POST);
        	
        	if ($this->check_ipn_request_is_valid()) :
        	
            	do_action("valid-paypal-standard-ipn-request", $_POST);

       		endif;
       		
       	endif;
			
	}
	
	/**
	 * Successful Payment!
	 **/
	function successful_request( $posted ) {
		
		// Custom holds post ID
	    if ( !empty($posted['custom']) && !empty($posted['invoice']) ) {
	
			$order = new cmdeals_order( (int) $posted['custom'] );
	        if ($order->order_key!==$posted['invoice']) exit;
	        
	        // Sandbox fix
	        if ($posted['test_ipn']==1 && $posted['payment_status']=='Pending') $posted['payment_status'] = 'completed';
	        
	        // We are here so lets check status and do actions
	        switch (strtolower($posted['payment_status'])) :
	            case 'completed' :
	            	
	            	// Check order not already completed
	            	if ($order->status == 'completed') exit;
	            	
	            	// Check valid txn_type
	            	$accepted_types = array('cart', 'instant', 'express_checkout', 'web_accept', 'masspay', 'send_money');
					if (!in_array(strtolower($posted['txn_type']), $accepted_types)) exit;
	            	
	            	// Payment completed
	                $order->add_order_note( __('IPN payment completed', 'cmdeals') );
	                $order->payment_complete();
	                
	                // Store PP Details
	                update_post_meta( (int) $posted['custom'], 'Payer PayPal address', $posted['payer_email']);
	                update_post_meta( (int) $posted['custom'], 'Transaction ID', $posted['txn_id']);
	                update_post_meta( (int) $posted['custom'], 'Payer first name', $posted['first_name']);
	                update_post_meta( (int) $posted['custom'], 'Payer last name', $posted['last_name']);
	                update_post_meta( (int) $posted['custom'], 'Payment type', $posted['payment_type']); 
	                
	            break;
	            case 'denied' :
	            case 'expired' :
	            case 'failed' :
	            case 'voided' :
	                // Order failed
	                $order->update_status('failed', sprintf(__('Payment %s via IPN.', 'cmdeals'), strtolower($posted['payment_status']) ) );
	            break;
	            case "refunded" :
	            case "reversed" :
	            case "chargeback" :
	            	
	            	// Mark order as refunded
	            	$order->update_status('refunded', sprintf(__('Payment %s via IPN.', 'cmdeals'), strtolower($posted['payment_status']) ) );
	            	
					$message = cmdeals_mail_template( 
						__('Order refunded/reversed', 'cmdeals'),
						sprintf(__('Order #%s has been marked as refunded - PayPal reason code: %s', 'cmdeals'), $order->id, $posted['reason_code'] )
					);
				
					// Send the mail
					cmdeals_mail( get_option('cmdeals_new_order_email_recipient'), sprintf(__('Payment for order #%s refunded/reversed', 'cmdeals'), $order->id), $message );
	            	
	            break;
	            default:
	            	// No action
	            break;
	        endswitch;
			
			exit;
			
	    }
		
	}

}

/**
 * Add the gateway to CMDeals
 **/
function add_paypal_gateway( $methods ) {
	$methods[] = 'cmdeals_paypal'; return $methods;
}

add_filter('cmdeals_payment_gateways', 'add_paypal_gateway' );
