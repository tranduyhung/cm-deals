<?php
/**
 * Admin functions for the deals-sales post type
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
 * Columns for order page
 **/
add_filter( 'manage_edit-deals-sales_columns', 'cmdeals_edit_order_columns' );

function cmdeals_edit_order_columns( $columns ) {

	$columns = array(
		'cb'			=> '<input type="checkbox" />';
		'sales_status'	=> __(' Status', 'cmdeals' );
		'order_title'	=> __(' Order', 'cmdeals' );
		'total_cost'	=> __(' Order Total', 'cmdeals' );
		'order_date'	=> __(' Date', 'cmdeals' );
		'order_actions'	=> __(' Actions', 'cmdeals' );
	);

	return $columns;
}

/**
 * Custom Columns for order page
 **/
add_action( 'manage_deals-sales_posts_custom_column', 'cmdeals_custom_order_columns', 2 );

function cmdeals_custom_order_columns( $column ) {
	global $post;

	$order = new cmdeals_order( $post->ID );

	switch ( $column ) {
		case 'sales_status' :
			echo sprintf(
				__( '<mark class="%s">%s</mark>', 'cmdeals' ),
				sanitize_title( $order->status ),
				__($order->status, 'cmdeals')
			);
			break;

		case 'order_title' :
			echo '<a href="' . admin_url( 'post.php?post=' . $post->ID . '&action=edit' ) . '">' . sprintf( __( 'Order #%s', 'cmdeals' ), $post->ID ) . '</a> ';

			if ( $order->user_id )
				$user_info = get_userdata( $order->user_id );

			if ( isset( $user_info ) && $user_info ) {
				$user = '<a href="user-edit.php?user_id=' . esc_attr( $user_info->ID ) . '">';

				if ( $user_info->first_name || $user_info->last_name )
					$user .= $user_info->first_name.' '.$user_info->last_name;
				else
					$user .= esc_html( $user_info->display_name );

				$user .= '</a>';
			} else {
				$user = __( 'Guest', 'cmdeals' );
			}

			echo '<small class="meta">' . __( 'Customer:', 'cmdeals' ) . ' ' . $user . '</small>';

			if ( $order->user_email ) {
				echo '<small class="meta">' . __( 'Email:', 'cmdeals' ) . ' ' . '<a href="' . esc_url( 'mailto:' . $order->user_email ) . '">' . esc_html( $order->user_email ) . '</a></small>';
			} else {
				if ( $order->payment_method_title ) :
				echo '<small class="meta">' . __( 'Paid via:', 'cmdeals' ) . ' ' . esc_html( $order->payment_method_title ) . '</small>';
			}

			break;

		case 'total_cost' :
			echo cmdeals_price( $order->order_total );
			break;

		case 'order_date' :
			if ( '0000-00-00 00:00:00' == $post->post_date ) {
				$t_time = $h_time = __( 'Unpublished', 'cmdeals' );
				$time_diff = 0;
			} else {
				$t_time = get_the_time( __( 'Y/m/d g:i:s A', 'cmdeals' ) );
				$m_time = $post->post_date;
				$time = get_post_time( 'G', true, $post );

				$time_diff = time() - $time;

				if ( $time_diff > 0 && $time_diff < 24*60*60 )
					$h_time = sprintf( __( '%s ago', 'cmdeals' ), human_time_diff( $time ) );
				else
					$h_time = mysql2date( __( 'Y/m/d', 'cmdeals' ), $m_time );
			}

			echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post ) . '</abbr>';
			break;

		case 'order_actions' :
			if ( in_array( $order->status, array( 'pending', 'on-hold' ) ) )
				echo '<a class="button" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=cmdeals-mark-order-processing&order_id=' . $post->ID ) ) . '">' . _e( 'Processing', 'cmdeals' ) . '</a>';

			if (in_array( $order->status, array( 'pending', 'on-hold', 'processing' ) ) ) {
				echo '<a class="button" href="' . wp_nonce_url( admin_url( 'admin-ajax.php?action=cmdeals-mark-order-complete&order_id=' . $post->ID ) ) . '">' . _e('Complete', 'cmdeals') . '</a>';
				echo '<a class="button" href="' . admin_url(' post.php?post=' . $post->ID . '&action=edit' ) . '">' . _e( 'View', 'cmdeals' ) . '</a>';

			break;
	}
}

