<?php
/**
 * CMDeals Emails
 * 
 * Email handling for important store events.
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

/**
 * Mail from name/email
 **/
function cmdeals_mail_from_name( $name ) {
	return get_option('cmdeals_email_from_name');
}
function cmdeals_mail_from( $email ) {
	return get_option('cmdeals_email_from_address');
}

/**
 * HTML emails from CMDeals
 **/
function cmdeals_mail( $to, $subject, $message, $headers = "Content-Type: text/html\r\n", $attachments = "" ) {
	
	add_filter( 'wp_mail_from', 'cmdeals_mail_from' );
	add_filter( 'wp_mail_from_name', 'cmdeals_mail_from_name' );
	add_filter( 'wp_mail_content_type', 'cmdeals_email_content_type' );
	
	// Send the mail	
	wp_mail( $to, $subject, $message, $headers, $attachments );
	
	// Unhook
	remove_filter( 'wp_mail_from', 'cmdeals_mail_from' );
	remove_filter( 'wp_mail_from_name', 'cmdeals_mail_from_name' );
	remove_filter( 'wp_mail_content_type', 'cmdeals_email_content_type' );
}

/**
 * Wraps a message in the cmdeals mail template
 **/
function cmdeals_mail_template( $heading, $message ) {
	global $email_heading;
	
	$email_heading = $heading;
	
	// Buffer
	ob_start();

	do_action('cmdeals_email_header');
	
	echo wpautop(wptexturize( $message ));
	
	do_action('cmdeals_email_footer');
	
	// Get contents
	$message = ob_get_clean();
	
	return $message;
}

/**
 * Email Header
 **/
add_action('cmdeals_email_header', 'cmdeals_email_header');

function cmdeals_email_header() {
	cmdeals_get_template('emails/email_header.php', false);
}


/**
 * Email Footer
 **/
add_action('cmdeals_email_footer', 'cmdeals_email_footer');

function cmdeals_email_footer() {
	cmdeals_get_template('emails/email_footer.php', false);
}	
	
/**
 * HTML email type
 **/
function cmdeals_email_content_type($content_type){
	return 'text/html';
}


/**
 * Fix recieve password mail links
 **/
function cmdeals_retrieve_password_message($content){
	return htmlspecialchars($content);
}
	

/**
 * Hooks for emails
 **/
add_action('cmdeals_low_stock_notification', 'cmdeals_low_stock_notification');
add_action('cmdeals_no_stock_notification', 'cmdeals_no_stock_notification');
add_action('cmdeals_deals_on_backorder_notification', 'cmdeals_deals_on_backorder_notification', 1, 2);
 
 
/**
 * New order notification email template
 **/
add_action('cmdeals_sales_status_pending_to_processing', 'cmdeals_new_order_notification');
add_action('cmdeals_sales_status_pending_to_completed', 'cmdeals_new_order_notification');
add_action('cmdeals_sales_status_pending_to_on-hold', 'cmdeals_new_order_notification');
add_action('cmdeals_sales_status_failed_to_processing', 'cmdeals_new_order_notification');
add_action('cmdeals_sales_status_failed_to_completed', 'cmdeals_new_order_notification');

function cmdeals_new_order_notification( $id ) {
	
	global $order_id, $email_heading;
	
	$order_id = $id;
	
	$email_heading = __('New Customer Order', 'cmdeals');
	
	$subject = sprintf(__('[%s] New Customer Order (# %s)', 'cmdeals'), get_bloginfo('name'), $order_id);
	
	// Buffer
	ob_start();
	
	// Get mail template
	cmdeals_get_template('emails/new_order.php', false);
	
	// Get contents
	$message = ob_get_clean();

	// Send the mail	
	cmdeals_mail( get_option('cmdeals_new_order_email_recipient'), $subject, $message );
}


/**
 * Processing order notification email template
 **/
add_action('cmdeals_sales_status_pending_to_processing', 'cmdeals_processing_order_customer_notification');
add_action('cmdeals_sales_status_pending_to_on-hold', 'cmdeals_processing_order_customer_notification');
 
function cmdeals_processing_order_customer_notification( $id ) {
	
	global $order_id, $email_heading;
	
	$order_id = $id;
	
	$order = new cmdeals_order( $order_id );
	
	$email_heading = __('Order Received', 'cmdeals');
	
	$subject = sprintf(__('[%s] Order Received', 'cmdeals'), get_bloginfo('name'));
	
	// Buffer
	ob_start();
	
	// Get mail template
	cmdeals_get_template('emails/customer_processing_order.php', false);
	
	// Get contents
	$message = ob_get_clean();

	// Send the mail	
	cmdeals_mail( $order->user_email, $subject, $message );
}


