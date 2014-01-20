<?php
/**
 * Deal Data
 * 
 * Function for displaying the deals data meta boxes
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

//require_once('writepanel-daily-deals-type-variable.php');


/**
* Procuct type panel
**/
function cmdeals_deals_type_box() {
	
	global $post, $thepostid;
	
	$thepostid = $post->ID;

	echo '<div class="cmdeals_options_panel">';
	
	// Deal Type
	if ($terms = wp_get_object_terms( $thepostid, 'deal-type' )) $deal_type = current($terms)->slug; else $deal_type = 'simple';
	
	cmdeals_wp_select( array( 'id' => 'daily-deals-type', 'label' => __('Deal Type', 'cmdeals'), 'value' => $deal_type, 'options' => apply_filters('deal_type_selector', array(
		'simple' => __('Simple deals', 'cmdeals'),
		'external' => __('External/Affiliate deals', 'cmdeals')
	), $deal_type) ) );
        
	// Featured
	cmdeals_wp_checkbox( array( 'id' => 'featured', 'label' => __('Featured', 'cmdeals'), 'description' => __('Enable this option to feature this deals', 'cmdeals') ) );
        
	cmdeals_wp_checkbox( array( 'id' => '_is_expired', 'label' => __('Is Expired', 'cmdeals'), 'description' => __('Check this if want manually expired', 'cmdeals') ) );
	
	echo '</div>';
        
        
	global $post, $wpdb, $thepostid, $cmdeals;
	add_action('admin_footer', 'cmdeals_meta_scripts');
	wp_nonce_field( 'cmdeals_save_data', 'cmdeals_meta_nonce' );
	
	$thepostid = $post->ID;
	
	$deal_custom_fields = get_post_custom( $thepostid );
	?>
	<div class="panel-wrap deal_data">
	
		<ul class="deal_data_tabs tabs" style="display:none;">
			
			<li class="active show_if_simple show_if_variable show_if_external"><a href="#general_deals_data"><?php _e('General', 'cmdeals'); ?></a></li>
			
			<li class="inventory_tab show_if_simple show_if_variable show_if_downloadable"><a href="#inventory_deals_data"><?php _e('Inventory', 'cmdeals'); ?></a></li>
			
			<li class="downloads_tab show_if_simple show_if_downloadable"><a href="#downloadable_deals_data"><?php _e('Downloads', 'cmdeals'); ?></a></li>
                        
			<li class="coupons_tab show_if_simple show_if_couponable"><a href="#couponable_deals_data"><?php _e('Vouchers', 'cmdeals'); ?></a></li>
			
			<?php do_action('cmdeals_deals_write_panel_tabs'); ?>

		</ul>
		<div id="general_deals_data" class="panel cmdeals_options_panel"><?php
						
			echo '<div class="options_group show_if_external">';
				// External URL
				cmdeals_wp_text_input( array( 'id' => 'deal_url', 'label' => __('Deal URL', 'cmdeals'), 'placeholder' => 'http://', 'description' => __('Enter the external URL to the deals.', 'cmdeals') ) );
			
			echo '</div>';
				
			echo '<div class="options_group pricing show_if_simple show_if_external">';
			
				// Price
				cmdeals_wp_text_input( array( 'id' => '_base_price', 'label' => __('Regular Price', 'cmdeals') . ' ('.get_cmdeals_currency_symbol().')' ) );
				
				// Special Price
				cmdeals_wp_text_input( array( 'id' => '_discount_price', 'label' => __('Sale Price', 'cmdeals') . ' ('.get_cmdeals_currency_symbol().')' ) );
						
				// Special Price date range
				$field = array( 'id' => 'end_time', 'label' => __('End Time', 'cmdeals') );
				
                                
                                // convert old version into new version 2.0
				$end_time       = get_post_meta($thepostid, '_end_time', true);
                                $end_time_old   = explode(' ', $end_time);
                                
				echo '	<p class="form-field sale_price_dates_fields">
							<label for="'.$field['id'].'">'.$field['label'].'</label>
							<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="';
				
				if(count($end_time_old) == 2)
                                    echo $end_time;
                                else
                                    if ($end_time) echo date('Y-m-d H:i:s', $end_time);
				echo '" placeholder="' . __('Until&hellip;', 'cmdeals') . '" maxlength="19" />
							<span class="description">' . __('Date format', 'cmdeals') . ': <code>YYYY-MM-DD hh:mm:ss</code></span>
						</p>';
                                	
				do_action('cmdeals_deals_options_pricing');
					
			echo '</div>';
                        
                        echo '<div class="options_group">';                        
	
                                cmdeals_wp_checkbox( array( 'id' => 'downloadable', 'wrapper_class' => 'show_if_simple', 'label' => __('Downloadable or Voucher', 'cmdeals'), 'description' => __('Enable this option if access is given to a downloadable file upon purchase of a deals or unchecked if given a voucher code.', 'cmdeals') ) );
	
			echo '</div>';					
			
			do_action('cmdeals_deals_options_general_deals_data');

			?>
		</div>
		<div id="inventory_deals_data" class="panel cmdeals_options_panel">
			
			<?php
			echo '<div class="stock_fields show_if_simple show_if_variable">';
			
			// Stock
			cmdeals_wp_text_input( array( 'id' => '_stock', 'label' => __('Stock Qty', 'cmdeals') ) );

			do_action('cmdeals_deals_options_stock_fields');
			
			echo '</div>';
			?>			
			
		</div>
		<div id="downloadable_deals_data" class="panel cmdeals_options_panel">
			<?php
	
				// File URL
				$file_path = get_post_meta($post->ID, 'file_path', true);
				$field = array( 'id' => 'file_path', 'label' => __('File path', 'cmdeals') );
				echo '<p class="form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
					<input type="text" class="short file_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path/URL', 'cmdeals').'" />
					<input type="button"  class="upload_file_button button" value="'.__('Upload a file', 'cmdeals').'" /> <span class="description">' . __('Create in zip file for images files.', 'cmdeals') . '</span>
				</p>';
					
				// Download Limit
				$download_limit = get_post_meta($post->ID, 'download_limit', true);
				$field = array( 'id' => 'download_limit', 'label' => __('Download Limit', 'cmdeals') );
				echo '<p class="form-field">
					<label for="'.$field['id'].'">'.$field['label'].':</label>
					<input type="text" class="short" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$download_limit.'" placeholder="'.__('Unlimited', 'cmdeals').'" /> <span class="description">' . __('Leave blank for unlimited re-downloads.', 'cmdeals') . '</span></p>';
	
			?>
		</div>
		<div id="couponable_deals_data" class="panel cmdeals_options_panel">
                        <div class="stock_fields show_if_simple show_if_variable">
                            <div class="options_group">
                                <?php 
                                    // Coupons
                                    cmdeals_wp_text_area( array( 'id' => '_coupon_area_deals', 'label' => __('Voucher Codes', 'cmdeals'), 'description' => __('Put voucher codes here (separate with ";")', 'cmdeals') ) );
                                ?>
                                <p class="form-field">
                                    <label>&nbsp;</label>
                                    <input type="button" id="generate_coupon" class="button" value="<?php _e('Generate Voucher', 'cmdeals'); ?>" onclick="randCouponString();"/>
                                </p>
                            </div>
                            <div class="options_group">
                                <p align="center"><?php _e('OR', 'cmdeals'); ?></p>
                            </div>
                            <div class="options_group">                                
                                <p class="form-field">
                                    <label for="coupon_file"><?php _e('Import CVS file', 'cmdeals'); ?></label>
                                    <input type="file" name="coupon_file" id="coupon_file" />
                                    <span class="description"><?php _e('import your custom voucher codes from cvs file.', 'cmdeals'); ?></span>
                                </p>
                            </div>
                            <div class="options_group">
                                <p align="center"><?php _e('How To Use', 'cmdeals'); ?></p>
                            </div>
                            <div class="options_group">
                                <?php 
                                    // how to use
                                    cmdeals_wp_text_area( array( 'id' => 'how_to_use', 'label' => __('How To Use', 'cmdeals'), 'description' => __('Describe to user how to use this voucher.', 'cmdeals') ) );
                                ?>
                            </div>
                        <?php

			do_action('cmdeals_deals_options_coupon_fields');
			
			echo '</div>';
			?>		
		</div>
		
		<?php do_action('cmdeals_deals_write_panels'); ?>
		
	</div>
	<?php
			
}

