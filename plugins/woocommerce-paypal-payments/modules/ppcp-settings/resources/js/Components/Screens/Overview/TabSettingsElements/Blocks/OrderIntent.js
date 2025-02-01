import { __ } from '@wordpress/i18n';
import {
	Header,
	SettingsBlock,
	Title,
	Description,
	ToggleSettingsBlock,
} from '../../../../ReusableComponents/SettingsBlocks';

const OrderIntent = ( { updateFormValue, settings } ) => {
	return (
		<SettingsBlock>
			<Header>
				<Title>
					{ __( 'Order Intent', 'woocommerce-paypal-payments' ) }
				</Title>
				<Description>
					{ __(
						'Choose between immediate capture or authorization-only, with manual capture in the Order section.',
						'woocommerce-paypal-payments'
					) }
				</Description>
			</Header>

			<ToggleSettingsBlock
				title={ __( 'Authorize Only', 'woocommerce-paypal-payments' ) }
				actionProps={ {
					callback: updateFormValue,
					key: 'authorizeOnly',
					value: settings.authorizeOnly,
				} }
			/>

			<ToggleSettingsBlock
				title={ __(
					'Capture Virtual-Only Orders',
					'woocommerce-paypal-payments'
				) }
				actionProps={ {
					callback: updateFormValue,
					key: 'captureVirtualOnlyOrders',
					value: settings.captureVirtualOnlyOrders,
				} }
			/>
		</SettingsBlock>
	);
};

export default OrderIntent;
