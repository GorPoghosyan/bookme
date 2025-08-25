/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};

;// CONCATENATED MODULE: external ["wc","wcBlocksRegistry"]
const external_wc_wcBlocksRegistry_namespaceObject = window["wc"]["wcBlocksRegistry"];
;// CONCATENATED MODULE: external ["wc","wcSettings"]
const external_wc_wcSettings_namespaceObject = window["wc"]["wcSettings"];
;// CONCATENATED MODULE: external ["wp","data"]
const external_wp_data_namespaceObject = window["wp"]["data"];
;// CONCATENATED MODULE: external ["wp","hooks"]
const external_wp_hooks_namespaceObject = window["wp"]["hooks"];
;// CONCATENATED MODULE: external "React"
const external_React_namespaceObject = window["React"];
;// CONCATENATED MODULE: external ["wp","htmlEntities"]
const external_wp_htmlEntities_namespaceObject = window["wp"]["htmlEntities"];
;// CONCATENATED MODULE: ./src/payment-method/check-availability/index.js

/**
 * External dependencies
 */



/**
 * Internal dependencies
 */
const PAYMENT_METHOD_NAME = 'wc-bookings-gateway';
const settings = (0,external_wc_wcSettings_namespaceObject.getSetting)('wc-bookings-gateway_data', {});
const label = (0,external_wp_htmlEntities_namespaceObject.decodeEntities)(settings.title);
const orderButtonText = (0,external_wp_htmlEntities_namespaceObject.decodeEntities)(settings.order_button_text);

/**
 * Content component
 */
const Content = () => {
  return (0,external_wp_htmlEntities_namespaceObject.decodeEntities)(settings.description || '');
};

/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = props => {
  const {
    PaymentMethodLabel
  } = props.components;
  return (0,external_React_namespaceObject.createElement)(PaymentMethodLabel, {
    text: label
  });
};

/**
 * Bookings payment method config object.
 */
const bookingsPaymentMethod = {
  name: PAYMENT_METHOD_NAME,
  content: (0,external_React_namespaceObject.createElement)(Content, null),
  label: (0,external_React_namespaceObject.createElement)(Label, null),
  edit: (0,external_React_namespaceObject.createElement)(Content, null),
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports
  },
  placeOrderButtonLabel: orderButtonText
};
/* harmony default export */ const check_availability = (bookingsPaymentMethod);
;// CONCATENATED MODULE: ./src/payment-method/index.js
/**
 * External dependencies
 */





/**
 * Internal dependencies
 */

const payment_method_settings = (0,external_wc_wcSettings_namespaceObject.getSetting)('wc-bookings-gateway_data', {});
const {
  PAYMENT_STORE_KEY
} = wc.wcBlocksData;
(0,external_wc_wcBlocksRegistry_namespaceObject.registerPaymentMethod)(check_availability);
if (payment_method_settings.is_enabled) {
  // Set the payment method as active when the checkout form is rendered.
  (0,external_wp_hooks_namespaceObject.addAction)('experimental__woocommerce_blocks-checkout-render-checkout-form', 'woocommerce-bookings-gateway', () => (0,external_wp_data_namespaceObject.dispatch)(PAYMENT_STORE_KEY).__internalSetActivePaymentMethod('wc-bookings-gateway'));
}
/******/ })()
;
//# sourceMappingURL=blocks.js.map