/**
 *  Javascript section of coupon styling
 * 	@since 1.4.7
 */	
(function( $ ) {
	//'use strict';
	$(function() { 
		
		/* initiate color picker */
		$('.wt_sc_coupon_colors .wt_sc_color_picker').wpColorPicker({
			'change':function(event, ui) { 
				var selected_color=ui.color.toString();			
				
				var target_elm=$(event.target);
					target_elm.val(selected_color);

				var coupon_type=target_elm.attr('data-coupon_type');
				
				wt_sc_update_color(coupon_type);
			 }
		});

		/* update color on page load */
		$('.wt_sc_coupon_colors').each(function(){
			var coupon_type=$(this).attr('data-coupon_type');
			wt_sc_update_color(coupon_type);
		});

		/** change the popup */
		$('.wt_sc_coupon_change_theme_link').on('click', function(){
			var coupon_type=$(this).attr('data-coupon_type');
			var style_key=$(this).parents('.wt_sc_sub_tab_content').find('.wt_sc_selected_coupon_style_input').val();
			$('.wt_sc_coupon_templates .wt_sc_single_template_box[data-style_key="'+style_key+'"]').css({'box-shadow':'0px 0px 5px rgb(81, 159, 242)'});
			$('.wt_sc_coupon_templates .wt_sc_single_template_box').attr('data-coupon_type', coupon_type);
			wt_sc_popup.showPopup($('.wt_sc_coupon_templates'));
		});

		/* Choose template from preset */
		$('.wt_sc_coupon_templates .wt_sc_single_template_box').on('click', function(){
			
			var coupon_type = $(this).attr('data-coupon_type');	
			$('.wt_sc_coupon_preview[data-coupon_type="'+coupon_type+'"]').html($(this).find('.wt_sc_single_template_box_inner').html());

			/* update current style */
			var style_key = $(this).attr('data-style_key');
			$('input[name="wt_coupon_styles['+coupon_type+'][style]"]').val(style_key);

			var color_config = $('.wt_sc_coupon_preview[data-coupon_type="'+coupon_type+'"] .wt_sc_template_refer').attr('data-color-config');
			var color_config_arr = color_config.split('|');

			var color_form_elm = $('.wt_sc_coupon_colors[data-coupon_type="'+coupon_type+'"] .wt_sc_coupon_color_form_element');
			color_form_elm.hide();
			
			/* update color pickers */
			for(var ee = 0; ee < color_config_arr.length; ee++)
			{
				color_form_elm.eq(ee).show();
				color_form_elm.eq(ee).find('input.wt_sc_color_picker').val(color_config_arr[ee]).attr('data-style_type', style_key).iris('color', color_config_arr[ee]);
			}

			wt_sc_popup.hidePopup();
		});


		/** submit form */
		$('.wt_sc_coupon_style_form').on('submit', function(e){
			e.preventDefault();
			
			var data=$(this).serialize();

			var submit_btn=$(this).find('input[type="submit"]');
			var spinner=submit_btn.siblings('.spinner');
			spinner.css({'visibility':'visible'});
			submit_btn.css({'opacity':'.5','cursor':'default'}).prop('disabled',true);

			$.ajax({
				url:wt_sc_customizer_params.ajax_url,
				type:'POST',
				dataType:'json',
				data:data,
				success:function(data)
				{
					spinner.css({'visibility':'hidden'});
					submit_btn.css({'opacity':'1','cursor':'pointer'}).prop('disabled',false);
					if(data.status)
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
					wt_sc_notify_msg.error(wt_sc_customizer_params.msgs.settings_error, false);
				}

			});
		});


		function wt_sc_update_color(coupon_type)
		{
			var preview_elm = $('.wt_sc_coupon_preview[data-coupon_type="'+coupon_type+'"]');
			var reference_elm_html = preview_elm.find('.wt_sc_template_refer').html();
			var color_picker_elm = $('.wt_sc_coupon_colors[data-coupon_type="'+coupon_type+'"] .wt_sc_color_picker');
			
			color_picker_elm.each(function(index){
						
				var find_str = '[wt_sc_color_'+index+']';
				find_str = find_str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
				var regexp = new RegExp(find_str, 'g');

				var color = $(this).val();
				reference_elm_html = reference_elm_html.replace(regexp, color);
			});

			preview_elm.children('.wt_sc_single_coupon').replaceWith(reference_elm_html);
		}

	});
})( jQuery );