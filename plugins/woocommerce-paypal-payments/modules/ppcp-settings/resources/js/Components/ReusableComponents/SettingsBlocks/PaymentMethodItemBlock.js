import { useState } from '@wordpress/element';
import { ToggleControl } from '@wordpress/components';
import SettingsBlock from './SettingsBlock';
import PaymentMethodIcon from '../PaymentMethodIcon';
import data from '../../../utils/data';

const PaymentMethodItemBlock = ( props ) => {
	const [ toggleIsChecked, setToggleIsChecked ] = useState( false );
	const [ modalIsVisible, setModalIsVisible ] = useState( false );
	const Modal = props?.modal;

	return (
		<>
			<SettingsBlock className="ppcp-r-settings-block__payment-methods__item">
				<div className="ppcp-r-settings-block__payment-methods__item__inner">
					<div className="ppcp-r-settings-block__payment-methods__item__title-wrapper">
						<PaymentMethodIcon
							icons={ [ props.icon ] }
							type={ props.icon }
						/>
						<span className="ppcp-r-settings-block__payment-methods__item__title">
							{ props.title }
						</span>
					</div>
					<p className="ppcp-r-settings-block__payment-methods__item__description">
						{ props.description }
					</p>
					<div className="ppcp-r-settings-block__payment-methods__item__footer">
						<ToggleControl
							__nextHasNoMarginBottom={ true }
							checked={ toggleIsChecked }
							onChange={ setToggleIsChecked }
						/>
						{ Modal && (
							<div
								className="ppcp-r-settings-block__payment-methods__item__settings"
								onClick={ () => setModalIsVisible( true ) }
							>
								{ data().getImage( 'icon-settings.svg' ) }
							</div>
						) }
					</div>
				</div>
			</SettingsBlock>
			{ Modal && modalIsVisible && (
				<Modal setModalIsVisible={ setModalIsVisible } />
			) }
		</>
	);
};

export default PaymentMethodItemBlock;