/**
 * Completed order notification email template - this one includes download links for downloadable deals
 **/
add_action('cmdeals_sales_status_completed', 'cmdeals_completed_order_customer_notification');
 
function cmdeals_completed_order_customer_notification( $id ) {
	
	global $order_id, $email_heading;
	
	$order_id = $id;
	
	$order = new cmdeals_order( $order_id );
	
	if ($order->has_downloadable_item()) :
		$subject		= __('[%s] Order Complete/Download Links', 'cmdeals');
		$email_heading 	= __('Order Complete/Download Links', 'cmdeals');
	else :
		$subject		= __('[%s] Order Complete', 'cmdeals');
		$email_heading 	= __('Order Complete', 'cmdeals');
	endif;
	
	$email_heading = apply_filters('cmdeals_completed_order_customer_notification_subject', $email_heading);

	$subject = sprintf($subject, get_bloginfo('name'));
	
	// Buffer
	ob_start();
	
	// Get mail template
	cmdeals_get_template('emails/customer_completed_order.php', false);
	
	// Get contents
	$message = ob_get_clean();

	// Send the mail	
	cmdeals_mail( $order->user_email, $subject, $message );
}


/**
 * Pay for order notification email template - this one includes a payment link
 **/
function cmdeals_pay_for_order_customer_notification( $the_order ) {
	
	global $order_id, $order, $email_heading;
	
	$order = $the_order;
	$order_id = $order->id;
	
	$email_heading = sprintf(__('Invoice for Order #%s', 'cmdeals'), $order_id);

	$subject = sprintf(__('[%s] Pay for Order', 'cmdeals'), get_bloginfo('name'));

	// Buffer
	ob_start();
	
	// Get mail template
	cmdeals_get_template('emails/customer_pay_for_order.php', false);
	
	// Get contents
	$message = ob_get_clean();

	// Send the mail	
	cmdeals_mail( $order->user_email, $subject, $message );
}

/**
 * Customer note notification
 **/
add_action('cmdeals_new_customer_note', 'cmdeals_customer_note_notification', 10, 2);

function cmdeals_customer_note_notification( $id, $note ) {
	
	global $order_id, $email_heading, $customer_note;
	
	$order_id = $id;
	$customer_note = $note;
	
	$order = new cmdeals_order( $order_id );
	
	if (!$customer_note) return;
	
	$email_heading = __('A note has been added to your order', 'cmdeals');
	
	$subject = sprintf(__('[%s] A note has been added to your order', 'cmdeals'), get_bloginfo('name'));
	
	// Buffer
	ob_start();
	
	// Get mail template
	cmdeals_get_template('emails/customer_note_notification.php', false);
	
	// Get contents
	$message = ob_get_clean();

	// Send the mail	
	cmdeals_mail( $order->user_email, $subject, $message );
}


/**
 * Low stock notification email
 **/
function cmdeals_low_stock_notification( $deal ) {
	$_deals = new cmdeals_deals($deal);

	$subject = '[' . get_bloginfo('name') . '] ' . __('Deal low in stock', 'cmdeals');
	
	$message = cmdeals_mail_template( 
		__('Deal low in stock', 'cmdeals'),
		'#' . $_deals->id .' '. $_deals->get_title() .' ' . __('is low in stock.', 'cmdeals') .
                '[<a href="'.get_edit_post_link( $_deals->id ).'" target="_blank">'.__('Edit here', 'cmdeals').'</a>]'
	);

	// Send the mail
	cmdeals_mail( get_option('cmdeals_stock_email_recipient'), $subject, $message );
}

/**
 * No stock notification email
 **/
function cmdeals_no_stock_notification( $deal ) {
	$_deals = new cmdeals_deals($deal);
	
	$subject = '[' . get_bloginfo('name') . '] ' . __('Deal out of stock', 'cmdeals');
	
	$message = cmdeals_mail_template( 
		__('Deal out of stock', 'cmdeals'),
		'#' . $_deals->id .' '. $_deals->get_title() . __('is out of stock.', 'cmdeals')
	);

	// Send the mail
	cmdeals_mail( get_option('cmdeals_stock_email_recipient'), $subject, $message );
}


/**
 * Backorder notification email
 **/
function cmdeals_deals_on_backorder_notification( $deal, $amount ) {
	$_deals = new cmdeals_deals($deal);
	
	$subject = '[' . get_bloginfo('name') . '] ' . __('Deal Backorder', 'cmdeals');

	$message = cmdeals_mail_template( 
		__('Deal Backorder', 'cmdeals'),
		$amount . __(' units of #', 'cmdeals') . $_deals->id .' '. $_deals->get_title() . ' ' . __('have been backordered.', 'cmdeals')
	);

	// Send the mail
	cmdeals_mail( get_option('cmdeals_stock_email_recipient'), $subject, $message );
}

