(function( $ ) {
	'use strict';
	
	$(document).ready(function() {
		
		/**
    	 *  Copy to clipboard
    	 * 	@since 1.3.5
    	 */
    	$(document).on('click', '.wt_sc_copy_to_clipboard', function(){
    		var target_class=$(this).attr('data-target');
    		var target_elm=$('.'+target_class);
    		if(target_elm.length>0 && "" !== target_elm.text().trim())
    		{
    			navigator.clipboard.writeText(target_elm.text().trim());
    			wt_sc_notify_msg.success(WTSmartCouponAdminOBJ.msgs.copied);
    		}
    	});


		$('#upload').on('change',function( ){
			$('.wt-file-container-label').html('selected').addClass('selected');
		});
	

		// Check if selected is a Simple product
		$('#wt_give_away_product').on('change',function() {
			var product_id = $(this).val();
			$('.error_message.wt_coupon_error').hide();
			var data = {
				'action'        : 'wt_check_product_type',
				'product'       : product_id
			};

			jQuery.ajax({
				type: "POST",
				async: true,
				url: WTSmartCouponAdminOBJ.ajaxurl,
				data: data,
				success: function (response) {
					if( 'simple' !== response ) {
						$('.error_message.wt_coupon_error').show();
						$('#wt_give_away_product').val('');
					}
				}
			});

			
		});


		$('.wt_colorpick').wpColorPicker({
			change:function(event,ui)
			{	
				var element = jQuery(event.target);
				var elementID = element.attr('id');
				element.val(ui.color);
				reload_all_coupon_preview();
			}
		});
		// $('#wt_active_coupon_border_color').wpColorPicker( options );


		var wt_create_coupon_preview = function(bg_color, text_color) {
			// var bg_color = $('#wt_active_coupon_bg_color').val() ;
			// var text_color = $('#wt_active_coupon_border_color').val() ;
			var coupon_html = '<div class="wt-single-coupon" style="background: '+ bg_color + ';\
								 color: '+ text_color+ ';\
								 box-shadow: 0 0 0 4px '+ bg_color + ', 2px 1px 6px 4px rgba(10, 10, 0, 0.5);\
								 text-shadow: -1px -1px '+ bg_color + '; \
								 border: 2px dashed  '+ text_color+ '; ">\
								 <div class="wt-coupon-content">\
									<div class="wt-coupon-amount">\
										<span class="amount"> 10 % </span><span>  Cart Discount </span>\
									</div>\
									<div class="wt-coupon-code"> <code> flat10% </code></div>\
									<div class="wt-coupon-expiry"></div>\
								</div></div>';

			return coupon_html;

		};
	

		var wt_reload_coupon_preview = function( coupon_type ) {
			switch( coupon_type) {
				case 'active_coupon' : 
					var coupon_preview_element = '.active_coupon_preview';
					var bg_color = $('#wt_active_coupon_bg_color').val();
					var text_color = $('#wt_active_coupon_border_color').val();
					break;
				case 'used_coupon' : 
					var coupon_preview_element = '.used_coupon_preview';
					var bg_color = $('#wt_used_coupon_bg_color').val();
					var text_color = $('#wt_used_coupon_border_color').val();
					break;
				case 'expired_coupon' : 
					var coupon_preview_element = '.expired_coupon_preview';
					var bg_color = $('#wt_expired_coupon_bg_color').val();
					var text_color = $('#wt_expired_coupon_border_color').val();
					break;

			}
			var preview = wt_create_coupon_preview( bg_color,text_color );
			
			jQuery( coupon_preview_element ).find('.wc-sc-coupon-preview-container').remove();
			jQuery( coupon_preview_element ).append( '<span class="wc-sc-coupon-preview-container">' + preview + '</span>' );
		};
		var reload_all_coupon_preview = function( ) {
			wt_reload_coupon_preview( 'active_coupon');
			wt_reload_coupon_preview( 'used_coupon');
			wt_reload_coupon_preview( 'expired_coupon');
		}

		jQuery(document).ready(function(){
			reload_all_coupon_preview();
		});

		jQuery('#wt_active_coupon_bg_color, #wt_active_coupon_border_color').on('change keyup irischange', function(){
			wt_reload_coupon_preview( 'active_coupon' );
		});

		jQuery('#wt_used_coupon_bg_color, #wt_used_coupon_border_color').on('change keyup irischange', function(){
			wt_reload_coupon_preview( 'used_coupon' );
		});
		
		jQuery('#wt_expired_coupon_bg_color, #wt_expired_coupon_border_color').on('change keyup irischange', function(){
			wt_reload_coupon_preview( 'expired_coupon' );
		});

		jQuery(document).on('click', '.wt-sc-pro-features-view-all', function(){
    		jQuery(".wt-sc-pro-features-all-features").show();
    		jQuery(".wt-sc-pro-features-view-all").hide();
    		jQuery(".wt-sc-pro-features-view-less").show();
    	});

		jQuery(document).on('click', '.wt-sc-pro-features-view-less', function(){
    		jQuery(".wt-sc-pro-features-all-features").hide();
    		jQuery(".wt-sc-pro-features-view-less").hide();
    		jQuery(".wt-sc-pro-features-view-all").show();
    	});

	});


	// Implement Subtab for admin screen.
	jQuery(document).ready(function(  ){

		jQuery('.wt_sub_tab li a').on('click', function( e ) {
			e.preventDefault();
			if( $(this).parent('li').hasClass('active') ) {
				return;//nothing to do;
			}
			var target=$(this).attr('href');
			var parent = $(this).parents('.wt_sub_tab');
			var container = $('.wt_sub_tab_container');
			$('.wt_sub_tab li').removeClass('active');
			$(this).parent('li').addClass('active');
			container.find('.wt_sub_tab_content').hide().removeClass('active');
			container.find(target).fadeIn().addClass('active');
		});

		wt_sc_popup.Set();
		wt_sc_form_toggler.Set();
		wt_sc_conditional_help_text.Set();
		wt_sc_coupon_edit_meta_item_table.Set();
		wt_sc_tab_view.Set();
		wt_sc_settings_form.Set();

		$(".wt-sc-tips").tipTip({'attribute': 'data-wt-sc-tip'});
	});
	
	

})( jQuery );


