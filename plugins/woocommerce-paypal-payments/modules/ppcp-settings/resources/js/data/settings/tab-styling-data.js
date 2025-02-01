import { __ } from '@wordpress/i18n';

const cartAndExpressCheckoutSettings = {
	paymentMethods: [],
	style: {
		shape: 'pill',
		label: 'paypal',
		color: 'gold',
	},
};

const settings = {
	paymentMethods: [],
	style: {
		layout: 'vertical',
		shape: cartAndExpressCheckoutSettings.style.shape,
		label: cartAndExpressCheckoutSettings.style.label,
		color: cartAndExpressCheckoutSettings.style.color,
		tagline: false,
	},
};

export const defaultLocationSettings = {
	cart: {
		value: 'cart',
		label: __( 'Cart', 'woocommerce-paypal-payments' ),
		settings: { ...cartAndExpressCheckoutSettings },
		// translators: %s: Link to Cart page
		description: __(
			'Customize the appearance of the PayPal smart buttons on the <a href="%s">[MISSING LINK]Cart page</a> and select which additional payment buttons to display in this location.',
			'wooocommerce-paypal-payments'
		),
		descriptionLink: '#',
	},
	'classic-checkout': {
		value: 'classic-checkout',
		label: __( 'Classic Checkout', 'woocommerce-paypal-payments' ),
		settings: { ...settings },
		// translators: %s: Link to Classic Checkout page
		description: __(
			'Customize the appearance of the PayPal smart buttons on the <a href="%s">[MISSING LINK]Classic Checkout page</a> and choose which additional payment buttons to display in this location.',
			'wooocommerce-paypal-payments'
		),
		descriptionLink: '#',
	},
	'express-checkout': {
		value: 'express-checkout',
		label: __( 'Express Checkout', 'woocommerce-paypal-payments' ),
		settings: { ...cartAndExpressCheckoutSettings },
		// translators: %s: Link to Express Checkout location
		description: __(
			'Customize the appearance of the PayPal smart buttons on the <a href="%s">[MISSING LINK]Express Checkout location</a> and choose which additional payment buttons to display in this location.',
			'wooocommerce-paypal-payments'
		),
		descriptionLink: '#',
	},
	'mini-cart': {
		value: 'mini-cart',
		label: __( 'Mini Cart', 'woocommerce-paypel-payements' ),
		settings: { ...settings },
		// translators: %s: Link to Mini Cart
		description: __(
			'Customize the appearance of the PayPal smart buttons on the <a href="%s">[MISSING LINK]Mini Cart</a> and choose which additional payment buttons to display in this location.',
			'wooocommerce-paypal-payments'
		),
		descriptionLink: '#',
	},
	'product-page': {
		value: 'product-page',
		label: __( 'Product Page', 'woocommerce-paypal-payments' ),
		settings: { ...settings },
		// translators: %s: Link to Product Page
		description: __(
			'Customize the appearance of the PayPal smart buttons on the <a href="%s">[MISSING LINK]Product Page</a> and choose which additional payment buttons to display in this location.',
			'wooocommerce-paypal-payments'
		),
		descriptionLink: '#',
	},
};

export const paymentMethodOptions = [
	{
		value: 'venmo',
		label: __( 'Venmo', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'paylater',
		label: __( 'Pay Later', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'googlepay',
		label: __( 'Google Pay', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'applepay',
		label: __( 'Apple Pay', 'woocommerce-paypal-payments' ),
	},
];

export const buttonLabelOptions = [
	{
		value: 'paypal',
		label: __( 'PayPal', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'checkout',
		label: __( 'Checkout', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'buynow',
		label: __( 'PayPal Buy Now', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'pay',
		label: __( 'Pay with PayPal', 'woocommerce-paypal-payments' ),
	},
];

export const colorOptions = [
	{
		value: 'gold',
		label: __( 'Gold (Recommended)', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'blue',
		label: __( 'Blue', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'silver',
		label: __( 'Silver', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'black',
		label: __( 'Black', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'white',
		label: __( 'White', 'woocommerce-paypal-payments' ),
	},
];

export const buttonLayoutOptions = [
	{
		label: __( 'Vertical', 'woocommerce-paypal-payments' ),
		value: 'vertical',
	},
	{
		label: __( 'Horizontal', 'woocommerce-paypal-payments' ),
		value: 'horizontal',
	},
];

export const shapeOptions = [
	{
		value: 'pill',
		label: __( 'Pill', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'rect',
		label: __( 'Rectangle', 'woocommerce-paypal-payments' ),
	},
];
