import metadata from './block.json';
const { registerCheckoutFilters,extensionCartUpdate } = window.wc.blocksCheckout;
const { jQuery } = window;


// Declare some global variables.
var wbte_auto_coupon_close_remove_timeout = null;
var wbte_auto_coupon_quantity_click_timeout = null;
var wbte_auto_coupon_needs_refresh_timeout = null;
var wbte_auto_coupon_needs_refresh = false;


// Set a flag to trigger an extra refresh
jQuery(document).ready(function(){
    
    // On value change.
    jQuery(document).on('input', '.wc-block-components-quantity-selector__input', function(){              
        clearTimeout(wbte_auto_coupon_quantity_click_timeout);
        wbte_auto_coupon_quantity_click_timeout = setTimeout(function(){
            wbte_auto_coupon_needs_refresh = true;
        }, 1000);
    });

    // On plus button click.
    jQuery(document).on('click', '.wc-block-components-quantity-selector__button--plus', function(){       
        clearTimeout(wbte_auto_coupon_quantity_click_timeout);
        wbte_auto_coupon_quantity_click_timeout = setTimeout(function(){
            wbte_auto_coupon_needs_refresh = true;
        }, 1000);      
    });
});  

/**
 *  Register checkout filter to remove auto coupon remove button.
 */
const updateDataToCart = ( defaultValue, extensions, args ) => {

    // Take auto coupon list from args.
    const applied_auto_coupon_list = args?.cart?.extensions?.wt_sc_blocks?.applied_auto_coupon_list;

    // Set a timer to avoid multiple function call.
    clearTimeout(wbte_auto_coupon_close_remove_timeout);
    wbte_auto_coupon_close_remove_timeout = setTimeout(function() {
        
        // Needs refresh, because quantity changed.
        if ( wbte_auto_coupon_needs_refresh ) { 
            
            // Add a timer to avoid continuous multiple requests.
            clearTimeout( wbte_auto_coupon_needs_refresh_timeout );
            wbte_auto_coupon_needs_refresh_timeout = setTimeout(function() { 
                
                if ( wbte_auto_coupon_needs_refresh ) {
                    // Set a loader.
                    if ( typeof window.wbte_sc_block_node === 'function' ) {
                        window.wbte_sc_block_node( jQuery('.wp-block-woocommerce-cart-order-summary-block') );
                    }

                    // Refresh the cart.
                    extensionCartUpdate( {
                        namespace: 'wbte-sc-blocks-update-cart',
                        data: {},
                    } );

                    // Remove the loader. Because sometimes below function will not work.
                    setTimeout( function() {                       
                        if ( typeof window.wbte_sc_unblock_node === 'function' ) {
                            window.wbte_sc_unblock_node( jQuery('.wp-block-woocommerce-cart-order-summary-block') );
                        }
                    }, 1500 );
                }

                // Reset the refresh required flag.
                wbte_auto_coupon_needs_refresh = false;
            }, 500 );         
        }


        // Remove the close button for auto coupons
        if ( Array.isArray( applied_auto_coupon_list ) ) {    
            jQuery('.wc-block-components-totals-discount__coupon-list-item').each( function() {
                var coupon_code = jQuery(this).find('.wc-block-components-chip__text').text().trim();
                
                // This is an auto coupon.
                if( -1 !== jQuery.inArray( coupon_code, applied_auto_coupon_list ) ){
                    jQuery(this).find('.wc-block-components-chip__remove').remove();
                }

                // Remove the loader.
                if ( typeof window.wbte_sc_unblock_node === 'function' ) {
                    window.wbte_sc_unblock_node( jQuery('.wp-block-woocommerce-cart-order-summary-block') );
                }
            });
        }

        
    }, 500);

    // Return the default value, because we are not altering any value here.
    return defaultValue;
};

registerCheckoutFilters( 'wt-sc-blocks-auto-apply-update-cart-checkout', {
    itemName: updateDataToCart,
} );