/**
 *  Toast notification
 * 	@since 1.3.5
 */
var wt_sc_notify_msg=
{
	error:function(message, auto_close)
	{
		var auto_close=(auto_close!== undefined ? auto_close : true);
		var er_elm=jQuery('<div class="wt_sc_notify_msg wt_sc_notify_msg_error">'+message+'</div>');				
		this.setNotify(er_elm, auto_close);
	},
	success:function(message, auto_close)
	{
		var auto_close=(auto_close!== undefined ? auto_close : true);
		var suss_elm=jQuery('<div class="wt_sc_notify_msg wt_sc_notify_msg_success">'+message+'</div>');				
		this.setNotify(suss_elm, auto_close);
	},
	setNotify:function(elm, auto_close)
	{
		jQuery('body').append(elm);
		elm.on('click',function(){
			wt_sc_notify_msg.fadeOut(elm);
		});
		elm.stop(true,true).animate({'opacity':1,'top':'50px'},1000);
		if(auto_close)
		{
			setTimeout(function(){
				wt_sc_notify_msg.fadeOut(elm);
			},5000);
		}else
		{
			jQuery('body').on('click',function(){
				wt_sc_notify_msg.fadeOut(elm);
			});
		}
	},
	fadeOut:function(elm)
	{
		elm.animate({'opacity':0,'top':'100px'},1000,function(){
			elm.remove();
		});
	}
}

/**
 *  Form toggler
 * 	@since 1.4.0
 */
