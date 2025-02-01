jQuery(function ($) {
    "use strict";
    $('form.checkout').on('change.wt_sc_payment_method_change', 'input[name="payment_method"]', function () {

        var t = {updateTimer: !1, dirtyInput: !1,
            reset_update_checkout_timer: function () {
                clearTimeout(t.updateTimer)
            }, trigger_update_checkout: function () {
                t.reset_update_checkout_timer(), t.dirtyInput = !1,
                        $(document.body).trigger("update_checkout")
            }
        };
        t.trigger_update_checkout();
        
    });


    $('document').ready(function(){
        
        /* After the coupon box click event was done */
        $(document).on("wt_sc_api_coupon_click_done", function(e){
            wt_unblock_node($( 'div.wt_coupon_wrapper'));
            wt_unblock_node($("div.wt-mycoupons"));
            wt_unblock_node($("div.wt_store_credit"));
        });

        $(document).on("click", '.wt-single-coupon.active-coupon', function(){        
            
            if(!$('.woocommerce-notices-wrapper').length){
                $('#main').prepend('<div class="woocommerce-notices-wrapper"></div>');
            }
            
            var coupon_code = ( typeof $(this).attr('data-code') === 'undefined' ? $(this).find('code').text() : $(this).attr('data-code') );
            var coupon_id = $(this).attr('data-id');

            $('div.wt_coupon_wrapper, div.wt_store_credit').each(function(){
                if($(this).find('.wt-single-coupon').length)
                {
                    wt_block_node($(this));
                }
            });

            /* For checkout block compatibility */
            if( $('.wc-block-checkout, .wc-block-cart').length ) {
                
                const coupon_click_event = new CustomEvent("wt_sc_api_coupon_clicked", {
                    detail:{ 'coupon_code' : coupon_code, 'coupon_id': coupon_id}
                });
                document.dispatchEvent(coupon_click_event);
                return false;
            }
            
            var data = {
                'coupon_code'   : coupon_code,
                'coupon_id'     : coupon_id,
                '_wpnonce'      : WTSmartCouponOBJ.nonces.apply_coupon
            };

            $.ajax({
                type: "POST",
                async: true,
                url: WTSmartCouponOBJ.wc_ajax_url + 'apply_coupon_on_click',
                data: data,
                success: function (response) {
                    if ( $( '.woocommerce-cart-form' ).length) {
                        update_cart(true);  // need only for cart page
                    }
                    
                    wt_unblock_node( $( 'div.wt_coupon_wrapper' ) );
                    wt_unblock_node($("div.wt-mycoupons"));
                    wt_unblock_node($("div.wt_store_credit"));

                    $( '.woocommerce-error, .woocommerce-message, .woocommerce-info' ).remove();
                    show_notice( response );
                    $(document.body).trigger("update_checkout");
                    $( document.body ).trigger("applied_coupon");

                    $('html, body').animate({
                        scrollTop: $(".woocommerce").offset().top
                    }, 1000);
                }
            });

        });

        /* For checkout block */
        if( $('.wc-block-checkout').length ) {
            $( document ).on("click", '[name="radio-control-wc-payment-method-options"]', function(){
                WTSmartCouponOBJ.payment_method = $('[name="radio-control-wc-payment-method-options"]:checked').val();
                let parent_div = $('[name="radio-control-wc-payment-method-options"]').parents('.wc-block-components-radio-control');
                parent_div.find('.wc-block-components-radio-control__option').removeClass('wc-block-components-radio-control__option-checked');
                wbte_set_block_checkout_values();
            });

            setTimeout( wbte_set_block_checkout_values, 200);
        }
    
    });

    var wbte_set_block_checkout_values = function() {

        let payment_method = '';
        let shipping_method = {};
      
        if( $('[name="radio-control-wc-payment-method-options"]').length ) {
            
            /* Prepare payment method from radio button */
            payment_method = $('[name="radio-control-wc-payment-method-options"]:checked').val();           

        }
        
        /* Prepare shipping method from radio button */
        if( $('.wc-block-components-shipping-rates-control input[type="radio"]').length ) {         
            $('.wc-block-components-shipping-rates-control input[type="radio"]:checked').each(function(){
                let prefix = $(this).attr('name').split('-').pop();
                shipping_method[prefix] = $(this).val();
            });
        }

        /* Store the value to global variable to prevent future auto refresh blocking */
        WTSmartCouponOBJ.shipping_method = shipping_method;
        WTSmartCouponOBJ.payment_method = payment_method;

        
        /* Send ajax request to set the value */
        let order_summary_block = $('.wp-block-woocommerce-checkout-order-summary-block');
        wt_block_node( order_summary_block );
        
        $.ajax({
            type: "POST",
            async: true,
            url: WTSmartCouponOBJ.wc_ajax_url + 'wbte_sc_set_block_checkout_values',
            data: { '_wpnonce': WTSmartCouponOBJ.nonces.public, 'payment_method': payment_method, 'shipping_method': shipping_method },
            dataType: 'json',
            success:function( data ) {
                wt_unblock_node( order_summary_block );
            },
            error:function() {
               wt_unblock_node( order_summary_block ); 
            }
        });

        /* Trigger checkout block refresh */
        setTimeout(function(){ 
            const checkout_value_updated_event = new CustomEvent("wbte_sc_checkout_value_updated", {
                detail:{ 'payment_method' : payment_method, 'shipping_method': shipping_method }
            });
            document.dispatchEvent(checkout_value_updated_event);
        }, 1000);
        
    }

    
    /**
     * Function from cart.js by woocommmerce
     * @param {bool} preserve_notices 
     */
    var update_cart = function( preserve_notices ) {
        var $form = $( '.woocommerce-cart-form' );
        wt_block_node( $form );
        wt_block_node( $( 'div.cart_totals' ) );
        
        

        // Make call to actual form post URL.
        $.ajax( {
            type:     $form.attr( 'method' ),
            url:      $form.attr( 'action' ),
            data:     $form.serialize(),
            dataType: 'html',
            success:  function( response ) {
                update_wc_div( response, preserve_notices );
            },
            complete: function() {
                wt_unblock_node( $form );
                wt_unblock_node( $( 'div.cart_totals' ) );
            }
        } );
    }


    /**
     * 
     * @param {string} html_str 
     * @param {bool} preserve_notices 
     */
    var update_wc_div = function( html_str, preserve_notices ) {
        var $html       = $.parseHTML( html_str );
        var $new_form   = $( '.woocommerce-cart-form', $html );
        var $new_totals = $( '.cart_totals', $html );
        var $notices    = $( '.woocommerce-error, .woocommerce-message, .woocommerce-info', $html );

        // No form, cannot do this.
        if ( $( '.woocommerce-cart-form' ).length === 0 ) {
            window.location.href = window.location.href;
            return;
        }

        // Remove errors
        if ( ! preserve_notices ) {
            $( '.woocommerce-error, .woocommerce-message, .woocommerce-info' ).remove();
        }

        if ( $new_form.length === 0 ) {
            // If the checkout is also displayed on this page, trigger reload instead.
            if ( $( '.woocommerce-checkout' ).length ) {
                window.location.href = window.location.href;
                return;
            }

            // No items to display now! Replace all cart content.
            var $cart_html = $( '.cart-empty', $html ).closest( '.woocommerce' );
            $( '.woocommerce-cart-form__contents' ).closest( '.woocommerce' ).replaceWith( $cart_html );

            // Display errors
            if ( $notices.length > 0 ) {
                show_notice( $notices );
            }
        } else {
            // If the checkout is also displayed on this page, trigger update event.
            if ( $( '.woocommerce-checkout' ).length ) {
                $( document.body ).trigger( 'update_checkout' );
            }

            $( '.woocommerce-cart-form' ).replaceWith( $new_form );
            $( '.woocommerce-cart-form' ).find( ':input[name="update_cart"]' ).prop( 'disabled', true );

            if ( $notices.length > 0 ) {
                show_notice( $notices );
            }

            update_cart_totals_div( $new_totals );
        }

        $( document.body ).trigger( 'updated_wc_div' );
    };
    

    /**
     * Function from woocmmerce cart.js
     * @param {string} html_str 
     */
    var update_cart_totals_div = function( html_str ) {
        $( '.cart_totals' ).replaceWith( html_str );
        $( document.body ).trigger( 'updated_cart_totals' );
    };



    /**
     * function from cart.js by wooocommerce
     * @param { jQuery object } node 
     */
    var wt_block_node = function( node ) {

        node.addClass( 'processing' );

        if(typeof $.fn.block === 'function')
        {
            node.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        }
    }
    window.wbte_sc_block_node = wt_block_node;
    
    /**
     * function from cart.js by wooocommerce
     * @param {jQuery object} $node 
     */
    var wt_unblock_node = function( node ) {
        
        node.removeClass( 'processing' );
        
        if(typeof $.fn.unblock === 'function')
        {
            node.unblock();
        }      
    };
    window.wbte_sc_unblock_node = wt_unblock_node;


    var show_notice = function( html_element, $target ) {
        if ( ! $target ) {
            $target = $( '.woocommerce-notices-wrapper:first' ) || $( '.cart-empty' ).closest( '.woocommerce' ) || $( '.woocommerce-cart-form' );
        }
        $target.prepend( html_element );
    };

});