<?php
/**
 * Functions for outputting and updating settings pages
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
 * Update options
 * 
 * Updates the options on the cmdeals settings pages. Returns true if saved.
 */
function cmdeals_update_options($options) {
    
    if(!isset($_POST) || !$_POST) return false;
    
    foreach ($options as $value) {
        if (isset($value['type']) && $value['type']=='checkbox') :
            
            if(isset($value['id']) && isset($_POST[$value['id']])) {
            	update_option($value['id'], 'yes');
            } else {
                update_option($value['id'], 'no');
            }
            
        elseif (isset($value['type']) && $value['type']=='image_width') :
            	
            if(isset($value['id']) && isset($_POST[$value['id'].'_width'])) {
              	update_option($value['id'].'_width', cmdeals_clean($_POST[$value['id'].'_width']));
            	update_option($value['id'].'_height', cmdeals_clean($_POST[$value['id'].'_height']));
				if (isset($_POST[$value['id'].'_crop'])) :
					update_option($value['id'].'_crop', 1);
				else :
					update_option($value['id'].'_crop', 0);
				endif;
            } else {
                update_option($value['id'].'_width', $value['std']);
            	update_option($value['id'].'_height', $value['std']);
            	update_option($value['id'].'_crop', 1);
            }	
            	
    	else :
		    
            if(isset($value['id']) && isset($_POST[$value['id']])) {
            	update_option($value['id'], cmdeals_clean($_POST[$value['id']]));
            } else {
                delete_option($value['id']);
            }
        
        endif;
        
    }
    return true;
}

/**
 * Admin fields
 * 
 * Loops though the cmdeals options array and outputs each field.
 */