var wt_sc_form_toggler=
{
	Set:function()
	{
		this.runToggler();
		jQuery('select.wt_sc_form_toggle').on('change', function(){
			wt_sc_form_toggler.toggle(jQuery(this));
		});
		jQuery('input[type="radio"].wt_sc_form_toggle').on('click',function(){
			if(jQuery(this).is(':checked'))
			{
				wt_sc_form_toggler.toggle(jQuery(this));
			}
		});
		jQuery('input[type="checkbox"].wt_sc_form_toggle').on('click',function(){
			wt_sc_form_toggler.toggle(jQuery(this),1);
		});
	},
	runToggler:function(prnt)
	{
		prnt=prnt ? prnt : jQuery('body');
		prnt.find('select.wt_sc_form_toggle').each(function(){
			wt_sc_form_toggler.toggle(jQuery(this));
		});
		prnt.find('input[type="radio"].wt_sc_form_toggle, input[type="checkbox"].wt_sc_form_toggle').each(function(){
			if(jQuery(this).is(':checked'))
			{
				wt_sc_form_toggler.toggle(jQuery(this));
			}
		});
		prnt.find('input[type="checkbox"].wt_sc_form_toggle').each(function(){
			wt_sc_form_toggler.toggle(jQuery(this),1);
		});
	},
	toggle:function(elm,checkbox)
	{
		var vl=elm.val();
		var trgt=elm.attr('wt_sc_form_toggle-target');
		jQuery('[wt_sc_form_toggle-id="'+trgt+'"]').hide().addClass('wt_sc_form_toggle_hidden');
		
		jQuery('[wt_sc_form_toggle-id="'+trgt+'"] [data-settings-required], [wt_sc_form_toggle-id="'+trgt+'"] [required]').each(function(){		
			var td_elm=jQuery(this).parents('td');
			if(td_elm.length>0)
			{
				var clone_elm=jQuery(this).clone();
				td_elm.data('w_sc_input_elm', clone_elm).addClass('wt_sc_form_toggle_input_holder');
				jQuery(this).remove();
			}
		});

		if(elm.css('display')!='none') /* if parent is visible. `:visible` method. it will not work on JS tabview */
		{
			var elms=this.getElms(elm, trgt, vl, checkbox);
			elms.show().removeClass('wt_sc_form_toggle_hidden').find('th label').css({'margin-left':'0px'})
			elms.each(function(){
				var lvl=jQuery(this).attr('wt_sc_form_toggle-level');
				var mrgin=15;
				if (typeof lvl!== typeof undefined && lvl!== false) {
				    mrgin=lvl*mrgin;
				}
				if(jQuery(this).find('.wt_sc_form_toggle_input_holder').length)
				{
					jQuery(this).find('.wt_sc_form_toggle_input_holder').prepend(jQuery(this).find('.wt_sc_form_toggle_input_holder').data('w_sc_input_elm'))
				}
				jQuery(this).find('th label').animate({'margin-left':mrgin+'px'});
			});
		}

		/* in case of greater than 1 level */
		jQuery('[wt_sc_form_toggle-id="'+trgt+'"]').each(function(){
			wt_sc_form_toggler.runToggler(jQuery(this));
		});
	},
	getElms:function(elm, trgt, vl, checkbox)
	{		
		return jQuery('[wt_sc_form_toggle-id="'+trgt+'"]').filter(function(){
				var toggle_val=jQuery(this).attr('wt_sc_form_toggle-val');
				if(toggle_val==vl)
				{
					if(checkbox)
					{
						if(elm.is(':checked'))
						{
							if(jQuery(this).attr('wt_sc_form_toggle-check')=='true')
							{
								return true;
							}else
							{
								return false;
							}
						}else
						{
							if(jQuery(this).attr('wt_sc_form_toggle-check')=='false')
							{
								return true;
							}else
							{
								return false;
							}
						}
					}else
					{
						return true;
					}
				}else if(toggle_val.indexOf("||")!=-1)
				{
					var val_arr=toggle_val.split("||");
					if(jQuery.inArray(vl, val_arr)!==-1)
					{
						return true;
					}else
					{
						return false;
					}
				}else
				{
					return false;
				}
			});
	}
}

