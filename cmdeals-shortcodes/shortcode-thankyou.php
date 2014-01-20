<?php
/**
 * Thankyou Shortcode
 * 
 * The thankyou page displays after successful checkout and can be hooked into by payment gateways.
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

function get_cmdeals_thankyou( $atts ) {
	global $cmdeals;
	return $cmdeals->shortcode_wrapper('cmdeals_thankyou', $atts); 
}

/**
 * Outputs the order received page
 **/
function cmdeals_thankyou( $atts ) {
	global $cmdeals;

	// Pay for order after checkout step
	if (isset($_GET['order'])) $order_id = $_GET['order']; else $order_id = 0;
	if (isset($_GET['key'])) $order_key = $_GET['key']; else $order_key = '';
	
	// Empty awaiting payment session
	unset($_SESSION['order_awaiting_payment']);
	
	if ($order_id > 0) :
	
		$order = new cmdeals_order( $order_id );
		
		if ($order->order_key == $order_key) :
		
			if (in_array($order->status, array('failed'))) :
				
				echo '<p>' . __('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'cmdeals') . '</p>';

				echo '<p>';

					if (is_user_logged_in()) :
						_e('Please attempt your purchase again or go to your account page.', 'cmdeals');
					else :
						_e('Please attempt your purchase again.', 'cmdeals');
					endif;
				
				echo '</p>';
				
				echo '<a href="'.esc_url( $order->get_checkout_payment_url() ).'" class="button pay">'.__('Pay', 'cmdeals').'</a> ';
				
				if (is_user_logged_in()) :
					echo '<a href="'.esc_url( get_permalink(get_option('cmdeals_myaccount_page_id')) ).'" class="button pay">'.__('My Account', 'cmdeals').'</a>';
				endif;

			else :
				
				echo '<p>' . __('Thank you. Your order has been received.', 'cmdeals') . '</p>';
				
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
				<div class="clear"></div>
				<?php
			
			endif;
			
			do_action( 'cmdeals_thankyou_' . $order->payment_method, $order_id );
			do_action( 'cmdeals_thankyou', $order_id );
			
		endif;
	
	else :
	
		echo '<p>' . __('Thank you. Your order has been received.', 'cmdeals') . '</p>';
		
	endif;
	
}