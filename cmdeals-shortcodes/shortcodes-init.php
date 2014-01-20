<?php
/**
 * Shortcodes init
 * 
 * Init main shortcodes, and add a few others such as recent deals.
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

include_once('shortcode-checkout.php');
include_once('shortcode-my_account.php');
include_once('shortcode-pay.php');
include_once('shortcode-thankyou.php');

/**
 * List deals in a category shortcode
 **/
function cmdeals_deals_category($atts){
	global $cmdeals_loop;
	
  	if (empty($atts)) return;
  
	extract(shortcode_atts(array(
		'per_page' 		=> '12',
		'columns' 		=> '3',
	  	'orderby'               => 'title',
	  	'order'                 => 'asc',
	  	'category'		=> ''
		), $atts));
		
	if (!$category) return;
		
	$cmdeals_loop['columns'] = $columns;
	
  	$args = array(
		'post_type'	=> 'daily-deals',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'orderby' => $orderby,
		'order' => $order,
		'posts_per_page' => $per_page,
		'tax_query' => array(
                        array(
                                'taxonomy' => 'deal-categories',
				'terms' => array( esc_attr($category) ),
				'field' => 'slug',
				'operator' => 'IN'
			)
	    )
	);
	
  	query_posts($args);
	
  	ob_start();
	cmdeals_get_template_part( 'loop', 'store' );
	wp_reset_query();
	return ob_get_clean();
}

/**
 * Recent Deals shortcode
 **/
function cmdeals_recent_deals( $atts ) {
	
	global $cmdeals_loop;
	
	extract(shortcode_atts(array(
		'per_page' 	=> '12',
		'columns' 	=> '4',
		'orderby' => 'date',
		'order' => 'desc'
	), $atts));
	
	$cmdeals_loop['columns'] = $columns;
	
	$args = array(
		'post_type'	=> 'daily-deals',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'posts_per_page' => $per_page,
		'orderby' => $orderby,
		'order' => $order
	);
	
	query_posts($args);
	ob_start();
	cmdeals_get_template_part( 'loop', 'store' );
	wp_reset_query();
	
	return ob_get_clean();
}

/**
 * List multiple deals shortcode
 **/
function cmdeals_deals($atts){
	global $cmdeals_loop;
	
  	if (empty($atts)) return;
  
	extract(shortcode_atts(array(
		'columns' 	=> '3',
	  	'orderby'   => 'title',
	  	'order'     => 'asc'
		), $atts));
		
	$cmdeals_loop['columns'] = $columns;
	
  	$args = array(
		'post_type'	=> 'daily-deals',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'orderby' => $orderby,
		'order' => $order
	);
		
	if(isset($atts['ids'])){
		$ids = explode(',', $atts['ids']);
	  	array_walk($ids, create_function('&$val', '$val = trim($val);'));
    	$args['post__in'] = $ids;
	}
	
  	query_posts($args);
	
  	ob_start();
	cmdeals_get_template_part( 'loop', 'store' );
	wp_reset_query();
	return ob_get_clean();
}

/**
 * Display a single prodcut
 **/
function cmdeals_deal($atts){
  	if (empty($atts)) return;
  
  	$args = array(
            'post_type' => 'daily-deals',
            'posts_per_page' => 1,
            'post_status' => 'publish'
  	);
  
  	if(isset($atts['id'])){
    	$args['p'] = $atts['id'];
  	}
  
  	query_posts($args);
	
  	ob_start();
	cmdeals_get_template_part( 'loop', 'store' );
	wp_reset_query();
	return ob_get_clean();  
}


/**
 * Display a single prodcut price + cart button
 **/
function cmdeals_deals_add_to_cart($atts){
  	if (empty($atts)) return;
  	
  	global $wpdb;
  	
  	if (!isset($atts['style'])) $atts['style'] = '';
  	
  	if ($atts['id']) :
  		$deal_data = get_post( $atts['id'] );
	else :
		return;
	endif;
	
	if ($deal_data->post_type!=='daily-deals') return;
	
	$_deals = new cmdeals_deals( $deal_data->ID ); 
		
	if (!$_deals->is_visible( true )) return; 
	
	ob_start();
	?>
	<div class="daily-deals" style="<?php echo $atts['style']; ?>">
		
		<?php cmdeals_template_single_add_to_cart( $deal_data, $_deals ); ?>
					
	</div><?php 
	
	return ob_get_clean();  
}


/**
 * Get the add to cart URL for a deals
 **/
function cmdeals_deals_add_to_cart_url( $atts ){
  	if (empty($atts)) return;
  	
  	global $wpdb;
  	  	
  	if ($atts['id']) :
  		$deal_data = get_post( $atts['id'] );
	else :
		return;
	endif;
	
	if ($deal_data->post_type!=='daily-deals') return;
	
	$_deals = new cmdeals_deals( $deal_data->ID ); 
		
	return esc_url( $_deals->add_to_cart_url() );
}


/**
 * Output featured deals
 **/
function cmdeals_featured_deals( $atts ) {
	
	global $cmdeals_loop;
	
	extract(shortcode_atts(array(
		'per_page' 	=> '12',
		'columns' 	=> '3',
		'orderby' => 'date',
		'order' => 'desc'
	), $atts));
	
	$cmdeals_loop['columns'] = $columns;
	
	$args = array(
		'post_type'	=> 'daily-deals',
		'post_status' => 'publish',
		'ignore_sticky_posts'	=> 1,
		'posts_per_page' => $per_page,
		'orderby' => $orderby,
		'order' => $order,
		'meta_query' => array(
			array(
				'key' => 'featured',
				'value' => 'yes'
			)
		)
	);
	query_posts($args);
	ob_start();
	cmdeals_get_template_part( 'loop', 'store' );
	wp_reset_query();
	
	return ob_get_clean();
}

/**
 * Shortcode creation
 **/
add_shortcode('deal_category', 'cmdeals_deals_category');
add_shortcode('add_to_cart', 'cmdeals_deals_add_to_cart');
add_shortcode('add_to_cart_url', 'cmdeals_deals_add_to_cart_url');
add_shortcode('daily-deals', 'cmdeals_deals');
add_shortcode('recent_deals', 'cmdeals_recent_deals');
add_shortcode('featured_deals', 'cmdeals_featured_deals');
add_shortcode('cmdeals_cart', 'get_cmdeals_cart');
add_shortcode('cmdeals_checkout', 'get_cmdeals_checkout');
add_shortcode('cmdeals_my_account', 'get_cmdeals_my_account');
add_shortcode('cmdeals_change_password', 'get_cmdeals_change_password');
add_shortcode('cmdeals_view_order', 'get_cmdeals_view_order');
add_shortcode('cmdeals_pay', 'get_cmdeals_pay');
add_shortcode('cmdeals_thankyou', 'get_cmdeals_thankyou');
