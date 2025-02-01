import { __ } from '@wordpress/i18n';

import PricingDescription from '../PricingDescription';
import AcdcFlow from './AcdcFlow';
import BcdcFlow from './BcdcFlow';

const WelcomeDocs = ( { useAcdc, isFastlane, isPayLater, storeCountry } ) => {
	return (
		<div className="ppcp-r-welcome-docs">
			<h2 className="ppcp-r-welcome-docs__title">
				{ __(
					`Want to know more about PayPal Payments?`,
					'woocommerce-paypal-payments'
				) }
			</h2>
			{ useAcdc ? (
				<AcdcFlow
					isFastlane={ isFastlane }
					isPayLater={ isPayLater }
					storeCountry={ storeCountry }
				/>
			) : (
				<BcdcFlow
					isPayLater={ isPayLater }
					storeCountry={ storeCountry }
				/>
			) }
			<PricingDescription />
		</div>
	);
};

export default WelcomeDocs;
