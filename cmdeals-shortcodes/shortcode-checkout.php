<?php
/**
 * Checkout Shortcode
 * 
 * Used on the checkout page, the checkout shortcode displays the checkout process.
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

function get_cmdeals_checkout( $atts ) {
	global $cmdeals;
	return $cmdeals->shortcode_wrapper('cmdeals_checkout', $atts);
}

function cmdeals_checkout( $atts ) {
	global $cmdeals, $cmdeals_checkout;
	$errors = array();
	$validation = $cmdeals->validation();
	
	// Process Discount Codes
	if (isset($_POST['apply_coupon']) && $_POST['apply_coupon'] && $cmdeals->verify_nonce('cart')) :
	
		$coupon_code = stripslashes(trim($_POST['coupon_code']));
		$cmdeals->cart->add_discount($coupon_code);
	
	// Remvoe Discount Codes
	elseif (isset($_GET['remove_discounts'])) :
		
		$cmdeals->cart->remove_coupons( $_GET['remove_discounts'] );
		
		// Re-calc price
		$cmdeals->cart->calculate_totals();
	
	endif;
	
	do_action('cmdeals_check_cart_items');
	
	$cmdeals->show_messages();
	
	if (sizeof($cmdeals->cart->get_cart())==0) :
		echo '<p>'.__('Your cart is currently empty.', 'cmdeals').'</p>';
		do_action('cmdeals_cart_is_empty');
		echo '<p><a class="button" href="'.get_permalink(get_option('cmdeals_store_page_id')).'">'.__('&larr; Return To Store', 'cmdeals').'</a></p>';
		return;
	endif;
	
	?>
	<form action="<?php echo esc_url( $cmdeals->cart->get_cart_url() ); ?>" method="post">
	<table class="store_table cart" cellspacing="0">
		<thead>
			<tr>
				<th class="daily-deals-remove"></th>
				<th class="daily-deals-thumbnail"></th>
				<th class="daily-deals-name"><span class="nobr"><?php _e('Deal Name', 'cmdeals'); ?></span></th>
				<th class="daily-deals-quantity"><?php _e('Quantity', 'cmdeals'); ?></th>
				<th class="daily-deals-subtotal"><?php _e('Price', 'cmdeals'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if (sizeof($cmdeals->cart->get_cart())>0) : 
				foreach ($cmdeals->cart->get_cart() as $cart_item_key => $values) :
					$_deals = $values['data'];
					if ($_deals->exists() && $values['quantity']>0) :
					
						?>
						<tr>
							<td class="daily-deals-remove"><a href="<?php echo esc_url( $cmdeals->cart->get_remove_url($cart_item_key) ); ?>" class="remove" title="<?php _e('Remove this item', 'cmdeals'); ?>">&times;</a></td>
							<td class="daily-deals-thumbnail">
								<?php echo $_deals->get_image(); ?>
							</td>
							<td class="daily-deals-name">
								<?php echo $_deals->get_title(); ?>
								<?php echo $cmdeals->cart->get_item_data( $values ); //Meta data ?>
							</td>
							<td class="daily-deals-quantity"><input type="hidden" name="cart[<?php echo $cart_item_key; ?>][qty]" value="<?php echo esc_attr( $values['quantity'] ); ?>"/><?php echo esc_attr( $values['quantity'] ); ?></td>
							<td class="daily-deals-subtotal"><?php 

								echo $cmdeals->cart->get_deals_subtotal( $_deals, $values['quantity'] )	;
														
							?></td>
						</tr>
						<?php
					endif;
				endforeach; 
			endif;
			
			do_action( 'cmdeals_cart_contents' );
			?>
		</tbody>
	</table>
	</form>
	<div class="cart-collaterals">
		
		<?php do_action('cmdeals_cart_collaterals'); ?>
		
		<?php cmdeals_cart_totals(); ?>
		
	</div>
	<?php	
	
	if (!defined('CMDEALS_CHECKOUT')) define('CMDEALS_CHECKOUT', true);
	
	if (sizeof($cmdeals->cart->get_cart())==0) :
		wp_redirect(get_permalink(get_option('cmdeals_checkout_page_id')));
		exit;
	endif;
	
	$non_js_checkout = (isset($_POST['update_totals']) && $_POST['update_totals']) ? true : false;
	
	$cmdeals_checkout = $cmdeals->checkout();
	
	$cmdeals_checkout->process_checkout();
	
	do_action('cmdeals_check_cart_items');
	
	if ( $cmdeals->error_count()==0 && $non_js_checkout) $cmdeals->add_message( __('The order totals have been updated. Please confirm your order by pressing the Place Order button at the bottom of the page.', 'cmdeals') );
	
	$cmdeals->show_messages();
	
	cmdeals_get_template('checkout/form.php', false);
	
}