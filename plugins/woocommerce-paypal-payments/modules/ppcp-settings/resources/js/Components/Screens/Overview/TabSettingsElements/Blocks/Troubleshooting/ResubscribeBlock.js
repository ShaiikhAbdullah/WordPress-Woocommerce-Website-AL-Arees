import { useState } from '@wordpress/element';
import { STORE_NAME } from '../../../../../../data/common';
import { ButtonSettingsBlock } from '../../../../../ReusableComponents/SettingsBlocks';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import {
	NOTIFICATION_ERROR,
	NOTIFICATION_SUCCESS,
} from '../../../../../ReusableComponents/Icons';

const ResubscribeBlock = () => {
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );
	const [ resubscribing, setResubscribing ] = useState( false );

	const { resubscribeWebhooks } = useDispatch( STORE_NAME );

	const startResubscribingWebhooks = async () => {
		setResubscribing( true );
		try {
			await resubscribeWebhooks();
		} catch ( error ) {
			setResubscribing( false );
			createErrorNotice(
				__(
					'Operation failed. Check WooCommerce logs for more details.',
					'woocommerce-paypal-payments'
				),
				{
					icon: NOTIFICATION_ERROR,
				}
			);
			return;
		}

		setResubscribing( false );
		createSuccessNotice(
			__(
				'Webhooks were successfully re-subscribed.',
				'woocommerce-paypal-payments'
			),
			{
				icon: NOTIFICATION_SUCCESS,
			}
		);
	};

	return (
		<ButtonSettingsBlock
			title={ __(
				'Resubscribe webhooks',
				'woocommerce-paypal-payments'
			) }
			description={ __(
				'Click to remove the current webhook subscription and subscribe again, for example, if the website domain or URL structure changed.',
				'woocommerce-paypal-payments'
			) }
			actionProps={ {
				buttonType: 'secondary',
				isBusy: resubscribing,
				callback: () => startResubscribingWebhooks(),
				value: __(
					'Resubscribe webhooks',
					'woocommerce-paypal-payments'
				),
			} }
		/>
	);
};

export default ResubscribeBlock;
