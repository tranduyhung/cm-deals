(
	function(){
	
		var icon_url = '../wp-content/plugins/wp-deals/cmdeals-assets/images/icons/wd_icon.png';
	
		tinymce.create(
			"tinymce.plugins.CMDealsShortcodes",
			{
				init: function(d,e) {},
				createControl:function(d,e)
				{
				
					if(d=="cmdeals_shortcodes_button"){
					
						d=e.createMenuButton( "cmdeals_shortcodes_button",{
							title:"Insert CMDeals Shortcode",
							image:icon_url,
							icons:false
							});
							
							var a=this;d.onRenderMenu.add(function(c,b){
								
								
								a.addImmediate(b,"Deals price/cart button", '[add_to_cart id=""]');
								a.addImmediate(b,"Deals by ID", '[daily-deals ids=""]');
								a.addImmediate(b,"Deals by category slug", '[deal_category category="" per_page="12" columns="3" orderby="date" order="desc"]');
								
								b.addSeparator();
								
								a.addImmediate(b,"Recent deals", '[recent_deals per_page="12" columns="3" orderby="date" order="desc"]');
								a.addImmediate(b,"Featured deals", '[featured_deals per_page="12" columns="3" orderby="date" order="desc"]');
								
								b.addSeparator();
								
								c=b.addMenu({title:"CMDeals Pages"});
										a.addImmediate(c,"Checkout","[cmdeals_checkout]" );
										a.addImmediate(c,"My Account","[cmdeals_my_account]" );
										a.addImmediate(c,"Change Password","[cmdeals_change_password]" );
										a.addImmediate(c,"View Order","[cmdeals_view_order]" );
										a.addImmediate(c,"Pay","[cmdeals_pay]" );
										a.addImmediate(c,"Thankyou","[cmdeals_thankyou]" );

							});
						return d
					
					} // End IF Statement
					
					return null
				},
		
				addImmediate:function(d,e,a){d.add({title:e,onclick:function(){tinyMCE.activeEditor.execCommand( "mceInsertContent",false,a)}})}
				
			}
		);
		
		tinymce.PluginManager.add( "CMDealsShortcodes", tinymce.plugins.CMDealsShortcodes);
	}
)();