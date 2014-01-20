jQuery( function($){
	
	// Prevent enter submitting post form
	$("#upsell_deals_data").bind("keypress", function(e) {
		if (e.keyCode == 13) return false;
	});
	
	// TABS
	$('ul.tabs').show();
	$('div.panel-wrap').each(function(){
		$('div.panel:not(div.panel:first)', this).hide();
	});
	$('ul.tabs a').click(function(){
		var panel_wrap =  $(this).closest('div.panel-wrap');
		$('ul.tabs li', panel_wrap).removeClass('active');
		$(this).parent().addClass('active');
		$('div.panel', panel_wrap).hide();
		$( $(this).attr('href') ).show();
		return false;
	});
	
	// ORDERS
	$('a.edit_address').click(function(){
		
		$(this).hide();
		$(this).closest('.order_data').find('div.address').hide();
		$(this).closest('.order_data').find('div.edit_address').show();

	});
	
	// Chosen selects
	jQuery("select.chosen_select").chosen();
			
	jQuery("select.chosen_select_nostd").chosen({
		allow_single_deselect: 'true'
	});
	
	$('#order_items_list button.remove_row').live('click', function(){
		var answer = confirm(cmdeals_writepanel_params.remove_item_notice);
		if (answer){
			$(this).closest('tr.item').hide();
			$('input', $(this).closest('tr.item')).val('');
		}
		return false;
	});
	
	$('button.calc_totals').live('click', function(){
		var answer = confirm(cmdeals_writepanel_params.cart_total);
		if (answer){
			
			var item_count      = $('#order_items_list tr.item').size();
                        var item_quantity   = 0;
			var total           = 0;
			var subtotal        = 0;
			
			// Items
			if (item_count>0) {
				for (i=0; i<item_count; i++) {
					
					itemQty     = parseInt($('input[name^=item_quantity]:eq(' + i + ')').val());
					itemBase    = $('input[name^=discount_item_cost]:eq(' + i + ')').val();
					
					subtotal    = subtotal + parseFloat( (itemBase * itemQty) );
					
				}
			}
			
			// Rounding
			total = subtotal * 100;
			total = total.toFixed(2);
			total = Math.round( total ) / 100;
			
			if (total < 0 ) total = 0;
			
			$('input#_order_total').val( total.toFixed(2) );

		}
		return false;
	});
	
	$('button.add_deals_sales_item').click(function(){
		
		var add_item_id = $('select.add_item_id').val();
		
		if (add_item_id) {
			
			$('table.cmdeals_order_items').block({ message: null, overlayCSS: { background: '#fff url(' + cmdeals_writepanel_params.plugin_url + '/cmdeals-assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
			
			var size = $('table.cmdeals_order_items tbody tr.item').size();
			
			var data = {
				action: 		'cmdeals_add_order_item',
				item_to_add:            $('select.add_item_id').val(),
				index:			size,
				security: 		cmdeals_writepanel_params.add_order_item_nonce
			};

			$.post( cmdeals_writepanel_params.ajax_url, data, function(response) {
				
				$('table.cmdeals_order_items tbody#order_items_list').append( response );
				$('table.cmdeals_order_items').unblock();
				$('select.add_item_id').css('border-color', '').val('');
				    jQuery(".tips").tipTip({
				    	'attribute' : 'tip',
				    	'fadeIn' : 50,
				    	'fadeOut' : 50
				    });				
			});

		} else {
			$('select.add_item_id').css('border-color', 'red');
		}

	});
	
	$('button.add_meta').live('click', function(){
		
		var index = $(this).closest('tr.item').attr('rel');
		
		$(this).closest('table.meta').find('.meta_items').append('<tr><td><input type="text" name="meta_name[' + index + '][]" placeholder="' + cmdeals_writepanel_params.meta_name + '" /></td><td><input type="text" name="meta_value[' + index + '][]" placeholder="' + cmdeals_writepanel_params.meta_value + '" /></td><td><button class="remove_meta button">&times;</button></td></tr>');
		
		return false;
		
	});
	
	$('button.remove_meta').live('click', function(){
		var answer = confirm("Remove this meta key?")
		if (answer){
			$(this).closest('tr').remove();
		}
		return false;
	});	
	
	// PRODUCT TYPE SPECIFIC OPTIONS
	$('select#daily-deals-type').change(function(){
		
		// Get value
		var select_val = $(this).val();
		
		$('.show_if_simple, .show_if_variable, .show_if_grouped, .show_if_external').hide();
		
		if (select_val=='simple') {
			$('.show_if_simple').show();
		}
		
		else if (select_val=='variable') {
			$('.show_if_variable').show();
			$('input#downloadable').prop('checked', false).change();
		}
		
		else if (select_val=='external') {
			$('.show_if_external').show();
		}
		
		$('ul.tabs li:visible').eq(0).find('a').click();
		
		$('body').trigger('cmdeals-daily-deals-type-change', select_val, $(this) );
		
	}).change();
	
	$('input#downloadable').change(function(){
	
		$('.show_if_downloadable').hide();
		$('.show_if_couponable').hide();
                
                $('select#daily-deals-type').change(function(){
                    
                    
                    var select_val = $(this).val();                    
                    if (select_val=='simple') {
                        if ($('input#downloadable').is(':checked')) {
                                $('.show_if_downloadable').show();
                                $('.show_if_couponable').hide();
                        }else{
                                $('.show_if_downloadable').hide();
                                $('.show_if_couponable').show();                    
                        }

                        if ($('.downloads_tab').is('.active')) $('ul.tabs li:visible').eq(0).find('a').click();
                        if ($('.coupons_tab').is('.active')) $('ul.tabs li:visible').eq(0).find('a').click();
                    }
                    
                }).change();
		
		
	}).change();
	
	$('input#virtual').change(function(){
	
		$('.show_if_virtual').hide();
		
		if ($('input#virtual').is(':checked')) {
			$('.show_if_virtual').show();
		}
		
	}).change();
	
	
	// DATE PICKER FIELDS
	var dates = $( "#end_time" ).datetimepicker({
		defaultDate: "",
		dateFormat: "yy-mm-dd",
                timeFormat: 'hh:mm:ss',
		numberOfMonths: 1,
		showButtonPanel: true,
		showOn: "button",
		buttonImage: cmdeals_writepanel_params.calendar_image,
		buttonImageOnly: true
	});
	
	$( ".date-picker" ).datepicker({
		dateFormat: "yy-mm-dd",
		numberOfMonths: 1,
		showButtonPanel: true,
		showOn: "button",
		buttonImage: cmdeals_writepanel_params.calendar_image,
		buttonImageOnly: true
	});
		
	
	// ATTRIBUTE TABLES
		
		// Multiselect attributes
		$("#attributes_list select.multiselect").chosen();	
		
		// Initial order
		var cmdeals_attributes_table_items = $('#attributes_list').children('tr').get();
		cmdeals_attributes_table_items.sort(function(a, b) {
		   var compA = $(a).attr('rel');
		   var compB = $(b).attr('rel');
		   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
		})
		$(cmdeals_attributes_table_items).each( function(idx, itm) { $('#attributes_list').append(itm); } );
		
		// Show
		function show_attribute_table() {
			$('table.cmdeals_attributes, table.cmdeals_variable_attributes').each(function(){
				if ($('tbody tr', this).size()==0) 
					$(this).parent().hide();
				else 
					$(this).parent().show();
			});
		}
		show_attribute_table();
		
		function row_indexes() {
			$('#attributes_list tr').each(function(index, el){ $('.attribute_position', el).val( parseInt( $(el).index('#attributes_list tr') ) ); });
		};
		
		// Add rows
		$('button.add_attribute').click(function(){
			
			var size = $('table.cmdeals_attributes tbody tr').size();
			
			var attribute_type = $('select.attribute_taxonomy').val();
			
			if (!attribute_type) {
			
				var deal_type = $('select#daily-deals-type').val();
				if (deal_type!='variable') enable_variation = 'style="display:none;"'; else enable_variation = '';
				
				// Add custom attribute row
				$('table.cmdeals_attributes tbody').append('<tr><td class="handle"></td><td><input type="text" name="attribute_names[' + size + ']" /><input type="hidden" name="attribute_is_taxonomy[' + size + ']" value="0" /><input type="hidden" name="attribute_position[' + size + ']" class="attribute_position" value="' + size + '" /></td><td><input type="text" name="attribute_values[' + size + ']" /></td><td class="center"><input type="checkbox" checked="checked" name="attribute_visibility[' + size + ']" value="1" /></td><td class="center enable_variation" ' + enable_variation + '><input type="checkbox" name="attribute_variation[' + size + ']" value="1" /></td><td class="center"><button type="button" class="remove_row button">&times;</button></td></tr>');
				
			} else {
				
				// Reveal taxonomy row
				var thisrow = $('table.cmdeals_attributes tbody tr.' + attribute_type);
				$('table.cmdeals_attributes tbody').append( $(thisrow) );
				$(thisrow).show();
				row_indexes();
				
			}
	
			show_attribute_table();
		});
		
		$('button.hide_row').live('click', function(){
			var answer = confirm("Remove this attribute?")
			if (answer){
				$(this).parent().parent().find('select, input[type=text]').val('');
				$(this).parent().parent().hide();
				show_attribute_table();
			}
			return false;
		});
		
		$('#attributes_list button.remove_row').live('click', function(){
			var answer = confirm("Remove this attribute?")
			if (answer){
				$(this).parent().parent().remove();
				show_attribute_table();
				row_indexes();
			}
			return false;
		});
		
		// Attribute ordering
		$('table.cmdeals_attributes tbody').sortable({
			items:'tr',
			cursor:'move',
			axis:'y',
			handle: '.handle',
			scrollSensitivity:40,
			helper:function(e,ui){
				ui.children().each(function(){
					$(this).width($(this).width());
				});
				ui.css('left', '0');
				return ui;
			},
			start:function(event,ui){
				ui.item.css('background-color','#f6f6f6');
			},
			stop:function(event,ui){
				ui.item.removeAttr('style');
				row_indexes();
			}
		});

		

	// Cross sells/Up sells
	$('.multi_select_deals button').live('click', function(){
		
		var wrapper = $(this).parent().parent().parent().parent();
		
		var button = $(this);
		var button_parent = button.parent().parent();
		
		if (button_parent.is('.multi_select_deals_target_upsell') || button_parent.is('.multi_select_deals_target_crosssell')) {	
			button.parent().remove();
		} else {
			if (button.is('.add_upsell')) {
				var target = $('.multi_select_deals_target_upsell', $(wrapper));
				var deal_id_field_name = 'upsell_ids[]';
			} else {
				var target = $('.multi_select_deals_target_crosssell', $(wrapper));
				var deal_id_field_name = 'crosssell_ids[]';
			}
		
			var exists = $('li[rel=' + button.parent().attr('rel') + ']', target);
			
			if ($(exists).size()>0) return false;
			
			var cloned_item = button.parent().clone();
			
			cloned_item.find('button:eq(0)').html('&times;');
			cloned_item.find('button:eq(1)').remove();
			cloned_item.find('input').val( button.parent().attr('rel') );
			cloned_item.find('.deal_id').attr('name', deal_id_field_name);
			
			cloned_item.appendTo(target);
		}
	});
	
	var xhr;
	
	$('.multi_select_deals #deal_search').bind('keyup click', function(){
		
		$('.multi_select_deals_source').addClass('loading');
		$('.multi_select_deals_source li:not(.deal_search)').remove();
		
		if (xhr) xhr.abort();
		
		var search = $(this).val();
		var input = this;
		var name = $(this).attr('rel');
		
		if (search.length<3) {
			$('.multi_select_deals_source').removeClass('loading');
			return;
		}
		
		var data = {
			name: 			name,
			search: 		encodeURI(search),
			action: 		'cmdeals_upsell_crosssell_search_deals',
			security: 		cmdeals_writepanel_params.upsell_crosssell_search_deals_nonce
		};
		
		xhr = $.ajax({
			url: cmdeals_writepanel_params.ajax_url,
			data: data,
			type: 'POST',
			success: function( response ) {
			
				$('.multi_select_deals_source').removeClass('loading');
				$('.multi_select_deals_source li:not(.deal_search)').remove();
				$(input).parent().parent().append( response );
				
			}
		});
 			
	});
	
	// Uploading files
	var file_path_field;
	
	window.send_to_editor_default = window.send_to_editor;

	jQuery('.upload_file_button').live('click', function(){
		
		file_path_field = jQuery(this).parent().find('.file_path');
		
		formfield = jQuery(file_path_field).attr('name');
		
		window.send_to_editor = window.send_to_download_url;
		
		tb_show('', 'media-upload.php?post_id=' + cmdeals_writepanel_params.post_id + '&amp;type=downloadable_deals&amp;from=wd01&amp;TB_iframe=true');
		return false;
	});

	window.send_to_download_url = function(html) {
		
		file_url = jQuery(html).attr('href');
		if (file_url) {
			jQuery(file_path_field).val(file_url);
		}
		tb_remove();
		window.send_to_editor = window.send_to_editor_default;
		
	}
        
        

});


// Create random string coupon codes.
function randString(n){
    if(!n){
        n = 5;
    }

    var text = '';
    var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    for(var i=0; i < n; i++){
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

// get coupon results
function randCouponString(){
    couponArea = jQuery('#_coupon_area_deals').val();
    countCoupon= prompt("How many coupons?","5");

    for ($i = 0; $i < countCoupon; $i++){
        couponArea += ' ' + randString(8) + ';';
    }

    jQuery('#_coupon_area_deals').val(couponArea);
}