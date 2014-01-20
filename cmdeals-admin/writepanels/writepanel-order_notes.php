<?php
/**
 * Order Notes
 * 
 * Functions for displaying order comments in admin
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
 * Order notes meta box
 */
function cmdeals_order_notes_meta_box() {
	global $cmdeals, $post;

	$args = array(
		'post_id' => $post->ID,
		'approve' => 'approve',
		'type' => ''
	);
	
	$notes = get_comments( $args );
	
	echo '<ul class="order_notes">';

	if ($notes) :
		foreach($notes as $note) : 

			$customer_note = get_comment_meta($note->comment_ID, 'is_customer_note', true);
			
			echo '<li rel="'.$note->comment_ID.'" class="note ';
			if ($customer_note) echo 'customer-note';
			echo '"><div class="note_content">';
			echo wpautop(wptexturize($note->comment_content));
			echo '</div><p class="meta">'. sprintf(__('added %s ago', 'cmdeals'), human_time_diff(strtotime($note->comment_date), current_time('timestamp'))) .' - <a href="#" class="delete_note">'.__('Delete note', 'cmdeals').'</a></p>';
			echo '</li>';
		endforeach;
	else :
		echo '<li>' . __('There are no notes for this order yet.', 'cmdeals') . '</li>';
	endif;
	
	echo '</ul>';
	?>
	<div class="add_note">
		<h4><?php _e('Add note', 'cmdeals'); ?></h4>
		<p><?php _e('Add a note for your reference, or add a customer note (the user will be notified).', 'cmdeals'); ?></p>
		<p><input type="text" name="order_note" id="add_order_note" class="input-text" />
		<select name="order_note_type" id="order_note_type">
			<option value="customer"><?php _e('Customer note', 'cmdeals'); ?></option>
			<option value=""><?php _e('Private note', 'cmdeals'); ?></option>
		</select></p>
		<a href="#" class="add_note button"><?php _e('Add', 'cmdeals'); ?></a>
	</div>
	<script type="text/javascript">
		
		jQuery('a.add_note').click(function(){
			
			if (!jQuery('input#add_order_note').val()) return;
			
			jQuery('#cmdeals-order-notes').block({ message: null, overlayCSS: { background: '#fff url(<?php echo $cmdeals->plugin_url(); ?>/cmdeals-assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
			
			var data = {
				action: 		'cmdeals_add_order_note',
				post_id:		'<?php echo $post->ID; ?>',
				note: 			jQuery('input#add_order_note').val(),
				note_type:		jQuery('select#order_note_type').val(),
				security: 		'<?php echo wp_create_nonce("add-order-note"); ?>'
			};

			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
				
				jQuery('ul.order_notes').prepend( response );
				jQuery('#cmdeals-order-notes').unblock();
				jQuery('#add_order_note').val('');
				
			});
			
			return false;
			
		});
		
		jQuery('a.delete_note').live('click', function(){
			
			var note = jQuery(this).closest('li.note');
			
			jQuery(note).block({ message: null, overlayCSS: { background: '#fff url(<?php echo $cmdeals->plugin_url(); ?>/cmdeals-assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
			
			var data = {
				action: 		'cmdeals_delete_order_note',
				note_id:		jQuery(note).attr('rel'),
				security: 		'<?php echo wp_create_nonce("delete-order-note"); ?>'
			};

			jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {
				
				jQuery(note).remove();
				
			});
			
			return false;
			
		});
		
	</script>
	<?php
}