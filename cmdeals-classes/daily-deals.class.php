<?php
/**
 * Deal Class
 * 
 * The CMDeals deals class handles individual deals data.
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

class cmdeals_deals {
	
	var $id;
	var $deal_custom_fields;
	var $exists;
	var $attributes;
	var $children;
	var $post;
	var $downloadable;
	var $virtual;
	var $price;
	var $visibility;
	var $_is_expired;
	var $_stock;
	var $stock_status;
	var $backsales;
	var $manage_stock;
	var $_discount_price;
	var $_base_price;
	var $deal_type;
	var $total_stock;
	var $_discount_price_dates_from;
	var $_discount_price_dates_to;
	var $min_variation_price;
	var $max_variation_price;
	var $featured;
	var $_coupon_area_deals;
	
	/**
	 * Loads all deals data from custom fields
	 *
	 * @param   int		$id		ID of the deals to load
	 */
	function cmdeals_deals( $id ) {
		
		$this->id = (int) $id;

		$this->deal_custom_fields = get_post_custom( $this->id );
		
		$this->exists = (sizeof($this->deal_custom_fields)>0) ? true : false;

		// Define the data we're going to load: Key => Default value
		$load_data = array(
			'downloadable'          => 'no',
			'virtual' 		=> 'no',
			'price' 		=> '',
			'visibility'            => 'hidden',
			'_is_expired'           => 'no',
			'_stock'                 => 0,
			'stock_status'          => 'instock',
			'backsales'             => 'no',
			'manage_stock'          => 'no',
			'_discount_price'	=> '',
			'_base_price'           => '',
			'_discount_price_dates_from'    => '',
			'_discount_price_dates_to'      => '',
			'min_variation_price'	=> '',
			'max_variation_price'	=> '',
			'featured'		=> 'no',
			'_coupon_area_deals'	=> ''
		);
		
		// Load the data from the custom fields
		foreach ($load_data as $key => $default) $this->$key = (isset($this->deal_custom_fields[$key][0]) && $this->deal_custom_fields[$key][0]!=='') ? $this->deal_custom_fields[$key][0] : $default;
			
		// Get deals type
		$transient_name = 'cmdeals_deals_type_' . $this->id;
		
		if ( false === ( $this->deal_type = get_transient( $transient_name ) ) ) :
			$terms = wp_get_object_terms( $id, 'deal-type', array('fields' => 'names') );
			$this->deal_type = (isset($terms[0])) ? sanitize_title($terms[0]) : 'simple';
			set_transient( $transient_name, $this->deal_type );
		endif;

	}
	
    
    /**
     * Get total stock
     * 
     * This is the stock of parent and children combined
     */
    function get_total_stock() {
        
        if (is_null($this->total_stock)) :
        
        	$transient_name = 'cmdeals_deals_total_stock_' . $this->id;
        
        	if ( false === ( $this->total_stock = get_transient( $transient_name ) ) ) :
        
		        $this->total_stock = $this->_stock;
		        
                        if (sizeof($this->get_children())>0) foreach ($this->get_children() as $child_id) :

                                $stock = get_post_meta($child_id, '_stock', true);

                                if ( $stock!='' ) :

                                        $this->total_stock += $stock;

                                endif;

                        endforeach;

                        set_transient( $transient_name, $this->total_stock );
				
                endif;

        endif;

        return (int) $this->total_stock;
    }
    
	/** Returns the deals's children */
	function get_children() {
		
		if (!is_array($this->children)) :
		
			$this->children = array();
			
			if ($this->is_type('variable') || $this->is_type('grouped')) :
			
				$child_post_type = ($this->is_type('variable')) ? 'deal-variations' : 'daily-deals';
				
				$transient_name = 'cmdeals_deals_children_ids_' . $this->id;
        
	        	if ( false === ( $this->children = get_transient( $transient_name ) ) ) :
	        			
			        $this->children = get_posts( 'post_parent=' . $this->id . '&post_type=' . $child_post_type . '&orderby=menu_order&order=ASC&fields=ids&post_status=any&numberposts=-1' );
					
					set_transient( $transient_name, $this->children );
					
				endif;

			endif;
			
		endif;
		
		return (array) $this->children;
	}
	
	function get_child( $child_id ) {
		if ($this->is_type('variable')) :
			$child = new cmdeals_deals_variation( $child_id, $this->id, $this->deal_custom_fields );
		else :
			$child = new cmdeals_deals( $child_id );
		endif;
		return $child;
	}

	/**
	 * Reduce stock level of the deals
	 *
	 * @param   int		$by		Amount to reduce by
	 */
	function reduce_stock( $by = 1 ) {
		global $cmdeals;
		
		if ($this->managing_stock()) :
			$this->_stock = ($this->_stock == -1)? -1:$this->_stock - $by;
			$this->total_stock = $this->get_total_stock() - $by;
			update_post_meta($this->id, '_stock', $this->_stock);
			
			// Out of stock attribute
			if (!$this->is_in_stock()) :
				update_post_meta($this->id, 'stock_status', 'outofstock');
				update_post_meta($this->id, '_is_expired', 'yes');
                                $cmdeals->clear_deals_transients( $this->id ); // Clear transient
			endif;
			
			return $this->_stock;
		endif;
	}
	
	/**
	 * Increase stock level of the deals
	 *
	 * @param   int		$by		Amount to increase by
	 */
	function increase_stock( $by = 1 ) {
		if ($this->managing_stock()) :
			$this->_stock = $this->_stock + $by;
			$this->total_stock = $this->get_total_stock() + $by;
			update_post_meta($this->id, '_stock', $this->_stock);
			
			// Out of stock attribute
			if ($this->is_in_stock()): 
                            update_post_meta($this->id, 'stock_status', 'instock');
                            update_post_meta($this->id, '_is_expired', 'no');
                        endif;
			
			return $this->_stock;
		endif;
	}
	
	/**
	 * Checks the deals type
	 *
	 * Backwards compat with downloadable/virtual
	 */
	function is_type( $type ) {
		if (is_array($type) && in_array($this->deal_type, $type)) return true;
		if ($this->deal_type==$type) return true;
		return false;
	}
	
	/**
	 * Checks if a deals is downloadable
	 */
	function is_downloadable() {
		if ( $this->downloadable=='yes' ) return true; else return false;
	}
		
	/**
	 * Checks if available vouchers
	 */
	function is_vouchers() {
		if ( $this->downloadable == 'no' && $this->_coupon_area_deals != '' ) return true; else return false;
	}
		
	/** Returns whether or not the deals has any child deals */
	function has_child() {
		return sizeof($this->get_children()) ? true : false;
	}
	
	/** Returns whether or not the deals post exists */
	function exists() {
		if ($this->exists) return true;
		return false;
	}
	
	/** Get the deals's post data */
	function get_post_data() {
		if (empty($this->post)) :
			$this->post = get_post( $this->id );
		endif;
		return $this->post;
	}
	
	/** Get the title of the post */
	function get_title() {
		$this->get_post_data();
		return apply_filters('cmdeals_deals_title', apply_filters('the_title', $this->post->post_title), $this);
	}

	
	/** Get the add to url */
	function add_to_cart_url() {
		global $cmdeals;
		
		if ($this->is_type('variable')) :
			$url = add_query_arg('buy-this', 'variation');
			$url = add_query_arg('daily-deals', $this->id, $url);
		else :
			$url = add_query_arg('buy-this', $this->id);
		endif;
		
		$url = $cmdeals->nonce_url( 'add_to_cart', $url );
		return $url;
	}
	
	/** Returns whether or not the deals is stock managed */
	function managing_stock() {
                if( $this->is_type( array('external') )) return false;
                return true;
	}
	
	/** Returns whether or not the deals is in stock */
	function is_in_stock() {
            
                if( !$this->is_type( array('external') )):
                    if ($this->managing_stock()) :
                            if ($this->is_downloadable()) :
                                    if ($this->get_total_stock()==0)
                                        return false;
                                    else
                                        return true;
                            elseif($this->is_vouchers()):
                                return true;
                            else:
                                return false;
                            endif;
                    endif;
                    
                    return false;
                    
                endif;	
                
                return true;
	}
	
	/** Returns whether or not the deals can be backordered */
	function backsales_allowed() {
		if ($this->backsales=='yes' || $this->backsales=='notify') return true;
		return false;
	}
	
	/** Returns whether or not the deals needs to notify the customer on backorder */
	function backsales_require_notification() {
		if ($this->backsales=='notify') return true;
		return false;
	}
	
	/**
         * Returns number of items available for sale.
         * 
         * @return int
         */
        function get_stock_quantity() {
            return (int) $this->_stock;
        }

	/** Returns whether or not the deals has enough stock for the order */
	function has_enough_stock( $quantity ) {
		
		if (!$this->managing_stock()) return true;

		if ($this->backsales_allowed()) return true;
		
		if ($this->_stock >= $quantity || $this->_stock >= -1) :
			return true;
		endif;
		
		return false;
		
	}
	
	/** Returns the availability of the deals */
	function get_availability() {
	
		$availability = "";
		$class = "";
		
		if (!$this->managing_stock()) :
			if ($this->is_in_stock()) :
				//$availability = __('In stock', 'cmdeals'); /* Lets not bother showing stock if its not managed and is available */
			else :
				$availability = __('Out of stock', 'cmdeals');
				$class = 'out-of-stock';
			endif;
		else :
			if ($this->is_in_stock()) :
				if ($this->get_total_stock() > 0) :
					$availability = __('In stock', 'cmdeals');
					
					if ($this->backsales_allowed()) :
						if ($this->backsales_require_notification()) :
							$availability .= ' &ndash; '.$this->_stock.' ';
							$availability .= __('available', 'cmdeals');
							$availability .= __(' (backsales allowed)', 'cmdeals');
						endif;
					else :
						$availability .= ' &ndash; '.$this->_stock.' ';
						$availability .= __('available', 'cmdeals');
					endif;
					
				else :
					
					if ($this->backsales_allowed()) :
						if ($this->backsales_require_notification()) :
							$availability = __('Available on backorder', 'cmdeals');
						else :
							$availability = __('In stock', 'cmdeals');
						endif;
					else :
						$availability = __('Out of stock', 'cmdeals');
						$class = 'out-of-stock';
					endif;
					
				endif;
			else :
				if ($this->backsales_allowed()) :
					$availability = __('Available on backorder', 'cmdeals');
				else :
					$availability = __('Out of stock', 'cmdeals');
					$class = 'out-of-stock';
				endif;
			endif;
		endif;
		
		return array( 'availability' => $availability, 'class' => $class);
	}
	
	/** Returns whether or not the deals is featured */
	function is_featured() {
		if ($this->featured=='yes') return true; else return false;
	}
	
	/** Returns whether or not the deals is expired */
	function is_expired() {
                $date_to    = get_post_meta($this->id, '_end_time', true);
		if ($this->_is_expired == 'yes' || $date_to < current_time('timestamp')) return true; else return false;
	}
	
	/** Returns whether or not the deals is visible */
	function is_visible( $single = false ) {
		// Out of stock visibility
		if (get_option('cmdeals_hide_out_of_stock_items') == 'yes') :
			if (!$this->is_expired())
                                $single = false;
                        else
                                $single = true;
                endif;
                
                return $single;
                        
	}
	
	/** Returns whether or not the deals is on sale */
	function is_on_sale() {
		if ( $this->has_child() ) :
			
			foreach ($this->get_children() as $child_id) :
				$_discount_price    = get_post_meta( $child_id, '_discount_price', true );
				$_base_price        = get_post_meta( $child_id, '_base_price', true );
				if ( $_discount_price > 0 && $_discount_price < $_base_price ) return true;
			endforeach;
			
		else :
		
			if ( $this->is_free() ) return true;
		
		endif;
		return false;
	}
        
        /** Return the deals is free or not **/
        function is_free(){
            if( $this->get_sale() == 0 || $this->get_price() == $this->get_sale() ) return true; else false;
        }
	
	/** Returns the deals's weight */
	function get_weight() {
		if ($this->weight) return $this->weight;
	}
	
	/** Adjust a deals price dynamically */
	function adjust_price( $price ) {
		if ($price>0) :
			$this->_base_price += $price;
		endif;
	}
	
	/** Returns the deals's price */
	function get_price() {
		return $this->_base_price;
	}
	
	/** Returns the deals's sale */
	function get_sale() {
		return $this->_discount_price;
	}
	
	/** Returns the price in html format */
	function get_price_html() {
		$price = '';
		if ($this->is_type('grouped')) :
			
			$min_price = '';
			$max_price = '';
			
			foreach ($this->get_children() as $child_id) :
				$child_price = get_post_meta( $child_id, 'price', true);
				if ($child_price<$min_price || $min_price == '') $min_price = $child_price;
				if ($child_price>$max_price || $max_price == '') $max_price = $child_price;
			endforeach;
			
			$price .= '<span class="from">' . __('From:', 'cmdeals') . ' </span>' . cmdeals_price($min_price);	
			
			$price = apply_filters('cmdeals_grouped_price_html', $price, $this);
				
		elseif ($this->is_type('variable')) :
			
			if ( !$this->min_variation_price || $this->min_variation_price !== $this->max_variation_price ) $price .= '<span class="from">' . __('From:', 'cmdeals') . ' </span>';
			
			$price .= cmdeals_price($this->get_price());
			
			$price = apply_filters('cmdeals_variable_price_html', $price, $this);
			
		else :
			if ($this->_base_price > 0 && $this->_discount_price > 0) :
				if (isset($this->_discount_price) && isset($this->_base_price) && $this->_base_price > $this->_discount_price ) :
				
					$price .= '<del>'.cmdeals_price( $this->_base_price ).'</del> <ins>'.cmdeals_price($this->get_sale()).'</ins>';
					
					$price = apply_filters('cmdeals_discount_price_html', $price, $this);
					
				else :
				
					$price .= cmdeals_price($this->get_price());
					
					$price = apply_filters('cmdeals_price_html', $price, $this);
					
				endif;
				
			elseif ($this->_base_price == 0 || $this->_base_price === '' || $this->_discount_price == 0 || $this->_discount_price === '') :
				
                                $price = __('Free!', 'cmdeals');  

                                $price = apply_filters('cmdeals_free_price_html', $price, $this);
				
			endif;
		endif;
		
		return $price;
	}
        
        
	/** Returns the price in html format */
	function get_sale_html() {
                $sale = '';
            
                $sale .= cmdeals_price($this->get_sale());

                $sale = apply_filters('cmdeals_sale_html', $sale, $this);
                
                return $sale;
                
        }
	
	/** Returns the deals rating in html format - ratings are stored in transient cache */
	function get_rating_html( $location = '' ) {
		
		if ($location) $location = '_'.$location;
		$star_size = apply_filters('cmdeals_star_rating_size'.$location, 16);

		if ( false === ( $average_rating = get_transient( $this->id . '_cmdeals_average_rating' ) ) ) :
		
			global $wpdb;

			$count = $wpdb->get_var("
				SELECT COUNT(meta_value) FROM $wpdb->commentmeta 
				LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
				WHERE meta_key = 'rating'
				AND comment_post_ID = $this->id
				AND comment_approved = '1'
				AND meta_value > 0
			");
			
			$ratings = $wpdb->get_var("
				SELECT SUM(meta_value) FROM $wpdb->commentmeta 
				LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
				WHERE meta_key = 'rating'
				AND comment_post_ID = $this->id
				AND comment_approved = '1'
			");
		
			if ( $count>0 ) :
				$average_rating = number_format($ratings / $count, 2);
			else :
				$average_rating = '';
			endif;
			
			set_transient( $this->id . '_cmdeals_average_rating', $average_rating );
		
		endif;

		if ( $average_rating>0 ) :
			return '<div class="star-rating" title="'.sprintf(__('Rated %s out of 5', 'cmdeals'), $average_rating).'"><span style="width:'.($average_rating*$star_size).'px"><span class="rating">'.$average_rating.'</span> '.__('out of 5', 'cmdeals').'</span></div>';
		else :
			return '';
		endif;
	}
        
	/** Returns the deals categories */
	function get_categories( $sep = ', ', $before = '', $after = '' ) {
		return get_the_term_list($this->id, 'deal-categories', $before, $sep, $after);
	}
	
	/** Returns the deals tags */
	function get_tags( $sep = ', ', $before = '', $after = '' ) {
		return get_the_term_list($this->id, 'deal-tags', $before, $sep, $after);
	}
	
	/** Get and return related deals */
	function get_related( $limit = 5 ) {
		global $cmdeals;
		
		// Related deals are found from category and tag
		$tags_array = array(0);
		$cats_array = array(0);
		
		// Get tags
		$terms = wp_get_post_terms($this->id, 'deal-tags');
		foreach ($terms as $term) $tags_array[] = $term->term_id;
		
		// Get categories
		$terms = wp_get_post_terms($this->id, 'deal-categories');
		foreach ($terms as $term) $cats_array[] = $term->term_id;
		
		// Don't bother if none are set
		if ( sizeof($cats_array)==1 && sizeof($tags_array)==1 ) return array();
		
		// Meta query
		$meta_query = array();
                $meta_query[] = $cmdeals->query->stock_status_meta_query();
		
		// Get the posts
		$related_posts = get_posts(array(
			'orderby' 		=> 'rand',
			'posts_per_page'        => $limit,
			'post_type'             => 'daily-deals',
			'fields' 		=> 'ids',
			'meta_query'            => $meta_query,
			'tax_query'             => array(
				'relation' => 'OR',
				array(
					'taxonomy' 	=> 'deal-categories',
					'field' 	=> 'id',
					'terms' 	=> $cats_array
				),
				array(
					'taxonomy' 	=> 'deal-tags',
					'field' 	=> 'id',
					'terms' 	=> $tags_array
				)
			)
		));
		
		$related_posts = array_diff( $related_posts, array($this->id) );
		
		return $related_posts;
	}
	
	/** Returns a single deals attribute */
	function get_attribute( $attr ) {
		$attributes = $this->get_attributes();
		
		if ( isset($attributes[$attr]) ) return $attributes[$attr]['value']; else return false;
	}
	
	/** Returns deals attributes */
	function get_attributes() {
		
		if (!is_array($this->attributes)) :
	
			if (isset($this->deal_custom_fields['deal_attributes'][0])) 
				$this->attributes = maybe_unserialize( maybe_unserialize( $this->deal_custom_fields['deal_attributes'][0] )); 
			else 
				$this->attributes = array();	
		
		endif;
	
		return (array) $this->attributes;
	}
	
	/** Returns whether or not the deals has any attributes set */
	function has_attributes() {
		if (sizeof($this->get_attributes())>0) :
			foreach ($this->get_attributes() as $attribute) :
				if (isset($attribute['is_visible']) && $attribute['is_visible']) return true;
			endforeach;
		endif;
		return false;
	}
	
	/** Lists a table of attributes for the deals page */
	function list_attributes() {
		global $cmdeals;
		
		$attributes = $this->get_attributes();
		
		$show_dimensions 	= false;
		$has_dimensions 	= false;
		
		if (get_option('cmdeals_enable_dimension_deals_attributes')=='yes') :
			
			$show_dimensions 	= true;
			$weight 			= '';
			$dimensions 		= '';
			
			$length = $this->length;
			$width = $this->width;
			$height = $this->height;
			
			if ($this->get_weight()) $weight = $this->get_weight() . get_option('cmdeals_weight_unit');
			
			if (($length && $width && $height)) $dimensions = $length . get_option('cmdeals_dimension_unit') . ' x ' . $width . get_option('cmdeals_dimension_unit') . ' x ' . $height . get_option('cmdeals_dimension_unit');
			
			if ($weight || $dimensions) $has_dimensions = true;
			
		endif;	
		
		if (sizeof($attributes)>0 || ($show_dimensions && $has_dimensions)) :
			
			echo '<table class="store_attributes">';
			$alt = 1;
			
			if (($show_dimensions && $has_dimensions)) :
				
				if ($weight) :
					
					$alt = $alt*-1;
					echo '<tr class="';
					if ($alt==1) echo 'alt';
					echo '"><th>'.__('Weight', 'cmdeals').'</th><td>'.$weight.'</td></tr>';
					
				endif;
				
				if ($dimensions) :
					
					$alt = $alt*-1;
					echo '<tr class="';
					if ($alt==1) echo 'alt';
					echo '"><th>'.__('Dimensions', 'cmdeals').'</th><td>'.$dimensions.'</td></tr>';
					
				endif;
				
			endif;
			
			foreach ($attributes as $attribute) :
				if (!isset($attribute['is_visible']) || !$attribute['is_visible']) continue;
				
				$alt = $alt*-1;
				echo '<tr class="';
				if ($alt==1) echo 'alt';
				echo '"><th>'.$cmdeals->attribute_label( $attribute['name'] ).'</th><td>';
				
				if ($attribute['is_taxonomy']) :
					$post_terms = wp_get_post_terms( $this->id, $attribute['name'] );
					$values = array();
					foreach ($post_terms as $term) :
						$values[] = $term->name;
					endforeach;
					echo implode(', ', $values);
				else :
					// Convert pipes to commas
					$value = explode('|', $attribute['value']);
					$value = implode(', ', $value);
					echo wpautop(wptexturize($value));
				endif;
				
				echo '</td></tr>';
			endforeach;
			echo '</table>';

		endif;
	}
	
	/**
         * Return an array of attributes used for variations, as well as their possible values
         * 
         * @return two dimensional array of attributes and their available values
         */   
        function get_available_attribute_variations() {      

            if (!$this->is_type('variable') || !$this->has_child()) return array();

            $attributes = $this->get_attributes();

            if(!is_array($attributes)) return array();

            $available_attributes = array();

            foreach ($attributes as $attribute) {
                if (!$attribute['is_variation']) continue;

                $values = array();
                $attribute_field_name = 'attribute_'.sanitize_title($attribute['name']);

                foreach ($this->get_children() as $child_id) {

                    if (get_post_status( $child_id ) != 'publish') continue; // Disabled

                    $child = $this->get_child( $child_id );

                    $vattributes = $child->get_variation_attributes();

                    if (is_array($vattributes)) {
                        foreach ($vattributes as $name => $value) {
                            if ($name == $attribute_field_name) {
                                $values[] = $value;
                            }
                        }
                    }
                }

                // empty value indicates that all options for given attribute are available
                if(in_array('', $values)) {

                    // Get all options
                    if ($attribute['is_taxonomy']) :
                            $options = array();
                            $post_terms = wp_get_post_terms( $this->id, $attribute['name'] );
                                            foreach ($post_terms as $term) :
                                                    $options[] = $term->slug;
                                            endforeach;
                                    else :
                                            $options = explode('|', $attribute['value']);
                                    endif;

                                    $options = array_map('trim', $options);

                    $values = array_unique($options);
                } else {

                    // Order custom attributes (non taxonomy) as defined
                        if (!$attribute['is_taxonomy']) :
                            $options = explode('|', $attribute['value']);
                            $options = array_map('trim', $options);
                            $values = array_intersect( $options, $values );
                        endif;

                        $values = array_unique($values);

                }

                $available_attributes[$attribute['name']] = array_unique($values);
            }

            return $available_attributes;
        }

        /**
         * Gets the main deals image
         */ 
        function get_image( $size = 'store_thumbnail' ) {
            global $cmdeals;

            if (has_post_thumbnail($this->id)) :
                            echo get_the_post_thumbnail($this->id, $size); 
                    elseif (($parent_id = wp_get_post_parent_id( $this->id )) && has_post_thumbnail($parent_id)) :
                            echo get_the_post_thumbnail($parent_id, $size); 
                    else :
                            echo '<img src="'.$cmdeals->plugin_url(). '/cmdeals-assets/images/placeholder.png" alt="Placeholder" width="'.$cmdeals->get_image_size('store_thumbnail_image_width').'" height="'.$cmdeals->get_image_size('store_thumbnail_image_height').'" />'; 
                    endif;
        }
    
        /**
         * Gets the percent price deals.
         */  
        function get_discount() {
            
            $disc_percent   = ($this->get_price() != 0) ? round(($this->get_save_price() / $this->get_price() * 100), 1) . '%' : 0;

            return apply_filters( 'cmdeals_discount_price', $disc_percent);

        }
    
        /**
         * Gets the save price.
         */  
        function get_save_price() {
            
            $save_price     = $this->get_price() - $this->get_sale();

            return apply_filters( 'cmdeals_save_price', $save_price);

        }
        /**
         * get purchase deal
         */
        function get_purchase(){
            global $wpdb;
            $results = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."cmdeals_permissions WHERE deal_id = '%s';", $this->id) );
            return apply_filters('cmdeals_purchase_deals', $results);
        }

}