/**
 * Deal Data Save
 * 
 * Function for processing and storing all deals data.
 */
add_action('cmdeals_process_daily-deals_meta', 'cmdeals_process_deals_meta', 1, 2);

function cmdeals_process_deals_meta( $post_id, $post ) {
	global $wpdb, $cmdeals;

	$cmdeals_errors = array();
		
	// Update post meta
	update_post_meta( $post_id, '_base_price', stripslashes( $_POST['_base_price'] ) );
	update_post_meta( $post_id, '_discount_price', stripslashes( $_POST['_discount_price'] ) );
	update_post_meta( $post_id, 'stock_status', stripslashes( $_POST['stock_status'] ) );
	if (isset($_POST['featured'])) update_post_meta( $post_id, 'featured', 'yes' ); else update_post_meta( $post_id, 'featured', 'no' );
	if (isset($_POST['_is_expired'])) update_post_meta( $post_id, '_is_expired', 'yes' ); else update_post_meta( $post_id, '_is_expired', 'no' );
		
	// Deal type + Downloadable/Virtual
	$deal_type = sanitize_title( stripslashes( $_POST['daily-deals-type'] ) );
	$is_downloadable = (isset($_POST['downloadable'])) ? 'yes' : 'no';
	
	if( !$deal_type ) $deal_type = 'simple';
	
	wp_set_object_terms($post_id, $deal_type, 'deal-type');
	update_post_meta( $post_id, 'downloadable', $is_downloadable );
	
	// Set transient for deals type
	set_transient( 'cmdeals_deals_type_' . $post_id, $deal_type );

	// Sales and prices
	if ($deal_type=='simple' || $deal_type=='external') :
		
		$date_to    = (isset($_POST['end_time'])) ? $_POST['end_time'] : '';
		$stock      = (isset($_POST['_stock'])) ? $_POST['_stock'] : 0;
                
                if($is_downloadable == 'no' && $deal_type=='simple'):
                    $coupons    = stripslashes($_POST['_coupon_area_deals']);
                    
                    /* 
                     * get the file from post 
                     */
                    $tmp_name = $_FILES['coupon_file']['tmp_name'];
                    if(isset($tmp_name)) {                         
                        if (($handle = fopen( $tmp_name, "r" )) !== FALSE) {
                            while (($data = fgetcsv( $handle, 2000, "," )) !== FALSE) {
                                $coupons .= $data[0].'; ';
                            }

                        }
                    }
                    
                    update_post_meta( $post_id, '_coupon_area_deals', $coupons );                    
                    update_post_meta( $post_id, 'how_to_use', stripslashes($_POST['how_to_use']) );                    
                    
                else:
                    update_post_meta( $post_id, '_coupon_area_deals', '' );
                    update_post_meta( $post_id, 'how_to_use', '' );
                
                endif;
		
		// Dates
		if ($date_to) :
                        if(strtotime($date_to) < strtotime(date('Y-m-d H:i:s')))                            
                            update_post_meta( $post_id, '_is_expired', 'yes' );
                            
			update_post_meta( $post_id, '_end_time', strtotime($date_to) );
		else :
			update_post_meta( $post_id, '_end_time', strtotime(date('Y-m-d H:i:s')) );
			update_post_meta( $post_id, '_is_expired', 'yes' );
		endif;
                
		// Update price
                update_post_meta( $post_id, '_discount_price', stripslashes($_POST['_discount_price']) );
                update_post_meta( $post_id, '_base_price', stripslashes($_POST['_base_price']) );
		
                // update stock
                update_post_meta( $post_id, '_stock', $stock );
                
	else :
		
		update_post_meta( $post_id, '_base_price', '' );
		update_post_meta( $post_id, '_discount_price', '' );
		update_post_meta( $post_id, '_end_time', '' );
		update_post_meta( $post_id, '_stock', '' );
                update_post_meta( $post_id, '_is_expired', 'yes' );
	endif;
	
	// Downloadable options
	if ($is_downloadable=='yes') :
		
		if (isset($_POST['file_path']) && $_POST['file_path']) update_post_meta( $post_id, 'file_path', esc_attr($_POST['file_path']) );
		if (isset($_POST['download_limit'])) update_post_meta( $post_id, 'download_limit', esc_attr($_POST['download_limit']) );
		
	endif;
	
	// Deal url
	if ($deal_type=='external') :
		
		if (isset($_POST['deal_url']) && $_POST['deal_url']) update_post_meta( $post_id, 'deal_url', esc_attr($_POST['deal_url']) );
		
	endif;
			
	// Do action for deals type
	do_action( 'cmdeals_process_daily-deals_meta_' . $deal_type, $post_id );
	
	// Clear cache/transients
	$cmdeals->clear_deals_transients( $post_id );
		
	// Save errors
	update_option('cmdeals_errors', $cmdeals_errors);
}

