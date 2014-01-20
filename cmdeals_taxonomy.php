<?php
/**
 * CMDeals Custom Post Types/Taxonomies
 * 
 * Inits custom post types and taxonomies
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
 * Custom Post Types and taxonomies
 **/
function cmdeals_post_type() {
	global $wpdb, $cmdeals;
	
	$store_page_id = get_option('cmdeals_store_page_id');
	$base_slug = ($store_page_id > 0 && get_page( $store_page_id )) ? get_page_uri( $store_page_id ) : 'deals';	
	
	$category_base = (get_option('cmdeals_prepend_store_page_to_urls')=="yes") ? trailingslashit($base_slug) : '';
	
	$category_slug = (get_option('cmdeals_deal_category_slug')) ? get_option('cmdeals_deal_category_slug') : _x('daily-deals-category', 'slug', 'cmdeals');
	$tag_slug = (get_option('cmdeals_deal_tags_slug')) ? get_option('cmdeals_deal_tags_slug') : _x('daily-deals-tag', 'slug', 'cmdeals');
	
	$deal_base = (get_option('cmdeals_prepend_store_page_to_deals')=='yes') ? trailingslashit($base_slug) : trailingslashit(__('deals', 'cmdeals'));
	if (get_option('cmdeals_prepend_category_to_deals')=='yes') $deal_base .= trailingslashit('%deal-categories%');
	$deal_base = untrailingslashit($deal_base);
	
	if (current_user_can('manage_deals')) $show_in_menu = 'cmdeals'; else $show_in_menu = true;

	/**
	 * Taxonomies
	 **/
	$admin_only_query_var = (is_admin()) ? true : false;
	  
	register_taxonomy( 'deal-type',
        array('daily-deals'),
        array(
            'hierarchical' 			=> false,
            'show_ui' 				=> false,
            'show_in_nav_menus' 	=> false,
            'query_var' 			=> $admin_only_query_var,
            'rewrite'				=> false
        )
    );
	register_taxonomy( 'deal-categories',
        array('daily-deals'),
        array(
            'hierarchical' 			=> true,
            'update_count_callback' => '_update_post_term_count',
            'label' 				=> __( 'Deal Categories', 'cmdeals'),
            'labels' => array(
                    'name'              => __( 'Deal Categories', 'cmdeals' ),
                    'singular_name'     => __( 'Deal Category', 'cmdeals' ),
                    'search_items'      => __( 'Deal Search Category', 'cmdeals' ),
                    'all_items'         => __( 'All Deal Categories', 'cmdeals' ),
                    'edit_item'         => __( 'Edit Deal Category', 'cmdeals' ),
                    'update_item'       => __( 'Update Deal Category', 'cmdeals' ),
                    'add_new_item'      => __( 'Add Deal Category', 'cmdeals' ),
                    'new_item_name'     => __( 'New Deal Category', 'cmdeals' )
                ),
            'show_ui' 				=> true,
            'query_var' 			=> true,
            'rewrite' 				=> array( 'slug' => $category_base . $category_slug, 'with_front' => false ),
        )
    );
    
    register_taxonomy( 'deal-tags',
        array('daily-deals'),
        array(
            'hierarchical' 			=> false,
            'label' 				=> __( 'Deal Tags', 'cmdeals'),
            'labels' => array(
                        'name'              => __( 'Deal Tags', 'cmdeals' ),
                        'singular_name'     => __( 'Deal Tag', 'cmdeals' ),
                        'search_items'      => __( 'Deal Search Tag', 'cmdeals' ),
                        'all_items'         => __( 'All Deal Tags', 'cmdeals' ),
                        'edit_item'         => __( 'Edit Deal Tag', 'cmdeals' ),
                        'update_item'       => __( 'Update Deal Tag', 'cmdeals' ),
                        'add_new_item'      => __( 'Add Deal Tag', 'cmdeals' ),
                        'new_item_name'     => __( 'New Deal Tag', 'cmdeals' )
            	),
            'show_ui' 				=> true,
            'query_var' 			=> true,
            'rewrite' 				=> array( 'slug' => $category_base . $tag_slug, 'with_front' => false ),
        )
    );
    
    register_taxonomy( 'deals_sales_status',
        array('deals-sales'),
        array(
            'hierarchical' 			=> true,
            'update_count_callback' => '_update_post_term_count',
            'labels' => array(
                    'name' 				=> __( 'Order statuses', 'cmdeals'),
                    'singular_name' 	=> __( 'Order status', 'cmdeals'),
                    'search_items' 		=> __( 'Search Order statuses', 'cmdeals'),
                    'all_items' 		=> __( 'All  Order statuses', 'cmdeals'),
                    'parent_item' 		=> __( 'Parent Order status', 'cmdeals'),
                    'parent_item_colon' => __( 'Parent Order status:', 'cmdeals'),
                    'edit_item' 		=> __( 'Edit Order status', 'cmdeals'),
                    'update_item' 		=> __( 'Update Order status', 'cmdeals'),
                    'add_new_item' 		=> __( 'Add New Order status', 'cmdeals'),
                    'new_item_name' 	=> __( 'New Order status Name', 'cmdeals')
           	 ),
            'show_ui' 				=> false,
            'show_in_nav_menus' 	=> false,
            'query_var' 			=> $admin_only_query_var,
            'rewrite' 				=> false,
        )
    );
    
    $attribute_taxonomies = $cmdeals->get_attribute_taxonomies();    
	if ( $attribute_taxonomies ) :
		foreach ($attribute_taxonomies as $tax) :
	    	
	    	$name = $cmdeals->attribute_taxonomy_name($tax->attribute_name);
	    	$hierarchical = true;
	    	if ($name) :
	    	
	    		$label = ( isset( $tax->attribute_label ) && $tax->attribute_label ) ? $tax->attribute_label : $tax->attribute_name;
				
				$show_in_nav_menus = apply_filters('cmdeals_attribute_show_in_nav_menus', false, $name);
				
	    		register_taxonomy( $name,
			        array('daily-deals'),
			        array(
			            'hierarchical' 				=> $hierarchical,
			            'labels' => array(
			                    'name' 						=> $label,
			                    'singular_name' 			=> $label,
			                    'search_items' 				=> __( 'Search', 'cmdeals') . ' ' . $label,
			                    'all_items' 				=> __( 'All', 'cmdeals') . ' ' . $label,
			                    'parent_item' 				=> __( 'Parent', 'cmdeals') . ' ' . $label,
			                    'parent_item_colon' 		=> __( 'Parent', 'cmdeals') . ' ' . $label . ':',
			                    'edit_item' 				=> __( 'Edit', 'cmdeals') . ' ' . $label,
			                    'update_item' 				=> __( 'Update', 'cmdeals') . ' ' . $label,
			                    'add_new_item' 				=> __( 'Add New', 'cmdeals') . ' ' . $label,
			                    'new_item_name' 			=> __( 'New', 'cmdeals') . ' ' . $label
			            	),
			            'show_ui' 					=> false,
			            'query_var' 				=> true,
			            'show_in_nav_menus' 		=> $show_in_nav_menus,
			            'rewrite' 					=> array( 'slug' => $category_base . strtolower(sanitize_title($tax->attribute_name)), 'with_front' => false, 'hierarchical' => $hierarchical ),
			        )
			    );
	    		
	    	endif;
	    endforeach;    	
    endif;
    
    /**
	 * Post Types
	 **/
	register_post_type( "daily-deals",
		array(
                        'labels' => array(
                                'name' 			=> __( 'Deals', 'cmdeals' ),
                                'singular_name' 		=> __( 'Deal', 'cmdeals' ),
                                'add_new' 			=> __( 'Add Deal', 'cmdeals' ),
                                'add_new_item' 		=> __( 'Add New Deal', 'cmdeals' ),
                                'edit' 			=> __( 'Edit', 'cmdeals' ),
                                'edit_item' 		=> __( 'Edit Deal', 'cmdeals' ),
                                'new_item' 			=> __( 'New Deal', 'cmdeals' ),
                                'view' 			=> __( 'View Deal', 'cmdeals' ),
                                'view_item' 		=> __( 'View Deal', 'cmdeals' ),
                                'search_items' 		=> __( 'Search Deals', 'cmdeals' ),
                                'not_found' 		=> __( 'No Deals found', 'cmdeals' ),
                                'not_found_in_trash' 	=> __( 'No Deals found in trash', 'cmdeals' ),
                                'parent' 			=> __( 'Parent Deal', 'cmdeals' )
                        ),
			'description' 			=> __( 'This is where you can add new deals to your store.', 'cmdeals' ),
			'public' 				=> true,
			'show_ui' 				=> true,
			'capability_type' 		=> 'post',
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> false,
			'hierarchical' 			=> true,
			'rewrite' 				=> array( 'slug' => $deal_base, 'with_front' => false ),
			'query_var' 			=> true,			
			'supports' 				=> array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
			'has_archive' 			=> $base_slug,
			'show_in_nav_menus' 	=> false,
			'menu_icon'				=> $cmdeals->plugin_url() . '/cmdeals-assets/images/icons/menu_icon_deals.png'
		)
	);
	
	register_post_type( "deal-variations",
		array(
			'labels' => array(
					'name' 					=> __( 'Variations', 'cmdeals' ),
					'singular_name' 		=> __( 'Variation', 'cmdeals' ),
					'add_new' 				=> __( 'Add Variation', 'cmdeals' ),
					'add_new_item' 			=> __( 'Add New Variation', 'cmdeals' ),
					'edit' 					=> __( 'Edit', 'cmdeals' ),
					'edit_item' 			=> __( 'Edit Variation', 'cmdeals' ),
					'new_item' 				=> __( 'New Variation', 'cmdeals' ),
					'view' 					=> __( 'View Variation', 'cmdeals' ),
					'view_item' 			=> __( 'View Variation', 'cmdeals' ),
					'search_items' 			=> __( 'Search Variations', 'cmdeals' ),
					'not_found' 			=> __( 'No Variations found', 'cmdeals' ),
					'not_found_in_trash' 	=> __( 'No Variations found in trash', 'cmdeals' ),
					'parent' 				=> __( 'Parent Variation', 'cmdeals' )
				),
			'public' 				=> true,
			'show_ui' 				=> false,
			'capability_type' 		=> 'post',
			'publicly_queryable' 	=> true,
			'exclude_from_search' 	=> true,
			'hierarchical' 			=> true,
			'rewrite' 				=> false,
			'query_var'				=> true,			
			'supports' 				=> array( 'title', 'editor', 'custom-fields', 'page-attributes', 'thumbnail' ),
			'show_in_nav_menus' 	=> false
		)
	);
    
    register_post_type( "deals-sales",
		array(
                        'labels' => array(
                                'name' 			=> __( 'Sales', 'cmdeals' ),
                                'singular_name' 		=> __( 'Sale', 'cmdeals' ),
                                'add_new' 			=> __( 'Add Sale', 'cmdeals' ),
                                'add_new_item' 		=> __( 'Add New Sale', 'cmdeals' ),
                                'edit' 			=> __( 'Edit', 'cmdeals' ),
                                'edit_item' 		=> __( 'Edit Sale', 'cmdeals' ),
                                'new_item' 			=> __( 'New Sale', 'cmdeals' ),
                                'view' 			=> __( 'View Sale', 'cmdeals' ),
                                'view_item' 		=> __( 'View Sale', 'cmdeals' ),
                                'search_items' 		=> __( 'Search Sales', 'cmdeals' ),
                                'not_found' 		=> __( 'No Sales found', 'cmdeals' ),
                                'not_found_in_trash' 	=> __( 'No Sales found in trash', 'cmdeals' ),
                                'parent' 			=> __( 'Parent Sale', 'cmdeals' )
                        ),
			'description' 			=> __( 'Stored and manage all sales data transaction.', 'cmdeals' ),
			'public' 				=> true,
			'show_ui' 				=> true,
			'capability_type' 		=> 'post',
			'capabilities' => array(
				'publish_posts' 	=> 'manage_deals',
				'edit_posts' 		=> 'manage_deals',
				'edit_others_posts' => 'manage_deals',
				'delete_posts' 		=> 'manage_deals',
				'delete_others_posts'=> 'manage_deals',
				'read_private_posts'=> 'manage_deals',
				'edit_post' 		=> 'manage_deals',
				'delete_post' 		=> 'manage_deals',
				'read_post' 		=> 'manage_deals',
			),
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_in_menu' 			=> $show_in_menu,
			'hierarchical' 			=> false,
			'show_in_nav_menus' 	=> false,
			'rewrite' 				=> false,
			'query_var' 			=> true,			
			'supports' 				=> array( 'title', 'comments', 'custom-fields' ),
			'has_archive' 			=> false
		)
	);

    

} 