/**
 * Preview Emails
 **/
add_action('admin_init', 'cmdeals_preview_emails');

function cmdeals_preview_emails() {
	if (isset($_GET['preview_cmdeals_mail'])) :
		$nonce = $_REQUEST['_wpnonce'];
		if (!wp_verify_nonce($nonce, 'preview-mail') ) die('Security check'); 
		
		global $email_heading;
	
		$email_heading = __('Email preview', 'cmdeals');
		
		do_action('cmdeals_email_header');
		
		echo '<h2>CMDeals sit amet</h2>';
		
		echo wpautop('Ut ut est qui euismod parum. Dolor veniam tation nihil assum mazim. Possim fiant habent decima et claritatem. Erat me usus gothica laoreet consequat. Clari facer litterarum aliquam insitam dolor. 

Gothica minim lectores demonstraverunt ut soluta. Sequitur quam exerci veniam aliquip litterarum. Lius videntur nisl facilisis claritatem nunc. Praesent in iusto me tincidunt iusto. Dolore lectores sed putamus exerci est. ');
		
		do_action('cmdeals_email_footer');
		
		exit;
		
	endif;
}

/**
 * Add order meta to email templates
 **/
add_action('cmdeals_email_after_order_table', 'cmdeals_email_order_meta', 10, 2);

function cmdeals_email_order_meta( $order, $sent_to_admin ) {
	
	$meta = array();
	$show_fields = apply_filters('cmdeals_email_order_meta_keys', array('coupons'), $sent_to_admin);

	if ($order->customer_note) :
		$meta[__('Note:', 'cmdeals')] = wptexturize($order->customer_note);
	endif;
	
	if ($show_fields) foreach ($show_fields as $field) :
		
		$value = get_post_meta( $order->id, $field, true );
		if ($value) $meta[ucwords(esc_attr($field))] = wptexturize($value);
		
	endforeach;
	
	if (sizeof($meta)>0) :
		echo '<h2>'.__('Order information', 'cmdeals').'</h2>';
		foreach ($meta as $key=>$value) :
			echo '<p><strong>'.$key.':</strong> '.$value.'</p>';
		endforeach;
	endif;
}


/**
 * Customer new account welcome email
 **/
function cmdeals_customer_new_account( $user_id, $plaintext_pass ) {
	global $email_heading, $user_login, $user_pass, $blogname;
	
	if ( empty($plaintext_pass) ) return;
	
	$user = new WP_User($user_id);
	
	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);
	$user_pass 	= $plaintext_pass;
	 
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
	$subject		= sprintf(__('Your account on %s', 'cmdeals'), $blogname);
	$email_heading 	= __('Your account details', 'cmdeals');

	// Buffer
	ob_start();
	
	// Get mail template
	cmdeals_get_template('emails/customer_new_account.php', false);
	
	// Get contents
	$message = ob_get_clean();

	// Send the mail	
	cmdeals_mail( $user_email, $subject, $message );
}


/* --------------------------------------------------------------- */
/* Voucher
/* --------------------------------------------------------------- */

/**
 * Voucher Header
 **/
add_action('cmdeals_voucher_header', 'cmdeals_voucher_header');

function cmdeals_voucher_header() {
	cmdeals_get_template('voucher/voucher_header.php', false);
}


/**
 * Voucher Footer
 **/
add_action('cmdeals_voucher_footer', 'cmdeals_voucher_footer');

function cmdeals_voucher_footer() {
	cmdeals_get_template('voucher/voucher_footer.php', false);
}


/**
 * Download Voucher
 **/
add_action('init', 'cmdeals_download_voucher');

function cmdeals_download_voucher() {
	if (isset($_GET['cmdeals-download-voucher']) ) :

		$nonce = $_REQUEST['_wpnonce'];
		if (!wp_verify_nonce($nonce, 'download-voucher') ) die('Security check'); 
				
		global $voucher;
                
		$voucher['heading']     = apply_filters('cmdeals_voucher_heading', __('Voucher Detail', 'cmdeals'));
		$voucher['code']        = (isset($_GET['code']) && $_GET['code'] != '' )? $_GET['code']:'';
		if (isset($_GET['deal-id']) && $_GET['deal-id'] != '' ) 
                    $voucher['id']   = $_GET['deal-id'];
                else
                    return;
		
                // Get mail template
                cmdeals_get_template('voucher/download_voucher.php', false);
                
		exit;
		
	endif;
}