<?php
/**
 * My Account Shortcode
 * 
 * Shows the 'my account' section where the customer can view past sales and update their information.
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

function get_cmdeals_my_account ( $atts ) {
	global $cmdeals;
	return $cmdeals->shortcode_wrapper('cmdeals_my_account', $atts); 
}	
function cmdeals_my_account( $atts ) {
	global $cmdeals;
	
	extract(shortcode_atts(array(
    	'recent_sales' => 10
	), $atts));

  	$recent_sales = ('all' == $recent_sales) ? -1 : $recent_sales;
	
	global $post, $current_user;

	get_currentuserinfo();
	
	$cmdeals->show_messages();
	
	if (is_user_logged_in()) :
	
		?>
		<p><?php echo sprintf( __('Hello, <strong>%s</strong>. From your account dashboard you can view your recent sales, download deals, view your voucher code and <a href="%s">change your password</a>.', 'cmdeals'), $current_user->display_name, get_permalink(get_option('cmdeals_change_password_page_id'))); ?></p>
		
		<?php do_action('cmdeals_before_my_account'); ?>
		
		<?php if ($downloads = $cmdeals->customer->get_downloadable_deals()) : ?>
		<h2><?php _e('Available downloads', 'cmdeals'); ?></h2>
		<table class="store_table digital-downloads">
                    <thead>
                        <td><?php _e('Deals', 'cmdeals'); ?></td>
                        <td><?php _e('Files', 'cmdeals'); ?></td>
                        <td><?php _e('Download', 'cmdeals'); ?></td>
                    </thead>
			<?php foreach ($downloads as $download) : ?>
                    <tbody>
                        <td><?php echo $download['download_name']; ?></td>
                        <td><a href="<?php echo esc_url( $download['download_url'] ); ?>"><?php _e('Download'); ?></a></td>
                        <td><?php if (is_numeric($download['downloads_remaining'])) : ?><span class="count"><?php echo $download['downloads_remaining'] . _n(' download Remaining', ' downloads Remaining', $download['downloads_remaining'], 'cmdeals'); ?></span><?php else: _e('Unlimited Download', 'cmdeals'); endif; ?></td>
                    </tbody>
			<?php endforeach; ?>
		</table>
		<?php endif; ?>	
                
		<?php if ($coupons = $cmdeals->customer->get_coupons_deals()) : ?>
		<h2><?php _e('Available Vouchers', 'cmdeals'); ?></h2>		
		<table class="store_table voucher-deals">
                    <thead>
                        <td><?php _e('Deals', 'cmdeals'); ?></td>
                        <td><?php _e('Voucher', 'cmdeals'); ?></td>
                        <td><?php _e('How To Use', 'cmdeals'); ?></td>
                        <td><?php _e('Print', 'cmdeals'); ?></td>
                    </thead>
			<?php foreach ($coupons as $coupon) : ?>
                    <tbody>
                        <td><?php echo $coupon['voucher_name']; ?></td>
                        <td><?php echo $coupon['voucher_value']; ?></td>
                        <td><a href="#howto-<?php echo $coupon['deal_id']; ?>" class="zoom"><?php _e('View', 'cmdeals'); ?></a>
                            <div class="voucher-how-to" id="howto-<?php echo $coupon['deal_id']; ?>">
                                <?php echo stripslashes(get_post_meta($coupon['deal_id'], 'how_to_use', true)); ?>
                            </div>
                        </td>
                        <td><a href="#" onclick="window.open('<?php echo wp_nonce_url('?cmdeals-download-voucher=true&deal-id='.$coupon['deal_id'].'&code='.$coupon['voucher_value'], 'download-voucher'); ?>', 'popupwindow', 'scrollbars=yes,width=650,height=650,location=no');return true"><?php _e('Click', 'cmdeals'); ?></a></td>
                    </tbody>
			<?php endforeach; ?>
		</table>
		<?php endif; ?>	
		
		
		<h2><?php _e('Recent Orders', 'cmdeals'); ?></h2>
		<?php
		$args = array(
		    'numberposts'   => $recent_sales,
		    'meta_key'      => '_customer_user',
		    'meta_value'    => get_current_user_id(),
		    'post_type'     => 'deals-sales',
		    'post_status'   => 'publish' 
		);
		$customer_sales = get_posts($args);
		if ($customer_sales) :
		?>
			<table class="store_table my_account_sales">
			
				<thead>
					<tr>
						<th><span class="nobr"><?php _e('#', 'cmdeals'); ?></span></th>
						<th><span class="nobr"><?php _e('Date', 'cmdeals'); ?></span></th>
						<th><span class="nobr"><?php _e('Total', 'cmdeals'); ?></span></th>
						<th colspan="2"><span class="nobr"><?php _e('Status', 'cmdeals'); ?></span></th>
					</tr>
				</thead>
				
				<tbody><?php
					foreach ($customer_sales as $customer_order) :
						$order = new cmdeals_order();
						$order->populate($customer_order);
						?><tr class="order">
							<td><?php echo $order->id; ?></td>
							<td><time title="<?php echo esc_attr( strtotime($order->order_date) ); ?>"><?php echo date(get_option('date_format'), strtotime($order->order_date)); ?></time></td>
							<td><?php echo cmdeals_price($order->order_total); ?></td>
							<td><?php 
								$status = get_term_by('slug', $order->status, 'deals_sales_status');
								echo __($status->name, 'cmdeals'); 
							?></td>
							<td style="text-align:right; white-space:nowrap;">
								<?php if (in_array($order->status, array('pending', 'failed'))) : ?>
									<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e('Pay', 'cmdeals'); ?></a>
									<a href="<?php echo esc_url( $order->get_cancel_order_url() ); ?>" class="button cancel"><?php _e('Cancel', 'cmdeals'); ?></a>
								<?php endif; ?>
								<a href="<?php echo esc_url( add_query_arg('order', $order->id, get_permalink(get_option('cmdeals_view_order_page_id'))) ); ?>" class="button"><?php _e('View', 'cmdeals'); ?></a>
							</td>
						</tr><?php
					endforeach;
				?></tbody>
			
			</table>
		<?php
		else : 
			_e('You have no recent sales.', 'cmdeals');
		endif;
                
		do_action('cmdeals_after_my_account');
		
	else :
		
		// Login/register template
		cmdeals_get_template( 'myaccount/login.php' );
		
	endif;
		
}

function get_cmdeals_change_password() {
	global $cmdeals;
	return $cmdeals->shortcode_wrapper('cmdeals_change_password'); 
}	
function cmdeals_change_password() {
	global $cmdeals;
	
	$user_id = get_current_user_id();
	
	if (is_user_logged_in()) :
		
		if ($_POST) :
			
			if ($user_id>0 && $cmdeals->verify_nonce('change_password')) :
				
				if ( $_POST['password-1'] && $_POST['password-2']  ) :
					
					if ( $_POST['password-1']==$_POST['password-2'] ) :
	
						wp_update_user( array ('ID' => $user_id, 'user_pass' => $_POST['password-1']) ) ;
						
						wp_safe_redirect( get_permalink(get_option('cmdeals_myaccount_page_id')) );
						exit;
						
					else :
					
						$cmdeals->add_error( __('Passwords do not match.', 'cmdeals') );
					
					endif;
				
				else :
				
					$cmdeals->add_error( __('Please enter your password.', 'cmdeals') );
					
				endif;			
				
			endif;
		
		endif;
		
		$cmdeals->show_messages();

		?>
		<form action="<?php echo esc_url( get_permalink(get_option('cmdeals_change_password_page_id')) ); ?>" method="post">
	
			<p class="form-row form-row-first">
				<label for="password-1"><?php _e('New password', 'cmdeals'); ?> <span class="required">*</span></label>
				<input type="password" class="input-text" name="password-1" id="password-1" />
			</p>
			<p class="form-row form-row-last">
				<label for="password-2"><?php _e('Re-enter new password', 'cmdeals'); ?> <span class="required">*</span></label>
				<input type="password" class="input-text" name="password-2" id="password-2" />
			</p>
			<div class="clear"></div>
			<?php $cmdeals->nonce_field('change_password')?>
			<p><input type="submit" class="button" name="save_password" value="<?php _e('Save', 'cmdeals'); ?>" /></p>
	
		</form>
		<?php
		
	else :
	
		wp_safe_redirect( get_permalink(get_option('cmdeals_myaccount_page_id')) );
		exit;
		
	endif;
	
}

function get_cmdeals_view_order () {
	global $cmdeals;
	return $cmdeals->shortcode_wrapper('cmdeals_view_order'); 
}	

function cmdeals_view_order() {
	global $cmdeals;
	
	$user_id = get_current_user_id();
	
	if (is_user_logged_in()) :
	
		if (isset($_GET['order'])) $order_id = (int) $_GET['order']; else $order_id = 0;
	
		$order = new cmdeals_order( $order_id );
		
		if ( $order_id>0 && $order->user_id == get_current_user_id() ) :
			
			echo '<p>' . sprintf( __('Order <mark>#%s</mark> made on <mark>%s</mark>', 'cmdeals'), $order->id, date(get_option('date_format'), strtotime($order->order_date)) );
			
			$status = get_term_by('slug', $order->status, 'deals_sales_status');
			
			echo sprintf( __('. Order status: <mark>%s</mark>', 'cmdeals'), __($status->name, 'cmdeals') );
			
			echo '.</p>';

			$notes = $order->get_customer_order_notes();
			if ($notes) :
				?>
				<h2><?php _e('Order Updates', 'cmdeals'); ?></h2>
				<ol class="commentlist notes">	
					<?php foreach ($notes as $note) : ?>
					<li class="comment note">
						<div class="comment_container">			
							<div class="comment-text">
								<p class="meta"><?php echo date_i18n('l jS \of F Y, h:ia', strtotime($note->comment_date)); ?></p>
								<div class="description">
									<?php echo wpautop(wptexturize($note->comment_content)); ?>
								</div>
				  				<div class="clear"></div>
				  			</div>
							<div class="clear"></div>			
						</div>
					</li>
					<?php endforeach; ?>
				</ol>
				<?php
			endif;
			
			do_action( 'cmdeals_view_order', $order_id );
		
		else :
		
			wp_safe_redirect( get_permalink(get_option('cmdeals_myaccount_page_id')) );
			exit;
			
		endif;
		
	else :
	
		wp_safe_redirect( get_permalink(get_option('cmdeals_myaccount_page_id')) );
		exit;
		
	endif;
}
