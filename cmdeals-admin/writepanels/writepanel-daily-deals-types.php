<?php
/**
 * Deal Type
 * 
 * Function for displaying the deals type meta (specific) meta boxes
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

include_once('writepanel-daily-deals-type-downloadable.php');
include_once('writepanel-daily-deals-type-variable.php');

/**
 * Deal type meta box
 * 
 * Display the deals type meta box which contains a hook for deals types to hook into and show their options
 *
 * @since 		1.0
 */
function cmdeals_deals_type_options_box() {

	global $post;
	?>
	<div id="simple_deals_options" class="panel cmdeals_options_panel">
		<?php
			_e('Simple deals have no specific options.', 'cmdeals');
		?>
	</div>
	<?php 
	do_action('cmdeals_deals_type_options_box');
}

/**
 * Virtual Deal Type - Deal Options
 * 
 * Deal Options for the virtual deals type
 */
function virtual_deals_type_options() {
	?>
	<div id="virtual_deals_options">
		<?php
			_e('Virtual deals have no specific options.', 'cmdeals');
		?>
	</div>
	<?php
}
add_action('cmdeals_deals_type_options_box', 'virtual_deals_type_options');

/**
 * Grouped Deal Type - Deal Options
 * 
 * Deal Options for the grouped deals type
 *
 * @since 		1.0
 */
function grouped_deals_type_options() {
	?>
	<div id="grouped_deals_options">
		<?php
			_e('Grouped deals have no specific options &mdash; you can add simple deals to this grouped deals by editing them and setting their <code>parent deals</code> option.', 'cmdeals');
		?>
	</div>
	<?php
}
add_action('cmdeals_deals_type_options_box', 'grouped_deals_type_options');


/**
 * Deal Type selectors
 * 
 * Adds a deals type to the deals type selector in the deals options meta box
 */
add_filter('deal_type_selector', 'virtual_deals_type_selector', 1, 2);
add_filter('deal_type_selector', 'grouped_deals_type_selector', 1, 2);

function virtual_deals_type_selector( $types, $deal_type ) {
	$types['virtual'] = __('Virtual', 'cmdeals');
	return $types;
}

function grouped_deals_type_selector( $types, $deal_type ) {
	$types['grouped'] = __('Grouped', 'cmdeals');
	return $types;
}
