import { __ } from '@wordpress/i18n';
import {
	AccordionSettingsBlock,
	SelectSettingsBlock,
} from '../../../../ReusableComponents/SettingsBlocks';

const creditCardExamples = [
	{ value: '', label: __( 'Select', 'woocommerce-paypal-payments' ) },
	{
		value: 'mastercard',
		label: __( 'Mastercard', 'woocommerce-paypal-payments' ),
	},
	{ value: 'visa', label: __( 'Visa', 'woocommerce-paypal-payments' ) },
	{
		value: 'amex',
		label: __( 'American Express', 'woocommerce-paypal-payments' ),
	},
	{ value: 'jcb', label: __( 'JCB', 'woocommerce-paypal-payments' ) },
	{
		value: 'diners-club',
		label: __( 'Diners Club', 'woocommerce-paypal-payments' ),
	},
];

const OtherSettings = ( { settings, updateFormValue } ) => {
	return (
		<AccordionSettingsBlock
			title={ __(
				'Other payment method settings',
				'woocommerce-paypal-payments'
			) }
			description={ __(
				'Modify the checkout experience for alternative payment methods, credit cards, and digital wallets.',
				'woocommerce-paypal-payments'
			) }
		>
			<SelectSettingsBlock
				title={ __(
					'Disable specific credit cards',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					"If left blank, PayPal and other buttons will present in the user's detected language. Enter a language here to force all buttons to display in that language.",
					'woocommerce-paypal-payments'
				) }
				actionProps={ {
					options: creditCardExamples,
					value: settings.buttonLanguage,
					callback: updateFormValue,
					key: 'buttonLanguage',
					isMulti: true,
				} }
				order={ [ 'title', 'description', 'action' ] }
			/>
		</AccordionSettingsBlock>
	);
};

export default OtherSettings;
