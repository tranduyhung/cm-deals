<?php
/**
 * Order
 * 
 * The CMDeals order class handles order data.
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

class cmdeals_order {
	
	private $_data = array();
	
	public function __get($variable) {
		return isset($this->_data[$variable]) ? $this->_data[$variable] : null;
	}
	
	public function __set($variable, $value) {
		$this->_data[$variable] = $value;
	} 
	
	/** Get the order if ID is passed, otherwise the order is new and empty */
	function cmdeals_order( $id='' ) {
		if ($id>0) $this->get_order( $id );
	}
	
	/** Gets an order from the database */
	function get_order( $id = 0 ) {
		if (!$id) return false;
		if ($result = get_post( $id )) : 	 	  	 	
			$this->populate( $result );	 	 	 	 	 	
			return true;
		endif;
		return false;
	}
	
	/** Populates an order from the loaded post data */
	function populate( $result ) {
		global $cmdeals;
		
		// Standard post data
		$this->id = $result->ID; 
		$this->order_date = $result->post_date;
		$this->modified_date = $result->post_modified;	
		$this->customer_note = $result->post_excerpt;
		
		// Custom fields
		$this->items 			= (array) get_post_meta( $this->id, '_order_items', true );
		$this->user_id 			= (int) get_post_meta( $this->id, '_customer_user', true );
		$this->completed_date		= get_post_meta( $this->id, '_completed_date', true );
                
                $user_info = get_userdata($this->user_id);
                if ($user_info->user_email) $this->user_email       = $user_info->user_email;		
                if ($user_info->user_nicename) $this->user_name     = $user_info->display_name;		
		
		if (!$this->completed_date) $this->completed_date = $this->modified_date;
		
		$order_custom_fields = get_post_custom( $this->id ); //print_r($order_custom_fields);
		
		// Define the data we're going to load: Key => Default value
		$load_data = array(
			'order_key'			=> '',
			'payment_method'		=> '',
			'payment_method_title'          => '',
			'order_total'			=> ''
		);
		
		// Load the data from the custom fields
		foreach ($load_data as $key => $default) :
			if (isset($order_custom_fields[ '_' . $key ][0]) && $order_custom_fields[ '_' . $key ][0]!=='') :
				$this->$key = $order_custom_fields[ '_' . $key ][0];
			else :
				$this->$key = $default;
			endif;
		endforeach;
			
		// Taxonomy data 
		$terms = wp_get_object_terms( $this->id, 'deals_sales_status' );
		if (!is_wp_error($terms) && $terms) :
			$term = current($terms);
			$this->status = $term->slug; 
		else :
			$this->status = 'pending';
		endif;
			
	}
	
	/** Gets order total */
	function get_order_total() {
		return $this->order_total;
	}
	
	/** Calculate item cost - useful for gateways */
	function get_item_cost( $item, $inc_tax = false ) {
		if ($inc_tax) :
			return number_format( $item['cost'] * (1 + ($item['taxrate']/100)) , 2, '.', '');
		else :
			return number_format( $item['cost'] , 2, '.', '');
		endif;
	}
	
	/** Calculate row cost - useful for gateways */
	function get_row_cost( $item, $inc_tax = false ) {
		if ($inc_tax) :
			return number_format( ($item['cost'] * $item['qty']) * (1 + ($item['taxrate']/100)) , 2, '.', '');
		else :
			return number_format( $item['cost'] * $item['qty'] , 2, '.', '');
		endif;
	}
	
	
	/** Gets subtotal */
	function get_subtotal_to_display() {
		global $cmdeals;
		
		if ($this->display_totals_ex_tax || !$this->prices_include_tax) :
			
			$subtotal = cmdeals_price($this->order_subtotal);
			
			if ($this->order_tax>0 && $this->prices_include_tax) :
				$subtotal .= ' <small>'.$cmdeals->countries->ex_tax_or_vat().'</small>';
			endif;
		
		else :
			
			// Calculate subtotal inc. tax
			$subtotal = 0;
			
			foreach ($this->items as $item) :
				
				if (!isset($item['base_cost'])) $item['base_cost'] = $item['cost'];
				
				$subtotal += round(($item['base_cost']*$item['qty']) * (($item['taxrate']/100) + 1), 2);
				
			endforeach;

			$subtotal = cmdeals_price( $subtotal );
			
			if ($this->order_tax>0 && !$this->prices_include_tax) :
				$subtotal .= ' <small>'.$cmdeals->countries->inc_tax_or_vat().'</small>';
			endif;
		
		endif;
		
		return $subtotal;
	}
	
	/** Get a deals (either deals or variation) */
	function get_deals_from_item( $item ) {
		
		if (isset($item['variation_id']) && $item['variation_id']>0) :
			$_deals = new cmdeals_deals_variation( $item['variation_id'] );
		else :
			$_deals = new cmdeals_deals( $item['id'] );
		endif;
		
		return $_deals;

	}
	
	/** Output items for display in emails */
	function email_order_items_list( $show_download_links = false ) {
		
		$return = '';
		
		foreach($this->items as $item) : 
			
			$_deals = $this->get_deals_from_item( $item );

			$return .= $item['qty'] . ' x ' . apply_filters('cmdeals_order_deals_title', $item['name'], $_deals);
						
			$return .= ' - ' . strip_tags(cmdeals_price( $item['cost']*$item['qty'], array('ex_tax_label' => 1 )));
			
			$item_meta = new order_item_meta( $item['item_meta'] );					
			$return .= PHP_EOL . $item_meta->display( true, true );
			
			if ($show_download_links) :
				
				if ($_deals->exists) :
			
					if ($_deals->is_downloadable()) :
						$return .= PHP_EOL . ' - ' . $this->get_downloadable_file_url( $item['id'], $item['variation_id'] ) . '';
					endif;
		
				endif;	
					
			endif;
			
			$return .= PHP_EOL;
			
		endforeach;	
		
		return $return;	
	}
	
	/** Output items for display in html emails */
	function email_order_items_table( $show_item = false ) {

		$return = '';
		
		foreach($this->items as $item) : 
			
			$_deals = $this->get_deals_from_item( $item );
			
			$file = $variation = '';
						
//			$item_meta = new order_item_meta( $item['item_meta'] );					
//			$variation = '<br/><small>' . $item_meta->display( true, true ) . '</small>';
			
			if ($show_item) :
				
				if ($_deals->exists) :
			
					if ($_deals->is_downloadable()) :
						$file = '<br/><small><a href="' . $this->get_downloadable_file_url( $item['id'], $item['variation_id'] ) . '">'.__('Download here', 'cmdeals').'</a></small>';
                                        elseif( $_deals->is_vouchers()):
						$file = '<br/><small>'.$this->get_voucher( $item['id'] ).'</small>';
                                        endif;
		
				endif;	
					
			endif;
			
			$return .= '<tr>
				<td style="text-align:left; border: 1px solid #eee;">' . apply_filters('cmdeals_order_deals_title', $item['name'], $_deals) . $file . '</td>
				<td style="text-align:left; border: 1px solid #eee;">' . $item['qty'] . '</td>
				<td style="text-align:left; border: 1px solid #eee;">';
				
					if (!isset($item['discount_cost'])) $item['discount_cost'] = $item['cost'];
                                        $return .= cmdeals_price( round(($item['discount_cost']*$item['qty']), 2) );
			
			$return .= '	
				</td>
			</tr>';
			
		endforeach;	
		
		return $return;	
		
	}
	
	/**  Returns true if the order contains a downloadable deals */
	function has_downloadable_item() {
		$has_downloadable_item = false;
		
		foreach($this->items as $item) : 
			
			$_deals = $this->get_deals_from_item( $item );

			if ($_deals->exists && $_deals->is_downloadable()) :
				$has_downloadable_item = true;
			endif;
			
		endforeach;	
		
		return $has_downloadable_item;
	}
	
	/**  Generates a URL so that a customer can checkout/pay for their (unpaid - pending) order via a link */
	function get_checkout_payment_url() {
		
		$payment_page = get_permalink(get_option('cmdeals_pay_page_id'));
		
		if (get_option('cmdeals_force_ssl_checkout')=='yes' || is_ssl()) $payment_page = str_replace('http:', 'https:', $payment_page);
	
		return add_query_arg('pay_for_order', 'true', add_query_arg('order', $this->order_key, add_query_arg('order_id', $this->id, $payment_page)));
	}
	
	
	/** Generates a URL so that a customer can cancel their (unpaid - pending) order */
	function get_cancel_order_url() {
		global $cmdeals;
		return $cmdeals->nonce_url( 'cancel_order', add_query_arg('cancel_order', 'true', add_query_arg('order', $this->order_key, add_query_arg('order_id', $this->id, trailingslashit( home_url() )))));
	}
	
	
	/** Gets a downloadable deals file url */
	function get_downloadable_file_url( $item_id, $variation_id ) {
	 	
	 	$download_id = ($variation_id>0) ? $variation_id : $item_id;
				
		if ($this->user_id>0) :
			$user_info = get_userdata($this->user_id);
			if ($user_info->user_email) :
				$user_email = $user_info->user_email;
			endif;
		endif;
				
	 	return add_query_arg('download_file', $download_id, add_query_arg('order', $this->order_key, add_query_arg('email', $user_email, trailingslashit( home_url() ))));
	 }
	
	/** Gets a voucher deals*/
	function get_voucher( $item_id ) {
		
		global $wpdb;
                
                $voucher    = '';
		
		if ($this->user_id>0) :
			
			$user_info = get_userdata($this->user_id); 
			if ($user_info->user_email)
				$user_email = $user_info->user_email;

			$results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."cmdeals_permissions WHERE user_id = $this->user_id AND order_key = '$this->order_key' AND deal_id = $item_id" ));
			
			if ($results) foreach ($results as $result) :
				
				if ($result->order_id>0) :
                        
                                        $order = new cmdeals_order( $result->order_id );

                                        if ( $order->status!='completed' && $order->status!='processing' ) continue;
                                        
                                        $voucher  = sprintf( __('Voucher: %s', 'cmdeals'), $result->vouchers);
				
				endif;
				
			endforeach;
		
		endif;
		
		return apply_filters('cmdeals_email_get_voucher', $voucher);
                
	 }
	 
	/**
	 * Adds a note (comment) to the order
	 *
	 * @param   string	$note		Note to add
	 * @param   int		$is_customer_note	Is this a note for the customer?
	 */
	function add_order_note( $note, $is_customer_note = 0 ) {
		
		$comment_post_ID = $this->id;
		$comment_author = 'CMDeals';
		$comment_author_email = 'cmdeals@' . str_replace('www.', '', str_replace('http://', '', site_url()));
		$comment_author_url = '';
		$comment_content = $note;
		$comment_type = '';
		$comment_parent = 0;
		
		$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');
		
		$commentdata['comment_author_IP'] = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] );
		$commentdata['comment_agent']     = substr($_SERVER['HTTP_USER_AGENT'], 0, 254);
	
		$commentdata['comment_date']     = current_time('mysql');
		$commentdata['comment_date_gmt'] = current_time('mysql', 1);
	
		$comment_id = wp_insert_comment( $commentdata );
		
		add_comment_meta($comment_id, 'is_customer_note', $is_customer_note);
		
		if ($is_customer_note) :
			do_action( 'cmdeals_new_customer_note', $this->id, $note );
		endif;
		
		return $comment_id;
		
	}

	/**
	 * Adds a note (comment) to the order
	 *
	 * @param   string	$new_status		Status to change the order to
	 * @param   string	$note			Optional note to add
	 */
	function update_status( $new_status, $note = '' ) {
		
		if ($note) $note .= ' ';
	
		$new_status = get_term_by( 'slug', sanitize_title( $new_status ), 'deals_sales_status');
		if ($new_status) :
		
			wp_set_object_terms($this->id, $new_status->slug, 'deals_sales_status');
			
			if ( $this->status != $new_status->slug ) :
				// Status was changed
				do_action( 'cmdeals_sales_status_'.$new_status->slug, $this->id );
				do_action( 'cmdeals_sales_status_'.$this->status.'_to_'.$new_status->slug, $this->id );
				$this->add_order_note( $note . sprintf( __('Order status changed from %s to %s.', 'cmdeals'), $this->status, $new_status->slug ) );
				clean_term_cache( '', 'deals_sales_status' );
				
				// Date
				if ($new_status->slug=='completed') :
					update_post_meta( $this->id, '_completed_date', current_time('mysql') );
				endif;
				
				// Sales
				if ($this->status == 'on-hold' && ($new_status->slug=='processing' || $new_status->slug=='completed')) :
					$this->record_deals_sales();
				endif;
				
			endif;
		
		endif;
		
	}
	
	/**
	 * Cancel the order and restore the cart (before payment)
	 *
	 * @param   string	$note	Optional note to add
	 */
	function cancel_order( $note = '' ) {
		
		unset($_SESSION['order_awaiting_payment']);
		
		$this->update_status('cancelled', $note);
		
	}

	/**
	 * When a payment is complete this function is called
	 *
	 * Most of the time this should mark an order as 'processing' so that admin can process/post the items
	 * If the cart contains only downloadable items then the order is 'complete' since the admin needs to take no action
	 * Stock levels are reduced at this point
	 * Sales are also recorded for deals
	 */
	function payment_complete() {
		
		unset($_SESSION['order_awaiting_payment']);
		
		if ( $this->status=='on-hold' || $this->status=='pending' ) :
		
			$downloadable_order = false;
			$voucher_order      = false;
			
			if (sizeof($this->items)>0) foreach ($this->items as $item) :
			
				if ($item['id']>0) :
				
					$_deals = $this->get_deals_from_item( $item );
					if ( $_deals->exists && $_deals->is_downloadable() ) :
						$downloadable_order = true;
						continue;
					elseif ( $_deals->exists && $_deals->is_vouchers() ) :
						$voucher_order = true;
						continue;
					endif;
					
				endif;
				
				$downloadable_order = false;
				break;
			
			endforeach;
			
			if ($downloadable_order || $voucher_order) :
				$new_sales_status = 'completed';
			else :
				$new_sales_status = 'processing';
			endif;
			
			$new_sales_status = apply_filters('cmdeals_payment_complete_sales_status', $new_sales_status, $this->id);
			
			$this->update_status($new_sales_status);
			
			// Payment is complete so reduce stock levels
			$this->reduce_order_stock();
			
			do_action( 'cmdeals_payment_complete', $this->id );
		
		endif;
	}
	
	/**
	 * Record sales
	 */
	function record_deals_sales() {
		
		if ( get_post_meta( $this->id, '_recorded_sales', true )=='yes' ) return;
		
		if (sizeof($this->items)>0) foreach ($this->items as $item) :
			if ($item['id']>0) :
				$sales 	= (int) get_post_meta( $item['id'], 'total_sales', true );
				$sales += (int) $item['qty'];
				if ($sales) update_post_meta( $item['id'], 'total_sales', $sales );
			endif;
		endforeach;
		
		update_post_meta( $this->id, '_recorded_sales', 'yes' );
		
	}
	
	/**
	 * Reduce stock levels
	 */
	function reduce_order_stock() {
		
		if (sizeof($this->items)>0) :
		
			// Reduce stock levels and do any other actions with deals in the cart
			foreach ($this->items as $item) :
			
				if ($item['id']>0) :
					$_deals = $this->get_deals_from_item( $item );
					
					if ( $_deals->exists && $_deals->managing_stock() ) :
					
						$old_stock = $_deals->_stock;
						
						$new_quantity = $_deals->reduce_stock( $item['qty'] );
						
						$this->add_order_note( sprintf( __('Item #%s stock reduced from %s to %s.', 'cmdeals'), $item['id'], $old_stock, $new_quantity) );
							
						if ($new_quantity<0) :
							do_action('cmdeals_deals_on_backorder_notification', $item['id'], $item['qty']);
						endif;
						
						// stock status notifications
						if (get_option('cmdeals_notify_no_stock_amount') && get_option('cmdeals_notify_no_stock_amount')>=$new_quantity) :
							do_action('cmdeals_no_stock_notification', $item['id']);
						elseif (get_option('cmdeals_notify_low_stock_amount') && get_option('cmdeals_notify_low_stock_amount')>=$new_quantity) :
							do_action('cmdeals_low_stock_notification', $item['id']);
						endif;
						
					endif;
					
				endif;
			 	
			endforeach;
			
			$this->add_order_note( __('Order item stock reduced successfully.', 'cmdeals') );
		
		endif;
			
	}
	
	/**
	 * List order notes (public) for the customer
	 */
	function get_customer_order_notes() {
		
		$notes = array();
		
		$args = array(
			'post_id' => $this->id,
			'approve' => 'approve',
			'type' => ''
		);
		
		remove_filter('comments_clauses', 'cmdeals_exclude_order_comments');
		
		$comments = get_comments( $args );
		
		foreach ($comments as $comment) :
			$is_customer_note = get_comment_meta($comment->comment_ID, 'is_customer_note', true);
			if ($is_customer_note) $notes[] = $comment;
		endforeach;
		
		add_filter('comments_clauses', 'cmdeals_exclude_order_comments');
		
		return (array) $notes;
		
	}

}


