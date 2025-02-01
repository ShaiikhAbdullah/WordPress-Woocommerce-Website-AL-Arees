/*! For license information please see 834.js.LICENSE.txt */
"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[834,675],{239:(t,e,r)=>{r.d(e,{A:()=>a});var n=r(6798),o=r.n(n)()((function(t){return t[1]}));o.push([t.id,".base-modal__title-container[data-v-81aa9dfa]{align-items:center;display:flex;justify-content:flex-start;margin-bottom:4px}.base-modal__title-container--centered[data-v-81aa9dfa]{justify-content:center}.base-modal__title[data-v-81aa9dfa]{color:var(--dark);font-size:20px;font-weight:700;margin:0}.base-modal__subtitle[data-v-81aa9dfa]{color:var(--gray);font-size:14px;margin-bottom:24px;margin-top:4px}.base-modal__subtitle--centered[data-v-81aa9dfa]{text-align:center}",""]);const a=o},1481:(t,e,r)=>{r.d(e,{A:()=>a});var n=r(6798),o=r.n(n)()((function(t){return t[1]}));o.push([t.id,".setup-online-store-modal[data-v-2f88bd18]{margin-top:24px}",""]);const a=o},6675:(t,e,r)=>{r.r(e),r.d(e,{default:()=>f});var n=r(9726),o={class:"base-modal"},a={key:1,class:"base-modal__title"};const i=(0,n.pM)({__name:"BaseModal",props:{title:{},subtitle:{},titleIcon:{},titleAlignment:{default:"left"}},setup:function(t){return function(t,e){var r=(0,n.g2)("HIcon");return(0,n.uX)(),(0,n.CE)("div",o,[(0,n.Lk)("span",{class:(0,n.C4)(["base-modal__title-container",{"base-modal__title-container--centered":"centered"===t.titleAlignment}])},[t.titleIcon?((0,n.uX)(),(0,n.Wv)(r,{key:0,class:"h-mr-8",name:t.titleIcon.name,color:t.titleIcon.color},null,8,["name","color"])):(0,n.Q3)("",!0),t.title?((0,n.uX)(),(0,n.CE)("h2",a,(0,n.v_)(t.title),1)):(0,n.Q3)("",!0)],2),t.subtitle?((0,n.uX)(),(0,n.CE)("p",{key:0,class:(0,n.C4)(["base-modal__subtitle",{"base-modal__subtitle--centered":"centered"===t.titleAlignment}])},(0,n.v_)(t.subtitle),3)):(0,n.Q3)("",!0),(0,n.RG)(t.$slots,"default")])}}});var u=r(100),l=r.n(u),c=r(239),s={insert:"head",singleton:!1};l()(c.A,s);c.A.locals;const f=(0,r(6262).A)(i,[["__scopeId","data-v-81aa9dfa"]])},7834:(t,e,r)=>{r.r(e),r.d(e,{default:()=>L});var n=r(9726),o=r(6675),a=r(3679),i=r(611),u=r(6259),l=r(4464),c=r(9462),s=r(3059);function f(t){return f="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},f(t)}function h(){h=function(){return e};var t,e={},r=Object.prototype,n=r.hasOwnProperty,o=Object.defineProperty||function(t,e,r){t[e]=r.value},a="function"==typeof Symbol?Symbol:{},i=a.iterator||"@@iterator",u=a.asyncIterator||"@@asyncIterator",l=a.toStringTag||"@@toStringTag";function c(t,e,r){return Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}),t[e]}try{c({},"")}catch(t){c=function(t,e,r){return t[e]=r}}function s(t,e,r,n){var a=e&&e.prototype instanceof g?e:g,i=Object.create(a.prototype),u=new I(n||[]);return o(i,"_invoke",{value:S(t,r,u)}),i}function d(t,e,r){try{return{type:"normal",arg:t.call(e,r)}}catch(t){return{type:"throw",arg:t}}}e.wrap=s;var p="suspendedStart",v="suspendedYield",y="executing",m="completed",b={};function g(){}function _(){}function w(){}var x={};c(x,i,(function(){return this}));var O=Object.getPrototypeOf,E=O&&O(O(N([])));E&&E!==r&&n.call(E,i)&&(x=E);var j=w.prototype=g.prototype=Object.create(x);function L(t){["next","throw","return"].forEach((function(e){c(t,e,(function(t){return this._invoke(e,t)}))}))}function k(t,e){function r(o,a,i,u){var l=d(t[o],t,a);if("throw"!==l.type){var c=l.arg,s=c.value;return s&&"object"==f(s)&&n.call(s,"__await")?e.resolve(s.__await).then((function(t){r("next",t,i,u)}),(function(t){r("throw",t,i,u)})):e.resolve(s).then((function(t){c.value=t,i(c)}),(function(t){return r("throw",t,i,u)}))}u(l.arg)}var a;o(this,"_invoke",{value:function(t,n){function o(){return new e((function(e,o){r(t,n,e,o)}))}return a=a?a.then(o,o):o()}})}function S(e,r,n){var o=p;return function(a,i){if(o===y)throw Error("Generator is already running");if(o===m){if("throw"===a)throw i;return{value:t,done:!0}}for(n.method=a,n.arg=i;;){var u=n.delegate;if(u){var l=P(u,n);if(l){if(l===b)continue;return l}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if(o===p)throw o=m,n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);o=y;var c=d(e,r,n);if("normal"===c.type){if(o=n.done?m:v,c.arg===b)continue;return{value:c.arg,done:n.done}}"throw"===c.type&&(o=m,n.method="throw",n.arg=c.arg)}}}function P(e,r){var n=r.method,o=e.iterator[n];if(o===t)return r.delegate=null,"throw"===n&&e.iterator.return&&(r.method="return",r.arg=t,P(e,r),"throw"===r.method)||"return"!==n&&(r.method="throw",r.arg=new TypeError("The iterator does not provide a '"+n+"' method")),b;var a=d(o,e.iterator,r.arg);if("throw"===a.type)return r.method="throw",r.arg=a.arg,r.delegate=null,b;var i=a.arg;return i?i.done?(r[e.resultName]=i.value,r.next=e.nextLoc,"return"!==r.method&&(r.method="next",r.arg=t),r.delegate=null,b):i:(r.method="throw",r.arg=new TypeError("iterator result is not an object"),r.delegate=null,b)}function A(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function T(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function I(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(A,this),this.reset(!0)}function N(e){if(e||""===e){var r=e[i];if(r)return r.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var o=-1,a=function r(){for(;++o<e.length;)if(n.call(e,o))return r.value=e[o],r.done=!1,r;return r.value=t,r.done=!0,r};return a.next=a}}throw new TypeError(f(e)+" is not iterable")}return _.prototype=w,o(j,"constructor",{value:w,configurable:!0}),o(w,"constructor",{value:_,configurable:!0}),_.displayName=c(w,l,"GeneratorFunction"),e.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===_||"GeneratorFunction"===(e.displayName||e.name))},e.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,w):(t.__proto__=w,c(t,l,"GeneratorFunction")),t.prototype=Object.create(j),t},e.awrap=function(t){return{__await:t}},L(k.prototype),c(k.prototype,u,(function(){return this})),e.AsyncIterator=k,e.async=function(t,r,n,o,a){void 0===a&&(a=Promise);var i=new k(s(t,r,n,o),a);return e.isGeneratorFunction(r)?i:i.next().then((function(t){return t.done?t.value:i.next()}))},L(j),c(j,l,"Generator"),c(j,i,(function(){return this})),c(j,"toString",(function(){return"[object Generator]"})),e.keys=function(t){var e=Object(t),r=[];for(var n in e)r.push(n);return r.reverse(),function t(){for(;r.length;){var n=r.pop();if(n in e)return t.value=n,t.done=!1,t}return t.done=!0,t}},e.values=N,I.prototype={constructor:I,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=t,this.done=!1,this.delegate=null,this.method="next",this.arg=t,this.tryEntries.forEach(T),!e)for(var r in this)"t"===r.charAt(0)&&n.call(this,r)&&!isNaN(+r.slice(1))&&(this[r]=t)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var r=this;function o(n,o){return u.type="throw",u.arg=e,r.next=n,o&&(r.method="next",r.arg=t),!!o}for(var a=this.tryEntries.length-1;a>=0;--a){var i=this.tryEntries[a],u=i.completion;if("root"===i.tryLoc)return o("end");if(i.tryLoc<=this.prev){var l=n.call(i,"catchLoc"),c=n.call(i,"finallyLoc");if(l&&c){if(this.prev<i.catchLoc)return o(i.catchLoc,!0);if(this.prev<i.finallyLoc)return o(i.finallyLoc)}else if(l){if(this.prev<i.catchLoc)return o(i.catchLoc,!0)}else{if(!c)throw Error("try statement without catch or finally");if(this.prev<i.finallyLoc)return o(i.finallyLoc)}}}},abrupt:function(t,e){for(var r=this.tryEntries.length-1;r>=0;--r){var o=this.tryEntries[r];if(o.tryLoc<=this.prev&&n.call(o,"finallyLoc")&&this.prev<o.finallyLoc){var a=o;break}}a&&("break"===t||"continue"===t)&&a.tryLoc<=e&&e<=a.finallyLoc&&(a=null);var i=a?a.completion:{};return i.type=t,i.arg=e,a?(this.method="next",this.next=a.finallyLoc,b):this.complete(i)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),b},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.finallyLoc===t)return this.complete(r.completion,r.afterLoc),T(r),b}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.tryLoc===t){var n=r.completion;if("throw"===n.type){var o=n.arg;T(r)}return o}}throw Error("illegal catch attempt")},delegateYield:function(e,r,n){return this.delegate={iterator:N(e),resultName:r,nextLoc:n},"next"===this.method&&(this.arg=t),b}},e}function d(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function p(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?d(Object(r),!0).forEach((function(e){v(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):d(Object(r)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}function v(t,e,r){return(e=function(t){var e=function(t,e){if("object"!=f(t)||!t)return t;var r=t[Symbol.toPrimitive];if(void 0!==r){var n=r.call(t,e||"default");if("object"!=f(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"==f(e)?e:e+""}(e))in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}function y(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var n,o,a,i,u=[],l=!0,c=!1;try{if(a=(r=r.call(t)).next,0===e){if(Object(r)!==r)return;l=!1}else for(;!(l=(n=a.call(r)).done)&&(u.push(n.value),u.length!==e);l=!0);}catch(t){c=!0,o=t}finally{try{if(!l&&null!=r.return&&(i=r.return(),Object(i)!==i))return}finally{if(c)throw o}}return u}}(t,e)||function(t,e){if(t){if("string"==typeof t)return m(t,e);var r={}.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?m(t,e):void 0}}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function m(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=Array(e);r<e;r++)n[r]=t[r];return n}function b(t,e,r,n,o,a,i){try{var u=t[a](i),l=u.value}catch(t){return void r(t)}u.done?e(l):Promise.resolve(l).then(n,o)}var g={class:"setup-online-store-modal"},_={class:"d-flex justify-content-end h-mt-24"};const w=(0,n.pM)({__name:"SetupOnlineStoreModal",props:{data:{}},setup:function(t){var e=t,r=(0,a.db)().sendAmplitudeEvent,f=(0,a.hS)().closeModal,d=(0,l.NK)(),v=(0,n.KR)(!1),m=(0,n.EW)((function(){var t;return Object.keys((null===(t=d.woo)||void 0===t?void 0:t.storeCountries)||[]).map((function(t){return{label:d.woo.storeCountries[t],value:t}}))})),w=(0,n.EW)((function(){var t;return Object.values((null===(t=d.woo)||void 0===t?void 0:t.typeOfProducts)||[]).map((function(t){return{label:t.label,value:t.name}}))})),x=(0,n.EW)((function(){var t;return(null===(t=d.siteUrl)||void 0===t?void 0:t.replace(/(^\w+:|^)\/\//,""))||""})),O=(0,n.EW)((function(){return{storeName:{type:u.PU.text,label:(0,i.T)("hostinger_easy_onboarding_your_store_name"),value:x.value,validation:{required:!0}},products:{type:u.PU.select,label:(0,i.T)("hostinger_easy_onboarding_what_products_what_do_you_sell"),values:w.value,validation:{custom:function(t){return t.trim()?null:{text:(0,i.T)("hostinger_easy_onboarding_this_field_is_required")}}}},locations:{type:u.PU.select,label:(0,i.T)("hostinger_easy_onboarding_where_is_your_store"),validation:{custom:function(t){return t.trim()?null:{text:(0,i.T)("hostinger_easy_onboarding_this_field_is_required")}}},values:m.value}}})),E=function(){var t,n=(t=h().mark((function t(n){var o,a,i,u,l,m;return h().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:if(i=n.values,n.isFormValid){t.next=3;break}return t.abrupt("return");case 3:return v.value=!0,t.next=6,c.UE.postOnboardingSubmission(p(p({},i),{},{businessEmail:null===(o=d.woo)||void 0===o?void 0:o.storeEmail,isAgreeMarketing:!1}));case 6:return u=t.sent,l=y(u,2),m=l[1],t.next=11,r(s.lg.DSECRIPTION_FILLED);case 11:if(v.value=!1,!m){t.next=14;break}return t.abrupt("return");case 14:if(null===(a=e.data)||void 0===a||!a.onConfirm){t.next=17;break}return t.next=17,e.data.onConfirm();case 17:f();case 18:case"end":return t.stop()}}),t)})),function(){var e=this,r=arguments;return new Promise((function(n,o){var a=t.apply(e,r);function i(t){b(a,n,o,i,u,"next",t)}function u(t){b(a,n,o,i,u,"throw",t)}i(void 0)}))});return function(t){return n.apply(this,arguments)}}();return function(t,e){return(0,n.uX)(),(0,n.Wv)(o.default,{"title-alignment":"centered",title:(0,n.R1)(i.T)("hostinger_easy_onboarding_tell_us_about_your_business"),subtitle:(0,n.R1)(i.T)("hostinger_easy_onboarding_tell_us_about_your_business_description")},{default:(0,n.k6)((function(){return[(0,n.Lk)("div",g,[(0,n.bF)((0,n.R1)(u.vZ),{onOnSubmit:e[0]||(e[0]=function(t){return E(t)})},{default:(0,n.k6)((function(){return[(0,n.bF)((0,n.R1)(u.TW),{class:"h-mb-16",schema:O.value.storeName,name:"storeName"},null,8,["schema"]),(0,n.bF)((0,n.R1)(u.TW),{class:"h-mb-16",schema:O.value.products,name:"industry"},null,8,["schema"]),(0,n.bF)((0,n.R1)(u.TW),{class:"h-mb-16",schema:O.value.locations,name:"storeLocation"},null,8,["schema"]),(0,n.Lk)("div",_,[(0,n.bF)((0,n.R1)(u.BN),{onClick:(0,n.R1)(f),class:"h-mr-16","is-disabled":v.value,size:"small",variant:"text"},{default:(0,n.k6)((function(){return[(0,n.eW)((0,n.v_)((0,n.R1)(i.T)("hostinger_easy_onboarding_cancel")),1)]})),_:1},8,["onClick","is-disabled"]),(0,n.bF)((0,n.R1)(u.BN),{class:"setup-online-store","is-loading":v.value,"h-form-submit":"",size:"small"},{default:(0,n.k6)((function(){return[(0,n.eW)((0,n.v_)((0,n.R1)(i.T)("hostinger_easy_onboarding_continue")),1)]})),_:1},8,["is-loading"])])]})),_:1})])]})),_:1},8,["title","subtitle"])}}});var x=r(100),O=r.n(x),E=r(1481),j={insert:"head",singleton:!1};O()(E.A,j);E.A.locals;const L=(0,r(6262).A)(w,[["__scopeId","data-v-2f88bd18"]])}}]);