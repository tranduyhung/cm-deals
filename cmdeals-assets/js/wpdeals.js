jQuery(document).ready(function($) {
	
	if (cmdeals_params.option_ajax_add_to_cart=='yes') {
	
		// Ajax add to cart
		$('.add_to_cart_button').live('click', function() {
			
			// AJAX add to cart request
			var $thisbutton = $(this);
			
			if ($thisbutton.is('.deal_type_simple, .deal_type_downloadable, .deal_type_virtual')) {
				
				if (!$($thisbutton).attr('data-daily-deals_id')) return true;
				
				$($thisbutton).addClass('loading');
				
				var data = {
					action: 		'cmdeals_add_to_cart',
					deal_id: 	$($thisbutton).attr('data-daily-deals_id'),
					security: 		cmdeals_params.add_to_cart_nonce
				};
				
				// Trigger event
				$('body').trigger('adding_to_cart');
				
				// Ajax action
				$.post( cmdeals_params.ajax_url, data, function(response) {
					
					$($thisbutton).removeClass('loading');
	
					// Get response
					data = $.parseJSON( response );
					
					if (data.error) {
						alert(data.error);
						return;
					}
					
					fragments = data;
	
					// Block fragments class
					if (fragments) {
						$.each(fragments, function(key, value) {
							$(key).addClass('updating');
						});
					}
					
					// Block widgets and fragments
					$('.widget_storeping_cart, .store_table.cart, .updating, .cart_totals').fadeTo('400', '0.6').block({message: null, overlayCSS: {background: 'transparent url(' + cmdeals_params.plugin_url + '/cmdeals-assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6}});
					
					// Changes button classes
					$($thisbutton).addClass('added');
	
					// Cart widget load
					if ($('.widget_storeping_cart').size()>0) {
						$('.widget_storeping_cart:eq(0)').load( window.location + ' .widget_storeping_cart:eq(0) > *', function() {
							
							// Replace fragments
							if (fragments) {
								$.each(fragments, function(key, value) {
									$(key).replaceWith(value);
								});
							}
							
							// Unblock
							$('.widget_storeping_cart, .updating').css('opacity', '1').unblock();
						} );
					} else {
						// Replace fragments
						if (fragments) {
							$.each(fragments, function(key, value) {
								$(key).replaceWith(value);
							});
						}
						
						// Unblock
						$('.widget_storeping_cart, .updating').css('opacity', '1').unblock();
					}
					
					// Cart page elements
					$('.store_table.cart').load( window.location + ' .store_table.cart:eq(0) > *', function() {
						
						$("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").addClass('buttons_added').append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />');
						
						$('.store_table.cart').css('opacity', '1').unblock();
						
					});
					
					$('.cart_totals').load( window.location + ' .cart_totals:eq(0) > *', function() {
						$('.cart_totals').css('opacity', '1').unblock();
					});
					
					// Trigger event so themes can refresh other areas
					$('body').trigger('added_to_cart');
			
				});
				
				return false;
			
			} else {
				return true;
			}
			
		});
	
	}
	
	// Orderby
	$('select.orderby').change(function(){
		$(this).closest('form').submit();
	});
	
	// Star ratings
	$('#rating').hide().before('<p class="stars"><span><a class="star-1" href="#">1</a><a class="star-2" href="#">2</a><a class="star-3" href="#">3</a><a class="star-4" href="#">4</a><a class="star-5" href="#">5</a></span></p>');
	
	$('p.stars a').click(function(){
		var $star = $(this);
		$('#rating').val( $star.text() );
		$('p.stars a').removeClass('active');
		$star.addClass('active');
		return false;
	});
	
	// Quantity buttons
	$("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").addClass('buttons_added').append('<input type="button" value="+" id="add1" class="plus" />').prepend('<input type="button" value="-" id="minus1" class="minus" />');
	
	$(".plus").live('click', function() {
	    var currentVal = parseInt($(this).prev(".qty").val());
	    if (!currentVal || currentVal=="" || currentVal == "NaN") currentVal = 0;
	    $(this).prev(".qty").val(currentVal + 1); 
	});
	
	$(".minus").live('click', function() {
	    var currentVal = parseInt($(this).next(".qty").val());
	    if (!currentVal || currentVal=="" || currentVal == "NaN") currentVal = 1;
	    if (currentVal > 0)  $(this).next(".qty").val(currentVal - 1);
	});
	
	/* states */
	var states_json = cmdeals_params.countries.replace(/&quot;/g, '"');
	var states = $.parseJSON( states_json );			
	
	$('select.country_to_state').change(function(){
		
		var country = $(this).val();
		var state_box = $('#' + $(this).attr('rel'));
		
		var input_name = $(state_box).attr('name');
		var input_id = $(state_box).attr('id');
		
		var value = $(state_box).val();

		if (states[country]) {
			var options = '';
			var state = states[country];
			for(var index in state) {
				options = options + '<option value="' + index + '">' + state[index] + '</option>';
			}
			if ($(state_box).is('input')) {
				// Change for select
				$(state_box).replaceWith('<select name="' + input_name + '" id="' + input_id + '"></select>');
				state_box = $('#' + $(this).attr('rel'));
			}
			$(state_box).html( '<option value="">' + cmdeals_params.select_state_text + '</option>' + options);
			
			$(state_box).val(value);
		} else {
			if ($(state_box).is('select')) {
				$(state_box).replaceWith('<input type="text" class="input-text" placeholder="' + cmdeals_params.state_text + '" name="' + input_name + '" id="' + input_id + '" />');
			}
		}
		
	}).change();
	
	/* Tabs */
	$('div.cmdeals_tabs .panel').hide();
	$('div.cmdeals_tabs ul.tabs li a').click(function(){
		
		var $tab = $(this);
		var $tabs_wrapper = $tab.closest('div.cmdeals_tabs');
		
		$('ul.tabs li', $tabs_wrapper).removeClass('active');
		$('div.panel', $tabs_wrapper).hide();
		$('div' + $tab.attr('href')).show();
		$tab.parent().addClass('active');
		
		return false;	
	});
	$('div.cmdeals_tabs').each(function() {
		var hash = window.location.hash;
		if (hash.toLowerCase().indexOf("comment-") >= 0) {
			$('ul.tabs li.reviews_tab a', $(this)).click();
		} else {
			$('ul.tabs li:first a', $(this)).click();
		}
	});
		
	// Variations
	
	//check if two arrays of attributes match
    function variations_match(attrs1, attrs2) {        
        var match = true;
        for (name in attrs1) {
            var val1 = attrs1[name];
            var val2 = attrs2[name];
            
            if(val1.length != 0 && val2.length != 0 && val1 != val2) {
                match = false;
            }
        }
        
        return match;
    }
    
    //search for matching variations for given set of attributes
    function find_matching_variations(settings) {
        var matching = [];
        
        for (var i = 0; i < deal_variations.length; i++) {
        	var variation = deal_variations[i];
        	var variation_id = variation.variation_id;
        	
			if(variations_match(variation.attributes, settings)) {
                matching.push(variation);
            }
        }
        return matching;
    }
    
    //disable option fields that are unavaiable for current set of attributes
    function update_variation_values(variations) {
        
        // Loop through selects and disable/enable options based on selections
        $('.variations select').each(function( index, el ){
        	
        	current_attr_select = $(el);
        	
        	// Disable all
        	current_attr_select.find('option:gt(0)').attr('disabled', 'disabled');
        	
        	// Get name
	        var current_attr_name 	= current_attr_select.attr('name');
	        
	        // Loop through variations
	        for(num in variations) {
	            var attributes = variations[num].attributes;
	            
	            for(attr_name in attributes) {
	                var attr_val = attributes[attr_name];
	                
	                if(attr_name == current_attr_name) {
	                    if (attr_val) {
	                    	
	                    	// Decode entities
	                    	attr_val = $("<div/>").html( attr_val ).text();
	                    	
	                    	// Add slashes
	                    	attr_val = attr_val.replace(/'/g, "\\'");
	                    	attr_val = attr_val.replace(/"/g, "\\\"");
	                    	
	                    	// Compare the meercat
	                    	current_attr_select.find('option[value="'+attr_val+'"]').removeAttr('disabled');
	                    	
	                    } else {
	                    	current_attr_select.find('option').removeAttr('disabled');
	                    }
	                }
	            }
	        }
        	
        });
        
    }
    
    //show single variation details (price, stock, image)
    function show_variation(variation) {
        var img = $('div.images img:eq(0)');
        var link = $('div.images a.zoom:eq(0)');
        var o_src = $(img).attr('original-src');
        var o_link = $(link).attr('original-href');

        var variation_image = variation.image_src;
        var variation_link = variation.image_link;
		
		$('.variations_button').show();
        $('.single_variation').html( variation.price_html + variation.availability_html );

        if (!o_src) {
            $(img).attr('original-src', $(img).attr('src'));
        }

        if (!o_link) {
            $(link).attr('original-href', $(link).attr('href'));
        }

        if (variation_image && variation_image.length > 1) {	
            $(img).attr('src', variation_image);
            $(link).attr('href', variation_link);
        } else {
            $(img).attr('src', o_src);
            $(link).attr('href', o_link);
        }

        $('.single_variation_wrap').slideDown('200').trigger('variationWrapShown');
    }
	
	//when one of attributes is changed - check everything to show only valid options
    function check_variations( exclude ) {
		var all_set = true;
		var current_settings = {};
        
		$('.variations select').each(function(){
			
			if ( exclude && $(this).attr('name') == exclude ) {
				
				all_set = false;
				current_settings[$(this).attr('name')] = '';
				
			} else {
				if ($(this).val().length == 0) all_set = false;

            	// Encode entities
            	value = $(this).val()
		            .replace(/&/g, '&amp;')
		            .replace(/"/g, '&quot;')
		            .replace(/'/g, '&#039;')
		            .replace(/</g, '&lt;')
		            .replace(/>/g, '&gt;');

				// Add to settings array
				current_settings[$(this).attr('name')] = value;
			}
				
		});
        
        var matching_variations = find_matching_variations(current_settings);
        
        if(all_set) {
        	var variation = matching_variations.pop();
        	if (variation) {
            	$('form input[name=variation_id]').val(variation.variation_id);
            	show_variation(variation);
            } else {
            	// Nothing found - reset fields
            	$('.variations select').val('');
            }
        } else {
            update_variation_values(matching_variations);
        }
    }

	$('.variations select').change(function(){
		
		$('form input[name=variation_id]').val('');
        $('.single_variation_wrap').hide();
        $('.single_variation').text('');
		check_variations();
		$(this).blur();
		if( $().uniform && $.isFunction($.uniform.update) ) {
			$.uniform.update();
		}
		
	}).focus(function(){
		
		check_variations( $(this).attr('name') );

	}).change();
	
	if (cmdeals_params.is_checkout==1 || cmdeals_params.is_pay_page==1) {
	
		var updateTimer;
		var xhr;
		
		function update_checkout() {
		
			if (xhr) xhr.abort();
			
			var data = {
				action: 			'cmdeals_update_order_review',
				security: 			cmdeals_params.update_order_review_nonce,
				post_data:			$('form.checkout').serialize()
			};
			
			xhr = $.ajax({
				type: 		'POST',
				url: 		cmdeals_params.ajax_url,
				data: 		data,
				success: 	function( response ) {
					$('#order_methods, #order_review').remove();
					$('#order_review_heading').after(response);
					$('#order_review input[name=payment_method]:checked').click();
				}
			});
		
		}
			
		$('p.password, form.login, div.shipping_address').hide();
		
		$('input.show_password').change(function(){
			$('p.password').slideToggle();
		});
		
		$('a.showlogin').click(function(){
			$('form.login').slideToggle();
			return false;
		});
		
		if (cmdeals_params.option_guest_checkout=='yes') {
			
			$('div.create-account').hide();
			
			$('input#createaccount').change(function(){
				$('div.create-account').hide();
				if ($(this).is(':checked')) {
					$('div.create-account').slideDown();
				}
			}).change();
		
		}
		
		$('.payment_methods input.input-radio').live('click', function(){
			$('div.payment_box').hide();
			if ($(this).is(':checked')) {
				$('div.payment_box.' + $(this).attr('ID')).slideDown();
			}
		});
		
		$('#order_review input[name=payment_method]:checked').click();
				
		// Update on page load
		if (cmdeals_params.is_checkout==1) update_checkout();
		
		/* AJAX Form Submission */
		$('form.checkout').submit(function(){
			var $form = $(this);
			
			if ($form.is('.processing')) return false;
			
			$form.addClass('processing').block({message: null, overlayCSS: {background: '#fff url(' + cmdeals_params.plugin_url + '/cmdeals-assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6}});
			
			$.ajax({
				type: 		'POST',
				url: 		cmdeals_params.checkout_url,
				data: 		$form.serialize(),
				success: 	function( code ) {
					$('.cmdeals_error, .cmdeals_message').remove();
						try {
							success = $.parseJSON( code );					
							window.location = decodeURI(success.redirect);
						}
						catch(err) {
						  	$form.prepend( code );
							$form.removeClass('processing').unblock(); 
							
							$('html, body').animate({
							    scrollTop: ($('form.checkout').offset().top - 100)
							}, 1000);
						}
					},
				dataType: 	"html"
			});
			return false;
		});

	}

});