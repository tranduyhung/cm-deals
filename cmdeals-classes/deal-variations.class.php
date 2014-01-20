<?php
/**
 * Deal Variation Class
 * 
 * The CMDeals deals variation class handles deals variation data.
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

class cmdeals_deals_variation extends cmdeals_deals {
	
	var $variation_data;
	var $variation_id;
	var $variation_has_length;
	var $variation_has_width;
	var $variation_has_height;
	var $variation_has_weight;
	var $variation_has_price;
	var $variation_has_sale_price;
	var $variation_has_stock;
	
	/**
	 * Loads all deals data from custom fields
	 *
	 * @param   int		$id		ID of the deals to load
	 */
	function cmdeals_deals_variation( $variation_id, $parent_id = '', $parent_custom_fields = '' ) {
		
		$this->variation_id = $variation_id;
	
		$deal_custom_fields = get_post_custom( $this->variation_id );
		
		$this->exists = (sizeof($deal_custom_fields)>0) ? true : false;
		
		$this->variation_data = array();
		
		foreach ($deal_custom_fields as $name => $value) :
			
			if (!strstr($name, 'attribute_')) continue;
			
			$this->variation_data[$name] = $value[0];
			
		endforeach;

		/* Get main deals data from parent */
		$this->id = ($parent_id>0) ? $parent_id : wp_get_post_parent_id( $this->variation_id );
		if (!$parent_custom_fields) $parent_custom_fields = get_post_custom( $this->id );
		
		// Define the data we're going to load from the parent: Key => Default value
		$load_data = array(
			'price' 		=> 0,
			'visibility'	=> 'hidden',
			'stock'			=> 0,
			'stock_status'	=> 'instock',
			'backsales'	=> 'no',
			'manage_stock'	=> 'no',
			'_discount_price'	=> '',
			'_base_price' => '',
			'weight'		=> '',
			'length'		=> '',
			'width'			=> '',
			'height'		=> '',
			'tax_status'	=> 'taxable',
			'tax_class'		=> '',
			'upsell_ids'	=> array(),
			'crosssell_ids' => array()
		);
		
		// Load the data from the custom fields
		foreach ($load_data as $key => $default) $this->$key = (isset($parent_custom_fields[$key][0]) && $parent_custom_fields[$key][0]!=='') ? $parent_custom_fields[$key][0] : $default;

		$this->deal_type = 'variable';
		
		$this->variation_has_stock = $this->variation_has_weight = $this->variation_has_length = $this->variation_has_width = $this->variation_has_height = $this->variation_has_price = $this->variation_has_sale_price = false;
				
		/* Override parent data with variation */		
		if (isset($deal_custom_fields['stock'][0]) && $deal_custom_fields['stock'][0]!=='') :
			$this->variation_has_stock = true;
			$this->manage_stock = 'yes';
			$this->stock = $deal_custom_fields['stock'][0];
		endif;
		
		if (isset($deal_custom_fields['weight'][0]) && $deal_custom_fields['weight'][0]!=='') :
			$this->variation_has_weight = true;
			$this->weight = $deal_custom_fields['weight'][0];
		endif;
		
		if (isset($deal_custom_fields['length'][0]) && $deal_custom_fields['length'][0]!=='') :
			$this->variation_has_length = true;
			$this->length = $deal_custom_fields['length'][0];
		endif;
		
		if (isset($deal_custom_fields['width'][0]) && $deal_custom_fields['width'][0]!=='') :
			$this->variation_has_width = true;
			$this->width = $deal_custom_fields['width'][0];
		endif;
		
		if (isset($deal_custom_fields['height'][0]) && $deal_custom_fields['height'][0]!=='') :
			$this->variation_has_height = true;
			$this->height = $deal_custom_fields['height'][0];
		endif;
		
		if (isset($deal_custom_fields['price'][0]) && $deal_custom_fields['price'][0]!=='') :
			$this->variation_has_price = true;
			$this->price = $deal_custom_fields['price'][0];
			$this->_base_price = $deal_custom_fields['price'][0];
		endif;
		
		if (isset($deal_custom_fields['_discount_price'][0]) && $deal_custom_fields['_discount_price'][0]!=='') :
			$this->variation_has_sale_price = true;
			$this->sale_price = $deal_custom_fields['_discount_price'][0];
			if ($this->sale_price < $this->price) $this->price = $this->sale_price;
		endif;
		
		if (isset($deal_custom_fields['downloadable'][0]) && $deal_custom_fields['downloadable'][0]=='yes') :
			$this->downloadable = 'yes';
		else :
			$this->downloadable = 'no';
		endif;
		
		if (isset($deal_custom_fields['virtual'][0]) && $deal_custom_fields['virtual'][0]=='yes') :
			$this->virtual = 'yes';
		else :
			$this->virtual = 'no';
		endif;
		
		$this->total_stock = $this->stock;
	}
	
	/**
     * Get variation ID
     * 
     * @return int
     */
    function get_variation_id() {
        return (int) $this->variation_id;
    }
    
    /**
     * Get variation attribute values
     * 
     * @return array of attributes and their values for this variation
     */
    function get_variation_attributes() {
        return $this->variation_data;
    }
	
	/**
     * Get variation attribute values
     * 
     * @return string containing the formatted price
     */
	function get_price_html() {
		if ($this->variation_has_price || $this->variation_has_sale_price) :
			$price = '';
			
			if ($this->price) :
				if ($this->variation_has_sale_price) :
					$price .= '<del>'.cmdeals_price( $this->_base_price ).'</del> <ins>'.cmdeals_price( $this->sale_price ).'</ins>';
				else :
					$price .= cmdeals_price( $this->price );
				endif;
			endif;
	
			return $price;
		else :
			return cmdeals_price(parent::get_price());
		endif;
	}
	
	/**
     * Gets the main deals image
     */ 
    function get_image( $size = 'store_thumbnail' ) {
    	global $cmdeals;
    	
    	if ($this->variation_id && has_post_thumbnail($this->variation_id)) :
			echo get_the_post_thumbnail($this->variation_id, $size); 
		elseif (has_post_thumbnail($this->id)) :
			echo get_the_post_thumbnail($this->id, $size); 
		elseif ($parent_id = wp_get_post_parent_id( $this->id ) && has_post_thumbnail($parent_id)) :
			echo get_the_post_thumbnail($parent_id, $size); 
		else :
			echo '<img src="'.$cmdeals->plugin_url(). '/cmdeals-assets/images/placeholder.png" alt="Placeholder" width="'.$cmdeals->get_image_size('store_thumbnail_image_width').'" height="'.$cmdeals->get_image_size('store_thumbnail_image_height').'" />'; 
		endif;
    }
	
	/**
	 * Reduce stock level of the deals
	 *
	 * @param   int		$by		Amount to reduce by
	 */
	function reduce_stock( $by = 1 ) {
		if ($this->variation_has_stock) :
			if ($this->managing_stock()) :
				
				$this->stock = $this->stock - $by;
				$this->total_stock = $this->get_total_stock() - $by;
				update_post_meta($this->variation_id, 'stock', $this->stock);
				
				// Parents out of stock attribute
				if (!$this->is_in_stock()) :
				
					// Check parent
					$parent_deals = new cmdeals_deals( $this->id );
					
					if ($parent_deals->managing_stock()) :
						if (!$parent_deals->backsales_allowed()) :
							if ($parent_deals->get_total_stock()==0 || $parent_deals->get_total_stock()<0) :
								update_post_meta($this->id, 'stock_status', 'outofstock');
								$cmdeals->clear_deals_transients( $this->id ); // Clear transient
							endif;
						endif;
					else :
						if ($parent_deals->get_total_stock()==0 || $parent_deals->get_total_stock()<0) :
							update_post_meta($this->id, 'stock_status', 'outofstock');
							$cmdeals->clear_deals_transients( $this->id ); // Clear transient
						endif;
					endif;

				endif;
				
				return $this->stock;
			endif;
		else :
			return parent::reduce_stock( $by );
		endif;
	}
	
	/**
	 * Increase stock level of the deals
	 *
	 * @param   int		$by		Amount to increase by
	 */
	function increase_stock( $by = 1 ) {
		if ($this->variation_has_stock) :
			if ($this->managing_stock()) :

				$this->stock = $this->stock + $by;
				$this->total_stock = $this->get_total_stock() + $by;
				update_post_meta($this->variation_id, 'stock', $this->stock);
				
				// Parents out of stock attribute
				if ($this->is_in_stock()) update_post_meta($this->id, 'stock_status', 'instock');
				
				return $this->stock;
			endif;
		else :
			return parent::increase_stock( $by );
		endif;
	}

}