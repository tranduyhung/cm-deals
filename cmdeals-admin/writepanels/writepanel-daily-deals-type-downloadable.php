<?php
/**
 * Downloadable Deal Type
 * 
 * Functions specific to downloadable deals (for the write panels)
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
 * Deal Options
 * 
 * Deal Options for the downloadable deals type
 */
function downloadable_deals_type_options() {
	global $post;
	?>
	<div id="downloadable_deals_options" class="panel cmdeals_options_panel">
		<?php

			// File URL
			$file_path = get_post_meta($post->ID, 'file_path', true);
			$field = array( 'id' => 'file_path', 'label' => __('File path', 'cmdeals') );
			echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
				<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path/URL', 'cmdeals').'" />
				<input type="button"  class="upload_file_button button" value="'.__('Upload a file', 'cmdeals').'" /> <span class="description">' . __('Create in zip for images files.', 'cmdeals') . '</span>
			</p>';
				
			// Download Limit
			$download_limit = get_post_meta($post->ID, 'download_limit', true);
			$field = array( 'id' => 'download_limit', 'label' => __('Download Limit', 'cmdeals') );
			echo '<p class="form-field">
				<label for="'.$field['id'].'">'.$field['label'].':</label>
				<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$download_limit.'" /> <span class="description">' . __('Leave blank for unlimited re-downloads.', 'cmdeals') . '</span></p>';

		?>
	</div>
	<?php
}
add_action('cmdeals_deals_type_options_box', 'downloadable_deals_type_options');


/**
 * Deal Type Javascript
 * 
 * Javascript for the downloadable deals type
 */
function downloadable_deals_write_panel_js() {
	global $post;
	?>
	jQuery(function(){

		window.send_to_editor_default = window.send_to_editor;

		jQuery('.upload_file_button').live('click', function(){
			
			var post_id = <?php echo $post->ID; ?>;
			
			formfield = jQuery('#file_path').attr('name');
			
			window.send_to_editor = window.send_to_download_url;
			
			tb_show('', 'media-upload.php?post_id=' + post_id + '&amp;type=file&amp;from=wd01&amp;TB_iframe=true');
			return false;
		});

		window.send_to_download_url = function(html) {
			
			file_url = jQuery(html).attr('href');
			if (file_url) {
				jQuery('#file_path').val(file_url);
			}
			tb_remove();
			window.send_to_editor = window.send_to_editor_default;
			
		}
		
	});
	<?php
}
add_action('cmdeals_deals_write_panel_js', 'downloadable_deals_write_panel_js');

add_filter( 'gettext', 'cmdeals_change_insert_into_post', null, 2 );

function cmdeals_change_insert_into_post( $translation, $original ) {
    if( !isset( $_REQUEST['from'] ) ) return $translation;

    if( $_REQUEST['from'] == 'wd01' && $original == 'Insert into Post' ) return __('Insert into URL field', 'cmdeals' );

    return $translation;
}


/**
 * Deal Type selector
 * 
 * Adds this deals type to the deals type selector in the deals options meta box
 */
function downloadable_deals_type_selector( $types, $deal_type ) {
	$types['downloadable'] = __('Downloadable', 'cmdeals');
	return $types;
}
add_filter('deal_type_selector', 'downloadable_deals_type_selector', 1, 2);

/**
 * Process meta
 * 
 * Processes this deals types options when a post is saved
 */
function process_deals_meta_downloadable( $post_id ) {
	
	if (isset($_POST['file_path']) && $_POST['file_path']) update_post_meta( $post_id, 'file_path', $_POST['file_path'] );
	if (isset($_POST['download_limit'])) update_post_meta( $post_id, 'download_limit', $_POST['download_limit'] );

}
add_action('cmdeals_process_deals_meta_downloadable', 'process_deals_meta_downloadable');