/**
 *  Conditional help text
 * 	@since 1.4.0
 */
var wt_sc_conditional_help_text=
{
	Set:function(prnt)
	{
		prnt=prnt ? prnt : jQuery('body');
		const regex = /\[(.*?)\]/gm;
		let m;
		prnt.find('.wt_sc_conditional_help_text').each(function()
		{
			var help_text_elm=jQuery(this);
			var this_condition=jQuery(this).attr('data-sc-help-condition');
			if(this_condition!='')
			{
				var condition_conf=new Array();
				var field_arr=new Array();
				while ((m = regex.exec(this_condition)) !== null)
				{
					/* This is necessary to avoid infinite loops with zero-width matches */
				    if(m.index === regex.lastIndex)
				    {
				        regex.lastIndex++;
				    }
				    condition_conf.push(m[1]);
				    condition_arr=m[1].split('=');
				    if(condition_arr.length>1) /* field value pair */
				    {
				    	field_arr.push(condition_arr[0]);
				    }
				}
				if(field_arr.length>0)
				{					
					var callback_fn=function()
					{
						var is_hide=true;
						var previous_type='';
						for(var c_i=0; c_i<condition_conf.length; c_i++)
						{
							var cr_conf=condition_conf[c_i]; /* conf */
							var conf_arr=cr_conf.split('=');
							if(conf_arr.length>1) /* field value pair */
							{
								if(previous_type!='field')
								{
									previous_type='field';
									var elm=jQuery('[name="'+conf_arr[0]+'"]');
									var vl='';
									if(elm.prop('nodeName').toLowerCase()=='input' && elm.attr('type')=='radio')
									{
										vl=jQuery('[name="'+conf_arr[0]+'"]:checked').val();
									}
									else if(elm.prop('nodeName').toLowerCase()=='input' && elm.attr('type')=='checkbox')
									{
										if(elm.is(':checked'))
										{
											vl=elm.val();
										}
									}else
									{
										vl=elm.val();
									}
									
									var check_val_arr = conf_arr[1].split('|');
									
									is_hide = (-1 !== jQuery.inArray(vl, check_val_arr) ? false : true);
								}
							}else /* glue */
							{
								if(previous_type!='glue')
								{
									previous_type='glue';
									if(conf_arr[0]=='OR')
									{
										if(is_hide===false) /* one previous condition is okay, then stop the loop */
										{
											break;
										}

									}else if(conf_arr[0]=='AND')
									{
										if(is_hide===true && c_i>0) /* one previous condition is not okay,  then stop the loop */
										{
											break;
										} 
									}
								}
							}
						}
						if(is_hide)
						{
							help_text_elm.hide();
						}else
						{
							help_text_elm.css({'display':'inline-block'});
						}
					}
					callback_fn();
					for(var f_i=0; f_i<field_arr.length; f_i++)
					{
						var elm=jQuery('[name="'+field_arr[f_i]+'"]');
						if(elm.prop('nodeName')=='radio' || elm.prop('nodeName')=='checkbox')
						{
							elm.on('click', callback_fn);
						}else
						{
							elm.on('change', callback_fn);
						}
					}
				}
			}
		});
	}
}

/**
 *  @since 1.4.0
 * 	Coupon edit page product/category table
 */