/**
 * Replaces "Post" in the update messages for custom post types on the "Edit" post screen.
 *
 * For example "Post updated. View Post." becomes "Deal updated. View Deal".
 *
 * @since 1.1
 *
 * @param array $messages The default WordPress messages.
 */
function cmdeals_custom_update_messages( $messages ) {
	global $post, $post_ID;

	$post_types = get_post_types( array( 'show_ui' => true, '_builtin' => false ), 'objects' );

	foreach( $post_types as $post_type => $post_object ) {

		$messages[$post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __( '%s updated. <a href="%s">View %s</a>', 'cmdeals' ), $post_object->labels->singular_name, esc_url( get_permalink( $post_ID ) ), $post_object->labels->singular_name ),
			2 => __( 'Custom field updated.', 'cmdeals' ),
			3 => __( 'Custom field deleted.', 'cmdeals' ),
			4 => sprintf( __( '%s updated.', 'cmdeals' ), $post_object->labels->singular_name ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s', 'cmdeals' ), $post_object->labels->singular_name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( '%s published. <a href="%s">View %s</a>', 'cmdeals' ), $post_object->labels->singular_name, esc_url( get_permalink( $post_ID ) ), $post_object->labels->singular_name ),
			7 => sprintf( __( '%s saved.', 'cmdeals' ), $post_object->labels->singular_name ),
			8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'cmdeals' ), $post_object->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $post_object->labels->singular_name ),
			9 => sprintf( __( '%s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %s</a>', 'cmdeals' ), $post_object->labels->singular_name, date_i18n( __( 'M j, Y @ G:i', 'cmdeals' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), $post_object->labels->singular_name ),
			10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'cmdeals' ), $post_object->labels->singular_name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), $post_object->labels->singular_name ),
			);
	}

	return $messages;
}
add_filter( 'post_updated_messages', 'cmdeals_custom_update_messages' );


