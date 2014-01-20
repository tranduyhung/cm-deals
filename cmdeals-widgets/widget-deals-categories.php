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

class CMDeals_Widget_Deal_Categories extends WP_Widget {

	/** Variables to setup the widget. */
	var $cmdeals_widget_cssclass;
	var $cmdeals_widget_description;
	var $cmdeals_widget_idbase;
	var $cmdeals_widget_name;
	
	/** constructor */
	function CMDeals_Widget_Deal_Categories() {
	
		/* Widget variable settings. */
		$this->cmdeals_widget_cssclass = 'widget_deal_categories';
		$this->cmdeals_widget_description = __( 'A list or dropdown of deals categories.', 'cmdeals' );
		$this->cmdeals_widget_idbase = 'cmdeals_deal_categories';
		$this->cmdeals_widget_name = __('CMDeals Deal Categories', 'cmdeals' );
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->cmdeals_widget_cssclass, 'description' => $this->cmdeals_widget_description );
		
		/* Create the widget. */
		$this->WP_Widget('deal-categories', $this->cmdeals_widget_name, $widget_ops);
	}

	/** @see WP_Widget */
	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Deal Categories', 'cmdeals' ) : $instance['title'], $instance, $this->id_base);
		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('menu_order' => 'asc', 'show_count' => $c, 'hierarchical' => $h, 'taxonomy' => 'deal-categories');

		if ( $d ) {

			// Stuck with this until a fix for http://core.trac.wordpress.org/ticket/13258
			cmdeals_deals_dropdown_categories( $c, $h );
			
			?>
			<script type='text/javascript'>
			/* <![CDATA[ */
				var dropdown = document.getElementById("dropdown_deal_category");
				function onCatChange() {
					if ( dropdown.options[dropdown.selectedIndex].value !=='' ) {
						location.href = "<?php echo home_url(); ?>/?deal-categories="+dropdown.options[dropdown.selectedIndex].value;
					}
				}
				dropdown.onchange = onCatChange;
			/* ]]> */
			</script>
			<?php
			
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';
		wp_list_categories(apply_filters('cmdeals_deal_categories_widget_args', $cat_args));
?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	/** @see WP_Widget->update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}
	
	/** @see WP_Widget->form */
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'cmdeals' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('dropdown') ); ?>" name="<?php echo esc_attr( $this->get_field_name('dropdown') ); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Show as dropdown', 'cmdeals' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('count') ); ?>" name="<?php echo esc_attr( $this->get_field_name('count') ); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts', 'cmdeals' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('hierarchical') ); ?>" name="<?php echo esc_attr( $this->get_field_name('hierarchical') ); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy', 'cmdeals' ); ?></label></p>
<?php
	}

} // class CMDeals_Widget_Deal_Categories