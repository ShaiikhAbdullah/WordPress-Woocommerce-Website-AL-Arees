import { __ } from '@wordpress/i18n';
import {
	AccordionSettingsBlock,
	RadioSettingsBlock,
	ToggleSettingsBlock,
	InputSettingsBlock,
	SelectSettingsBlock,
} from '../../../../ReusableComponents/SettingsBlocks';

const PaypalSettings = ( { updateFormValue, settings } ) => {
	return (
		<AccordionSettingsBlock
			className="ppcp-r-settings-block--settings"
			title={ __( 'PayPal Settings', 'woocommerce-paypal-payments' ) }
			description={ __(
				'Modify the PayPal checkout experience.',
				'woocommerce-paypal-payments'
			) }
			actionProps={ {
				callback: updateFormValue,
				key: 'payNowExperience',
				value: settings.payNowExperience,
			} }
		>
			<RadioSettingsBlock
				title={ __(
					'Subtotal mismatch fallback',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Due to differences in how WooCommerce and PayPal calculates taxes, some transactions may fail due to a rounding error. This settings determines the fallback behavior.',
					'woocommerce-paypal-payments'
				) }
				options={ [
					{
						id: 'add_a_correction',
						value: 'add_a_correction',
						label: __(
							'Add a correction',
							'woocommerce-paypal-payments'
						),
						description: __(
							'Adds an additional line item with the missing amount.',
							'woocommerce-paypal-payments'
						),
					},
					{
						id: 'do_not_send_line_items',
						value: 'do_not_send_line_items',
						label: __(
							'Do not send line items',
							'woocommerce-paypal-payments'
						),
						description: __(
							'Resubmit the transaction without line item details.',
							'woocommerce-paypal-payments'
						),
					},
				] }
				actionProps={ {
					name: 'paypal_settings_mismatch',
					key: 'subtotalMismatchFallback',
					currentValue: settings.subtotalMismatchFallback,
					callback: updateFormValue,
				} }
			/>

			<ToggleSettingsBlock
				title={ __(
					'Instant payments only',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'If enabled, PayPal will not allow buyers to use funding sources that take additional time to complete, such as eChecks.',
					'woocommerce-paypal-payments'
				) }
				actionProps={ {
					value: settings.savePaypalAndVenmo,
					callback: updateFormValue,
					key: 'savePaypalAndVenmo',
				} }
			/>

			<InputSettingsBlock
				title={ __( 'Brand name', 'woocommerce-paypal-payments' ) }
				description={ __(
					'What business name to show to your buyers during checkout and on receipts.',
					'woocommerce-paypal-payments'
				) }
				actionProps={ {
					value: settings.brandName,
					callback: updateFormValue,
					key: 'brandName',
					placeholder: __(
						'Brand name',
						'woocommerce-paypal-payments'
					),
				} }
				order={ [ 'title', 'description', 'action' ] }
			/>

			<InputSettingsBlock
				title={ __( 'Soft Descriptor', 'woocommerce-paypal-payments' ) }
				description={ __(
					"The dynamic text used to construct the statement descriptor that appears on a payer's card statement. Applies to PayPal and Credit Card transactions. Max value of 22 characters.",
					'woocommerce-paypal-payments'
				) }
				actionProps={ {
					value: settings.softDescriptor,
					callback: updateFormValue,
					key: 'softDescriptor',
					placeholder: __(
						'Soft Descriptor',
						'woocommerce-paypal-payments'
					),
				} }
				order={ [ 'title', 'description', 'action' ] }
			/>

			<RadioSettingsBlock
				title={ __(
					'PayPal landing page',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Determine which experience a buyer sees when they click the PayPal button.',
					'woocommerce-paypal-payments'
				) }
				options={ [
					{
						id: 'no_preference',
						value: 'no_reference',
						label: __(
							'No preference',
							'woocommerce-paypal-payments'
						),
						description: __(
							'Shows the buyer the PayPal login for a recognized PayPal buyer.',
							'woocommerce-paypal-payments'
						),
					},
					{
						id: 'login_page',
						value: 'login_page',
						label: __(
							'Login page',
							'woocommerce-paypal-payments'
						),
						description: __(
							'Always show the buyer the PayPal login screen.',
							'woocommerce-paypal-payments'
						),
					},
					{
						id: 'guest_checkout_page',
						value: 'guest_checkout_page',
						label: __(
							'Guest checkout page',
							'woocommerce-paypal-payments'
						),
						description: __(
							'Always show the buyer the guest checkout fields first.',
							'woocommerce-paypal-payments'
						),
					},
				] }
				actionProps={ {
					name: 'paypal_settings_landing',
					key: 'paypalLandingPage',
					currentValue: settings.paypalLandingPage,
					callback: updateFormValue,
				} }
			/>

			<SelectSettingsBlock
				title={ __( 'Button Language', 'woocommerce-paypal-payments' ) }
				description={ __(
					"If left blank, PayPal and other buttons will present in the user's detected language. Enter a language here to force all buttons to display in that language.",
					'woocommerce-paypal-payments'
				) }
				actionProps={ {
					value: settings.buttonLanguage,
					callback: updateFormValue,
					options: languagesExample,
					key: 'buttonLanguage',
					placeholder: __(
						'Browser language',
						'woocommerce-paypal-payments'
					),
				} }
				order={ [ 'title', 'description', 'action' ] }
			/>
		</AccordionSettingsBlock>
	);
};

const languagesExample = [
	{ value: 'en', label: 'English' },
	{ value: 'de', label: 'German' },
	{ value: 'es', label: 'Spanish' },
	{ value: 'it', label: 'Italian' },
];

export default PaypalSettings;