/**
 * Filter to allow deal-categories in the permalinks for deals.
 *
 * @since 1.1
 *
 * @param string $permalink The existing permalink URL.
 */
function cmdeals_deal_category_filter_post_link( $permalink, $post, $leavename, $sample ) {
    // Abort if post is not a deals
    if ($post->post_type!=='daily-deals') return $permalink;
    
    // Abort early if the placeholder rewrite tag isn't in the generated URL
    if ( false === strpos( $permalink, '%deal-categories%' ) ) return $permalink;

    // Get the custom taxonomy terms in use by this post
    $terms = get_the_terms( $post->ID, 'deal-categories' );

    if ( empty( $terms ) ) :
    	// If no terms are assigned to this post, use a string instead (can't leave the placeholder there)
        $permalink = str_replace( '%deal-categories%', __('daily-deals', 'cmdeals'), $permalink );
    else :
    	// Replace the placeholder rewrite tag with the first term's slug
        $first_term = array_shift( $terms );
        $permalink = str_replace( '%deal-categories%', $first_term->slug, $permalink );
    endif;

    return $permalink;
}
add_filter( 'post_type_link', 'cmdeals_deal_category_filter_post_link', 10, 4 );


/**
 * Add term ordering to get_terms
 * 
 * It enables the support a 'menu_order' parameter to get_terms for the deal-categories taxonomy.
 * By default it is 'ASC'. It accepts 'DESC' too
 * 
 * To disable it, set it ot false (or 0)
 * 
 */
