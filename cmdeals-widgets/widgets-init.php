<?php
/**
 * Widgets init
 * 
 * Init the widgets.
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

include_once('widget-featured-deals.php');
include_once('widget-deals-categories.php');
include_once('widget-deals-search.php');
include_once('widget-deals-tags-cloud.php');
include_once('widget-recent-deals.php');
include_once('widget-login.php');

function cmdeals_register_widgets() {
	register_widget('CMDeals_Widget_Recent_Deals');
	register_widget('CMDeals_Widget_Featured_Deals');
	register_widget('CMDeals_Widget_Deal_Categories');
	register_widget('CMDeals_Widget_Deal_Tag_Cloud');
	register_widget('CMDeals_Widget_Deal_Search');
	register_widget('CMDeals_Widget_Login');
}
add_action('widgets_init', 'cmdeals_register_widgets');