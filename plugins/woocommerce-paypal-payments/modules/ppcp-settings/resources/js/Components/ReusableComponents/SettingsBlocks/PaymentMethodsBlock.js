import { useState, useCallback } from '@wordpress/element';
import SettingsBlock from './SettingsBlock';
import PaymentMethodItemBlock from './PaymentMethodItemBlock';

const PaymentMethodsBlock = ( { paymentMethods, className = '' } ) => {
	const [ selectedMethod, setSelectedMethod ] = useState( null );

	const handleSelect = useCallback( ( methodId, isSelected ) => {
		setSelectedMethod( isSelected ? methodId : null );
	}, [] );

	if ( paymentMethods.length === 0 ) {
		return null;
	}

	return (
		<SettingsBlock
			className={ `ppcp-r-settings-block__payment-methods ${ className }` }
		>
			{ paymentMethods.map( ( paymentMethod ) => (
				<PaymentMethodItemBlock
					key={ paymentMethod.id }
					{ ...paymentMethod }
					isSelected={ selectedMethod === paymentMethod.id }
					onSelect={ ( checked ) =>
						handleSelect( paymentMethod.id, checked )
					}
				/>
			) ) }
		</SettingsBlock>
	);
};

export default PaymentMethodsBlock;