/**
 * Order page filters
 **/
add_filter( 'views_edit-deals-sales', 'cmdeals_custom_order_views' );

function cmdeals_custom_order_views( $views ) {
	unset($views['publish']);

	if ( isset( $views['trash'] ) ) {
		$trash = $views['trash'];
		unset( $views['draft'] );
		unset( $views['trash'] );
		$views['trash'] = $trash;
	}

	return $views;
}

/**
 * Order page actions
 **/
add_filter( 'post_row_actions', 'cmdeals_remove_row_actions', 10, 1 );

function cmdeals_remove_row_actions( $actions ) {
	if( get_post_type() === 'deals-sales' ) {
		unset( $actions['view'] );
		unset( $actions['inline hide-if-no-js'] );
	}

	return $actions;
}

/**
 * Order page bulk actions
 **/
add_filter( 'bulk_actions-edit-deals-sales', 'cmdeals_bulk_actions' );

function cmdeals_bulk_actions( $actions ) {
	if ( isset( $actions['edit'] ) )
		unset( $actions['edit'] );

	return $actions;
}

/**
 * Filter sales by status
 **/
add_action( 'restrict_manage_posts','cmdeals_sales_by_status' );

function cmdeals_sales_by_status() {
	global $typenow, $wp_query;

	if ( $typenow=='deals-sales' ) {
		$terms = get_terms( 'deals_sales_status' );
		$output = '<select name="deals_sales_status" id="dropdown_deals_sales_status">';
		$output .= '<option value="">' . __( 'Show all statuses', 'cmdeals' ) . '</option>';

		foreach ( $terms as $term ) {
			$output .= "<option value='$term->slug' ";

			if ( isset( $wp_query->query['deals_sales_status'] ) )
				$output .= selected( $term->slug, $wp_query->query['deals_sales_status'], false );

			$output .= ">" .__( $term->name, 'cmdeals' ) . " ($term->count)</option>";
		}

		$output .= '</select>';

		echo $output;
	}
}

/**
 * Filter sales by customer
 **/
add_action( 'restrict_manage_posts', 'cmdeals_sales_by_customer' );

function cmdeals_sales_by_customer() {
	global $typenow, $wp_query;

	if ( $typenow == 'deals-sales' ) {
		$users_query = new WP_User_Query(
			array( 'fields' => 'all',
					'orderby' => 'display_name')
		);

		$users = $users_query->get_results();

		if ( $users ) {
			$output = '<select name="_customer_user" id="dropdown_customers">';
			$output .= '<option value="">' . __('Show all customers', 'cmdeals') . '</option>';

			foreach ( $users as $user ) {
				$output .= "<option value='$user->ID' ";

				if ( isset ( $_GET['_customer_user'] ) )
					$output .= selected( $user->ID, $_GET['_customer_user'], false);

				$output .= ">$user->display_name</option>";
			}

			$output .= '</select>';

			echo $output;
		}
	}
}

/**
 * Filter sales by customer query
 **/
add_filter( 'request', 'cmdeals_sales_by_customer_query' );

function cmdeals_sales_by_customer_query( $vars ) {
	global $typenow, $wp_query;

	if ( $typenow == 'deals-sales' && isset( $_GET['_customer_user'] ) && $_GET['_customer_user'] > 0 ) {
		$vars['meta_key'] = '_customer_user';
		$vars['meta_value'] = (int) $_GET['_customer_user'];
	}

	return $vars;
}

/**
 * Make order columns sortable
 * https://gist.github.com/906872
 **/
