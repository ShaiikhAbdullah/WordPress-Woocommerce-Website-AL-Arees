import AcdcOptionalPaymentMethods from './AcdcOptionalPaymentMethods';
import BcdcOptionalPaymentMethods from './BcdcOptionalPaymentMethods';

const OptionalPaymentMethods = ( {
	useAcdc,
	isFastlane,
	isPayLater,
	storeCountry,
} ) => {
	return (
		<div className="ppcp-r-optional-payment-methods">
			{ useAcdc ? (
				<AcdcOptionalPaymentMethods
					isFastlane={ isFastlane }
					isPayLater={ isPayLater }
					storeCountry={ storeCountry }
				/>
			) : (
				<BcdcOptionalPaymentMethods
					isPayLater={ isPayLater }
					storeCountry={ storeCountry }
				/>
			) }
		</div>
	);
};

export default OptionalPaymentMethods;
