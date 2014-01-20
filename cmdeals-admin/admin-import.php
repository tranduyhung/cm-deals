<?php
/**
 * Functions for handling WordPress import to make it compatable with CMDeals
 *
 * WordPress import should work - however, it fails to import custom deals attribute taxonomies.
 * This code grabs the file before it is imported and ensures the taxonomies are created.
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

function cmdeals_import_start() {
	
	global $wpdb;
	
	$id = (int) $_POST['import_id'];
	$file = get_attached_file( $id );

	$parser = new WXR_Parser();
	$import_data = $parser->parse( $file );

	if (isset($import_data['posts'])) :
		$posts = $import_data['posts'];
		
		if ($posts && sizeof($posts)>0) foreach ($posts as $post) :
			
			if ($post['post_type']=='daily-deals') :
				
				if ($post['terms'] && sizeof($post['terms'])>0) :
					
					foreach ($post['terms'] as $term) :
						
						$domain = $term['domain'];
						
						if (strstr($domain, 'pa_')) :
							
							// Make sure it exists!
							if (!taxonomy_exists( $domain )) :
								
								$nicename = strtolower(sanitize_title(str_replace('pa_', '', $domain)));
								
								$exists_in_db = $wpdb->get_var("SELECT attribute_id FROM ".$wpdb->prefix . "cmdeals_attribute_taxonomies WHERE attribute_name = '".$nicename."';");
								
								if (!$exists_in_db) :
								
									// Create the taxonomy
									$wpdb->insert( $wpdb->prefix . "cmdeals_attribute_taxonomies", array( 'attribute_name' => $nicename, 'attribute_type' => 'select' ), array( '%s', '%s' ) );
									
								endif;
								
								// Register the taxonomy now so that the import works!
								register_taxonomy( $domain,
							        array('daily-deals'),
							        array(
							            'hierarchical' => true,
							            'labels' => array(
							                    'name' => $nicename,
							                    'singular_name' => $nicename,
							                    'search_items' =>  __( 'Search', 'cmdeals') . ' ' . $nicename,
							                    'all_items' => __( 'All', 'cmdeals') . ' ' . $nicename,
							                    'parent_item' => __( 'Parent', 'cmdeals') . ' ' . $nicename,
							                    'parent_item_colon' => __( 'Parent', 'cmdeals') . ' ' . $nicename . ':',
							                    'edit_item' => __( 'Edit', 'cmdeals') . ' ' . $nicename,
							                    'update_item' => __( 'Update', 'cmdeals') . ' ' . $nicename,
							                    'add_new_item' => __( 'Add New', 'cmdeals') . ' ' . $nicename,
							                    'new_item_name' => __( 'New', 'cmdeals') . ' ' . $nicename
							            ),
							            'show_ui' => false,
							            'query_var' => true,
							            'rewrite' => array( 'slug' => strtolower(sanitize_title($nicename)), 'with_front' => false, 'hierarchical' => true ),
							        )
							    );
								
							endif;
							
						endif;
						
					endforeach;
					
				endif;
				
			endif;
			
		endforeach;
		
	endif;

}

add_action('import_start', 'cmdeals_import_start');