import { __ } from '@wordpress/i18n';
import { CommonHooks } from '../../../../../../data';

const HooksTableBlock = () => {
	const { webhooks } = CommonHooks.useWebhooks();

	return (
		<table className="ppcp-r-table">
			<thead>
				<tr>
					<th className="ppcp-r-table__hooks-url">
						{ __( 'URL', 'woocommerce-paypal-payments' ) }
					</th>
					<th className="ppcp-r-table__hooks-events">
						{ __(
							'Tracked events',
							'woocommerce-paypal-payments'
						) }
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td className="ppcp-r-table__hooks-url">
						{ webhooks?.url }
					</td>
					<td
						className="ppcp-r-table__hooks-events"
						dangerouslySetInnerHTML={ { __html: webhooks?.events } }
					></td>
				</tr>
			</tbody>
		</table>
	);
};

export default HooksTableBlock;
