<?php
/**
 * Recent Deals Widget
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

class CMDeals_Widget_Recent_Deals extends WP_Widget {

	/** Variables to setup the widget. */
	var $cmdeals_widget_cssclass;
	var $cmdeals_widget_description;
	var $cmdeals_widget_idbase;
	var $cmdeals_widget_name;
	
	/** constructor */
	function CMDeals_Widget_Recent_Deals() {
		
		/* Widget variable settings. */
		$this->cmdeals_widget_cssclass = 'widget_recent_deals';
		$this->cmdeals_widget_description = __( 'Display a list of your most recent deals on your site.', 'cmdeals' );
		$this->cmdeals_widget_idbase = 'cmdeals_recent_deals';
		$this->cmdeals_widget_name = __('CMDeals Recent Deals', 'cmdeals' );
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->cmdeals_widget_cssclass, 'description' => $this->cmdeals_widget_description );
		
		/* Create the widget. */
		$this->WP_Widget('recent_deals', $this->cmdeals_widget_name, $widget_ops);

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	/** @see WP_Widget */
	function widget($args, $instance) {
		global $cmdeals;
		
		$cache = wp_cache_get('widget_recent_deals', 'widget');

		if ( !is_array($cache) ) $cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);
		
		$title = apply_filters('widget_title', empty($instance['title']) ? __('New Deals', 'cmdeals') : $instance['title'], $instance, $this->id_base);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

                $show_variations = $instance['show_variations'] ? '1' : '0';

                $query_args = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'post_type' => 'daily-deals', 'meta_query' => array('key' => '_is_expired', 'value' => 'no') );

		$r = new WP_Query($query_args);
		
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul class="deal_list_widget">
		<?php  while ($r->have_posts()) : $r->the_post(); global $post; $_deals = new cmdeals_deals(get_the_ID()); ?>
		<li>
                <a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>">
			<?php if (has_post_thumbnail()) the_post_thumbnail('store_catalog'); else echo '<img src="'.$cmdeals->plugin_url().'/cmdeals-assets/images/placeholder.png" alt="Placeholder" width="'.$cmdeals->get_image_size('store_thumbnail_image_width').'" height="'.$cmdeals->get_image_size('store_thumbnail_image_height').'" />'; ?>
			<?php if ( get_the_title() ) the_title(); else the_ID(); ?>
		</a> <?php echo $_deals->get_price_html(); ?>
                <?php cmdeals_template_loop_countdown($post, $_deals); ?>
                </li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		//wp_reset_postdata();

		endif;

		if (isset($args['widget_id']) && isset($cache[$args['widget_id']])) $cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_deals', $cache, 'widget');
	}

	/** @see WP_Widget->update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];		
		$instance['show_variations'] = !empty($new_instance['show_variations']) ? 1 : 0;

		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_deals']) ) delete_option('widget_recent_deals');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_deals', 'widget');
	}

	/** @see WP_Widget->form */
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		if ( !isset($instance['number']) || !$number = (int) $instance['number'] )
			$number = 5;

		$show_variations = isset( $instance['show_variations'] ) ? (bool) $instance['show_variations'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cmdeals'); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of deals to show:', 'cmdeals'); ?></label>
		<input id="<?php echo esc_attr( $this->get_field_id('number') ); ?>" name="<?php echo esc_attr( $this->get_field_name('number') ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" size="3" /></p>

    <p><input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('show_variations') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_variations') ); ?>"<?php checked( $show_variations ); ?> />
		<label for="<?php echo $this->get_field_id('show_variations'); ?>"><?php _e( 'Show hidden deals variations', 'cmdeals' ); ?></label><br />

<?php
	}
} // class CMDeals_Widget_Recent_Deals