function cmdeals_admin_fields($options) {
	global $cmdeals;

    foreach ($options as $value) :
    	if (!isset( $value['name'] ) ) $value['name'] = '';
    	if (!isset( $value['class'] )) $value['class'] = '';
    	if (!isset( $value['css'] )) $value['css'] = '';
    	if (!isset( $value['std'] )) $value['std'] = '';
        switch($value['type']) :
            case 'title':
            	if (isset($value['name']) && $value['name']) echo '<h3>'.$value['name'].'</h3>'; 
            	if (isset($value['desc']) && $value['desc']) echo wpautop(wptexturize($value['desc']));
            	echo '<table class="form-table">'. "\n\n";
            	if (isset($value['id']) && $value['id']) do_action('cmdeals_settings_'.sanitize_title($value['id']));
            break;
            case 'sectionend':
            	if (isset($value['id']) && $value['id']) do_action('cmdeals_settings_'.sanitize_title($value['id']).'_end');
            	echo '</table>';
            	if (isset($value['id']) && $value['id']) do_action('cmdeals_settings_'.sanitize_title($value['id']).'_after');
            break;
            case 'text':
            	?><tr valign="top">
					<th scope="row" class="titledesc"><?php echo $value['name']; ?></th>
                    <td class="forminp"><input name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" type="<?php echo esc_attr( $value['type'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" value="<?php if ( get_option( $value['id'] ) !== false && get_option( $value['id'] ) !== null ) { echo esc_attr( stripslashes( get_option($value['id'] ) ) ); } else { echo esc_attr( $value['std'] ); } ?>" /> <span class="description"><?php echo $value['desc']; ?></span></td>
                </tr><?php
            break;
            case 'color' :
            	?><tr valign="top">
					<th scope="row" class="titledesc"><?php echo $value['name']; ?></th>
                    <td class="forminp"><input name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" type="text" style="<?php echo esc_attr( $value['css'] ); ?>" value="<?php if ( get_option( $value['id'] ) !== false && get_option( $value['id'] ) !== null ) { echo esc_attr( stripslashes( get_option($value['id'] ) ) ); } else { echo esc_attr( $value['std'] ); } ?>" class="colorpick" /> <span class="description"><?php echo $value['desc']; ?></span> <div id="colorPickerDiv_<?php echo esc_attr( $value['id'] ); ?>" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div></td>
                </tr><?php
            break;
            case 'image_width' :
            	?><tr valign="top">
					<th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
                    <td class="forminp">
                    	
                    	<?php _e('Width', 'cmdeals'); ?> <input name="<?php echo esc_attr( $value['id'] ); ?>_width" id="<?php echo esc_attr( $value['id'] ); ?>_width" type="text" size="3" value="<?php if ( $size = get_option( $value['id'].'_width') ) echo stripslashes($size); else echo $value['std']; ?>" /> 
                    	
                    	<?php _e('Height', 'cmdeals'); ?> <input name="<?php echo esc_attr( $value['id'] ); ?>_height" id="<?php echo esc_attr( $value['id'] ); ?>_height" type="text" size="3" value="<?php if ( $size = get_option( $value['id'].'_height') ) echo stripslashes($size); else echo $value['std']; ?>" /> 
                    	
                    	<label><?php _e('Hard Crop', 'cmdeals'); ?> <input name="<?php echo esc_attr( $value['id'] ); ?>_crop" id="<?php echo esc_attr( $value['id'] ); ?>_crop" type="checkbox" <?php if (get_option( $value['id'].'_crop')!='') checked(get_option( $value['id'].'_crop'), 1); else checked(1); ?> /></label> 
                    	
                    	<span class="description"><?php echo $value['desc'] ?></span></td>
                </tr><?php
            break;
            case 'select':
            	?><tr valign="top">
					<th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
                    <td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" class="<?php if (isset($value['class'])) echo $value['class']; ?>">
                        <?php
                        foreach ($value['options'] as $key => $val) {
                        ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php if (get_option($value['id']) == $key) { ?> selected="selected" <?php } ?>><?php echo ucfirst($val) ?></option>
                        <?php
                        }
                        ?>
                       </select> <span class="description"><?php echo $value['desc'] ?></span>
                    </td>
                </tr><?php
            break;
            case 'checkbox' :
            
            	if (!isset($value['checkboxgroup']) || (isset($value['checkboxgroup']) && $value['checkboxgroup']=='start')) :
            		?>
            		<tr valign="top">
					<th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
					<td class="forminp">
					<?php
            	endif;
            	
            	?>
	            <fieldset><legend class="screen-reader-text"><span><?php echo $value['name'] ?></span></legend>
					<label for="<?php echo $value['id'] ?>">
					<input name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" type="checkbox" value="1" <?php checked(get_option($value['id']), 'yes'); ?> />
					<?php echo $value['desc'] ?></label><br>
				</fieldset>
				<?php
				
				if (!isset($value['checkboxgroup']) || (isset($value['checkboxgroup']) && $value['checkboxgroup']=='end')) :
					?>
						</td>
					</tr>
					<?php
				endif;
				
            break;
            case 'textarea':
            	?><tr valign="top">
					<th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
                    <td class="forminp">
                        <textarea <?php if ( isset($value['args']) ) echo $value['args'] . ' '; ?>name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>"><?php if (get_option($value['id'])) echo esc_textarea(stripslashes(get_option($value['id']))); else echo esc_textarea( $value['std'] ); ?></textarea> <span class="description"><?php echo $value['desc'] ?></span>
                    </td>
                </tr><?php
            break;
            case 'single_select_page' :
            	$page_setting = (int) get_option($value['id']);
            	
            	$args = array( 'name'				=> $value['id'],
            				   'id'					=> $value['id'],
            				   'sort_column' 		=> 'menu_order',
            				   'sort_order'			=> 'ASC',
            				   'show_option_none' 	=> ' ',
            				   'class'				=> $value['class'],
            				   'echo' 				=> false,
            				   'selected'			=> $page_setting);
            	
            	if( isset($value['args']) ) $args = wp_parse_args($value['args'], $args);
            	
            	?><tr valign="top" class="single_select_page">
                    <th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
                    <td class="forminp">
			        	<?php echo str_replace(' id=', " data-placeholder='".__('Select a page...', 'cmdeals')."' style='".$value['css']."' class='".$value['class']."' id=", wp_dropdown_pages($args)); ?> <span class="description"><?php echo $value['desc'] ?></span>        
			        </td>
               	</tr><?php	
            break;
            case 'single_select_country' :
            	$countries = $cmdeals->countries->countries;
            	$country_setting = (string) get_option($value['id']);
            	if (strstr($country_setting, ':')) :
            		$country = current(explode(':', $country_setting));
            		$state = end(explode(':', $country_setting));
            	else :
            		$country = $country_setting;
            		$state = '*';
            	endif;
            	?><tr valign="top">
                    <th scope="rpw" class="titledesc"><?php echo $value['name'] ?></th>
                    <td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php _e('Choose a country&hellip;', 'cmdeals'); ?>" title="Country" class="chosen_select">	
			        	<?php echo $cmdeals->countries->country_dropdown_options($country, $state); ?>          
			        </select> <span class="description"><?php echo $value['desc'] ?></span>
               		</td>
               	</tr><?php	
            break;
            case 'multi_select_countries' :
            	$countries = $cmdeals->countries->countries;
            	asort($countries);
            	$selections = (array) get_option($value['id']);
            	?><tr valign="top">
					<th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
                    <td class="forminp">
	                    <select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="width:450px;" data-placeholder="<?php _e('Choose countries&hellip;', 'cmdeals'); ?>" title="Country" class="chosen_select">	
				        	<?php
				        		if ($countries) foreach ($countries as $key=>$val) :
	                    			echo '<option value="'.$key.'" '.selected( in_array($key, $selections), true, false ).'>'.$val.'</option>';   			
	                    		endforeach;   
	                    	?>     
				        </select>
               		</td>
               	</tr><?php		            	
            break;
        endswitch;
    endforeach;
}
