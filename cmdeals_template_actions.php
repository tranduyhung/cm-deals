<?php
/**
 * CMDeals Template Actions
 * 
 * Actions used in the template files to output content.
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
/* Content Wrappers */
add_action( 'cmdeals_before_main_content', 'cmdeals_output_content_wrapper', 10);
add_action( 'cmdeals_after_main_content', 'cmdeals_output_content_wrapper_end', 10);

/* Sidebar */
add_action( 'cmdeals_sidebar', 'cmdeals_get_sidebar', 10);

/* Deals Loop */
add_action( 'cmdeals_before_store_loop_item_title', 'cmdeals_template_loop_deals_thumbnail', 10, 2);
add_action( 'cmdeals_after_store_loop_item_title', 'cmdeals_template_loop_price', 10, 2);
add_action( 'cmdeals_after_store_loop_item_title', 'cmdeals_template_loop_countdown', 10, 2);

/* Subcategories */
add_action( 'cmdeals_before_subcategory_title', 'cmdeals_subcategory_thumbnail', 10);

/* Before Single Deals */
add_action( 'cmdeals_before_single_deals', 'cmdeals_before_single_deals', 10, 2);

/* Before Single Deals Summary Div */
add_action( 'cmdeals_before_single_deals_summary', 'cmdeals_show_deals_images', 20);
add_action( 'cmdeals_deals_thumbnails', 'cmdeals_show_deals_thumbnails', 20 );

/* After Single Deals Summary Div */
add_action( 'cmdeals_after_single_deals_summary', 'cmdeals_deals_description', 10);
add_action( 'cmdeals_after_single_deals_summary', 'cmdeals_template_single_add_to_cart', 20, 2 );
add_action( 'cmdeals_after_single_deals_summary', 'cmdeals_single_meta_content', 30, 2 );
add_action( 'cmdeals_after_single_deals_summary', 'cmdeals_output_related_deals', 40);
add_action( 'cmdeals_after_single_deals_summary', 'cmdeals_deals_comments', 50);

/* Deal Summary Box */
add_action( 'cmdeals_single_deals_summary', 'cmdeals_template_single_add_to_cart', 10, 2 );
add_action( 'cmdeals_single_deals_summary', 'cmdeals_template_single_price', 20, 2);
add_action( 'cmdeals_single_deals_summary', 'cmdeals_template_single_bought', 30, 2);
add_action( 'cmdeals_single_deals_summary', 'cmdeals_template_single_countdown', 40, 2);
add_action( 'cmdeals_single_deals_summary', 'cmdeals_template_single_sharing', 50, 2);

/* Deal Buy now */
add_action( 'cmdeals_simple_add_to_cart', 'cmdeals_simple_add_to_cart', 20, 2 ); 
add_action( 'cmdeals_variable_add_to_cart', 'cmdeals_variable_add_to_cart', 30, 2 ); 
add_action( 'cmdeals_external_add_to_cart', 'cmdeals_external_add_to_cart', 40, 2 );

/* Deal Add to Cart forms */
add_action( 'cmdeals_add_to_cart_form', 'cmdeals_add_to_cart_form_nonce', 10);

/* Pagination in loop-store */
add_action( 'cmdeals_pagination', 'cmdeals_pagination', 10 );
add_action( 'cmdeals_pagination', 'cmdeals_catalog_ordering', 20 );

/* Checkout */
add_action( 'cmdeals_before_checkout_form', 'cmdeals_checkout_login_form', 10 );
add_action( 'cmdeals_checkout_order_review', 'cmdeals_order_review', 10 );

/* Remove the singular class for cmdeals single deals */
add_action( 'after_setup_theme', 'cmdeals_body_classes_check' );

function cmdeals_body_classes_check () {
	if( has_filter( 'body_class', 'twentyeleven_body_classes' ) ) add_filter( 'body_class', 'cmdeals_body_classes' );
}

/* Footer */
add_action( 'wp_footer', 'cmdeals_demo_store' );

/* Order details */
add_action( 'cmdeals_view_order', 'cmdeals_order_details_table', 10 );
add_action( 'cmdeals_thankyou', 'cmdeals_order_details_table', 10 );