var wt_sc_coupon_edit_meta_item_table=
{
	Set:function()
	{
		this.set_add_row();
		this.set_remove_row();
		this.reg_multi_select(jQuery('.wt_sc_product_search'));
		this.reg_multi_select(jQuery('.wt_sc_category_search'));
	},

	/**
	 * 	Add form index to fields in the table row
	 */
	set_table_form_field_index:function(table_elm)
	{
		table_elm.find('tbody tr').each(function(ind, elm){
			
			jQuery(elm).find('input, select').each(function(){
				var new_name = jQuery(this).attr('name').replace(/[0-9]/g, ind);
				jQuery(this).attr('name', new_name);
			});

		});
	},
	set_add_row:function()
	{
		jQuery('.wt_sc_meta_item_tb_add_row').on('click', function(){
			var tb=jQuery(this).parents('table');
			if(parseInt(tb.parent('.wt_sc_coupon_fieldset').attr('data-disabled'))===1)
			{
				return false;
			}
			var first_row=tb.find('tbody tr:eq(0)');
			first_row.find('.wt_sc_select2').select2("destroy"); /* destroy select2 before cloning */
			var new_row=first_row.clone().insertBefore(jQuery(this).parents('tr')); /* clone and insert before the add button row */
			
			/* reset all values to default */		
			new_row.find('input, select').each(function(){
				jQuery(this).val(jQuery(this).attr('data-default-val'));
			});
			
			/* enable select2 */
			wt_sc_coupon_edit_meta_item_table.reg_multi_select(first_row.find('.wt_sc_select2')); 
			wt_sc_coupon_edit_meta_item_table.reg_multi_select(new_row.find('.wt_sc_select2'));

			tb.find('.wt_sc_meta_item_tb_delete_row').css({'opacity':1, 'cursor':'pointer'}); /* enable row delete function */
			
			wt_sc_coupon_edit_meta_item_table.set_table_form_field_index(tb);
		});
	},
	set_remove_row:function()
	{
		jQuery(document).on('click', '.wt_sc_meta_item_tb_delete_row', function(){
			var tb=jQuery(this).parents('table');
			if(parseInt(tb.parent('.wt_sc_coupon_fieldset').attr('data-disabled'))===1)
			{
				return false;
			}
			if(tb.children('tbody').find('tr').length<=2)
			{
				jQuery(this).parents('tr').find('input, select').each(function(){
					jQuery(this).val(jQuery(this).attr('data-default-val'));
				});
				wt_sc_coupon_edit_meta_item_table.reg_multi_select(jQuery(this).parents('tr').find('.wt_sc_select2'));

				tb.find('.wt_sc_meta_item_tb_delete_row').css({'opacity':.5, 'cursor':'not-allowed'});
				
				wt_sc_coupon_edit_meta_item_table.clear_parent_elm_val(tb.find('.wt_sc_select2:eq(0)'));
				return false;
			}
			
			var row=jQuery(this).parents('tr');
			row.remove();

			wt_sc_coupon_edit_meta_item_table.set_table_form_field_index(tb);

			wt_sc_coupon_edit_meta_item_table.set_val_to_parent_elm(tb.find('.wt_sc_select2:eq(0)'));
		});
	},
	display_result:function(self, select2_args)
	{
		jQuery( self ).selectWoo( select2_args ).addClass( 'enhanced' );

		jQuery(self).on("change", function (e) {
			wt_sc_coupon_edit_meta_item_table.set_val_to_parent_elm(jQuery(self)); 
		});

		if(jQuery(self).data('sortable')) {
			var $select = jQuery(self);
			var $list   = jQuery( self ).next( '.select2-container' ).find( 'ul.select2-selection__rendered' );

			$list.sortable({
				placeholder : 'ui-state-highlight select2-selection__choice',
				forcePlaceholderSize: true,
				items       : 'li:not(.select2-search__field)',
				tolerance   : 'pointer',
				stop: function() {
					jQuery( $list.find( '.select2-selection__choice' ).get().reverse() ).each( function() {
						var id     = jQuery( this ).data( 'data' ).id;
						var option = $select.find( 'option[value="' + id + '"]' )[0];
						$select.prepend( option );
					} );
				}
			});
		// Keep multiselects ordered alphabetically if they are not sortable.
		} else if ( jQuery( self ).prop( 'multiple' ) ) {
			jQuery( self ).on( 'change', function(){
				var $children = jQuery( self ).children();
				$children.sort(function(a, b){
					var atext = a.text.toLowerCase();
					var btext = b.text.toLowerCase();

					if ( atext > btext ) {
						return 1;
					}
					if ( atext < btext ) {
						return -1;
					}
					return 0;
				});
				jQuery( self ).html( $children );
			});
		}
	},
	reg_multi_select:function(elms)
	{
		if(elms.hasClass('wt_sc_product_search'))
		{
			this.reg_product_search(elms);

		}else if(elms.hasClass('wt_sc_category_search'))
		{
			this.reg_category_search(elms);
		}
	},
	reg_category_search:function(elms)
	{
		elms.each( function() {
			var select2_args = {
				allowClear        : jQuery( this ).data( 'allow_clear' ) ? true : false,
				placeholder       : jQuery( this ).data( 'placeholder' ),
				minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : 3,
				escapeMarkup      : function( m ) {
					return m;
				},
				ajax: {
					url:         wc_enhanced_select_params.ajax_url,
					dataType:    'json',
					delay:       250,
					data:function( params ) {
						return {
							term:     params.term,
							action:   'woocommerce_json_search_categories',
							security: wc_enhanced_select_params.search_categories_nonce
						};
					},
					processResults: function( data ) {
						var terms = [];
						if ( data ) {
							jQuery.each( data, function( id, term ) {
								terms.push({
									id:   id,
									text: term.name
								});
							});
						}
						return {
							results: terms
						};
					},
					cache: true
				}
			};

			jQuery(this).selectWoo(select2_args).addClass('enhanced');

			jQuery(this).on("change", function (e) { 
				wt_sc_coupon_edit_meta_item_table.set_val_to_parent_elm(jQuery(this)); 
			});
		});
	},
	reg_product_search:function(elms)
	{
		// Ajax product search box
		elms.each( function() {
			var select2_args = {
				allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
				placeholder: jQuery( this ).data( 'placeholder' ),
				minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
				escapeMarkup: function( m ) {
					return m;
				},
				ajax: {
					url:         wc_enhanced_select_params.ajax_url,
					dataType:    'json',
					delay:       250,
					data:        function( params ) {
						return {
							term         : params.term,
							action       : jQuery( this ).data( 'action' ) || 'woocommerce_json_search_products_and_variations',
							security     : wc_enhanced_select_params.search_products_nonce,
							exclude      : jQuery( this ).data( 'exclude' ),
							exclude_type : jQuery( this ).data( 'exclude_type' ),
							include      : jQuery( this ).data( 'include' ),
							limit        : jQuery( this ).data( 'limit' ),
							display_stock: jQuery( this ).data( 'display_stock' )
						};
					},
					processResults: function( data ) {
						var terms = [];
						if ( data ) {
							jQuery.each( data, function( id, text ) {
								terms.push( { id: id, text: text } );
							});
						}
						return {
							results: terms
						};
					},
					cache: true
				}
			};

			wt_sc_coupon_edit_meta_item_table.display_result( this, select2_args );
		});
	},
	clear_parent_elm_val:function(sele_elm)
	{
		var parent_elm=sele_elm.parents('.wt_sc_coupon_fieldset').data('parent-select');
		if(typeof parent_elm!='undefined' && parent_elm.length)
		{
			parent_elm.val(null).trigger('change');
		}
	},

	/**
	 * 	Add/remove the product ids to the parent woocommerce default field
	 */
	set_val_to_parent_elm:function(sele_elm)
	{
		var parent_elm=sele_elm.parents('.wt_sc_coupon_fieldset').data('parent-select');
		if(typeof parent_elm!='undefined' && parent_elm.length)
		{
			parent_elm.val(null).trigger('change');
			sele_elm.parents('.wt_sc_coupon_meta_item_table').find('.wt_sc_select2').each(function(){
				var selected_opt=jQuery(this).find(':selected');
				if(selected_opt.length)
				{
					var opt=new Option(selected_opt.text(), selected_opt.val(), true, true);
					parent_elm.append(opt).trigger('change');
				}
			});
		}		
	},

}

