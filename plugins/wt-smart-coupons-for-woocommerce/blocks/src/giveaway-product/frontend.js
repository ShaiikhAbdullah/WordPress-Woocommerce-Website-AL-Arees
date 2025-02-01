import metadata from './block.json';
import {Disabled} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
const { registerCheckoutFilters,registerCheckoutBlock,extensionCartUpdate } = window.wc.blocksCheckout;

// Declare some global variables.
var wbte_giveaway_eligible_message_timeout = null;
var wbte_giveaway_products_timeout = null;
var wbte_cart_obj = null;
var wbte_isFirefox = typeof InstallTrigger !== 'undefined';


/**
 *  Register checkout filter to alter cart and show notices
 */
const updateDataToCart = ( defaultValue, extensions, args ) => {

    wbte_cart_obj = args?.cart;

    // Add giveaway item text to cart item.
    const cartitem_giveaway_text = args?.cart?.extensions?.wt_sc_blocks?.cartitem_giveaway_text;
    const cart_item_key = args?.cartItem?.key;
    if (cart_item_key && cartitem_giveaway_text && cartitem_giveaway_text[cart_item_key] ) {
        args.cartItem.short_description = cartitem_giveaway_text[cart_item_key];
        args.cartItem.quantity_limits.maximum = args.cartItem.quantity;
        args.cartItem.quantity_limits.minimum = args.cartItem.quantity;
    }

    //Show giveaway available message.
    const { createInfoNotice, removeNotice, removeAllNotices } = useDispatch( 'core/notices' );
    const context = 'wc/cart';
    const msg_id = 'wbte-giveaway-eligible-msg';
    const giveaway_eligible_message = args?.cart?.extensions?.wt_sc_blocks?.giveaway_eligible_message;
    clearTimeout(wbte_giveaway_eligible_message_timeout);
    wbte_giveaway_eligible_message_timeout = setTimeout(function(){
        if(giveaway_eligible_message){
            createInfoNotice( giveaway_eligible_message, { id: msg_id, type: 'default', isDismissible: false , context } );
        }else{
            removeNotice( msg_id, context );
        }      
    }, 10);

    return defaultValue;
};

registerCheckoutFilters( 'wt-sc-blocks-update-cart', {
    itemName: updateDataToCart,
} );


const modifyCartItemClass = ( defaultValue, extensions, args ) => {
    
    // Add custom CSS class to giveaway cart item.
    const cartitem_giveaway_text = args?.cart?.extensions?.wt_sc_blocks?.cartitem_giveaway_text;
    const cart_item_key = args?.cartItem?.key;
    if (cart_item_key && cartitem_giveaway_text && cartitem_giveaway_text[cart_item_key] ) {
        return 'wbte-giveaway-cart-item';
    }

    return defaultValue;
};

registerCheckoutFilters( 'wt-sc-blocks-modify-cart-item-class', {
    cartItemClass: modifyCartItemClass,
} );


document.addEventListener('wbte_sc_checkout_value_updated', function(e){ 
    extensionCartUpdate( {
        namespace: 'wbte-sc-blocks-update-checkout',
        data: {},
    } );
});

// Webkit browsers (other than Firefox) requires an extra refresh to show the giveaway products
if ( ! wbte_isFirefox && "1" === WTSmartCouponOBJ.is_cart ) {
    
    setTimeout(function(){      
        if ( wbte_cart_obj ) {

            let html = wbte_cart_obj?.extensions?.wt_sc_blocks?.giveaway_products_html;
            let temp_elm = document.createElement("div");
            temp_elm.innerHTML = html;
            let text = temp_elm.textContent || temp_elm.innerText || "";

            // Only do the refresh when giveaway product HTML exists.
            if ( text.trim() ) { 
                extensionCartUpdate( {
                    namespace: 'wbte-sc-blocks-update-checkout',
                    data: {},
                } );
            }
        }
    }, 100);
}

/** 
 *  Giveaway products block
 */
const Block = ({ children, checkoutExtensionData, cart }) => {
    
    const [productsHtml, setProductsHtml] = useState('');
    
    if ( wbte_cart_obj ) {
        clearTimeout(wbte_giveaway_products_timeout);
        wbte_giveaway_products_timeout = setTimeout(function(){
            const giveaway_products_html = wbte_cart_obj?.extensions?.wt_sc_blocks?.giveaway_products_html;
            setProductsHtml(giveaway_products_html);
        }, 10);      
    }
 
    return (
        <div
          dangerouslySetInnerHTML={{__html: productsHtml}}
        />
      );
}

registerCheckoutBlock( {
    metadata,
    component: Block
} );
    