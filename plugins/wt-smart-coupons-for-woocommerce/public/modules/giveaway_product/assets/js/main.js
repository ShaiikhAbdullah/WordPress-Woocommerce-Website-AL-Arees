jQuery(function ($) {
    "use strict";

    $(document).on('change', '.wt_give_away_product_attr', function(){
               
        var parent = $(this).closest('.wt_get_away_product');
        var attributes = {};
        parent.find('.wt_give_away_product_attr').each(function(index) {
            attributes[$(this).attr('data-attribute_name')] = $(this).val();
        });

        if(""==$(this).val())
        {
            parent.find('input[name="variation_id"]').val(0);
            parent.find('input[name="wt_variation_options"]').val(JSON.stringify( attributes ));
            return false;
        }

        var product_id =parent.attr('product-id');

        var data = {
            'action'        : 'update_variation_id',
            'attributes'    : attributes,
            'product'       : product_id,
            '_wpnonce'      : WTSmartCouponOBJ.nonces.public
        };

        $('.wt_choose_free_product, .wt_add_to_cart_all_btn, .checkout-button').prop('disabled', true).css({'opacity':.5, 'cursor':'not-allowed'});
        $('.wt_get_away_product').css({'opacity':.5, 'cursor':'wait'});
        jQuery.ajax({
            type: "POST",
            async: true,
            url: WTSmartCouponOBJ.ajaxurl,
            data: data,
            dataType: 'json',
            success:function(response)
            {
                $('.wt_choose_free_product, .wt_add_to_cart_all_btn, .checkout-button').prop('disabled', false).css({'opacity':1, 'cursor':'pointer'});
                $('.wt_get_away_product').css({'opacity':1, 'cursor':'default'});
                if(response.status==true)
                {
                    parent.find('input[name="variation_id"]').val(response.variation_id);
                    parent.find('input[name="wt_variation_options"]').val(JSON.stringify( attributes ));
                }else{
                    parent.find('input[name="variation_id"]').val(0);
                    alert(response.status_msg);
                }              
            },
            error:function()
            {
                parent.find('input[name="variation_id"]').val(0);
                $('.wt_choose_free_product, .wt_add_to_cart_all_btn, .checkout-button').prop('disabled', false).css({'opacity':1, 'cursor':'pointer'});
                $('.wt_get_away_product').css({'opacity':1, 'cursor':'default'});
                alert(WTSmartCouponOBJ.labels.error);
            }
        });
    });

    $(document).on('click', '.wt_add_to_cart_all_btn', function(e){
        
        e.preventDefault();
        var coupon_id=$(this).attr('coupon-id');
        var free_product_container=$('.wt_give_away_products[coupon="'+coupon_id+'"]');
        
        var variation_id_arr=new Array();
        var product_id_arr=new Array();
        var attributes_arr=new Array();
        var is_error=false;
        free_product_container.find('.wt_get_away_product').each(function(){
            
            if($(this).attr('data-is_purchasable')==1)
            {
                var variation_id_obj=$(this).find('[name="variation_id"]');
                if(variation_id_obj.length>0) /* variable product */
                {
                    if(variation_id_obj.val().trim()=="" || variation_id_obj.val().trim()=="0")
                    {
                        alert(WTSmartCouponOBJ.labels.choose_variation);
                        is_error=true;
                        return false;
                    }else
                    {
                        variation_id_arr.push(variation_id_obj.val());
                        attributes_arr.push(JSON.parse($(this).find('[name="wt_variation_options"]').val()));
                    }
                }else{
                    variation_id_arr.push(0);
                    attributes_arr.push([]);
                }
                var product_id=$(this).attr('product-id');
                product_id_arr.push(product_id);
            }

        });

        if(is_error===true)
        {
            return false;
        }

        var data = {
            'action'            : 'wt_choose_free_product',
            '_wpnonce'          : WTSmartCouponOBJ.nonces.public,
            'product_id'        : 0,
            'variation_id'      : 0,
            'attributes'        : attributes_arr,
            'coupon_id'         : coupon_id,
            'product_id_arr'    : product_id_arr,
            'variation_id_arr'  : variation_id_arr,
            'add_to_cart_all'   : 1
        };
        wt_sc_ajax_add_giveaway($(this), data);

    });

    function wt_sc_ajax_add_giveaway(btn_elm, data)
    {
        var html_back=btn_elm.html();
        btn_elm.html(WTSmartCouponOBJ.labels.please_wait);
        
        var all_btn_elms=$('.wt_add_to_cart_all_btn, .wt_choose_free_product');

        all_btn_elms.prop('disabled', true);

        if ( ! $('.woocommerce-notices-wrapper').length ) {
            $('#main').prepend( '<div class="woocommerce-notices-wrapper"></div>' );
        }

        $('.woocommerce-notices-wrapper').html('');
        
        jQuery.ajax({
            type:"POST",
            async:true,
            url:WTSmartCouponOBJ.ajaxurl,
            data:data,
            success:function(response)
            {
                if(response=='success')
                {
                    location.reload();
                }else
                {
                    $('.woocommerce-notices-wrapper').html(response);
                    $("html, body").stop(true, true).animate({scrollTop:($('.woocommerce-notices-wrapper').offset().top-70)}, 500);
                    
                    btn_elm.html(html_back);
                    all_btn_elms.prop('disabled', false);
                }
            },
            error:function()
            {
                btn_elm.html(html_back);
                all_btn_elms.prop('disabled', false);
            }
        });
    }

    $(document).on('click','.wt_choose_free_product',function( e ) {
        e.preventDefault();
       
        var parent_obj = $(this).closest('.wt_get_away_product');
        if(parent_obj.attr('data-is_purchasable')==1)
        {
            var variation_id=0;
            var variation_id_obj=parent_obj.find('[name="variation_id"]');
            if(variation_id_obj.length>0) /* variable product */
            {
                if(variation_id_obj.val().trim()=="" || variation_id_obj.val().trim()=="0")
                {
                    alert(WTSmartCouponOBJ.labels.choose_variation);
                    return false;
                }else
                {
                    variation_id=variation_id_obj.val();
                }
            }

            var coupon_id = $(this).closest('.wt_give_away_products').attr('coupon');
            if(typeof coupon_id=='undefined')
            {
                return false;
            }

            var product_id = $(this).attr('prod-id');
            if(typeof product_id=='undefined')
            {
                return false;
            }

            var variation_attributes=( parent_obj.find( 'input[name="wt_variation_options"]' ).length>0 ? JSON.parse( parent_obj.find( 'input[name="wt_variation_options"]' ).val() ) : '' );
            var data = {
                'action'        : 'wt_choose_free_product',
                '_wpnonce'      : WTSmartCouponOBJ.nonces.public,
                'product_id'    : product_id,
                'variation_id'  : variation_id,
                'attributes'    : variation_attributes,
                'coupon_id'     : coupon_id
            };

            wt_sc_ajax_add_giveaway($(this), data);
        }
        
    });


});