/**
 *  Popup creator
 * 	@since 1.4.1
 */
var wt_sc_popup={
	Set:function()
	{		
		jQuery('body').prepend('<div class="wt_sc_cst_overlay"></div>');
		this.regPopupOpen();
		this.regPopupClose();
	},
	regPopupOpen:function()
	{
		jQuery('[data-wt_sc_popup]').on('click',function(){
			var elm_class=jQuery(this).attr('data-wt_sc_popup');
			var elm=jQuery('.'+elm_class);
			if(elm.length>0)
			{
				wt_sc_popup.showPopup(elm);
			}
		});
	},
	showPopup:function(popup_elm)
	{
		var pw=popup_elm.outerWidth();
		var wh=jQuery(window).height();
		var ph=wh-150;
		popup_elm.css({'margin-left':((pw/2)*-1),'display':'block','top':'20px'}).animate({'top':'50px'});
		popup_elm.find('.wt_sc_popup_body').css({'max-height':ph+'px','overflow':'auto'});
		jQuery('.wt_sc_cst_overlay').show();
	},
	hidePopup:function()
	{
		jQuery('.wt_sc_popup_close').trigger('click');
	},
	regPopupClose:function(popup_elm)
	{
		jQuery(document).on('keyup', function(e){
			if('Escape' === e.key)
			{
				wt_sc_popup.hidePopup();
			}
		});
		jQuery('.wt_sc_popup_close, .wt_sc_popup_cancel, .wt_sc_cst_overlay').off('click').on('click',function(){
			jQuery('.wt_sc_cst_overlay, .wt_sc_popup').hide();
		});
	}
}

