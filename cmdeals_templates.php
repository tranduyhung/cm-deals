<?php
/**
 * CMDeals Templates
 * 
 * Handles template usage so that we can use our own templates instead of the theme's.
 *
 * Templates are in the 'templates' folder. cmdeals looks for theme 
 * overides in /theme/cmdeals/ by default  but this can be overwritten with WPDEALS_TEMPLATE_URL
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

function cmdeals_template_loader( $template ) {
	global $cmdeals;
	
	if ( is_single() && get_post_type() == 'daily-deals' ) {
		
		$template = locate_template( array( 'single-daily-deals.php', WPDEALS_TEMPLATE_URL . 'single-daily-deals.php' ) );
		
		if ( ! $template ) $template = $cmdeals->plugin_path() . '/cmdeals-templates/single-daily-deals.php';
		
	}
	elseif ( is_tax('deal-categories') ) {
		
		$template = locate_template(  array( 'taxonomy-deal-categories.php', WPDEALS_TEMPLATE_URL . 'taxonomy-deal-categories.php' ) );
		
		if ( ! $template ) $template = $cmdeals->plugin_path() . '/cmdeals-templates/taxonomy-deal-categories.php';
	}
	elseif ( is_tax('deal-tags') ) {
		
		$template = locate_template( array( 'taxonomy-deal-tags.php', WPDEALS_TEMPLATE_URL . 'taxonomy-deal-tags.php' ) );
		
		if ( ! $template ) $template = $cmdeals->plugin_path() . '/cmdeals-templates/taxonomy-deal-tags.php';
	}
	elseif ( is_post_type_archive('daily-deals') ||  is_page( get_option('cmdeals_store_page_id') )) {

		$template = locate_template( array( 'archive-daily-deals.php', WPDEALS_TEMPLATE_URL . 'archive-daily-deals.php' ) );
		
		if ( ! $template ) $template = $cmdeals->plugin_path() . '/cmdeals-templates/archive-daily-deals.php';
		
	}
        elseif ( is_page( get_option('cmdeals_featured_page_id') ) ) {
		
		$template = locate_template( array( 'featured-store.php', WPDEALS_TEMPLATE_URL . 'featured-store.php' ) );
		
		if ( ! $template ) $template = $cmdeals->plugin_path() . '/cmdeals-templates/featured-store.php';
                
		
	}
	
	return $template;

}
add_filter( 'template_include', 'cmdeals_template_loader' );

/**
 * Get template part (for templates like loop)
 */
function cmdeals_get_template_part( $slug, $name = '' ) {
	global $cmdeals, $post;
	if ($name=='store') :
		if (!locate_template(array( $slug.'-store.php', WPDEALS_TEMPLATE_URL . $slug.'-store.php' ))) :
			load_template( $cmdeals->plugin_path() . '/cmdeals-templates/'.$slug.'-store.php',false );
			return;
		endif;
	endif;
	get_template_part( WPDEALS_TEMPLATE_URL . $slug, $name );
}

/**
 * Get the reviews template (comments)
 */
function cmdeals_comments_template($template) {
	global $cmdeals;
		
	if(get_post_type() !== 'daily-deals') return $template;
	
	if (file_exists( STYLESHEETPATH . '/' . WPDEALS_TEMPLATE_URL . 'single-daily-deals-reviews.php' ))
		return STYLESHEETPATH . '/' . WPDEALS_TEMPLATE_URL . 'single-daily-deals-reviews.php'; 
	else
		return $cmdeals->plugin_path() . '/deals-templates/single-daily-deals-reviews.php';
}

add_filter('comments_template', 'cmdeals_comments_template' );


/**
 * Get other templates (e.g. deals attributes)
 */
function cmdeals_get_template($template_name, $require_once = true) {
	global $cmdeals;
	if (file_exists( STYLESHEETPATH . '/' . WPDEALS_TEMPLATE_URL . $template_name )) load_template( STYLESHEETPATH . '/' . WPDEALS_TEMPLATE_URL . $template_name, $require_once ); 
	elseif (file_exists( STYLESHEETPATH . '/' . $template_name )) load_template( STYLESHEETPATH . '/' . $template_name , $require_once); 
	else load_template( $cmdeals->plugin_path() . '/cmdeals-templates/' . $template_name , $require_once);
}