/**
* Outputs deals list in selection boxes
**/
function cmdeals_deals_selection_list_remove( $posts_to_display, $name ) {
	global $thepostid;
	
	$args = array(
		'post_type'	=> 'daily-deals',
		'post_status'     => 'publish',
		'numberposts' => -1,
		'orderby' => 'title',
		'order' => 'asc',
		'include' => $posts_to_display,
	);
	$related_posts = get_posts($args);
	$loop = 0;
	if ($related_posts) : foreach ($related_posts as $related_post) :
		
		if ($related_post->ID==$thepostid) continue;
		
		?><li rel="<?php echo $related_post->ID; ?>"><button type="button" name="Remove" class="button remove" title="Remove">&times;</button><strong><?php echo $related_post->post_title; ?></strong> &ndash; #<?php echo $related_post->ID; ?><input type="hidden" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo esc_attr( $related_post->ID ); ?>" /></li><?php 

	endforeach; endif;
}


/**
 * Change label for insert buttons
 */
add_filter( 'gettext', 'cmdeals_change_insert_into_post', null, 2 );

function cmdeals_change_insert_into_post( $translation, $original ) {
    if( !isset( $_REQUEST['from'] ) ) return $translation;

    if( $_REQUEST['from'] == 'wd01' && $original == 'Insert into Post' ) return __('Insert into URL field', 'cmdeals' );

    return $translation;
}

/* 
 * change the enctype form
 */
add_action('post_edit_form_tag', 'post_edit_form_tag_');
function post_edit_form_tag_() {
    echo ' enctype="multipart/form-data"';
}