/**
 *  Tab view
 * 	
 * 	@since 1.4.4
 */
var wt_sc_tab_view=
{
	Set:function()
	{
		this.subTab();
		var wt_sc_nav_tab=jQuery('.wt-sc-tab-head .nav-tab');
	 	if(wt_sc_nav_tab.length>0)
	 	{
		 	wt_sc_nav_tab.on('click',function(){
		 		var wt_sc_tab_hash=jQuery(this).attr('href');
		 		wt_sc_nav_tab.removeClass('nav-tab-active');
		 		jQuery(this).addClass('nav-tab-active');
		 		wt_sc_tab_hash=wt_sc_tab_hash.charAt(0)=='#' ? wt_sc_tab_hash.substring(1) : wt_sc_tab_hash;
		 		var wt_sc_tab_elm=jQuery('div[data-id="'+wt_sc_tab_hash+'"]');
		 		jQuery('.wt-sc-tab-content').hide();
		 		if(wt_sc_tab_elm.length>0 && wt_sc_tab_elm.is(':hidden'))
		 		{	 		
		 			wt_sc_tab_elm.fadeIn();
		 		}
		 	});
		 	jQuery(window).on('hashchange', function (e) {
			    var location_hash=window.location.hash;
			 	if("" !== location_hash)
			 	{
			    	wt_sc_tab_view.showTab(location_hash);
			    }
			}).trigger('hashchange');

		 	var location_hash=window.location.hash;
		 	if("" !== location_hash)
		 	{
		 		wt_sc_tab_view.showTab(location_hash);
		 	}else
		 	{
		 		wt_sc_nav_tab.eq(0).trigger('click');
		 	}		 	
		}
	},
	showTab:function(location_hash)
	{
		var wt_sc_tab_hash=location_hash.charAt(0)=='#' ? location_hash.substring(1) : location_hash;
 		if("" !== wt_sc_tab_hash)
 		{
 			var wt_sc_tab_hash_arr=wt_sc_tab_hash.split('#');
 			wt_sc_tab_hash=wt_sc_tab_hash_arr[0];
 			var wt_sc_tab_elm=jQuery('div[data-id="'+wt_sc_tab_hash+'"]');
	 		if(wt_sc_tab_elm.length>0 && wt_sc_tab_elm.is(':hidden'))
	 		{	 			
	 			jQuery('a[href="#'+wt_sc_tab_hash+'"]').trigger('click');
	 			if(wt_sc_tab_hash_arr.length>1)
		 		{
		 			var wt_sc_sub_tab_link=wt_sc_tab_elm.find('.wt_sc_sub_tab');
		 			if(wt_sc_sub_tab_link.length>0) /* subtab exists  */
		 			{
		 				var wt_sc_sub_tab=wt_sc_sub_tab_link.find('li[data-target='+wt_sc_tab_hash_arr[1]+']');
		 				wt_sc_sub_tab.trigger('click');
		 			}
		 		}
	 		}
 		}
	},
	subTab:function()
	{
		jQuery('.wt_sc_sub_tab li').on('click',function(){
			var trgt=jQuery(this).attr('data-target');
			var prnt=jQuery(this).parent('.wt_sc_sub_tab');
			var ctnr=prnt.siblings('.wt_sc_sub_tab_container');
			prnt.find('li a').css({'color':'#0073aa','cursor':'pointer', 'font-weight':'normal'});
			jQuery(this).find('a').css({'color':'#000','cursor':'default', 'font-weight':'500'});
			ctnr.find('.wt_sc_sub_tab_content').hide();
			ctnr.find('.wt_sc_sub_tab_content[data-id="'+trgt+'"]').fadeIn();
		});
		jQuery('.wt_sc_sub_tab').each(function(){
			var elm=jQuery(this).children('li').eq(0);
			elm.trigger('click');
		});
		jQuery('.wt_sc_sub_tab_trigger').on('click', function(){
			var trgt=jQuery(this).attr('data-target');
			jQuery('.wt_sc_sub_tab li[data-target="'+trgt+'"]').trigger('click');
		});
	}
}