/**
 * Front page archive/store template applied to main loop
 */
if (!function_exists('cmdeals_front_page_archive')) {
	function cmdeals_front_page_archive( $query ) {
			
		global $paged, $cmdeals, $wp_the_query, $wp_query;
		
		if ( defined('SHOP_IS_ON_FRONT') ) :
		
			wp_reset_query();
			
			// Only apply to front_page
			if ( $query === $wp_the_query ) :
				
				if (get_query_var('paged')) :
					$paged = get_query_var('paged'); 
				else :
					$paged = (get_query_var('page')) ? get_query_var('page') : 1;
				endif;
	
				// Filter the query
				add_filter( 'parse_query', array( &$cmdeals->query, 'parse_query') );
				
				// Query the deals
				$wp_query->query( array( 'page_id' => '', 'p' => '', 'post_type' => 'daily-deals', 'paged' => $paged ) );
				
				// get deals in view (for use by widgets)
				$cmdeals->query->get_deals_in_view();
				
				// Remove the query manipulation
				remove_filter( 'parse_query', array( &$cmdeals->query, 'parse_query') ); 
				remove_action('loop_start', 'cmdeals_front_page_archive', 1);
	
			endif;
		
		endif;
	}
}
add_action('loop_start', 'cmdeals_front_page_archive', 1);

/**
 * Detect frontpage store and fix pagination on static front page
 **/
function cmdeals_front_page_archive_paging_fix() {
		
	if ( is_front_page() && is_page( get_option('cmdeals_store_page_id') )) :
		
		if (get_query_var('paged')) :
			$paged = get_query_var('paged'); 
		else :
			$paged = (get_query_var('page')) ? get_query_var('page') : 1;
		endif;
			
		query_posts( array( 'page_id' => get_option('cmdeals_store_page_id'), 'is_paged' => true, 'paged' => $paged ) );
		
		define('SHOP_IS_ON_FRONT', true);
		
	endif;
}
add_action('wp', 'cmdeals_front_page_archive_paging_fix', 1);

/**
 * Detect frontpage store and fix pagination on static front page
 **/
function cmdeals_front_page_featured_paging_fix() {
		
	if ( is_front_page() && is_page( get_option('cmdeals_featured_page_id') )) :
		
		query_posts( array( 'page_id' => get_option('cmdeals_featured_page_id'), 'is_paged' => true ) );
		
		define('FEATURED_IS_ON_FRONT', true);
		
	endif;
}
add_action('wp', 'cmdeals_front_page_archive_paging_fix', 1);

/**
 * Add Body classes based on page/template
 **/
global $cmdeals_body_classes;

function cmdeals_page_body_classes() {
	
	global $cmdeals_body_classes;
	
	$cmdeals_body_classes = (array) $cmdeals_body_classes;
	
	if (is_cmdeals()) $cmdeals_body_classes[] = 'cmdeals';
	
	if (is_checkout()) $cmdeals_body_classes[] = 'cmdeals-checkout';
	
	if (is_account_page()) $cmdeals_body_classes[] = 'cmdeals-account';
	
	if (is_cmdeals() || is_checkout() || is_account_page() || get_page(get_option('cmdeals_order_tracking_page_id')) || get_page(get_option('cmdeals_thanks_page_id'))) $cmdeals_body_classes[] = 'cmdeals-page';
	
}
add_action('wp_head', 'cmdeals_page_body_classes');

function cmdeals_body_class($classes) {
	
	global $cmdeals_body_classes;
	
	$cmdeals_body_classes = (array) $cmdeals_body_classes;
	
	$classes = array_merge($classes, $cmdeals_body_classes);
	
	return $classes;
}
add_filter('body_class','cmdeals_body_class');

/**
 * Fix active class in nav for store page
 **/
