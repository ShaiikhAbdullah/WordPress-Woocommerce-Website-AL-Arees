import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Button, Icon } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { reusableBlock } from '@wordpress/icons';

import SettingsCard from '../../ReusableComponents/SettingsCard';
import TodoSettingsBlock from '../../ReusableComponents/SettingsBlocks/TodoSettingsBlock';
import FeatureSettingsBlock from '../../ReusableComponents/SettingsBlocks/FeatureSettingsBlock';
import { TITLE_BADGE_POSITIVE } from '../../ReusableComponents/TitleBadge';
import { useMerchantInfo } from '../../../data/common/hooks';
import { STORE_NAME } from '../../../data/common';

const TabOverview = () => {
	const [ todos, setTodos ] = useState( [] );
	const [ todosData, setTodosData ] = useState( todosDataDefault );
	const [ isRefreshing, setIsRefreshing ] = useState( false );

	const { merchant } = useMerchantInfo();
	const { refreshFeatureStatuses } = useDispatch( STORE_NAME );

	const features = featuresDefault.map( ( feature ) => {
		const merchantFeature = merchant?.features?.[ feature.id ];
		return {
			...feature,
			enabled: merchantFeature?.enabled ?? false,
		};
	} );

	const refreshHandler = async () => {
		setIsRefreshing( true );

		const result = await refreshFeatureStatuses();

		// TODO: Implement the refresh logic, remove this debug code -- PCP-4024
		if ( result && ! result.success ) {
			console.error(
				'Failed to refresh features:',
				result.message || 'Unknown error'
			);
		} else {
			console.log( 'Features refreshed successfully.' );
		}

		setIsRefreshing( false );
	};

	return (
		<div className="ppcp-r-tab-overview">
			{ todosData.length > 0 && (
				<SettingsCard
					className="ppcp-r-tab-overview-todo"
					title={ __(
						'Things to do next',
						'woocommerce-paypal-payments'
					) }
					description={ __(
						'Complete these tasks to keep your store updated with the latest products and services.',
						'woocommerce-paypal-payments'
					) }
				>
					<TodoSettingsBlock
						todos={ todos }
						setTodos={ setTodos }
						todosData={ todosData }
						setTodosData={ setTodosData }
					/>
				</SettingsCard>
			) }

			<SettingsCard
				className="ppcp-r-tab-overview-features"
				title={ __( 'Features', 'woocommerce-paypal-payments' ) }
				description={
					<>
						<p>
							{ __(
								'Enable additional features and capabilities on your WooCommerce store.',
								'woocommerce-paypal-payments'
							) }
						</p>
						<p>
							{ __(
								'Click Refresh to update your current features after making changes.',
								'woocommerce-paypal-payments'
							) }
						</p>
						<Button
							variant="tertiary"
							onClick={ refreshHandler }
							disabled={ isRefreshing }
						>
							<Icon icon={ reusableBlock } size={ 18 } />
							{ isRefreshing
								? __(
										'Refreshing…',
										'woocommerce-paypal-payments'
								  )
								: __(
										'Refresh',
										'woocommerce-paypal-payments'
								  ) }
						</Button>
					</>
				}
				contentItems={ features.map( ( feature ) => (
					<FeatureSettingsBlock
						key={ feature.id }
						title={ feature.title }
						description={ feature.description }
						actionProps={ {
							buttons: feature.buttons,
							enabled: feature.enabled,
							notes: feature.notes,
							badge: feature.enabled
								? {
										text: __(
											'Active',
											'woocommerce-paypal-payments'
										),
										type: TITLE_BADGE_POSITIVE,
								  }
								: undefined,
						} }
					/>
				) ) }
			/>

			<SettingsCard
				className="ppcp-r-tab-overview-help"
				title={ __( 'Help Center', 'woocommerce-paypal-payments' ) }
				description={ __(
					'Access detailed guides and responsive support to streamline setup and enhance your experience.',
					'woocommerce-paypal-payments'
				) }
				contentItems={ [
					<FeatureSettingsBlock
						key="documentation"
						title={ __(
							'Documentation',
							'woocommerce-paypal-payments'
						) }
						description={ __(
							'Find detailed guides and resources to help you set up, manage, and optimize your PayPal integration.',
							'woocommerce-paypal-payments'
						) }
						actionProps={ {
							buttons: [
								{
									type: 'tertiary',
									text: __(
										'View full documentation',
										'woocommerce-paypal-payments'
									),
									url: '#',
								},
							],
						} }
					/>,
					<FeatureSettingsBlock
						key="support"
						title={ __( 'Support', 'woocommerce-paypal-payments' ) }
						description={ __(
							'Need help? Access troubleshooting tips or contact our support team for personalized assistance.',
							'woocommerce-paypal-payments'
						) }
						actionProps={ {
							buttons: [
								{
									type: 'tertiary',
									text: __(
										'View support options',
										'woocommerce-paypal-payments'
									),
									url: '#',
								},
							],
						} }
					/>,
				] }
			/>
		</div>
	);
};

