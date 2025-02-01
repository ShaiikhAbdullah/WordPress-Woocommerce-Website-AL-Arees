import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

const ConnectionInfo = ( { connectionStatusDataDefault } ) => {
	const [ connectionData, setConnectionData ] = useState( {
		...connectionStatusDataDefault,
	} );

	const toggleStatusClassName = [ 'ppcp-r-connection-status__status-toggle' ];

	if ( connectionData.showAllData ) {
		toggleStatusClassName.push(
			'ppcp-r-connection-status__status-toggle--toggled'
		);
	}

	return (
		<div className="ppcp-r-connection-status__data">
			<div className="ppcp-r-connection-status__status-row ppcp-r-connection-status__status-row--first">
				<span className="ppcp-r-connection-status__status-label">
					{ __( 'Merchant ID', 'woocommerce-paypal-payments' ) }
				</span>
				<span className="ppcp-r-connection-status__status-value">
					{ connectionData.merchantId }
				</span>
			</div>
			<div className="ppcp-r-connection-status__status-row">
				<span className="ppcp-r-connection-status__status-label">
					{ __( 'Email address', 'woocommerce-paypal-payments' ) }
				</span>
				<span className="ppcp-r-connection-status__status-value">
					{ connectionData.email }
				</span>
			</div>
			<div className="ppcp-r-connection-status__status-row">
				<span className="ppcp-r-connection-status__status-label">
					{ __( 'Client ID', 'woocommerce-paypal-payments' ) }
				</span>
				<span className="ppcp-r-connection-status__status-value">
					{ connectionData.clientId }
				</span>
			</div>
		</div>
	);
};
export default ConnectionInfo;

export const connectionStatusDataDefault = {
	connectionStatus: true,
	showAllData: false,
	email: 'bt_us@woocommerce.com',
	merchantId: 'AT45V2DGMKLRY',
	clientId: 'BAARTJLxtUNN4d2GMB6Eut3suMDYad72xQA-FntdIFuJ6FmFJITxAY8',
};
