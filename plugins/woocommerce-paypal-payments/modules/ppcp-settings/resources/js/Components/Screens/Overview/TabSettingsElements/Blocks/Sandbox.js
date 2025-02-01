import { __, sprintf } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import {
	AccordionSettingsBlock,
	ButtonSettingsBlock,
	RadioSettingsBlock,
	ToggleSettingsBlock,
	InputSettingsBlock,
} from '../../../../ReusableComponents/SettingsBlocks';
import TitleBadge, {
	TITLE_BADGE_POSITIVE,
} from '../../../../ReusableComponents/TitleBadge';
import ConnectionInfo, {
	connectionStatusDataDefault,
} from '../../../../ReusableComponents/ConnectionInfo';

const Sandbox = ( { settings, updateFormValue } ) => {
	const className = settings.sandboxConnected
		? 'ppcp-r-settings-block--sandbox-connected'
		: 'ppcp-r-settings-block--sandbox-disconnected';

	return (
		<AccordionSettingsBlock
			title={ __( 'Sandbox', 'woocommerce-paypal-payments' ) }
			className={ className }
			description={ __(
				"Test your site in PayPal's Sandbox environment.",
				'woocommerce-paypal-payments'
			) }
			actionProps={ {
				callback: updateFormValue,
				key: 'payNowExperience',
				value: settings.payNowExperience,
			} }
		>
			{ settings.sandboxConnected && (
				<ButtonSettingsBlock
					title={ __(
						'Sandbox account credentials',
						'woocommerce-paypal-payments'
					) }
					description={ __(
						'Your account is connected to sandbox, no real charging takes place. To accept live payments, turn off sandbox mode and connect your live PayPal account.',
						'woocommerce-paypal-payments'
					) }
					tag={
						<TitleBadge
							type={ TITLE_BADGE_POSITIVE }
							text={ __(
								'Connected',
								'woocommerce-paypal-payments'
							) }
						/>
					}
				>
					<div className="ppcp-r-settings-block--sandbox">
						<ToggleSettingsBlock
							title={ __(
								'Enable sandbox mode',
								'woocommerce-paypal-payments'
							) }
							actionProps={ {
								callback: updateFormValue,
								key: 'sandboxEnabled',
								value: settings.sandboxEnabled,
							} }
						/>
						<ConnectionInfo
							connectionStatusDataDefault={
								connectionStatusDataDefault
							}
						/>
						<Button
							variant="secondary"
							onClick={ () =>
								updateFormValue( 'sandboxConnected', false )
							}
						>
							{ __(
								'Disconnect Sandbox',
								'woocommerce-paypal-payments'
							) }
						</Button>
					</div>
				</ButtonSettingsBlock>
			) }
			{ ! settings.sandboxConnected && (
				<RadioSettingsBlock
					title={ __(
						'Connect Sandbox Account',
						'woocommerce-paypal-payments'
					) }
					description={ __(
						'Connect a PayPal Sandbox account in order to test your website. Transactions made will not result in actual money movement. Do not fulfil orders completed in Sandbox mode.',
						'woocommerce-paypal-payments'
					) }
					options={ [
						{
							id: 'sandbox_mode',
							value: 'sandbox_mode',
							label: __(
								'Sandbox Mode',
								'woocommerce-paypal-payments'
							),
							description: __(
								'Activate Sandbox mode to safely test PayPal with sample data. Once your store is ready to go live, you can easily switch to your production account.',
								'woocommerce-paypal-payments'
							),
							additionalContent: (
								<Button
									variant="primary"
									onClick={ () =>
										updateFormValue(
											'sandboxConnected',
											true
										)
									}
								>
									{ __(
										'Connect Sandbox Account',
										'woocommerce-paypal-payments'
									) }
								</Button>
							),
						},
						{
							id: 'manual_connect',
							value: 'manual_connect',
							label: __(
								'Manual Connect',
								'woocommerce-paypal-payments'
							),
							description: sprintf(
								__(
									'For advanced users: Connect a custom PayPal REST app for full control over your integration. For more information on creating a PayPal REST application, <a target="_blank" href="%s">click here</a>.',
									'woocommerce-paypal-payments'
								),
								'#'
							),
							additionalContent: (
								<>
									<InputSettingsBlock
										title={ __(
											'Sandbox Client ID',
											'woocommerce-paypal-payments'
										) }
										actionProps={ {
											value: settings.sandboxClientId, // Add this to settings if not present
											callback: updateFormValue,
											key: 'sandboxClientId',
											placeholder: __(
												'Enter Client ID',
												'woocommerce-paypal-payments'
											),
										} }
									/>
									<InputSettingsBlock
										title={ __(
											'Sandbox Secret Key',
											'woocommerce-paypal-payments'
										) }
										actionProps={ {
											value: settings.sandboxSecretKey, // Add this to settings if not present
											callback: updateFormValue,
											key: 'sandboxSecretKey',
											placeholder: __(
												'Enter Secret Key',
												'woocommerce-paypal-payments'
											),
										} }
									/>
									<Button
										variant="primary"
										onClick={ () =>
											updateFormValue(
												'sandboxManuallyConnected',
												true
											)
										} // Add this handler if needed
									>
										{ __(
											'Connect Account',
											'woocommerce-paypal-payments'
										) }
									</Button>
								</>
							),
						},
					] }
					actionProps={ {
						name: 'paypal_connect_sandbox',
						key: 'sandboxMode',
						currentValue: settings.sandboxMode,
						callback: updateFormValue,
					} }
				/>
			) }
		</AccordionSettingsBlock>
	);
};

export default Sandbox;
