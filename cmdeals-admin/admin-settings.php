<?php
/**
 * Functions for the settings page in admin.
 * 
 * The settings page contains options for the CMDeals plugin - this file contains functions to display
 * and save the list of options.
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
 * Define settings for the CMDeals settings pages
 */
global $cmdeals_settings;

$cmdeals_settings['general'] = apply_filters('cmdeals_general_settings', array(

	array( 'name' => __( 'General Options', 'cmdeals' ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

	array(  
		'name' => __( 'Base Country/Region', 'cmdeals' ),
		'desc' 		=> __( 'This is the base country for your business.', 'cmdeals' ),
		'id' 		=> 'cmdeals_default_country',
		'css' 		=> 'min-width:300px;',
		'std' 		=> 'GB',
		'type' 		=> 'single_select_country'
	),
	
	array(  
		'name' => __( 'Currency', 'cmdeals' ),
		'desc' 		=> __("This controls what currency prices are listed at in the deals and which currency gateways will take payments in.", 'cmdeals' ),
		'tip' 		=> '',
		'id' 		=> 'cmdeals_currency',
		'css' 		=> 'min-width:300px;',
		'std' 		=> 'GBP',
		'type' 		=> 'select',
		'class'		=> 'chosen_select',
		'options' => array_unique(apply_filters('cmdeals_currencies', array( 
			'USD' => __( 'US Dollars (&#36;)', 'cmdeals' ),
			'EUR' => __( 'Euros (&euro;)', 'cmdeals' ),
			'GBP' => __( 'Pounds Sterling (&pound;)', 'cmdeals' ),
			'AUD' => __( 'Australian Dollars (&#36;)', 'cmdeals' ),
			'BRL' => __( 'Brazilian Real (&#36;)', 'cmdeals' ),
			'CAD' => __( 'Canadian Dollars (&#36;)', 'cmdeals' ),
			'CZK' => __( 'Czech Koruna (&#75;&#269;)', 'cmdeals' ),
			'DKK' => __( 'Danish Krone', 'cmdeals' ),
			'HKD' => __( 'Hong Kong Dollar (&#36;)', 'cmdeals' ),
			'HUF' => __( 'Hungarian Forint', 'cmdeals' ),
			'IDR' => __( 'Indonesia (IDR)', 'cmdeals' ),
			'ILS' => __( 'Israeli Shekel', 'cmdeals' ),
			'JPY' => __( 'Japanese Yen (&yen;)', 'cmdeals' ),
			'MYR' => __( 'Malaysian Ringgits', 'cmdeals' ),
			'MXN' => __( 'Mexican Peso (&#36;)', 'cmdeals' ),
			'NZD' => __( 'New Zealand Dollar (&#36;)', 'cmdeals' ),
			'NOK' => __( 'Norwegian Krone', 'cmdeals' ),
			'PHP' => __( 'Philippine Pesos', 'cmdeals' ),
			'PLN' => __( 'Polish Zloty', 'cmdeals' ),
			'SGD' => __( 'Singapore Dollar (&#36;)', 'cmdeals' ),
			'SEK' => __( 'Swedish Krona', 'cmdeals' ),
			'CHF' => __( 'Swiss Franc', 'cmdeals' ),
			'TWD' => __( 'Taiwan New Dollars', 'cmdeals' ),
			'THB' => __( 'Thai Baht', 'cmdeals' ), 
			'TRY' => __( 'Turkish Lira (TL)', 'cmdeals' ),
			'ZAR' => __( 'South African rand (R)', 'cmdeals' ),
			))
		)
	),
	
	array(  
		'name' => __( 'Checkout', 'cmdeals' ),
		'desc' 		=> __( 'Allow users to create an account and login from the checkout page', 'cmdeals' ),
		'id' 		=> 'cmdeals_enable_signup_and_login_from_checkout',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'start'
	),
	
	array(  
		'desc' 		=> __( 'Show order comments section', 'cmdeals' ),
		'id' 		=> 'cmdeals_enable_order_comments',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> ''
	),
	
	array(  
		'desc' 		=> __( 'Force <abbr title="Secure Sockets Layer, a computing protocol that ensures the security of data sent via the Internet by using encryption">SSL</abbr>/HTTPS (an SSL Certificate is required)', 'cmdeals' ),
		'id' 		=> 'cmdeals_force_ssl_checkout',
		'std' 		=> 'no',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> ''
	),
	
	array(  
		'desc' 		=> __( 'Un-force <abbr title="Secure Sockets Layer, a computing protocol that ensures the security of data sent via the Internet by using encryption">SSL</abbr>/HTTPS when leaving the checkout', 'cmdeals' ),
		'id' 		=> 'cmdeals_unforce_ssl_checkout',
		'std' 		=> 'no',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'end'
	),
	
	array(  
		'name' => __( 'Customer Accounts', 'cmdeals' ),
		'desc' 		=> __( 'Allow unregistered users to register from the My Account page', 'cmdeals' ),
		'id' 		=> 'cmdeals_enable_myaccount_registration',
		'std' 		=> 'no',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'start'
	),
	
	array(  
		'desc' 		=> __( 'Clear cart when logging out', 'cmdeals' ),
		'id' 		=> 'cmdeals_clear_cart_on_logout',
		'std' 		=> 'no',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> ''
	),
	
	array(  
		'desc' 		=> __( 'Prevent customers from accessing WordPress admin', 'cmdeals' ),
		'id' 		=> 'cmdeals_lock_down_admin',
		'std' 		=> 'no',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'end'
	),
	
	array(  
		'name' => __( 'Styling', 'cmdeals' ),
		'desc' 		=> __( 'Enable CMDeals CSS styles', 'cmdeals' ),
		'id' 		=> 'cmdeals_frontend_css',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'start'
	),
	
	array(  
		'desc' 		=> __( 'Enable the "Demo Store" notice on your site', 'cmdeals' ),
		'id' 		=> 'cmdeals_demo_store',
		'std' 		=> 'no',
		'type' 		=> 'checkbox',
		'checkboxgroup'	=> 'end'
	),
	
	array(  
		'name' => __( 'Scripts', 'cmdeals' ),
		'desc' 		=> __( 'Enable CMDeals lightbox on the deals page', 'cmdeals' ),
		'id' 		=> 'cmdeals_enable_lightbox',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'start'
	),
	
	array(  
		'desc' 		=> __( 'Enable jQuery UI (used by the price slider widget)', 'cmdeals' ),
		'id' 		=> 'cmdeals_enable_jquery_ui',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> ''
	),
	
	array(  
	    'desc'     => __( 'Output CMDeals JavaScript in the footer (<code>wp_footer</code>)', 'cmdeals' ),
	    'id'     => 'cmdeals_scripts_position',
	    'std'     => 'yes',
	    'type'     => 'checkbox',
	    'checkboxgroup'		=> 'end'
	),

	array(  
		'name' => __('File download method', 'cmdeals'),
		'desc' 		=> __('Forcing downloads will keep URLs hidden, but some servers may serve large files unreliably. If supported, <code>X-Accel-Redirect</code>/ <code>X-Sendfile</code> can be used to serve downloads instead (server requires <code>mod_xsendfile</code>).', 'cmdeals'),
		'id' 		=> 'cmdeals_file_download_method',
		'type' 		=> 'select',
		'class'		=> 'chosen_select',
		'css' 		=> 'min-width:300px;',
		'std'		=> 'force',
		'options' => array(  
			'force'  	=> __( 'Force Downloads', 'cmdeals' ),
			'xsendfile' => __( 'X-Accel-Redirect/X-Sendfile', 'cmdeals' ),
			'redirect'  => __( 'Redirect only', 'cmdeals' ),	
		)
	),
	
	array( 'type' => 'sectionend', 'id' => 'general_options'),
	
	array( 'name' => __( 'ShareThis', 'cmdeals' ), 'type' => 'title', 'desc' => '', 'id' => 'share_this' ),

	array(  
		'name' => __( 'Share Button', 'cmdeals' ),
		'desc' 		=> __( 'Display share button on single deal', 'cmdeals' ),
		'id' 		=> 'cmdeals_show_share',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox'
	),
	array(  
		'name' => __( 'ShareThis Publisher ID', 'cmdeals' ),
		'desc' 		=> sprintf( __( 'Enter your %1$sShareThis publisher ID%2$s to show social sharing buttons on deals pages.', 'cmdeals' ), '<a href="http://sharethis.com/account/">', '</a>' ),
		'id' 		=> 'cmdeals_sharethis',
		'type' 		=> 'text',
		'std' 		=> '',
                'css' 		=> 'min-width:300px;',
	),
	
	array( 'type' => 'sectionend', 'id' => 'share_this'),
	
	array( 'name' => __( 'Google Analytics', 'cmdeals' ), 'type' => 'title', 'desc' => '', 'id' => 'google_analytics' ),
	
	array(  
		'name' => __('Google Analytics ID', 'cmdeals'),
		'desc' 		=> __('Log into your google analytics account to find your ID. e.g. <code>UA-XXXXX-X</code>', 'cmdeals'),
		'id' 		=> 'cmdeals_ga_id',
		'type' 		=> 'text',
        'css' 		=> 'min-width:300px;',
	),
	
	array(  
		'name' => __('Tracking code', 'cmdeals'),
		'desc' 		=> __('Add tracking code to your site\'s footer. You don\'t need to enable this if using a 3rd party analytics plugin.', 'cmdeals'),
		'id' 		=> 'cmdeals_ga_standard_tracking_enabled',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'start'
	),
	
	array(  
		'name' => __('Tracking code', 'cmdeals'),
		'desc' 		=> __('Add eCommerce tracking code to the thankyou page', 'cmdeals'),
		'id' 		=> 'cmdeals_ga_ecommerce_tracking_enabled',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'end'
	),
					
	array( 'type' => 'sectionend', 'id' => 'google_analytics'),

)); // End general settings

$store_page_id = get_option('cmdeals_store_page_id');
$base_slug = ($store_page_id > 0 && get_page( $store_page_id )) ? get_page_uri( $store_page_id ) : 'store';	
	
$cmdeals_settings['pages'] = apply_filters('cmdeals_page_settings', array(

	array( 'name' => __( 'Page Setup', 'cmdeals' ), 'type' => 'title', 'desc' => '', 'id' => 'page_options' ),
	
	array(  
		'name' => __( 'Store Base Page', 'cmdeals' ),
		'desc' 		=> sprintf( __( 'This sets the base page of your store.', 'cmdeals' ), '<a target="_blank" href="options-permalink.php">', '</a>' ),
		'id' 		=> 'cmdeals_store_page_id',
		'type' 		=> 'single_select_page',
		'std' 		=> '',
		'class'		=> 'chosen_select_nostd',
		'css' 		=> 'min-width:300px;',
	),
	
	array(  
		'name' => __( 'Base Page Title', 'cmdeals' ),
		'desc' 		=> __( 'This title to show on the store base page. Leave blank to use the page title.', 'cmdeals' ),
		'id' 		=> 'cmdeals_store_page_title',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> 'All Deals' // Default value for the page title - changed in settings
	),

	array(  
		'name' => __( 'Terms page ID', 'cmdeals' ),
		'desc' 		=> __( 'If you define a "Terms" page the customer will be asked if they accept them when checking out.', 'cmdeals' ),
		'tip' 		=> '',
		'id' 		=> 'cmdeals_terms_page_id',
		'std' 		=> '',
		'class'		=> 'chosen_select_nostd',
		'css' 		=> 'min-width:300px;',
		'type' 		=> 'single_select_page'
	),
	
	array( 'type' => 'sectionend', 'id' => 'page_options' ),
	
	array( 'name' => __( 'Permalinks', 'cmdeals' ), 'type' => 'title', 'desc' => '', 'id' => 'permalink_options' ),
	
	array(  
		'name' => __( 'Taxonomy base page', 'cmdeals' ),
		'desc' 		=> sprintf(__( 'Prepend store categories/tags with store base page (<code>%s</code>)', 'cmdeals' ), $base_slug),
		'id' 		=> 'cmdeals_prepend_store_page_to_urls',
		'std' 		=> 'no',
		'type' 		=> 'checkbox',
	),
	
	array(  
		'name' => __( 'Deal category slug', 'cmdeals' ),
		'desc' 		=> __( 'Shows in the deals category URLs. Leave blank to use the default slug.', 'cmdeals' ),
		'id' 		=> 'cmdeals_deal_category_slug',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> ''
	),
	
	array(  
		'name' => __( 'Deal tag slug', 'cmdeals' ),
		'desc' 		=> __( 'Shows in the deals tag URLs. Leave blank to use the default slug.', 'cmdeals' ),
		'id' 		=> 'cmdeals_deal_tags_slug',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> ''
	),
	
	array(  
		'name' => __( 'Deal base page', 'cmdeals' ),
		'desc' 		=> sprintf(__( 'Prepend deals permalinks with store base page (<code>%s</code>)', 'cmdeals' ), $base_slug),
		'id' 		=> 'cmdeals_prepend_store_page_to_deals',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'start'
	),
	
	array(  
		'name' => __( 'Deal base category', 'cmdeals' ),
		'desc' 		=> __( 'Prepend deals permalinks with deals category', 'cmdeals' ),
		'id' 		=> 'cmdeals_prepend_category_to_deals',
		'std' 		=> 'no',
		'type' 		=> 'checkbox',
		'checkboxgroup'		=> 'end'
	),
	
	array( 'type' => 'sectionend', 'id' => 'permalink_options' ),
	
	array( 'name' => __( 'Store Pages', 'cmdeals' ), 'type' => 'title', 'desc' => __( 'The following pages need selecting so that CMDeals knows which are which. These pages should have been created upon installation of the plugin.', 'cmdeals' ) ),
		
	array(  
		'name' => __( 'Featured Page', 'cmdeals' ),
		'desc' 		=> '',
		'id' 		=> 'cmdeals_featured_page_id',
		'type' 		=> 'single_select_page',
		'std' 		=> '',
		'class'		=> 'chosen_select_nostd',
		'css' 		=> 'min-width:300px;',
	),
	
	array(  
		'name' => __( 'Checkout Page', 'cmdeals' ),
		'desc' 		=> __( 'Page contents: [cmdeals_checkout]', 'cmdeals' ),
		'id' 		=> 'cmdeals_checkout_page_id',
		'type' 		=> 'single_select_page',
		'std' 		=> '',
		'class'		=> 'chosen_select_nostd',
		'css' 		=> 'min-width:300px;',
	),
	
	array(  
		'name' => __( 'Pay Page', 'cmdeals' ),
		'desc' 		=> __( 'Page contents: [cmdeals_pay] Parent: "Checkout"', 'cmdeals' ),
		'id' 		=> 'cmdeals_pay_page_id',
		'type' 		=> 'single_select_page',
		'std' 		=> '',
		'class'		=> 'chosen_select_nostd',
		'css' 		=> 'min-width:300px;',
	),
	
	array(  
		'name' => __('Thanks Page', 'cmdeals'),
		'desc' 		=> __( 'Page contents: [cmdeals_thankyou] Parent: "Checkout"', 'cmdeals' ),
		'id' 		=> 'cmdeals_thanks_page_id',
		'type' 		=> 'single_select_page',
		'std' 		=> '',
		'class'		=> 'chosen_select_nostd',
		'css' 		=> 'min-width:300px;',
	),
	
	array(  
		'name' => __( 'My Account Page', 'cmdeals' ),
		'desc' 		=> __( 'Page contents: [cmdeals_my_account]', 'cmdeals' ),
		'id' 		=> 'cmdeals_myaccount_page_id',
		'type' 		=> 'single_select_page',
		'std' 		=> '',
		'class'		=> 'chosen_select_nostd',
		'css' 		=> 'min-width:300px;',
	),
    
	array(  
		'name' => __( 'View Order Page', 'cmdeals' ),
		'desc' 		=> __( 'Page contents: [cmdeals_view_order] Parent: "My Account"', 'cmdeals' ),
		'id' 		=> 'cmdeals_view_order_page_id',
		'type' 		=> 'single_select_page',
		'std' 		=> '',
		'class'		=> 'chosen_select_nostd',
		'css' 		=> 'min-width:300px;',
	),
	
	array(  
		'name' => __( 'Change Password Page', 'cmdeals' ),
		'desc' 		=> __( 'Page contents: [cmdeals_change_password] Parent: "My Account"', 'cmdeals' ),
		'id' 		=> 'cmdeals_change_password_page_id',
		'type' 		=> 'single_select_page',
		'std' 		=> '',
		'class'		=> 'chosen_select_nostd',
		'css' 		=> 'min-width:300px;',
	),	
	
	array( 'type' => 'sectionend', 'id' => 'page_options'),

)); // End pages settings


$cmdeals_settings['deals'] = apply_filters('cmdeals_deals_settings', array(
	
	array( 'type' => 'sectionend', 'id' => 'deals_options' ),
	
	array(	'name' => __( 'Pricing Options', 'cmdeals' ), 'type' => 'title','desc' => '', 'id' => 'pricing_options' ),
	
	array(  
		'name' => __( 'Currency Position', 'cmdeals' ),
		'desc' 		=> __( 'This controls the position of the currency symbol.', 'cmdeals' ),
		'tip' 		=> '',
		'id' 		=> 'cmdeals_currency_pos',
		'css' 		=> 'min-width:150px;',
		'std' 		=> 'left',
		'type' 		=> 'select',
		'options' => array( 
			'left' => __( 'Left', 'cmdeals' ),
			'right' => __( 'Right', 'cmdeals' ),
			'left_space' => __( 'Left (with space)', 'cmdeals' ),
			'right_space' => __( 'Right (with space)', 'cmdeals' )
		)
	),
	
	array(  
		'name' => __( 'Thousand separator', 'cmdeals' ),
		'desc' 		=> __( 'This sets the thousand separator of displayed prices.', 'cmdeals' ),
		'tip' 		=> '',
		'id' 		=> 'cmdeals_price_thousand_sep',
		'css' 		=> 'width:30px;',
		'std' 		=> ',',
		'type' 		=> 'text',
	),
	
	array(  
		'name' => __( 'Decimal separator', 'cmdeals' ),
		'desc' 		=> __( 'This sets the decimal separator of displayed prices.', 'cmdeals' ),
		'tip' 		=> '',
		'id' 		=> 'cmdeals_price_decimal_sep',
		'css' 		=> 'width:30px;',
		'std' 		=> '.',
		'type' 		=> 'text',
	),
	
	array(  
		'name' => __( 'Number of decimals', 'cmdeals' ),
		'desc' 		=> __( 'This sets the number of decimal points shown in displayed prices.', 'cmdeals' ),
		'tip' 		=> '',
		'id' 		=> 'cmdeals_price_num_decimals',
		'css' 		=> 'width:30px;',
		'std' 		=> '2',
		'type' 		=> 'text',
	),
	
	array(  
		'name'		=> __( 'Trim zeros', 'cmdeals' ),
		'desc' 		=> __( 'Trim zeros after the decimal point when displaying prices', 'cmdeals' ),
		'id' 		=> 'cmdeals_price_trim_zeros',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox'
	),
	
	array( 'type' => 'sectionend', 'id' => 'pricing_options' ),
	
	array(	'name' => __( 'Image Options', 'cmdeals' ), 'type' => 'title','desc' => sprintf(__('These settings affect the actual dimensions of images in your deals - the display on the front-end will still be affected by CSS styles. After changing these settings you may need to <a href="%s">regenerate your thumbnails</a>.', 'cmdeals'), 'http://wordpress.org/extend/plugins/regenerate-thumbnails/'), 'id' => 'image_options' ),
	
	array(  
		'name' => __( 'Deals Images', 'cmdeals' ),
		'desc' 		=> __('This size is usually used in deals listings', 'cmdeals'),
		'id' 		=> 'cmdeals_deals_image',
		'css' 		=> '',
		'type' 		=> 'image_width',
		'std' 		=> '200'
	),

	array(  
		'name' => __( 'Single Deal Image', 'cmdeals' ),
		'desc' 		=> __('This is the size used by the main image on the deals page.', 'cmdeals'),
		'id' 		=> 'cmdeals_single_image',
		'css' 		=> '',
		'type' 		=> 'image_width',
		'std' 		=> '300'
	),
	
	array(  
		'name' => __( 'Deal Thumbnails', 'cmdeals' ),
		'desc' 		=> __('This size is usually used for the gallery of images on the deals page.', 'cmdeals'),
		'id' 		=> 'cmdeals_thumbnail_image',
		'css' 		=> '',
		'type' 		=> 'image_width',
		'std' 		=> '90'
	),
	
	array( 'type' => 'sectionend', 'id' => 'image_options' ),

)); // End deals settings


$cmdeals_settings['inventory'] = apply_filters('cmdeals_inventory_settings', array(

	array(	'name' => __( 'Inventory Options', 'cmdeals' ), 'type' => 'title','desc' => '', 'id' => 'inventory_options' ),
		
	array(  
		'name' => __( 'Notifications', 'cmdeals' ),
		'desc' 		=> __( 'Enable low stock notifications', 'cmdeals' ),
		'id' 		=> 'cmdeals_notify_low_stock',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox',
		'checkboxgroup' => 'start'
	),
	
	array(  
		'desc' 		=> __( 'Enable out of stock notifications', 'cmdeals' ),
		'id' 		=> 'cmdeals_notify_no_stock',
		'std' 		=> 'yes',
		'type' 		=> 'checkbox',
		'checkboxgroup' => 'end'
	),
	
	array(  
		'name' => __( 'Low stock threshold', 'cmdeals' ),
		'desc' 		=> '',
		'tip' 		=> '',
		'id' 		=> 'cmdeals_notify_low_stock_amount',
		'css' 		=> 'width:30px;',
		'type' 		=> 'text',
		'std' 		=> '2'
	),
	
	array(  
		'name' => __( 'Out of stock threshold', 'cmdeals' ),
		'desc' 		=> '',
		'tip' 		=> '',
		'id' 		=> 'cmdeals_notify_no_stock_amount',
		'css' 		=> 'width:30px;',
		'type' 		=> 'text',
		'std' 		=> '0'
	),
	
	array(  
		'name' => __( 'Out of stock visibility', 'cmdeals' ),
		'desc' 		=> __('Hide out of stock items from the deals', 'cmdeals'),
		'id' 		=> 'cmdeals_hide_out_of_stock_items',
		'std' 		=> 'no',
		'type' 		=> 'checkbox'
	),
	
	array( 'type' => 'sectionend', 'id' => 'inventory_options'),

)); // End inventory settings


$cmdeals_settings['email'] = apply_filters('cmdeals_email_settings', array(
	
	array(	'name' => __( 'Email Recipient Options', 'cmdeals' ), 'type' => 'title', '', 'id' => 'email_recipient_options' ),
	
	array(  
		'name' => __( 'New order notifications', 'cmdeals' ),
		'desc' 		=> __( 'The recipient of new order emails. Defaults to the admin email.', 'cmdeals' ),
		'id' 		=> 'cmdeals_new_order_email_recipient',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> esc_attr(get_option('admin_email'))
	),
	
	array(  
		'name' => __( 'Inventory notifications', 'cmdeals' ),
		'desc' 		=> __( 'The recipient of stock emails. Defaults to the admin email.', 'cmdeals' ),
		'id' 		=> 'cmdeals_stock_email_recipient',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> esc_attr(get_option('admin_email'))
	),
	
	array( 'type' => 'sectionend', 'id' => 'email_recipient_options' ),
	
	array(	'name' => __( 'Email Sender Options', 'cmdeals' ), 'type' => 'title', '', 'id' => 'email_options' ),
	
	array(  
		'name' => __( '"From" name', 'cmdeals' ),
		'desc' 		=> __( 'The sender name for CMDeals emails.', 'cmdeals' ),
		'id' 		=> 'cmdeals_email_from_name',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> esc_attr(get_bloginfo('name'))
	),
	
	array(  
		'name' => __( '"From" email address', 'cmdeals' ),
		'desc' 		=> __( 'The sender email address for CMDeals emails.', 'cmdeals' ),
		'id' 		=> 'cmdeals_email_from_address',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> get_option('admin_email')
	),
	
	array( 'type' => 'sectionend', 'id' => 'email_options' ),
	
	array(	'name' => __( 'Email template', 'cmdeals' ), 'type' => 'title', 'desc' => sprintf(__('This section lets you customise the CMDeals emails. <a href="%s" target="_blank">Click here to preview your email template</a>. For more advanced control copy <code>cmdeals/deals-templates/emails/</code> to <code>yourtheme/cmdeals/emails/</code>.', 'cmdeals'), wp_nonce_url(admin_url('?preview_cmdeals_mail=true'), 'preview-mail')), 'id' => 'email_template_options' ),
	
	array(  
		'name' => __( 'Header image', 'cmdeals' ),
		'desc' 		=> sprintf(__( 'Enter a URL to an image you want to show in the email\'s header. Upload your image using the <a href="%s">media uploader</a>.', 'cmdeals' ), admin_url('media-new.php')),
		'id' 		=> 'cmdeals_email_header_image',
		'type' 		=> 'text',
		'css' 		=> 'min-width:300px;',
		'std' 		=> ''
	),
	
	array(  
		'name' => __( 'Email footer text', 'cmdeals' ),
		'desc' 		=> __( 'The text to appear in the footer of CMDeals emails.', 'cmdeals' ),
		'id' 		=> 'cmdeals_email_footer_text',
		'css' 		=> 'width:100%; height: 75px;',
		'type' 		=> 'textarea',
		'std' 		=> get_bloginfo('name') . ' - ' . __('Powered by CMDeals', 'cmdeals')
	),
	
	array(  
		'name' => __( 'Base colour', 'cmdeals' ),
		'desc' 		=> __( 'The base colour for CMDeals email templates. Default <code>#336666</code>.', 'cmdeals' ),
		'id' 		=> 'cmdeals_email_base_color',
		'type' 		=> 'color',
		'css' 		=> 'width:6em;',
		'std' 		=> '#336666'
	),
	
	array(  
		'name' => __( 'Background colour', 'cmdeals' ),
		'desc' 		=> __( 'The background colour for CMDeals email templates. Default <code>#eeeeee</code>.', 'cmdeals' ),
		'id' 		=> 'cmdeals_email_background_color',
		'type' 		=> 'color',
		'css' 		=> 'width:6em;',
		'std' 		=> '#eeeeee'
	),
	
	array(  
		'name' => __( 'Email body background colour', 'cmdeals' ),
		'desc' 		=> __( 'The main body background colour. Default <code>#fdfdfd</code>.', 'cmdeals' ),
		'id' 		=> 'cmdeals_email_body_background_color',
		'type' 		=> 'color',
		'css' 		=> 'width:6em;',
		'std' 		=> '#fdfdfd'
	),
	
	array(  
		'name' => __( 'Email body text colour', 'cmdeals' ),
		'desc' 		=> __( 'The main body text colour. Default <code>#505050</code>.', 'cmdeals' ),
		'id' 		=> 'cmdeals_email_text_color',
		'type' 		=> 'color',
		'css' 		=> 'width:6em;',
		'std' 		=> '#505050'
	),
	
	array( 'type' => 'sectionend', 'id' => 'email_template_options' ),

)); // End email settings

/**
 * Settings page
 * 
 * Handles the display of the main cmdeals settings page in admin.
 */
if (!function_exists('cmdeals_settings')) {
function cmdeals_settings() {
    global $cmdeals, $cmdeals_settings;
    
    $current_tab = (isset($_GET['tab'])) ? $_GET['tab'] : 'general';
    
    if( isset( $_POST ) && $_POST ) :
    	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'cmdeals-settings' ) ) die( __( 'Action failed. Please refresh the page and retry.', 'cmdeals' ) ); 
    	
    	switch ( $current_tab ) :
			case "general" :
			case "pages" :
			case "deals" :
			case "inventory" :
			case "email" :
				cmdeals_update_options( $cmdeals_settings[$current_tab] );
			break;
		endswitch;
		
		do_action( 'cmdeals_update_options' );
		do_action( 'cmdeals_update_options_' . $current_tab );
		flush_rewrite_rules( false );
		wp_redirect( add_query_arg( 'subtab', esc_attr(str_replace('#', '', $_POST['subtab'])), add_query_arg( 'saved', 'true', admin_url( 'admin.php?page=cmdeals&tab=' . $current_tab ) )) );
    endif;
    
    if (isset($_GET['saved']) && $_GET['saved']) :
    	echo '<div id="message" class="updated fade"><p><strong>' . __( 'Your settings have been saved.', 'cmdeals' ) . '</strong></p></div>';
        flush_rewrite_rules( false );
    endif;
    
    // Install/page installer
    $install_complete = false;
    $show_page_installer = false;
    
    // Add pages button
    if (isset($_GET['install_cmdeals_pages']) && $_GET['install_cmdeals_pages']) :
	    	
    	cmdeals_create_pages();
    	update_option('skip_install_cmdeals_pages', 1);
    	$install_complete = true;
	
	// Skip button
    elseif (isset($_GET['skip_install_cmdeals_pages']) && $_GET['skip_install_cmdeals_pages']) :
    	
    	update_option('skip_install_cmdeals_pages', 1);
    	$install_complete = true;
    	
    // If we have just activated CMDeals...
    elseif (isset($_GET['installed']) && $_GET['installed']) :
    	
        flush_rewrite_rules( false );

        if (get_option('cmdeals_store_page_id')) :
                $install_complete = true;
        else :
                $show_page_installer = true;
        endif;
		
    // If we havn't just installed, but page installed has not been skipped and store page does not exist...
    elseif (!get_option('skip_install_cmdeals_pages') && !get_option('cmdeals_store_page_id')) :

            $show_page_installer = true;

    endif;
	
    if ($show_page_installer) :

        echo '<div id="message" class="updated fade">
                <p><strong>' . __( 'Welcome to CMDeals!', 'cmdeals' ) . '</strong></p>
                <p>'. __('CMDeals requires several WordPress pages containing shortcodes in order to work correctly; these include Store, Checkout and My Account. To add these pages automatically please click the \'Automatically add pages\' button below, otherwise you can set them up manually. See the \'Pages\' tab in settings for more information.', 'cmdeals') .'</p>
                <p><a href="'.remove_query_arg('installed', add_query_arg('install_cmdeals_pages', 'true')).'" class="button button-primary">'. __('Automatically add pages', 'cmdeals') .'</a> <a href="'.remove_query_arg('installed', add_query_arg('skip_install_cmdeals_pages', 'true')).'" class="button">'. __('Skip setup', 'cmdeals') .'</a></p>
        </div>';

    elseif ($install_complete) :

        echo '<div id="message" class="updated fade">
                <p style="float:right;">' . __( 'Like CMDeals? <a href="http://wordpress.org/extend/plugins/wp-deals/" target="_blank">Support us by leaving a rating!</a>', 'cmdeals' ) . '</p>
                <p><strong>' . __( 'CMDeals has been installed and setup. Enjoy :)', 'cmdeals' ) . '</strong></p>
        </div>';

        flush_rewrite_rules( false );

    endif;
    ?>
	<div class="wrap cmdeals">
		<form method="post" id="mainform" action="">
			<div class="icon32 icon32-cmdeals-settings" id="icon-cmdeals"><br></div><h2 class="nav-tab-wrapper cmdeals-nav-tab-wrapper">
				<?php
					$tabs = array(
						'general' => __( 'General', 'cmdeals' ),
						'pages' => __( 'Pages', 'cmdeals' ),
						'deals' => __( 'Deals', 'cmdeals' ),
						'inventory' => __( 'Inventory', 'cmdeals' ),
						'payment_gateways' => __( 'Payment Gateways', 'cmdeals' ),
						'email' => __( 'Emails', 'cmdeals' ),
					);
					
					$tabs = apply_filters('cmdeals_settings_tabs_array', $tabs);
					
					foreach ($tabs as $name => $label) :
						echo '<a href="' . admin_url( 'admin.php?page=cmdeals&tab=' . $name ) . '" class="nav-tab ';
						if( $current_tab==$name ) echo 'nav-tab-active';
						echo '">' . $label . '</a>';
					endforeach;
					
					do_action( 'cmdeals_settings_tabs' ); 
				?>
			</h2>
			<?php wp_nonce_field( 'cmdeals-settings', '_wpnonce', true, true ); ?>
			<?php
				switch ($current_tab) :
					case "general" :
					case "pages" :
					case "deals" :
					case "inventory" :
					case "email" :
						cmdeals_admin_fields( $cmdeals_settings[$current_tab] );
					break;
            	
					break;
					case "payment_gateways" : 	
					
						$links = array( '<a href="#gateway-order">'.__('Payment Gateways', 'cmdeals').'</a>' );
            			
		            	foreach ($cmdeals->payment_gateways->payment_gateways() as $gateway) :
		            		$title = ( isset( $gateway->method_title ) && $gateway->method_title) ? ucwords($gateway->method_title) : ucwords($gateway->id);
		            		$links[] = '<a href="#gateway-'.$gateway->id.'">'.$title.'</a>';
						endforeach;
						
						echo '<div class="subsubsub_section"><ul class="subsubsub"><li>' . implode(' | </li><li>', $links) . '</li></ul><br class="clear" />';
		            	
		            	// Gateway ordering
		            	echo '<div class="section" id="gateway-order">';
		            	
		            	?>
		            	<h3><?php _e('Payment Gateways', 'cmdeals'); ?></h3>
		            	<p><?php _e('Your activated payment gateways are listed below. Drag and drop rows to re-order them for display on the checkout.', 'cmdeals'); ?></p>
		            	<table class="wd_gateways widefat" cellspacing="0">
		            		<thead>
		            			<tr>
		            				<th width="1%"><?php _e('Default', 'cmdeals'); ?></th>
		            				<th><?php _e('Gateway', 'cmdeals'); ?></th>
		            				<th><?php _e('Status', 'cmdeals'); ?></th>
		            			</tr>
		            		</thead>
		            		<tbody>
				            	<?php
				            	foreach ( $cmdeals->payment_gateways->payment_gateways() as $gateway ) :
				            		
				            		$default_gateway = get_option('cmdeals_default_gateway');
				            		
				            		echo '<tr>
				            			<td width="1%" class="radio">
				            				<input type="radio" name="default_gateway" value="'.$gateway->id.'" '.checked($default_gateway, $gateway->id, false).' />
				            				<input type="hidden" name="gateway_order[]" value="'.$gateway->id.'" />
				            			</td>
				            			<td>
				            				<p><strong>'.$gateway->title.'</strong><br/>
				            				<small>'.__('Gateway ID', 'cmdeals').': '.$gateway->id.'</small></p>
				            			</td>
				            			<td>';
				            		
				            		if ($gateway->enabled == 'yes') 
				            			echo '<img src="'.$cmdeals->plugin_url().'/cmdeals-assets/images/success.gif" alt="yes" />';
									else 
										echo '<img src="'.$cmdeals->plugin_url().'/cmdeals-assets/images/success-off.gif" alt="no" />';	
				            			
				            		echo '</td>
				            		</tr>';
				            		
				            	endforeach; 
				            	?>
		            		</tbody>
		            	</table>
		            	<?php
		            	
		            	echo '</div>';
		            	
		            	// Specific gateway options
		            	foreach ( $cmdeals->payment_gateways->payment_gateways() as $gateway ) :
		            		echo '<div class="section" id="gateway-'.$gateway->id.'">';
		            		$gateway->admin_options();
		            		echo '</div>';
		            	endforeach; 
		            	
		            	echo '</div>';
            	
					break;
					default :
						do_action( 'cmdeals_settings_tabs_' . $current_tab );
					break;
				endswitch;
			?>
	        <p class="submit">
	        	<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'cmdeals' ); ?>" />
	        	<input type="hidden" name="subtab" id="last_tab" />
	        </p>
		</form>
		
		<script type="text/javascript">
			jQuery(window).load(function(){
			
				// Subsubsub tabs
				jQuery('ul.subsubsub li a:eq(0)').addClass('current');
				jQuery('.subsubsub_section .section:gt(0)').hide();
				
				jQuery('ul.subsubsub li a').click(function(){
					jQuery('a', jQuery(this).closest('ul.subsubsub')).removeClass('current');
					jQuery(this).addClass('current');
					jQuery('.section', jQuery(this).closest('.subsubsub_section')).hide();
					jQuery( jQuery(this).attr('href') ).show();
					jQuery('#last_tab').val( jQuery(this).attr('href') );
					return false;
				});
				
				<?php if (isset($_GET['subtab']) && $_GET['subtab']) echo 'jQuery("ul.subsubsub li a[href=#'.$_GET['subtab'].']").click();'; ?>
				
				// Countries
				jQuery('select#cmdeals_allowed_countries').change(function(){
					if (jQuery(this).val()=="specific") {
						jQuery(this).parent().parent().next('tr').show();
					} else {
						jQuery(this).parent().parent().next('tr').hide();
					}
				}).change();
				
				// Color picker
				jQuery('.colorpick').each(function(){
					jQuery('.colorpickdiv', jQuery(this).parent()).farbtastic(this);
					jQuery(this).click(function() {
						if ( jQuery(this).val() == "" ) jQuery(this).val('#');
						jQuery('.colorpickdiv', jQuery(this).parent() ).show();
					});	
				});
				jQuery(document).mousedown(function(){
					jQuery('.colorpickdiv').hide();
				});
				
				// Edit prompt
				jQuery(function(){
					var changed = false;
					
					jQuery('input, textarea, select, checkbox').change(function(){
						changed = true;
					});
					
					jQuery('.cmdeals-nav-tab-wrapper a').click(function(){
						if (changed) {
							window.onbeforeunload = function() {
							    return '<?php echo __( 'The changes you made will be lost if you navigate away from this page.', 'cmdeals' ); ?>';
							}
						} else {
							window.onbeforeunload = '';
						}
					});
					
					jQuery('.submit input').click(function(){
						window.onbeforeunload = '';
					});
				});
				
				// Sorting
				jQuery('table.wd_gateways tbody').sortable({
					items:'tr',
					cursor:'move',
					axis:'y',
					handle: 'td',
					scrollSensitivity:40,
					helper:function(e,ui){
						ui.children().each(function(){
							jQuery(this).width(jQuery(this).width());
						});
						ui.css('left', '0');
						return ui;
					},
					start:function(event,ui){
						ui.item.css('background-color','#f6f6f6');
					},
					stop:function(event,ui){
						ui.item.removeAttr('style');
					}
				});
				
				// Chosen selects
				jQuery("select.chosen_select").chosen();
				
				jQuery("select.chosen_select_nostd").chosen({
					allow_single_deselect: 'true'
				});
				
			});
		</script>
	</div>
	<?php
}
}