<?php
/**
 * Contains the query functions for CMDeals which alter the front-end post queries and loops.
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

class cmdeals_query {
	
	var $unfiltered_deals_ids 	= array(); 	// Unfiltered deals ids (before layered nav etc)
	var $filtered_deals_ids 		= array(); 	// Filtered deals ids (after layered nav)
	var $post__in 					= array(); 	// Deal id's that match the layered nav + price filter
	var $meta_query 				= ''; 		// The meta query for the page
	var $layered_nav_post__in 		= array(); 	// posts matching layered nav only
	var $layered_nav_deals_ids 	= array();	// Stores posts matching layered nav, so price filter can find max price in view
	
	/** constructor */
	function __construct() {
		add_filter( 'parse_query',  array( &$this, 'parse_query') );
		add_action( 'wp',           array( &$this, 'remove_parse_query') );
	}
	
	/**
	 * Query the deals, applying sorting/ordering etc. This applies to the main wordpress loop
	 */
	function parse_query( $q ) {

                if (is_admin()) return;

                    // Only apply to deals categories, the deals post archive, the store page, and deals tags
                if 	( 
                            (
                                    isset( $q->query_vars['suppress_filters'] ) 
                                    && true == $q->query_vars['suppress_filters']
                            ) || (  
                                    !$q->is_tax( 'deal-category' ) 
                                    && !$q->is_tax( 'deal-tags' ) 
                                    && !$q->is_post_type_archive( 'daily-deals' ) 
                                    // this rule should be covered by the above && !$q->is_page(get_option('cmdeals_store_page_id')) 
                            )
                    ) 
                return;

                // Meta query
                $meta_query = (array) $q->get( 'meta_query' );
                $meta_query[] = $this->stock_status_meta_query();

                // Ordering
                $ordering = $this->get_catalog_ordering_args();

                // Get a list of post id's which match the current filters set (in the layered nav and price filter)
                $post__in = array_unique(apply_filters('loop_store_post_in', array()));

                // Ordering query vars
                $q->set( 'orderby', $ordering['orderby'] );
                $q->set( 'order', $ordering['order'] );
                if (isset($ordering['meta_key'])) $q->set( 'meta_key', $ordering['meta_key'] );
                if (isset($ordering['meta_value'])) $q->set( 'meta_value', $ordering['meta_value'] );

                // Query vars that affect posts shown
                $q->set( 'post_type', 'daily-deals' );
                $q->set( 'meta_query', $meta_query );
                $q->set( 'post__in', $post__in );
                $q->set( 'posts_per_page', apply_filters('loop_store_per_page', get_option('posts_per_page')) );

                // Store variables
                $this->post__in = $post__in;
                $this->meta_query = $meta_query;

                // We're on a store page so queue the cmdeals_get_deals_in_view function
                add_action('wp', array( &$this, 'get_deals_in_view' ), 2);
	}
	
	/**
	 * Remove parse_query so it only applies to main loop
	 */
	function remove_parse_query() {
		remove_filter( 'parse_query', array( &$this, 'parse_query') ); 
	}
	
	/**
	 * Get an unpaginated list all deals ID's (both filtered and unfiltered). Makes use of transients.
	 */
	function get_deals_in_view() {
		global $wp_query;
		
		$unfiltered_deals_ids = array();
		
		// Get WP Query for current page (without 'paged')
		$current_wp_query = $wp_query->query;
		unset($current_wp_query['paged']);
		
		// Generate a transient name based on current query
		$transient_name = 'cmdeals_unfiltered_deals_ids_' . sanitize_key( http_build_query($current_wp_query) );
		$transient_name = (is_search()) ? $transient_name . '_search' : $transient_name;
		
		if ( false === ( $unfiltered_deals_ids = get_transient( $transient_name ) ) ) {

			// Get all visible posts, regardless of filters
		    $unfiltered_deals_ids = get_posts(
				array_merge( 
					$current_wp_query,
					array(
						'post_type' 	=> 'daily-deals',
						'numberposts' 	=> -1,
						'post_status' 	=> 'publish',
						'meta_query' 	=> $this->meta_query,
						'fields' 	=> 'ids',
						'no_found_rows' => true
					)
				)
			);
		
			set_transient( $transient_name, $unfiltered_deals_ids );
		}
		
		// Store the variable
		$this->unfiltered_deals_ids = $unfiltered_deals_ids;
		
		// Also store filtered posts ids...
		if (sizeof($this->post__in)>0) :
			$this->filtered_deals_ids = array_intersect($this->unfiltered_deals_ids, $this->post__in);
		else :
			$this->filtered_deals_ids = $this->unfiltered_deals_ids;
		endif;
		
		// And filtered post ids which just take layered nav into consideration (to find max price in the price widget)
		if (sizeof($this->layered_nav_post__in)>0) :
			$this->layered_nav_deals_ids = array_intersect($this->unfiltered_deals_ids, $this->layered_nav_post__in);
		else :
			$this->layered_nav_deals_ids = $this->unfiltered_deals_ids;
		endif;
	}
	
	/**
	 * Returns an array of arguments for ordering deals based on the selected values
	 */
	function get_catalog_ordering_args() {
		$current_order = (isset($_SESSION['orderby'])) ? $_SESSION['orderby'] : apply_filters('cmdeals_default_catalog_orderby', 'title');
		
		switch ($current_order) :
			case 'recent' :
				$orderby = 'meta_value_num date';
				$order = 'desc';
				$meta_key = '_is_expired';
				$meta_value = 'no';
			break;
			case 'past' :
				$orderby = 'meta_value_num date';
				$order = 'desc';
				$meta_key = '_is_expired';
				$meta_value = 'yes';
			break;
			default :
				$orderby = 'date';
				$order = 'desc';
				$meta_key = '';
				$meta_value = '';
			break;
		endswitch;
		
		$args = array();
		
		$args['orderby'] = $orderby;
		$args['order'] = $order;
		if ($meta_key) $args['meta_key'] = $meta_key;
		if ($meta_value) $args['meta_value'] = $meta_value;
		
		return apply_filters('cmdeals_get_catalog_ordering_args', $args);
	}
	
	/**
	 * Returns a meta query to handle deals stock status
	 */
	function stock_status_meta_query( $status = 'instock' ) {
		$meta_query = array();
		if (get_option('cmdeals_hide_out_of_stock_items')=='yes') :
			 $meta_query = array(
		        'key' 		=> 'stock_status',
                        'value' 	=> $status,
                        'compare' 	=> '='
		    );
		endif;
		return $meta_query;
	}
	
	/**
	 * Get a list of deals id's which should be hidden from the frontend; useful for custom queries and loops. Makes use of transients.
	 */
	function get_hidden_deals_ids() {
		
		$transient_name = (is_search()) ? 'cmdeals_hidden_from_search_deals_ids' : 'cmdeals_hidden_deals_ids';
		
		if ( false === ( $hidden_deals_ids = get_transient( $transient_name ) ) ) {
			
			$meta_query = array();
			
			$hidden_deals_ids = get_posts(array(
				'post_type' 	=> 'daily-deals',
				'numberposts' 	=> -1,
				'post_status' 	=> 'publish',
				'meta_query' 	=> $meta_query,
				'fields' 		=> 'ids',
				'no_found_rows' => true
			));
			
			set_transient( $transient_name, $hidden_deals_ids );
		}
                
		return (array) $hidden_deals_ids;
	}	
 
}