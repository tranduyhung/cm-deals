<?php
/**
 * Pay Shortcode
 * 
 * The pay page. Used for form based gateways to show payment forms and order info.
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

function get_cmdeals_pay( $atts ) {
	global $cmdeals;
	return $cmdeals->shortcode_wrapper('cmdeals_pay', $atts); 
}

/**
 * Outputs the pay page - payment gateways can hook in here to show payment forms etc
 **/
function cmdeals_pay() {
	global $cmdeals;
	
	if ( isset($_GET['pay_for_order']) && isset($_GET['order']) && isset($_GET['order_id']) ) :
		
		// Pay for existing order
		$order_key = urldecode( $_GET['order'] );
		$order_id = (int) $_GET['order_id'];
		$order = new cmdeals_order( $order_id );
		
		if ($order->id == $order_id && $order->order_key == $order_key && in_array($order->status, array('pending', 'failed'))) :
			
			// Pay form was posted - process payment
			if (isset($_POST['pay']) && $cmdeals->verify_nonce('pay')) :
			
				// Update payment method
				if ($order->order_total > 0 ) : 
					$payment_method 			= cmdeals_clean($_POST['payment_method']);
					
					$available_gateways = $cmdeals->payment_gateways->get_available_payment_gateways();
					
					// Update meta
					update_post_meta( $order_id, '_payment_method', $payment_method);
					if (isset($available_gateways) && isset($available_gateways[$payment_method])) :
						$payment_method_title = $available_gateways[$payment_method]->title;
					endif;
					update_post_meta( $order_id, '_payment_method_title', $payment_method_title);

					$result = $available_gateways[$payment_method]->process_payment( $order_id );

					// Redirect to success/confirmation/payment page
					if ($result['result']=='success') :
						wp_redirect( $result['redirect'] );
						exit;
					endif;
				else :
					
					// No payment was required for order
					$order->payment_complete();
					wp_safe_redirect( get_permalink(get_option('cmdeals_thanks_page_id')) );
					exit;
					
				endif;
	
			endif;
			
			// Show messages
			$cmdeals->show_messages();
			
			// Show form
			cmdeals_pay_for_existing_order( $order );
		
		elseif (!in_array($order->status, array('pending', 'failed'))) :
			
			$cmdeals->add_error( __('Your order has already been paid for. Please contact us if you need assistance.', 'cmdeals') );
			
			$cmdeals->show_messages();
			
		else :
		
			$cmdeals->add_error( __('Invalid order.', 'cmdeals') );
			
			$cmdeals->show_messages();
			
		endif;
		
	else :
		
		// Pay for order after checkout step
		if (isset($_GET['order'])) $order_id = $_GET['order']; else $order_id = 0;
		if (isset($_GET['key'])) $order_key = $_GET['key']; else $order_key = '';
		
		if ($order_id > 0) :
		
			$order = new cmdeals_order( $order_id );
		
			if ($order->order_key == $order_key && in_array($order->status, array('pending', 'failed'))) :
		
				?>
				<ul class="order_details">
					<li class="order">
						<?php _e('Order:', 'cmdeals'); ?>
						<strong># <?php echo $order->id; ?></strong>
					</li>
					<li class="date">
						<?php _e('Date:', 'cmdeals'); ?>
						<strong><?php echo date(get_option('date_format'), strtotime($order->order_date)); ?></strong>
					</li>
					<li class="total">
						<?php _e('Total:', 'cmdeals'); ?>
						<strong><?php echo cmdeals_price($order->order_total); ?></strong>
					</li>
					<li class="method">
						<?php _e('Payment method:', 'cmdeals'); ?>
						<strong><?php 
							echo $order->payment_method_title; 
						?></strong>
					</li>
				</ul>
				
				<?php do_action( 'cmdeals_receipt_' . $order->payment_method, $order_id ); ?>
				
				<div class="clear"></div>
				<?php
				
			else :
			
				wp_safe_redirect( get_permalink(get_option('cmdeals_myaccount_page_id')) );
				exit;
				
			endif;
			
		else :
			
			wp_safe_redirect( get_permalink(get_option('cmdeals_myaccount_page_id')) );
			exit;
			
		endif;

	endif;
}

/**
 * Outputs the payment page when a user comes to pay from a link (for an existing/past created order)
 **/
function cmdeals_pay_for_existing_order( $pay_for_order ) {
	
	global $order;
	
	$order = $pay_for_order;
	
	cmdeals_get_template('checkout/pay_for_order.php');
	
}