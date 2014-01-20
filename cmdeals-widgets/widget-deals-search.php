<?php
/**
 * Deal Search Widget
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

class CMDeals_Widget_Deal_Search extends WP_Widget {

	/** Variables to setup the widget. */
	var $cmdeals_widget_cssclass;
	var $cmdeals_widget_description;
	var $cmdeals_widget_idbase;
	var $cmdeals_widget_name;
	
	/** constructor */
	function CMDeals_Widget_Deal_Search() {
	
		/* Widget variable settings. */
		$this->cmdeals_widget_cssclass = 'widget_deals_search';
		$this->cmdeals_widget_description = __( 'A Search box for deals only.', 'cmdeals' );
		$this->cmdeals_widget_idbase = 'cmdeals_deals_search';
		$this->cmdeals_widget_name = __('CMDeals Deal Search', 'cmdeals' );
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->cmdeals_widget_cssclass, 'description' => $this->cmdeals_widget_description );
		
		/* Create the widget. */
		$this->WP_Widget('deal_search', $this->cmdeals_widget_name, $widget_ops);
	}

	/** @see WP_Widget */
	function widget( $args, $instance ) {
		extract($args);

		$title = $instance['title'];
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		
		echo $before_widget;
		
		if ($title) echo $before_title . $title . $after_title;
		
		?>
		<form role="search" method="get" id="searchform" action="<?php echo esc_url( home_url() ); ?>">
			<div>
				<label class="screen-reader-text" for="s"><?php _e('Search for:', 'cmdeals'); ?></label>
				<input type="text" value="<?php the_search_query(); ?>" name="s" id="s" placeholder="<?php _e('Search for deals', 'cmdeals'); ?>" />
				<input type="submit" id="searchsubmit" value="<?php _e('Search', 'cmdeals'); ?>" />
				<input type="hidden" name="post_type" value="daily-deals" />
			</div>
		</form>
		<?php
		
		echo $after_widget;
	}

	/** @see WP_Widget->update */
	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		return $instance;
	}

	/** @see WP_Widget->form */
	function form( $instance ) {
		global $wpdb;
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cmdeals') ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php if (isset ( $instance['title'])) {echo esc_attr( $instance['title'] );} ?>" /></p>
		<?php
	}
} // CMDeals_Widget_Deal_Search