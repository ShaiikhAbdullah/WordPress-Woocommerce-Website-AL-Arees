import { __ } from '@wordpress/i18n';
import {
	InputSettingsBlock,
	ToggleSettingsBlock,
} from '../../../ReusableComponents/SettingsBlocks';
import SettingsCard from '../../../ReusableComponents/SettingsCard';
import OrderIntent from './Blocks/OrderIntent';
import SavePaymentMethods from './Blocks/SavePaymentMethods';

const CommonSettings = ( { updateFormValue, settings } ) => {
	return (
		<SettingsCard
			icon="icon-settings-common.svg"
			title={ __( 'Common settings', 'woocommerce-paypal-payments' ) }
			className="ppcp-r-settings-card ppcp-r-settings-card--common-settings"
			description={ __(
				'Customize key features to tailor your PayPal experience.',
				'woocommerce-paypal-payments'
			) }
		>
			<InputSettingsBlock
				title="Invoice Prefix"
				supplementaryLabel={ __(
					'(Recommended)',
					'woocommerce-paypal-payments'
				) }
				description="Add a unique prefix to invoice numbers for site-specific tracking (recommended)."
				actionProps={ {
					callback: updateFormValue,
					key: 'invoicePrefix',
					value: settings.invoicePrefix,
					placeholder: __(
						'Input prefix',
						'woocommerce-paypal-payments'
					),
				} }
			/>

			<OrderIntent
				settings={ settings }
				updateFormValue={ updateFormValue }
			/>

			<SavePaymentMethods
				updateFormValue={ updateFormValue }
				settings={ settings }
			/>

			<ToggleSettingsBlock
				title={ __(
					'Pay Now Experience',
					'woocommerce-paypal-payments'
				) }
				description={ __(
					'Let PayPal customers skip the Order Review page by selecting shipping options directly within PayPal.',
					'woocommerce-paypal-payments'
				) }
				actionProps={ {
					callback: updateFormValue,
					key: 'payNowExperience',
					value: settings.payNowExperience,
				} }
			/>
		</SettingsCard>
	);
};

export default CommonSettings;
