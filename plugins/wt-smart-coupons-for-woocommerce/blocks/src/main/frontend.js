import metadata from './block.json';
import { useDispatch, useSelect } from '@wordpress/data';
import { __, sprintf, _n } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { CART_STORE_KEY, VALIDATION_STORE_KEY } from '@woocommerce/block-data';
import { useState, useEffect } from '@wordpress/element';
import { applyCheckoutFilter, extensionCartUpdate } from '@woocommerce/blocks-checkout';


// Global import
const { registerCheckoutBlock } = wc.blocksCheckout;

    
export const WtScBlocksMain = (() => {
    const [couponCode, setCouponCode] = useState('');
    const [isClickBinded, setIsClickBinded] = useState(false);
    const { applyCoupon, removeCoupon } = useDispatch( CART_STORE_KEY );
    const { createErrorNotice, createSuccessNotice } = useDispatch( 'core/notices' );

    if ( ! isClickBinded ) {
        
        /* Set `true` for preventing multiple event binding */
        setIsClickBinded(true);
        
        /* Click event triggered by plugin public JS file */
        document.addEventListener('wt_sc_api_coupon_clicked', function(e){ 

            const couponCode = e.detail.coupon_code;
            const couponId = e.detail.coupon_id;
            const context = 'wc/cart'; /* message context */

            applyCoupon(couponCode)
                .then((response) => { 
                    
                    /* Trigger a custom event */
                    const coupon_click_done_event = new CustomEvent("wt_sc_api_coupon_click_done", {
                        detail:{ 'coupon_code' : couponCode, 'coupon_id': couponId, 'status': true}
                    });
                    document.dispatchEvent(coupon_click_done_event);

                    /* Show success message */
                    if (
                        applyCheckoutFilter( {
                            filterName: 'showApplyCouponNotice',
                            defaultValue: true,
                            arg: { couponCode, context },
                        } )
                    ) {
                        createSuccessNotice(
                            sprintf(
                                /* translators: %s coupon code. */
                                __('Coupon code "%s" has been applied to your cart.', 'wt-smart-coupons-for-woocommerce'),
                                couponCode
                            ),
                            { id: 'coupon-form', type: 'snackbar', context }
                        );
                    }

                })
                .catch((error) => {            
                    
                    /* Trigger a custom event */
                    const coupon_click_done_event = new CustomEvent("wt_sc_api_coupon_click_done", {
                        detail:{ 'coupon_code' : couponCode, 'coupon_id': couponId, 'status': false, 'message': error.message}
                    });
                    document.dispatchEvent(coupon_click_done_event);

                    /* Show error message */
                    createErrorNotice(
                        error.message,
                        {
                            id: 'coupon-form',
                            type: 'snackbar',
                            context,
                        }
                    );
                });    
        });

    }
    
    return '';
});

const options = {
    metadata,
    component: WtScBlocksMain
};

registerCheckoutBlock( options );