var wt_sc_settings_form=
{
	Set:function()
	{
		jQuery('.wt_sc_settings_form').find('[required]').each(function(){
			jQuery(this).removeAttr('required').attr('data-settings-required','');
		});

		jQuery('.wt_sc_settings_form').on('submit', function(e){
			e.preventDefault();
			if(!wt_sc_settings_form.validate(jQuery(this)))
			{
				return false;
			}

			var settings_base=jQuery(this).find('.wt_sc_settings_base').val();
			var data=jQuery(this).serialize();

			var submit_btn=jQuery(this).find('input[type="submit"]');
			var spinner=submit_btn.siblings('.spinner');
			spinner.css({'visibility':'visible'});
			submit_btn.css({'opacity':'.5','cursor':'default'}).prop('disabled',true);	

			jQuery.ajax({
				url:WTSmartCouponAdminOBJ.ajaxurl,
				type:'POST',
				dataType:'json',
				data:data+'&wt_sc_settings_base='+settings_base+'&action=wt_sc_save_settings&_wpnonce='+WTSmartCouponAdminOBJ.nonce,
				success:function(data)
				{
					spinner.css({'visibility':'hidden'});
					submit_btn.css({'opacity':'1','cursor':'pointer'}).prop('disabled',false);
					if(true === data.status)
					{
						wt_sc_notify_msg.success(data.msg);
					}else
					{
						wt_sc_notify_msg.error(data.msg);
					}
				},
				error:function () 
				{
					spinner.css({'visibility':'hidden'});
					submit_btn.css({'opacity':'1','cursor':'pointer'}).prop('disabled',false);
					wt_sc_notify_msg.error(WTSmartCouponAdminOBJ.msgs.settings_error, false);
				}
			});
		});
	},
	validate:function(form_elm)
	{
		var is_valid=true;
		form_elm.find('[data-settings-required]').each(function(){
			var elm=jQuery(this);
			if(elm.val().trim() === "" && elm.is(':visible'))
			{
				var required_msg=elm.attr('data-required-msg');
				if(typeof required_msg === 'undefined')
				{
					var prnt=elm.parents('tr');
					var label=prnt.find('th label');				
					var temp_elm=jQuery('<div />').html(label.html());
					temp_elm.find('.wt_sc_required_field').remove();
					required_msg='<b><i>'+temp_elm.text()+'</i></b>'+WTSmartCouponAdminOBJ.msgs.is_required;
				}

				wt_sc_notify_msg.error(required_msg);
				is_valid=false;
				return false;
			}			
		});
		return is_valid;
	}
}