function cmdeals_nav_menu_item_classes( $menu_items, $args ) {
	
	if (!is_cmdeals()) return $menu_items;
	
	$store_page 		= (int) get_option('cmdeals_store_page_id');
	$page_for_posts = (int) get_option( 'page_for_posts' );

	foreach ( (array) $menu_items as $key => $menu_item ) :

		$classes = (array) $menu_item->classes;

		// Unset active class for blog page
		if ( $page_for_posts == $menu_item->object_id ) :
			$menu_items[$key]->current = false;
			unset( $classes[ array_search('current_page_parent', $classes) ] );
			unset( $classes[ array_search('current-menu-item', $classes) ] );

		// Set active state if this is the store page link
		elseif ( is_store() && $store_page == $menu_item->object_id ) :
			$menu_items[$key]->current = true;
			$classes[] = 'current-menu-item';
			$classes[] = 'current_page_item';
		
		endif;

		$menu_items[$key]->classes = array_unique( $classes );
	
	endforeach;

	return $menu_items;
}
add_filter( 'wp_nav_menu_objects',  'cmdeals_nav_menu_item_classes', 2, 20 );

/**
 * Fix active class in wp_list_pages for store page
 *
 * Suggested by jessor - https://github.com/cmdeals/cmdeals/issues/177
 **/
function cmdeals_list_pages($pages){
    global $post;

    if (is_cmdeals() || is_checkout() || is_page(get_option('cmdeals_thanks_page_id'))) {
        $pages = str_replace( 'current_page_parent', '', $pages); // remove current_page_parent class from any item
        $store_page = 'page-item-' . get_option('cmdeals_store_page_id'); // find store_page_id through cmdeals options
        
        if (is_store()) :
        	$pages = str_replace($store_page, $store_page . ' current_page_item', $pages); // add current_page_item class to store page
    	else :
    		$pages = str_replace($store_page, $store_page . ' current_page_parent', $pages); // add current_page_parent class to store page
    	endif;
    }
    return $pages;
}

add_filter('wp_list_pages', 'cmdeals_list_pages');




/**
 * Filter the products in admin based on options
 *
 * @access public
 * @param mixed $query
 * @return void
 */
function cmdeals_featured_deals_query( $q ) {
    global $typenow, $wp_query;

    if ( is_page( get_option('cmdeals_featured_page_id') ) ) {
        
        // Ordering query vars
        $q->set( 'orderby', 'rand' );

        // Query vars that affect posts shown
        $q->set( 'post_type', 'daily-deals' );
        $q->set( 'ignore_sticky_posts', 1 );
        $q->set( 'posts_per_page', 1 );
        $q->set( 'meta_query', array(            
                array(
                        'key' => 'featured',
                        'value' => 'yes',
                        'compare' 	=> '='
                ),
                array(
                        'key' => '_is_expired',
                        'value' => 'no',
                        'compare' 	=> '='
                )
        ) );

    }

}


/**
 * Featured page template applied
 */
function cmdeals_featured_page( $query ) {

        global $cmdeals, $wp_the_query, $wp_query;

        if ( is_page( get_option('cmdeals_featured_page_id') ) || defined('FEATURED_IS_ON_FRONT') ) :

                wp_reset_query();

                // Only apply to front_page
                if ( $query === $wp_the_query ) :

                        // Filter the query
                        add_filter( 'parse_query', 'cmdeals_featured_deals_query' );

                        // Query the deals
                        $wp_query->query( array( 
                            'meta_query' => array(            
                                    array(
                                            'key' => 'featured',
                                            'value' => 'yes',
                                            'compare' 	=> '='
                                    ),
                                    array(
                                            'key' => '_is_expired',
                                            'value' => 'no',
                                            'compare' 	=> '='
                                    )
                            ),
                            'posts_per_page' => 1, 
                            'orderby' => 'rand',
                            'page_id' => '', 
                            'p' => '', 
                            'post_type' => 'daily-deals' ) );

                        // Remove the query manipulation
                        remove_filter( 'parse_query', 'cmdeals_featured_deals_query' ); 
                        remove_action('loop_start', 'cmdeals_featured_page', 1);

                endif;


        endif;
}
add_action('loop_start', 'cmdeals_featured_page', 1);