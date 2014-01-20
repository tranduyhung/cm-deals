<?php
/**
 * CMDeals countries
 * 
 * The CMDeals countries class stores country data.
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

class cmdeals_countries {
	
	var $countries;
	
	/**
	 * Constructor
	 */
	function __construct() {
	
		$this->countries = array(
			'AD' => __('Andorra', 'cmdeals'),
                        'AE' => __('United Arab Emirates', 'cmdeals'),
			'AF' => __('Afghanistan', 'cmdeals'),
			'AG' => __('Antigua and Barbuda', 'cmdeals'),
			'AI' => __('Anguilla', 'cmdeals'),
			'AL' => __('Albania', 'cmdeals'),
			'AM' => __('Armenia', 'cmdeals'),
			'AN' => __('Netherlands Antilles', 'cmdeals'),
			'AO' => __('Angola', 'cmdeals'),
			'AQ' => __('Antarctica', 'cmdeals'),
			'AR' => __('Argentina', 'cmdeals'),
			'AS' => __('American Samoa', 'cmdeals'),
			'AT' => __('Austria', 'cmdeals'),
			'AU' => __('Australia', 'cmdeals'),
			'AW' => __('Aruba', 'cmdeals'),
			'AX' => __('Aland Islands', 'cmdeals'),
			'AZ' => __('Azerbaijan', 'cmdeals'),
			'BA' => __('Bosnia and Herzegovina', 'cmdeals'),
			'BB' => __('Barbados', 'cmdeals'),
			'BD' => __('Bangladesh', 'cmdeals'),
			'BE' => __('Belgium', 'cmdeals'),
			'BF' => __('Burkina Faso', 'cmdeals'),
			'BG' => __('Bulgaria', 'cmdeals'),
			'BH' => __('Bahrain', 'cmdeals'),
			'BI' => __('Burundi', 'cmdeals'),
			'BJ' => __('Benin', 'cmdeals'),
			'BL' => __('Saint Barth‚àö¬©lemy', 'cmdeals'),
			'BM' => __('Bermuda', 'cmdeals'),
			'BN' => __('Brunei', 'cmdeals'),
			'BO' => __('Bolivia', 'cmdeals'),
			'BR' => __('Brazil', 'cmdeals'),
			'BS' => __('Bahamas', 'cmdeals'),
			'BT' => __('Bhutan', 'cmdeals'),
			'BV' => __('Bouvet Island', 'cmdeals'),
			'BW' => __('Botswana', 'cmdeals'),
			'BY' => __('Belarus', 'cmdeals'),
			'BZ' => __('Belize', 'cmdeals'),
			'CA' => __('Canada', 'cmdeals'),
			'CC' => __('Cocos (Keeling) Islands', 'cmdeals'),
			'CD' => __('Congo (Kinshasa)', 'cmdeals'),
			'CF' => __('Central African Republic', 'cmdeals'),
			'CG' => __('Congo (Brazzaville)', 'cmdeals'),
			'CH' => __('Switzerland', 'cmdeals'),
			'CI' => __('Ivory Coast', 'cmdeals'),
			'CK' => __('Cook Islands', 'cmdeals'),
			'CL' => __('Chile', 'cmdeals'),
			'CM' => __('Cameroon', 'cmdeals'),
			'CN' => __('China', 'cmdeals'),
			'CO' => __('Colombia', 'cmdeals'),
			'CR' => __('Costa Rica', 'cmdeals'),
			'CU' => __('Cuba', 'cmdeals'),
			'CV' => __('Cape Verde', 'cmdeals'),
			'CX' => __('Christmas Island', 'cmdeals'),
			'CY' => __('Cyprus', 'cmdeals'),
			'CZ' => __('Czech Republic', 'cmdeals'),
			'DE' => __('Germany', 'cmdeals'),
			'DJ' => __('Djibouti', 'cmdeals'),
			'DK' => __('Denmark', 'cmdeals'),
			'DM' => __('Dominica', 'cmdeals'),
			'DO' => __('Dominican Republic', 'cmdeals'),
			'DZ' => __('Algeria', 'cmdeals'),
			'EC' => __('Ecuador', 'cmdeals'),
			'EE' => __('Estonia', 'cmdeals'),
			'EG' => __('Egypt', 'cmdeals'),
			'EH' => __('Western Sahara', 'cmdeals'),
			'ER' => __('Eritrea', 'cmdeals'),
			'ES' => __('Spain', 'cmdeals'),
			'ET' => __('Ethiopia', 'cmdeals'),
			'FI' => __('Finland', 'cmdeals'),
			'FJ' => __('Fiji', 'cmdeals'),
			'FK' => __('Falkland Islands', 'cmdeals'),
			'FM' => __('Micronesia', 'cmdeals'),
			'FO' => __('Faroe Islands', 'cmdeals'),
			'FR' => __('France', 'cmdeals'),
			'GA' => __('Gabon', 'cmdeals'),
			'GB' => __('United Kingdom', 'cmdeals'),
			'GD' => __('Grenada', 'cmdeals'),
			'GE' => __('Georgia', 'cmdeals'),
			'GF' => __('French Guiana', 'cmdeals'),
			'GG' => __('Guernsey', 'cmdeals'),
			'GH' => __('Ghana', 'cmdeals'),
			'GI' => __('Gibraltar', 'cmdeals'),
			'GL' => __('Greenland', 'cmdeals'),
			'GM' => __('Gambia', 'cmdeals'),
			'GN' => __('Guinea', 'cmdeals'),
			'GP' => __('Guadeloupe', 'cmdeals'),
			'GQ' => __('Equatorial Guinea', 'cmdeals'),
			'GR' => __('Greece', 'cmdeals'),
			'GS' => __('South Georgia/Sandwich Islands', 'cmdeals'),
			'GT' => __('Guatemala', 'cmdeals'),
			'GU' => __('Guam', 'cmdeals'),
			'GW' => __('Guinea-Bissau', 'cmdeals'),
			'GY' => __('Guyana', 'cmdeals'),
			'HK' => __('Hong Kong S.A.R., China', 'cmdeals'),
			//'HM' => __('Heard Island and McDonald Islands', 'cmdeals'), // Uninhabitted :)
			'HN' => __('Honduras', 'cmdeals'),
			'HR' => __('Croatia', 'cmdeals'),
			'HT' => __('Haiti', 'cmdeals'),
			'HU' => __('Hungary', 'cmdeals'),
			'ID' => __('Indonesia', 'cmdeals'),
			'IE' => __('Ireland', 'cmdeals'),
			'IL' => __('Israel', 'cmdeals'),
			'IM' => __('Isle of Man', 'cmdeals'),
			'IN' => __('India', 'cmdeals'),
			'IO' => __('British Indian Ocean Territory', 'cmdeals'),
			'IQ' => __('Iraq', 'cmdeals'),
			'IR' => __('Iran', 'cmdeals'),
			'IS' => __('Iceland', 'cmdeals'),
			'IT' => __('Italy', 'cmdeals'),
			'JE' => __('Jersey', 'cmdeals'),
			'JM' => __('Jamaica', 'cmdeals'),
			'JO' => __('Jordan', 'cmdeals'),
			'JP' => __('Japan', 'cmdeals'),
			'KE' => __('Kenya', 'cmdeals'),
			'KG' => __('Kyrgyzstan', 'cmdeals'),
			'KH' => __('Cambodia', 'cmdeals'),
			'KI' => __('Kiribati', 'cmdeals'),
			'KM' => __('Comoros', 'cmdeals'),
			'KN' => __('Saint Kitts and Nevis', 'cmdeals'),
			'KP' => __('North Korea', 'cmdeals'),
			'KR' => __('South Korea', 'cmdeals'),
			'KW' => __('Kuwait', 'cmdeals'),
			'KY' => __('Cayman Islands', 'cmdeals'),
			'KZ' => __('Kazakhstan', 'cmdeals'),
			'LA' => __('Laos', 'cmdeals'),
			'LB' => __('Lebanon', 'cmdeals'),
			'LC' => __('Saint Lucia', 'cmdeals'),
			'LI' => __('Liechtenstein', 'cmdeals'),
			'LK' => __('Sri Lanka', 'cmdeals'),
			'LR' => __('Liberia', 'cmdeals'),
			'LS' => __('Lesotho', 'cmdeals'),
			'LT' => __('Lithuania', 'cmdeals'),
			'LU' => __('Luxembourg', 'cmdeals'),
			'LV' => __('Latvia', 'cmdeals'),
			'LY' => __('Libya', 'cmdeals'),
			'MA' => __('Morocco', 'cmdeals'),
			'MC' => __('Monaco', 'cmdeals'),
			'MD' => __('Moldova', 'cmdeals'),
			'ME' => __('Montenegro', 'cmdeals'),
			'MF' => __('Saint Martin (French part)', 'cmdeals'),
			'MG' => __('Madagascar', 'cmdeals'),
			'MH' => __('Marshall Islands', 'cmdeals'),
			'MK' => __('Macedonia', 'cmdeals'),
			'ML' => __('Mali', 'cmdeals'),
			'MM' => __('Myanmar', 'cmdeals'),
			'MN' => __('Mongolia', 'cmdeals'),
			'MO' => __('Macao S.A.R., China', 'cmdeals'),
			'MP' => __('Northern Mariana Islands', 'cmdeals'),
			'MQ' => __('Martinique', 'cmdeals'),
			'MR' => __('Mauritania', 'cmdeals'),
			'MS' => __('Montserrat', 'cmdeals'),
			'MT' => __('Malta', 'cmdeals'),
			'MU' => __('Mauritius', 'cmdeals'),
			'MV' => __('Maldives', 'cmdeals'),
			'MW' => __('Malawi', 'cmdeals'),
			'MX' => __('Mexico', 'cmdeals'),
			'MY' => __('Malaysia', 'cmdeals'),
			'MZ' => __('Mozambique', 'cmdeals'),
			'NA' => __('Namibia', 'cmdeals'),
			'NC' => __('New Caledonia', 'cmdeals'),
			'NE' => __('Niger', 'cmdeals'),
			'NF' => __('Norfolk Island', 'cmdeals'),
			'NG' => __('Nigeria', 'cmdeals'),
			'NI' => __('Nicaragua', 'cmdeals'),
			'NL' => __('Netherlands', 'cmdeals'),
			'NO' => __('Norway', 'cmdeals'),
			'NP' => __('Nepal', 'cmdeals'),
			'NR' => __('Nauru', 'cmdeals'),
			'NU' => __('Niue', 'cmdeals'),
			'NZ' => __('New Zealand', 'cmdeals'),
			'OM' => __('Oman', 'cmdeals'),
			'PA' => __('Panama', 'cmdeals'),
			'PE' => __('Peru', 'cmdeals'),
			'PF' => __('French Polynesia', 'cmdeals'),
			'PG' => __('Papua New Guinea', 'cmdeals'),
			'PH' => __('Philippines', 'cmdeals'),
			'PK' => __('Pakistan', 'cmdeals'),
			'PL' => __('Poland', 'cmdeals'),
			'PM' => __('Saint Pierre and Miquelon', 'cmdeals'),
			'PN' => __('Pitcairn', 'cmdeals'),
			'PR' => __('Puerto Rico', 'cmdeals'),
			'PS' => __('Palestinian Territory', 'cmdeals'),
			'PT' => __('Portugal', 'cmdeals'),
			'PW' => __('Palau', 'cmdeals'),
			'PY' => __('Paraguay', 'cmdeals'),
			'QA' => __('Qatar', 'cmdeals'),
			'RE' => __('Reunion', 'cmdeals'),
			'RO' => __('Romania', 'cmdeals'),
			'RS' => __('Serbia', 'cmdeals'),
			'RU' => __('Russia', 'cmdeals'),
			'RW' => __('Rwanda', 'cmdeals'),
			'SA' => __('Saudi Arabia', 'cmdeals'),
			'SB' => __('Solomon Islands', 'cmdeals'),
			'SC' => __('Seychelles', 'cmdeals'),
			'SD' => __('Sudan', 'cmdeals'),
			'SE' => __('Sweden', 'cmdeals'),
			'SG' => __('Singapore', 'cmdeals'),
			'SH' => __('Saint Helena', 'cmdeals'),
			'SI' => __('Slovenia', 'cmdeals'),
			'SJ' => __('Svalbard and Jan Mayen', 'cmdeals'),
			'SK' => __('Slovakia', 'cmdeals'),
			'SL' => __('Sierra Leone', 'cmdeals'),
			'SM' => __('San Marino', 'cmdeals'),
			'SN' => __('Senegal', 'cmdeals'),
			'SO' => __('Somalia', 'cmdeals'),
			'SR' => __('Suriname', 'cmdeals'),
			'ST' => __('Sao Tome and Principe', 'cmdeals'),
			'SV' => __('El Salvador', 'cmdeals'),
			'SY' => __('Syria', 'cmdeals'),
			'SZ' => __('Swaziland', 'cmdeals'),
			'TC' => __('Turks and Caicos Islands', 'cmdeals'),
			'TD' => __('Chad', 'cmdeals'),
			'TF' => __('French Southern Territories', 'cmdeals'),
			'TG' => __('Togo', 'cmdeals'),
			'TH' => __('Thailand', 'cmdeals'),
			'TJ' => __('Tajikistan', 'cmdeals'),
			'TK' => __('Tokelau', 'cmdeals'),
			'TL' => __('Timor-Leste', 'cmdeals'),
			'TM' => __('Turkmenistan', 'cmdeals'),
			'TN' => __('Tunisia', 'cmdeals'),
			'TO' => __('Tonga', 'cmdeals'),
			'TR' => __('Turkey', 'cmdeals'),
			'TT' => __('Trinidad and Tobago', 'cmdeals'),
			'TV' => __('Tuvalu', 'cmdeals'),
			'TW' => __('Taiwan', 'cmdeals'),
			'TZ' => __('Tanzania', 'cmdeals'),
			'UA' => __('Ukraine', 'cmdeals'),
			'UG' => __('Uganda', 'cmdeals'),
			'UM' => __('US Minor Outlying Islands', 'cmdeals'),
			'US' => __('United States', 'cmdeals'),
			'USAF' => __('US Armed Forces', 'cmdeals'), 
			'UY' => __('Uruguay', 'cmdeals'),
			'UZ' => __('Uzbekistan', 'cmdeals'),
			'VA' => __('Vatican', 'cmdeals'),
			'VC' => __('Saint Vincent and the Grenadines', 'cmdeals'),
			'VE' => __('Venezuela', 'cmdeals'),
			'VG' => __('British Virgin Islands', 'cmdeals'),
			'VI' => __('U.S. Virgin Islands', 'cmdeals'),
			'VN' => __('Vietnam', 'cmdeals'),
			'VU' => __('Vanuatu', 'cmdeals'),
			'WF' => __('Wallis and Futuna', 'cmdeals'),
			'WS' => __('Samoa', 'cmdeals'),
			'YE' => __('Yemen', 'cmdeals'),
			'YT' => __('Mayotte', 'cmdeals'),
			'ZA' => __('South Africa', 'cmdeals'),
			'ZM' => __('Zambia', 'cmdeals'),
			'ZW' => __('Zimbabwe', 'cmdeals')
		);

		asort($this->countries);

	}
	
	/** get base country */
	function get_base_country() {
		$default = get_option('cmdeals_default_country');
    	if (strstr($default, ':')) :
    		$country = current(explode(':', $default));
    	else :
    		$country = $default;
    	endif;
		
		return $country;	    	
	}
	
	/** get countries we allow only */
	function get_allowed_countries() {
	
		$countries = $this->countries;
		
		if (get_option('cmdeals_allowed_countries')!=='specific') return $countries;

		$allowed_countries = array();
		
		$allowed_countries_raw = get_option('cmdeals_specific_allowed_countries');
		
		foreach ($allowed_countries_raw as $country) :
			
			$allowed_countries[$country] = $countries[$country];
			
		endforeach;
		
		asort($allowed_countries);
		
		return $allowed_countries;
	}
	
	/** Gets an array of countries in the EU */
	function get_european_union_countries() {
		return array('AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK');
	}
	
	/** Gets the correct string for shipping - ether 'to the' or 'to' */
	function shipping_to_prefix() {
		global $cmdeals;
		$return = '';
		if (in_array($cmdeals->customer->get_country(), array( 'GB', 'US', 'AE', 'CZ', 'DO', 'NL', 'PH', 'USAF' ))) $return = __('to the', 'cmdeals');
		else $return = __('to', 'cmdeals');
		return apply_filters('cmdeals_countries_shipping_to_prefix', $return, $cmdeals->customer->get_shipping_country());
	}
	
	/** Prefix certain countries with 'the' */
	function estimated_for_prefix() {
		global $cmdeals;
		$return = '';
		if (in_array($cmdeals->customer->get_country(), array( 'GB', 'US', 'AE', 'CZ', 'DO', 'NL', 'PH', 'USAF' ))) $return = __('the', 'cmdeals') . ' ';
		return apply_filters('cmdeals_countries_estimated_for_prefix', $return, $cmdeals->customer->get_shipping_country());
	}
	
	/** Correctly name tax in some countries VAT on the frontend */
	function tax_or_vat() {
		global $cmdeals;
		
		$return = ( in_array($this->get_base_country(), $this->get_european_union_countries()) ) ? __('VAT', 'cmdeals') : __('Tax', 'cmdeals');
		
		return apply_filters('cmdeals_countries_tax_or_vat', $return);
	}
	
	function inc_tax_or_vat( $rate = false ) {
		global $cmdeals;
		
		if ( $rate > 0 || $rate === 0 ) :
			$rate = rtrim(rtrim($rate, '0'), '.');
			if (!$rate) $rate = 0;
			$return = ( in_array($this->get_base_country(), $this->get_european_union_countries()) ) ? sprintf(__('(inc. %s%% VAT)', 'cmdeals'), $rate) : sprintf(__('(inc. %s%% tax)', 'cmdeals'), $rate);
		else :
			$return = ( in_array($this->get_base_country(), $this->get_european_union_countries()) ) ? __('(inc. VAT)', 'cmdeals') : __('(inc. tax)', 'cmdeals');
		endif;
		
		return apply_filters('cmdeals_countries_inc_tax_or_vat', $return, $rate);
	}
	
	function ex_tax_or_vat() {
		global $cmdeals;
		
		$return = ( in_array($this->get_base_country(), $this->get_european_union_countries()) ) ? __('(ex. VAT)', 'cmdeals') : __('(ex. tax)', 'cmdeals');
		
		return apply_filters('cmdeals_countries_ex_tax_or_vat', $return);
	}
	
	/** Outputs the list of countries for use in dropdown boxes */
	function country_dropdown_options( $selected_country = '', $escape=false ) {
		
		$countries = $this->countries;
		
		if ( $countries ) foreach ( $countries as $key=>$value) :
			echo '<option';
			if ($selected_country==$key) echo ' selected="selected"';
			echo ' value="'.$key.'">'. ($escape ? esc_js( __($value, 'cmdeals') ) : __($value, 'cmdeals') ) .'</option>';
		endforeach;
	}
}