/**
 * Order Item Meta
 * 
 * A Simple class for managing order item meta so plugins add it in the correct format
 */
class order_item_meta {
	
	var $meta;
	
	/**
	 * Constructor
	 */
	function __construct( $item_meta = '' ) {
		$this->meta = array();
		
		if ($item_meta) $this->meta = $item_meta;
	}
	
	/**
	 * Load item meta
	 */
	function new_order_item( $item ) {
		if ($item) :
			do_action('cmdeals_order_item_meta', $this, $item);
		endif;
	}
	
	/**
	 * Add meta
	 */
	function add( $name, $value ) {
		$this->meta[] = array(
			'meta_name'     => $name, 
			'meta_value' 	=> $value
		);
	}
	
	/**
	 * Display meta in a formatted list
	 */
	function display( $flat = false, $return = false ) {
		global $cmdeals;
		
		if ($this->meta && is_array($this->meta)) :
			
			if (!$flat) $output = '<dl class="variation">'; else $output = '';
			
			$meta_list = array();
			
			foreach ($this->meta as $meta) :
				
				$name 	= $meta['meta_name'];
				$value	= $meta['meta_value'];
				
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
					$meta_list[] = $cmdeals->attribute_label(str_replace('attribute_', '', $name)).': '.$value;
				else :
					$meta_list[] = '<dt>'.$cmdeals->attribute_label(str_replace('attribute_', '', $name)).':</dt><dd>'.$value.'</dd>';
				endif;
				
			endforeach;
			
			if ($flat) :
				$output .= implode(', ', $meta_list);
			else :
				$output .= implode('', $meta_list);
			endif;
			
			if (!$flat) $output .= '</dl>';

			if ($return) return $output; else echo $output;
			
		endif;
	}
	
}