add_filter( 'manage_edit-deals-sales_sortable_columns', 'cmdeals_custom_deals_sales_sort' );

function cmdeals_custom_deals_sales_sort($columns) {
	$custom = array(
		'order_title'	=> 'ID',
		'order_total'	=> 'order_total',
		'order_date'	=> 'date'
	);

	return wp_parse_args($custom, $columns);
}

/**
 * Order column orderby/request
 **/
add_filter( 'request', 'cmdeals_custom_deals_sales_orderby' );

function cmdeals_custom_deals_sales_orderby( $vars ) {
	global $typenow, $wp_query;

	if ($typenow != 'deals-sales') return $vars;

	// Sorting
	if (isset( $vars['orderby'] )) {
		if ( 'order_total' == $vars['orderby'] ) {
			$vars = array_merge( $vars, array(
					'meta_key' 	=> '_order_total',
					'orderby' 	=> 'meta_value_num'
				)
			);
		}
	}

	return $vars;
}

/**
 * Order custom field search
 **/
if ( is_admin() ) {
	add_filter( 'parse_query', 'cmdeals_deals_sales_search_custom_fields' );
	add_filter( 'get_search_query', 'cmdeals_deals_sales_search_label' );
}

function cmdeals_deals_sales_search_custom_fields( $wp ) {
	global $pagenow, $wpdb;

	if ( 'edit.php' != $pagenow ) return $wp;

	if ( !isset( $wp->query_vars['s'] ) || !$wp->query_vars['s'] ) return $wp;

	if ( $wp->query_vars['post_type'] != 'deals-sales' ) return $wp;

	$search_fields = array(
		'_order_key',
		'_order_items'
	);

	// Query matching custom fields - this seems faster than meta_query
	$post_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key IN (' . '"' . implode('","', $search_fields ) . '"' . ') AND meta_value LIKE "%%%s%%"', esc_attr( $_GET['s']) ) );

	// Query matching excerpts and titles
	$post_ids = array_merge(
		$post_ids,
		$wpdb->get_col(
			$wpdb->prepare( '
				SELECT ' . $wpdb->posts . '.ID 
				FROM ' . $wpdb->posts . ' 
				LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id
				LEFT JOIN ' . $wpdb->users.' ON ' . $wpdb->postmeta . '.meta_value = ' . $wpdb->users . '.ID
				WHERE
					post_excerpt	LIKE "%%%1$s%%" OR
					post_title		LIKE "%%%1$s%%" OR
					(
						meta_key		= "_customer_user" AND
						(
							user_login		LIKE "%%%1$s%%" OR
							user_nicename	LIKE "%%%1$s%%" OR
							user_email		LIKE "%%%1$s%%" OR
							display_name	LIKE "%%%1$s%%"
						)
					)
				',
				esc_attr( $_GET['s'] )
			)
		)
	);

	// Add ID
	$search_order_id = str_replace( 'Order #', '', $_GET['s'] );

	if ( is_numeric( $search_order_id ) )
		$post_ids[] = $search_order_id;

	// Add blank ID so not all results are returned if the search finds nothing
	$post_ids[] = 0;

	// Remove s - we don't want to search order name
	unset( $wp->query_vars['s'] );

	// so we know we're doing this
	$wp->query_vars['deals_sales_search'] = true;

	// Search by found posts
	$wp->query_vars['post__in'] = $post_ids;
}

function cmdeals_deals_sales_search_label( $query ) {
	global $pagenow, $typenow;

	if( 'edit.php' != $pagenow ) return $query;

	if ( $typenow!='deals-sales' ) return $query;

	if ( !get_query_var('deals_sales_search')) return $query;

	return $_GET['s'];
}

/**
 * Query vars for custom searches
 **/
add_filter( 'query_vars', 'cmdeals_add_custom_query_var' );

function cmdeals_add_custom_query_var( $public_query_vars ) {
	$public_query_vars[] = 'deals_sales_search';
	return $public_query_vars;
}