add_filter( 'terms_clauses', 'cmdeals_terms_clauses', 10, 3);

function cmdeals_terms_clauses($clauses, $taxonomies, $args ) {
	global $wpdb, $cmdeals;

	// No sorting when menu_order is false
	if ( isset($args['menu_order']) && $args['menu_order'] == false ) return $clauses;
	
	// No sorting when orderby is non default
	if ( isset($args['orderby']) && $args['orderby'] != 'name' ) return $clauses;
	
	// No sorting in admin when sorting by a column
	if ( isset($_GET['orderby']) ) return $clauses;

	// wordpress should give us the taxonomies asked when calling the get_terms function. Only apply to categories and pa_ attributes
	$found = false;
	foreach ((array) $taxonomies as $taxonomy) :
		if ($taxonomy=='deal-categories' || strstr($taxonomy, 'pa_')) :
			$found = true;
			break;
		endif;
	endforeach;
	if (!$found) return $clauses;
	
	// Meta name
	if (strstr($taxonomies[0], 'pa_')) :
		$meta_name =  'order_' . esc_attr($taxonomies[0]);
	else :
		$meta_name = 'order';
	endif;

	// query fields
	if( strpos('COUNT(*)', $clauses['fields']) === false ) $clauses['fields']  .= ', tm.* ';

	//query join
	$clauses['join'] .= " LEFT JOIN {$wpdb->cmdeals_termmeta} AS tm ON (t.term_id = tm.cmdeals_term_id AND tm.meta_key = '". $meta_name ."') ";
	
	// default to ASC
	if( ! isset($args['menu_order']) || ! in_array( strtoupper($args['menu_order']), array('ASC', 'DESC')) ) $args['menu_order'] = 'ASC';

	$order = "ORDER BY CAST(tm.meta_value AS SIGNED) " . $args['menu_order'];
	
	if ( $clauses['orderby'] ):
		$clauses['orderby'] = str_replace('ORDER BY', $order . ',', $clauses['orderby'] );
	else:
		$clauses['orderby'] = $order;
	endif;
	
	return $clauses;
}

