(function( $ ) {
	'use strict';

	var wt_sc_coupon_usage_restriction=
	{
		Set:function()
		{
			/* Insert Product condition */
			var product_ids_elm = $("#woocommerce-coupon-data .form-field").has('[name="product_ids[]"]'); //Since WC3.0.0
			if (product_ids_elm.length != 1) product_ids_elm = $("#woocommerce-coupon-data .form-field").has('[name="product_ids"]'); //Prior to WC3.0.0
			if (product_ids_elm.length == 1) {
				var product_ids_elm = $("#woocommerce-coupon-data .form-field").has('[name="product_ids[]"]'); 
				$("#woocommerce-coupon-data .form-field._wt_product_condition_field").insertBefore(product_ids_elm);
				product_ids_elm.parents('.options_group').addClass('wt_sc_coupon_product_restriction_fields');
			}
			
			/* Insert `all product` condition section. Disable/Enable product,category restriction */
			$('#woocommerce-coupon-data .form-field._wt_enable_product_category_restriction_field').detach().insertAfter("#woocommerce-coupon-data .form-field.exclude_sale_items_field");

			/* Insert individual quantity condition */
			$('#woocommerce-coupon-data .form-field._wt_use_individual_min_max_field').detach().insertAfter("#woocommerce-coupon-data .form-field._wt_enable_product_category_restriction_field");

			/* Insert Category Condiiton */
			var product_cat_elm = $("#woocommerce-coupon-data .form-field").has('[name="product_categories[]"]');
			if (product_cat_elm.length == 1)
			{
				$("#woocommerce-coupon-data .form-field._wt_category_condition_field").insertBefore(product_cat_elm);
				product_cat_elm.parents('.options_group').addClass('wt_sc_coupon_category_restriction_fields');
			}

			/* Insert matching fields */
			var ex_cat_ids_elm = $("#woocommerce-coupon-data .form-field").has('[name="exclude_product_categories[]"]');
			if(ex_cat_ids_elm.length==1)
			{
				$('.wt_sc_coupon_restriction_matching_products').insertAfter(ex_cat_ids_elm.parents('.options_group'));
			}

			/* Adjust width of exclude_categories select2 width */
			ex_cat_ids_elm.find('.select2-container').css({'width':'90%', 'max-width':'600px'});
			ex_cat_ids_elm.find('.select2-search--inline, .select2-search__field').css({'width':'100%'});

			/* Hide WC default tooltip and add custom help text */
			ex_cat_ids_elm.find('.woocommerce-help-tip').hide();
			ex_cat_ids_elm.find('.woocommerce-help-tip').after($('.wt_sc_exclude_category_help'));


			/* Adjust width of exclude_product_ids select2 width */
			var exclude_product_ids_elm = $("#woocommerce-coupon-data .form-field").has('[name="exclude_product_ids[]"]');
			exclude_product_ids_elm.find('.select2-container').css({'width':'90%', 'max-width':'600px'});
			exclude_product_ids_elm.find('.select2-search--inline, .select2-search__field').css({'width':'100%'});

			/* Hide WC default tooltip and add custom help text */
			exclude_product_ids_elm.find('.woocommerce-help-tip').hide();
			exclude_product_ids_elm.find('.woocommerce-help-tip').after($('.wt_sc_exclude_product_help'));

			/* Replace WC default restriction fields with WT fields */
			this.set_restriction_fields("product_ids[]", '.wt_sc_coupon_products_fieldset');
			this.set_restriction_fields("product_categories[]", '.wt_sc_coupon_categories_fieldset');


			this.reg_restriction_events();
			
		},

		/**
		*	Register restriction field events
		*/
		reg_restriction_events()
		{
			/**
			 *  Product/Category restriction toggle
			 */
			$('.wt_enable_product_category_restriction').on('click', function(){
				wt_sc_coupon_usage_restriction.toggle_restriction_fields();
			});
			/* on page load */
			wt_sc_coupon_usage_restriction.toggle_restriction_fields();


			/**
			 *  Quantity min/max toggle
			 */
			$('[name="_wt_use_individual_min_max"]').on('click', function(){
				wt_sc_coupon_usage_restriction.toggle_min_max_options();
			});
			/* on page load */
			wt_sc_coupon_usage_restriction.toggle_min_max_options();
		},

		/**
		*	Enable/Disable restriction fields
		*/
		toggle_restriction_fields:function()
		{
			var restriction_field_set=$('.wt_sc_coupon_product_restriction_fields, .wt_sc_coupon_category_restriction_fields');
			if($('.wt_enable_product_category_restriction').is(':checked'))
			{
				restriction_field_set.show();

				/* restore the values of WC default product/category fields from WT product/category fields */
				wt_sc_coupon_edit_meta_item_table.set_val_to_parent_elm($('.wt_sc_coupon_products_fieldset').find('.wt_sc_select2:eq(0)'));
				wt_sc_coupon_edit_meta_item_table.set_val_to_parent_elm($('.wt_sc_coupon_categories_fieldset').find('.wt_sc_select2:eq(0)'));

				if(typeof wt_sc_giveaway_params.bogo_coupon_type!='undefined' && wt_sc_giveaway_params.bogo_coupon_type==$('#discount_type').val())
				{
					$('.wt_sc_coupon_category_restriction_fields').hide();
				}

			}else 
			{
				restriction_field_set.hide();

				/* empty WC default product/exclude product fields */
				$('[name="product_ids[]"]').val(null).trigger('change');
				$('[name="exclude_product_ids[]"]').val(null).trigger('change');

				/* empty WC default category/exclude category fields */
				$('[name="product_categories[]"]').val(null).trigger('change');
				$('[name="exclude_product_categories[]"]').val(null).trigger('change');
			}
			this.toggle_min_max_options();
		},

		toggle_min_max_options:function()
		{
			var wt_global_qty_fields=$('._wt_min_matching_product_qty_field, ._wt_max_matching_product_qty_field');
			if($('[name="_wt_use_individual_min_max"]').is(':checked'))
			{
				$('.wt_sc_coupon_restriction_min_max input').prop('disabled', false);
				if($('.wt_enable_product_category_restriction').is(':checked'))
				{
					wt_global_qty_fields.hide();
				}else{
					wt_global_qty_fields.show();
				}
			}else
			{
				$('.wt_sc_coupon_restriction_min_max input').prop('disabled', true);
				wt_global_qty_fields.show();
			}
		},

		/**
		*	Position restriction fields. 
		* 	This function will hide default WC restriction fields and add WT restriction fields
		*/
		set_restriction_fields:function(form_field_name, fieldset_selector) 
		{
			var form_field_elm=$("#woocommerce-coupon-data .form-field").has('[name="'+form_field_name+'"]');
			var fieldset_elm=$(fieldset_selector);
			if(form_field_elm.length)
			{
				fieldset_elm.insertAfter(form_field_elm);
				fieldset_elm.data('parent-select', form_field_elm.find('[name="'+form_field_name+'"]'));
				form_field_elm.hide();
			}else{
				fieldset_elm.hide();
			}
		},			
	}

	$(document).ready(function(){
		wt_sc_coupon_usage_restriction.Set();
	});

})( jQuery );