import { __ } from '@wordpress/i18n';

import { CommonHooks, OnboardingHooks } from '../../../data';
import OnboardingHeader from '../../ReusableComponents/OnboardingHeader';
import SelectBoxWrapper from '../../ReusableComponents/SelectBoxWrapper';
import SelectBox from '../../ReusableComponents/SelectBox';
import OptionalPaymentMethods from '../../ReusableComponents/OptionalPaymentMethods/OptionalPaymentMethods';
import PricingDescription from '../../ReusableComponents/PricingDescription';

const OPM_RADIO_GROUP_NAME = 'optional-payment-methods';

const StepPaymentMethods = ( {} ) => {
	const {
		areOptionalPaymentMethodsEnabled,
		setAreOptionalPaymentMethodsEnabled,
	} = OnboardingHooks.useOptionalPaymentMethods();

	const { storeCountry, storeCurrency } = CommonHooks.useWooSettings();

	return (
		<div className="ppcp-r-page-optional-payment-methods">
			<OnboardingHeader
				title={ __(
					'Add optional payment methods to your Checkout',
					'woocommerce-paypal-payments'
				) }
			/>
			<div className="ppcp-r-inner-container">
				<SelectBoxWrapper>
					<SelectBox
						title={ __(
							'Available with additional application',
							'woocommerce-paypal-payments'
						) }
						description={
							<OptionalPaymentMethods
								useAcdc={ true }
								isFastlane={ true }
								isPayLater={ true }
								storeCountry={ storeCountry }
								storeCurrency={ storeCurrency }
							/>
						}
						name={ OPM_RADIO_GROUP_NAME }
						value={ true }
						changeCallback={ setAreOptionalPaymentMethodsEnabled }
						currentValue={ areOptionalPaymentMethodsEnabled }
						type="radio"
					></SelectBox>
					<SelectBox
						title={ __(
							'No thanks, I prefer to use a different provider for processing credit cards, digital wallets, and local payment methods',
							'woocommerce-paypal-payments'
						) }
						name={ OPM_RADIO_GROUP_NAME }
						value={ false }
						changeCallback={ setAreOptionalPaymentMethodsEnabled }
						currentValue={ areOptionalPaymentMethodsEnabled }
						type="radio"
					></SelectBox>
				</SelectBoxWrapper>
				<PricingDescription />
			</div>
		</div>
	);
};

export default StepPaymentMethods;
