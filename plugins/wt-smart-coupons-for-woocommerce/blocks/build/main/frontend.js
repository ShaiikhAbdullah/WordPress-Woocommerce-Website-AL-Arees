/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@woocommerce/blocks-checkout":
/*!****************************************!*\
  !*** external ["wc","blocksCheckout"] ***!
  \****************************************/
/***/ ((module) => {

module.exports = window["wc"]["blocksCheckout"];

/***/ }),

/***/ "@woocommerce/block-data":
/*!**************************************!*\
  !*** external ["wc","wcBlocksData"] ***!
  \**************************************/
/***/ ((module) => {

module.exports = window["wc"]["wcBlocksData"];

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

/***/ "@wordpress/html-entities":
/*!**************************************!*\
  !*** external ["wp","htmlEntities"] ***!
  \**************************************/
/***/ ((module) => {

module.exports = window["wp"]["htmlEntities"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./src/main/block.json":
/*!*****************************!*\
  !*** ./src/main/block.json ***!
  \*****************************/
/***/ ((module) => {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"wt-sc-blocks/main","version":"1.0.0","title":"Smart coupon blocks","category":"woocommerce","parent":["woocommerce/checkout-fields-block","woocommerce/cart-items-block"],"attributes":{"lock":{"type":"object","default":{"remove":true,"move":true}}},"textdomain":"wt-smart-coupons-for-woocommerce"}');

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
/*!******************************!*\
  !*** ./src/main/frontend.js ***!
  \******************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   WtScBlocksMain: () => (/* binding */ WtScBlocksMain)
/* harmony export */ });
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./block.json */ "./src/main/block.json");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _woocommerce_block_data__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @woocommerce/block-data */ "@woocommerce/block-data");
/* harmony import */ var _woocommerce_block_data__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_block_data__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @woocommerce/blocks-checkout */ "@woocommerce/blocks-checkout");
/* harmony import */ var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_6__);








// Global import
const {
  registerCheckoutBlock
} = wc.blocksCheckout;
const WtScBlocksMain = () => {
  const [couponCode, setCouponCode] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_5__.useState)('');
  const [isClickBinded, setIsClickBinded] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_5__.useState)(false);
  const {
    applyCoupon,
    removeCoupon
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useDispatch)(_woocommerce_block_data__WEBPACK_IMPORTED_MODULE_4__.CART_STORE_KEY);
  const {
    createErrorNotice,
    createSuccessNotice
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_1__.useDispatch)('core/notices');
  if (!isClickBinded) {
    /* Set `true` for preventing multiple event binding */
    setIsClickBinded(true);

    /* Click event triggered by plugin public JS file */
    document.addEventListener('wt_sc_api_coupon_clicked', function (e) {
      const couponCode = e.detail.coupon_code;
      const couponId = e.detail.coupon_id;
      const context = 'wc/cart'; /* message context */

      applyCoupon(couponCode).then(response => {
        /* Trigger a custom event */
        const coupon_click_done_event = new CustomEvent("wt_sc_api_coupon_click_done", {
          detail: {
            'coupon_code': couponCode,
            'coupon_id': couponId,
            'status': true
          }
        });
        document.dispatchEvent(coupon_click_done_event);

        /* Show success message */
        if ((0,_woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_6__.applyCheckoutFilter)({
          filterName: 'showApplyCouponNotice',
          defaultValue: true,
          arg: {
            couponCode,
            context
          }
        })) {
          createSuccessNotice((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.sprintf)( /* translators: %s coupon code. */
          (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Coupon code "%s" has been applied to your cart.', 'wt-smart-coupons-for-woocommerce'), couponCode), {
            id: 'coupon-form',
            type: 'snackbar',
            context
          });
        }
      }).catch(error => {
        /* Trigger a custom event */
        const coupon_click_done_event = new CustomEvent("wt_sc_api_coupon_click_done", {
          detail: {
            'coupon_code': couponCode,
            'coupon_id': couponId,
            'status': false,
            'message': error.message
          }
        });
        document.dispatchEvent(coupon_click_done_event);

        /* Show error message */
        createErrorNotice(error.message, {
          id: 'coupon-form',
          type: 'snackbar',
          context
        });
      });
    });
  }
  return '';
};
const options = {
  metadata: _block_json__WEBPACK_IMPORTED_MODULE_0__,
  component: WtScBlocksMain
};
registerCheckoutBlock(options);
})();

/******/ })()
;
//# sourceMappingURL=frontend.js.map