/**
 * CMDeals Term Meta API
 * 
 * API for working with term meta data. Adapted from 'Term meta API' by Nikolay Karev
 * 
 */
add_action( 'init', 'cmdeals_taxonomy_metadata_wpdbfix', 0 );
add_action( 'switch_blog', 'cmdeals_taxonomy_metadata_wpdbfix', 0 );

function cmdeals_taxonomy_metadata_wpdbfix() {
	global $wpdb;

	$variable_name = 'cmdeals_termmeta';
	$wpdb->$variable_name = $wpdb->prefix . $variable_name;	
	$wpdb->tables[] = $variable_name;
} 

function update_cmdeals_term_meta($term_id, $meta_key, $meta_value, $prev_value = ''){
	return update_metadata('cmdeals_term', $term_id, $meta_key, $meta_value, $prev_value);
}

function add_cmdeals_term_meta($term_id, $meta_key, $meta_value, $unique = false){
	return add_metadata('cmdeals_term', $term_id, $meta_key, $meta_value, $unique);
}

function delete_cmdeals_term_meta($term_id, $meta_key, $meta_value = '', $delete_all = false){
	return delete_metadata('cmdeals_term', $term_id, $meta_key, $meta_value, $delete_all);
}

function get_cmdeals_term_meta($term_id, $key, $single = true){
	return get_metadata('cmdeals_term', $term_id, $key, $single);
}

/**
 * CMDeals Dropdown categories
 * 
 * Stuck with this until a fix for http://core.trac.wordpress.org/ticket/13258
 * We use a custom walker, just like WordPress does it
 */
function cmdeals_deals_dropdown_categories( $show_counts = 0, $hierarchal = 0 ) {
	global $wp_query;
	
	$r = array();
	$r['pad_counts'] = 1;
	$r['hierarchal'] = $hierarchal;
	$r['hide_empty'] = 1;
	$r['show_count'] = $show_counts;
	$r['selected'] = (isset($wp_query->query['deal-categories'])) ? $wp_query->query['deal-categories'] : '';
	
	$terms = get_terms( 'deal-categories', $r );
	if (!$terms) return;
	
	$output  = "<select name='deal-categories' id='dropdown_deal_category'>";
	$output .= '<option value="">'.__('Show all categories', 'cmdeals').'</option>';
	$output .= cmdeals_walk_category_dropdown_tree( $terms, 0, $r );
	$output .="</select>";
	
	echo $output;
}

/**
 * Walk the Deal Categories.
 */
function cmdeals_walk_category_dropdown_tree() {
	$args = func_get_args();
	// the user's options are the third parameter
	if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
		$walker = new CMDeals_Walker_CategoryDropdown;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array(array( &$walker, 'walk' ), $args );
}

/**
 * Create HTML dropdown list of Deal Categories.
 */
class CMDeals_Walker_CategoryDropdown extends Walker {

	var $tree_type = 'category';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id', 'slug' => 'slug' );

	function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0	) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$cat_name = apply_filters('list_deal_categories', $category->name, $category);
		$output .= "\t<option class=\"level-$depth\" value=\"".$category->slug."\"";
		if ( $category->slug == $args['selected'] )
			$output .= ' selected="selected"';
		$output .= '>';
		$output .= $pad.$cat_name;
		if ( $args['show_count'] )
			$output .= '&nbsp;('. $category->count .')';
		$output .= "</option>\n";
	}
}