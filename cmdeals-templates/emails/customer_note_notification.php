<?php if (!defined('ABSPATH')) exit; ?>

<?php global $order_id, $cmdeals, $customer_note; $order = new cmdeals_order( $order_id ); ?>

<?php do_action('cmdeals_email_header'); ?>

<p><?php _e("Hello, a note has just been added to your order:", 'cmdeals'); ?></p>

<blockquote><?php echo wpautop(wptexturize( $customer_note )) ?></blockquote>

<p><?php _e("For your reference, your order details are shown below.", 'cmdeals'); ?></p>

<?php do_action('cmdeals_email_before_order_table', $order, false); ?>

<h2><?php echo __('Order #:', 'cmdeals') . ' ' . $order->id; ?></h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<thead>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Deal', 'cmdeals'); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Quantity', 'cmdeals'); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e('Price', 'cmdeals'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee;"><?php _e('Order Total:', 'cmdeals'); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo cmdeals_price($order->get_order_total()); ?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php if ($order->status=='completed') echo $order->email_order_items_table( true, true ); else echo $order->email_order_items_table( false, true ); ?>
	</tbody>
</table>

<?php do_action('cmdeals_email_after_order_table', $order, false); ?>

<div style="clear:both;"></div>

<?php do_action('cmdeals_email_footer'); ?>