// TODO: This list should be refactored into a separate module, maybe utils/thingsToDoNext.js
const todosDataDefault = [
	{
		value: 'paypal_later_messaging',
		description: __(
			'Enable Pay Later messaging',
			'woocommerce-paypal-payments'
		),
	},
	{
		value: 'capture_authorized_payments',
		description: __(
			'Capture authorized payments',
			'woocommerce-paypal-payments'
		),
	},
	{
		value: 'enable_google_pay',
		description: __( 'Enable Google Pay', 'woocommerce-paypal-payments' ),
	},
	{
		value: 'paypal_shortcut',
		description: __(
			'Add PayPal shortcut to the Cart page',
			'woocommerce-paypal-payments'
		),
	},
	{
		value: 'advanced_cards',
		description: __(
			'Add Advanced Cards to Blocks Checkout',
			'woocommerce-paypal-payments'
		),
	},
];

// TODO: Hardcoding this list here is not the best idea. Can we move this to a REST API response?
const featuresDefault = [
	{
		id: 'save_paypal_and_venmo',
		title: __( 'Save PayPal and Venmo', 'woocommerce-paypal-payments' ),
		description: __(
			'Securely save PayPal and Venmo payment methods for subscriptions or return buyers.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'secondary',
				class: 'small-button',
				text: __( 'Configure', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'tertiary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
	{
		id: 'advanced_credit_and_debit_cards',
		title: __(
			'Advanced Credit and Debit Cards',
			'woocommerce-paypal-payments'
		),
		description: __(
			'Process major credit and debit cards including Visa, Mastercard, American Express and Discover.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'secondary',
				class: 'small-button',
				text: __( 'Configure', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'tertiary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
	{
		id: 'alternative_payment_methods',
		title: __(
			'Alternative Payment Methods',
			'woocommerce-paypal-payments'
		),
		description: __(
			'Offer global, country-specific payment options for your customers.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'secondary',
				class: 'small-button',
				text: __( 'Apply', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'tertiary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
	{
		id: 'google_pay',
		title: __( 'Google Pay', 'woocommerce-paypal-payments' ),
		description: __(
			'Let customers pay using their Google Pay wallet.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'secondary',
				class: 'small-button',
				text: __( 'Configure', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'tertiary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
	{
		id: 'apple_pay',
		title: __( 'Apple Pay', 'woocommerce-paypal-payments' ),
		description: __(
			'Let customers pay using their Apple Pay wallet.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'secondary',
				class: 'small-button',
				text: __(
					'Domain registration',
					'woocommerce-paypal-payments'
				),
				url: '#',
			},
			{
				type: 'tertiary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
	},
	{
		id: 'pay_later_messaging',
		title: __( 'Pay Later Messaging', 'woocommerce-paypal-payments' ),
		description: __(
			'Let customers know they can buy now and pay later with PayPal. Adding this messaging can boost conversion rates and increase cart sizes by 39%¹, with no extra cost to you—plus, you get paid up front.',
			'woocommerce-paypal-payments'
		),
		buttons: [
			{
				type: 'secondary',
				class: 'small-button',
				text: __( 'Configure', 'woocommerce-paypal-payments' ),
				url: '#',
			},
			{
				type: 'tertiary',
				text: __( 'Learn more', 'woocommerce-paypal-payments' ),
				url: '#',
			},
		],
		notes: [
			__( '¹PayPal Q2 Earnings-2021.', 'woocommerce-paypal-payments' ),
		],
	},
];

export default TabOverview;
