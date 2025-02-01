/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "./src/giveaway-product/block.json":
/*!*****************************************!*\
  !*** ./src/giveaway-product/block.json ***!
  \*****************************************/
/***/ ((module) => {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"wt-sc-blocks/giveaway-product","version":"1.0.0","title":"Giveaway product","category":"woocommerce","keywords":["Giveaway","WooCommerce"],"icon":"","description":"Giveaway coupon related operations.","parent":["woocommerce/cart-items-block"],"attributes":{"lock":{"type":"object","default":{"remove":true,"move":true}}},"textdomain":"wt-smart-coupons-for-woocommerce"}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!******************************************!*\
  !*** ./src/giveaway-product/frontend.js ***!
  \******************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./block.json */ "./src/giveaway-product/block.json");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_4__);





const {
  registerCheckoutFilters,
  registerCheckoutBlock,
  extensionCartUpdate
} = window.wc.blocksCheckout;

// Declare some global variables.
var wbte_giveaway_eligible_message_timeout = null;
var wbte_giveaway_products_timeout = null;
var wbte_cart_obj = null;
var wbte_isFirefox = typeof InstallTrigger !== 'undefined';

/**
 *  Register checkout filter to alter cart and show notices
 */
const updateDataToCart = (defaultValue, extensions, args) => {
  wbte_cart_obj = args?.cart;

  // Add giveaway item text to cart item.
  const cartitem_giveaway_text = args?.cart?.extensions?.wt_sc_blocks?.cartitem_giveaway_text;
  const cart_item_key = args?.cartItem?.key;
  if (cart_item_key && cartitem_giveaway_text && cartitem_giveaway_text[cart_item_key]) {
    args.cartItem.short_description = cartitem_giveaway_text[cart_item_key];
    args.cartItem.quantity_limits.maximum = args.cartItem.quantity;
    args.cartItem.quantity_limits.minimum = args.cartItem.quantity;
  }

  //Show giveaway available message.
  const {
    createInfoNotice,
    removeNotice,
    removeAllNotices
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_4__.useDispatch)('core/notices');
  const context = 'wc/cart';
  const msg_id = 'wbte-giveaway-eligible-msg';
  const giveaway_eligible_message = args?.cart?.extensions?.wt_sc_blocks?.giveaway_eligible_message;
  clearTimeout(wbte_giveaway_eligible_message_timeout);
  wbte_giveaway_eligible_message_timeout = setTimeout(function () {
    if (giveaway_eligible_message) {
      createInfoNotice(giveaway_eligible_message, {
        id: msg_id,
        type: 'default',
        isDismissible: false,
        context
      });
    } else {
      removeNotice(msg_id, context);
    }
  }, 10);
  return defaultValue;
};
registerCheckoutFilters('wt-sc-blocks-update-cart', {
  itemName: updateDataToCart
});
const modifyCartItemClass = (defaultValue, extensions, args) => {
  // Add custom CSS class to giveaway cart item.
  const cartitem_giveaway_text = args?.cart?.extensions?.wt_sc_blocks?.cartitem_giveaway_text;
  const cart_item_key = args?.cartItem?.key;
  if (cart_item_key && cartitem_giveaway_text && cartitem_giveaway_text[cart_item_key]) {
    return 'wbte-giveaway-cart-item';
  }
  return defaultValue;
};
registerCheckoutFilters('wt-sc-blocks-modify-cart-item-class', {
  cartItemClass: modifyCartItemClass
});
document.addEventListener('wbte_sc_checkout_value_updated', function (e) {
  extensionCartUpdate({
    namespace: 'wbte-sc-blocks-update-checkout',
    data: {}
  });
});

// Webkit browsers (other than Firefox) requires an extra refresh to show the giveaway products
if (!wbte_isFirefox && "1" === WTSmartCouponOBJ.is_cart) {
  setTimeout(function () {
    if (wbte_cart_obj) {
      let html = wbte_cart_obj?.extensions?.wt_sc_blocks?.giveaway_products_html;
      let temp_elm = document.createElement("div");
      temp_elm.innerHTML = html;
      let text = temp_elm.textContent || temp_elm.innerText || "";

      // Only do the refresh when giveaway product HTML exists.
      if (text.trim()) {
        extensionCartUpdate({
          namespace: 'wbte-sc-blocks-update-checkout',
          data: {}
        });
      }
    }
  }, 100);
}

/** 
 *  Giveaway products block
 */
const Block = ({
  children,
  checkoutExtensionData,
  cart
}) => {
  const [productsHtml, setProductsHtml] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_3__.useState)('');
  if (wbte_cart_obj) {
    clearTimeout(wbte_giveaway_products_timeout);
    wbte_giveaway_products_timeout = setTimeout(function () {
      const giveaway_products_html = wbte_cart_obj?.extensions?.wt_sc_blocks?.giveaway_products_html;
      setProductsHtml(giveaway_products_html);
    }, 10);
  }
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    dangerouslySetInnerHTML: {
      __html: productsHtml
    }
  });
};
registerCheckoutBlock({
  metadata: _block_json__WEBPACK_IMPORTED_MODULE_1__,
  component: Block
});
})();

/******/ })()
;
//# sourceMappingURL=frontend.js.map